<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddScopeToOthersTable extends Migration
{
    public function up()
    {
        if (!$this->db->fieldExists('scope', 'others')) {
            $this->forge->addColumn('others', [
                'scope' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 30,
                    'default'    => 'siteplan',
                    'after'      => 'tipe',
                ],
            ]);
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('scope', 'others')) {
            $this->forge->dropColumn('others', 'scope');
        }
    }
}
