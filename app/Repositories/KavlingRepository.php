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
    public function getIdProyekByIdMkdt(int $id_mkdt): ?int
    {
        $row = $this->db->table('kavling')
            ->select('cluster.id_proyek')
            ->join('jalan', 'jalan.id_jalan = kavling.id_jalan')
            ->join('cluster', 'cluster.id_cluster = jalan.id_cluster')
            ->where('kavling.id_mkdt', $id_mkdt)
            ->get()->getRow();

        return $row ? (int) $row->id_proyek : null;
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
    public function getKavlingList($id_proyek = null, $search = "", $limit = null, $is_cashout_subkon = 0, $id_cluster = null, $id_jalan = null, $only_available = 0)
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

        if ($only_available) {
            $builder->join('mkdt', 'mkdt.id_mkdt = kavling.id_mkdt', 'left');
            $builder->groupStart()
                ->where('mkdt.id_mkdt', null)
                ->orWhere('mkdt.status_mkdt', 'Batal')
                ->groupEnd();
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
        return $this->db->table('cluster')
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
            ->join('jalan', 'jalan.id_cluster = cluster.id_cluster')
            ->join('kavling FORCE INDEX (idx_kavling_id_jalan)', 'kavling.id_jalan = jalan.id_jalan', '', false)
            ->join('mkdt', 'mkdt.id_mkdt = kavling.id_mkdt', 'left')
            ->join('proyek', 'proyek.id_proyek = cluster.id_proyek')
            ->join('tipe', 'kavling.id_tipe = tipe.id_tipe')
            ->join('hargajual', 'hargajual.id = kavling.harga_akhir', "left")
            ->join('legal', 'legal.id_legal = kavling.id_legal', 'left')
            ->join('pajak', 'pajak.id = kavling.id_pajak', 'left')
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

            8 => "mkdt.status_mkdt, produksi.progres_bangunan",

            10 => "kavling.id_pajak, mkdt.status_mkdt, mkdt.is_batal,
                   pajak.pph42_nilai, pajak.pph42_tgl_bayar, pajak.pph42_ntpn,
                   pajak.ppn_nilai, pajak.ppn_tgl_bayar, pajak.ppn_ntpn, pajak.ppn_no_faktur"
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
     * Select tambahan untuk status pencairan hasil akad (divisi Keuangan / id_divisi 3 saja).
     * Join tunggal ke derived table teragregasi per id_plan, bukan subquery per baris.
     */
    private function addPencairanAkadSelect(BaseBuilder $builder): void
    {
        $builder->join('pencairan_akad_plan pap', 'pap.id_mkdt = mkdt.id_mkdt', 'left');

        $builder->join(
            "(SELECT id_plan,
                     SUM(CASE WHEN status <> 'void' THEN 1 ELSE 0 END) AS pa_pengajuan_count,
                     SUM(CASE WHEN status <> 'void' THEN total_cair ELSE 0 END) AS pa_total_cair_sum
              FROM pencairan_akad_pengajuan
              GROUP BY id_plan) papg",
            'papg.id_plan = pap.id',
            'left',
            false
        );

        $builder->select('
            pap.id AS pa_plan_id,
            pap.total_hasil_akad AS pa_total_hasil_akad,
            papg.pa_pengajuan_count,
            papg.pa_total_cair_sum
        ', true);
    }

    /**
     * Main: ambil data kavling dengan seluruh filter.
     */
    public function getAll($id_proyek, $id_cluster = null, $id_jalan = null, $id_divisi = null, $kategoriFilters = [])
    {
        $builder = $this->baseQuery();

        $this->addDivisiSelect($builder, $id_divisi);

        if ((int) $id_divisi === 3) {
            $this->addPencairanAkadSelect($builder);
        }

        if (!empty($kategoriFilters['kategori'])) {
            $this->applyKategoriFilter($builder, $kategoriFilters);
        }

        // filter proyek
        $builder->where('cluster.id_proyek', $id_proyek);

        $projectJalanIds = $this->getProjectJalanIds($id_proyek, $id_cluster);
        if ($projectJalanIds === []) {
            return [];
        }

        $builder->whereIn('kavling.id_jalan', $projectJalanIds);

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

    private function getProjectJalanIds($id_proyek, $id_cluster = null): array
    {
        if (empty($id_proyek)) {
            return [];
        }

        $builder = $this->db->table('jalan')
            ->select('jalan.id_jalan')
            ->join('cluster', 'cluster.id_cluster = jalan.id_cluster')
            ->where('cluster.id_proyek', $id_proyek);

        if ($id_cluster) {
            $builder->where('cluster.id_cluster', $id_cluster);
        }

        $rows = $builder->get()->getResult();

        return array_values(array_map(static function ($row) {
            return (string) $row->id_jalan;
        }, $rows));
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

    public function getKategoriOptions(int $idProyek): array
    {
        $options = [
            ['key' => 'Sudah Akad', 'label' => 'Sudah Akad', 'has_periode' => true, 'cat' => 'Status kavling'],
            ['key' => 'Akad Komersil', 'label' => 'Akad Komersil', 'has_periode' => true, 'cat' => 'Status kavling'],
            ['key' => 'Akad Subsidi', 'label' => 'Akad Subsidi', 'has_periode' => true, 'cat' => 'Status kavling'],
            ['key' => 'Booking', 'label' => 'Booking', 'has_periode' => true, 'cat' => 'Status kavling'],
            ['key' => 'Batal', 'label' => 'Batal', 'has_periode' => true, 'cat' => 'Status kavling'],
            ['key' => 'SP3K', 'label' => 'SP3K', 'has_periode' => true, 'cat' => 'Status kavling'],
            ['key' => 'Masalah', 'label' => 'Masalah', 'has_periode' => true, 'cat' => 'Masalah'],
            ['key' => 'Turun Pembangunan', 'label' => 'Turun Pembangunan', 'has_periode' => true, 'cat' => 'Pembangunan'],
            ['key' => 'Bangunan Selesai', 'label' => 'Bangunan Selesai', 'has_periode' => true, 'cat' => 'Pembangunan'],
            ['key' => 'Jatuh Tempo', 'label' => 'Jatuh Tempo', 'has_periode' => true, 'cat' => 'Keuangan'],
            ['key' => 'Pengajuan Pencairan Hasil Akad', 'label' => 'Pengajuan Pencairan Hasil Akad', 'has_periode' => true, 'cat' => 'Keuangan'],
        ];

        $results = [];
        foreach ($options as $opt) {
            $builder = $this->db->table('kavling')
                ->join('jalan', 'jalan.id_jalan = kavling.id_jalan')
                ->join('cluster', 'cluster.id_cluster = jalan.id_cluster');
            $builder->where('cluster.id_proyek', $idProyek);
            $builder->join('mkdt', 'mkdt.id_mkdt = kavling.id_mkdt', 'left');
            $builder->join('produksi', 'produksi.id_produksi = kavling.id_produksi', 'left');

            $this->applySingleKategoriCondition($builder, $opt['key'], null, null, null, null);
            $count = $builder->countAllResults();
            if ($count > 0 || in_array($opt['key'], ['Masalah'])) {
                $opt['count'] = $count;
                $results[] = $opt;
            }
        }
        return $results;
    }

    public function applyKategoriFilter(BaseBuilder $builder, array $filters)
    {
        $kategoriList = $filters['kategori'] ?? [];
        $periodeMulai = $filters['periode_mulai'] ?? null;
        $periodeSelesai = $filters['periode_selesai'] ?? null;
        $statusMasalah = $filters['status_masalah'] ?? null;
        $periodeMasalahJenis = $filters['periode_masalah_jenis'] ?? null;

        if (empty($kategoriList)) {
            return;
        }

        $builder->groupStart();

        foreach ($kategoriList as $kategori) {
            $builder->orGroupStart();
            $this->applySingleKategoriCondition($builder, $kategori, $periodeMulai, $periodeSelesai, $statusMasalah, $periodeMasalahJenis);
            $builder->groupEnd();
        }

        $builder->groupEnd();
    }

    private function applySingleKategoriCondition(BaseBuilder $builder, string $kategori, ?string $periodeMulai, ?string $periodeSelesai, ?string $statusMasalah, ?string $periodeMasalahJenis)
    {
        $dateCondition = function (string $column) use ($builder, $periodeMulai, $periodeSelesai) {
            if ($periodeMulai && $periodeSelesai) {
                $builder->where("$column >=", $periodeMulai);
                $builder->where("$column <=", $periodeSelesai);
            } elseif ($periodeMulai) {
                $builder->where("$column >=", $periodeMulai);
            } elseif ($periodeSelesai) {
                $builder->where("$column <=", $periodeSelesai);
            }
        };

        switch ($kategori) {
            case 'Sudah Akad':
                $builder->where('mkdt.status_mkdt', 'Akad');
                $dateCondition('mkdt.akad_tgl');
                break;
            case 'Akad Komersil':
                $builder->where('mkdt.status_mkdt', 'Akad');
                $builder->where('mkdt.is_subsidi', 0);
                $dateCondition('mkdt.akad_tgl');
                break;
            case 'Akad Subsidi':
                $builder->where('mkdt.status_mkdt', 'Akad');
                $builder->where('mkdt.is_subsidi', 1);
                $dateCondition('mkdt.akad_tgl');
                break;
            case 'Booking':
                $builder->where('mkdt.status_mkdt', 'Booking');
                $dateCondition('mkdt.booking_tgl');
                break;
            case 'Batal':
                $builder->groupStart()
                    ->where('mkdt.is_batal', 1)
                    ->orWhere('mkdt.status_mkdt', 'Batal')
                    ->groupEnd();
                $dateCondition('mkdt.mkdt_batal_tgl');
                break;
            case 'Masalah':
                $statusSql = "";
                if ($statusMasalah) {
                    $statusSql = "AND tm.status = " . $this->db->escape($statusMasalah);
                } else {
                    $statusSql = "AND tm.status != 'selesai'";
                }

                $dateSql = "";
                $dateCol = ($periodeMasalahJenis === 'tgl_selesai') ? "tmp.created_at" : "tm.tanggal_masalah";

                if ($periodeMulai && $periodeSelesai) {
                    $dateSql = "AND $dateCol >= " . $this->db->escape($periodeMulai) . " AND $dateCol <= " . $this->db->escape($periodeSelesai);
                } elseif ($periodeMulai) {
                    $dateSql = "AND $dateCol >= " . $this->db->escape($periodeMulai);
                } elseif ($periodeSelesai) {
                    $dateSql = "AND $dateCol <= " . $this->db->escape($periodeSelesai);
                }

                if ($periodeMasalahJenis === 'tgl_selesai' && $dateSql !== "") {
                    // Jika filter periode aktif dan memilih tanggal selesai
                    $builder->where("EXISTS (
                        SELECT 1 FROM tiket_masalah_progress tmp 
                        JOIN tiket_masalah tm ON tmp.id_tiket_masalah = tm.id 
                        WHERE tm.ref_type = 'kavling' AND tm.ref_id = kavling.id_kavling AND tmp.status_sesudah = 'selesai' 
                        $statusSql $dateSql
                    )", null, false);
                } else {
                    // Default query status masalah
                    $builder->where("EXISTS (
                        SELECT 1 FROM tiket_masalah tm 
                        WHERE tm.ref_type = 'kavling' AND tm.ref_id = kavling.id_kavling 
                        $statusSql $dateSql
                    )", null, false);
                }
                break;
            case 'Turun Pembangunan':
                $builder->where('mkdt.perintah_bangun', 1);
                $dateCondition('mkdt.perintah_bangun_tgl');
                break;
            case 'Bangunan Selesai':
                $builder->where('produksi.progres_bangunan', 100);
                $dateCondition('produksi.tanggal_selesai_pembangunan');
                break;
            case 'Jatuh Tempo':
                $dateSql = "";
                if ($periodeMulai && $periodeSelesai) {
                    $dateSql = "AND keu.jatuh_tempo_tgl >= " . $this->db->escape($periodeMulai) . " AND keu.jatuh_tempo_tgl <= " . $this->db->escape($periodeSelesai);
                } elseif ($periodeMulai) {
                    $dateSql = "AND keu.jatuh_tempo_tgl >= " . $this->db->escape($periodeMulai);
                } elseif ($periodeSelesai) {
                    $dateSql = "AND keu.jatuh_tempo_tgl <= " . $this->db->escape($periodeSelesai);
                }
                $builder->where("EXISTS (SELECT 1 FROM keuangan keu WHERE keu.id_mkdt = mkdt.id_mkdt AND keu.sudah_dibayar = 0 AND keu.jatuh_tempo_tgl < CURDATE() $dateSql)", null, false);
                break;
            case 'Hasil Akad Belum Cair':
                $builder->where('mkdt.status_mkdt', "Akad");
                $builder->where('mkdt.is_kpr', 1);
                $builder->where("NOT EXISTS (
                    SELECT 1 FROM pencairan_akad_plan pap
                    JOIN pencairan_akad_pengajuan papg ON papg.id_plan = pap.id
                    WHERE pap.id_mkdt = mkdt.id_mkdt AND papg.status <> 'void' AND papg.total_cair > 0
                )", null, false);
                break;
            case 'Pengajuan Pencairan Hasil Akad':
                $dateSql = "";
                if ($periodeMulai && $periodeSelesai) {
                    $dateSql = "AND papg.tanggal_pengajuan >= " . $this->db->escape($periodeMulai) . " AND papg.tanggal_pengajuan <= " . $this->db->escape($periodeSelesai);
                } elseif ($periodeMulai) {
                    $dateSql = "AND papg.tanggal_pengajuan >= " . $this->db->escape($periodeMulai);
                } elseif ($periodeSelesai) {
                    $dateSql = "AND papg.tanggal_pengajuan <= " . $this->db->escape($periodeSelesai);
                }
                $builder->where("EXISTS (
                    SELECT 1 FROM pencairan_akad_plan pap
                    JOIN pencairan_akad_pengajuan papg ON papg.id_plan = pap.id
                    WHERE pap.id_mkdt = mkdt.id_mkdt AND papg.status <> 'void' AND papg.total_pengajuan > 0
                    $dateSql
                )", null, false);
                break;
            case 'Pencairan Hasil Akad':
                $dateSql = "";
                if ($periodeMulai && $periodeSelesai) {
                    $dateSql = "AND papy.tanggal_cair >= " . $this->db->escape($periodeMulai) . " AND papy.tanggal_cair <= " . $this->db->escape($periodeSelesai);
                } elseif ($periodeMulai) {
                    $dateSql = "AND papy.tanggal_cair >= " . $this->db->escape($periodeMulai);
                } elseif ($periodeSelesai) {
                    $dateSql = "AND papy.tanggal_cair <= " . $this->db->escape($periodeSelesai);
                }
                $builder->where("EXISTS (
                    SELECT 1 FROM pencairan_akad_plan pap
                    JOIN pencairan_akad_pengajuan papg ON papg.id_plan = pap.id
                    JOIN pencairan_akad_payment papy ON papy.id_pengajuan = papg.id
                    WHERE pap.id_mkdt = mkdt.id_mkdt AND papg.status <> 'void'
                    $dateSql
                )", null, false);
                break;
            case 'SP3K':
                $builder->groupStart()
                    ->where('mkdt.sp3k_tgl IS NOT NULL')
                    ->where('mkdt.status_mkdt', 'Booking')
                    ->groupEnd();
                $dateCondition('mkdt.sp3k_tgl');
                break;
        }
    }
}
