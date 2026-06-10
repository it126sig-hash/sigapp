<?php

namespace App\Repositories;

use CodeIgniter\Model;

class KeuanganRepository extends Model
{
    protected $table = 'keuangan';
    protected $primaryKey = 'id_keuangan';
    protected $returnType = 'object';

    //ambil semua tagihan
    public function getTagihanById(int $idMkdt)
    {
        return $this->select(['keuangan.*', 'users.username'])
            ->join('users', 'users.id = keuangan.add_by')
            ->where('keuangan.id_mkdt', $idMkdt)
            ->findAll();
    }
    //hanya tagihan turun kpr
    public function getTagihanTurunKPRById(int $idMkdt)
    {
        //untuk
        return $this->select(['keuangan.*', 'users.username'])
            ->join('users', 'users.id = keuangan.add_by')
            ->where('keuangan.id_mkdt', $idMkdt)
            ->where('keuangan.berita_acara', 'Turun KPR')
            ->first();
    }
    function getAllJatuhTempo($id_proyek)
    {
        return $this->select('
            keuangan.berita_acara,
            keuangan.jatuh_tempo_tgl,
            keuangan.nominal,
            keuangan.keterangan,
            c.nama_konsumen,
            c.hp_konsumen,
            c.alamat_konsumen,
            k.no_kavling,
            hj.id_tipe,
            j.nama_jalan,
            cl.nama_cluster,
            m.id_mkdt,
            nama_proyek
        ')
            ->join('mkdt m', 'm.id_mkdt = keuangan.id_mkdt')
            ->join('kavling k', 'm.id_mkdt = k.id_mkdt')
            ->join('jalan j', 'k.id_jalan = j.id_jalan')
            ->join('cluster cl', 'cl.id_cluster = j.id_cluster')
            ->join('proyek p', 'p.id_proyek = cl.id_proyek')
            ->join('konsumen c', 'c.id_konsumen = m.id_konsumen')
            ->join('hargajual hj', 'hj.id = k.harga_akhir')
            ->where('keuangan.sudah_dibayar', 0)
            ->where('keuangan.jatuh_tempo_tgl <=', date('Y-m-d', strtotime('+7 days')))
            ->where('p.id_proyek', $id_proyek)
            ->orderBy('keuangan.jatuh_tempo_tgl', 'ASC')
            // ->orderBy('m.id_mkdt', 'ASC')
            ->get()->getResult()
        ;
    }
    public function getLIKeu()
    {
        //ambil list item keuangan
        return  $this->db->table('keuangan_item_list')
            ->select("*")
            ->where('deleted_at', null)
            ->orderBy('id_keuangan_item_list', 'ASC')
            ->get()->getResult();
    }
    public function getBelumLunasQuery()
    {
        //ambil list item keuangan
        return $this->select('
            j.nama_jalan,
            k.no_kavling,
            hj.id_tipe,
            c.nama_konsumen,
            m.booking_tgl,

            m.is_kpr,
            keuangan.jatuh_tempo_tgl,
            
            keuangan.nominal,
            "" as total_tagihan,
            "" as sudah_bayar,
            "" as sisa_tagihan,

            keuangan.berita_acara,
            keuangan.keterangan,


            c.alamat_konsumen,
            c.hp_konsumen,


            (m.harga_uang_muka - m.harga_diskon_uang_muka - m.harga_sbum) as um,
            (m.harga_administrasi) as adm,
            (m.harga_bphtb + m.harga_biaya_proses + m.harga_ppn + m.harga_penambahan_um +m.harga_penambahan +m.harga_penambahan_tanah) as bb,

            mps.total_um,
            mps.total_adm,
            mps.total_bb,

            a.username as uadd_by,
            b.username as uedit_by,
            tipe.no_tipe_rumah,
            m.id_mkdt,
            p.nama_proyek,
        ')
            ->join('mkdt m', 'm.id_mkdt = keuangan.id_mkdt')
            ->join('kavling k', 'm.id_mkdt = k.id_mkdt')
            ->join('jalan j', 'k.id_jalan = j.id_jalan')
            ->join('cluster cl', 'cl.id_cluster = j.id_cluster')
            ->join('proyek p', 'p.id_proyek = cl.id_proyek')
            ->join('konsumen c', 'c.id_konsumen = m.id_konsumen')
            ->join('hargajual hj', 'hj.id = k.harga_akhir')
            ->where('keuangan.sudah_dibayar', 0)
            ->join('tipe', "tipe.id_tipe = k.id_tipe", 'left')
            ->join('users a', "a.id = m.add_by", 'left')
            ->join('users b', "b.id = m.edit_by", 'left')
            ->join('mkdt_payment_summary mps', "mps.id_mkdt = m.id_mkdt", 'left')
            ->orderBy('jatuh_tempo_tgl', 'ASC')
            ->orderBy('jatuh_tempo_tgl', 'ASC')
            ->where('m.status_mkdt!=', "Batal")
            ->where('
            (m.harga_uang_muka - m.harga_diskon_uang_muka - m.harga_sbum) +
            (m.harga_administrasi) +
            (m.harga_bphtb + m.harga_biaya_proses + m.harga_ppn + m.harga_penambahan_um +m.harga_penambahan +m.harga_penambahan_tanah)
            >
            ', 0)

            ->where('m.is_lunas', "0");
        // return  $this->db->table('mkdt')
        //     ->select('
        //     jalan.nama_jalan,
        //     kavling.no_kavling,
        //     hargajual.id_tipe,
        //     konsumen.nama_konsumen,
        //     mkdt.booking_tgl,
        //     mkdt.is_kpr,
        //     (SELECT jatuh_tempo_tgl FROM keuangan WHERE id_mkdt = mkdt.id_mkdt AND sudah_dibayar = 0 ORDER BY jatuh_tempo_tgl ASC LIMIT 1) as jatuh_tempo_tgl,

        //     "" as total_tagihan,
        //     "" as sudah_bayar,
        //     "" as sisa_tagihan,

        //     (mkdt.harga_uang_muka - mkdt.harga_diskon_uang_muka - mkdt.harga_sbum) as um,
        //     (mkdt.harga_administrasi) as adm,
        //     (mkdt.harga_bphtb + mkdt.harga_biaya_proses + mkdt.harga_ppn + mkdt.harga_penambahan_um +mkdt.harga_penambahan +mkdt.harga_penambahan_tanah) as bb,

        //     mps.total_um,
        //     mps.total_adm,
        //     mps.total_bb,

        //     mkdt.id_mkdt,
        //     a.username as uadd_by,
        //     b.username as uedit_by, 
        //     proyek.nama_proyek,
        //     tipe.no_tipe_rumah
        //     ')
        //     ->join('kavling', "kavling.id_mkdt = mkdt.id_mkdt")
        //     ->join('hargajual', "mkdt.id_hargajual = hargajual.id", 'left')
        //     ->join('konsumen', "konsumen.id_konsumen = mkdt.id_konsumen", 'left')
        //     ->join('jalan', "jalan.id_jalan = kavling.id_jalan", 'left')
        //     ->join('tipe', "tipe.id_tipe = kavling.id_tipe", 'left')
        //     ->join('cluster', "jalan.id_cluster = cluster.id_cluster", 'left')
        //     ->join('proyek', "proyek.id_proyek = cluster.id_proyek", 'left')
        //     ->join('users a', "a.id = mkdt.add_by", 'left')
        //     ->join('users b', "b.id = mkdt.edit_by", 'left')
        //     ->join('mkdt_payment_summary mps', "mps.id_mkdt = mkdt.id_mkdt", 'left')
        //     ->orderBy('jatuh_tempo_tgl', 'ASC')
        //     ->where('mkdt.status_mkdt!=', "Batal")
        //     ->where('
        //     (mkdt.harga_uang_muka - mkdt.harga_diskon_uang_muka - mkdt.harga_sbum) +
        //     (mkdt.harga_administrasi) +
        //     (mkdt.harga_bphtb + mkdt.harga_biaya_proses + mkdt.harga_ppn + mkdt.harga_penambahan_um +mkdt.harga_penambahan +mkdt.harga_penambahan_tanah)
        //     >
        //     ', 0)

        //     ->where('mkdt.is_lunas', "0");
    }

    public function getBelumLunasGroupedQuery()
    {
        // Subquery: agregat tagihan belum lunas per id_mkdt
        $subQuery = $this->db->table('keuangan')
            ->select('id_mkdt, MIN(jatuh_tempo_tgl) AS jatuh_tempo_tgl, COUNT(*) AS jumlah_tagihan')
            ->where('sudah_dibayar', 0)
            ->groupBy('id_mkdt')
            ->getCompiledSelect();

        return $this->db->table("mkdt m")
            ->select('
                j.nama_jalan,
                k.no_kavling,
                hj.id_tipe,
                c.nama_konsumen,
                m.booking_tgl,
                m.is_kpr,
                keu_agg.jatuh_tempo_tgl,
                keu_agg.jumlah_tagihan,
                "" as total_tagihan,
                "" as sudah_bayar,
                "" as sisa_tagihan,
                (m.harga_uang_muka - m.harga_diskon_uang_muka - m.harga_sbum) as um,
                (m.harga_administrasi) as adm,
                (m.harga_bphtb + m.harga_biaya_proses + m.harga_ppn + m.harga_penambahan_um + m.harga_penambahan + m.harga_penambahan_tanah) as bb,
                mps.total_um,
                mps.total_adm,
                mps.total_bb,
                tipe.no_tipe_rumah,
                m.id_mkdt,
                p.nama_proyek
            ')
            ->join("({$subQuery}) keu_agg", 'keu_agg.id_mkdt = m.id_mkdt', 'inner')
            ->join('kavling k', 'k.id_mkdt = m.id_mkdt')
            ->join('jalan j', 'j.id_jalan = k.id_jalan')
            ->join('cluster cl', 'cl.id_cluster = j.id_cluster')
            ->join('proyek p', 'p.id_proyek = cl.id_proyek')
            ->join('konsumen c', 'c.id_konsumen = m.id_konsumen')
            ->join('hargajual hj', 'hj.id = k.harga_akhir')
            ->join('tipe', 'tipe.id_tipe = k.id_tipe', 'left')
            ->join('users a', 'a.id = m.add_by', 'left')
            ->join('users b', 'b.id = m.edit_by', 'left')
            ->join('mkdt_payment_summary mps', 'mps.id_mkdt = m.id_mkdt', 'left')
            ->where('m.status_mkdt !=', 'Batal')
            ->where('m.is_lunas', '0')
            ->where('
                (m.harga_uang_muka - m.harga_diskon_uang_muka - m.harga_sbum) +
                (m.harga_administrasi) +
                (m.harga_bphtb + m.harga_biaya_proses + m.harga_ppn + m.harga_penambahan_um + m.harga_penambahan + m.harga_penambahan_tanah)
                >
            ', 0)
            ->orderBy('keu_agg.jatuh_tempo_tgl', 'ASC');
    }

    //tanpa tagihan turun kpr
    public function getTagihanOnlyByID(int $idMkdt)
    {
        //untuk 
        return $this->select(['keuangan.*', 'users.username'])
            ->join('users', 'users.id = keuangan.add_by')
            ->where('keuangan.id_mkdt', $idMkdt)
            ->where('berita_acara !=', 'Turun KPR')
            ->findAll();
    }
}
