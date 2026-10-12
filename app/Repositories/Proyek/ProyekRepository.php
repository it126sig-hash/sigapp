<?php

namespace App\Repositories\Proyek;

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
            ->select('id_proyek, nama_proyek, alamat_proyek, kelurahan, kecamatan, kota, provinsi, siteplan, logo, landing_page_url')
            ->orderBy('order_by');

        if (!empty($params['search'])) {
            $builder->like('nama_proyek', $params['search']);
        }

        return $builder->get()->getResult();
    }

    public function getById(int $idProyek): ?object
    {
        return $this->db->table('proyek')
            ->select('id_proyek, nama_proyek, alamat_proyek, kelurahan, kecamatan, kota, provinsi, siteplan, logo, landing_page_url, id_users')
            ->where('id_proyek', $idProyek)
            ->get()
            ->getRow();
    }

    public function getAccessibleForUser(int $userId, bool $isAdmin): array
    {
        $rows = $this->db->table('proyek')
            ->select('id_proyek, nama_proyek, alamat_proyek, logo, landing_page_url, id_users')
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

    public function getAksesProyekList(): array
    {
        $sql = "
            SELECT 
                proyek.id_proyek,
                proyek.nama_proyek,
                proyek.id_users,
                GROUP_CONCAT(users.username SEPARATOR ',') AS kumpulan_username
            FROM proyek
            LEFT JOIN users
            ON FIND_IN_SET(users.id, proyek.id_users)
            GROUP BY proyek.id_proyek, proyek.nama_proyek, proyek.id_users
        ";

        return $this->db->query($sql)->getResult();
    }

    public function getAksesProyekById(int $idProyek): ?object
    {
        $sql = "
            SELECT 
                proyek.id_proyek,
                proyek.nama_proyek,
                proyek.id_users,
                GROUP_CONCAT(users.username SEPARATOR ',') AS kumpulan_username
            FROM proyek
            LEFT JOIN users
            ON FIND_IN_SET(users.id, proyek.id_users)
            WHERE id_proyek = ?
            GROUP BY proyek.id_proyek, proyek.nama_proyek, proyek.id_users
        ";

        $rows = $this->db->query($sql, [$idProyek])->getResult();

        return !empty($rows) ? $rows[0] : null;
    }

    public function searchUsers(string $keyword): array
    {
        return $this->db->table('users')
            ->select('id, username')
            ->like('username', $keyword)
            ->get()
            ->getResult();
    }

    public function updateUserAccess(int $idProyek, string $userIdsCsv): bool
    {
        return $this->db->table('proyek')
            ->where('id_proyek', $idProyek)
            ->update(['id_users' => $userIdsCsv]);
    }
}
