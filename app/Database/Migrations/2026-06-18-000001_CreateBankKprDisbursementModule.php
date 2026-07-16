<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateBankKprDisbursementModule extends Migration
{
    public function up()
    {
        $this->createDisbursementTable();
        $this->seedSiteplanMenu();
    }

    public function down()
    {
        $this->removeSiteplanMenu();
        $this->forge->dropTable('bank_kpr_disbursement', true);
    }

    private function createDisbursementTable(): void
    {
        if ($this->db->tableExists('bank_kpr_disbursement')) {
            return;
        }

        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'id_mkdt' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
            ],
            'id_kavling' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
            ],
            'id_bank' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
            ],
            'nominal_plafon' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0,
            ],
            'nominal_cair' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0,
            ],
            'nominal_retensi' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0,
            ],
            'tanggal_cair' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'rekening_tujuan' => [
                'type'       => 'VARCHAR',
                'constraint' => 160,
                'null'       => true,
            ],
            'no_referensi' => [
                'type'       => 'VARCHAR',
                'constraint' => 160,
                'null'       => true,
            ],
            'file_bukti' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'keterangan' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'status' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'default'    => 'draft',
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
            'deleted_by' => [
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
            'deleted_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('id_mkdt', false, false, 'idx_bank_kpr_mkdt');
        $this->forge->addKey('id_kavling', false, false, 'idx_bank_kpr_kavling');
        $this->forge->addKey('id_bank', false, false, 'idx_bank_kpr_bank');
        $this->forge->addKey('status', false, false, 'idx_bank_kpr_status');
        $this->forge->createTable('bank_kpr_disbursement', true);
    }

    private function seedSiteplanMenu(): void
    {
        if (! $this->db->tableExists('siteplan_menu_items') || ! $this->db->tableExists('siteplan_menu_roles')) {
            return;
        }

        $now = date('Y-m-d H:i:s');
        $payload = [
            'id_group'    => 3,
            'item_key'    => 'keuangan_pencairan_bank',
            'label'       => 'Pencairan Bank',
            'group_label' => 'Pembayaran',
            'onclick'     => 'pencairan_bank()',
            'icon'        => 'fas fa-university',
            'btn_class'   => 'btn-primary',
            'sort_order'  => 55,
            'is_active'   => 1,
            'extra_id'    => 'pencairan_bank-btn',
            'extra_class' => '',
            'updated_at'  => $now,
        ];

        $existing = $this->db->table('siteplan_menu_items')
            ->select('id')
            ->where('item_key', 'keuangan_pencairan_bank')
            ->get()
            ->getRow();

        if ($existing) {
            $this->db->table('siteplan_menu_items')->where('id', (int) $existing->id)->update($payload);
            $itemId = (int) $existing->id;
        } else {
            $payload['created_at'] = $now;
            $this->db->table('siteplan_menu_items')->insert($payload);
            $itemId = (int) $this->db->insertID();
        }

        foreach ([1, 3] as $roleId) {
            $exists = $this->db->table('siteplan_menu_roles')
                ->where('id_group', $roleId)
                ->where('id_siteplan_menu_item', $itemId)
                ->countAllResults();

            if ($exists > 0) {
                continue;
            }

            $this->db->table('siteplan_menu_roles')->insert([
                'id_group'              => $roleId,
                'id_siteplan_menu_item' => $itemId,
                'created_at'            => $now,
                'updated_at'            => $now,
            ]);
        }
    }

    private function removeSiteplanMenu(): void
    {
        if (! $this->db->tableExists('siteplan_menu_items')) {
            return;
        }

        $item = $this->db->table('siteplan_menu_items')
            ->select('id')
            ->where('item_key', 'keuangan_pencairan_bank')
            ->get()
            ->getRow();

        if (! $item) {
            return;
        }

        if ($this->db->tableExists('siteplan_menu_roles')) {
            $this->db->table('siteplan_menu_roles')
                ->where('id_siteplan_menu_item', (int) $item->id)
                ->delete();
        }

        $this->db->table('siteplan_menu_items')
            ->where('id', (int) $item->id)
            ->delete();
    }
}
