<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddVoidFieldsToKeuanganTable extends Migration
{
    public function up()
    {
        if (!$this->db->tableExists('keuangan') || $this->db->fieldExists('is_void', 'keuangan')) {
            return;
        }

        $this->forge->addColumn('keuangan', [
            'is_void' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'null'       => false,
                'after'      => 'status',
            ],
            'void_reason' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'after'      => 'is_void',
            ],
        ]);
    }

    public function down()
    {
        if ($this->db->tableExists('keuangan') && $this->db->fieldExists('is_void', 'keuangan')) {
            $this->forge->dropColumn('keuangan', ['is_void', 'void_reason']);
        }
    }
}
