<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class FlagLegacyFeeSalesAkad2025 extends Migration
{
    public function up()
    {
        // a. Cek apakah kolom is_fee_sales_selesai ada di tabel mkdt. Jika belum ada, tambahkan.
        if ($this->db->tableExists('mkdt')) {
            if (! $this->db->fieldExists('is_fee_sales_selesai', 'mkdt')) {
                $this->forge->addColumn('mkdt', [
                    'is_fee_sales_selesai' => [
                        'type'       => 'TINYINT',
                        'constraint' => 1,
                        'default'    => 0,
                        'null'       => false,
                        'after'      => 'is_ajb',
                    ],
                ]);
            }

            $indexes = $this->db->query("SHOW INDEX FROM mkdt WHERE Key_name = 'idx_mkdt_fee_sales_selesai'")->getResultArray();
            if (empty($indexes)) {
                $this->db->query('CREATE INDEX idx_mkdt_fee_sales_selesai ON mkdt (is_fee_sales_selesai)');
            }
        }

        $this->db->transStart();

        // b. Ambil seluruh transaksi mkdt legacy akad <= 31-12-2025
        $builder = $this->db->table('mkdt m')
            ->select('m.id_mkdt, m.status_mkdt, m.is_ajb, m.akad_tgl, m.akad_indent_tgl, m.harga_net_ajb, m.harga_jual_net, m.harga_jual, m.is_subsidi, k.id_sales, k.sales AS sales_konsumen, s.nama AS master_sales_nama, s.fee_subsidi, s.fee_komersil')
            ->join('konsumen k', 'k.id_konsumen = m.id_konsumen', 'left')
            ->join('sales s', 's.id = k.id_sales', 'left')
            ->where('m.is_batal', 0)
            ->where('(m.status_mkdt = "Akad" OR m.is_ajb IN ("AJB", "PPJB", "1") OR m.akad_tgl IS NOT NULL OR m.akad = 1 OR m.akad_indent = 1)')
            ->where('(
                (m.akad_tgl IS NOT NULL AND m.akad_tgl > "1970-01-01" AND m.akad_tgl <= "2025-12-31")
                OR ((m.akad_tgl IS NULL OR m.akad_tgl <= "1970-01-01") AND m.akad_indent_tgl IS NOT NULL AND m.akad_indent_tgl > "1970-01-01" AND m.akad_indent_tgl <= "2025-12-31")
            )');

        $rows = $builder->get()->getResult();

        if (! empty($rows)) {
            $idMkdtList = array_map(static fn ($r) => (int) $r->id_mkdt, $rows);

            // c. Update mkdt.is_fee_sales_selesai = 1 untuk id_mkdt tersebut
            foreach (array_chunk($idMkdtList, 500) as $chunkIds) {
                $this->db->table('mkdt')
                    ->whereIn('id_mkdt', $chunkIds)
                    ->update(['is_fee_sales_selesai' => 1]);
            }

            // d. Untuk unit yang belum memiliki record di fee_sales, insert ke fee_sales & fee_sales_termin
            $existingFeeSales = $this->db->table('fee_sales')
                ->select('id_mkdt')
                ->whereIn('id_mkdt', $idMkdtList)
                ->get()
                ->getResultArray();

            $existingMap = array_flip(array_column($existingFeeSales, 'id_mkdt'));
            $now = date('Y-m-d H:i:s');

            foreach ($rows as $row) {
                $idMkdt = (int) $row->id_mkdt;
                if (isset($existingMap[$idMkdt])) {
                    continue;
                }

                $hNetAjb  = (float) ($row->harga_net_ajb ?? 0);
                $hJualNet = (float) ($row->harga_jual_net ?? 0);
                $hJual    = (float) ($row->harga_jual ?? 0);
                $hargaNet = $hNetAjb > 0 ? $hNetAjb : ($hJualNet > 0 ? $hJualNet : $hJual);

                $isSubsidi   = (bool) ($row->is_subsidi ?? 0);
                $feeSubsidi  = ! empty($row->fee_subsidi) ? (float) $row->fee_subsidi : 1.5;
                $feeKomersil = ! empty($row->fee_komersil) ? (float) $row->fee_komersil : 2.0;
                $persentase  = $isSubsidi ? $feeSubsidi : $feeKomersil;

                $totalFee = round(($hargaNet * $persentase) / 100, 2);

                $salesKonsumen   = trim((string) ($row->sales_konsumen ?? ''));
                $masterSalesNama = trim((string) ($row->master_sales_nama ?? ''));
                $salesNama       = $salesKonsumen !== '' ? $salesKonsumen : ($masterSalesNama !== '' ? $masterSalesNama : 'Sales External (Legacy)');
                $idSales         = ! empty($row->id_sales) ? (int) $row->id_sales : null;

                $tglAkad = (! empty($row->akad_tgl) && $row->akad_tgl > '1970-01-01')
                    ? $row->akad_tgl
                    : ((! empty($row->akad_indent_tgl) && $row->akad_indent_tgl > '1970-01-01') ? $row->akad_indent_tgl : date('Y-m-d'));

                $this->db->table('fee_sales')->insert([
                    'id_mkdt'          => $idMkdt,
                    'id_sales'         => $idSales,
                    'sales_nama'       => $salesNama,
                    'sales_keterangan' => 'Akad legacy <= 31-12-2025',
                    'harga_net_ajb'    => $hargaNet,
                    'persentase_fee'   => $persentase,
                    'total_fee'        => $totalFee,
                    'skema_termin'     => '100',
                    'status'           => 'selesai',
                    'catatan'          => 'Data legacy akad selesai fee sales (<= 31-12-2025)',
                    'created_at'       => $now,
                    'updated_at'       => $now,
                ]);

                $feeId = $this->db->insertID();

                $this->db->table('fee_sales_termin')->insert([
                    'id_fee_sales'          => $feeId,
                    'termin_ke'             => 1,
                    'persen'                => 100.00,
                    'nominal'               => $totalFee,
                    'status'                => 'sudah_cair',
                    'nomor_spp'             => 'SPP-LEGACY-2025',
                    'tanggal_pengajuan_spp' => $tglAkad,
                    'tanggal_cair'          => $tglAkad,
                    'keterangan'            => 'Pencairan komisi selesai (data lampau sebelum 2026)',
                    'created_at'            => $now,
                    'updated_at'            => $now,
                ]);

                $existingMap[$idMkdt] = true;
            }
        }

        $this->db->transComplete();
    }

    public function down()
    {
        $this->db->transStart();

        $legacyFees = $this->db->table('fee_sales')
            ->select('id')
            ->where('catatan', 'Data legacy akad selesai fee sales (<= 31-12-2025)')
            ->get()
            ->getResultArray();

        $legacyFeeIds = array_column($legacyFees, 'id');

        if (! empty($legacyFeeIds)) {
            // a. Hapus entri fee_sales_termin
            $this->db->table('fee_sales_termin')
                ->whereIn('id_fee_sales', $legacyFeeIds)
                ->delete();

            // b. Hapus entri fee_sales
            $this->db->table('fee_sales')
                ->whereIn('id', $legacyFeeIds)
                ->delete();
        }

        // c. Reset mkdt.is_fee_sales_selesai = 0
        if ($this->db->tableExists('mkdt') && $this->db->fieldExists('is_fee_sales_selesai', 'mkdt')) {
            $this->db->table('mkdt')->update(['is_fee_sales_selesai' => 0]);
        }

        $this->db->transComplete();

        // d. Drop column is_fee_sales_selesai jika ada
        if ($this->db->tableExists('mkdt') && $this->db->fieldExists('is_fee_sales_selesai', 'mkdt')) {
            $indexes = $this->db->query("SHOW INDEX FROM mkdt WHERE Key_name = 'idx_mkdt_fee_sales_selesai'")->getResultArray();
            if (! empty($indexes)) {
                $this->db->query('ALTER TABLE mkdt DROP INDEX idx_mkdt_fee_sales_selesai');
            }
            $this->forge->dropColumn('mkdt', 'is_fee_sales_selesai');
        }
    }
}
