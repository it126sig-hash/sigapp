<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateBpbModule extends Migration
{
    private const GROUPS = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];

    public function up()
    {
        $this->createRequests();
        $this->createItems();
        $this->createFiles();
        $this->createSignatures();
        $this->createHistory();
        $this->createCounters();
        $this->createProfileSignatures();
        $this->seedMenu();
        $this->seedNotificationEvents();
    }

    public function down()
    {
        if ($this->db->tableExists('notification_event_types')) {
            $this->db->table('notification_event_types')->whereIn('event_type', [
                'bpb_signature_requested', 'bpb_signature_completed', 'bpb_rejected',
                'bpb_approved', 'bpb_status_changed',
            ])->delete();
        }

        if ($this->db->tableExists('menus')) {
            $menu = $this->db->table('menus')->select('id')->where('slug', 'bpb')->get()->getRow();
            if ($menu) {
                if ($this->db->tableExists('menu_roles')) {
                    $this->db->table('menu_roles')->where('id_menu', (int) $menu->id)->delete();
                }
                $this->db->table('menus')->where('id', (int) $menu->id)->delete();
            }
        }

        foreach (['user_signature_profiles', 'bpb_number_counters', 'bpb_history', 'bpb_signatures', 'bpb_files', 'bpb_items', 'bpb_requests'] as $table) {
            $this->forge->dropTable($table, true);
        }
    }

    private function createRequests(): void
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'nomor' => ['type' => 'VARCHAR', 'constraint' => 40, 'null' => true],
            'sequence_no' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'sequence_year' => ['type' => 'SMALLINT', 'unsigned' => true, 'null' => true],
            'applicant_user_id' => ['type' => 'INT', 'unsigned' => true],
            'applicant_name' => ['type' => 'VARCHAR', 'constraint' => 160, 'null' => true],
            'applicant_department' => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true],
            'cc_user_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'approver_user_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'status' => ['type' => 'VARCHAR', 'constraint' => 40, 'default' => 'draft'],
            'current_document_hash' => ['type' => 'CHAR', 'constraint' => 64, 'null' => true],
            'verification_token' => ['type' => 'CHAR', 'constraint' => 64, 'null' => true],
            'actual_amount' => ['type' => 'DECIMAL', 'constraint' => '18,2', 'null' => true],
            'recipient_name' => ['type' => 'VARCHAR', 'constraint' => 160, 'null' => true],
            'account_number' => ['type' => 'VARCHAR', 'constraint' => 80, 'null' => true],
            'disbursement_note' => ['type' => 'TEXT', 'null' => true],
            'purchase_date' => ['type' => 'DATE', 'null' => true],
            'purchase_note' => ['type' => 'TEXT', 'null' => true],
            'pending_reason' => ['type' => 'TEXT', 'null' => true],
            'rejection_reason' => ['type' => 'TEXT', 'null' => true],
            'cancellation_reason' => ['type' => 'TEXT', 'null' => true],
            'submitted_at' => ['type' => 'DATETIME', 'null' => true],
            'approved_at' => ['type' => 'DATETIME', 'null' => true],
            'processed_at' => ['type' => 'DATETIME', 'null' => true],
            'disbursed_at' => ['type' => 'DATETIME', 'null' => true],
            'purchased_at' => ['type' => 'DATETIME', 'null' => true],
            'created_by' => ['type' => 'INT', 'unsigned' => true],
            'updated_by' => ['type' => 'INT', 'unsigned' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('nomor', 'uq_bpb_requests_nomor');
        $this->forge->addUniqueKey('verification_token', 'uq_bpb_requests_verification_token');
        $this->forge->addKey('applicant_user_id', false, false, 'idx_bpb_requests_applicant');
        $this->forge->addKey('cc_user_id', false, false, 'idx_bpb_requests_cc');
        $this->forge->addKey('approver_user_id', false, false, 'idx_bpb_requests_approver');
        $this->forge->addKey('status', false, false, 'idx_bpb_requests_status');
        $this->forge->createTable('bpb_requests', true, ['ENGINE' => 'InnoDB']);
    }

    private function createItems(): void
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'bpb_id' => ['type' => 'BIGINT', 'unsigned' => true],
            'item_order' => ['type' => 'SMALLINT', 'unsigned' => true, 'default' => 1],
            'nama_barang' => ['type' => 'VARCHAR', 'constraint' => 255],
            'jumlah' => ['type' => 'DECIMAL', 'constraint' => '12,2'],
            'keterangan' => ['type' => 'TEXT', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('bpb_id', false, false, 'idx_bpb_items_bpb');
        $this->forge->createTable('bpb_items', true, ['ENGINE' => 'InnoDB']);
    }

    private function createFiles(): void
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'bpb_id' => ['type' => 'BIGINT', 'unsigned' => true],
            'category' => ['type' => 'VARCHAR', 'constraint' => 20],
            'logical_path' => ['type' => 'VARCHAR', 'constraint' => 500],
            'original_name' => ['type' => 'VARCHAR', 'constraint' => 255],
            'mime_type' => ['type' => 'VARCHAR', 'constraint' => 100],
            'file_size' => ['type' => 'BIGINT', 'unsigned' => true],
            'file_sha256' => ['type' => 'CHAR', 'constraint' => 64],
            'uploaded_by' => ['type' => 'INT', 'unsigned' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['bpb_id', 'category'], false, false, 'idx_bpb_files_bpb_category');
        $this->forge->createTable('bpb_files', true, ['ENGINE' => 'InnoDB']);
    }

    private function createSignatures(): void
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'bpb_id' => ['type' => 'BIGINT', 'unsigned' => true],
            'role' => ['type' => 'VARCHAR', 'constraint' => 40],
            'signer_user_id' => ['type' => 'INT', 'unsigned' => true],
            'signer_name' => ['type' => 'VARCHAR', 'constraint' => 160],
            'signer_department' => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true],
            'signer_level' => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true],
            'method' => ['type' => 'VARCHAR', 'constraint' => 20],
            'signature_path' => ['type' => 'VARCHAR', 'constraint' => 500],
            'document_hash' => ['type' => 'CHAR', 'constraint' => 64],
            'ip_address' => ['type' => 'VARCHAR', 'constraint' => 45, 'null' => true],
            'user_agent' => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
            'signed_at' => ['type' => 'DATETIME'],
            'revoked_at' => ['type' => 'DATETIME', 'null' => true],
            'revoked_by' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'revoked_reason' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['bpb_id', 'role'], false, false, 'idx_bpb_signatures_bpb_role');
        $this->forge->createTable('bpb_signatures', true, ['ENGINE' => 'InnoDB']);
    }

    private function createHistory(): void
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'bpb_id' => ['type' => 'BIGINT', 'unsigned' => true],
            'from_status' => ['type' => 'VARCHAR', 'constraint' => 40, 'null' => true],
            'to_status' => ['type' => 'VARCHAR', 'constraint' => 40],
            'action' => ['type' => 'VARCHAR', 'constraint' => 60],
            'summary' => ['type' => 'VARCHAR', 'constraint' => 500],
            'metadata' => ['type' => 'LONGTEXT', 'null' => true],
            'actor_user_id' => ['type' => 'INT', 'unsigned' => true],
            'actor_name' => ['type' => 'VARCHAR', 'constraint' => 160],
            'created_at' => ['type' => 'DATETIME'],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['bpb_id', 'created_at'], false, false, 'idx_bpb_history_bpb_created');
        $this->forge->createTable('bpb_history', true, ['ENGINE' => 'InnoDB']);
    }

    private function createCounters(): void
    {
        $this->forge->addField([
            'year' => ['type' => 'SMALLINT', 'unsigned' => true],
            'last_number' => ['type' => 'INT', 'unsigned' => true, 'default' => 0],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('year', true);
        $this->forge->createTable('bpb_number_counters', true, ['ENGINE' => 'InnoDB']);
    }

    private function createProfileSignatures(): void
    {
        $this->forge->addField([
            'user_id' => ['type' => 'INT', 'unsigned' => true],
            'signature_path' => ['type' => 'VARCHAR', 'constraint' => 500],
            'updated_at' => ['type' => 'DATETIME'],
        ]);
        $this->forge->addKey('user_id', true);
        $this->forge->createTable('user_signature_profiles', true, ['ENGINE' => 'InnoDB']);
    }

    private function seedMenu(): void
    {
        if (! $this->db->tableExists('menus')) {
            return;
        }
        $row = $this->db->table('menus')->select('id')->where('slug', 'bpb')->get()->getRow();
        if ($row) {
            $menuId = (int) $row->id;
        } else {
            $max = $this->db->table('menus')->selectMax('sort_order')->where('parent_id', 0)->get()->getRow();
            $now = date('Y-m-d H:i:s');
            $this->db->table('menus')->insert([
                'name' => 'Bon Permintaan Barang', 'url' => 'bpb', 'icon' => 'shopping-cart',
                'slug' => 'bpb', 'parent_id' => 0, 'is_active' => 1,
                'sort_order' => ((int) ($max->sort_order ?? 0)) + 1, 'date_add' => $now, 'date_edit' => $now,
            ]);
            $menuId = (int) $this->db->insertID();
        }
        if (! $this->db->tableExists('menu_roles')) {
            return;
        }
        foreach (self::GROUPS as $groupId) {
            $builder = $this->db->table('menu_roles');
            if (! $builder->where(['id_groups' => $groupId, 'id_menu' => $menuId])->countAllResults()) {
                $this->db->table('menu_roles')->insert([
                    'id_groups' => $groupId, 'id_menu' => $menuId,
                    'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s'),
                ]);
            }
        }
    }

    private function seedNotificationEvents(): void
    {
        if (! $this->db->tableExists('notification_event_types')) {
            return;
        }
        $events = [
            ['bpb_signature_requested', 'BPB - Permintaan Tanda Tangan', 'Permintaan tanda tangan BPB', 'BPB', 1],
            ['bpb_signature_completed', 'BPB - Tanda Tangan Selesai', 'Tanda tangan BPB telah diberikan', 'BPB', 0],
            ['bpb_rejected', 'BPB - Ditolak', 'Pengajuan BPB ditolak', 'BPB', 0],
            ['bpb_approved', 'BPB - Disetujui', 'Seluruh approval BPB selesai', 'BPB', 0],
            ['bpb_status_changed', 'BPB - Perubahan Status', 'Status operasional BPB berubah', 'BPB', 0],
        ];
        foreach ($events as [$type, $label, $description, $category, $mandatory]) {
            if ($this->db->table('notification_event_types')->where('event_type', $type)->countAllResults()) {
                continue;
            }
            $this->db->table('notification_event_types')->insert([
                'event_type' => $type, 'label' => $label, 'description' => $description,
                'category' => strtolower($category), 'is_mandatory' => $mandatory,
                'default_in_app' => 1, 'default_email' => $mandatory, 'default_web_push' => $mandatory,
                'relevant_groups' => null, 'sort_order' => 90,
                'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }
}
