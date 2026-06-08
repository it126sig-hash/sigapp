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
