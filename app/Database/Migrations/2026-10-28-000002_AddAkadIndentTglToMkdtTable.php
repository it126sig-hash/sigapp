<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddAkadIndentTglToMkdtTable extends Migration
{
    public function up()
    {
        if (!$this->db->tableExists('mkdt') || $this->db->fieldExists('akad_indent_tgl', 'mkdt')) {
            return;
        }

        $this->forge->addColumn('mkdt', [
            'akad_indent_tgl' => [
                'type'    => 'DATE',
                'null'    => true,
                'default' => null,
                'after'   => 'akad_tgl',
            ],
        ]);
    }

    public function down()
    {
        if ($this->db->tableExists('mkdt') && $this->db->fieldExists('akad_indent_tgl', 'mkdt')) {
            $this->forge->dropColumn('mkdt', 'akad_indent_tgl');
        }
    }
}
