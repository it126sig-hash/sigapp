<?php

namespace App\Services;

use CodeIgniter\Email\Email;
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

    public function processQueue()
    {
        $batchId = trim(sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        ));

        // Ambil semua queue yang pending dengan join ke proyek, kavling, users, dan auth_groups
        $queues = $this->db->table('notification_email_queue q')
            ->select('q.*, n.notif, n.type, n.created_at as notif_date')
            ->select('p.nama_proyek, k.no_kavling')
            ->select('u.name as actor_name, u.username as actor_username')
            ->select('ag.name as departemen_name, ag.description as departemen_desc')
            ->join('notification n', 'n.id = q.notification_id')
            ->join('proyek p', 'p.id_proyek = n.id_proyek', 'left')
            ->join('kavling k', 'k.id_kavling = n.id_kavling', 'left')
            ->join('users u', 'u.id = q.actor_user_id', 'left')
            ->join('auth_groups_users agu', 'agu.user_id = u.id', 'left')
            ->join('auth_groups ag', 'ag.id = agu.group_id', 'left')
            ->where('q.status', 'pending')
            ->groupBy('q.id') // Cegah duplikasi jika user actor memiliki lebih dari satu grup
            ->get()->getResult();

        if (empty($queues)) {
            return [
                'emails_sent' => 0,
                'emails_failed' => 0,
                'queues_sent' => 0,
                'queues_failed' => 0,
                'calendar_created' => 0,
                'calendar_skipped' => 0,
                'calendar_failed' => 0,
            ];
        }

        // Group by target
        $grouped = [];
        foreach ($queues as $q) {
            $key = ($q->target_group ?: 'all') . '-' . ($q->target_user_id ?: 'all');
            if (!isset($grouped[$key])) {
                $grouped[$key] = [
                    'target_group' => $q->target_group,
                    'target_user_id' => $q->target_user_id,
                    'actor_ids' => [],
                    'items' => [],
                    'queue_ids' => []
                ];
            }
            $grouped[$key]['items'][] = $q;
            $grouped[$key]['actor_ids'][] = $q->actor_user_id;
            $grouped[$key]['queue_ids'][] = $q->id;
        }

        $stats = [
            'emails_sent' => 0,
            'emails_failed' => 0,
            'queues_sent' => 0,
            'queues_failed' => 0,
            'calendar_created' => 0,
            'calendar_skipped' => 0,
            'calendar_failed' => 0,
        ];

        foreach ($grouped as $group) {
            $users = $this->getTargetUsers($group['target_group'], $group['target_user_id']);
            $failedUsers = 0;
            
            foreach ($users as $user) {
                // Skip if user was the actor for all of these notifications
                // Or if user disabled email notif
                if ($user->email_notif_enabled == 0 || empty($user->email)) {
                    continue;
                }

                // Filter items where user is NOT the actor
                $userItems = array_filter($group['items'], function($item) use ($user) {
                    return $item->actor_user_id != $user->id;
                });

                if (empty($userItems)) {
                    continue;
                }

                // Kelompokkan per proyek sebelum dikirim ke template email
                $groupedByProyek = [];
                foreach ($userItems as $item) {
                    $proyekName = !empty($item->nama_proyek) ? $item->nama_proyek : 'Umum / Lainnya';
                    if (!isset($groupedByProyek[$proyekName])) {
                        $groupedByProyek[$proyekName] = [];
                    }
                    $groupedByProyek[$proyekName][] = $item;
                }

                $sentAt = date('Y-m-d H:i:s');
                try {
                    $sent = $this->sendDigestEmail($user, $groupedByProyek);
                } catch (\Throwable $e) {
                    $sent = false;
                    log_message('error', 'Email digest gagal untuk user ' . $user->id . ': ' . $e->getMessage());
                }
                if ($sent) {
                    $stats['emails_sent']++;
                    $calendarStats = $this->syncGoogleCalendarForUser($user, $userItems, $sentAt);
                    $stats['calendar_created'] += $calendarStats['created'];
                    $stats['calendar_skipped'] += $calendarStats['skipped'];
                    $stats['calendar_failed'] += $calendarStats['failed'];
                } else {
                    $failedUsers++;
                    $stats['emails_failed']++;
                }
            }

            $queueStatus = $failedUsers > 0 ? 'failed' : 'sent';
            $this->db->table('notification_email_queue')
                ->whereIn('id', $group['queue_ids'])
                ->update([
                    'status' => $queueStatus,
                    'batch_id' => $batchId,
                    'processed_at' => date('Y-m-d H:i:s')
                ]);

            if ($queueStatus === 'sent') {
                $stats['queues_sent'] += count($group['queue_ids']);
            } else {
                $stats['queues_failed'] += count($group['queue_ids']);
                log_message('error', 'Email digest gagal untuk sebagian user. Queue tidak ditandai sent: ' . implode(',', $group['queue_ids']));
            }
        }

        return $stats;
    }

    protected function getTargetUsers($groupId, $userId)
    {
        $builder = $this->db->table('users')
            ->select('users.id, users.email, users.username, users.email_notif_enabled')
            ->where('users.active', 1)
            ->where('users.deleted_at IS NULL')
            ->where('users.email !=', '')
            ->where('users.email IS NOT NULL');

        if ($userId) {
            $builder->where('users.id', $userId);
        } else if ($groupId) {
            // Find users in this group
            // Note: target_group can be like "3;4;9"
            $groups = explode(';', $groupId);
            $builder->join('auth_groups_users agu', 'agu.user_id = users.id')
                    ->whereIn('agu.group_id', $groups)
                    ->groupBy('users.id');
        }

        return $builder->get()->getResult();
    }

    protected function sendDigestEmail($user, $items)
    {
        $html = view('emails/email_digest', [
            'user' => $user,
            'items' => $items
        ]);

        $this->email->clear();
        $this->email->setTo($user->email);
        $this->email->setSubject('Rangkuman Notifikasi SIGAPP - ' . date('d M Y H:i'));
        $this->email->setMessage($html);
        $this->email->setMailType('html');

        return $this->email->send();
    }

    protected function syncGoogleCalendarForUser($user, array $items, string $sentAt): array
    {
        $stats = [
            'created' => 0,
            'skipped' => 0,
            'failed' => 0,
        ];

        foreach ($items as $item) {
            $result = $this->googleCalendarService->syncUrgentTicketFromNotificationItem($item, (int) $user->id, $sentAt);
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
}
