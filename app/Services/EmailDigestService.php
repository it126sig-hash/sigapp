<?php

namespace App\Services;

use Config\Email as EmailConfig;

class EmailDigestService
{
    protected $db;
    protected $email;
    protected GoogleCalendarService $googleCalendarService;

    public function __construct()
    {
        $this->db = db_connect();

        $config = new EmailConfig();
        $this->email = \Config\Services::email($config);
        $this->googleCalendarService = new GoogleCalendarService();
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
            ->select('p.nama_proyek, k.no_kavling')
            ->select('recipient.id as recipient_id, recipient.email, recipient.username, recipient.email_notif_enabled')
            ->select('u.name as actor_name, u.username as actor_username')
            ->select('MIN(ag.name) as departemen_name, MIN(ag.description) as departemen_desc', false)
            ->join('notification n', 'n.id = d.notification_id')
            ->join('users recipient', 'recipient.id = d.user_id')
            ->join('proyek p', 'p.id_proyek = n.id_proyek', 'left')
            ->join('kavling k', 'k.id_kavling = n.id_kavling', 'left')
            ->join('users u', 'u.id = n.add_by', 'left')
            ->join('auth_groups_users agu', 'agu.user_id = u.id', 'left')
            ->join('auth_groups ag', 'ag.id = agu.group_id', 'left')
            ->where('d.channel', 'email')
            ->whereIn('d.status', ['pending', 'failed'])
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

            $items = array_values(array_filter($userRows, static fn ($item) => (int) $item->actor_user_id !== (int) $user->id));
            if ($items === []) {
                $this->markDeliveries($this->deliveryIds($userRows), 'skipped', 'Actor notifikasi tidak dikirim email ke diri sendiri.');
                $stats['queues_skipped'] += count($userRows);
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

                $skippedActorRows = array_udiff($userRows, $items, static fn ($a, $b) => ((int) $a->delivery_id) <=> ((int) $b->delivery_id));
                if ($skippedActorRows !== []) {
                    $this->markDeliveries($this->deliveryIds($skippedActorRows), 'skipped', 'Actor notifikasi tidak dikirim email ke diri sendiri.');
                    $stats['queues_skipped'] += count($skippedActorRows);
                }
            } else {
                $this->retryOrFailDeliveries($userRows, 'Email digest gagal dikirim.');
                $stats['emails_failed']++;
                $stats['queues_failed'] += count($userRows);
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

        $queues = $this->db->table('notification_email_queue q')
            ->select('q.*, n.notif, n.type, n.created_at as notif_date')
            ->select('p.nama_proyek, k.no_kavling')
            ->select('u.name as actor_name, u.username as actor_username')
            ->select('MIN(ag.name) as departemen_name, MIN(ag.description) as departemen_desc', false)
            ->join('notification n', 'n.id = q.notification_id')
            ->join('proyek p', 'p.id_proyek = n.id_proyek', 'left')
            ->join('kavling k', 'k.id_kavling = n.id_kavling', 'left')
            ->join('users u', 'u.id = q.actor_user_id', 'left')
            ->join('auth_groups_users agu', 'agu.user_id = u.id', 'left')
            ->join('auth_groups ag', 'ag.id = agu.group_id', 'left')
            ->where('q.status', 'pending')
            ->groupBy('q.id')
            ->get()
            ->getResult();

        $stats = $this->emptyStats();
        if (empty($queues)) {
            return $stats;
        }

        $grouped = [];
        foreach ($queues as $q) {
            $key = ($q->target_group ?: 'all') . '-' . ($q->target_user_id ?: 'all');
            if (! isset($grouped[$key])) {
                $grouped[$key] = [
                    'target_group' => $q->target_group,
                    'target_user_id' => $q->target_user_id,
                    'items' => [],
                    'queue_ids' => [],
                ];
            }
            $grouped[$key]['items'][] = $q;
            $grouped[$key]['queue_ids'][] = $q->id;
        }

        foreach ($grouped as $group) {
            $users = $this->getTargetUsers($group['target_group'], $group['target_user_id']);
            $sentQueueIds = [];
            $failedQueueIds = [];

            foreach ($users as $user) {
                if ((int) $user->email_notif_enabled === 0 || empty($user->email)) {
                    continue;
                }

                $userItems = array_filter($group['items'], static fn ($item) => (int) $item->actor_user_id !== (int) $user->id);
                if (empty($userItems)) {
                    continue;
                }

                $sentAt = date('Y-m-d H:i:s');
                try {
                    $sent = $this->sendDigestEmail($user, $this->groupItemsByProyek($userItems));
                } catch (\Throwable $e) {
                    $sent = false;
                    log_message('error', 'Email digest gagal untuk user ' . $user->id . ': ' . $e->getMessage());
                }

                if ($sent) {
                    $stats['emails_sent']++;
                    $sentQueueIds = array_merge($sentQueueIds, $group['queue_ids']);
                    $calendarStats = $this->syncGoogleCalendarForUser($user, $userItems, $sentAt);
                    $stats['calendar_created'] += $calendarStats['created'];
                    $stats['calendar_skipped'] += $calendarStats['skipped'];
                    $stats['calendar_failed'] += $calendarStats['failed'];
                } else {
                    $stats['emails_failed']++;
                    $failedQueueIds = array_merge($failedQueueIds, $group['queue_ids']);
                }
            }

            if ($failedQueueIds !== []) {
                $this->db->table('notification_email_queue')
                    ->whereIn('id', array_unique($failedQueueIds))
                    ->update([
                        'status' => 'failed',
                        'batch_id' => $batchId,
                        'processed_at' => date('Y-m-d H:i:s'),
                    ]);
                $stats['queues_failed'] += count(array_unique($failedQueueIds));
                continue;
            }

            if ($sentQueueIds !== []) {
                $this->db->table('notification_email_queue')
                    ->whereIn('id', array_unique($sentQueueIds))
                    ->update([
                        'status' => 'sent',
                        'batch_id' => $batchId,
                        'processed_at' => date('Y-m-d H:i:s'),
                    ]);
                $stats['queues_sent'] += count(array_unique($sentQueueIds));
            }
        }

        return $stats;
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
