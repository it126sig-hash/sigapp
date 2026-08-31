<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ImproveMemberGetMemberBonusFlow extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('referrals') && !$this->db->fieldExists('status', 'referrals')) {
            $this->forge->addColumn('referrals', [
                'status' => [
                    'type'       => 'ENUM',
                    'constraint' => ['active', 'inactive'],
                    'default'    => 'active',
                    'after'      => 'id_proyek',
                ],
            ]);
        }

        if ($this->db->tableExists('referral_bonuses')) {
            $columns = [
                'nominal_pengajuan_keuangan' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '15,2',
                    'null'       => true,
                    'after'      => 'id_pengajuan_pencairan',
                ],
                'tanggal_spp' => [
                    'type' => 'DATE',
                    'null' => true,
                    'after' => 'nominal_pengajuan_keuangan',
                ],
                'bukti_pengajuan_keuangan' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                    'after'      => 'tanggal_spp',
                ],
                'submitted_keuangan_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                    'after' => 'bukti_pengajuan_keuangan',
                ],
                'submitted_keuangan_by' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'null'       => true,
                    'after'      => 'submitted_keuangan_at',
                ],
                'nominal_cair_keuangan' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '15,2',
                    'null'       => true,
                    'after'      => 'submitted_keuangan_by',
                ],
                'tanggal_cair_keuangan' => [
                    'type' => 'DATE',
                    'null' => true,
                    'after' => 'nominal_cair_keuangan',
                ],
                'bukti_transfer_ke_promosi' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                    'after'      => 'tanggal_cair_keuangan',
                ],
                'cair_keuangan_by' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'null'       => true,
                    'after'      => 'bukti_transfer_ke_promosi',
                ],
            ];

            foreach ($columns as $field => $definition) {
                if (!$this->db->fieldExists($field, 'referral_bonuses')) {
                    $this->forge->addColumn('referral_bonuses', [$field => $definition]);
                }
            }
        }

        if (!$this->db->tableExists('referral_bonus_histories')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'id_bonus' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                ],
                'id_referral' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                ],
                'action' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 60,
                ],
                'old_status' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 40,
                    'null'       => true,
                ],
                'new_status' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 40,
                    'null'       => true,
                ],
                'old_nominal_bonus' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '15,2',
                    'null'       => true,
                ],
                'new_nominal_bonus' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '15,2',
                    'null'       => true,
                ],
                'nominal_pengajuan_keuangan' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '15,2',
                    'null'       => true,
                ],
                'nominal_cair_keuangan' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '15,2',
                    'null'       => true,
                ],
                'payload_json' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'note' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'add_by' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'null'       => true,
                ],
                'created_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addKey('id_bonus', false, false, 'idx_referral_bonus_histories_bonus');
            $this->forge->addKey('id_referral', false, false, 'idx_referral_bonus_histories_referral');
            $this->forge->addKey('action', false, false, 'idx_referral_bonus_histories_action');
            $this->forge->createTable('referral_bonus_histories', true);
        }

        $this->ensureMgmMenuRoles();
    }

    public function down()
    {
        $this->forge->dropTable('referral_bonus_histories', true);

        if ($this->db->tableExists('referral_bonuses')) {
            foreach ([
                'cair_keuangan_by',
                'bukti_transfer_ke_promosi',
                'tanggal_cair_keuangan',
                'nominal_cair_keuangan',
                'submitted_keuangan_by',
                'submitted_keuangan_at',
                'tanggal_spp',
                'nominal_pengajuan_keuangan',
            ] as $field) {
                if ($this->db->fieldExists($field, 'referral_bonuses')) {
                    $this->forge->dropColumn('referral_bonuses', $field);
                }
            }
        }

        if ($this->db->tableExists('referrals') && $this->db->fieldExists('status', 'referrals')) {
            $this->forge->dropColumn('referrals', 'status');
        }
    }

    private function ensureMgmMenuRoles(): void
    {
        if (!$this->db->tableExists('menus') || !$this->db->tableExists('menu_roles')) {
            return;
        }

        $menu = $this->db->table('menus')
            ->select('id')
            ->where('url', 'member-get-member')
            ->get()
            ->getRow();

        if (!$menu) {
            return;
        }

        foreach ([3, 8] as $groupId) {
            $exists = $this->db->table('menu_roles')
                ->where('id_menu', $menu->id)
                ->where('id_groups', $groupId)
                ->countAllResults();

            if ($exists === 0) {
                $this->db->table('menu_roles')->insert([
                    'id_menu' => $menu->id,
                    'id_groups' => $groupId,
                ]);
            }
        }
    }
}
