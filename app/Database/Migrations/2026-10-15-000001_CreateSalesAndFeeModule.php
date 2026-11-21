<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSalesAndFeeModule extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();

        // 1. Kolom tambahan pada mkdt
        if (! $this->db->fieldExists('harga_net_ajb', 'mkdt')) {
            $this->forge->addColumn('mkdt', [
                'harga_net_ajb' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '15,2',
                    'default'    => 0.00,
                    'null'       => false,
                    'after'      => 'harga_jual_net',
                ],
            ]);
        }

        // 2. Kolom tambahan pada konsumen
        $konsumenCols = [];
        if (! $this->db->fieldExists('keterangan_sales', 'konsumen')) {
            $konsumenCols['keterangan_sales'] = [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'after'      => 'sales',
            ];
        }
        if (! $this->db->fieldExists('id_sales', 'konsumen')) {
            $konsumenCols['id_sales'] = [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'sales',
            ];
        }
        if (! empty($konsumenCols)) {
            $this->forge->addColumn('konsumen', $konsumenCols);
        }

        // 3. Tabel Master Sales External
        if (! $this->db->tableExists('sales')) {
            $this->forge->addField([
                'id'              => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
                'nama'            => ['type' => 'VARCHAR', 'constraint' => 100],
                'kontak'          => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
                'bank'            => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
                'no_rek'          => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
                'proyek'          => ['type' => 'TEXT', 'null' => true],
                'fee_subsidi'     => ['type' => 'DECIMAL', 'constraint' => '5,2', 'default' => 0.00],
                'fee_komersil'    => ['type' => 'DECIMAL', 'constraint' => '5,2', 'default' => 0.00],
                'tanggal_pks'     => ['type' => 'DATE', 'null' => true],
                'tanggal_exp_pks' => ['type' => 'DATE', 'null' => true],
                'lampiran_pks'    => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
                'status'          => ['type' => 'ENUM', 'constraint' => ['active', 'inactive'], 'default' => 'active'],
                'catatan'         => ['type' => 'TEXT', 'null' => true],
                'created_by'      => ['type' => 'INT', 'unsigned' => true, 'null' => true],
                'updated_by'      => ['type' => 'INT', 'unsigned' => true, 'null' => true],
                'created_at'      => ['type' => 'DATETIME', 'null' => true],
                'updated_at'      => ['type' => 'DATETIME', 'null' => true],
                'deleted_at'      => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addKey('nama');
            $this->forge->createTable('sales', true);
        }

        // 4. Tabel Riwayat Perubahan Fee & PKS
        if (! $this->db->tableExists('sales_history')) {
            $this->forge->addField([
                'id'                   => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
                'id_sales'             => ['type' => 'INT', 'unsigned' => true],
                'fee_subsidi_lama'     => ['type' => 'DECIMAL', 'constraint' => '5,2', 'null' => true],
                'fee_subsidi_baru'     => ['type' => 'DECIMAL', 'constraint' => '5,2', 'null' => true],
                'fee_komersil_lama'    => ['type' => 'DECIMAL', 'constraint' => '5,2', 'null' => true],
                'fee_komersil_baru'    => ['type' => 'DECIMAL', 'constraint' => '5,2', 'null' => true],
                'tanggal_pks_lama'     => ['type' => 'DATE', 'null' => true],
                'tanggal_pks_baru'     => ['type' => 'DATE', 'null' => true],
                'tanggal_exp_pks_lama' => ['type' => 'DATE', 'null' => true],
                'tanggal_exp_pks_baru' => ['type' => 'DATE', 'null' => true],
                'lampiran_pks_lama'    => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
                'lampiran_pks_baru'    => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
                'catatan'              => ['type' => 'TEXT', 'null' => true],
                'created_by'           => ['type' => 'INT', 'unsigned' => true, 'null' => true],
                'created_at'           => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addKey('id_sales');
            $this->forge->createTable('sales_history', true);
        }

        // 5. Tabel Header Fee Sales
        if (! $this->db->tableExists('fee_sales')) {
            $this->forge->addField([
                'id'               => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
                'id_mkdt'          => ['type' => 'INT', 'constraint' => 11],
                'id_sales'         => ['type' => 'INT', 'unsigned' => true, 'null' => true],
                'sales_nama'       => ['type' => 'VARCHAR', 'constraint' => 100],
                'sales_keterangan' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
                'harga_net_ajb'    => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0.00],
                'persentase_fee'   => ['type' => 'DECIMAL', 'constraint' => '5,2', 'default' => 0.00],
                'total_fee'        => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0.00],
                'skema_termin'     => ['type' => 'ENUM', 'constraint' => ['100', '30_60_10', 'custom'], 'default' => '100'],
                'status'           => ['type' => 'ENUM', 'constraint' => ['draft', 'proses_termin', 'selesai', 'dibatalkan'], 'default' => 'draft'],
                'catatan'          => ['type' => 'TEXT', 'null' => true],
                'created_by'       => ['type' => 'INT', 'unsigned' => true, 'null' => true],
                'updated_by'       => ['type' => 'INT', 'unsigned' => true, 'null' => true],
                'created_at'       => ['type' => 'DATETIME', 'null' => true],
                'updated_at'       => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addUniqueKey('id_mkdt');
            $this->forge->addKey('id_sales');
            $this->forge->createTable('fee_sales', true);
        }

        // 6. Tabel Detail Termin Fee Sales
        if (! $this->db->tableExists('fee_sales_termin')) {
            $this->forge->addField([
                'id'                   => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
                'id_fee_sales'         => ['type' => 'INT', 'unsigned' => true],
                'termin_ke'            => ['type' => 'TINYINT', 'unsigned' => true],
                'persen'               => ['type' => 'DECIMAL', 'constraint' => '5,2', 'default' => 0.00],
                'nominal'              => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0.00],
                'status'               => ['type' => 'ENUM', 'constraint' => ['belum_diajukan', 'pengajuan_spp', 'sudah_cair'], 'default' => 'belum_diajukan'],
                'nomor_spp'            => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
                'tanggal_pengajuan_spp'=> ['type' => 'DATE', 'null' => true],
                'tanggal_cair'         => ['type' => 'DATE', 'null' => true],
                'keterangan'           => ['type' => 'TEXT', 'null' => true],
                'lampiran_spp'         => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
                'lampiran_bukti_cair'  => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
                'created_by'           => ['type' => 'INT', 'unsigned' => true, 'null' => true],
                'updated_by'           => ['type' => 'INT', 'unsigned' => true, 'null' => true],
                'created_at'           => ['type' => 'DATETIME', 'null' => true],
                'updated_at'           => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addKey('id_fee_sales');
            $this->forge->createTable('fee_sales_termin', true);
        }

        // 7. Menu Navigation
        // A. Menu Master Sales (Role 1: Admin, 4: MKDT)
        $masterSalesMenu = $db->table('menus')->where('url', 'master-sales')->get()->getRow();
        if (! $masterSalesMenu) {
            $db->table('menus')->insert([
                'name'       => 'Master Sales',
                'url'        => 'master-sales',
                'icon'       => 'users',
                'parent_id'  => 0,
                'is_active'  => 1,
                'sort_order' => 25,
            ]);
            $msId = $db->insertID();
            $db->table('menu_roles')->insertBatch([
                ['id_groups' => 1, 'id_menu' => $msId],
                ['id_groups' => 4, 'id_menu' => $msId],
            ]);
        }

        // B. Menu Fee Sales (Role 1: Admin, 2: Umum, 3: Keuangan, 4: MKDT)
        $feeSalesMenu = $db->table('menus')->where('url', 'fee-sales')->get()->getRow();
        if (! $feeSalesMenu) {
            $db->table('menus')->insert([
                'name'       => 'Fee Sales',
                'url'        => 'fee-sales',
                'icon'       => 'percent',
                'parent_id'  => 0,
                'is_active'  => 1,
                'sort_order' => 26,
            ]);
            $fsId = $db->insertID();
            $db->table('menu_roles')->insertBatch([
                ['id_groups' => 1, 'id_menu' => $fsId],
                ['id_groups' => 2, 'id_menu' => $fsId],
                ['id_groups' => 3, 'id_menu' => $fsId],
                ['id_groups' => 4, 'id_menu' => $fsId],
            ]);
        }
    }

    public function down()
    {
        $db = \Config\Database::connect();

        // Hapus menu
        $menuUrls = ['master-sales', 'fee-sales'];
        $menus = $db->table('menus')->whereIn('url', $menuUrls)->get()->getResult();
        foreach ($menus as $m) {
            $db->table('menu_roles')->where('id_menu', $m->id)->delete();
            $db->table('menus')->where('id', $m->id)->delete();
        }

        $this->forge->dropTable('fee_sales_termin', true);
        $this->forge->dropTable('fee_sales', true);
        $this->forge->dropTable('sales_history', true);
        $this->forge->dropTable('sales', true);

        if ($this->db->fieldExists('harga_net_ajb', 'mkdt')) {
            $this->forge->dropColumn('mkdt', 'harga_net_ajb');
        }
        if ($this->db->fieldExists('keterangan_sales', 'konsumen')) {
            $this->forge->dropColumn('konsumen', 'keterangan_sales');
        }
        if ($this->db->fieldExists('id_sales', 'konsumen')) {
            $this->forge->dropColumn('konsumen', 'id_sales');
        }
    }
}
