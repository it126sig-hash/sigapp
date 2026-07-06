<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddKeteranganStatusToMkdtTable extends Migration
{
    public function up()
    {
        if (!$this->db->tableExists('mkdt') || $this->db->fieldExists('keterangan_status', 'mkdt')) {
            return;
        }

        $this->forge->addColumn('mkdt', [
            'keterangan_status' => [
                'type'       => 'TEXT',
                'null'       => true,
                'after'      => 'keterangan',
            ],
        ]);
    }

    public function down()
    {
        if ($this->db->tableExists('mkdt') && $this->db->fieldExists('keterangan_status', 'mkdt')) {
            $this->forge->dropColumn('mkdt', 'keterangan_status');
        }
    }
}
