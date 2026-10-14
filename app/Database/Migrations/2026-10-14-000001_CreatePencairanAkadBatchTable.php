<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePencairanAkadBatchTable extends Migration
{
    public function up()
    {
        $this->createBatchTable();
        $this->addBatchIdToPengajuan();
        $this->backfillExistingPengajuan();
    }

    public function down()
    {
        if ($this->db->tableExists('pencairan_akad_pengajuan') && $this->db->fieldExists('id_batch', 'pencairan_akad_pengajuan')) {
            $this->forge->dropColumn('pencairan_akad_pengajuan', 'id_batch');
        }

        if ($this->db->tableExists('pencairan_akad_batch')) {
            $this->forge->dropTable('pencairan_akad_batch', true);
        }
    }

    private function createBatchTable(): void
    {
        if ($this->db->tableExists('pencairan_akad_batch')) {
            return;
        }

        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'nomor_batch' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            'tanggal_pengajuan' => [
                'type' => 'DATE',
            ],
            'tanggal_rencana_cair' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'catatan' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'lampiran_surat' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'total_pengajuan' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0,
            ],
            'total_cair' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0,
            ],
            'status' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'default'    => 'active',
                'comment'    => 'active|partial|paid|void',
            ],
            'void_reason' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'add_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
            ],
            'edit_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
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
        $this->forge->addKey('nomor_batch', false, false, 'idx_pencairan_akad_batch_nomor');
        $this->forge->addKey('status', false, false, 'idx_pencairan_akad_batch_status');
        $this->forge->addKey('tanggal_pengajuan', false, false, 'idx_pencairan_akad_batch_tgl');
        $this->forge->createTable('pencairan_akad_batch', true);
    }

    private function addBatchIdToPengajuan(): void
    {
        if (!$this->db->tableExists('pencairan_akad_pengajuan')) {
            return;
        }

        if (!$this->db->fieldExists('id_batch', 'pencairan_akad_pengajuan')) {
            $this->forge->addColumn('pencairan_akad_pengajuan', [
                'id_batch' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                    'after'      => 'id',
                ],
            ]);
            $this->db->query('ALTER TABLE `pencairan_akad_pengajuan` ADD INDEX `idx_pencairan_akad_pengajuan_batch` (`id_batch`)');
        }
    }

    private function backfillExistingPengajuan(): void
    {
        if (!$this->db->tableExists('pencairan_akad_pengajuan') || !$this->db->tableExists('pencairan_akad_batch')) {
            return;
        }

        $existing = $this->db->table('pencairan_akad_pengajuan')
            ->where('id_batch IS NULL', null, false)
            ->get()
            ->getResult();

        foreach ($existing as $p) {
            $tgl = !empty($p->tanggal_pengajuan) ? $p->tanggal_pengajuan : date('Y-m-d');
            $nomorBatch = 'PA-' . date('Ym', strtotime($tgl)) . '-' . str_pad((string) $p->id, 4, '0', STR_PAD_LEFT);

            $this->db->table('pencairan_akad_batch')->insert([
                'nomor_batch'          => $nomorBatch,
                'tanggal_pengajuan'    => $tgl,
                'tanggal_rencana_cair' => $p->tanggal_rencana_cair ?: null,
                'catatan'              => $p->catatan,
                'lampiran_surat'       => $p->lampiran_surat,
                'total_pengajuan'      => $p->total_pengajuan,
                'total_cair'           => $p->total_cair,
                'status'               => $p->status,
                'void_reason'          => $p->void_reason,
                'add_by'               => $p->add_by,
                'edit_by'              => $p->edit_by,
                'created_at'           => $p->created_at ?: date('Y-m-d H:i:s'),
                'updated_at'           => $p->updated_at ?: date('Y-m-d H:i:s'),
            ]);

            $batchId = (int) $this->db->insertID();

            $this->db->table('pencairan_akad_pengajuan')
                ->where('id', (int) $p->id)
                ->update(['id_batch' => $batchId]);
        }
    }
}
