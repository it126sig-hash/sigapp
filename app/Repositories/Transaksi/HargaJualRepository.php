<?php

namespace App\Repositories\Transaksi;

use App\Models\FileHargajualModel;
use App\Models\KavlingModel;
use App\Models\ProyekModel;
use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Database\BaseConnection;

class HargaJualRepository
{
    protected BaseConnection $db;
    protected ProyekModel $proyekModel;
    protected KavlingModel $kavlingModel;
    protected FileHargajualModel $fileHargajualModel;

    public function __construct(?BaseConnection $db = null)
    {
        $this->db                 = $db ?? \Config\Database::connect();
        $this->proyekModel        = new ProyekModel();
        $this->kavlingModel       = new KavlingModel();
        $this->fileHargajualModel = new FileHargajualModel();
    }

    public function getHargaJualById(int $id): ?object
    {
        return $this->db->table('hargajual b')
            ->select([
                'b.id',
                'b.id AS harga_akhir',
                'b.tgl_harga',
                'b.row',
                'b.hargajual',
                'b.hargajual_net',
                'b.kpr',
                'b.uang_muka',
                'b.bphtb',
                'b.ppn',
                'b.biaya_proses',
                'b.biaya_adm',
                'b.lb',
                'b.lt',
            ])
            ->where('b.id', $id)
            ->limit(1)
            ->get()
            ->getRow();
    }

    public function hasModuleAccess(int $userId, string $module = 'Hargajual'): bool
    {
        if ($userId <= 0) {
            return false;
        }

        $akses = $this->db->table('modul_akses')
            ->where('user_id', $userId)
            ->get()
            ->getResult();

        foreach ($akses as $row) {
            if (($row->module ?? '') == $module) {
                return true;
            }
        }

        return false;
    }

    public function getProyekList(): array
    {
        return $this->proyekModel->select('id_proyek, nama_proyek')->findAll();
    }

    public function getDetailById(int $id): ?object
    {
        return $this->db->table('hargajual')
            ->select('
                hargajual.*,
                file_hargajual.lokasi,
                file_hargajual.file_name,
                file_hargajual.default_filename,
                proyek.nama_proyek,
                tipe.tipe_rumah,
                users.username as uadd_by,
                c.username as uedit_by
            ')
            ->join('proyek', 'proyek.id_proyek = hargajual.id_proyek')
            ->join('file_hargajual', 'file_hargajual.id_filehj = hargajual.id_filehj', 'left')
            ->join('tipe', 'hargajual.id_tipe = tipe.id_tipe', 'left')
            ->join('users', 'users.id = hargajual.add_by', 'left')
            ->join('users as c', 'c.id = hargajual.edit_by', 'left')
            ->where('hargajual.id', $id)
            ->get()
            ->getRow(0);
    }

    public function getActiveByProyek(?int $idProyek, string $search = ''): array
    {
        return $this->db->table('hargajual')
            ->select('
                hargajual.*,
                proyek.nama_proyek,
                users.username as uadd_by,
                c.username as uedit_by,
                fhj.lokasi,
                fhj.file_name
            ')
            ->join('proyek', 'proyek.id_proyek = hargajual.id_proyek')
            ->join('users', 'users.id = hargajual.add_by', 'left')
            ->join('users as c', 'c.id = hargajual.edit_by', 'left')
            ->join('file_hargajual fhj', 'hargajual.id_filehj = fhj.id_filehj', 'left')
            ->where('hargajual.id_proyek', $idProyek)
            ->where('hargajual.is_active', 1)
            ->like('hargajual.id_tipe', $search)
            ->orWhere('hargajual.id_proyek', $idProyek)
            ->where('hargajual.is_active', 1)
            ->like('hargajual.tgl_harga', $search)
            ->orWhere('hargajual.id_proyek', $idProyek)
            ->where('hargajual.is_active', 1)
            ->like('hargajual.hargajual', $search)
            ->orderBy('hargajual.tgl_harga', 'DESC')
            ->get()
            ->getResult();
    }

    public function getDataTableRows(array $var, ?int $idProyek): array
    {
        $columns   = ['hargajual.hargajual', 'hargajual.kpr', 'hargajual.id_tipe'];
        $condition = [];

        if (isset($var['is_active']) && $var['is_active'] !== '' && $var['is_active'] !== 'all') {
            $condition['hargajual.is_active'] = $var['is_active'];
        }

        if ($idProyek) {
            $condition['hargajual.id_proyek'] = $idProyek;
        }

        $query = $this->db->table('hargajual')
            ->select('
                hargajual.*,
                proyek.nama_proyek,
                file_hargajual.lokasi,
                file_hargajual.file_name,
                file_hargajual.default_filename,
                users.username as uadd_by,
                c.username as uedit_by
            ')
            ->join('proyek', 'proyek.id_proyek = hargajual.id_proyek')
            ->join('file_hargajual', 'file_hargajual.id_filehj = hargajual.id_filehj', 'left')
            ->join('users', 'users.id = hargajual.add_by', 'left')
            ->join('users as c', 'c.id = hargajual.edit_by', 'left');

        $query = $this->applyDataTableWhere($var, $columns, $condition, $query);

        $start  = isset($var['start']) ? (int) $var['start'] : 0;
        $length = isset($var['length']) ? (int) $var['length'] : 10;

        $query->orderBy('tgl_harga', 'desc');
        if ($length > 0) {
            $query->offset($start)->limit($length);
        }

        return $query->get()->getResult();
    }

    public function getDataTableFilteredCount(array $var, ?int $idProyek): int
    {
        $columns   = ['hargajual.hargajual', 'hargajual.kpr', 'hargajual.id_tipe'];
        $condition = [];

        if (isset($var['is_active']) && $var['is_active'] !== '' && $var['is_active'] !== 'all') {
            $condition['hargajual.is_active'] = $var['is_active'];
        }

        if ($idProyek) {
            $condition['hargajual.id_proyek'] = $idProyek;
        }

        $query = $this->db->table('hargajual')
            ->select('hargajual.*')
            ->join('proyek', 'proyek.id_proyek = hargajual.id_proyek')
            ->join('file_hargajual', 'file_hargajual.id_filehj = hargajual.id_filehj', 'left')
            ->join('users', 'users.id = hargajual.add_by', 'left')
            ->join('users as c', 'c.id = hargajual.edit_by', 'left');

        $query = $this->applyDataTableWhere($var, $columns, $condition, $query);

        return count($query->get()->getResult());
    }

    public function getDataTableTotalCount(?int $idProyek): int
    {
        $condition = [];
        $countTotal = $this->db->table('hargajual')
            ->select('count(hargajual.id) as count')
            ->join('proyek', 'proyek.id_proyek = hargajual.id_proyek')
            ->join('users', 'users.id = hargajual.add_by', 'left')
            ->join('users as c', 'c.id = hargajual.edit_by', 'left')
            ->where($condition);

        $row = $countTotal->get()->getRow();

        return (int) ($row->count ?? 0);
    }

    public function insertFileHargajual(array $data): int
    {
        $this->fileHargajualModel->insert($data);
        return (int) $this->fileHargajualModel->getInsertID();
    }

    public function insertHargaJual(array $data): bool
    {
        return (bool) $this->db->table('hargajual')->insert($data);
    }

    public function updateHargaJual(int $id, array $data): bool
    {
        return (bool) $this->db->table('hargajual')->where('id', $id)->update($data);
    }

    public function findKavlingById(int $idKavling): ?object
    {
        $row = $this->kavlingModel->find($idKavling);
        return $row ? (object) $row : null;
    }

    public function updateKavlingHarga(int $idKavling, array $data): bool
    {
        return (bool) $this->kavlingModel->update($idKavling, $data);
    }

    public function getPricelistRowById(int $id): ?object
    {
        return $this->db->table('hargajual')
            ->select('hargajual, tgl_harga, row')
            ->where('id', $id)
            ->get()
            ->getRow();
    }

    protected function applyDataTableWhere(array $var, array $columns, array $condition, BaseBuilder $query): BaseBuilder
    {
        $searchValue = '';
        if (isset($var['search']) && is_array($var['search'])) {
            $searchValue = (string) ($var['search']['value'] ?? '');
        } elseif (isset($var['search']) && is_object($var['search'])) {
            $searchValue = (string) ($var['search']->value ?? '');
        }

        $x = 0;
        foreach ($columns as $col) {
            if ($x === 0) {
                $query->like($col, $searchValue);
            } else {
                $query->orLike($col, $searchValue);
            }
            if (!empty($condition)) {
                $query->where($condition);
            }
            $x++;
        }

        return $query;
    }
}
