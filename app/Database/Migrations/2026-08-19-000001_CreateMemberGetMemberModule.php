<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateMemberGetMemberModule extends Migration
{
    public function up()
    {
        // 1. Tambah kode_referal di tabel konsumen
        if ($this->db->tableExists('konsumen')) {
            if (!$this->db->fieldExists('kode_referal', 'konsumen')) {
                $this->forge->addColumn('konsumen', [
                    'kode_referal' => [
                        'type'       => 'VARCHAR',
                        'constraint' => 10,
                        'null'       => true,
                        'unique'     => true,
                        'after'      => 'id_konsumen'
                    ]
                ]);
            }
        }

        // 2. Modifikasi enum departemen_asal di pengajuan_pencairan
        if ($this->db->tableExists('pengajuan_pencairan')) {
            // Kita drop kolom lama dan buat ulang dengan enum baru. Atau alter table dengan raw SQL.
            // Cara paling aman di CI4 Migration untuk ubah ENUM adalah raw query
            $this->db->query("ALTER TABLE pengajuan_pencairan MODIFY COLUMN departemen_asal ENUM('pajak','legal','produksi','mkdt','promosi') NOT NULL;");
        }

        // 3. Tabel referrals
        if (!$this->db->tableExists('referrals')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'id_konsumen_referrer' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                ],
                'id_mkdt_referred' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                ],
                'id_proyek' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
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
                'updated_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addUniqueKey('id_mkdt_referred', 'uniq_referrals_mkdt_referred');
            $this->forge->addKey('id_konsumen_referrer', false, false, 'idx_referrals_referrer');
            $this->forge->addKey('id_proyek', false, false, 'idx_referrals_proyek');
            $this->forge->createTable('referrals', true);
        }

        // 4. Tabel referral_bonus_stages
        if (!$this->db->tableExists('referral_bonus_stages')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'id_proyek' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                ],
                'nama_tahapan' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 50,
                ],
                'trigger_status_mkdt' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 30,
                ],
                'nominal_default' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '15,2',
                    'default'    => 0,
                ],
                'urutan' => [
                    'type'       => 'TINYINT',
                    'constraint' => 3,
                    'unsigned'   => true,
                    'default'    => 1,
                ],
                'is_active' => [
                    'type'       => 'TINYINT',
                    'constraint' => 1,
                    'default'    => 1,
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
            $this->forge->addUniqueKey(['id_proyek', 'nama_tahapan'], 'uniq_referral_bonus_stages');
            $this->forge->createTable('referral_bonus_stages', true);
        }

        // 5. Tabel referral_bonuses
        if (!$this->db->tableExists('referral_bonuses')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'id_referral' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                ],
                'id_stage' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                ],
                'nominal_bonus' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '15,2',
                    'null'       => true,
                ],
                'status' => [
                    'type'       => 'ENUM',
                    'constraint' => ['eligible', 'dikonfirmasi', 'dibayar_promosi', 'diajukan_keuangan', 'cair', 'selesai', 'batal'],
                    'default'    => 'eligible',
                ],
                'eligible_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
                'confirmed_by' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'null'       => true,
                ],
                'confirmed_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
                'paid_by_promosi' => [
                    'type'       => 'TINYINT',
                    'constraint' => 1,
                    'default'    => 0,
                ],
                'paid_promosi_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
                'paid_promosi_by' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'null'       => true,
                ],
                'bukti_bayar_promosi' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                ],
                'id_pengajuan_pencairan' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                ],
                'cair_keuangan_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
                'keterangan' => [
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
            $this->forge->addUniqueKey(['id_referral', 'id_stage'], 'uniq_referral_bonuses');
            $this->forge->addKey('id_pengajuan_pencairan', false, false, 'idx_referral_bonuses_pengajuan');
            $this->forge->createTable('referral_bonuses', true);
        }

        // Backfill generate kode referal pada tabel konsumen
        $this->generateKodeReferalExistingKonsumen();
        
        // Buat Menu
        $this->createMenus();
    }

    public function down()
    {
        $this->forge->dropTable('referral_bonuses', true);
        $this->forge->dropTable('referral_bonus_stages', true);
        $this->forge->dropTable('referrals', true);

        if ($this->db->tableExists('konsumen') && $this->db->fieldExists('kode_referal', 'konsumen')) {
            $this->forge->dropColumn('konsumen', 'kode_referal');
        }

        // Revert enum
        if ($this->db->tableExists('pengajuan_pencairan')) {
             $this->db->query("ALTER TABLE pengajuan_pencairan MODIFY COLUMN departemen_asal ENUM('pajak','legal','produksi','mkdt') NOT NULL;");
        }
    }

    private function generateKodeReferalExistingKonsumen()
    {
        $builder = $this->db->table('konsumen');
        $konsumenList = $builder->select('id_konsumen')->where('kode_referal IS NULL', null, false)->get()->getResult();

        $batchData = [];
        foreach ($konsumenList as $k) {
            $kode = $this->generateUniqueKodeReferal($k->id_konsumen);
            $batchData[] = [
                'id_konsumen' => $k->id_konsumen,
                'kode_referal' => $kode
            ];

            // Insert per 500 rows to avoid memory issue
            if(count($batchData) >= 500) {
                $builder->updateBatch($batchData, 'id_konsumen');
                $batchData = [];
            }
        }
        
        if(count($batchData) > 0) {
            $builder->updateBatch($batchData, 'id_konsumen');
        }
    }

    private function generateUniqueKodeReferal($idKonsumen)
    {
        $characters = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789'; // Exclude 0,O,I,1
        do {
            $randomString = '';
            for ($i = 0; $i < 2; $i++) {
                $randomString .= $characters[rand(0, strlen($characters) - 1)];
            }
            // ID Pad to 4 digit
            $kode = $randomString . str_pad($idKonsumen, 4, '0', STR_PAD_LEFT);

            // Cek unique existing in db
            $exists = $this->db->table('konsumen')->where('kode_referal', $kode)->countAllResults();
        } while ($exists > 0);

        return $kode;
    }
    
    private function createMenus()
    {
        if (!$this->db->tableExists('menus')) return;
        
        $builder = $this->db->table('menus');
        
        // Cek apakah menu Member Get Member sudah ada
        $menuExists = $builder->where('url', 'member-get-member')->countAllResults();
        if ($menuExists == 0) {
            // Parent/Root level jika tidak ada sub menu
            $builder->insert([
                'name' => 'MGM (Referral)',
                'url' => 'member-get-member',
                'icon' => 'fas fa-users',
                'slug' => 'member-get-member',
                'parent_id' => 0,
                'is_active' => 1,
                'sort_order' => 10,
                'date_add' => date('Y-m-d H:i:s')
            ]);
            
            $menuId = $this->db->insertID();
            
            // Assign to role MKDT (4), Keuangan (3), (Asumsi role Promosi adalah group 7, atau jika blm ada akan kita inject manual nanti via UI)
            if ($this->db->tableExists('menu_roles')) {
                $roles = [3, 4];
                $menuRolesData = [];
                foreach($roles as $r) {
                    $menuRolesData[] = [
                        'id_groups' => $r,
                        'id_menu' => $menuId
                    ];
                }
                $this->db->table('menu_roles')->insertBatch($menuRolesData);
            }
        }
    }
}
