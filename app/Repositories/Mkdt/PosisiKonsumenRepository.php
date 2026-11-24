<?php

namespace App\Repositories\Mkdt;

use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Database\BaseBuilder;

class PosisiKonsumenRepository
{
    protected $db;
    public function __construct(?BaseConnection $db = null)
    {
        $this->db = $db ?? \Config\Database::connect();
    }

    public function updateHargaNetAjb(int $idMkdt, float $hargaNetAjb): bool
    {
        if ($idMkdt <= 0) {
            return false;
        }

        return (bool) $this->db->table('mkdt')
            ->where('id_mkdt', $idMkdt)
            ->update(['harga_net_ajb' => $hargaNetAjb]);
    }
    public function getBaseQuery($status = "Booking")
    {
        //pembagian um, adm, bb itu masih per field table, seharusnya di ubah ke dinamis agar sesuai dengan summary table
        //per tnalggal 29 des 2025, untuk sementara sbum di jadikan diskon uang muka(perlu konfirmasi ke mkdt/keuangan)
        //st jalan adalah status listrik
        $builder = $this->db->table('mkdt')
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
            mkdt.akad,
            mkdt.akad_indent,
            mkdt.akad_indent_tgl,
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
            ->join('mkdt_payment_summary mps', "mps.id_mkdt = mkdt.id_mkdt", 'left');

        if ($status !== null && $status !== '') {
            $builder->where('mkdt.status_mkdt', $status);
        }

        $builder->orderBy('jalan.nama_jalan', 'ASC')
            ->orderBy('ABS(kavling.no_kavling)', 'ASC');

        return $builder;
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

    public function getFirstProyek(): ?object
    {
        $row = $this->db->table('proyek')
            ->orderBy('id_proyek', 'ASC')
            ->limit(1)
            ->get()
            ->getRow();

        return $row ?: null;
    }

    /**
     * @return array<int, object>
     */
    public function getPphList(): array
    {
        return $this->db->table('pph')
            ->where('deleted_at', null)
            ->get()
            ->getResult();
    }

    /**
     * @return array<int, object>
     */
    public function getPpnList(): array
    {
        return $this->db->table('ppn')
            ->where('deleted_at', null)
            ->get()
            ->getResult();
    }

    /**
     * @return array<int, object>
     */
    public function getLiKeu(): array
    {
        return $this->db->table('keuangan_item_list')
            ->select('*')
            ->where('deleted_at', null)
            ->orderBy('id_keuangan_item_list', 'ASC')
            ->get()
            ->getResult();
    }

    /**
     * Struktur key/kolom map `config_shape` identik dengan PosisiKonsumen::getConfigShapeMap().
     *
     * @return array<string, array<string, mixed>>
     */
    public function getConfigShapeMap(): array
    {
        $map  = [];
        $rows = $this->db->table('config_shape')->get()->getResult();

        foreach ($rows as $row) {
            if (!isset($row->config_name)) {
                continue;
            }
            $map[$row->config_name] = [
                'fill'        => $row->fill ?? null,
                'stroke'      => $row->stroke ?? null,
                'strokeWidth' => $row->strokeWidth ?? null,
                'dashed'      => $row->dashed ?? null,
                'keterangan'  => $row->keterangan ?? null,
            ];
        }

        return $map;
    }

    /**
     * @return array<int, object>
     */
    public function getChecklistSubItemsWithGroup(): array
    {
        return $this->db->table('checklist_subitem')
            ->select('
                checklist_group.nama_group,
                checklist_item.nama_item,
                checklist_subitem.id_subitem,
                checklist_subitem.nama_subitem
            ')
            ->join('checklist_item', 'checklist_item.id_item = checklist_subitem.id_item')
            ->join('checklist_group', 'checklist_item.id_group = checklist_group.id_group')
            ->where('checklist_item.is_active', 1)
            ->where('checklist_group.is_active', 1)
            ->where('checklist_subitem.is_active', 1)
            ->orderBy('checklist_item.id_group', 'asc')
            ->orderBy('checklist_item.id_item', 'asc')
            ->get()
            ->getResult();
    }

    /**
     * @return array<int, object>
     */
    public function getHakAksesByUser(int $userId, ?string $namaHak = null): array
    {
        if ($userId <= 0) {
            return [];
        }

        $builder = $this->db->table('hak_akses')
            ->like('id_users', (string) $userId);

        if ($namaHak !== null && $namaHak !== '') {
            $builder->like('nama_hak', ';' . $namaHak . ';');
        }

        return $builder->get()->getResult();
    }

    public function normalizeStatusKavling(?string $statusKavling): string
    {
        $value = strtolower(trim((string) $statusKavling));
        if ($value === 'booking') {
            return 'aktif';
        }

        return in_array($value, ['semua', 'aktif', 'indent', 'akad', 'ajb_indent'], true)
            ? $value
            : 'aktif';
    }

    public function applyStatusKavlingFilter(BaseBuilder $builder, string $statusKavling): void
    {
        $normalized = $this->normalizeStatusKavling($statusKavling);

        switch ($normalized) {
            case 'semua':
                $builder->whereIn('mkdt.status_mkdt', ['Booking', 'Akad']);
                break;

            case 'aktif':
                $builder->whereIn('mkdt.status_mkdt', ['Booking', 'Akad'])
                    ->where('COALESCE(mkdt.akad, 0) =', 0, false)
                    ->where('COALESCE(mkdt.akad_indent, 0) =', 0, false);
                break;

            case 'indent':
                $builder->whereIn('mkdt.status_mkdt', ['Booking', 'Akad'])
                    ->where('COALESCE(mkdt.akad, 0) =', 0, false)
                    ->where('COALESCE(mkdt.akad_indent, 0) =', 1, false);
                break;

            case 'akad':
                $builder->whereIn('mkdt.status_mkdt', ['Booking', 'Akad'])
                    ->where('COALESCE(mkdt.akad, 0) =', 1, false);
                break;

            case 'ajb_indent':
                $builder->whereIn('mkdt.status_mkdt', ['Booking', 'Akad'])
                    ->where('COALESCE(mkdt.akad_indent, 0) =', 1, false);
                break;
        }
    }

    /**
     * Raw DataTables query untuk posisi konsumen aktif/akad (?format=raw).
     *
     * @param array<string, mixed> $params
     * @return array{recordsTotal: int, recordsFiltered: int, rows: array<int, object>}
     */
    public function getRawDataTables(array $params, ?string $status = null): array
    {
        $hasExplicitStatusKavling = isset($params['status_kavling'])
            && is_scalar($params['status_kavling'])
            && trim((string) $params['status_kavling']) !== '';
        $hasLegacyAkadParams = (isset($params['akad']) && $params['akad'] !== '' && $params['akad'] !== null)
            || (isset($params['akad_indent']) && $params['akad_indent'] !== '' && $params['akad_indent'] !== null);
        $isLegacyAkadRoute = !$hasExplicitStatusKavling
            && $status !== null
            && strtolower(trim($status)) === 'akad';

        $useStatusKavlingFilter = $hasExplicitStatusKavling || (!$isLegacyAkadRoute && !$hasLegacyAkadParams);
        $baseStatus             = $useStatusKavlingFilter ? null : $this->resolveBaseStatus($status);
        $search                 = $this->extractSearchValue($params);
        $start                  = max(0, (int) ($params['start'] ?? 0));
        $length                 = isset($params['length']) ? (int) $params['length'] : 10;

        $totalBuilder = $this->getBaseQuery($baseStatus);
        $this->applyBaseFilters($totalBuilder, $params, $useStatusKavlingFilter);
        $recordsTotal = (int) $totalBuilder->countAllResults();

        $filteredBuilder = $this->getBaseQuery($baseStatus);
        $this->applyBaseFilters($filteredBuilder, $params, $useStatusKavlingFilter);
        $this->applyBaseSearch($filteredBuilder, $search);
        $recordsFiltered = (int) $filteredBuilder->countAllResults();

        $dataBuilder = $this->getBaseQuery($baseStatus);
        $this->applyBaseFilters($dataBuilder, $params, $useStatusKavlingFilter);
        $this->applyBaseSearch($dataBuilder, $search);

        if ($length > 0) {
            $dataBuilder->limit($length, $start);
        } elseif ($start > 0) {
            $dataBuilder->offset($start);
        }

        return [
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'rows'            => $dataBuilder->get()->getResult(),
        ];
    }

    /**
     * Raw DataTables query untuk posisi konsumen batal (?format=raw).
     *
     * @param array<string, mixed> $params
     * @return array{recordsTotal: int, recordsFiltered: int, rows: array<int, object>}
     */
    public function getRawDataTablesBatal(array $params): array
    {
        $search = $this->extractSearchValue($params);
        $start  = max(0, (int) ($params['start'] ?? 0));
        $length = isset($params['length']) ? (int) $params['length'] : 10;

        $totalBuilder = $this->getQueryBatal();
        $this->applyBatalFilters($totalBuilder, $params);
        $recordsTotal = (int) $totalBuilder->countAllResults();

        $filteredBuilder = $this->getQueryBatal();
        $this->applyBatalFilters($filteredBuilder, $params);
        $this->applyBatalSearch($filteredBuilder, $search);
        $recordsFiltered = (int) $filteredBuilder->countAllResults();

        $dataBuilder = $this->getQueryBatal();
        $this->applyBatalFilters($dataBuilder, $params);
        $this->applyBatalSearch($dataBuilder, $search);

        if ($length > 0) {
            $dataBuilder->limit($length, $start);
        } elseif ($start > 0) {
            $dataBuilder->offset($start);
        }

        return [
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'rows'            => $dataBuilder->get()->getResult(),
        ];
    }

    private function resolveBaseStatus(?string $status): string
    {
        if ($status === null || trim($status) === '' || strtolower(trim($status)) === 'aktif') {
            return 'Booking';
        }
        if (strtolower(trim($status)) === 'akad') {
            return 'Akad';
        }

        return trim($status);
    }

    /**
     * @param array<string, mixed> $params
     */
    private function extractSearchValue(array $params): string
    {
        if (isset($params['search']) && is_array($params['search'])) {
            return trim((string) ($params['search']['value'] ?? ''));
        }
        if (isset($params['search'])) {
            return trim((string) $params['search']);
        }

        return '';
    }

    /**
     * @param array<string, mixed> $params
     */
    public function applyBaseFilters(BaseBuilder $builder, array $params, bool $applyDefaultStatusKavling = true): void
    {
        if (!empty($params['id_proyek'])) {
            $builder->where('proyek.id_proyek', $params['id_proyek']);
        }
        if (!empty($params['id_cluster'])) {
            $builder->where('cluster.id_cluster', $params['id_cluster']);
        }
        if (!empty($params['id_jalan'])) {
            $builder->where('jalan.id_jalan', $params['id_jalan']);
        }

        if (isset($params['sp3k']) && $params['sp3k'] !== '' && $params['sp3k'] !== null) {
            $builder->where('mkdt.sp3k', $params['sp3k']);
        }
        if (isset($params['wawancara']) && $params['wawancara'] !== '' && $params['wawancara'] !== null) {
            $builder->where('mkdt.wawancara', $params['wawancara']);
        }

        $hasExplicitStatusKavling = isset($params['status_kavling'])
            && is_scalar($params['status_kavling'])
            && trim((string) $params['status_kavling']) !== '';
        $hasLegacyAkadParams = (isset($params['akad']) && $params['akad'] !== '' && $params['akad'] !== null)
            || (isset($params['akad_indent']) && $params['akad_indent'] !== '' && $params['akad_indent'] !== null);

        if ($hasExplicitStatusKavling || ($applyDefaultStatusKavling && !$hasLegacyAkadParams)) {
            $statusKavling = $this->normalizeStatusKavling(
                $hasExplicitStatusKavling ? (string) $params['status_kavling'] : null
            );
            $this->applyStatusKavlingFilter($builder, $statusKavling);
        } else {
            if (isset($params['akad']) && $params['akad'] !== '' && $params['akad'] !== null) {
                $builder->where('mkdt.akad', $params['akad']);
            }
            if (isset($params['akad_indent']) && $params['akad_indent'] !== '' && $params['akad_indent'] !== null) {
                $builder->where('mkdt.akad_indent', $params['akad_indent']);
                if ((int) $params['akad_indent'] === 1 && (!isset($params['akad']) || $params['akad'] === '' || $params['akad'] === null)) {
                    $builder->where('mkdt.akad', 0);
                }
            }
        }

        $bulan = $params['bulan'] ?? '';
        $tahun = $params['tahun'] ?? '';
        if ($bulan !== '' || $tahun !== '') {
            $bulanField = $params['bulan_field'] ?? '';
            if ($bulanField === 'akad') {
                $col = 'mkdt.akad_tgl';
            } elseif ($bulanField === 'indent') {
                $col = 'mkdt.akad_indent_tgl';
            } else {
                $col = 'mkdt.booking_tgl';
            }

            if ($bulan !== '' && $bulan !== null) {
                $builder->where("MONTH($col)", $bulan);
            }
            if ($tahun !== '' && $tahun !== null) {
                $builder->where("YEAR($col)", $tahun);
            }
        }
    }

    public function applyBaseSearch(BaseBuilder $builder, string $search): void
    {
        if ($search === '') {
            return;
        }

        $searchableColumns = [
            'kavling.id_kavling',
            'jalan.nama_jalan',
            'kavling.no_kavling',
            'hargajual.id_tipe',
            'konsumen.nama_konsumen',
            'konsumen.kode_referal',
            'konsumen.sales',
            'mkdt.booking_tgl',
            'mkdt.wawancara_tgl',
            'mkdt.akad_tgl',
            'mkdt.akad_indent_tgl',
            'mkdt.is_kpr',
            'list_bank.bank',
            'mkdt.keterangan',
            'mkdt.sp3k_tgl',
            'mkdt.sp3k_tgl_exp',
            'produksi.progres_bangunan',
            'produksi.lpa',
            'produksi.st_jalan',
            'legal.sertifikat_split_no_hgb',
            'legal.sertifikat_split_nib',
            'legal.pbg_no',
            'legal.pbb_pecah_nop',
            'mkdt.keterangan_status',
        ];

        $builder->groupStart();
        foreach ($searchableColumns as $index => $column) {
            if ($index === 0) {
                $builder->like($column, $search);
            } else {
                $builder->orLike($column, $search);
            }
        }
        $builder->groupEnd();
    }

    /**
     * @param array<string, mixed> $params
     */
    private function applyBatalFilters(BaseBuilder $builder, array $params): void
    {
        if (!empty($params['id_proyek'])) {
            $builder->where('proyek.id_proyek', $params['id_proyek']);
        }
        if (!empty($params['id_cluster'])) {
            $builder->where('cluster.id_cluster', $params['id_cluster']);
        }
        if (!empty($params['id_jalan'])) {
            $builder->where('jalan.id_jalan', $params['id_jalan']);
        }

        if (!empty($params['periode_booking'])) {
            $dates = explode(' to ', (string) $params['periode_booking']);
            if (count($dates) === 2) {
                $builder->where('mkdt.booking_tgl >=', trim($dates[0]));
                $builder->where('mkdt.booking_tgl <=', trim($dates[1]));
            } elseif (count($dates) === 1 && trim($dates[0]) !== '') {
                $builder->where('mkdt.booking_tgl', trim($dates[0]));
            }
        }

        if (!empty($params['periode_batal'])) {
            $dates = explode(' to ', (string) $params['periode_batal']);
            if (count($dates) === 2) {
                $builder->where('mkdt.mkdt_batal_tgl >=', trim($dates[0]));
                $builder->where('mkdt.mkdt_batal_tgl <=', trim($dates[1]));
            } elseif (count($dates) === 1 && trim($dates[0]) !== '') {
                $builder->where('mkdt.mkdt_batal_tgl', trim($dates[0]));
            }
        }
    }

    private function applyBatalSearch(BaseBuilder $builder, string $search): void
    {
        if ($search === '') {
            return;
        }

        $searchableColumns = [
            'jalan.nama_jalan',
            'kavling.no_kavling',
            'tipe.tipe_rumah',
            'mkdt.keterangan_batal',
            'konsumen.nama_konsumen',
            'mkdt.booking_tgl',
            'mkdt.is_kpr',
        ];

        $builder->groupStart();
        foreach ($searchableColumns as $index => $column) {
            if ($index === 0) {
                $builder->like($column, $search);
            } else {
                $builder->orLike($column, $search);
            }
        }
        $builder->groupEnd();
    }
}
