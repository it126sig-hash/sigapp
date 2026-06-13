<?php

namespace App\Repositories;

use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Database\BaseBuilder;
use App\Models\KavlingModel;

class KavlingRepository
{
    protected $db;
    protected $model;
    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->model = model(KavlingModel::class);
    }

    public function getKavlingByIdMkdt(int $id_mkdt): ?array
    {
        return $this->db->table('kavling')
            ->select('
                        kavling.id_mkdt,
                        kavling.no_kavling, 
                        jalan.nama_jalan, 
                        tipe.id_tipe,
                        tipe.tipe_rumah,
                        tipe.lb,
                        tipe.lt
                    ')
            ->join('jalan', 'jalan.id_jalan = kavling.id_jalan')
            ->join('cluster', 'cluster.id_cluster = jalan.id_cluster')
            ->join('proyek', 'proyek.id_proyek = cluster.id_proyek')
            ->join('tipe', 'kavling.id_tipe = tipe.id_tipe')
            ->where('kavling.id_mkdt', $id_mkdt)
            ->get()->getResult();
    }
    public function getKavlingById(int $idKavling): ?object
    {
        return $this->model->select('
                        kavling.id_mkdt,
                        kavling.no_kavling, 
                        jalan.nama_jalan, 
                        tipe.id_tipe,
                        tipe.tipe_rumah,
                        tipe.lb,
                        tipe.lt
                    ')
            ->join('jalan', 'jalan.id_jalan = kavling.id_jalan')
            ->join('cluster', 'cluster.id_cluster = jalan.id_cluster')
            ->join('proyek', 'proyek.id_proyek = cluster.id_proyek')
            ->join('tipe', 'kavling.id_tipe = tipe.id_tipe')
            ->where('kavling.id_kavling', $idKavling)
            ->first();
    }
    public function getKavlingByIds(array $idKavlings): ?array
    {
        return $this->model->select('
                        kavling.id_kavling,
                        kavling.id_mkdt,
                        kavling.no_kavling, 
                        jalan.nama_jalan, 
                        tipe.id_tipe,
                        tipe.tipe_rumah,
                        tipe.lb,
                        tipe.lt
                    ')
            ->join('jalan', 'jalan.id_jalan = kavling.id_jalan')
            ->join('cluster', 'cluster.id_cluster = jalan.id_cluster')
            ->join('proyek', 'proyek.id_proyek = cluster.id_proyek')
            ->join('tipe', 'kavling.id_tipe = tipe.id_tipe')
            ->whereIn('kavling.id_kavling', $idKavlings)
            ->get()
            ->getResult();
    }
    public function getKavlingList($id_proyek = null, $search = "", $limit = null, $is_cashout_subkon = 0, $id_cluster = null, $id_jalan = null)
    {
        $builder = $this->db->table('kavling');
        $builder->select('kavling.id_kavling, jalan.nama_jalan, kavling.no_kavling');
        if ($is_cashout_subkon == 1) {
            $builder->select('cashout_subkon_kavling.id_cashout_subkon');
        }
        $builder->join('jalan', 'jalan.id_jalan = kavling.id_jalan');
        $builder->join('cluster', 'cluster.id_cluster = jalan.id_cluster');
        $builder->join('proyek', 'proyek.id_proyek = cluster.id_proyek');

        if ($is_cashout_subkon == 1) {
            $builder->join('cashout_subkon_kavling', 'cashout_subkon_kavling.id_kavling = kavling.id_kavling', 'left');
        }

        $builder->where('proyek.id_proyek', $id_proyek);

        if (!empty($id_cluster)) {
            $builder->where('cluster.id_cluster', $id_cluster);
        }

        if (!empty($id_jalan)) {
            $builder->where('kavling.id_jalan', $id_jalan);
        }

        if (!empty($search)) {
            // Kita gunakan OR untuk berbagai kemungkinan format penulisan user
            $builder->groupStart()
                ->like("CONCAT(nama_jalan, ' ', no_kavling)", $search)
                ->orLike("CONCAT(nama_jalan, ' no ', no_kavling)", $search)
                ->orLike('nama_jalan', $search)
                ->orLike('no_kavling', $search)
                ->groupEnd();
        }

        $builder->orderBy("jalan.nama_jalan", 'ASC');
        $builder->orderBy("ABS(kavling.no_kavling)", 'ASC');

        return $builder->get($limit)->getResult();
    }
    public function getDiskresiByKavlingId(int $idKavling): ?object
    {
        return $this->db->table('kavling k')
            ->select([
                'k.id_mkdt',
                'k.harga_akhir_tgl',
                'a.username AS username_harga_akhir',
                'k.diskresi_harga',
                'k.diskresi_memo',
                'k.diskresi_at',
                'b.username AS username_diskresi',
            ])
            ->join('users a', 'a.id = k.harga_akhir_oleh', 'left')
            ->join('users b', 'b.id = k.diskresi_oleh', 'left')
            ->where('k.id_kavling', $idKavling)
            ->limit(1)
            ->get()
            ->getRow();
    }
    private function baseQuery(): BaseBuilder
    {
        return $this->db->table('kavling')
            ->select('
                kavling.*,
                hargajual.hargajual,
                hargajual.tgl_harga,
                hargajual.is_subsidi,
                jalan.nama_jalan,
                cluster.id_cluster,
                cluster.nama_cluster,
                tipe.id_tipe,
                tipe.tipe_rumah,
                tipe.no_tipe_rumah,
                tipe.id_gambar_kerja,
                produksi.progres_bangunan,
                produksi.tanggal_pembangunan,
                produksi.tanggal_rencana_selesai_pembangunan,
                produksi.tanggal_selesai_pembangunan,
                produksi.keterangan as keterangan_produksi,
                users.username as harga_akhir_oleh_username,
                u.username as perintah_bangun_username
            ')
            ->join('mkdt', 'mkdt.id_mkdt = kavling.id_mkdt', 'left')
            ->join('jalan', 'jalan.id_jalan = kavling.id_jalan')
            ->join('cluster', 'cluster.id_cluster = jalan.id_cluster')
            ->join('proyek', 'proyek.id_proyek = cluster.id_proyek')
            ->join('tipe', 'kavling.id_tipe = tipe.id_tipe')
            ->join('hargajual', 'hargajual.id = kavling.harga_akhir', "left")
            ->join('legal', 'legal.id_legal = kavling.id_legal', 'left')
            ->join('produksi', 'produksi.id_produksi = kavling.id_produksi', 'left')
            ->join("users as u", "u.id = kavling.perintah_bangun_oleh", "left")
            ->join("users", "users.id = kavling.harga_akhir_oleh", "left");
    }

    /**
     * Select tambahan berdasarkan divisi.
     */
    private function addDivisiSelect(BaseBuilder $builder, $id_divisi)
    {
        $divisiFields = [
            3 => "mkdt.status_mkdt, mkdt.is_lunas, mkdt.is_subsidi as mkdt_is_subsidi, mkdt.is_kpr, mkdt.is_batal, mkdt.dajam_selesai,
                  (SELECT jatuh_tempo_tgl FROM keuangan WHERE keuangan.id_mkdt = mkdt.id_mkdt
                   and sudah_dibayar = 0 ORDER BY jatuh_tempo_tgl asc LIMIT 1) AS jatuh_tempo_tgl, mkdt.is_sudah_isi_tagihan,
                   ",

            4 => "mkdt.status_mkdt, mkdt.booking_tgl, mkdt.wawancara_tgl, mkdt.sp3k_tgl, mkdt.akad_tgl,
                  mkdt.is_subsidi as mkdt_is_subsidi, mkdt.is_kpr, mkdt.is_batal, mkdt.is_sudah_isi_tagihan",

            5 => "pbb_pecah_nop, pbb_pecah_luas_bumi, pbb_pecah_njop_bumi, pbb_pecah_luas_bangunan,
                  pbb_pecah_njop_bangunan, pbb_pecah_tanggal_bayar, pbb_pecah_jumlah_tagihan,
                  pbb_is_pembetulan, pbb_tgl_pembetulan, pbb_is_balik_nama, pbb_balik_nama,
                  pbb_balik_nama_tgl_pengiriman, pbb_balik_nama_ke,
                  sertifikat_split_no_hgb_induk, sertifikat_is_split, sertifikat_split_no_hgb,
                  sertifikat_split_tanggal_terbit, sertifikat_split_tanggal_berakhir,
                  sertifikat_split_nib, sertifikat_split_tanggal_surat_ukur, sertifikat_split_no_surat_ukur,
                  sertifikat_split_luas_tanah, sertifikat_balik_nama, sertifikat_balik_nama_tgl_pengiriman,
                  sertifikat_balik_nama_ke, sertifikat_nib_elektronik, sertifikat_is_balik_nama,
                  pbg_no, pbg_tanggal_terbit, pbg_tanggal_pengajuan, pbg_tipe,
                  pbg_status, pbg_dikirim_ke, pbg_tanggal_kirim, pbg_is_revisi, pbg_no_revisi,
                  pbg_status_revisi, pbg_tanggal_terbit_revisi, pbg_tipe_revisi, bphtb_tanggal_verifikasi,
                  bphtb_jatuh_tempo, bphtb_perpanjang_jatuh_tempo, bphtb_tanggal_pembayaran,
                  bphtb_nominal_disetujui, bphtb_tanggal_validasi, bphtb_nominal_tervalidasi,
                  pph_tgl_permohonan, pph_nominal_validasi, pph_nominal_bayar, pph_nominal_disetujui,
                  pph_tanggal_validasi, pph_no_sket, pph_kode_verifikasi, pph_ntpn, pph_tgl_bayar,
                  pph_tgl_selesai, pph_jenis_validasi, ajb_no, ajb_tanggal, ajb_notaris, ajb_dikirim_ke,
                  ajb_tanggal_dikirim, ppjb_no, ppjb_tanggal, ppjb_notaris",

            7 => "produksi.st_0, produksi.st_25, produksi.st_50, produksi.st_75, produksi.st_100,
                  produksi.slo, produksi.bp, produksi.lpa, produksi.st_jalan, produksi.st_saluran,
                  produksi.st_air, mkdt.status_mkdt",

            8 => "mkdt.status_mkdt, produksi.progres_bangunan"
        ];

        if (isset($divisiFields[$id_divisi])) {
            $builder->select($divisiFields[$id_divisi], true);
        } else {
            $builder->select("
                mkdt.status_mkdt, mkdt.is_batal, mkdt.is_lunas, mkdt.dajam_selesai,
                sertifikat_split_no_hgb_induk, sertifikat_split_no_hgb, sertifikat_is_balik_nama,
                pbb_pecah_nop, pbb_is_balik_nama, pbg_no, ajb_no, pph_tgl_bayar,
                bphtb_tanggal_pembayaran
            ", true);
        }
    }

    /**
     * Main: ambil data kavling dengan seluruh filter.
     */
    public function getAll($id_proyek, $id_cluster = null, $id_jalan = null, $id_divisi = null)
    {
        $builder = $this->baseQuery();


        $this->addDivisiSelect($builder, $id_divisi);

        // filter proyek
        $builder->where('cluster.id_proyek', $id_proyek);

        // filter cluster
        if ($id_cluster) {
            $builder->where('cluster.id_cluster', $id_cluster);
        }

        // filter jalan
        if ($id_jalan) {
            $builder->where('kavling.id_jalan', $id_jalan);
        }

        return $builder->get()->getResult();
    }

    public function getPerintahBangun($id_kavling)
    {
        return $this->model->select('
            perintah_bangun,
            perintah_bangun_tgl,
            perintah_bangun_file,
            username
        ')
            ->join('users', 'users.id = kavling.perintah_bangun_oleh', 'left')
            ->where('kavling.id_kavling', $id_kavling)
            ->first();
    }
    public function setPerintahBangun($id, $data)
    {
        return $this->model->update($id, $data);
    }
}
