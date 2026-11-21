<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddNoNupColumnToMkdt extends Migration
{
    public function up()
    {
        if (! $this->db->fieldExists('no_nup', 'mkdt')) {
            $this->forge->addColumn('mkdt', [
                'no_nup' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 50,
                    'null'       => true,
                    'after'      => 'id_mkdt',
                ],
            ]);
        }

        // Backfill existing NUP records in mkdt with standard NUP numbering format
        $this->db->query("
            UPDATE mkdt
            SET no_nup = CONCAT('NUP-', LPAD(id_mkdt, 3, '0'))
            WHERE status_mkdt = 'NUP' AND (no_nup IS NULL OR no_nup = '')
        ");
    }

    public function down()
    {
        if ($this->db->fieldExists('no_nup', 'mkdt')) {
            $this->forge->dropColumn('mkdt', 'no_nup');
        }
    }
}
