<?php

namespace App\Repositories;

use App\Models\MkdtKavlingHistoryModel;
use CodeIgniter\Database\BaseConnection;

class NupRepository
{
    private BaseConnection $db;
    private MkdtKavlingHistoryModel $historyModel;

    public function __construct(?BaseConnection $db = null)
    {
        $this->db           = $db ?? \Config\Database::connect();
        $this->historyModel = new MkdtKavlingHistoryModel($this->db);
    }

    public function getNupListByProyek(int $idProyek, ?string $search = null): array
    {
        $builder = $this->db->table('mkdt m')
            ->select("
                m.id_mkdt,
                m.no_nup,
                m.id_konsumen,
                m.id_proyek,
                m.status_mkdt,
                m.booking_fee,
                m.booking_tgl,
                m.catatan_nup,
                m.file_nup,
                m.nup_source,
                m.last_id_kavling,
                m.nup_at,
                m.harga_jual,
                m.created_at,
                k.nama_konsumen,
                k.nik,
                k.npwp,
                k.hp_konsumen,
                k.email_konsumen,
                k.alamat_konsumen,
                k.sales,
                k.kode_referal,
                k.file_ktp,
                k.file_npwp,
                k.file_data_diri,
                COALESCE(mps.total_booking, m.booking_fee, 0) AS total_booking,
                (COALESCE(mps.total_um, 0) + COALESCE(mps.total_adm, 0) + COALESCE(mps.total_bb, 0)) AS total_angsuran_masuk,
                (COALESCE(mps.total_um, 0) + COALESCE(mps.total_adm, 0) + COALESCE(mps.total_bb, 0) + COALESCE(mps.total_booking, m.booking_fee, 0)) AS total_sudah_bayar,
                CASE WHEN mbp.verified_at IS NOT NULL THEN 1 ELSE 0 END AS booking_verified,
                last_j.nama_jalan AS last_nama_jalan,
                last_kav.no_kavling AS last_no_kavling,
                COALESCE(u_nup.username, u_add.username, '-') AS diinput_oleh
            ", false)
            ->join('konsumen k', 'k.id_konsumen = m.id_konsumen', 'left')
            ->join('mkdt_payment_summary mps', 'mps.id_mkdt = m.id_mkdt', 'left')
            ->join('mkdt_booking_payment mbp', 'mbp.id_mkdt = m.id_mkdt', 'left')
            ->join('kavling last_kav', 'last_kav.id_kavling = m.last_id_kavling', 'left')
            ->join('jalan last_j', 'last_j.id_jalan = last_kav.id_jalan', 'left')
            ->join('users u_add', 'u_add.id = m.add_by', 'left')
            ->join('users u_nup', 'u_nup.id = m.nup_by', 'left')
            ->where('m.status_mkdt', 'NUP')
            ->where('COALESCE(m.is_batal, 0) =', 0, false)
            ->where('m.id_proyek', $idProyek);

        $search = trim((string) $search);
        if ($search !== '') {
            $builder->groupStart()
                ->like('m.no_nup', $search)
                ->orLike('k.nama_konsumen', $search)
                ->orLike('k.nik', $search)
                ->orLike('k.hp_konsumen', $search)
                ->orLike('k.sales', $search)
                ->orLike('m.catatan_nup', $search)
                ->orLike('last_j.nama_jalan', $search)
                ->groupEnd();
        }

        return $builder
            ->orderBy('COALESCE(m.nup_at, m.created_at)', 'DESC', false)
            ->orderBy('m.id_mkdt', 'DESC')
            ->get()
            ->getResultArray();
    }

    public function getNupDetail(int $idMkdt, bool $lock = false): ?array
    {
        $sql = "
            SELECT
                m.*,
                k.id_konsumen AS kons_id_konsumen,
                k.kode_referal,
                k.no_spptb,
                k.nama_konsumen,
                k.npwp,
                k.nik,
                k.alamat_konsumen,
                k.hp_konsumen,
                k.file_npwp,
                k.file_ktp,
                k.file_data_diri,
                k.email_konsumen,
                k.nama_instansi,
                k.alamat_instansi,
                k.tel_instansi,
                k.email_instansi,
                k.alamat_surat,
                k.pekerjaan,
                k.lama_bekerja,
                k.bidang_pekerjaan,
                k.status_pernikahan,
                k.nama_pasangan,
                k.nik_pasangan,
                k.hp_pasangan,
                k.status_pekerjaan_pasangan,
                k.instansi_pasangan,
                k.sales,
                k.status_konsumen,
                COALESCE(mps.total_booking, m.booking_fee, 0) AS total_booking,
                (COALESCE(mps.total_um, 0) + COALESCE(mps.total_adm, 0) + COALESCE(mps.total_bb, 0)) AS total_angsuran_masuk,
                (COALESCE(mps.total_um, 0) + COALESCE(mps.total_adm, 0) + COALESCE(mps.total_bb, 0) + COALESCE(mps.total_booking, m.booking_fee, 0)) AS total_sudah_bayar,
                last_j.nama_jalan AS last_nama_jalan,
                last_kav.no_kavling AS last_no_kavling
            FROM " . $this->db->prefixTable('mkdt') . " m
            LEFT JOIN " . $this->db->prefixTable('konsumen') . " k ON k.id_konsumen = m.id_konsumen
            LEFT JOIN " . $this->db->prefixTable('mkdt_payment_summary') . " mps ON mps.id_mkdt = m.id_mkdt
            LEFT JOIN " . $this->db->prefixTable('kavling') . " last_kav ON last_kav.id_kavling = m.last_id_kavling
            LEFT JOIN " . $this->db->prefixTable('jalan') . " last_j ON last_j.id_jalan = last_kav.id_jalan
            WHERE m.id_mkdt = ?
        ";

        if ($lock && $this->db->DBDriver !== 'SQLite3') {
            $sql .= ' FOR UPDATE';
        }

        $row = $this->db->query($sql, [$idMkdt])->getRowArray();

        return $row ?: null;
    }

    public function getKavlingRow(int $idKavling, bool $lock = false): ?array
    {
        $sql = "
            SELECT
                k.*,
                j.nama_jalan,
                j.id_cluster,
                cl.nama_cluster,
                cl.id_proyek,
                t.tipe_rumah,
                t.no_tipe_rumah,
                t.lb,
                t.lt
            FROM " . $this->db->prefixTable('kavling') . " k
            LEFT JOIN " . $this->db->prefixTable('jalan') . " j ON j.id_jalan = k.id_jalan
            LEFT JOIN " . $this->db->prefixTable('cluster') . " cl ON cl.id_cluster = j.id_cluster
            LEFT JOIN " . $this->db->prefixTable('tipe') . " t ON t.id_tipe = k.id_tipe
            WHERE k.id_kavling = ?
        ";

        if ($lock && $this->db->DBDriver !== 'SQLite3') {
            $sql .= ' FOR UPDATE';
        }

        $row = $this->db->query($sql, [$idKavling])->getRowArray();

        return $row ?: null;
    }

    public function getKavlingLabel(?int $idKavling): ?string
    {
        if (! $idKavling || $idKavling <= 0) {
            return null;
        }

        $row = $this->getKavlingRow($idKavling, false);
        if (! $row) {
            return 'Kavling #' . $idKavling;
        }

        $jalan = trim((string) ($row['nama_jalan'] ?? ''));
        $no    = trim((string) ($row['no_kavling'] ?? ''));

        return trim($jalan . ' No. ' . ($no !== '' ? $no : '-'));
    }

    public function getProyekIdByKavling(?int $idKavling): ?int
    {
        if (! $idKavling || $idKavling <= 0) {
            return null;
        }

        $row = $this->getKavlingRow($idKavling, false);

        return ! empty($row['id_proyek']) ? (int) $row['id_proyek'] : null;
    }

    /**
     * Mengambil daftar seluruh kavling di proyek untuk Select2 (Aktifkan NUP & Pindah Kavling).
     * Kavling yang sudah memiliki konsumen aktif tetap dikembalikan dengan is_occupied = true
     * agar di UI ditampilkan berwarna merah dan disabled.
     */
    public function getKavlingOptionsForTransfer(
        int $idProyek,
        ?string $search = null,
        ?int $currentIdKavling = null,
        ?int $lastIdKavling = null,
        ?string $filterHargaJual = null
    ): array {
        $builder = $this->db->table('kavling k')
            ->select("
                k.id_kavling,
                k.no_kavling,
                k.id_jalan,
                k.id_mkdt,
                k.id_keuangan,
                k.id_legal,
                k.id_produksi,
                k.id_komplain,
                k.harga_akhir,
                k.harga_akhir_tgl,
                k.harga_akhir_oleh,
                j.nama_jalan,
                j.id_cluster,
                cl.nama_cluster,
                cl.id_proyek,
                t.tipe_rumah,
                t.no_tipe_rumah,
                t.lb,
                t.lt,
                hj.id AS id_hargajual,
                hj.hargajual AS nominal_hargajual,
                m.status_mkdt,
                m.is_batal,
                c.nama_konsumen AS active_nama_konsumen
            ", false)
            ->join('jalan j', 'j.id_jalan = k.id_jalan')
            ->join('cluster cl', 'cl.id_cluster = j.id_cluster')
            ->join('tipe t', 't.id_tipe = k.id_tipe', 'left')
            ->join('hargajual hj', 'hj.id = k.harga_akhir', 'left')
            ->join('mkdt m', 'm.id_mkdt = k.id_mkdt', 'left')
            ->join('konsumen c', 'c.id_konsumen = m.id_konsumen', 'left')
            ->where('cl.id_proyek', $idProyek);

        $normalizedFilterHj = strtolower(trim((string) $filterHargaJual));
        if ($normalizedFilterHj === 'sudah') {
            $builder->where('hj.id IS NOT NULL', null, false)
                ->where('hj.hargajual >', 0);
        } elseif ($normalizedFilterHj === 'belum') {
            $builder->groupStart()
                ->where('hj.id IS NULL', null, false)
                ->orWhere('hj.hargajual <=', 0)
                ->groupEnd();
        }

        $search = trim((string) $search);
        if ($search !== '') {
            $builder->groupStart()
                ->like('j.nama_jalan', $search)
                ->orLike('k.no_kavling', $search)
                ->orLike("CONCAT(j.nama_jalan, ' No. ', k.no_kavling)", $search)
                ->orLike("CONCAT(j.nama_jalan, ' ', k.no_kavling)", $search)
                ->orLike('c.nama_konsumen', $search)
                ->groupEnd();
        }

        $rows = $builder
            ->orderBy('j.nama_jalan', 'ASC')
            ->orderBy('ABS(k.no_kavling)', 'ASC', false)
            ->orderBy('k.no_kavling', 'ASC')
            ->limit(1000)
            ->get()
            ->getResultArray();

        $results = [];
        foreach ($rows as $r) {
            $idKav     = (int) $r['id_kavling'];
            $idMkdtKav = (int) ($r['id_mkdt'] ?? 0);
            $statusMk  = trim((string) ($r['status_mkdt'] ?? ''));
            $isBatal   = (int) ($r['is_batal'] ?? 0) === 1;

            $hasActiveConsumer = $idMkdtKav > 0
                && ! $isBatal
                && ! in_array(strtolower($statusMk), ['', 'batal', 'nup'], true);

            $isCurrentKavling = $currentIdKavling !== null && $currentIdKavling > 0 && $idKav === $currentIdKavling;
            $isLastKavling    = $lastIdKavling !== null && $lastIdKavling > 0 && $idKav === $lastIdKavling;

            $idHargaJual  = ! empty($r['id_hargajual']) ? (int) $r['id_hargajual'] : 0;
            $nominalHj    = (float) ($r['nominal_hargajual'] ?? 0);
            $hasHargaJual = $idHargaJual > 0 && $nominalHj > 0;

            $results[] = [
                'id_kavling'        => $idKav,
                'nama_jalan'        => $r['nama_jalan'] ?? '',
                'no_kavling'        => $r['no_kavling'] ?? '',
                'id_cluster'        => $r['id_cluster'] ?? null,
                'nama_cluster'      => $r['nama_cluster'] ?? '',
                'tipe_rumah'        => $r['tipe_rumah'] ?? '',
                'no_tipe_rumah'     => $r['no_tipe_rumah'] ?? '',
                'lb'                => $r['lb'] ?? '',
                'lt'                => $r['lt'] ?? '',
                'has_hargajual'     => $hasHargaJual,
                'harga_akhir'       => $hasHargaJual ? (string) ($r['harga_akhir'] ?: $idHargaJual) : '-',
                'id_hargajual'      => $hasHargaJual ? (string) $idHargaJual : '-',
                'nominal_hargajual' => $nominalHj,
                'harga_akhir_tgl'   => $r['harga_akhir_tgl'] ?? '',
                'harga_akhir_oleh'  => $r['harga_akhir_oleh'] ?? '',
                'id_mkdt'           => $hasActiveConsumer ? $idMkdtKav : null,
                'id_keuangan'       => $r['id_keuangan'] ?? null,
                'id_legal'          => $r['id_legal'] ?? null,
                'id_produksi'       => $r['id_produksi'] ?? null,
                'id_komplain'       => $r['id_komplain'] ?? null,
                'is_occupied'       => $hasActiveConsumer || $isCurrentKavling,
                'is_current'        => $isCurrentKavling,
                'is_last_kavling'   => $isLastKavling,
                'active_konsumen'   => $hasActiveConsumer ? ($r['active_nama_konsumen'] ?: 'Terisi') : null,
                'status_mkdt'       => $hasActiveConsumer ? $statusMk : null,
            ];
        }

        return $results;
    }

    public function insertKavlingHistory(array $data): int
    {
        if (! $this->historyModel->insert($data)) {
            throw new \RuntimeException('Gagal menyimpan riwayat perpindahan kavling / NUP');
        }

        return (int) $this->historyModel->getInsertID();
    }

    public function getKavlingHistory(int $idMkdt = 0, int $idKavling = 0): array
    {
        if ($idMkdt <= 0 && $idKavling <= 0) {
            return [];
        }

        if (! $this->db->tableExists('mkdt_kavling_history')) {
            return [];
        }

        $builder = $this->db->table('mkdt_kavling_history h')
            ->select("
                h.*,
                k.nama_konsumen,
                u.username AS actor_name
            ", false)
            ->join('konsumen k', 'k.id_konsumen = h.id_konsumen', 'left')
            ->join('users u', 'u.id = h.created_by', 'left');

        $builder->groupStart();
        if ($idMkdt > 0) {
            $builder->orWhere('h.id_mkdt', $idMkdt);
        }
        if ($idKavling > 0) {
            $builder->orWhere('h.from_id_kavling', $idKavling);
            $builder->orWhere('h.to_id_kavling', $idKavling);
        }
        $builder->groupEnd();

        return $builder
            ->orderBy('h.id', 'DESC')
            ->get()
            ->getResultArray();
    }

    public function isNoNupExists(string $noNup, int $idProyek, int $excludeIdMkdt = 0): bool
    {
        $noNup = trim($noNup);
        if ($noNup === '') {
            return false;
        }

        $builder = $this->db->table('mkdt')
            ->where('id_proyek', $idProyek)
            ->where('no_nup', $noNup)
            ->where('COALESCE(is_batal, 0) =', 0, false);

        if ($excludeIdMkdt > 0) {
            $builder->where('id_mkdt !=', $excludeIdMkdt);
        }

        return $builder->countAllResults() > 0;
    }

    public function generateNextNupNumber(int $idProyek): string
    {
        $maxNum = 0;
        $rows = $this->db->table('mkdt')
            ->select('no_nup')
            ->where('id_proyek', $idProyek)
            ->where('status_mkdt', 'NUP')
            ->where('no_nup IS NOT NULL')
            ->get()
            ->getResultArray();

        foreach ($rows as $r) {
            if (preg_match('/NUP-(\d+)/i', (string) ($r['no_nup'] ?? ''), $m)) {
                $num = (int) $m[1];
                if ($num > $maxNum) {
                    $maxNum = $num;
                }
            }
        }

        return sprintf('NUP-%03d', $maxNum + 1);
    }
}
