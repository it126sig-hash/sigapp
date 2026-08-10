<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class InsertMasalahPriorityToConfigShape extends Migration
{
    public function up()
    {
        $data = [
            [
                'config_name' => 'Masalah Urgent',
                'fill'        => '#ea5455',
                'stroke'      => '#000000',
                'strokeWidth' => '0',
                'dashed'      => null,
                'keterangan'  => 'Masalah Urgent',
            ],
            [
                'config_name' => 'Masalah Medium',
                'fill'        => '#ff9f43',
                'stroke'      => '#000000',
                'strokeWidth' => '0',
                'dashed'      => null,
                'keterangan'  => 'Masalah Medium',
            ],
            [
                'config_name' => 'Masalah Normal',
                'fill'        => '#00cfe8',
                'stroke'      => '#000000',
                'strokeWidth' => '0',
                'dashed'      => null,
                'keterangan'  => 'Masalah Normal',
            ],
            [
                'config_name' => 'Masalah Low',
                'fill'        => '#28c76f',
                'stroke'      => '#000000',
                'strokeWidth' => '0',
                'dashed'      => null,
                'keterangan'  => 'Masalah Low',
            ],
        ];
        
        $this->db->table('config_shape')->insertBatch($data);
    }

    public function down()
    {
        $names = ['Masalah Urgent', 'Masalah Medium', 'Masalah Normal', 'Masalah Low'];
        $this->db->table('config_shape')->whereIn('config_name', $names)->delete();
    }
}
