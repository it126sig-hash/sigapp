<?php

namespace App\Repositories;

use CodeIgniter\Model;

class NotifRepository extends Model
{
    protected $table = 'notification';
    protected $primaryKey = 'id';
    protected $returnType = 'object';

    function tambah_notif($target, $notif, $add_by, $id_kavling, $id_konsumen)
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
}
