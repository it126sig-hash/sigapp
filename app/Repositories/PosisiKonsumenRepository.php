<?php

namespace App\Repositories;

use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Database\BaseBuilder;

class PosisiKonsumenRepository
{
    protected $db;
    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }
    public function getBaseQuery($status = "Booking")
    {
        //pembagian um, adm, bb itu masih per field table, seharusnya di ubah ke dinamis agar sesuai dengan summary table
        //per tnalggal 29 des 2025, untuk sementara sbum di jadikan diskon uang muka(perlu konfirmasi ke mkdt/keuangan)
        //st jalan adalah status listrik
        return $this->db->table('mkdt')
            ->select('
            "" as action,
            kavling.id_kavling,
            jalan.nama_jalan,
            kavling.no_kavling,
            hargajual.id_tipe,
            konsumen.nama_konsumen,
            konsumen.kode_referal,
            konsumen.sales,
            mkdt.booking_tgl,
            mkdt.wawancara_tgl,
            mkdt.akad_tgl,
            mkdt.is_kpr,
            list_bank.bank as bank,
            mkdt.keterangan,
            mkdt.sp3k_tgl,
            mkdt.sp3k_tgl_exp,
            "" as sikasep,
            "" as tunai,
            GREATEST(COALESCE(mkdt.harga_uang_muka, 0) - COALESCE(mkdt.harga_diskon_uang_muka, 0) - COALESCE(mkdt.harga_sbum, 0), 0) as um,
            COALESCE(mkdt.harga_administrasi, 0) as adm,
            (COALESCE(mkdt.harga_bphtb, 0) + COALESCE(mkdt.harga_biaya_proses, 0) + COALESCE(mkdt.harga_ppn, 0)
                + COALESCE(mkdt.harga_penambahan, 0) + COALESCE(mkdt.harga_penambahan_tanah, 0)
                + CASE WHEN COALESCE(mkdt.harga_kpr_acc, 0) > 0
                    THEN GREATEST(COALESCE(mkdt.harga_kpr, 0) - COALESCE(mkdt.harga_kpr_acc, 0), 0)
                    ELSE 0 END) as bb,
            produksi.progres_bangunan,
            produksi.lpa,
            produksi.st_jalan as st_listrik,
            "" as st_jalan,
            legal.sertifikat_split_no_hgb,
            legal.sertifikat_split_nib,
            legal.pbg_no,
            legal.pbb_pecah_nop,
            "" as sikumbang,
            mkdt.keterangan_status,
            mkdt.akad_indent,
            COALESCE(mps.total_um, 0) as total_um,
            COALESCE(mps.total_adm, 0) as total_adm,
            COALESCE(mps.total_bb, 0) as total_bb,

            mkdt.id_mkdt,

            tipe.lb,
            tipe.lt,
            tipe.tipe_rumah,
            tipe.no_tipe_rumah,
            kavling.id_produksi,
            kavling.id_legal,
            kavling.id_keuangan,
            kavling.id_komplain,
            kavling.id_jalan,
            jalan.id_cluster,
            hargajual.id as id_hargajual,
            hargajual.hargajual as harga_akhir,

            a.username as uadd_by,
            b.username as uedit_by,
            produksi.lpa_tanggal,
            proyek.id_proyek,
            proyek.nama_proyek
            ')
            ->join('kavling', "kavling.id_mkdt = mkdt.id_mkdt")
            ->join('hargajual', "mkdt.id_hargajual = hargajual.id")
            ->join('list_bank', 'list_bank.id = mkdt.id_bank', 'left')
            ->join('produksi', "kavling.id_produksi = produksi.id_produksi", 'left')
            ->join('tipe', "tipe.id_tipe = kavling.id_tipe")
            ->join('konsumen', "konsumen.id_konsumen = mkdt.id_konsumen", 'left')
            ->join('legal', "kavling.id_legal = legal.id_legal", 'left')
            ->join('jalan', "jalan.id_jalan = kavling.id_jalan", 'left')
            ->join('cluster', "jalan.id_cluster = cluster.id_cluster", 'left')
            ->join('proyek', "proyek.id_proyek = cluster.id_proyek", 'left')
            ->join('users a', "a.id = mkdt.add_by", 'left')
            ->join('users b', "b.id = mkdt.edit_by", 'left')
            ->join('mkdt_payment_summary mps', "mps.id_mkdt = mkdt.id_mkdt", 'left')
            ->where('mkdt.status_mkdt', $status)
            ->orderBy('jalan.nama_jalan', 'ASC')
            ->orderBy('ABS(kavling.no_kavling)', 'ASC');
    }
    public function getQueryBatal()
    {
        return $this->db->table('mkdt')
            ->select('
            jalan.nama_jalan,
            kavling.no_kavling,
            tipe.tipe_rumah,
            mkdt.keterangan_batal,
            "" as tanggal_batal,
            mkdt.perlu_refund,
            konsumen.nama_konsumen,
            mkdt.booking_tgl,
            mkdt.is_kpr,
            "" as total_tagihan,
            "" as sudah_bayar,
            "" as sisa_tagihan,

            "" as action,
            kavling.id_kavling,

            GREATEST(COALESCE(mkdt.harga_uang_muka, 0) - COALESCE(mkdt.harga_diskon_uang_muka, 0) - COALESCE(mkdt.harga_sbum, 0), 0) as um,
            COALESCE(mkdt.harga_administrasi, 0) as adm,
            (COALESCE(mkdt.harga_bphtb, 0) + COALESCE(mkdt.harga_biaya_proses, 0) + COALESCE(mkdt.harga_ppn, 0)
                + COALESCE(mkdt.harga_penambahan, 0) + COALESCE(mkdt.harga_penambahan_tanah, 0)
                + CASE WHEN COALESCE(mkdt.harga_kpr_acc, 0) > 0
                    THEN GREATEST(COALESCE(mkdt.harga_kpr, 0) - COALESCE(mkdt.harga_kpr_acc, 0), 0)
                    ELSE 0 END) as bb,

            COALESCE(mps.total_um, 0) as total_um,
            COALESCE(mps.total_adm, 0) as total_adm,
            COALESCE(mps.total_bb, 0) as total_bb,

            mkdt.id_mkdt,
            mkdt.mkdt_batal_tgl,

            tipe.no_tipe_rumah,
            kavling.id_produksi,
            kavling.id_legal,
            kavling.id_keuangan,
            kavling.id_komplain,
            kavling.id_jalan,
            jalan.id_cluster,
            hargajual.id as id_hargajual,
            hargajual.hargajual as harga_akhir,

            a.username as uadd_by,
            b.username as uedit_by,
            user_batal.username as nama_pembatal,
            produksi.lpa_tanggal,
            proyek.nama_proyek
            ')
            ->join('kavling', "kavling.id_kavling = mkdt.id_kavling")
            ->join('hargajual', "mkdt.id_hargajual = hargajual.id", 'left')
            ->join('produksi', "kavling.id_produksi = produksi.id_produksi", 'left')
            ->join('tipe', "tipe.id_tipe = kavling.id_tipe")
            ->join('konsumen', "konsumen.id_konsumen = mkdt.id_konsumen", 'left')

            ->join('jalan', "jalan.id_jalan = kavling.id_jalan", 'left')
            ->join('cluster', "jalan.id_cluster = cluster.id_cluster", 'left')
            ->join('proyek', "proyek.id_proyek = cluster.id_proyek", 'left')
            ->join('users a', "a.id = mkdt.add_by", 'left')
            ->join('users b', "b.id = mkdt.edit_by", 'left')
            ->join('users user_batal', "user_batal.id = mkdt.mkdt_batal_oleh", 'left')
            ->join('mkdt_payment_summary mps', "mps.id_mkdt = mkdt.id_mkdt", 'left')
            ->where('mkdt.status_mkdt', "Batal");
    }

    function getRiwayatExport($id_proyek, $status)
    {
        if ($id_proyek == null) {
            return $this->db->table('riwayat_export_poskon r')
                ->select('r.*, p.nama_proyek, u.username as export_by')
                ->join('proyek p', 'p.id_proyek = r.id_proyek', 'left')
                ->join('users u', 'u.id = r.export_by', 'left')
                ->orderBy('r.export_tgl', 'desc')
                ->where('r.status', $status)
                ->limit(20)
                ->get()
                ->getResult();
        }
        return $this->db->table('riwayat_export_poskon r')
            ->select('r.*, p.nama_proyek, u.username as export_by')
            ->join('proyek p', 'p.id_proyek = r.id_proyek', 'left')
            ->join('users u', 'u.id = r.export_by', 'left')
            ->where('r.id_proyek', $id_proyek)
            ->where('r.status', $status)
            ->orderBy('r.export_tgl', 'desc')
            ->limit(20)
            ->get()
            ->getResult();
    }

    function insertRiwayatExport($data)
    {
        return $this->db->table('riwayat_export_poskon')->insert($data);
    }
}
