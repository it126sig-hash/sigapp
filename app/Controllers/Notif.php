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
    function tambah_notif($target = 0, $notif, $add_by, $id_kavling, $id_konsumen)
    {
        $data = [
            'notif' => $notif,
            'group_target' => $target,
            'add_by' => $add_by,
            'id_kavling' => $id_kavling,
            'id_konsumen' => $id_konsumen,
            'created_at' => date('Y-m-d H:i:s')
        ];
        return $this->db->table('notification')
            ->insert($data);
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

    function getActivity($all = false, $offset, $id_proyek = null){
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
            ->orderBy('created_at', 'desc')
            ->limit(5,$offset)
            ->get()->getResult();

        return $q;
    }
}
