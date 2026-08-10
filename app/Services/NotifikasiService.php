<?php

namespace App\Services;

class NotifikasiService
{
    protected $db;
    protected $group_id;

    function __construct()
    {
        $this->db = db_connect();

        if (!session()->group_id) {
            $q = $this->db->table('auth_groups_users')
                ->select('group_id')
                ->where('user_id', user_id())
                ->get()->getRow();

            session()->set('group_id', $q->group_id);
        }
        if(session()->group_id == 1)
            $this->group_id = "";
        else
            $this->group_id = session()->group_id;
    }
    function tambah_notif($target, $notif, $add_by, $id_kavling, $id_konsumen, $type = null, $id_proyek = null)
    {
        if (is_array($id_kavling)) {
            $id_kavling = $id_kavling[0] ?? null;
        }
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
                    'id_proyek' => $id_proyek,
                    'is_read' => 0,
                    'created_at' => date('Y-m-d H:i:s')
                ];
            }
            $this->db->table('notification')->insertBatch($batchData);
            $insertId = $this->db->insertID(); // Approximate, we will use it for triggers
            
            foreach ($target as $t) {
                $this->triggerNotifSideEffects($insertId, $t, null, $notif, $add_by);
            }
            return $insertId;
        } else {
            $data = [
                'notif' => $notif,
                'group_target' => $target,
                'type' => $type,
                'is_read' => 0,
                'add_by' => $add_by,
                'id_kavling' => $id_kavling,
                'id_konsumen' => $id_konsumen,
                'id_proyek' => $id_proyek,
                'created_at' => date('Y-m-d H:i:s')
            ];
            $this->db->table('notification')->insert($data);
            $insertId = $this->db->insertID();
            
            $this->triggerNotifSideEffects($insertId, $target, null, $notif, $add_by);
            return $insertId;
        }
    }

    function tambah_notif_user($user_id, $notif, $add_by, $id_kavling, $id_konsumen, $type = null, $id_proyek = null)
    {
        $data = [
            'notif' => $notif,
            'user_id' => $user_id,
            'group_target' => null,
            'type' => $type,
            'is_read' => 0,
            'add_by' => $add_by,
            'id_kavling' => $id_kavling,
            'id_konsumen' => $id_konsumen,
            'id_proyek' => $id_proyek,
            'created_at' => date('Y-m-d H:i:s')
        ];
        $this->db->table('notification')->insert($data);
        $insertId = $this->db->insertID();
        
        $this->triggerNotifSideEffects($insertId, null, $user_id, $notif, $add_by);
        return $insertId;
    }
    
    protected function triggerNotifSideEffects($notificationId, $targetGroup, $targetUser, $notifMsg, $actorId)
    {
        // 1. Queue email
        $this->db->table('notification_email_queue')->insert([
            'notification_id' => $notificationId,
            'target_group' => $targetGroup,
            'target_user_id' => $targetUser,
            'actor_user_id' => $actorId,
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
        // 2. Web Push Notification
        // Panggil push service secara async (bisa blocking dikit, sebaiknya dipisah ke job queue di masa depan)
        try {
            $pushService = new \App\Services\WebPushService();
            if ($targetUser) {
                $pushService->sendToUser($targetUser, 'SIGAPP', $notifMsg);
            } else if ($targetGroup) {
                $pushService->sendToGroup($targetGroup, 'SIGAPP', $notifMsg, '/', $actorId);
            }
        } catch (\Exception $e) {
            log_message('error', 'Push Notif Error: ' . $e->getMessage());
        }
    }

    function getNotif($all = false){
        $r['token'] = csrf_hash();

        $offset = 0;

        if($all)
            $this->group_id = '';

        $r['notif'] = $this->getActivity(false, $offset);

        return $this->response->setJSON($r);
    }

    function loadNotif($all = false)
    {
        $r['token'] = csrf_hash();
        $offset = $this->request->getVar('offset');

        if($all)
            $this->group_id = '';

        $r['notif'] = $this->getActivity(false, $offset);

        return $this->response->setJSON($r);
    }

    function getActivity($all = false, $offset = null, $id_proyek = null){
        if($all)
            $this->group_id = '';

        $builder = $this->db->table('notification')
            ->select('notification.*, users.username, nama_jalan, no_kavling,   ')
            ->join('users', 'users.id = notification.add_by')
            ->join('kavling', 'kavling.id_kavling = notification.id_kavling', 'left')
            ->join('jalan', 'jalan.id_jalan = kavling.id_jalan', 'left')
            ->join('cluster', 'jalan.id_cluster = cluster.id_cluster', 'left')
            ->join('proyek', 'proyek.id_proyek = cluster.id_proyek', 'left');

        if ($id_proyek) {
            $builder->groupStart()
                ->like('proyek.id_proyek', ''.$id_proyek.'')
                ->orWhere('notification.id_kavling IS NULL')
            ->groupEnd();
        }

        return $builder->groupStart()
                ->like('group_target', $this->group_id)
                ->orWhere('user_id', user_id())
            ->groupEnd()
            ->orderBy('created_at', 'desc')
            ->limit(5, $offset)
            ->get()->getResult();
    }
}
