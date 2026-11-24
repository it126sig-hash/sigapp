<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCancelledStatusToKavlingRequest extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('kavling_request')) {
            return;
        }

        $table = $this->db->prefixTable('kavling_request');
        if (strtolower((string) $this->db->DBDriver) === 'mysqli') {
            $this->db->query(
                "ALTER TABLE `{$table}` MODIFY COLUMN `status` ENUM('pending', 'approved', 'rejected', 'cancelled') NOT NULL DEFAULT 'pending'"
            );
        }

        if (! $this->db->fieldExists('alasan_batal', 'kavling_request')) {
            $col = [
                'type'    => 'TEXT',
                'null'    => true,
                'default' => null,
            ];
            if ($this->db->fieldExists('alasan_tolak', 'kavling_request')) {
                $col['after'] = 'alasan_tolak';
            }

            $this->forge->addColumn('kavling_request', [
                'alasan_batal' => $col,
            ]);
        }

        $this->db->resetDataCache();
    }

    public function down()
    {
        if (! $this->db->tableExists('kavling_request')) {
            return;
        }

        if ($this->db->fieldExists('alasan_batal', 'kavling_request')) {
            $this->forge->dropColumn('kavling_request', 'alasan_batal');
        }

        $this->db->table('kavling_request')
            ->where('status', 'cancelled')
            ->update(['status' => 'rejected']);

        $table = $this->db->prefixTable('kavling_request');
        if (strtolower((string) $this->db->DBDriver) === 'mysqli') {
            $this->db->query(
                "ALTER TABLE `{$table}` MODIFY COLUMN `status` ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending'"
            );
        }

        $this->db->resetDataCache();
    }
}
