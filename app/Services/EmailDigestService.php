<?php

namespace App\Services;

use App\Support\NotificationTextFormatter;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Email\Email;
use Config\Email as EmailConfig;

class EmailDigestService
{
    protected BaseConnection $db;
    protected Email $email;
    protected GoogleCalendarService $googleCalendarService;

    public function __construct(?BaseConnection $db = null, ?Email $email = null, ?GoogleCalendarService $googleCalendarService = null)
    {
        $this->db = $db ?? db_connect();

        $config = new EmailConfig();
        $this->email = $email ?? \Config\Services::email($config);
        $this->googleCalendarService = $googleCalendarService ?? new GoogleCalendarService();
    }

    public function processQueue(): array
    {
        if ($this->featureEnabled('NOTIF_EMAIL_USE_DELIVERY_OUTBOX', true) && $this->db->tableExists('notification_deliveries')) {
            $stats = $this->processDeliveryQueue();
            if (($stats['queues_seen'] ?? 0) > 0) {
                unset($stats['queues_seen']);
                return $stats;
            }
        }

        return $this->processLegacyQueue();
    }



    protected function processDeliveryQueue(): array
    {
        $rows = $this->db->table('notification_deliveries d')
            ->select('d.id as delivery_id, d.attempts, d.user_id as recipient_user_id')
            ->select('n.id as notification_id, n.notif, n.type, n.created_at as notif_date, n.add_by as actor_user_id')
            ->select('nr.read_at as recipient_read_at')
            ->select('COALESCE(p.id_proyek, pk.id_proyek) as id_proyek, COALESCE(p.nama_proyek, pk.nama_proyek) as nama_proyek, k.no_kavling')
            ->select('recipient.id as recipient_id, recipient.email, recipient.username, recipient.email_notif_enabled')
            ->select('u.name as actor_name, u.username as actor_username')
            ->select('MIN(ag.name) as departemen_name, MIN(ag.description) as departemen_desc', false)
            ->join('notification n', 'n.id = d.notification_id')
            ->join('notification_recipients nr', 'nr.id = d.notification_recipient_id AND nr.user_id = d.user_id')
            ->join('users recipient', 'recipient.id = d.user_id')
            ->where('d.available_at <=', date('Y-m-d H:i:s'))
            ->where('d.attempts <', 5)
            ->groupBy('d.id')
            ->orderBy('d.available_at', 'asc')
            ->orderBy('d.id', 'asc')
            ->limit(200)
            ->get()
            ->getResult();

        $stats = $this->emptyStats();
        $stats['queues_seen'] = count($rows);
        if ($rows === []) {
            return $stats;
        }

        $grouped = [];
        foreach ($rows as $row) {
            $grouped[(int) $row->recipient_user_id][] = $row;
        }

        foreach ($grouped as $userRows) {
            $readRows = array_values(array_filter($userRows, static fn ($item) => ! empty($item->recipient_read_at)));
            if ($readRows !== []) {
                $this->markDeliveries($this->deliveryIds($readRows), 'skipped', 'Notifikasi sudah dibaca sebelum email digest dikirim.');
                $stats['queues_skipped'] += count($readRows);
            }

            $userRows = array_values(array_filter($userRows, static fn ($item) => empty($item->recipient_read_at)));
            if ($userRows === []) {
                continue;
            }

            $user = (object) [
                'id' => $userRows[0]->recipient_id,
                'email' => $userRows[0]->email,
                'username' => $userRows[0]->username,
                'email_notif_enabled' => $userRows[0]->email_notif_enabled,
            ];

            if ((int) $user->email_notif_enabled === 0 || empty($user->email)) {
                $this->markDeliveries($this->deliveryIds($userRows), 'skipped', 'Email user kosong atau dinonaktifkan.');
                $stats['queues_skipped'] += count($userRows);
                continue;
            }

            $actorRows = array_values(array_filter($userRows, static fn ($item) => (int) $item->actor_user_id === (int) $user->id));
            if ($actorRows !== []) {
                $this->markDeliveries($this->deliveryIds($actorRows), 'skipped', 'Actor notifikasi tidak dikirim email ke diri sendiri.');
                $stats['queues_skipped'] += count($actorRows);
            }

            $items = array_values(array_filter($userRows, static fn ($item) => (int) $item->actor_user_id !== (int) $user->id));
            if ($items === []) {
                continue;
            }

            $sentAt = date('Y-m-d H:i:s');
            try {
                $sent = $this->sendDigestEmail($user, $this->groupItemsByProyek($items));
            } catch (\Throwable $e) {
                $sent = false;
                log_message('error', 'Email digest gagal untuk user ' . $user->id . ': ' . $e->getMessage());
            }

            if ($sent) {
                $this->markDeliveries($this->deliveryIds($items), 'sent');
                $stats['emails_sent']++;
                $stats['queues_sent'] += count($items);

                $calendarStats = $this->syncGoogleCalendarForUser($user, $items, $sentAt);
                $stats['calendar_created'] += $calendarStats['created'];
                $stats['calendar_skipped'] += $calendarStats['skipped'];
                $stats['calendar_failed'] += $calendarStats['failed'];
            } else {
                $this->retryOrFailDeliveries($items, 'Email digest gagal dikirim.');
                $stats['emails_failed']++;
                $stats['queues_failed'] += count($items);
            }
        }

        return $stats;
    }

    protected function processLegacyQueue(): array
    {
        $batchId = trim(sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        ));

        $stats = $this->emptyStats();
        $overlapIds = $this->skipLegacyRowsCoveredByOutbox($batchId);
        $stats['queues_skipped'] += count($overlapIds);

        $queues = $this->db->table('notification_email_queue q')
            ->select('q.*, n.notif, n.type, n.is_read as legacy_is_read, n.created_at as notif_date')
            ->select('COALESCE(p.id_proyek, pk.id_proyek) as id_proyek, COALESCE(p.nama_proyek, pk.nama_proyek) as nama_proyek, k.no_kavling')
            ->select('u.name as actor_name, u.username as actor_username')
            ->select('MIN(ag.name) as departemen_name, MIN(ag.description) as departemen_desc', false)
            ->join('notification n', 'n.id = q.notification_id')
            ->join('proyek p', 'p.id_proyek = n.id_proyek', 'left')
            ->join('kavling k', 'k.id_kavling = n.id_kavling', 'left')
            ->join('jalan j', 'j.id_jalan = k.id_jalan', 'left')
            ->join('cluster c', 'c.id_cluster = j.id_cluster', 'left')
            ->join('proyek pk', 'pk.id_proyek = c.id_proyek', 'left')
            ->join('users u', 'u.id = q.actor_user_id', 'left')
            ->join('auth_groups_users agu', 'agu.user_id = u.id', 'left')
            ->join('auth_groups ag', 'ag.id = agu.group_id', 'left')
            ->where('q.status', 'pending')
            ->groupBy('q.id')
            ->get()
            ->getResult();

        if (empty($queues)) {
            return $stats;
        }

        $userItemsMap = [];
        $allQueueIds = [];

        foreach ($queues as $q) {
            $allQueueIds[] = $q->id;
            $users = $this->getTargetUsers($q->target_group, $q->target_user_id);
            foreach ($users as $user) {
                if ((int) $user->email_notif_enabled === 0 || empty($user->email)) {
                    continue;
                }
                if ((int) $q->actor_user_id === (int) $user->id) {
                    continue;
                }
                if (!isset($userItemsMap[$user->id])) {
                    $userItemsMap[$user->id] = ['user' => $user, 'items' => []];
                }
                // Avoid duplicating the same notification for the same user if multiple queues overlap
                $exists = false;
                foreach ($userItemsMap[$user->id]['items'] as $existingItem) {
                    if ($existingItem->notification_id == $q->notification_id) {
                        $exists = true;
                        break;
                    }
                }
                if (!$exists) {
                    $userItemsMap[$user->id]['items'][] = $q;
                }
            }
        }

        $sentQueueIds = [];
        $failedQueueIds = [];

        foreach ($userItemsMap as $userId => $data) {
            $user = $data['user'];
            
            $userItems = $this->unreadLegacyItemsForUser($data['items'], $userId);
            if (empty($userItems)) {
                continue;
            }

            $userQueueIds = $this->legacyQueueIds($userItems);

            $sentAt = date('Y-m-d H:i:s');
            try {
                $sent = $this->sendDigestEmail($user, $this->groupItemsByProyek($userItems));
            } catch (\Throwable $e) {
                $sent = false;
                log_message('error', 'Email digest gagal untuk user ' . $user->id . ': ' . $e->getMessage());
            }

            if ($sent) {
                $stats['emails_sent']++;
                $sentQueueIds = array_merge($sentQueueIds, $userQueueIds);
                $calendarStats = $this->syncGoogleCalendarForUser($user, $userItems, $sentAt);
                $stats['calendar_created'] += $calendarStats['created'];
                $stats['calendar_skipped'] += $calendarStats['skipped'];
                $stats['calendar_failed'] += $calendarStats['failed'];
            } else {
                $stats['emails_failed']++;
                $failedQueueIds = array_merge($failedQueueIds, $userQueueIds);
            }
        }

        $failedQueueIds = array_values(array_unique(array_map('intval', $failedQueueIds)));
        $sentQueueIds = array_values(array_diff(
            array_unique(array_map('intval', $sentQueueIds)),
            $failedQueueIds
        ));
        $skippedQueueIds = array_values(array_diff(
            array_unique(array_map('intval', $allQueueIds)),
            $failedQueueIds,
            $sentQueueIds
        ));

        if ($failedQueueIds !== []) {
            $this->markLegacyQueues($failedQueueIds, 'failed', $batchId);
            $stats['queues_failed'] += count($failedQueueIds);
        }

        if ($sentQueueIds !== []) {
            $this->markLegacyQueues($sentQueueIds, 'sent', $batchId);
            $stats['queues_sent'] += count($sentQueueIds);
        }

        if ($skippedQueueIds !== []) {
            $this->markLegacyQueues($skippedQueueIds, 'skipped', $batchId);
            $stats['queues_skipped'] += count($skippedQueueIds);
        }

        return $stats;
    }

    protected function skipLegacyRowsCoveredByOutbox(string $batchId): array
    {
        if (! $this->db->tableExists('notification_deliveries')) {
            return [];
        }

        $rows = $this->db->table('notification_email_queue q')
            ->select('q.id')
            ->join('notification_deliveries d', 'd.notification_id = q.notification_id')
            ->where('q.status', 'pending')
            ->where('d.channel', 'email')
            ->groupBy('q.id')
            ->get()
            ->getResult();

        $ids = array_values(array_unique(array_map(static fn ($row) => (int) $row->id, $rows)));
        if ($ids !== []) {
            $this->markLegacyQueues($ids, 'skipped', $batchId);
        }

        return $ids;
    }

    protected function unreadLegacyItemsForUser(array $items, int $userId): array
    {
        if ($items === []) {
            return [];
        }

        if (! $this->db->tableExists('notification_recipients')) {
            return array_values(array_filter($items, static fn ($item) => (int) ($item->legacy_is_read ?? 0) === 0));
        }

        $notificationIds = array_values(array_unique(array_map(static fn ($item) => (int) $item->notification_id, $items)));
        $rows = $this->db->table('notification_recipients')
            ->select('notification_id, read_at')
            ->where('user_id', $userId)
            ->whereIn('notification_id', $notificationIds)
            ->get()
            ->getResult();

        $readState = [];
        foreach ($rows as $row) {
            $readState[(int) $row->notification_id] = $row->read_at;
        }

        return array_values(array_filter($items, static function ($item) use ($readState): bool {
            $notificationId = (int) $item->notification_id;

            return array_key_exists($notificationId, $readState) && empty($readState[$notificationId]);
        }));
    }

    protected function markLegacyQueues(array $ids, string $status, string $batchId): void
    {
        if ($ids === []) {
            return;
        }

        $this->db->table('notification_email_queue')
            ->whereIn('id', $ids)
            ->update([
                'status' => $status,
                'batch_id' => $batchId,
                'processed_at' => date('Y-m-d H:i:s'),
            ]);
    }

    protected function legacyQueueIds(array $items): array
    {
        return array_values(array_unique(array_map(static fn ($item) => (int) $item->id, $items)));
    }

    protected function getTargetUsers($groupId, $userId): array
    {
        $builder = $this->db->table('users')
            ->select('users.id, users.email, users.username, users.email_notif_enabled')
            ->where('users.active', 1)
            ->where('users.deleted_at IS NULL', null, false)
            ->where('users.email !=', '')
            ->where('users.email IS NOT NULL', null, false);

        if ($userId) {
            $builder->where('users.id', $userId);
        } elseif ($groupId) {
            $groups = array_values(array_filter(array_map('intval', explode(';', (string) $groupId))));
            if ($groups === []) {
                return [];
            }
            $builder->join('auth_groups_users agu', 'agu.user_id = users.id')
                ->whereIn('agu.group_id', $groups)
                ->groupBy('users.id');
        }

        return $builder->get()->getResult();
    }

    protected function sendDigestEmail($user, $items): bool
    {
        $html = view('emails/email_digest', [
            'user' => $user,
            'items' => $items,
        ]);

        $this->email->clear();
        $this->email->setTo($user->email);
        $this->email->setSubject('Rangkuman Notifikasi SIGAPP - ' . date('d M Y H:i'));
        $this->email->setMessage($html);
        $this->email->setMailType('html');

        return (bool) $this->email->send();
    }

    protected function syncGoogleCalendarForUser($user, array $items, string $sentAt): array
    {
        $stats = [
            'created' => 0,
            'skipped' => 0,
            'failed' => 0,
        ];

        foreach ($items as $item) {
            $calendarItem = clone $item;
            if (isset($calendarItem->delivery_id)) {
                $calendarItem->id = $calendarItem->delivery_id;
            }

            $result = $this->googleCalendarService->syncUrgentTicketFromNotificationItem($calendarItem, (int) $user->id, $sentAt);
            $status = $result['status'] ?? 'skipped';
            if ($status === 'created') {
                $stats['created']++;
            } elseif ($status === 'failed') {
                $stats['failed']++;
            } else {
                $stats['skipped']++;
            }
        }

        return $stats;
    }

    protected function groupItemsByProyek(array $items): array
    {
        $groupedByProyek = [];
        foreach ($items as $item) {
            $item->notif_text = NotificationTextFormatter::plain($item->notif ?? '');
            $proyekName = ! empty($item->nama_proyek) ? $item->nama_proyek : 'Umum / Lainnya';
            $groupedByProyek[$proyekName][] = $item;
        }

        return $groupedByProyek;
    }

    protected function markDeliveries(array $ids, string $status, ?string $error = null): void
    {
        if ($ids === []) {
            return;
        }

        $this->db->table('notification_deliveries')
            ->whereIn('id', $ids)
            ->update([
                'status' => $status,
                'processed_at' => date('Y-m-d H:i:s'),
                'last_error' => $error,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
    }

    protected function retryOrFailDeliveries(array $rows, string $error): void
    {
        foreach ($rows as $row) {
            $attempts = ((int) $row->attempts) + 1;
            $this->db->table('notification_deliveries')
                ->where('id', (int) $row->delivery_id)
                ->update([
                    'status' => 'failed',
                    'attempts' => $attempts,
                    'available_at' => $attempts < 5 ? date('Y-m-d H:i:s', time() + ($this->retryDelayMinutes($attempts) * 60)) : date('Y-m-d H:i:s'),
                    'processed_at' => $attempts >= 5 ? date('Y-m-d H:i:s') : null,
                    'last_error' => $error,
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
        }
    }

    protected function deliveryIds(array $rows): array
    {
        return array_values(array_unique(array_map(static fn ($row) => (int) $row->delivery_id, $rows)));
    }

    protected function retryDelayMinutes(int $attempts): int
    {
        return match ($attempts) {
            1 => 1,
            2 => 5,
            3 => 15,
            default => 60,
        };
    }

    protected function emptyStats(): array
    {
        return [
            'emails_sent' => 0,
            'emails_failed' => 0,
            'queues_sent' => 0,
            'queues_failed' => 0,
            'queues_skipped' => 0,
            'calendar_created' => 0,
            'calendar_skipped' => 0,
            'calendar_failed' => 0,
        ];
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
