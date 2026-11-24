<?php

namespace App\Repositories\Proyek;

class TipeRepository
{
    protected $db;

    public function __construct()
    {
        $this->db = db_connect();
    }

    public function getAll(array $params): array
    {
        $builder = $this->db->table('tipe')
            ->select('id_tipe, tipe.id_proyek, no_tipe_rumah, tipe_rumah, lb, lt, jumlah_kamar_tidur, jumlah_kamar_mandi, spesifikasi_teknis_atap, spesifikasi_teknis_dinding, spesifikasi_teknis_lantai, spesifikasi_teknis_pondasi, harga, keterangan, nama_proyek, is_subsidi, tipe.is_active, tipe.id_gambar_tipe, tipe.id_gambar_denah, tipe.id_gambar_kerja, (SELECT count(id_kavling) FROM kavling WHERE kavling.id_tipe = tipe.id_tipe AND deleted_at IS NULL) as jumlah_kavling')
            ->join('proyek', 'proyek.id_proyek = tipe.id_proyek')
            ->orderBy('tipe_rumah', 'desc');

        if (!empty($params['id_proyek'])) {
            $builder->where('tipe.id_proyek', $params['id_proyek']);
        }

        $onlyActive = $params['only_active'] ?? null;
        if ($onlyActive === 1 || $onlyActive === '1' || $onlyActive === true || (is_string($onlyActive) && strtolower($onlyActive) === 'true')) {
            $builder->where('tipe.is_active', 1);
        }

        $isActive = $params['is_active'] ?? null;
        if ($isActive === 0 || $isActive === 1 || $isActive === '0' || $isActive === '1') {
            $builder->where('tipe.is_active', (int) $isActive);
        }

        if (!empty($params['search'])) {
            $builder->groupStart()
                ->like('tipe.no_tipe_rumah', $params['search'])
                ->orLike('tipe.tipe_rumah', $params['search'])
            ->groupEnd();
        }

        return $builder->get()->getResult();
    }

    public function getDataTables(array $params): array
    {
        $search = is_array($params['search'] ?? null)
            ? (string) ($params['search']['value'] ?? '')
            : (string) ($params['search'] ?? '');
        $idProyek = $params['id_proyek'] ?? null;

        $isActiveRaw = $params['is_active'] ?? null;
        $isActive = ($isActiveRaw === 0 || $isActiveRaw === 1 || $isActiveRaw === '0' || $isActiveRaw === '1')
            ? (string) $isActiveRaw
            : null;

        $recordsTotal = $this->countDataTables('', $idProyek);
        $recordsFiltered = $this->countDataTables($search, $idProyek, $isActive);

        $builder = $this->dataTablesBaseQuery($search, $idProyek, $isActive);

        if (isset($params['start'], $params['length']) && (int) $params['length'] > 0) {
            $builder->limit((int) $params['length'], (int) $params['start']);
        }

        return [
            'draw' => $params['draw'] ?? null,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'rows' => $builder->get()->getResult(),
        ];
    }

    public function getGambarKerjaHistory(int $idTipe): array
    {
        return $this->db->table('gambar_kerja')
            ->select('gambar_kerja.*, u.username as uadd_by')
            ->join('users as u', 'u.id = gambar_kerja.upload_by', 'left')
            ->where('id_tipe', $idTipe)
            ->groupStart()
                ->where('tipe', 'gambarkerja')
                ->orWhere('tipe', '')
                ->orWhere('tipe IS NULL', null, false)
            ->groupEnd()
            ->orderBy('upload_at', 'desc')
            ->get()
            ->getResult();
    }

    public function ensureConfigShape(string $tipeRumah, string $fill): void
    {
        $exists = $this->db->table('config_shape')
            ->where('config_name', $tipeRumah)
            ->countAllResults() > 0;

        if (!$exists) {
            $this->db->table('config_shape')->insert([
                'config_name' => $tipeRumah,
                'fill' => $fill,
            ]);
        }
    }

    private function countDataTables(string $search = '', $idProyek = null, ?string $isActive = null): int
    {
        return count($this->dataTablesBaseQuery($search, $idProyek, $isActive)->get()->getResult());
    }

    private function dataTablesBaseQuery(string $search = '', $idProyek = null, ?string $isActive = null)
    {
        $builder = $this->db->table('tipe')
            ->select('id_tipe, tipe.id_proyek, no_tipe_rumah, tipe_rumah, lb, lt, jumlah_kamar_tidur, jumlah_kamar_mandi, harga, keterangan, nama_proyek, is_subsidi, tipe.is_active, tipe.id_gambar_tipe, tipe.id_gambar_denah, tipe.id_gambar_kerja, (SELECT count(id_kavling) FROM kavling WHERE kavling.id_tipe = tipe.id_tipe AND deleted_at IS NULL) as jumlah_kavling')
            ->join('proyek', 'proyek.id_proyek = tipe.id_proyek')
            ->orderBy('tipe_rumah', 'desc');

        if (!empty($idProyek)) {
            $builder->where('tipe.id_proyek', $idProyek);
        }

        if ($isActive === '0' || $isActive === '1') {
            $builder->where('tipe.is_active', (int) $isActive);
        }

        if ($search !== '') {
            $builder->groupStart()
                ->like('tipe.no_tipe_rumah', $search)
                ->orLike('tipe.tipe_rumah', $search)
            ->groupEnd();
        }

        return $builder;
    }

    public function getListKavling(int $idTipe): array
    {
        return $this->db->table('kavling')
            ->select('kavling.id_kavling, kavling.no_kavling, kavling.status_kavling, jalan.nama_jalan')
            ->join('jalan', 'jalan.id_jalan = kavling.id_jalan', 'left')
            ->where('kavling.id_tipe', $idTipe)
            ->get()
            ->getResult();
    }
}
