<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RevampKavlingRequestTables extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('kavling_request')) {
            $this->db->table('kavling_request')->truncate();

            if (! $this->db->fieldExists('applied_id_tipe', 'kavling_request')) {
                $col = [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                    'default'    => null,
                ];
                if ($this->db->fieldExists('id_tipe', 'kavling_request')) {
                    $col['after'] = 'id_tipe';
                }
                $this->forge->addColumn('kavling_request', ['applied_id_tipe' => $col]);
                $this->db->resetDataCache();
            }

            if (! $this->db->fieldExists('catatan_planning', 'kavling_request')) {
                $col = [
                    'type'    => 'TEXT',
                    'null'    => true,
                    'default' => null,
                ];
                if ($this->db->fieldExists('alasan_tolak', 'kavling_request')) {
                    $col['after'] = 'alasan_tolak';
                }
                $this->forge->addColumn('kavling_request', ['catatan_planning' => $col]);
                $this->db->resetDataCache();
            }

            if (! $this->db->fieldExists('processed_by', 'kavling_request')) {
                $col = [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                    'default'    => null,
                ];
                if ($this->db->fieldExists('created_by', 'kavling_request')) {
                    $col['after'] = 'created_by';
                }
                $this->forge->addColumn('kavling_request', ['processed_by' => $col]);
                $this->db->resetDataCache();
            }

            if (! $this->db->fieldExists('processed_at', 'kavling_request')) {
                $col = [
                    'type'    => 'DATETIME',
                    'null'    => true,
                    'default' => null,
                ];
                if ($this->db->fieldExists('processed_by', 'kavling_request')) {
                    $col['after'] = 'processed_by';
                }
                $this->forge->addColumn('kavling_request', ['processed_at' => $col]);
                $this->db->resetDataCache();
            }
        }

        if (! $this->db->tableExists('kavling_request_jalan')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'id_request' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => false,
                ],
                'id_jalan' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => false,
                ],
                'created_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
            ]);

            $this->forge->addKey('id', true);
            $this->forge->addKey('id_request');
            $this->forge->addKey('id_jalan');
            $this->forge->createTable('kavling_request_jalan', true);
        }

        if (! $this->db->tableExists('kavling_request_kavling')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'id_request' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => false,
                ],
                'id_kavling' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => false,
                ],
                'old_id_tipe' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                    'default'    => null,
                ],
                'created_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
            ]);

            $this->forge->addKey('id', true);
            $this->forge->addKey('id_request');
            $this->forge->addKey('id_kavling');
            $this->forge->createTable('kavling_request_kavling', true);
        }

        $this->db->resetDataCache();
    }

    public function down()
    {
        $this->db->resetDataCache();

        if ($this->db->tableExists('kavling_request_kavling')) {
            $this->forge->dropTable('kavling_request_kavling', true);
        }

        if ($this->db->tableExists('kavling_request_jalan')) {
            $this->forge->dropTable('kavling_request_jalan', true);
        }

        if ($this->db->tableExists('kavling_request')) {
            foreach (['processed_at', 'processed_by', 'catatan_planning', 'applied_id_tipe'] as $field) {
                if ($this->db->fieldExists($field, 'kavling_request')) {
                    $this->forge->dropColumn('kavling_request', $field);
                }
            }
        }

        $this->db->resetDataCache();
    }
}
