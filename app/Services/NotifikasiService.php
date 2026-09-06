<?php

namespace App\Services;

use App\Data\NotificationAudience;
use App\Data\NotificationData;
use App\Repositories\NotificationRepository;
use CodeIgniter\Database\BaseConnection;
use RuntimeException;

class NotifikasiService
{
    protected BaseConnection $db;
    protected NotificationRepository $notificationRepository;
    protected NotificationPreferenceService $preferenceService;

    public function __construct()
    {
        $this->db = db_connect();
        $this->notificationRepository = new NotificationRepository($this->db);
        $this->preferenceService = new NotificationPreferenceService();
    }

    public function create(NotificationData $data, NotificationAudience $audience): int
    {
        $now = date('Y-m-d H:i:s');
        $idKavling = is_array($data->idKavling) ? ($data->idKavling[0] ?? null) : $data->idKavling;

        // Validasi: actionUrl tidak boleh eksternal
        $actionUrl = $data->actionUrl;
        if ($actionUrl && preg_match('/^https?:\/\//i', $actionUrl)) {
            $actionUrl = null;
        }

        $this->db->transStart();

        $this->db->table('notification')->insert([
            'notif' => $data->message,
            'group_target' => $audience->legacyGroupTarget(),
            'user_id' => $audience->legacyUserId(),
            'type' => $data->type,
            'is_read' => 0,
            'add_by' => $data->actorUserId,
            'id_kavling' => $idKavling,
            'id_konsumen' => $data->idKonsumen,
            'id_proyek' => $data->idProyek,
            'action_url' => $actionUrl,
            'created_at' => $now,
        ]);

        $notificationId = (int) $this->db->insertID();
        if ($notificationId <= 0) {
            $this->db->transRollback();
            throw new RuntimeException('Gagal membuat notifikasi.');
        }

        $recipientRows = [];
        $recipientUserIds = $this->resolveRecipientIds($audience, $data->actorUserId);
        $eventType = $data->eventType ?? $data->type;

        if ($this->featureEnabled('NOTIF_DUAL_WRITE_RECIPIENTS', true)) {
            $recipientRows = $this->storeRecipients($notificationId, $recipientUserIds, $data->actorUserId, $eventType, $now);
        }
        if ($this->featureEnabled('NOTIF_WRITE_DELIVERY_OUTBOX', true)) {
            $this->storeDeliveries($notificationId, $recipientRows, $data->actorUserId, $eventType, $now);
        }
        if ($this->featureEnabled('NOTIF_WRITE_LEGACY_EMAIL_QUEUE', true)) {
            $this->storeLegacyEmailQueue($notificationId, $audience, $data->actorUserId, $now);
        }

        $this->db->transComplete();
        if ($this->db->transStatus() === false) {
            throw new RuntimeException('Gagal menyimpan notifikasi dan delivery outbox.');
        }

        return $notificationId;
    }

    public function tambah_notif($target, $notif, $add_by, $id_kavling, $id_konsumen, $type = null, $id_proyek = null, ?string $actionUrl = null, ?string $eventType = null)
    {
        $audience = NotificationAudience::fromLegacyTarget($target);
        $data = new NotificationData(
            message: $notif,
            actorUserId: $add_by,
            idKavling: $id_kavling,
            idKonsumen: $id_konsumen,
            type: $type,
            idProyek: $id_proyek,
            actionUrl: $actionUrl,
            eventType: $eventType
        );
        return $this->create($data, $audience);
    }

    public function tambah_notif_user($user_id, $notif, $add_by, $id_kavling, $id_konsumen, $type = null, $id_proyek = null, ?string $actionUrl = null, ?string $eventType = null)
    {
        $audience = NotificationAudience::forUser($user_id);
        $data = new NotificationData(
            message: $notif,
            actorUserId: $add_by,
            idKavling: $id_kavling,
            idKonsumen: $id_konsumen,
            type: $type,
            idProyek: $id_proyek,
            actionUrl: $actionUrl,
            eventType: $eventType
        );
        return $this->create($data, $audience);
    }

    public function getActivity($all = false, $offset = null, $id_proyek = null, $limit = 10): array
    {
        $userId = function_exists('user_id') ? (int) user_id() : 0;
        if ($userId <= 0) {
            return [];
        }

        return $this->notificationRepository->listForUser(
            $userId,
            $this->currentGroupId($userId),
            $id_proyek ? (int) $id_proyek : null,
            (int) ($offset ?? 0),
            (int) $limit,
            (bool) $all
        );
    }

    protected function resolveRecipientIds(NotificationAudience $audience, int $actorUserId): array
    {
        $recipients = [];

        if ($audience->isGlobal()) {
            foreach ($this->activeUsersQuery()->get()->getResult() as $user) {
                $recipients[(int) $user->id] = (int) $user->id;
            }
        }

        if ($audience->groupIds() !== []) {
            $rows = $this->activeUsersQuery()
                ->join('auth_groups_users agu', 'agu.user_id = users.id')
                ->whereIn('agu.group_id', $audience->groupIds())
                ->groupBy('users.id')
                ->get()
                ->getResult();

            foreach ($rows as $user) {
                $recipients[(int) $user->id] = (int) $user->id;
            }
        }

        if ($audience->userIds() !== []) {
            foreach ($audience->userIds() as $userId) {
                if ($this->isActiveUser((int) $userId)) {
                    $recipients[(int) $userId] = (int) $userId;
                }
            }
        }

        foreach ($this->adminUserIds() as $adminId) {
            $recipients[$adminId] = $adminId;
        }

        if ($actorUserId > 0 && $this->isActiveUser($actorUserId)) {
            $recipients[$actorUserId] = $actorUserId;
        }

        ksort($recipients);

        return array_values($recipients);
    }

    protected function storeRecipients(int $notificationId, array $userIds, int $actorUserId, ?string $eventType, string $now): array
    {
        if (! $this->db->tableExists('notification_recipients')) {
            return [];
        }

        $rows = [];
        foreach ($userIds as $userId) {
            $isActor = ($userId === $actorUserId);
            $readAt = null;

            if ($isActor) {
                $readAt = $now;
            } elseif ($eventType !== null && !$this->preferenceService->isAllowed($userId, $eventType, 'in_app')) {
                // Jika user mematikan in_app notif untuk event ini, tandai sebagai sudah dibaca
                // agar tidak muncul di unread badge, tapi tetap ter-record.
                $readAt = $now;
            }

            $existing = $this->db->table('notification_recipients')
                ->select('id')
                ->where('notification_id', $notificationId)
                ->where('user_id', $userId)
                ->get()
                ->getRow();

            if (! $existing) {
                $this->db->table('notification_recipients')->insert([
                    'notification_id' => $notificationId,
                    'user_id' => $userId,
                    'read_at' => $readAt,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
                $recipientId = (int) $this->db->insertID();
            } else {
                $recipientId = (int) $existing->id;
            }

            if ($recipientId > 0) {
                $rows[] = ['id' => $recipientId, 'user_id' => (int) $userId];
            }
        }

        return $rows;
    }

    protected function storeDeliveries(int $notificationId, array $recipientRows, int $actorUserId, ?string $eventType, string $now): void
    {
        if (! $this->db->tableExists('notification_deliveries') || $recipientRows === []) {
            return;
        }

        foreach ($recipientRows as $recipient) {
            foreach (['email', 'web_push'] as $channel) {
                $status = ((int) $recipient['user_id'] === $actorUserId) ? 'skipped' : 'pending';
                $lastError = $status === 'skipped' ? 'Actor notifikasi tidak dikirim ke channel delivery sendiri.' : null;

                // Cek preference
                if ($status === 'pending' && $eventType !== null) {
                    if (!$this->preferenceService->isAllowed((int) $recipient['user_id'], $eventType, $channel)) {
                        $status = 'preference_blocked';
                        $lastError = 'Notifikasi ini diblokir oleh preferensi user.';
                    }
                }

                $this->insertDeliveryIfMissing(
                    (int) $recipient['id'],
                    $notificationId,
                    (int) $recipient['user_id'],
                    $channel,
                    $status,
                    $lastError,
                    $now
                );
            }
        }
    }

    protected function insertDeliveryIfMissing(int $recipientId, int $notificationId, int $userId, string $channel, string $status, ?string $lastError, string $now): void
    {
        $exists = $this->db->table('notification_deliveries')
            ->where('notification_recipient_id', $recipientId)
            ->where('channel', $channel)
            ->countAllResults();

        if ($exists > 0) {
            return;
        }

        $this->db->table('notification_deliveries')->insert([
            'notification_recipient_id' => $recipientId,
            'notification_id' => $notificationId,
            'user_id' => $userId,
            'channel' => $channel,
            'status' => $status,
            'attempts' => 0,
            'available_at' => $now,
            'processed_at' => $status === 'skipped' ? $now : null,
            'last_error' => $lastError,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    protected function storeLegacyEmailQueue(int $notificationId, NotificationAudience $audience, int $actorUserId, string $now): void
    {
        if (! $this->db->tableExists('notification_email_queue')) {
            return;
        }

        $this->db->table('notification_email_queue')->insert([
            'notification_id' => $notificationId,
            'target_group' => $audience->legacyGroupTarget(),
            'target_user_id' => $audience->legacyUserId(),
            'actor_user_id' => $actorUserId,
            'status' => 'pending',
            'created_at' => $now,
        ]);
    }

    protected function activeUsersQuery()
    {
        return $this->db->table('users')
            ->select('users.id')
            ->where('users.active', 1)
            ->where('users.deleted_at IS NULL', null, false);
    }

    protected function isActiveUser(int $userId): bool
    {
        if ($userId <= 0) {
            return false;
        }

        return (bool) $this->activeUsersQuery()
            ->where('users.id', $userId)
            ->countAllResults();
    }

    protected function adminUserIds(): array
    {
        $rows = $this->activeUsersQuery()
            ->join('auth_groups_users agu', 'agu.user_id = users.id')
            ->where('agu.group_id', 1)
            ->groupBy('users.id')
            ->get()
            ->getResult();

        return array_values(array_unique(array_map(static fn ($row) => (int) $row->id, $rows)));
    }

    protected function currentGroupId(int $userId): int
    {
        $row = $this->db->table('auth_groups_users')
            ->select('group_id')
            ->where('user_id', $userId)
            ->get()
            ->getRow();

        return (int) ($row->group_id ?? 0);
    }

    protected function featureEnabled(string $key, bool $default): bool
    {
        $value = getenv($key);
        if ($value === false || $value === '') {
            return $default;
        }

        return in_array(strtolower((string) $value), ['1', 'true', 'yes', 'on'], true);
    }
}
