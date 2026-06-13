<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPerluRefundToMkdtTable extends Migration
{
    public function up()
    {
        if (!$this->db->tableExists('mkdt') || $this->db->fieldExists('perlu_refund', 'mkdt')) {
            return;
        }

        $this->forge->addColumn('mkdt', [
            'perlu_refund' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'after'      => 'keterangan_batal',
            ],
        ]);
    }

    public function down()
    {
        if ($this->db->tableExists('mkdt') && $this->db->fieldExists('perlu_refund', 'mkdt')) {
            $this->forge->dropColumn('mkdt', 'perlu_refund');
        }
    }
}
