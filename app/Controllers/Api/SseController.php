<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;

class SseController extends BaseController
{
    public function stream()
    {
        if (!function_exists('user_id') || !user_id()) {
            return $this->response->setStatusCode(401);
        }

        $userId = user_id();
        $db = db_connect();

        // Cari group id user
        $groupIdQuery = $db->table('auth_groups_users')
            ->select('group_id')
            ->where('user_id', $userId)
            ->get()
            ->getRow();
            
        $groupId = $groupIdQuery ? $groupIdQuery->group_id : '';
        if ($groupId == 1) $groupId = ''; // Admin melihat semua
        
        $session = session();
        $idProyek = (int) ($session->get('id_proyek') ?: 0);
        
        // Lepas lock session secepat mungkin agar request AJAX lain tidak terblokir
        session_write_close();

        // Header SSE
        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('Connection: keep-alive');
        header('X-Accel-Buffering: no'); // Nginx disable buffering

        // Get initial count for reference
        $lastCheckedId = $this->getLatestNotificationId($db, $userId, $groupId, $idProyek);
        $lastBadgeCount = $this->getBadgeCount($db, $userId, $groupId, $idProyek);

        // Initial push
        $this->sendSseEvent('badge', ['total' => $lastBadgeCount]);

        // Loop for up to 300 seconds (5 minutes) to avoid hanging processes forever
        $timeout = time() + 300;
        
        while (time() < $timeout) {
            // Check connection
            if (connection_aborted()) {
                break;
            }

            // Check new notifications
            $latestId = $this->getLatestNotificationId($db, $userId, $groupId, $idProyek);
            if ($latestId > $lastCheckedId) {
                // Fetch the new notification
                $newNotifs = $this->getNewNotifications($db, $userId, $groupId, $idProyek, $lastCheckedId);
                
                foreach ($newNotifs as $notif) {
                    $this->sendSseEvent('notification', [
                        'id' => $notif->id,
                        'notif' => $notif->notif,
                        'type' => $notif->type,
                        'is_read' => $notif->is_read
                    ]);
                }
                $lastCheckedId = $latestId;
                
                // Update badge
                $badgeCount = $this->getBadgeCount($db, $userId, $groupId, $idProyek);
                if ($badgeCount !== $lastBadgeCount) {
                    $this->sendSseEvent('badge', ['total' => $badgeCount]);
                    $lastBadgeCount = $badgeCount;
                }
            } else {
                // Still check badge in case someone else read it (count decreases)
                $badgeCount = $this->getBadgeCount($db, $userId, $groupId, $idProyek);
                if ($badgeCount !== $lastBadgeCount) {
                    $this->sendSseEvent('badge', ['total' => $badgeCount]);
                    $lastBadgeCount = $badgeCount;
                }
            }

            // Keep alive ping
            echo ": ping\n\n";
            ob_flush();
            flush();

            sleep(5); // Poll interval 5 seconds
        }
        
        exit();
    }

    private function sendSseEvent($event, $data)
    {
        echo "event: {$event}\n";
        echo "data: " . json_encode($data) . "\n\n";
        ob_flush();
        flush();
    }

    private function buildFilter($builder, $userId, $groupId, $idProyek)
    {
        if ($idProyek > 0) {
            // Using a simple where for simplicity in SSE polling
            // Note: complex joins might be slow in loop, keeping it simple if possible
            // But we need to check id_proyek which is via kavling->jalan->cluster
            // Since this runs in a loop, it's better if id_proyek is cached in notification table
        }
        
        if ($groupId !== '') {
            $builder->groupStart()
                ->where('group_target', (string)$groupId)
                ->orLike('group_target', $groupId . ';', 'after')
                ->orLike('group_target', ';' . $groupId . ';', 'both')
                ->orLike('group_target', ';' . $groupId, 'before')
                ->orWhere('group_target', '0')
                ->orWhere('user_id', $userId)
                ->groupEnd();
        } else {
            // For admin or missing group, still filter user_id if specific target
            $builder->groupStart()
                ->where('user_id', $userId)
                ->orWhere('user_id IS NULL')
                ->groupEnd();
        }
    }

    private function getLatestNotificationId($db, $userId, $groupId, $idProyek)
    {
        $builder = $db->table('notification')->selectMax('id');
        $this->buildFilter($builder, $userId, $groupId, $idProyek);
        $result = $builder->get()->getRow();
        return $result ? (int)$result->id : 0;
    }

    private function getNewNotifications($db, $userId, $groupId, $idProyek, $lastCheckedId)
    {
        $builder = $db->table('notification')
            ->select('id, notif, type, is_read, created_at')
            ->where('id >', $lastCheckedId);
        $this->buildFilter($builder, $userId, $groupId, $idProyek);
        return $builder->get()->getResult();
    }

    private function getBadgeCount($db, $userId, $groupId, $idProyek)
    {
        $builder = $db->table('notification')
            ->where('is_read', 0);
        $this->buildFilter($builder, $userId, $groupId, $idProyek);
        return $builder->countAllResults();
    }
}
