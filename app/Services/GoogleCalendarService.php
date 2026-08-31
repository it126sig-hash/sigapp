<?php

namespace App\Services;

use CodeIgniter\HTTP\CURLRequest;
use DateInterval;
use DateTimeImmutable;
use DateTimeZone;
use RuntimeException;

class GoogleCalendarService
{
    private const SCOPE = 'openid email https://www.googleapis.com/auth/calendar.events';
    private const AUTH_URL = 'https://accounts.google.com/o/oauth2/v2/auth';
    private const TOKEN_URL = 'https://oauth2.googleapis.com/token';
    private const USERINFO_URL = 'https://openidconnect.googleapis.com/v1/userinfo';
    private const EVENT_URL = 'https://www.googleapis.com/calendar/v3/calendars/%s/events';

    protected $db;
    protected CURLRequest $http;
    protected string $clientId;
    protected string $clientSecret;
    protected string $redirectUri;
    protected string $tokenKey;
    protected string $defaultCalendarId;
    protected string $timezone;
    protected int $eventOffsetHours;
    protected int $eventDurationMinutes;
    protected int $popupReminderMinutes;

    public function __construct(?CURLRequest $http = null)
    {
        $this->db = db_connect();
        $this->clientId = (string) env('GOOGLE_CALENDAR_CLIENT_ID', '');
        $this->clientSecret = (string) env('GOOGLE_CALENDAR_CLIENT_SECRET', '');
        $this->redirectUri = (string) env('GOOGLE_CALENDAR_REDIRECT_URI', '');
        $this->tokenKey = (string) env('GOOGLE_CALENDAR_TOKEN_KEY', '');
        $this->defaultCalendarId = (string) env('GOOGLE_CALENDAR_DEFAULT_ID', 'primary');
        $this->timezone = (string) env('GOOGLE_CALENDAR_TIMEZONE', 'Asia/Jakarta');
        $this->eventOffsetHours = (int) env('GOOGLE_CALENDAR_EVENT_OFFSET_HOURS', 2);
        $this->eventDurationMinutes = (int) env('GOOGLE_CALENDAR_EVENT_DURATION_MINUTES', 60);
        $this->popupReminderMinutes = (int) env('GOOGLE_CALENDAR_POPUP_REMINDER_MINUTES', 30);
        $this->http = $http ?? \Config\Services::curlrequest([
            'timeout' => 15,
            'http_errors' => false,
        ]);
    }

    public function isConfigured(): bool
    {
        return $this->clientId !== ''
            && $this->clientSecret !== ''
            && $this->tokenKey !== '';
    }

    public function getConfigurationMessage(): string
    {
        if ($this->isConfigured()) {
            return '';
        }

        return 'Google Calendar belum dikonfigurasi. Set GOOGLE_CALENDAR_CLIENT_ID, GOOGLE_CALENDAR_CLIENT_SECRET, dan GOOGLE_CALENDAR_TOKEN_KEY di .env.';
    }

    public function tablesReady(): bool
    {
        return $this->db->tableExists('google_calendar_connections')
            && $this->db->tableExists('google_calendar_event_syncs');
    }

    public function getStatus(int $userId): array
    {
        if (! $this->tablesReady()) {
            return [
                'connected' => false,
                'configured' => $this->isConfigured(),
                'ready' => false,
                'message' => 'Migrasi Google Calendar belum dijalankan.',
            ];
        }

        $row = $this->db->table('google_calendar_connections')
            ->where('user_id', $userId)
            ->get()
            ->getRow();

        return [
            'connected' => $row && empty($row->disconnected_at) && ! empty($row->refresh_token_enc),
            'configured' => $this->isConfigured(),
            'ready' => true,
            'message' => $this->getConfigurationMessage(),
            'google_email' => $row->google_email ?? null,
            'calendar_id' => $row->calendar_id ?? $this->defaultCalendarId,
            'connected_at' => $row->connected_at ?? null,
            'disconnected_at' => $row->disconnected_at ?? null,
        ];
    }

    public function buildAuthUrl(int $userId): string
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException($this->getConfigurationMessage());
        }
        if (! $this->tablesReady()) {
            throw new RuntimeException('Migrasi Google Calendar belum dijalankan.');
        }

        $state = bin2hex(random_bytes(24));
        session()->set('google_calendar_oauth_state', $state);
        session()->set('google_calendar_oauth_user_id', $userId);

        return self::AUTH_URL . '?' . http_build_query([
            'client_id' => $this->clientId,
            'redirect_uri' => $this->redirectUri(),
            'response_type' => 'code',
            'scope' => self::SCOPE,
            'access_type' => 'offline',
            'prompt' => 'consent',
            'state' => $state,
        ], '', '&', PHP_QUERY_RFC3986);
    }

    public function connectFromCallback(int $userId, string $state, string $code): void
    {
        if (! $this->tablesReady()) {
            throw new RuntimeException('Migrasi Google Calendar belum dijalankan.');
        }

        if (! $this->isConfigured()) {
            throw new RuntimeException($this->getConfigurationMessage());
        }

        $expectedState = (string) session()->get('google_calendar_oauth_state');
        $expectedUserId = (int) session()->get('google_calendar_oauth_user_id');
        session()->remove(['google_calendar_oauth_state', 'google_calendar_oauth_user_id']);

        if ($expectedState === '' || ! hash_equals($expectedState, $state) || $expectedUserId !== $userId) {
            throw new RuntimeException('State OAuth Google Calendar tidak valid.');
        }

        $token = $this->requestToken([
            'code' => $code,
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'redirect_uri' => $this->redirectUri(),
            'grant_type' => 'authorization_code',
        ]);

        if (empty($token['access_token'])) {
            throw new RuntimeException('Google tidak mengembalikan access token.');
        }

        $existing = $this->db->table('google_calendar_connections')
            ->where('user_id', $userId)
            ->get()
            ->getRow();
        $refreshToken = $token['refresh_token'] ?? null;
        if (! $refreshToken && $existing && ! empty($existing->refresh_token_enc)) {
            $refreshToken = $this->decryptToken($existing->refresh_token_enc);
        }
        if (! $refreshToken) {
            throw new RuntimeException('Google tidak mengembalikan refresh token. Coba disconnect akses SIGAPP di akun Google, lalu connect ulang.');
        }

        $userInfo = $this->fetchGoogleUserInfo($token['access_token']);
        $now = date('Y-m-d H:i:s');
        $payload = [
            'user_id' => $userId,
            'google_email' => $userInfo['email'] ?? null,
            'calendar_id' => $this->defaultCalendarId,
            'access_token_enc' => $this->encryptToken($token['access_token']),
            'refresh_token_enc' => $this->encryptToken($refreshToken),
            'token_expires_at' => $this->expiresAt((int) ($token['expires_in'] ?? 3600)),
            'connected_at' => $now,
            'disconnected_at' => null,
            'updated_at' => $now,
        ];

        if ($existing) {
            $this->db->table('google_calendar_connections')
                ->where('user_id', $userId)
                ->update($payload);
        } else {
            $payload['created_at'] = $now;
            $this->db->table('google_calendar_connections')->insert($payload);
        }
    }

    public function disconnect(int $userId): bool
    {
        if (! $this->tablesReady()) {
            return false;
        }

        return (bool) $this->db->table('google_calendar_connections')
            ->where('user_id', $userId)
            ->update([
                'access_token_enc' => null,
                'refresh_token_enc' => null,
                'token_expires_at' => null,
                'disconnected_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
    }

    public function syncUrgentTicketFromNotificationItem(object $item, int $userId, string $emailSentAt): array
    {
        if (! $this->tablesReady()) {
            return ['status' => 'skipped', 'reason' => 'tables_not_ready'];
        }

        $ticketId = $this->extractTicketId((string) ($item->type ?? ''));
        if ($ticketId <= 0) {
            return ['status' => 'skipped', 'reason' => 'missing_ticket_id'];
        }

        if ($this->findSync($userId, $ticketId)) {
            return ['status' => 'skipped', 'reason' => 'already_synced'];
        }

        $ticket = $this->getUrgentTicketForCalendar($ticketId);
        if (! $ticket) {
            return ['status' => 'skipped', 'reason' => 'not_eligible'];
        }

        if (! $this->isTicketAssignedToUser($ticketId, $userId)) {
            return ['status' => 'skipped', 'reason' => 'not_assigned'];
        }

        if (! $this->isUserConnected($userId)) {
            $this->recordSync($userId, $ticketId, (int) ($item->id ?? 0), 'skipped', null, null, 'User belum connect Google Calendar');
            return ['status' => 'skipped', 'reason' => 'not_connected'];
        }

        try {
            $accessToken = $this->getValidAccessToken($userId);
            $start = $this->buildEventStart($ticket->tanggal_kunjungan, $emailSentAt);
            $event = $this->createGoogleEvent($accessToken, $userId, $ticket, $start);
            $this->recordSync(
                $userId,
                $ticketId,
                (int) ($item->id ?? 0),
                'created',
                $event['id'] ?? null,
                $start->format('Y-m-d H:i:s'),
                null
            );

            return ['status' => 'created', 'event_id' => $event['id'] ?? null];
        } catch (\Throwable $e) {
            $this->recordSync($userId, $ticketId, (int) ($item->id ?? 0), 'failed', null, null, $e->getMessage());
            log_message('error', 'Google Calendar sync gagal: ' . $e->getMessage());
            return ['status' => 'failed', 'reason' => $e->getMessage()];
        }
    }

    protected function redirectUri(): string
    {
        return $this->redirectUri !== ''
            ? $this->redirectUri
            : site_url('google-calendar/callback');
    }

    protected function requestToken(array $form): array
    {
        $response = $this->http->post(self::TOKEN_URL, ['form_params' => $form]);
        $body = json_decode((string) $response->getBody(), true);
        if ($response->getStatusCode() >= 400 || ! is_array($body)) {
            throw new RuntimeException('Request token Google gagal.');
        }

        if (! empty($body['error'])) {
            throw new RuntimeException('Request token Google gagal: ' . ($body['error_description'] ?? $body['error']));
        }

        return $body;
    }

    protected function fetchGoogleUserInfo(string $accessToken): array
    {
        $response = $this->http->get(self::USERINFO_URL, [
            'headers' => ['Authorization' => 'Bearer ' . $accessToken],
        ]);
        $body = json_decode((string) $response->getBody(), true);

        return is_array($body) ? $body : [];
    }

    protected function getValidAccessToken(int $userId): string
    {
        $connection = $this->getActiveConnection($userId);
        if (! $connection) {
            throw new RuntimeException('User belum connect Google Calendar.');
        }

        $expiresAt = strtotime((string) $connection->token_expires_at);
        if ($expiresAt && $expiresAt > time() + 300) {
            return $this->decryptToken($connection->access_token_enc);
        }

        $refreshToken = $this->decryptToken($connection->refresh_token_enc);
        $token = $this->requestToken([
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'refresh_token' => $refreshToken,
            'grant_type' => 'refresh_token',
        ]);

        if (empty($token['access_token'])) {
            throw new RuntimeException('Google tidak mengembalikan access token saat refresh.');
        }

        $this->db->table('google_calendar_connections')
            ->where('user_id', $userId)
            ->update([
                'access_token_enc' => $this->encryptToken($token['access_token']),
                'token_expires_at' => $this->expiresAt((int) ($token['expires_in'] ?? 3600)),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

        return $token['access_token'];
    }

    protected function createGoogleEvent(string $accessToken, int $userId, object $ticket, DateTimeImmutable $start): array
    {
        $connection = $this->getActiveConnection($userId);
        $calendarId = $connection->calendar_id ?? $this->defaultCalendarId;
        $end = $start->add(new DateInterval('PT' . max(1, $this->eventDurationMinutes) . 'M'));
        $timezone = $this->timezone;

        $payload = [
            'summary' => '[URGENT] Tiket Masalah - ' . ($ticket->lokasi ?: ($ticket->nama_proyek ?? 'SIGAPP')),
            'description' => $this->buildEventDescription($ticket),
            'start' => [
                'dateTime' => $start->format('Y-m-d\TH:i:s'),
                'timeZone' => $timezone,
            ],
            'end' => [
                'dateTime' => $end->format('Y-m-d\TH:i:s'),
                'timeZone' => $timezone,
            ],
            'reminders' => [
                'useDefault' => false,
                'overrides' => [
                    ['method' => 'popup', 'minutes' => max(0, $this->popupReminderMinutes)],
                ],
            ],
        ];

        $response = $this->http->post(sprintf(self::EVENT_URL, rawurlencode($calendarId)), [
            'headers' => [
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json',
            ],
            'json' => $payload,
        ]);
        $body = json_decode((string) $response->getBody(), true);

        if ($response->getStatusCode() >= 400 || ! is_array($body)) {
            throw new RuntimeException('Create event Google Calendar gagal.');
        }

        if (! empty($body['error'])) {
            $message = is_array($body['error']) ? ($body['error']['message'] ?? 'Unknown Google error') : $body['error'];
            throw new RuntimeException('Create event Google Calendar gagal: ' . $message);
        }

        return $body;
    }

    protected function buildEventDescription(object $ticket): string
    {
        $plainText = trim(strip_tags((string) $ticket->keterangan));
        if (strlen($plainText) > 300) {
            $plainText = substr($plainText, 0, 297) . '...';
        }

        return implode("\n", array_filter([
            'ID tiket: #' . $ticket->id,
            'Proyek: ' . ($ticket->nama_proyek ?? '-'),
            'Lokasi: ' . ($ticket->lokasi ?: '-'),
            'Tanggal kunjungan: ' . ($ticket->tanggal_kunjungan ?? '-'),
            'Ringkasan: ' . $plainText,
            'Buka SIGAPP: ' . site_url('siteplan/view') . '?' . http_build_query([
                'urgent_action' => 'tiket_masalah|' . $ticket->ref_type . '|' . $ticket->ref_id,
            ]),
        ]));
    }

    protected function buildEventStart(string $tanggalKunjungan, string $emailSentAt): DateTimeImmutable
    {
        $timezone = new DateTimeZone($this->timezone);
        $sent = new DateTimeImmutable($emailSentAt, $timezone);
        $time = $sent->add(new DateInterval('PT' . max(0, $this->eventOffsetHours) . 'H'));

        return new DateTimeImmutable($tanggalKunjungan . ' ' . $time->format('H:i:s'), $timezone);
    }

    protected function getUrgentTicketForCalendar(int $ticketId): ?object
    {
        return $this->db->table('tiket_masalah tm')
            ->select('tm.*, p.nama_proyek')
            ->select('IF(tm.ref_type = "kavling", CONCAT_WS(" - ", jk.nama_jalan, k.no_kavling), CONCAT_WS(" - ", jo.nama_jalan, o.nama)) as lokasi', false)
            ->join('proyek p', 'p.id_proyek = tm.id_proyek', 'left')
            ->join('kavling k', 'k.id_kavling = tm.ref_id AND tm.ref_type = "kavling"', 'left')
            ->join('jalan jk', 'jk.id_jalan = k.id_jalan', 'left')
            ->join('others o', 'o.id = tm.ref_id AND tm.ref_type = "others"', 'left')
            ->join('jalan jo', 'jo.id_jalan = o.id_jalan', 'left')
            ->where('tm.id', $ticketId)
            ->where('tm.prioritas', 'urgent')
            ->where('tm.status !=', 'draft')
            ->where('tm.tanggal_kunjungan IS NOT NULL', null, false)
            ->where('tm.tanggal_kunjungan !=', '0000-00-00')
            ->get()
            ->getRow();
    }

    protected function isTicketAssignedToUser(int $ticketId, int $userId): bool
    {
        return (bool) $this->db->table('tiket_masalah_user')
            ->where('id_tiket_masalah', $ticketId)
            ->where('user_id', $userId)
            ->countAllResults();
    }

    protected function isUserConnected(int $userId): bool
    {
        return (bool) $this->getActiveConnection($userId);
    }

    protected function getActiveConnection(int $userId): ?object
    {
        return $this->db->table('google_calendar_connections')
            ->where('user_id', $userId)
            ->where('disconnected_at IS NULL', null, false)
            ->where('refresh_token_enc IS NOT NULL', null, false)
            ->get()
            ->getRow();
    }

    protected function findSync(int $userId, int $ticketId): ?object
    {
        return $this->db->table('google_calendar_event_syncs')
            ->where('user_id', $userId)
            ->where('id_tiket_masalah', $ticketId)
            ->get()
            ->getRow();
    }

    protected function recordSync(int $userId, int $ticketId, int $queueId, string $status, ?string $eventId, ?string $eventStartAt, ?string $error): void
    {
        $existing = $this->findSync($userId, $ticketId);
        $payload = [
            'user_id' => $userId,
            'id_tiket_masalah' => $ticketId,
            'notification_email_queue_id' => $queueId > 0 ? $queueId : null,
            'google_event_id' => $eventId,
            'event_start_at' => $eventStartAt,
            'status' => $status,
            'error_message' => $error,
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if ($existing) {
            $this->db->table('google_calendar_event_syncs')
                ->where('id', $existing->id)
                ->update($payload);
            return;
        }

        $payload['created_at'] = date('Y-m-d H:i:s');
        $this->db->table('google_calendar_event_syncs')->insert($payload);
    }

    protected function extractTicketId(string $type): int
    {
        $parts = explode('|', $type);
        if (($parts[0] ?? '') !== 'tiket_masalah') {
            return 0;
        }

        return isset($parts[3]) && ctype_digit((string) $parts[3]) ? (int) $parts[3] : 0;
    }

    protected function expiresAt(int $expiresIn): string
    {
        return date('Y-m-d H:i:s', time() + max(60, $expiresIn));
    }

    protected function encryptToken(string $plainText): string
    {
        $key = $this->binaryTokenKey();
        $iv = random_bytes(16);
        $cipherText = openssl_encrypt($plainText, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
        if ($cipherText === false) {
            throw new RuntimeException('Gagal mengenkripsi token Google Calendar.');
        }

        $mac = hash_hmac('sha256', $iv . $cipherText, $key, true);
        return base64_encode($iv . $mac . $cipherText);
    }

    protected function decryptToken(string $encoded): string
    {
        $raw = base64_decode($encoded, true);
        if ($raw === false || strlen($raw) <= 48) {
            throw new RuntimeException('Token Google Calendar tidak valid.');
        }

        $key = $this->binaryTokenKey();
        $iv = substr($raw, 0, 16);
        $mac = substr($raw, 16, 32);
        $cipherText = substr($raw, 48);
        $expectedMac = hash_hmac('sha256', $iv . $cipherText, $key, true);
        if (! hash_equals($expectedMac, $mac)) {
            throw new RuntimeException('Token Google Calendar gagal diverifikasi.');
        }

        $plainText = openssl_decrypt($cipherText, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
        if ($plainText === false) {
            throw new RuntimeException('Token Google Calendar gagal didekripsi.');
        }

        return $plainText;
    }

    protected function binaryTokenKey(): string
    {
        if ($this->tokenKey === '') {
            throw new RuntimeException($this->getConfigurationMessage());
        }

        return hash('sha256', $this->tokenKey, true);
    }
}
