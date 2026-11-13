<?php

namespace App\Repositories\Mkdt;

use CodeIgniter\Database\BaseConnection;

class BatalBookingRepository
{
    protected BaseConnection $db;

    public function __construct(?BaseConnection $db = null)
    {
        $this->db = $db ?? \Config\Database::connect();
    }

    public function getMkdtDetailForBatal(int $idMkdt): ?object
    {
        $row = $this->db->table('mkdt')
            ->select('
                mkdt.*,
                konsumen.no_spptb,
                konsumen.nama_konsumen,
                konsumen.nik as nik_konsumen,
                konsumen.npwp as npwp_konsumen,
                konsumen.email_konsumen,
                konsumen.hp_konsumen,
                konsumen.alamat_konsumen,
                konsumen.status_konsumen,
                konsumen.sales,
                users.username as mkdt_batal_oleh_u
            ')
            ->join('konsumen', 'konsumen.id_konsumen = mkdt.id_konsumen')
            ->join('users', 'users.id = mkdt.mkdt_batal_oleh', 'left')
            ->where('mkdt.id_mkdt', $idMkdt)
            ->get()
            ->getRow();

        return $row ?: null;
    }

    public function getMkdtRawById(int $idMkdt): ?object
    {
        $row = $this->db->table('mkdt')
            ->where('id_mkdt', $idMkdt)
            ->get()
            ->getRow();

        return $row ?: null;
    }

    /**
     * @return array<int, object>
     */
    public function getPembayaranLogsByMkdt(int $idMkdt): array
    {
        return $this->db->table('log_pembayaran')
            ->select('*')
            ->where('id_mkdt', $idMkdt)
            ->get()
            ->getResult();
    }

    public function getProyekIdByKavling(int $idKavling): ?int
    {
        $row = $this->db->table('kavling')
            ->select('cluster.id_proyek')
            ->join('jalan', 'jalan.id_jalan = kavling.id_jalan')
            ->join('cluster', 'cluster.id_cluster = jalan.id_cluster')
            ->where('kavling.id_kavling', $idKavling)
            ->get()
            ->getRow();

        $idProyek = $row ? (int) ($row->id_proyek ?? 0) : 0;

        return $idProyek > 0 ? $idProyek : null;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function updateMkdtBatal(int $idMkdt, array $data): bool
    {
        return (bool) $this->db->table('mkdt')
            ->where('id_mkdt', $idMkdt)
            ->update($data);
    }
}
