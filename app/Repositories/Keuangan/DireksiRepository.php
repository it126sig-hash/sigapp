<?php

namespace App\Repositories\Keuangan;

use CodeIgniter\Database\BaseConnection;

class DireksiRepository
{
    protected BaseConnection $db;

    public function __construct(?BaseConnection $db = null)
    {
        $this->db = $db ?? db_connect();
    }

    public function getDiskresiByKavling(int $idKavling): array
    {
        $kavling = $this->db->table('kavling')
            ->select('harga_akhir, diskresi_harga, diskresi_memo, diskresi_oleh, diskresi_at, username')
            ->join('users', 'users.id = kavling.diskresi_oleh', 'left')
            ->where('id_kavling', $idKavling)
            ->get()
            ->getRow(0);

        $hargaAkhir = [];
        if ($kavling && ! empty($kavling->harga_akhir)) {
            $hargaAkhir = $this->db->table('hargajual')
                ->select('hargajual.*, tipe_rumah, lokasi, file_name')
                ->join('file_hargajual', 'file_hargajual.id_filehj = hargajual.id_filehj')
                ->join('tipe', 'tipe.id_tipe = hargajual.id_tipe')
                ->where('id', $kavling->harga_akhir)
                ->get()
                ->getResult();
        }

        return [
            'data'        => $kavling,
            'harga_akhir' => $hargaAkhir,
        ];
    }

    public function updateDiskresiKavling(int $idKavling, array $data): bool
    {
        return (bool) $this->db->table('kavling')
            ->where('id_kavling', $idKavling)
            ->update($data);
    }

    public function getPendingTagihan(): array
    {
        return $this->db->table('invoice_log')
            ->select('
                invoice_log.*,
                konsumen.nama_konsumen,
                proyek.nama_proyek,
                jalan.nama_jalan,
                kavling.no_kavling,
                COALESCE(usr.name, usr.username, "-") as pembuat
            ')
            ->join('konsumen', 'konsumen.id_konsumen = invoice_log.id_konsumen', 'left')
            ->join('kavling', 'kavling.id_kavling = invoice_log.id_kavling', 'left')
            ->join('jalan', 'jalan.id_jalan = kavling.id_jalan', 'left')
            ->join('cluster', 'cluster.id_cluster = jalan.id_cluster', 'left')
            ->join('proyek', 'cluster.id_proyek = proyek.id_proyek', 'left')
            ->join('users usr', 'usr.id = invoice_log.add_by', 'left')
            ->where('invoice_log.is_signed_direktur', 0)
            ->where('invoice_log.nomor_surat IS NOT NULL')
            ->orderBy('invoice_log.date_add', 'DESC')
            ->get()
            ->getResult();
    }

    public function getHistoryTagihan(): array
    {
        return $this->db->table('invoice_log')
            ->select('
                invoice_log.*,
                konsumen.nama_konsumen,
                proyek.nama_proyek,
                jalan.nama_jalan,
                kavling.no_kavling,
                COALESCE(usr_add.name, usr_add.username, "-") as pembuat,
                COALESCE(usr_sign.name, usr_sign.username, "-") as diproses_oleh
            ')
            ->join('konsumen', 'konsumen.id_konsumen = invoice_log.id_konsumen', 'left')
            ->join('kavling', 'kavling.id_kavling = invoice_log.id_kavling', 'left')
            ->join('jalan', 'jalan.id_jalan = kavling.id_jalan', 'left')
            ->join('cluster', 'cluster.id_cluster = jalan.id_cluster', 'left')
            ->join('proyek', 'cluster.id_proyek = proyek.id_proyek', 'left')
            ->join('users usr_add', 'usr_add.id = invoice_log.add_by', 'left')
            ->join('users usr_sign', 'usr_sign.id = invoice_log.signed_by', 'left')
            ->whereIn('invoice_log.is_signed_direktur', [1, 2])
            ->where('invoice_log.nomor_surat IS NOT NULL')
            ->orderBy('invoice_log.signed_at', 'DESC')
            ->get()
            ->getResult();
    }

    public function findUserById(int $userId): ?object
    {
        return $this->db->table('users')
            ->where('id', $userId)
            ->get()
            ->getRow();
    }

    public function findInvoiceByNoInv(string $noInv): ?object
    {
        return $this->db->table('invoice_log')
            ->where('no_inv', $noInv)
            ->get()
            ->getRow();
    }

    public function updateInvoiceSignature(string $noInv, array $data): bool
    {
        return (bool) $this->db->table('invoice_log')
            ->where('no_inv', $noInv)
            ->update($data);
    }

    public function insertInvoiceStatusLog(array $data): bool
    {
        return (bool) $this->db->table('invoice_status_log')->insert($data);
    }
}
