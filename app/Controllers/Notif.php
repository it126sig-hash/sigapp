<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Services\SiteplanUrgentService;

class Notif extends BaseController
{

    protected $db;
    protected $group_id;
    protected $siteplanUrgentService;

    function __construct()
    {
        $this->db = db_connect();
        $this->siteplanUrgentService = new SiteplanUrgentService();

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
    function tambah_notif($target, $notif, $add_by, $id_kavling, $id_konsumen, $type = null)
    {
        if (is_array($target)) {
            $batchData = [];
            foreach ($target as $t) {
                $batchData[] = [
                    'notif' => $notif,
                    'group_target' => $t,
                    'add_by' => $add_by,
                    'id_kavling' => $id_kavling,
                    'id_konsumen' => $id_konsumen,
                    'type' => $type,
                    'is_read' => 0,
                    'created_at' => date('Y-m-d H:i:s')
                ];
            }
            return $this->db->table('notification')
                ->insertBatch($batchData);
        } else {
            $data = [
                'notif' => $notif,
                'group_target' => $target,
                'add_by' => $add_by,
                'id_kavling' => $id_kavling,
                'id_konsumen' => $id_konsumen,
                'type' => $type,
                'is_read' => 0,
                'created_at' => date('Y-m-d H:i:s')
            ];
            return $this->db->table('notification')
                ->insert($data);
        }
    }
    
    function getNotif($all = false){
        $r['token'] = csrf_hash();

        $offset = 0;

        if($all)
            $this->group_id = '';

        $r['notif'] = $this->getActivity(false, $offset);
        
        // Dapatkan jumlah unread notifikasi
        $r['unread_count'] = $this->getUnreadActivityCount();

        return $this->response->setJSON($r);
    }

    function getCenter()
    {
        $idProyek = (int) ($this->request->getGet('id_proyek') ?: session()->get('id_proyek'));
        $groupId = $this->getCurrentGroupId();
        $userId = function_exists('user_id') ? (int) user_id() : 0;
        $urgent = $idProyek > 0
            ? $this->siteplanUrgentService->getUrgentSummary($idProyek, $groupId, $userId)
            : $this->siteplanUrgentService->emptySummary();
        $activity = $this->getActivity(false, 0, $idProyek > 0 ? $idProyek : null, 10);
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

        $r['notif'] = $this->getActivity(false, $offset, $idProyek > 0 ? $idProyek : null);

        return $this->response->setJSON($r);
    }

    function getActivity($all = false, $offset = null, $id_proyek = null, $limit = 10){
        if($all)
            $this->group_id = '';
        $builder = $this->db->table('notification')
            ->select('notification.*, users.username, nama_jalan, no_kavling, proyek.id_proyek')
            ->join('users', 'users.id = notification.add_by')
            ->join('kavling', 'kavling.id_kavling = notification.id_kavling')
            ->join('jalan', 'jalan.id_jalan = kavling.id_jalan')
            ->join('cluster', 'jalan.id_cluster = cluster.id_cluster')
            ->join('proyek', 'proyek.id_proyek = cluster.id_proyek');

        if ($id_proyek) {
            $builder->where('proyek.id_proyek', (int) $id_proyek);
        }

        $this->applyGroupTargetFilter($builder);

        $q = $builder
            ->orderBy('is_read', 'asc') // Urutkan yang belum dibaca terlebih dahulu
            ->orderBy('created_at', 'desc')
            ->limit($limit, $offset) // Menampilkan 10 agar history lebih banyak
            ->get()->getResult();

        return $q;
    }
    
    function markAsRead($id) {
        $this->db->table('notification')
            ->where('id', $id)
            ->update(['is_read' => 1]);
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
        $builder = $this->db->table('notification')
            ->join('kavling', 'kavling.id_kavling = notification.id_kavling')
            ->join('jalan', 'jalan.id_jalan = kavling.id_jalan')
            ->join('cluster', 'jalan.id_cluster = cluster.id_cluster')
            ->join('proyek', 'proyek.id_proyek = cluster.id_proyek')
            ->where('notification.is_read', 0);

        if ($idProyek) {
            $builder->where('proyek.id_proyek', (int) $idProyek);
        }

        $this->applyGroupTargetFilter($builder);

        return (int) $builder->countAllResults();
    }

    protected function applyGroupTargetFilter($builder): void
    {
        if ($this->group_id === '' || $this->group_id === null) {
            return;
        }

        $role = (string) $this->group_id;
        $builder->groupStart()
            ->where('notification.group_target', $role)
            ->orLike('notification.group_target', $role . ';', 'after')
            ->orLike('notification.group_target', ';' . $role . ';', 'both')
            ->orLike('notification.group_target', ';' . $role, 'before')
            ->orWhere('notification.group_target', '0')
            ->groupEnd();
    }
}
