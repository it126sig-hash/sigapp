<?php

namespace App\Repositories;

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
            ->select('id_tipe, tipe.id_proyek, no_tipe_rumah, tipe_rumah, lb, lt, harga, keterangan, nama_proyek')
            ->join('proyek', 'proyek.id_proyek = tipe.id_proyek')
            ->orderBy('tipe_rumah', 'desc');

        if (!empty($params['id_proyek'])) {
            $builder->where('tipe.id_proyek', $params['id_proyek']);
        }

        if (!empty($params['search'])) {
            $builder->like('no_tipe_rumah', $params['search']);
        }

        return $builder->get()->getResult();
    }

    public function getDataTables(array $params): array
    {
        $search = $params['search']['value'] ?? '';
        $idProyek = $params['id_proyek'] ?? null;

        $recordsTotal = $this->countDataTables('', $idProyek);
        $recordsFiltered = $this->countDataTables($search, $idProyek);

        $builder = $this->dataTablesBaseQuery($search, $idProyek);

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

    private function countDataTables(string $search = '', $idProyek = null): int
    {
        return count($this->dataTablesBaseQuery($search, $idProyek)->get()->getResult());
    }

    private function dataTablesBaseQuery(string $search = '', $idProyek = null)
    {
        $builder = $this->db->table('tipe')
            ->select('id_tipe, tipe.id_proyek, no_tipe_rumah, tipe_rumah, lb, lt, harga, keterangan, nama_proyek, is_subsidi')
            ->join('proyek', 'proyek.id_proyek = tipe.id_proyek')
            ->orderBy('tipe_rumah', 'desc');

        if (!empty($idProyek)) {
            $builder->where('tipe.id_proyek', $idProyek);
        }

        if ($search !== '') {
            $builder->like('tipe.no_tipe_rumah', $search);
        }

        return $builder;
    }
}
