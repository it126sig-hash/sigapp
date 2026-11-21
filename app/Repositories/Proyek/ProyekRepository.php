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

    public function getAksesProyekPaginated(string $search, int $page, int $perPage): array
    {
        $page    = max(1, $page);
        $perPage = max(1, min(100, $perPage));
        $offset  = ($page - 1) * $perPage;
        $search  = trim($search);

        $whereSql = '';
        $bindings = [];

        if ($search !== '') {
            $like     = '%' . $this->db->escapeLikeString($search) . '%';
            $whereSql = "
                WHERE (
                    proyek.nama_proyek LIKE ? ESCAPE '!'
                    OR EXISTS (
                        SELECT 1
                        FROM users u2
                        LEFT JOIN karyawan k2 ON k2.id_user = CAST(u2.id AS CHAR)
                        WHERE FIND_IN_SET(u2.id, proyek.id_users) > 0
                          AND u2.active = 1
                          AND u2.deleted_at IS NULL
                          AND (
                              u2.username LIKE ? ESCAPE '!'
                              OR u2.name LIKE ? ESCAPE '!'
                              OR k2.nama_karyawan LIKE ? ESCAPE '!'
                          )
                    )
                )
            ";
            $bindings = [$like, $like, $like, $like];
        }

        $countRow = $this->db->query(
            "SELECT COUNT(*) AS total FROM proyek {$whereSql}",
            $bindings
        )->getRow();

        $total = (int) ($countRow->total ?? 0);

        $dataSql = "
            SELECT
                proyek.id_proyek,
                proyek.nama_proyek,
                proyek.logo,
                proyek.id_users,
                GROUP_CONCAT(users.username ORDER BY users.username ASC SEPARATOR ',') AS kumpulan_username
            FROM proyek
            LEFT JOIN users
                ON FIND_IN_SET(users.id, proyek.id_users) > 0
               AND users.active = 1
               AND users.deleted_at IS NULL
            {$whereSql}
            GROUP BY proyek.id_proyek, proyek.nama_proyek, proyek.logo, proyek.id_users, proyek.order_by
            ORDER BY proyek.order_by ASC, proyek.id_proyek ASC
            LIMIT ? OFFSET ?
        ";

        $rows = $this->db->query($dataSql, array_merge($bindings, [$perPage, $offset]))->getResult();

        return [
            'rows'  => $rows,
            'total' => $total,
        ];
    }

    public function getActiveUsersWithGroups(string $search = ''): array
    {
        $builder = $this->db->table('users u')
            ->select("u.id, u.username, COALESCE(NULLIF(MAX(k.nama_karyawan), ''), NULLIF(u.name, ''), u.username) AS nama_karyawan, MAX(agu.group_id) AS id_group, MAX(ag.name) AS divisi", false)
            ->join('karyawan k', 'k.id_user = CAST(u.id AS CHAR)', 'left', false)
            ->join('auth_groups_users agu', 'agu.user_id = u.id', 'left')
            ->join('auth_groups ag', 'ag.id = agu.group_id', 'left')
            ->where('u.active', 1)
            ->where('u.deleted_at', null)
            ->groupBy('u.id, u.username, u.name');

        $search = trim($search);
        if ($search !== '') {
            $builder->groupStart()
                ->like('u.username', $search)
                ->orLike('u.name', $search)
                ->orLike('k.nama_karyawan', $search)
                ->orLike('ag.name', $search)
                ->groupEnd();
        }

        return $builder
            ->orderBy('nama_karyawan', 'ASC')
            ->orderBy('u.username', 'ASC')
            ->get()
            ->getResult();
    }

    public function getAuthGroups(): array
    {
        return $this->db->table('auth_groups')
            ->select('id, name, description')
            ->orderBy('id', 'ASC')
            ->get()
            ->getResult();
    }

    public function existsProyek(int $idProyek): bool
    {
        if ($idProyek <= 0) {
            return false;
        }

        return $this->db->table('proyek')
            ->where('id_proyek', $idProyek)
            ->countAllResults() > 0;
    }

    public function findActiveUserIds(array $userIds): array
    {
        $userIds = array_values(array_unique(array_filter(
            array_map('intval', $userIds),
            static fn (int $id): bool => $id > 0
        )));

        if ($userIds === []) {
            return [];
        }

        $rows = $this->db->table('users')
            ->select('id')
            ->whereIn('id', $userIds)
            ->where('active', 1)
            ->where('deleted_at', null)
            ->get()
            ->getResult();

        return array_map(static fn ($row): int => (int) $row->id, $rows);
    }
}
