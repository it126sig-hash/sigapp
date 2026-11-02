<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddBpbShareToken extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('bpb_requests')) {
            return;
        }

        if (! $this->db->fieldExists('share_token', 'bpb_requests')) {
            $this->forge->addColumn('bpb_requests', [
                'share_token' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 64,
                    'null'       => true,
                    'after'      => 'verification_token',
                ],
            ]);
        }

        // Backfill share_token untuk data existing yang masih NULL/kosong
        $rows = $this->db->table('bpb_requests')
            ->select('id')
            ->where('share_token IS NULL OR share_token = ""', null, false)
            ->get()
            ->getResult();

        foreach ($rows as $row) {
            $token = bin2hex(random_bytes(16));
            $this->db->table('bpb_requests')
                ->where('id', $row->id)
                ->update(['share_token' => $token]);
        }

        // Tambahkan unique index jika belum ada
        $indexExists = false;
        $indexes = $this->db->query("SHOW INDEX FROM bpb_requests WHERE Key_name = 'uq_bpb_requests_share_token'")->getResult();
        if (! empty($indexes)) {
            $indexExists = true;
        }

        if (! $indexExists) {
            $this->db->query('ALTER TABLE bpb_requests ADD UNIQUE KEY uq_bpb_requests_share_token (share_token)');
        }
    }

    public function down()
    {
        if (! $this->db->tableExists('bpb_requests')) {
            return;
        }

        if ($this->db->fieldExists('share_token', 'bpb_requests')) {
            try {
                $this->db->query('ALTER TABLE bpb_requests DROP INDEX uq_bpb_requests_share_token');
            } catch (\Throwable) {
            }

            $this->forge->dropColumn('bpb_requests', 'share_token');
        }
    }
}
