<?php

namespace App\Repositories;

class ProyekRepository
{
    protected $db;

    public function __construct()
    {
        $this->db = db_connect();
    }

    public function getAll(array $params = []): array
    {
        $builder = $this->db->table('proyek')
            ->select('id_proyek, nama_proyek, alamat_proyek, kelurahan, kecamatan, kota, provinsi, siteplan, logo')
            ->orderBy('order_by');

        if (!empty($params['search'])) {
            $builder->like('nama_proyek', $params['search']);
        }

        return $builder->get()->getResult();
    }

    public function getById(int $idProyek): ?object
    {
        return $this->db->table('proyek')
            ->select('id_proyek, nama_proyek, alamat_proyek, kelurahan, kecamatan, kota, provinsi, siteplan, logo, id_users')
            ->where('id_proyek', $idProyek)
            ->get()
            ->getRow();
    }

    public function getAccessibleForUser(int $userId, bool $isAdmin): array
    {
        $rows = $this->db->table('proyek')
            ->select('id_proyek, nama_proyek, alamat_proyek, logo, id_users')
            ->orderBy('order_by', 'asc')
            ->get()
            ->getResult();

        if ($isAdmin) {
            return $rows;
        }

        $userIdStr = (string) (int) $userId;

        return array_values(array_filter($rows, static function ($row) use ($userIdStr) {
            if (! isset($row->id_users) || $row->id_users === null || trim((string) $row->id_users) === '') {
                return false;
            }

            $allowedUserIds = array_filter(array_map('trim', explode(',', (string) $row->id_users)));

            return in_array($userIdStr, $allowedUserIds, true);
        }));
    }

    public function getSiteplanUploads(int $idProyek): array
    {
        return $this->db->table('siteplan_upload')
            ->select('siteplan_upload.*, u.username as uadd_by')
            ->join('users as u', 'u.id = siteplan_upload.upload_by', 'left')
            ->where('id_proyek', $idProyek)
            ->orderBy('upload_at', 'desc')
            ->get()
            ->getResult();
    }
}
