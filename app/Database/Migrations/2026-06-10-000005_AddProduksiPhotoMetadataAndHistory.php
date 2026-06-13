<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddProduksiPhotoMetadataAndHistory extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('file_produksi')) {
            $fields = [];

            if (!$this->db->fieldExists('foto_lat', 'file_produksi')) {
                $fields['foto_lat'] = [
                    'type'       => 'DECIMAL',
                    'constraint' => '10,7',
                    'null'       => true,
                    'after'      => 'file_keterangan',
                ];
            }

            if (!$this->db->fieldExists('foto_lng', 'file_produksi')) {
                $fields['foto_lng'] = [
                    'type'       => 'DECIMAL',
                    'constraint' => '10,7',
                    'null'       => true,
                    'after'      => 'foto_lat',
                ];
            }

            if (!$this->db->fieldExists('foto_accuracy', 'file_produksi')) {
                $fields['foto_accuracy'] = [
                    'type'       => 'DECIMAL',
                    'constraint' => '10,2',
                    'null'       => true,
                    'after'      => 'foto_lng',
                ];
            }

            if (!$this->db->fieldExists('foto_coordinate_source', 'file_produksi')) {
                $fields['foto_coordinate_source'] = [
                    'type'       => 'VARCHAR',
                    'constraint' => 20,
                    'null'       => true,
                    'after'      => 'foto_accuracy',
                ];
            }

            if (!empty($fields)) {
                $this->forge->addColumn('file_produksi', $fields);
            }
        }

        if ($this->db->tableExists('produksi_change_history')) {
            return;
        }

        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'id_kavling' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'id_produksi' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'action' => [
                'type'       => 'VARCHAR',
                'constraint' => 80,
            ],
            'summary' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'old_data' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'new_data' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'files' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'add_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('id_kavling');
        $this->forge->addKey('id_produksi');
        $this->forge->addKey('add_by');
        $this->forge->createTable('produksi_change_history', true);
    }

    public function down()
    {
        if ($this->db->tableExists('produksi_change_history')) {
            $this->forge->dropTable('produksi_change_history', true);
        }

        if ($this->db->tableExists('file_produksi')) {
            $dropFields = [];
            foreach (['foto_coordinate_source', 'foto_accuracy', 'foto_lng', 'foto_lat'] as $field) {
                if ($this->db->fieldExists($field, 'file_produksi')) {
                    $dropFields[] = $field;
                }
            }

            if (!empty($dropFields)) {
                $this->forge->dropColumn('file_produksi', $dropFields);
            }
        }
    }
}
