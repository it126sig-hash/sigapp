<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddAkadIndentToMkdtTable extends Migration
{
    public function up()
    {
        if (!$this->db->tableExists('mkdt') || $this->db->fieldExists('akad_indent', 'mkdt')) {
            return;
        }

        $this->forge->addColumn('mkdt', [
            'akad_indent' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'after'      => 'akad_tgl',
            ],
        ]);
    }

    public function down()
    {
        if ($this->db->tableExists('mkdt') && $this->db->fieldExists('akad_indent', 'mkdt')) {
            $this->forge->dropColumn('mkdt', 'akad_indent');
        }
    }
}
