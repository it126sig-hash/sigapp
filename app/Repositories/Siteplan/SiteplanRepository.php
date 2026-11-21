<?php

namespace App\Repositories\Siteplan;

use App\Models\ChecklistSubItemModel;
use App\Models\ClusterModel;
use App\Models\ConfigModel;
use App\Models\JalanModel;
use App\Models\KavlingModel;
use App\Models\LegalModel;
use App\Models\MkdtModel;
use App\Models\ProduksiModel;
use App\Models\ProyekModel;
use App\Models\TipeModel;
use App\Repositories\Proyek\ProyekRepository;
use CodeIgniter\Database\BaseConnection;

/**
 * Repository terpusat untuk seluruh query database & akses Model modul Siteplan.
 */
class SiteplanRepository
{
    protected BaseConnection $db;
    protected ProyekModel $proyekModel;
    protected ClusterModel $clusterModel;
    protected JalanModel $jalanModel;
    protected TipeModel $tipeModel;
    protected KavlingModel $kavlingModel;
    protected ConfigModel $configModel;
    protected ChecklistSubItemModel $checklistSubItemModel;
    protected MkdtModel $mkdtModel;
    protected LegalModel $legalModel;
    protected ProduksiModel $produksiModel;
    protected ProyekRepository $proyekRepository;

    public function __construct(
        ?BaseConnection $db = null,
        ?ProyekModel $proyekModel = null,
        ?ClusterModel $clusterModel = null,
        ?JalanModel $jalanModel = null,
        ?TipeModel $tipeModel = null,
        ?KavlingModel $kavlingModel = null,
        ?ConfigModel $configModel = null,
        ?ChecklistSubItemModel $checklistSubItemModel = null,
        ?MkdtModel $mkdtModel = null,
        ?LegalModel $legalModel = null,
        ?ProduksiModel $produksiModel = null,
        ?ProyekRepository $proyekRepository = null
    ) {
        $this->db                    = $db ?? db_connect();
        $this->proyekModel           = $proyekModel ?? new ProyekModel();
        $this->clusterModel          = $clusterModel ?? new ClusterModel();
        $this->jalanModel            = $jalanModel ?? new JalanModel();
        $this->tipeModel             = $tipeModel ?? new TipeModel();
        $this->kavlingModel          = $kavlingModel ?? new KavlingModel();
        $this->configModel           = $configModel ?? new ConfigModel();
        $this->checklistSubItemModel = $checklistSubItemModel ?? new ChecklistSubItemModel();
        $this->mkdtModel             = $mkdtModel ?? new MkdtModel();
        $this->legalModel            = $legalModel ?? new LegalModel();
        $this->produksiModel         = $produksiModel ?? new ProduksiModel();
        $this->proyekRepository      = $proyekRepository ?? new ProyekRepository();
    }

    // ─── MASTER & KONFIGURASI ───

    public function getProfilePerusahaan(): ?object
    {
        return $this->db->table('profile_perusahaan')->get()->getRow();
    }

    public function getPphList(): array
    {
        return $this->db->table('pph')
            ->where('deleted_at', null)
            ->get()
            ->getResult();
    }

    public function getPpnList(): array
    {
        return $this->db->table('ppn')
            ->where('deleted_at', null)
            ->get()
            ->getResult();
    }

    public function getConfigShapeList(): array
    {
        return $this->db->table('config_shape')
            ->get()
            ->getResult();
    }

    public function getConfigShapeMap(): array
    {
        $rows = $this->getConfigShapeList();
        $map  = [];

        foreach ($rows as $row) {
            if (! isset($row->config_name)) {
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

    public function getConfigByRole(int $roleId): ?object
    {
        return $this->configModel
            ->select('*')
            ->where('id_role', $roleId)
            ->first();
    }

    public function getClusterOptionsByProyek(int $idProyek): array
    {
        return $this->clusterModel
            ->select('id_cluster, nama_cluster')
            ->where('cluster.id_proyek', $idProyek)
            ->orderBy('nama_cluster', 'ASC')
            ->findAll();
    }

    public function getClustersByProyek(int $idProyek): array
    {
        return $this->clusterModel
            ->select('id_cluster, nama_cluster')
            ->where('cluster.id_proyek', $idProyek)
            ->findAll();
    }

    public function getJalansByProyek(int $idProyek): array
    {
        return $this->jalanModel
            ->select('id_jalan, nama_jalan, nama_cluster')
            ->join('cluster', 'cluster.id_cluster = jalan.id_cluster')
            ->where('cluster.id_proyek', $idProyek)
            ->findAll();
    }

    public function getTipesByProyek(int $idProyek): array
    {
        return $this->tipeModel
            ->select('id_tipe, tipe_rumah, harga, no_tipe_rumah')
            ->where('id_proyek', $idProyek)
            ->findAll();
    }

    public function getChecklistSubItemsWithGroup(?int $idProyek = null): array
    {
        return $this->checklistSubItemModel
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
            ->findAll();
    }

    public function getChecklistItemsMap(): array
    {
        $rows = $this->getChecklistSubItemsWithGroup();
        $map  = [];

        foreach ($rows as $row) {
            $group = (string) ($row->nama_group ?? '');
            $item  = (string) ($row->nama_item ?? '');

            $map[$group][$item][] = [
                'id_subitem'   => (int) ($row->id_subitem ?? 0),
                'nama_subitem' => (string) ($row->nama_subitem ?? ''),
            ];
        }

        return $map;
    }

    /**
     * Membaca hak akses dari tabel `hak_akses` tanpa menginstansiasi Controller Home.
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

    public function getUserGroupId(int $userId): int
    {
        if ($userId <= 0) {
            return 0;
        }

        $row = $this->db->table('auth_groups_users')
            ->select('group_id')
            ->where('user_id', $userId)
            ->get()
            ->getRow();

        return (int) ($row->group_id ?? 0);
    }

    // ─── PROYEK & AKSES ───

    public function findProyekById(int $idProyek): ?object
    {
        if ($idProyek <= 0) {
            return null;
        }

        return $this->proyekModel
            ->select('*')
            ->where('id_proyek', $idProyek)
            ->first();
    }

    public function getProyekByUserId(int $userId, bool $isAdmin = false): array
    {
        return $this->proyekRepository->getAccessibleForUser($userId, $isAdmin);
    }

    public function userHasProjectAccess(?object $proyek, int $userId): bool
    {
        if (! $proyek || ! isset($proyek->id_users) || $proyek->id_users === null || $proyek->id_users === '') {
            return false;
        }

        $allowedUserIds = array_filter(array_map('trim', explode(',', (string) $proyek->id_users)));

        return in_array((string) $userId, $allowedUserIds, true);
    }

    // ─── KAVLING & SPASIAL ───

    public function findDuplicateKavlingByJalan(string $noKavling, int $idJalan, ?int $excludeId = null): ?object
    {
        $builder = $this->db->table('kavling')
            ->where('id_jalan', $idJalan)
            ->where('no_kavling', $noKavling);

        if ($excludeId !== null && $excludeId > 0) {
            $builder->where('id_kavling !=', $excludeId);
        }

        return $builder->get()->getRow();
    }

    public function isNoKavlingDuplicate(string $noKavling, int $idJalan, ?int $excludeId = null): bool
    {
        return $this->findDuplicateKavlingByJalan($noKavling, $idJalan, $excludeId) !== null;
    }

    public function insertKavling(array $fields): int
    {
        if (! $this->kavlingModel->insert($fields)) {
            return 0;
        }

        return (int) $this->kavlingModel->getInsertID();
    }

    public function updateKavling(int $id, array $fields): bool
    {
        return (bool) $this->kavlingModel->update($id, $fields);
    }

    public function findKavlingsByIdsRaw(array $ids): array
    {
        $ids = array_values(array_filter(array_map('intval', $ids), static fn (int $id): bool => $id > 0));
        if ($ids === []) {
            return [];
        }

        return $this->db->table('kavling')
            ->whereIn('id_kavling', $ids)
            ->get()
            ->getResultArray();
    }

    public function findKavlingPerintahBangunSnapshot(int $id): ?object
    {
        if ($id <= 0) {
            return null;
        }

        return $this->kavlingModel
            ->select('id_kavling, id_mkdt, perintah_bangun, perintah_bangun_tgl, perintah_bangun_oleh')
            ->find($id);
    }

    public function findKavlingWithRelationsById(int $id): ?object
    {
        if ($id <= 0) {
            return null;
        }

        return $this->kavlingModel
            ->select('
                kavling.*,
                jalan.nama_jalan,
                cluster.id_cluster,
                cluster.nama_cluster,
                tipe.id_tipe,
                tipe.tipe_rumah,
                tipe.no_tipe_rumah,
                tipe.id_gambar_kerja
            ')
            ->join('jalan', 'jalan.id_jalan = kavling.id_jalan')
            ->join('cluster', 'cluster.id_cluster = jalan.id_cluster')
            ->join('proyek', 'proyek.id_proyek = cluster.id_proyek')
            ->join('tipe', 'kavling.id_tipe = tipe.id_tipe')
            ->where('kavling.id_kavling', $id)
            ->first();
    }

    public function findKavlingsWithRelationsByIds(array $ids): array
    {
        $normalizedIds = array_values(array_filter(array_map('intval', $ids), static fn (int $id): bool => $id > 0));
        if ($normalizedIds === []) {
            return [];
        }

        $rows = $this->kavlingModel
            ->select('
                kavling.*,
                jalan.nama_jalan,
                cluster.id_cluster,
                cluster.nama_cluster,
                tipe.id_tipe,
                tipe.tipe_rumah,
                tipe.no_tipe_rumah,
                tipe.id_gambar_kerja
            ')
            ->join('jalan', 'jalan.id_jalan = kavling.id_jalan')
            ->join('cluster', 'cluster.id_cluster = jalan.id_cluster')
            ->join('proyek', 'proyek.id_proyek = cluster.id_proyek')
            ->join('tipe', 'kavling.id_tipe = tipe.id_tipe')
            ->whereIn('kavling.id_kavling', $normalizedIds)
            ->findAll();

        $byId = [];
        foreach ($rows as $row) {
            $byId[(int) $row->id_kavling] = $row;
        }

        $ordered = [];
        foreach ($normalizedIds as $id) {
            if (isset($byId[$id])) {
                $ordered[] = $byId[$id];
            }
        }

        return $ordered;
    }

    public function findHargaKavlingById(int $id): ?object
    {
        if ($id <= 0) {
            return null;
        }

        return $this->kavlingModel
            ->select('
                kavling.id_kavling,
                kavling.harga_akhir,
                kavling.no_kavling,
                jalan.nama_jalan,
                tipe.id_tipe,
                tipe.tipe_rumah,
                tgl_harga,
                row,
                hargajual.lb as hj_lb,
                hargajual.lt as hj_lt,
                hargajual,
                hargajual_net,
                ppn,
                kpr,
                uang_muka,
                bphtb,
                biaya_adm,
                biaya_proses,
                hargajual.keterangan as ket_hj,
                hargajual.id_filehj,
                fhj.lokasi,
                fhj.file_name
            ')
            ->join('jalan', 'jalan.id_jalan = kavling.id_jalan')
            ->join('tipe', 'kavling.id_tipe = tipe.id_tipe')
            ->join('hargajual', 'hargajual.id = kavling.harga_akhir', 'left')
            ->join('file_hargajual fhj', 'hargajual.id_filehj = fhj.id_filehj', 'left')
            ->where('kavling.id_kavling', $id)
            ->first();
    }

    public function findHargaKavlingsByIds(array $ids): array
    {
        $normalizedIds = array_values(array_filter(array_map('intval', $ids), static fn (int $id): bool => $id > 0));
        if ($normalizedIds === []) {
            return [];
        }

        $rows = $this->kavlingModel
            ->select('
                kavling.id_kavling,
                kavling.harga_akhir,
                kavling.no_kavling,
                jalan.nama_jalan,
                tipe.id_tipe,
                tipe.tipe_rumah,
                tgl_harga,
                row,
                hargajual.lb as hj_lb,
                hargajual.lt as hj_lt,
                hargajual,
                hargajual_net,
                ppn,
                kpr,
                uang_muka,
                bphtb,
                biaya_adm,
                biaya_proses,
                hargajual.keterangan as ket_hj,
                hargajual.id_filehj,
                fhj.lokasi,
                fhj.file_name
            ')
            ->join('jalan', 'jalan.id_jalan = kavling.id_jalan')
            ->join('tipe', 'kavling.id_tipe = tipe.id_tipe')
            ->join('hargajual', 'hargajual.id = kavling.harga_akhir', 'left')
            ->join('file_hargajual fhj', 'hargajual.id_filehj = fhj.id_filehj', 'left')
            ->whereIn('kavling.id_kavling', $normalizedIds)
            ->findAll();

        $byId = [];
        foreach ($rows as $row) {
            $byId[(int) $row->id_kavling] = $row;
        }

        $ordered = [];
        foreach ($normalizedIds as $id) {
            if (isset($byId[$id])) {
                $ordered[] = $byId[$id];
            }
        }

        return $ordered;
    }

    public function findTurunPembangunanByIds(array $ids): array
    {
        $normalizedIds = array_values(array_filter(array_map('intval', $ids), static fn (int $id): bool => $id > 0));
        if ($normalizedIds === []) {
            return [];
        }

        return $this->kavlingModel
            ->select('
                kavling.id_kavling,
                kavling.id_jalan,
                kavling.no_kavling,
                proyek.id_proyek,
                cluster.id_cluster,
                kavling.perintah_bangun,
                kavling.perintah_bangun_tgl,
                kavling.perintah_bangun_oleh,
                kavling.perintah_bangun_file,
                jalan.nama_jalan,
                cluster.nama_cluster,
                tipe.id_tipe,
                tipe.tipe_rumah,
                tipe.no_tipe_rumah,
                username
            ')
            ->join('jalan', 'jalan.id_jalan = kavling.id_jalan')
            ->join('cluster', 'cluster.id_cluster = jalan.id_cluster')
            ->join('proyek', 'proyek.id_proyek = cluster.id_proyek')
            ->join('tipe', 'kavling.id_tipe = tipe.id_tipe')
            ->join('users', 'users.id = kavling.perintah_bangun_oleh', 'left')
            ->whereIn('kavling.id_kavling', $normalizedIds)
            ->findAll();
    }

    // ─── DETAIL KAVLING (GET_DETAIL) ───

    public function getDetailMkdt(int $idMkdt): ?object
    {
        if ($idMkdt <= 0) {
            return null;
        }

        return $this->mkdtModel
            ->select('
                mkdt.*,
                konsumen.nama_konsumen,
                konsumen.nik as nik_konsumen,
                konsumen.hp_konsumen,
                konsumen.alamat_konsumen,
                konsumen.status_konsumen,
                konsumen.no_spptb,
                konsumen.npwp,
                konsumen.sales,
                konsumen.file_npwp,
                konsumen.file_ktp,
                konsumen.file_data_diri,
                konsumen.email_konsumen,
                konsumen.kode_referal,
                referrer.kode_referal as referred_by_kode,
                referrer.nama_konsumen as referred_by_nama,
                list_bank.bank as nama_bank,
                username
            ')
            ->join('konsumen', 'konsumen.id_konsumen = mkdt.id_konsumen')
            ->join('referrals', 'referrals.id_mkdt_referred = mkdt.id_mkdt', 'left')
            ->join('konsumen referrer', 'referrer.id_konsumen = referrals.id_konsumen_referrer', 'left')
            ->join('list_bank', 'list_bank.id = mkdt.id_bank', 'left')
            ->join('users', 'users.id = mkdt.edit_by')
            ->where('mkdt.id_mkdt', $idMkdt)
            ->first();
    }

    public function getDetailKavling(int $idKavling): ?object
    {
        if ($idKavling <= 0) {
            return null;
        }

        return $this->kavlingModel
            ->select('
                kavling.id_kavling,
                perintah_bangun,
                perintah_bangun_tgl,
                perintah_bangun_file,
                users.username,
                kavling.id_tipe,
                pajak.pph42_id_billing,
                pajak.pph42_ntpn,
                pajak.pph42_nilai,
                pajak.pph42_tgl_bayar,
                kavling.harga_akhir,
                kavling.harga_akhir_tgl,
                u_ha.username as harga_akhir_oleh_username
            ')
            ->join('users', 'users.id = kavling.perintah_bangun_oleh', 'left')
            ->join('users u_ha', 'u_ha.id = kavling.harga_akhir_oleh', 'left')
            ->join('pajak', 'pajak.id_mkdt = kavling.id_mkdt', 'left')
            ->where('id_kavling', $idKavling)
            ->first();
    }

    public function getDetailTipe(int $idTipe): ?object
    {
        if ($idTipe <= 0) {
            return null;
        }

        return $this->db->table('tipe')
            ->select('
                no_tipe_rumah,
                tipe_rumah,
                lb,
                lt,
                jumlah_kamar_tidur,
                jumlah_kamar_mandi,
                spesifikasi_teknis_atap,
                spesifikasi_teknis_dinding,
                spesifikasi_teknis_lantai,
                spesifikasi_teknis_pondasi,
                id_gambar_tipe,
                id_gambar_denah
            ')
            ->where('id_tipe', $idTipe)
            ->get()
            ->getRow();
    }

    public function getDetailPricelist(int $idHargaJual): ?object
    {
        if ($idHargaJual <= 0) {
            return null;
        }

        return $this->db->table('hargajual')
            ->where('id', $idHargaJual)
            ->get()
            ->getRow();
    }

    public function getDetailKeuanganByMkdt(int $idMkdt): array
    {
        if ($idMkdt <= 0) {
            return [];
        }

        return $this->db->table('keuangan')
            ->select('*')
            ->where('id_mkdt', $idMkdt)
            ->where('is_void', 0)
            ->get()
            ->getResult();
    }

    public function getLastQueryString(): string
    {
        return (string) $this->db->getLastQuery();
    }

    public function getDetailLogPembayaranByMkdt(int $idMkdt): array
    {
        if ($idMkdt <= 0) {
            return [];
        }

        return $this->db->table('log_pembayaran')
            ->select('log_pembayaran.id_pembayaran, log_pembayaran.nominal, log_pembayaran.payment_type')
            ->where('log_pembayaran.id_mkdt', $idMkdt)
            ->where('log_pembayaran.is_deleted', 0)
            ->get()
            ->getResult();
    }

    public function getDetailLogPembayaranDetailByMkdt(int $idMkdt): array
    {
        if ($idMkdt <= 0) {
            return [];
        }

        return $this->db->table('log_pembayaran_detail lpd')
            ->select('lpd.id_pembayaran, lpd.id_keuangan_item_list, lpd.booking_is_installment, kl.item, kl.kategori, COALESCE(SUM(lpd.nominal), 0) AS nominal')
            ->join('log_pembayaran lp', 'lp.id_pembayaran = lpd.id_pembayaran')
            ->join('keuangan_item_list kl', 'kl.id_keuangan_item_list = lpd.id_keuangan_item_list')
            ->where('lp.id_mkdt', $idMkdt)
            ->where('lp.is_deleted', 0)
            ->groupBy(['lpd.id_pembayaran', 'lpd.id_keuangan_item_list', 'lpd.booking_is_installment', 'kl.item', 'kl.kategori'])
            ->orderBy('lpd.id_pembayaran', 'ASC')
            ->orderBy('lpd.id_keuangan_item_list', 'ASC')
            ->get()
            ->getResult();
    }

    public function getDetailPaymentSummaryByMkdt(int $idMkdt): ?object
    {
        if ($idMkdt <= 0) {
            return null;
        }

        return $this->db->table('mkdt_payment_summary')
            ->where('id_mkdt', $idMkdt)
            ->get()
            ->getRow();
    }

    public function getDetailLastKeuanganUserByMkdt(int $idMkdt): ?object
    {
        if ($idMkdt <= 0) {
            return null;
        }

        $rows = $this->db->table('log_pembayaran')
            ->select('users.username, log_pembayaran.created_at')
            ->join('users', 'users.id = log_pembayaran.add_by')
            ->where('id_mkdt', $idMkdt)
            ->orderBy('log_pembayaran.created_at', 'desc')
            ->get()
            ->getResult();

        return count($rows) > 0 ? $rows[0] : null;
    }

    public function getDetailLegal(int $idLegal): ?object
    {
        if ($idLegal <= 0) {
            return null;
        }

        return $this->legalModel
            ->select('legal.*, a.username as uadd_by, b.username as uedit_by')
            ->where('id_legal', $idLegal)
            ->join('users a', 'a.id = legal.add_by', 'left')
            ->join('users b', 'b.id = legal.edit_by', 'left')
            ->first();
    }

    public function getDetailProduksi(int $idKavling): ?object
    {
        if ($idKavling <= 0) {
            return null;
        }

        return $this->produksiModel
            ->select('
                produksi.*,
                a.username as tanggal_pembangunan_oleh_u,
                b.username as tanggal_pembangunan_diubah_oleh_u,
                c.username as tanggal_selesai_pembangunan_diubah_oleh_u,
                sumurbor,
                sumurbor_tanggal,
                sumurbor_keterangan,
                d.username as sumurbor_oleh_u
            ')
            ->where('id_kavling', $idKavling)
            ->join('users as a', 'a.id = produksi.tanggal_pembangunan_oleh', 'left')
            ->join('users as b', 'b.id = produksi.tanggal_pembangunan_diubah_oleh', 'left')
            ->join('users as c', 'c.id = produksi.tanggal_selesai_pembangunan_diubah_oleh', 'left')
            ->join('kavling', 'kavling.id_produksi = produksi.id_produksi', 'left')
            ->join('users as d', 'd.id = kavling.sumurbor_oleh', 'left')
            ->first();
    }

    public function getDetailFileProduksi(int $idKavling): array
    {
        if ($idKavling <= 0) {
            return [];
        }

        return $this->db->table('file_produksi')
            ->select('file_produksi.*, username')
            ->join('users', 'file_produksi.upload_by = users.id')
            ->where('id_kavling', $idKavling)
            ->get()
            ->getResult();
    }

    public function getDetailFilePph(int $idKavling): array
    {
        if ($idKavling <= 0) {
            return [];
        }

        $sql = '
            (SELECT
                file_upload.*,
                c.username as uupload_by
            FROM `file_upload`
            LEFT JOIN users AS c ON file_upload.upload_by = c.id
            WHERE `id_kavling` = ?
                AND `id_group` = 10
                AND `kategori` = 9
            ORDER BY `upload_at` DESC
            LIMIT 1)
            UNION ALL
            (SELECT
                file_upload.*,
                c.username as uupload_by
            FROM `file_upload`
            LEFT JOIN users AS c ON file_upload.upload_by = c.id
            WHERE `id_kavling` = ?
                AND `id_group` = 10
                AND `kategori` = 10
            ORDER BY `upload_at` DESC
            LIMIT 1)
        ';

        return $this->db->query($sql, [$idKavling, $idKavling])->getResult();
    }

    public function getDetailFilePpn(int $idKavling): array
    {
        if ($idKavling <= 0) {
            return [];
        }

        $sql = '
            (SELECT
                file_upload.*,
                c.username as uupload_by
            FROM `file_upload`
            LEFT JOIN users AS c ON file_upload.upload_by = c.id
            WHERE `id_kavling` = ?
                AND `id_group` = 10
                AND `kategori` = 11
            ORDER BY `upload_at` DESC
            LIMIT 1)
            UNION ALL
            (SELECT
                file_upload.*,
                c.username as uupload_by
            FROM `file_upload`
            LEFT JOIN users AS c ON file_upload.upload_by = c.id
            WHERE `id_kavling` = ?
                AND `id_group` = 10
                AND `kategori` = 12
            ORDER BY `upload_at` DESC
            LIMIT 1)
            UNION ALL
            (SELECT
                file_upload.*,
                c.username as uupload_by
            FROM `file_upload`
            LEFT JOIN users AS c ON file_upload.upload_by = c.id
            WHERE `id_kavling` = ?
                AND `id_group` = 10
                AND `kategori` = 13
            ORDER BY `upload_at` DESC
            LIMIT 1)
        ';

        return $this->db->query($sql, [$idKavling, $idKavling, $idKavling])->getResult();
    }

    public function getDetailHutangSubkon(int $idKavling): array
    {
        if ($idKavling <= 0) {
            return [];
        }

        return $this->db->table('cashout_subkon_detail_allocation csda')
            ->select('csda.nominal, csd.tanggal_jatuh_tempo, csd.berita_acara, csd.keterangan, cs.nomor_surat')
            ->join('cashout_subkon_detail csd', 'csd.id_cashout_subkon_detail = csda.id_cashout_subkon_detail')
            ->join('cashout_subkon cs', 'cs.id_cashout_subkon = csda.id_cashout_subkon')
            ->where('csda.id_kavling', $idKavling)
            ->where('csd.status >=', 1)
            ->where('csd.status <=', 3)
            ->orderBy('csd.tanggal_jatuh_tempo', 'asc')
            ->get()
            ->getResult();
    }

    public function getDetailFinanceLedgerIncome(?int $idMkdt, ?int $idKavling): array
    {
        if (! $this->db->tableExists('finance_ledger')) {
            return [];
        }

        $builder = $this->db->table('finance_ledger')
            ->select('tanggal_transaksi, label, nominal, keterangan, source_type, source_id')
            ->where('direction', 'income')
            ->where('status', 'active')
            ->where('is_deleted', 0);

        if ($idMkdt && $idKavling) {
            $builder->groupStart()
                ->where('id_mkdt', $idMkdt)
                ->orWhere('id_kavling', $idKavling)
                ->groupEnd();
        } elseif ($idMkdt) {
            $builder->where('id_mkdt', $idMkdt);
        } elseif ($idKavling) {
            $builder->where('id_kavling', $idKavling);
        }

        return $builder
            ->orderBy('tanggal_transaksi', 'desc')
            ->orderBy('id', 'desc')
            ->get()
            ->getResult();
    }

    public function getDetailBankRetensiPending(int $idKavling): float
    {
        if ($idKavling <= 0 || ! $this->db->tableExists('bank_kpr_disbursement')) {
            return 0.0;
        }

        $row = $this->db->table('bank_kpr_disbursement')
            ->select('COALESCE(SUM(nominal_retensi), 0) as total', false)
            ->where('id_kavling', $idKavling)
            ->where('deleted_at', null)
            ->where('status !=', 'void')
            ->get()
            ->getRow();

        return (float) ($row->total ?? 0);
    }

    public function getDetailDanaAkadSummary(int $idKavling): array
    {
        if ($idKavling <= 0 || ! $this->db->tableExists('dana_akad')) {
            return [
                'total_nominal' => 0.0,
                'total_pending' => 0.0,
            ];
        }

        $row = $this->db->table('dana_akad')
            ->select('
                COALESCE(SUM(nominal), 0) as total_nominal,
                COALESCE(SUM(CASE WHEN COALESCE(sudah_cair, 0) = 1 THEN 0 ELSE GREATEST(COALESCE(nominal, 0) - COALESCE(nominal_cair, 0), 0) END), 0) as total_pending
            ', false)
            ->where('id_kavling', $idKavling)
            ->get()
            ->getRow();

        return [
            'total_nominal' => (float) ($row->total_nominal ?? 0),
            'total_pending' => (float) ($row->total_pending ?? 0),
        ];
    }

    public function getDetailBayarProduksi(int $idKavling): array
    {
        if ($idKavling <= 0) {
            return [];
        }

        return $this->db->table('list_bayar_produksi lc')
            ->select('lc.id as id_bayar_produksi, lc.item, lc.sort, c.*, u.username as add_by_u, e.username as edit_by_u')
            ->join('bayar_produksi c', 'c.id_item_produksi = lc.id and id_kavling = ' . $this->db->escape($idKavling), 'left')
            ->join('users u', 'u.id = c.add_by', 'left')
            ->join('users e', 'e.id = c.edit_by', 'left')
            ->where('lc.deleted_at', null)
            ->orderBy('lc.sort', 'ASC')
            ->orderBy('lc.id', 'ASC')
            ->get()
            ->getResult();
    }

    public function getDetailSi(int $idKavling): array
    {
        if ($idKavling <= 0) {
            return [];
        }

        return $this->db->table('list_si')
            ->select('si.*, list_si.nama')
            ->join('si', 'list_si.id = si.id_list_si and si.id_kavling = ' . $this->db->escape($idKavling), 'left')
            ->get()
            ->getResult();
    }
}
