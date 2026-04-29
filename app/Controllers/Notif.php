<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class Notif extends BaseController
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
        if ($this->group_id != '') {
            $r['unread_count'] = $this->db->table('notification')
                ->where('group_target', $this->group_id)
                ->where('is_read', 0)
                ->countAllResults();
        } else {
            $r['unread_count'] = 0;
        }

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
        $q = $this->db->table('notification')
        ->select('notification.*, users.username, nama_jalan, no_kavling,   ')
        ->join('users', 'users.id = notification.add_by')
        ->join('kavling', 'kavling.id_kavling = notification.id_kavling')
        ->join('jalan', 'jalan.id_jalan = kavling.id_jalan')
        ->join('cluster', 'jalan.id_cluster = cluster.id_cluster')
        ->join('proyek', 'proyek.id_proyek = cluster.id_proyek')
        ->like("proyek.id_proyek", ''.$id_proyek.'')
        ->like('group_target', $this->group_id)
            ->orderBy('is_read', 'asc') // Urutkan yang belum dibaca terlebih dahulu
            ->orderBy('created_at', 'desc')
            ->limit(10,$offset) // Menampilkan 10 agar history lebih banyak
            ->get()->getResult();

        return $q;
    }
    
    function markAsRead($id) {
        $this->db->table('notification')
            ->where('id', $id)
            ->update(['is_read' => 1]);
        return $this->response->setJSON(['status' => 'success', 'token' => csrf_hash()]);
    }
}
