<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPpnPphSettingDataSupport extends Migration
{
    public function up()
    {
        foreach (['ppn', 'pph'] as $table) {
            if (!$this->db->tableExists($table)) {
                continue;
            }

            $fields = $this->db->getFieldNames($table);
            if (in_array('deleted_at', $fields, true)) {
                continue;
            }

            $this->forge->addColumn($table, [
                'deleted_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
            ]);
        }
    }

    public function down()
    {
        foreach (['ppn', 'pph'] as $table) {
            if (!$this->db->tableExists($table)) {
                continue;
            }

            $fields = $this->db->getFieldNames($table);
            if (!in_array('deleted_at', $fields, true)) {
                continue;
            }

            $this->forge->dropColumn($table, 'deleted_at');
        }
    }
}
