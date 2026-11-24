<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddNupAndKavlingHistoryModule extends Migration
{
    public function up()
    {
        $mkdtColumns = [
            'id_proyek'         => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'catatan_nup'       => ['type' => 'TEXT', 'null' => true],
            'file_nup'          => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'file_surat_pindah' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'nup_source'        => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'last_id_kavling'   => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'nup_at'            => ['type' => 'DATETIME', 'null' => true],
            'nup_by'            => ['type' => 'INT', 'constraint' => 11, 'null' => true],
        ];

        foreach ($mkdtColumns as $colName => $colDef) {
            if (! $this->db->fieldExists($colName, 'mkdt')) {
                $this->forge->addColumn('mkdt', [$colName => $colDef]);
            }
        }

        // Backfill id_proyek pada mkdt dari relasi kavling -> jalan -> cluster
        if ($this->db->DBDriver !== 'SQLite3') {
            $this->db->query("
                UPDATE mkdt m
                INNER JOIN kavling k ON k.id_kavling = m.id_kavling
                INNER JOIN jalan j ON j.id_jalan = k.id_jalan
                INNER JOIN cluster c ON c.id_cluster = j.id_cluster
                SET m.id_proyek = c.id_proyek
                WHERE m.id_proyek IS NULL AND m.id_kavling IS NOT NULL AND m.id_kavling > 0
            ");
        }

        if (! $this->db->tableExists('mkdt_kavling_history')) {
            $this->forge->addField([
                'id'                           => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
                'id_mkdt'                      => ['type' => 'INT', 'constraint' => 11],
                'id_konsumen'                  => ['type' => 'INT', 'constraint' => 11, 'null' => true],
                'id_proyek'                    => ['type' => 'INT', 'constraint' => 11, 'null' => true],
                'action_type'                  => ['type' => 'VARCHAR', 'constraint' => 30],
                'from_id_kavling'              => ['type' => 'INT', 'constraint' => 11, 'null' => true],
                'to_id_kavling'                => ['type' => 'INT', 'constraint' => 11, 'null' => true],
                'from_kavling_label'           => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true],
                'to_kavling_label'             => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true],
                'status_sebelum'               => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
                'status_sesudah'               => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
                'nominal_booking_snapshot'     => ['type' => 'DOUBLE', 'default' => 0],
                'nominal_sudah_bayar_snapshot' => ['type' => 'DOUBLE', 'default' => 0],
                'catatan'                      => ['type' => 'TEXT', 'null' => true],
                'file_lampiran'                => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
                'created_by'                   => ['type' => 'INT', 'constraint' => 11, 'null' => true],
                'created_at'                   => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addKey('id_mkdt');
            $this->forge->addKey('id_konsumen');
            $this->forge->addKey('id_proyek');
            $this->forge->addKey('from_id_kavling');
            $this->forge->addKey('to_id_kavling');
            $this->forge->createTable('mkdt_kavling_history');
        }
    }

    public function down()
    {
        if ($this->db->tableExists('mkdt_kavling_history')) {
            $this->forge->dropTable('mkdt_kavling_history', true);
        }

        $mkdtColumns = [
            'id_proyek',
            'catatan_nup',
            'file_nup',
            'file_surat_pindah',
            'nup_source',
            'last_id_kavling',
            'nup_at',
            'nup_by',
        ];

        foreach ($mkdtColumns as $colName) {
            if ($this->db->fieldExists($colName, 'mkdt')) {
                $this->forge->dropColumn('mkdt', $colName);
            }
        }
    }
}
