<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSpecificationFieldsToTipeTable extends Migration
{
    public function up()
    {
        $fields = [
            'jumlah_kamar_tidur' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
                'after'      => 'lt',
            ],
            'jumlah_kamar_mandi' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
                'after'      => 'jumlah_kamar_tidur',
            ],
            'spesifikasi_teknis_atap' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'jumlah_kamar_mandi',
            ],
            'spesifikasi_teknis_dinding' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'spesifikasi_teknis_atap',
            ],
            'spesifikasi_teknis_lantai' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'spesifikasi_teknis_dinding',
            ],
            'spesifikasi_teknis_pondasi' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'spesifikasi_teknis_lantai',
            ],
        ];

        foreach ($fields as $field => $definition) {
            if (!$this->db->fieldExists($field, 'tipe')) {
                $this->forge->addColumn('tipe', [
                    $field => $definition,
                ]);
            }
        }
    }

    public function down()
    {
        foreach ([
            'spesifikasi_teknis_pondasi',
            'spesifikasi_teknis_lantai',
            'spesifikasi_teknis_dinding',
            'spesifikasi_teknis_atap',
            'jumlah_kamar_mandi',
            'jumlah_kamar_tidur',
        ] as $field) {
            if ($this->db->fieldExists($field, 'tipe')) {
                $this->forge->dropColumn('tipe', $field);
            }
        }
    }
}
