<?php

namespace App\Repositories\Produksi;

use App\Services\HistoryService;

class ProduksiRepository
{
    protected $db;
    protected HistoryService $historyService;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->historyService = new HistoryService();
    }

    public function getFilesByKavling(int $idKavling): array
    {
        return $this->db->table('file_produksi')
            ->select('file_produksi.*, username')
            ->join('users', 'file_produksi.upload_by = users.id')
            ->where('id_kavling', $idKavling)
            ->get()->getResult();
    }

    public function insertFiles(array $files): bool
    {
        return (bool) $this->db->table('file_produksi')->insertBatch($files);
    }

    public function hasProduksiChangeHistoryTable(): bool
    {
        return $this->historyService->hasTable();
    }

    public function insertProduksiChangeHistory(array $fields): bool
    {
        $files = $this->decodeJson($fields['files'] ?? null);

        return $this->historyService->log('produksi', [
            'reference_type' => 'produksi',
            'reference_id'   => $fields['id_produksi'] ?? null,
            'id_kavling'     => $fields['id_kavling'] ?? null,
            'action'         => $fields['action'] ?? 'update',
            'summary'        => $fields['summary'] ?? null,
            'old_data'       => $fields['old_data'] ?? null,
            'new_data'       => $fields['new_data'] ?? null,
            'metadata'       => ['files' => $files],
            'add_by'         => $fields['add_by'] ?? (function_exists('user_id') ? user_id() : null),
            'created_at'     => $fields['created_at'] ?? date('Y-m-d H:i:s'),
        ]);
    }

    public function countProduksiChangeHistory(int $idKavling): int
    {
        if (!$this->hasProduksiChangeHistoryTable()) {
            return 0;
        }

        return $this->historyService->getByKavling($idKavling, ['produksi'], 1, 0)['history_total'];
    }

    public function getProduksiChangeHistory(int $idKavling, int $limit, int $offset): array
    {
        if (!$this->hasProduksiChangeHistoryTable()) {
            return [];
        }

        $result = $this->historyService->getByKavling($idKavling, ['produksi'], $limit, $offset);
        $rows = [];

        foreach ($result['history'] as $row) {
            $metadata = is_array($row->metadata ?? null) ? $row->metadata : [];
            $files = $metadata['files'] ?? [];
            if (is_string($files)) {
                $decodedFiles = json_decode($files, true);
                $files = is_array($decodedFiles) ? $decodedFiles : [];
            }

            $row->id_produksi = $row->reference_id;
            $row->old_data = json_encode($row->old_data ?? [], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            $row->new_data = json_encode($row->new_data ?? [], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            $row->files = json_encode($files, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            $rows[] = $row;
        }

        return $rows;
    }

    private function decodeJson($value): array
    {
        if (is_array($value)) {
            return $value;
        }

        if (! is_string($value) || $value === '') {
            return [];
        }

        $decoded = json_decode($value, true);
        return is_array($decoded) ? $decoded : [];
    }

    public function getFileById(int $id): ?object
    {
        return $this->db->table('file_produksi')->where('id', $id)->get()->getRow() ?: null;
    }

    public function deleteFileById(int $id): bool
    {
        return (bool) $this->db->table('file_produksi')->delete(['id' => $id]);
    }

    public function getBayarList(int $idKavling): array
    {
        return $this->db->table('list_bayar_produksi lc')
            ->select('lc.id as id_bayar_produksi, lc.item, lc.sort, c.*, u.username as add_by_u, e.username as edit_by_u')
            ->join('bayar_produksi c', 'c.id_item_produksi = lc.id and id_kavling = ' . $this->db->escape($idKavling), 'left')
            ->join('users u', 'u.id = c.add_by', 'left')
            ->join('users e', 'e.id = c.edit_by', 'left')
            ->where('lc.deleted_at', null)
            ->orderBy('lc.sort', 'ASC')
            ->orderBy('lc.id', 'ASC')
            ->get()->getResult();
    }

    public function getBayarItemList(string $search): array
    {
        $builder = $this->db->table('list_bayar_produksi lc')
            ->select('lc.id, lc.item')
            ->where('lc.deleted_at', null)
            ->orderBy('lc.sort', 'ASC')
            ->orderBy('lc.id', 'ASC');

        if ($search !== '') {
            $builder->like('lc.item', $search);
        }

        return $builder->get()->getResult();
    }

    public function getRiwayatBayarByKavling(int $idKavling): array
    {
        return $this->db->table('bayar_produksi c')
            ->select('c.*, lc.item, u.username as uadd_by, e.username as uedit_by')
            ->join('list_bayar_produksi lc', 'lc.id = c.id_item_produksi')
            ->join('users u', 'u.id = c.add_by', 'left')
            ->join('users e', 'e.id = c.edit_by', 'left')
            ->where('c.id_kavling', $idKavling)
            ->orderBy('c.tanggal_bayar', 'DESC')
            ->get()->getResult();
    }

    public function insertBayarSingle(array $data): ?int
    {
        $inserted = (bool) $this->db->table('bayar_produksi')->insert([
            'id_kavling'       => $data['id_kavling'],
            'id_item_produksi' => $data['id_item_produksi'],
            'nominal'          => $data['nominal'],
            'keterangan'       => $data['keterangan'] ?? '',
            'tanggal_bayar'    => $data['tanggal_bayar'],
            'add_by'           => $data['add_by'],
            'created_at'       => date('Y-m-d H:i:s'),
        ]);

        if (!$inserted) {
            return null;
        }

        return (int) $this->db->insertID();
    }

    public function deleteBayar(int $id): ?int
    {
        $row = $this->db->table('bayar_produksi')->where('id', $id)->get()->getRow();
        if (!$row) {
            return null;
        }

        $idKavling = (int) $row->id_kavling;
        $this->db->table('bayar_produksi')->where('id', $id)->delete();

        return $idKavling;
    }

    public function getByIdWithUsers(int $idProduksi): ?object
    {
        return $this->db->table('produksi')
            ->select('
                produksi.*,
                a.username as tanggal_pembangunan_oleh_u,
                b.username as tanggal_pembangunan_diubah_oleh_u,
                c.username as tanggal_selesai_pembangunan_diubah_oleh_u,
                sumurbor, sumurbor_tanggal, sumurbor_keterangan, kavling.sumurbor_updated,
                d.username as sumurbor_oleh_u
            ')
            ->where('produksi.id_produksi', $idProduksi)
            ->join('users as a', 'a.id = produksi.tanggal_pembangunan_oleh', 'left')
            ->join('users as b', 'b.id = produksi.tanggal_pembangunan_diubah_oleh', 'left')
            ->join('users as c', 'c.id = produksi.tanggal_selesai_pembangunan_diubah_oleh', 'left')
            ->join('kavling', 'kavling.id_produksi = produksi.id_produksi', 'left')
            ->join('users as d', 'd.id = kavling.sumurbor_oleh', 'left')
            ->get()->getRow() ?: null;
    }

    public function getSlfList(?int $idProyek = null): array
    {
        $subQuery = $this->db->table('kavling')
            ->select('GROUP_CONCAT(concat(jalan.nama_jalan," No. ", kavling.no_kavling ) ORDER BY jalan.nama_jalan, kavling.no_kavling SEPARATOR \', \')', false)
            ->join('jalan', 'jalan.id_jalan = kavling.id_jalan')
            ->where('FIND_IN_SET(kavling.id_kavling, list_slf.id_kavling)', null, false)
            ->getCompiledSelect();

        $builder = $this->db->table('list_slf')
            ->select('
                list_slf.id,
                list_slf.id AS id_slf,
                list_slf.no_slf,
                list_slf.no_slf AS nomor_slf,
                list_slf.id_proyek,
                list_slf.id_kavling,
                list_slf.tanggal_slf,
                list_slf.penanggungjawab,
                list_slf.fungsi_utama,
                list_slf.fungsi_tambahan,
                list_slf.jenis_bangunan,
                list_slf.nama_bangunan,
                list_slf.nomor_pendaftaran_bangunan,
                list_slf.penerbitan_slf_no,
                list_slf.penerbitan_slf_tgl,
                list_slf.perpanjangan_slf_no,
                list_slf.perpanjangan_slf_tgl,
                list_slf.perpanjangan_slf_ke,
                list_slf.persyaratan_administrasi,
                list_slf.persyaratan_fungsi_bangunan,
                list_slf.persyaratan_peruntukan,
                list_slf.persyaratan_tata_bangunan,
                list_slf.persyaratan_kelaikan,
                users.username,
                users.username AS oleh,
                list_slf.created_at
            ')
            ->select("($subQuery) AS kavling", false)
            ->join('users', 'users.id = list_slf.add_by', 'left');

        if ($idProyek !== null && $idProyek > 0) {
            $builder->where('list_slf.id_proyek', $idProyek);
        }

        return $builder
            ->groupBy('list_slf.id')
            ->orderBy('list_slf.id', 'DESC')
            ->get()
            ->getResult();
    }

    public function getSlfById(int $id): ?object
    {
        $result = $this->db->table('list_slf')->where('id', $id)->get()->getResult();
        return $result[0] ?? null;
    }

    public function getSlfKavlingData(array $idKavlingList): array
    {
        return $this->db->table('kavling')
            ->select('
                `proyek`.`nama_proyek`,
                `proyek`.`alamat_proyek`,
                `proyek`.`kelurahan`,
                `proyek`.`kecamatan`,
                `proyek`.`kota`,
                `proyek`.`provinsi`,
                `proyek`.`nama_pt`,
                `cluster`.`nama_cluster`,
                `jalan`.`nama_jalan`,
                `tipe`.`no_tipe_rumah`,
                `tipe`.`tipe_rumah`,
                `kavling`.`no_kavling`,
                konsumen.nama_konsumen
            ')
            ->join('jalan', 'jalan.id_jalan = kavling.id_jalan')
            ->join('cluster', 'cluster.id_cluster = jalan.id_cluster')
            ->join('proyek', 'cluster.id_proyek = proyek.id_proyek')
            ->join('tipe', 'tipe.id_tipe = kavling.id_tipe')
            ->join('mkdt', 'mkdt.id_kavling = kavling.id_kavling', 'left')
            ->join('konsumen', 'konsumen.id_konsumen = mkdt.id_konsumen', 'left')
            ->whereIn('kavling.id_kavling', $idKavlingList)
            ->orderBy('jalan.nama_jalan', 'asc')
            ->orderBy('kavling.no_kavling', 'asc')
            ->get()->getResult();
    }

    public function insertSlf(array $data): bool
    {
        return (bool) $this->db->table('list_slf')->insert($data);
    }

    public function deleteSlfById(int $id): bool
    {
        return (bool) $this->db->table('list_slf')->delete(['id' => $id]);
    }

    public function getKavlingByProyek(int $idProyek, string $search, ?int $idKavling = null): array
    {
        $builder = $this->db->table('kavling')
            ->select("
                kavling.id_kavling,
                kavling.id_mkdt,
                kavling.id_produksi,
                cluster.id_cluster,
                cluster.nama_cluster,
                jalan.id_jalan,
                nama_jalan,
                no_kavling,
                nama_konsumen,
                tipe.id_tipe,
                tipe.no_tipe_rumah,
                tipe.tipe_rumah,
                tipe.id_gambar_kerja,
                hargajual.id_tipe AS hj_tipe_rumah,
                COALESCE(NULLIF(TRIM(hargajual.id_tipe), ''), tipe.tipe_rumah) AS tipe_rumah_aktif,
                produksi.progres_bangunan,
                kavling.status_komplain,
                kavling.id_komplain
            ")
            ->join('jalan', 'jalan.id_jalan = kavling.id_jalan')
            ->join('cluster', 'cluster.id_cluster = jalan.id_cluster')
            ->join('proyek', 'proyek.id_proyek = cluster.id_proyek')
            ->join('tipe', 'kavling.id_tipe = tipe.id_tipe')
            ->join('mkdt', 'mkdt.id_mkdt = kavling.id_mkdt', 'left')
            ->join('konsumen', 'konsumen.id_konsumen = mkdt.id_konsumen', 'left')
            ->join('hargajual', 'hargajual.id = kavling.harga_akhir', 'left')
            ->join('produksi', 'produksi.id_produksi = kavling.id_produksi', 'left')
            ->where('proyek.id_proyek', $idProyek);

        if ($idKavling && $idKavling > 0) {
            $builder->where('kavling.id_kavling', $idKavling);
        } else {
            $builder
                ->groupStart()
                ->like('nama_jalan', $search)
                ->orLike('nama_konsumen', $search)
                ->orLike('no_kavling', $search)
                ->groupEnd();
        }

        return $builder->get()->getResult();
    }

    public function getKavlingProduksiById(int $idKavling): ?object
    {
        return $this->db->table('kavling')
            ->select("
                kavling.id_kavling,
                kavling.id_produksi,
                kavling.no_kavling,
                kavling.sumurbor,
                kavling.sumurbor_tanggal,
                kavling.sumurbor_keterangan,
                kavling.sumurbor_updated,
                d.username AS sumurbor_oleh_u,
                jalan.nama_jalan,
                cluster.nama_cluster,
                tipe.id_tipe,
                tipe.no_tipe_rumah,
                tipe.tipe_rumah,
                tipe.id_gambar_kerja,
                hargajual.id_tipe AS hj_tipe_rumah,
                COALESCE(NULLIF(TRIM(hargajual.id_tipe), ''), tipe.tipe_rumah) AS tipe_rumah_aktif
            ")
            ->join('jalan', 'jalan.id_jalan = kavling.id_jalan', 'left')
            ->join('cluster', 'cluster.id_cluster = jalan.id_cluster', 'left')
            ->join('tipe', 'tipe.id_tipe = kavling.id_tipe', 'left')
            ->join('hargajual', 'hargajual.id = kavling.harga_akhir', 'left')
            ->join('users as d', 'd.id = kavling.sumurbor_oleh', 'left')
            ->where('kavling.id_kavling', $idKavling)
            ->get()
            ->getRow() ?: null;
    }

    public function createOther(array $fields): bool
    {
        return (bool) $this->db->table('others')->insert($fields);
    }

    public function getOtherById(int $id): ?object
    {
        return $this->db->table('others')->where('id', $id)->get()->getRow() ?: null;
    }

    public function updateOthers(int $id, array $fields): bool
    {
        return (bool) $this->db->table('others')->where('id', $id)->update($fields);
    }

    public function hasJalanProgressHistoryTable(): bool
    {
        return $this->db->tableExists('produksi_jalan_progress_history');
    }

    public function insertJalanProgressHistory(array $fields): bool
    {
        return (bool) $this->db->table('produksi_jalan_progress_history')->insert($fields);
    }

    // ─── EXTENDED PROD DOMAIN ACCESS (ZERO CONTROLLER LAYER VIOLATIONS) ───

    public function getSubkonList(string $search): array
    {
        $builder = $this->db->table('subkon');
        if ($search !== '') {
            $builder->like('nama_subkon', $search);
        }
        return $builder->get()->getResult();
    }

    public function getGambarKerjaById(int $id): ?object
    {
        return $this->db->table('gambar_kerja')
            ->where('id_gambar_kerja', $id)
            ->get()
            ->getRow() ?: null;
    }

    public function getKavlingById(int $idKavling): ?object
    {
        return $this->db->table('kavling')
            ->where('id_kavling', $idKavling)
            ->get()
            ->getRow() ?: null;
    }

    public function getKavlingCheckStatus(int $idKavling): ?object
    {
        return $this->db->table('kavling')
            ->select('is_checked')
            ->where('id_kavling', $idKavling)
            ->get()
            ->getRow() ?: null;
    }

    public function getKavlingOwnerKomplain(int $idKavling, int $idKomplain): ?object
    {
        return $this->db->table('kavling')
            ->where('id_kavling', $idKavling)
            ->where('id_komplain', $idKomplain)
            ->get()
            ->getRow() ?: null;
    }

    public function updateKavling(int $idKavling, array $data): bool
    {
        return (bool) $this->db->table('kavling')
            ->where('id_kavling', $idKavling)
            ->update($data);
    }

    public function getProduksiRaw(int $idProduksi): ?array
    {
        $row = $this->db->table('produksi')
            ->where('id_produksi', $idProduksi)
            ->get()
            ->getRowArray();

        return $row ?: null;
    }

    public function insertProduksi(array $data): int
    {
        $this->db->table('produksi')->insert($data);
        return (int) $this->db->insertID();
    }

    public function updateProduksi(int $idProduksi, array $data): bool
    {
        return (bool) $this->db->table('produksi')
            ->where('id_produksi', $idProduksi)
            ->update($data);
    }

    public function getKomplainWithUsers(int $idKavling): ?object
    {
        return $this->db->table('kavling')
            ->select('
                status_komplain,
                kavling.id_kavling,
                komplain.*,
                s.username as username_komplain_oleh,
                p.username as username_ditangani_oleh,
                ss.username as username_selesai_oleh_sales,
                sp.username as username_selesai_oleh_produksi,
                lu.username as username_last_update
            ')
            ->join('komplain', 'komplain.id_komplain = kavling.id_komplain')
            ->join('users as s', 's.id = komplain.komplain_oleh', 'left')
            ->join('users as p', 'p.id = komplain.ditangani_oleh', 'left')
            ->join('users as ss', 'ss.id = komplain.selesai_oleh_sales', 'left')
            ->join('users as sp', 'sp.id = komplain.selesai_oleh_produksi', 'left')
            ->join('users as lu', 'lu.id = komplain.edit_by', 'left')
            ->where('kavling.id_kavling', $idKavling)
            ->get()
            ->getRow() ?: null;
    }

    public function insertKomplain(array $data): ?int
    {
        $inserted = (bool) $this->db->table('komplain')->insert($data);
        if (!$inserted) {
            return null;
        }

        return (int) $this->db->insertID();
    }

    public function updateKomplain(int $idKomplain, array $data): bool
    {
        return (bool) $this->db->table('komplain')
            ->where('id_komplain', $idKomplain)
            ->update($data);
    }

    public function deleteKomplain(int $idKomplain): bool
    {
        return (bool) $this->db->table('komplain')
            ->where('id_komplain', $idKomplain)
            ->delete();
    }

    public function getSerahTerimaByKavling(int $idKavling): ?object
    {
        return $this->db->table('kavling')
            ->select('
                is_serah_terima, 
                kavling.id_serah_terima, 
                kavling.id_kavling, 
                serah_terima.*,
                users.username
            ')
            ->join('serah_terima', 'serah_terima.id_serah_terima = kavling.id_serah_terima')
            ->join('users', 'users.id = serah_terima.edit_by', 'left')
            ->where('kavling.id_kavling', $idKavling)
            ->get()
            ->getRow() ?: null;
    }

    public function insertSerahTerima(array $data): ?int
    {
        $inserted = (bool) $this->db->table('serah_terima')->insert($data);
        if (!$inserted) {
            return null;
        }

        return (int) $this->db->insertID();
    }

    public function updateSerahTerima(int $idSerahTerima, array $data): bool
    {
        return (bool) $this->db->table('serah_terima')
            ->where('id_serah_terima', $idSerahTerima)
            ->update($data);
    }

    public function getChecklistWorkByKavling(int $idKavling): array
    {
        return $this->db->table('checklist_work')
            ->select('id_subitem')
            ->where('id_kavling', $idKavling)
            ->get()
            ->getResult();
    }

    public function getChecklistWorkWithUsers(int $idKavling): array
    {
        return $this->db->table('checklist_work')
            ->select('checklist_work.*, users.username as username_prod, c.username as username_sales')
            ->where('id_kavling', $idKavling)
            ->join('users', 'checklist_work.produksi_cek = users.id', 'left')
            ->join('users as c', 'checklist_work.sales_cek = c.id', 'left')
            ->get()
            ->getResult();
    }

    public function getChecklistWorkForProduksi(int $idKavling): array
    {
        return $this->db->table('checklist_work')
            ->select('checklist_work.*, username')
            ->where('id_kavling', $idKavling)
            ->join('users', 'checklist_work.produksi_cek = users.id')
            ->get()
            ->getResult();
    }

    public function getAllSubitemIds(): array
    {
        return $this->db->table('checklist_subitem')
            ->select('id_subitem')
            ->get()
            ->getResult();
    }

    public function upsertChecklistWork(int $idKavling, int $idSubitem, array $data): bool
    {
        $existing = $this->db->table('checklist_work')
            ->where('id_kavling', $idKavling)
            ->where('id_subitem', $idSubitem)
            ->get()
            ->getRow();

        if ($existing) {
            return (bool) $this->db->table('checklist_work')
                ->where('id_kavling', $idKavling)
                ->where('id_subitem', $idSubitem)
                ->update($data);
        }

        $data['id_kavling'] = $idKavling;
        $data['id_subitem'] = $idSubitem;
        return (bool) $this->db->table('checklist_work')->insert($data);
    }

    public function getChecklistSubItemsWithGroup(): array
    {
        return $this->db->table('checklist_subitem')
            ->select('
                checklist_subitem.id_subitem,
                checklist_subitem.nama_subitem,
                NULL AS satuan,
                checklist_item.id_item,
                checklist_item.nama_item,
                checklist_group.id_group,
                checklist_group.nama_group
            ')
            ->join('checklist_item', 'checklist_item.id_item = checklist_subitem.id_item')
            ->join('checklist_group', 'checklist_item.id_group = checklist_group.id_group')
            ->where('checklist_item.is_active', 1)
            ->where('checklist_group.is_active', 1)
            ->where('checklist_subitem.is_active', 1)
            ->orderBy('checklist_item.id_group', 'asc')
            ->orderBy('checklist_item.id_item', 'asc')
            ->orderBy('checklist_subitem.id_subitem', 'asc')
            ->get()
            ->getResult();
    }

    public function getProyekDetailForSlf(int $idProyek): ?object
    {
        if ($idProyek <= 0) {
            return null;
        }

        return $this->db->table('proyek')
            ->select('id_proyek, nama_proyek, alamat_proyek, kelurahan, kecamatan, kota, provinsi, nama_pt, logo, logo_pt, siteplan')
            ->where('id_proyek', $idProyek)
            ->get()
            ->getRow() ?: null;
    }

    public function hasUpdateTanggalPembangunanAccess(int $userId): bool
    {
        if ($userId <= 0) {
            return false;
        }

        $rows = $this->db->table('hak_akses')
            ->where('nama_akses', 'update_tanggal_pembangunan')
            ->like('id_users', (string) $userId)
            ->get()
            ->getResult();

        foreach ($rows as $row) {
            $ids = array_filter(array_map('trim', explode(';', (string) ($row->id_users ?? ''))));
            if (in_array((string) $userId, $ids, true)) {
                return true;
            }
        }

        return false;
    }
}
