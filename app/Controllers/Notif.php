<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Repositories\NotificationRepository;
use App\Services\SiteplanUrgentService;

class Notif extends BaseController
{

    protected $db;
    protected $group_id;
    protected $siteplanUrgentService;
    protected NotificationRepository $notificationRepository;

    function __construct()
    {
        $this->db = db_connect();
        $this->siteplanUrgentService = new SiteplanUrgentService();
        $this->notificationRepository = new NotificationRepository($this->db);

        if (!session()->group_id) {
            $q = $this->db->table('auth_groups_users')
                ->select('group_id')
                ->where('user_id', user_id())
                ->get()->getRow();

            if ($q) {
                session()->set('group_id', $q->group_id);
            }
        }
        if(session()->group_id == 1)
            $this->group_id = "";
        else
            $this->group_id = session()->group_id;
    }
    function tambah_notif($target, $notif, $add_by, $id_kavling, $id_konsumen, $type = null, $id_proyek = null)
    {
        $notifService = new \App\Services\NotifikasiService();
        return $notifService->tambah_notif($target, $notif, $add_by, $id_kavling, $id_konsumen, $type, $id_proyek);
    }

    
    function getNotif($all = false){
        $r['token'] = csrf_hash();

        $offset = 0;

        if($all)
            $this->group_id = '';

        $r['notif'] = $this->sanitizeActivityItems($this->getActivity(false, $offset));
        
        // Dapatkan jumlah unread notifikasi
        $r['unread_count'] = $this->getUnreadActivityCount();

        return $this->response->setJSON($r);
    }

    function getSummary()
    {
        $idProyek = (int) ($this->request->getGet('id_proyek') ?: session()->get('id_proyek'));
        $activityUnreadCount = $this->getUnreadActivityCount($idProyek > 0 ? $idProyek : null);

        return $this->response->setJSON([
            'token' => csrf_hash(),
            'urgent_total' => 0,
            'activity_unread_count' => $activityUnreadCount,
            'badge_total' => $activityUnreadCount,
            'summary_only' => true,
        ]);
    }

    function getCenter()
    {
        $idProyek = (int) ($this->request->getGet('id_proyek') ?: session()->get('id_proyek'));
        $groupId = $this->getCurrentGroupId();
        $userId = function_exists('user_id') ? (int) user_id() : 0;
        $urgent = $idProyek > 0
            ? $this->siteplanUrgentService->getUrgentSummary($idProyek, $groupId, $userId)
            : $this->siteplanUrgentService->emptySummary();
        $activity = $this->sanitizeActivityItems($this->getActivity(false, 0, $idProyek > 0 ? $idProyek : null, 10));
        $activityUnreadCount = $this->getUnreadActivityCount($idProyek > 0 ? $idProyek : null);

        return $this->response->setJSON([
            'token' => csrf_hash(),
            'urgent_total' => (int) ($urgent['total'] ?? 0),
            'activity_unread_count' => $activityUnreadCount,
            'badge_total' => (int) ($urgent['total'] ?? 0) + $activityUnreadCount,
            'urgent' => $urgent,
            'activity' => [
                'items' => $activity,
            ],
        ]);
    }

    function snooze()
    {
        $idProyek = (int) ($this->request->getPost('id_proyek') ?: session()->get('id_proyek'));
        $itemKey = trim((string) $this->request->getPost('item_key'));
        $minutes = (int) $this->request->getPost('minutes');
        $allowedMinutes = [15, 60, 240];

        if ($idProyek <= 0 || $itemKey === '' || !in_array($minutes, $allowedMinutes, true)) {
            return $this->response->setStatusCode(422)->setJSON([
                'success' => false,
                'token' => csrf_hash(),
                'messages' => 'Data snooze tidak valid',
            ]);
        }

        $success = $this->siteplanUrgentService->snoozeUrgentItem((int) user_id(), $idProyek, $itemKey, $minutes);

        return $this->response->setJSON([
            'success' => $success,
            'token' => csrf_hash(),
            'messages' => $success ? 'Notifikasi urgent ditunda' : 'Gagal menunda notifikasi',
        ]);
    }

    function loadNotif($all = false)
    {
        $r['token'] = csrf_hash();
        $offset = $this->request->getVar('offset');
        $idProyek = (int) $this->request->getVar('id_proyek');

        if($all)
            $this->group_id = '';

        $r['notif'] = $this->sanitizeActivityItems($this->getActivity(false, $offset, $idProyek > 0 ? $idProyek : null));

        return $this->response->setJSON($r);
    }

    function getActivity($all = false, $offset = null, $id_proyek = null, $limit = 10){
        return $this->notificationRepository->listForUser(
            (int) user_id(),
            $this->getCurrentGroupId(),
            $id_proyek ? (int) $id_proyek : null,
            (int) ($offset ?? 0),
            (int) $limit,
            (bool) $all
        );
    }
    
    function markAsRead($id) {
        $success = $this->notificationRepository->markAsReadForUser((int) $id, (int) user_id());

        if (! $success) {
            return $this->response->setStatusCode(404)->setJSON([
                'status' => 'not_found',
                'token' => csrf_hash(),
            ]);
        }

        return $this->response->setJSON(['status' => 'success', 'token' => csrf_hash()]);
    }

    protected function getCurrentGroupId(): int
    {
        $groupId = (int) (session()->group_id ?? 0);
        if ($groupId > 0) {
            return $groupId;
        }

        $group = $this->db->table('auth_groups_users')
            ->select('group_id')
            ->where('user_id', user_id())
            ->get()
            ->getRow();
        $groupId = (int) ($group->group_id ?? 0);
        if ($groupId > 0) {
            session()->set('group_id', $groupId);
            $this->group_id = $groupId === 1 ? '' : $groupId;
        }

        return $groupId;
    }

    protected function getUnreadActivityCount($idProyek = null): int
    {
        return $this->notificationRepository->unreadCountForUser(
            (int) user_id(),
            $this->getCurrentGroupId(),
            $idProyek ? (int) $idProyek : null
        );
    }

    protected function sanitizeActivityItems(array $items): array
    {
        foreach ($items as $item) {
            if (is_object($item)) {
                $item->notif_text = $this->plainNotificationText($item->notif ?? '');
            } elseif (is_array($item)) {
                $item['notif_text'] = $this->plainNotificationText($item['notif'] ?? '');
            }
        }

        return $items;
    }

    protected function plainNotificationText($value): string
    {
        $decoded = html_entity_decode((string) $value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = trim(strip_tags($decoded));
        $text = preg_replace('/\s+/u', ' ', $text);

        return $text === '' ? '-' : $text;
    }

    protected function applyGroupTargetFilter($builder): void
    {
        if ($this->group_id === '' || $this->group_id === null) {
            return;
        }

        $role = (string) $this->group_id;
        $userId = function_exists('user_id') ? (int) user_id() : 0;
        
        $builder->groupStart()
            ->where('notification.group_target', $role)
            ->orLike('notification.group_target', $role . ';', 'after')
            ->orLike('notification.group_target', ';' . $role . ';', 'both')
            ->orLike('notification.group_target', ';' . $role, 'before')
            ->orWhere('notification.group_target', '0');
            
        if ($userId > 0) {
            $builder->orWhere('notification.user_id', $userId);
        }
        
        $builder->groupEnd();
    }
}
