<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpgradeTargetSiteplanAndTipeActive extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('tipe') && ! $this->db->fieldExists('is_active', 'tipe')) {
            $isActiveColumn = [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
                'null'       => false,
            ];

            if ($this->db->fieldExists('is_subsidi', 'tipe')) {
                $isActiveColumn['after'] = 'is_subsidi';
            }

            $this->forge->addColumn('tipe', [
                'is_active' => $isActiveColumn,
            ]);
        }

        if ($this->db->tableExists('target_siteplan_kavling')) {
            if (! $this->db->fieldExists('id_tipe', 'target_siteplan_kavling')) {
                $column = [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'null'       => true,
                    'default'    => null,
                ];
                if ($this->db->fieldExists('id_kavling', 'target_siteplan_kavling')) {
                    $column['after'] = 'id_kavling';
                }
                $this->forge->addColumn('target_siteplan_kavling', ['id_tipe' => $column]);
                $this->db->resetDataCache();
            }

            if (! $this->db->fieldExists('tipe_manual', 'target_siteplan_kavling')) {
                $column = [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                    'default'    => null,
                ];
                if ($this->db->fieldExists('id_tipe', 'target_siteplan_kavling')) {
                    $column['after'] = 'id_tipe';
                }
                $this->forge->addColumn('target_siteplan_kavling', ['tipe_manual' => $column]);
                $this->db->resetDataCache();
            }

            if (! $this->db->fieldExists('updated_by', 'target_siteplan_kavling')) {
                $column = [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'null'       => true,
                    'default'    => null,
                ];
                if ($this->db->fieldExists('created_at', 'target_siteplan_kavling')) {
                    $column['after'] = 'created_at';
                }
                $this->forge->addColumn('target_siteplan_kavling', ['updated_by' => $column]);
                $this->db->resetDataCache();
            }

            if (! $this->db->fieldExists('updated_at', 'target_siteplan_kavling')) {
                $column = [
                    'type'    => 'DATETIME',
                    'null'    => true,
                    'default' => null,
                ];
                if ($this->db->fieldExists('updated_by', 'target_siteplan_kavling')) {
                    $column['after'] = 'updated_by';
                }
                $this->forge->addColumn('target_siteplan_kavling', ['updated_at' => $column]);
                $this->db->resetDataCache();
            }

            if ($this->db->fieldExists('id_tipe', 'target_siteplan_kavling') && ! $this->indexExists('target_siteplan_kavling', 'id_tipe')) {
                $this->db->query('ALTER TABLE `target_siteplan_kavling` ADD INDEX `id_tipe` (`id_tipe`)');
            }
        }

        if (! $this->db->tableExists('target_siteplan_request')) {
            $this->forge->addField([
                'id_request' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'id_target' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => false,
                ],
                'id_kavling' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'null'       => false,
                ],
                'old_id_tipe' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'null'       => true,
                    'default'    => null,
                ],
                'old_tipe_manual' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                    'default'    => null,
                ],
                'new_id_tipe' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'null'       => true,
                    'default'    => null,
                ],
                'new_tipe_manual' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                    'default'    => null,
                ],
                'catatan_request' => [
                    'type'    => 'TEXT',
                    'null'    => true,
                    'default' => null,
                ],
                'status' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 20,
                    'null'       => false,
                    'default'    => 'pending',
                ],
                'request_by' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'null'       => true,
                    'default'    => null,
                ],
                'request_at' => [
                    'type'    => 'DATETIME',
                    'null'    => true,
                    'default' => null,
                ],
                'approve_by' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'null'       => true,
                    'default'    => null,
                ],
                'approve_at' => [
                    'type'    => 'DATETIME',
                    'null'    => true,
                    'default' => null,
                ],
                'catatan_approval' => [
                    'type'    => 'TEXT',
                    'null'    => true,
                    'default' => null,
                ],
            ]);

            $this->forge->addKey('id_request', true);
            $this->forge->addKey(['id_target', 'id_kavling']);
            $this->forge->addKey('status');
            $this->forge->addKey('request_by');
            $this->forge->createTable('target_siteplan_request', true);
        }
    }

    public function down()
    {
        $this->db->resetDataCache();

        if ($this->db->tableExists('target_siteplan_request')) {
            $this->forge->dropTable('target_siteplan_request', true);
        }

        if ($this->db->tableExists('target_siteplan_kavling')) {
            if ($this->indexExists('target_siteplan_kavling', 'id_tipe')) {
                $this->db->query('ALTER TABLE `target_siteplan_kavling` DROP INDEX `id_tipe`');
            }

            foreach (['updated_at', 'updated_by', 'tipe_manual', 'id_tipe'] as $field) {
                if ($this->db->fieldExists($field, 'target_siteplan_kavling')) {
                    $this->forge->dropColumn('target_siteplan_kavling', $field);
                }
            }
        }

        if ($this->db->tableExists('tipe') && $this->db->fieldExists('is_active', 'tipe')) {
            $this->forge->dropColumn('tipe', 'is_active');
        }

        $this->db->resetDataCache();
    }

    private function indexExists(string $table, string $index): bool
    {
        return $this->db->query("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$index])->getNumRows() > 0;
    }
}
