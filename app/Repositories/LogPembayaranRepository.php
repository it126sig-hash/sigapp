<?php

namespace App\Repositories;

use App\Exceptions\DataNotFoundException;
use CodeIgniter\Model;

class LogPembayaranRepository extends Model
{
    protected $table = 'log_pembayaran';
    protected $primaryKey = 'id_pembayaran';
    protected $returnType = 'object';
    protected $useSoftDeletes = false;
    protected $allowedFields = ['id_mkdt', 'id_keuangan', 'nominal', 'tanggal_bayar', 'payment_type', 'keterangan', 'st', 'is_deleted', 'deleted_at', 'deleted_by', 'add_by', 'edit_by'];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';
    protected $validationRules    = [];
    protected $validationMessages = [];
    protected $skipValidation     = true;

    public function getRiwayatBayarById(int $idMkdt): array
    {
        return $this->select([
            'log_pembayaran.*',
            'users.username',
            'keuangan.status',
        ])
            ->join('users', 'users.id = log_pembayaran.add_by')
            ->join('keuangan', 'keuangan.id_keuangan = log_pembayaran.id_keuangan', 'left')
            ->where('log_pembayaran.id_mkdt', $idMkdt)
            ->where('log_pembayaran.is_deleted', 0)
            ->orderBy('log_pembayaran.tanggal_bayar', 'ASC')
            ->findAll();
    }
    public function getRiwayatBayarQuery($id_proyek, $id_cluster, $id_jalan)
    {
        $q =  $this->select([
            'log_pembayaran.*',
            'users.username',
            'keuangan.status',
        ])
            ->join('users', 'users.id = log_pembayaran.add_by')
            ->join('keuangan', 'keuangan.id_keuangan = log_pembayaran.id_keuangan', 'left')
            ->join('mkdt', 'mkdt.id_mkdt = log_pembayaran.id_mkdt')
            ->join('proyek', 'proyek.id_proyek = mkdt.id_proyek')
            ->join('cluster', 'cluster.id_cluster = mkdt.id_cluster')
            ->join('jalan', 'jalan.id_jalan = mkdt.id_jalan')
            ->where('log_pembayaran.is_deleted', 0);


        $q->orderBy('log_pembayaran.tanggal_bayar', 'ASC');

        return $q;
    }

    public function softDeleteAndReturnIdMkdt($idPembayaran)
    {
        $row = $this->db->table('log_pembayaran')
            ->select('id_mkdt')
            ->where('id_pembayaran', $idPembayaran)
            ->where('is_deleted', 0)
            ->get()
            ->getRow();

        if (!$row) {
            throw new DataNotFoundException('Log pembayaran tidak ditemukan');
        }

        $this->db->table('log_pembayaran')
            ->where('id_pembayaran', $idPembayaran)
            ->update([
                'is_deleted' => 1,
                'st'         => 'void',
                'deleted_at' => date('Y-m-d H:i:s'),
                'deleted_by' => user_id()
            ]);

        return (int) $row->id_mkdt;
    }
    function getRiwayatBayarByIdPembayran($id)
    {
        return $this->select([
            'log_pembayaran.*',
            'users.username',
            'keuangan.status',
        ])
            ->join('users', 'users.id = log_pembayaran.add_by')
            ->join('keuangan', 'keuangan.id_keuangan = log_pembayaran.id_keuangan', 'left')
            ->where('log_pembayaran.id_pembayaran', $id)
            ->orderBy('log_pembayaran.tanggal_bayar', 'ASC')
            ->first();
    }
    function isBookingPaid($id_mkdt)
    {
        return $this->select('id_keuangan')
            ->where('id_mkdt', $id_mkdt)
            ->where('payment_type', 'Booking')
            ->first();
    }
    public function getDetailRiwayatBayarById(int $id_Pembayaran): array
    {
        return $this->db->table('log_pembayaran_detail')
            ->select([
                'log_pembayaran_detail.*',
                'kl.item',
                'kl.kategori',
            ])
            ->join('keuangan_item_list kl', 'kl.id_keuangan_item_list = log_pembayaran_detail.id_keuangan_item_list')
            ->where('log_pembayaran_detail.id_pembayaran', $id_Pembayaran)
            ->get()
            ->getResultArray();
    }
    public function getTotalBayarByIdMkdt(int $id_Mkdt): float
    {
        $row = $this->select('COALESCE(SUM(nominal), 0) AS total_bayar')
            ->where('id_mkdt', $id_Mkdt)
            ->where('is_deleted', 0)
            ->where('payment_type !=', 'Booking')
            ->first();

        return (float) ($row->total_bayar ?? 0);
    }
    public function getPaidItemSummaryByIdMkdt(int $id_Mkdt): array
    {
        return $this->db->table('log_pembayaran_detail lpd')
            ->select([
                'lpd.id_keuangan_item_list',
                'kl.item',
                'kl.kategori',
                'COALESCE(SUM(lpd.nominal), 0) AS total_nominal',
            ])
            ->join('keuangan_item_list kl', 'kl.id_keuangan_item_list = lpd.id_keuangan_item_list')
            ->join('log_pembayaran lp', 'lp.id_pembayaran = lpd.id_pembayaran')
            ->where('lp.id_mkdt', $id_Mkdt)
            ->where('lp.is_deleted', 0)
            ->where('lp.payment_type !=', 'Booking')
            ->groupBy(['lpd.id_keuangan_item_list', 'kl.item', 'kl.kategori'])
            ->orderBy('kl.id_keuangan_item_list', 'ASC')
            ->get()
            ->getResultArray();
    }
    public function getDetailRiwayatBayarByIdMkdt(int $id_Mkdt): array
    {
        return $this->db->table('log_pembayaran_detail lpd')
            ->select([
                'lpd.id_pembayaran',
                'lpd.id_keuangan_item_list',
                'lpd.nominal',
                'kl.item',
                'kl.kategori',
            ])
            ->join('keuangan_item_list kl', 'kl.id_keuangan_item_list = lpd.id_keuangan_item_list')
            ->join('log_pembayaran lp', 'lp.id_pembayaran = lpd.id_pembayaran')
            ->where('lp.id_mkdt', $id_Mkdt)
            ->where('lp.is_deleted', 0)
            ->get()
            ->getResultArray();
    }
    public function insertDetail($data)
    {
        $this->db->table("log_pembayaran_detail")->insert($data);
        $insertID = $this->db->insertID();

        return $insertID;
    }
}
