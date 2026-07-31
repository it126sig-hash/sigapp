<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTiketMasalahModule extends Migration
{
    public function up()
    {
        // 1. Create tiket_masalah table
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'ref_type' => [
                'type'       => 'ENUM',
                'constraint' => ['kavling', 'others'],
            ],
            'ref_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'id_proyek' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'tanggal_masalah' => [
                'type' => 'DATE',
            ],
            'keterangan' => [
                'type' => 'TEXT',
            ],
            'prioritas' => [
                'type'       => 'ENUM',
                'constraint' => ['urgent', 'medium', 'normal', 'low'],
                'default'    => 'normal',
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['dibuat', 'dalam_proses', 'selesai', 'batal', 'hold'],
                'default'    => 'dibuat',
            ],
            'pic_user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['ref_type', 'ref_id']);
        $this->forge->addKey(['id_proyek', 'status']);
        $this->forge->addKey('pic_user_id');
        $this->forge->createTable('tiket_masalah');

        // 2. Create tiket_masalah_user table
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'id_tiket_masalah' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['id_tiket_masalah', 'user_id'], false, true); // Unique index
        $this->forge->createTable('tiket_masalah_user');

        // 3. Create tiket_masalah_foto table
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'id_tiket_masalah' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'file_path' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
            ],
            'file_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'uploaded_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('id_tiket_masalah');
        $this->forge->createTable('tiket_masalah_foto');

        // 4. Create tiket_masalah_progress table
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'id_tiket_masalah' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'keterangan' => [
                'type' => 'TEXT',
            ],
            'status_sebelum' => [
                'type'       => 'ENUM',
                'constraint' => ['dibuat', 'dalam_proses', 'selesai', 'batal', 'hold'],
                'null'       => true,
            ],
            'status_sesudah' => [
                'type'       => 'ENUM',
                'constraint' => ['dibuat', 'dalam_proses', 'selesai', 'batal', 'hold'],
                'null'       => true,
            ],
            'foto_paths' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('id_tiket_masalah');
        $this->forge->createTable('tiket_masalah_progress');

        // 5. Alter notification table to add user_id
        if (!$this->db->fieldExists('user_id', 'notification')) {
            $this->forge->addColumn('notification', [
                'user_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                    'after'      => 'add_by',
                ],
            ]);
            $this->db->query('ALTER TABLE notification ADD INDEX idx_user_id (user_id)');
        }

        // 6. Alter others table to add fasum to tipe enum
        $this->db->query("ALTER TABLE others MODIFY COLUMN tipe ENUM('jalan', 'fasos', 'rth', 'fasum') NULL DEFAULT NULL");
    }

    public function down()
    {
        $this->forge->dropTable('tiket_masalah_progress', true);
        $this->forge->dropTable('tiket_masalah_foto', true);
        $this->forge->dropTable('tiket_masalah_user', true);
        $this->forge->dropTable('tiket_masalah', true);

        if ($this->db->fieldExists('user_id', 'notification')) {
            $this->db->query('ALTER TABLE notification DROP INDEX idx_user_id');
            $this->forge->dropColumn('notification', 'user_id');
        }

        $this->db->query("ALTER TABLE others MODIFY COLUMN tipe ENUM('jalan', 'fasos', 'rth') NULL DEFAULT NULL");
    }
}
