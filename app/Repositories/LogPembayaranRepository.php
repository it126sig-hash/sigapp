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
    public function getRiwayatBayarQuery($id_proyek, $id_cluster = null, $id_jalan = null)
    {
        $q = $this->db->table('log_pembayaran lp')
            ->select('
                lp.id_pembayaran,
                lp.id_mkdt,
                lp.nominal,
                lp.tanggal_bayar,
                lp.payment_type,
                lp.keterangan,
                lp.created_at,
                COALESCE(u.username, "-") AS username,
                COALESCE(c.nama_konsumen, "-") AS nama_konsumen,
                COALESCE(k.no_kavling, "-") AS no_kavling,
                COALESCE(k.id_kavling, 0) AS id_kavling,
                COALESCE(j.nama_jalan, "-") AS nama_jalan,
                cl.id_proyek,
                (
                    SELECT GROUP_CONCAT(CONCAT(kil.item, ": Rp ", FORMAT(lpd.nominal, 0)) SEPARATOR ", ")
                    FROM log_pembayaran_detail lpd
                    JOIN keuangan_item_list kil ON kil.id_keuangan_item_list = lpd.id_keuangan_item_list
                    WHERE lpd.id_pembayaran = lp.id_pembayaran
                ) AS detail_items
            ')
            ->join('mkdt m', 'm.id_mkdt = lp.id_mkdt', 'left')
            ->join('konsumen c', 'c.id_konsumen = m.id_konsumen', 'left')
            ->join('kavling k', 'k.id_mkdt = m.id_mkdt', 'left')
            ->join('jalan j', 'j.id_jalan = k.id_jalan', 'left')
            ->join('cluster cl', 'cl.id_cluster = j.id_cluster', 'left')
            ->join('users u', 'u.id = lp.add_by', 'left')
            ->where('lp.is_deleted', 0);

        if (!empty($id_proyek)) {
            $q->where('cl.id_proyek', $id_proyek);
        }
        if (!empty($id_cluster)) {
            $q->where('cl.id_cluster', $id_cluster);
        }
        if (!empty($id_jalan)) {
            $q->where('j.id_jalan', $id_jalan);
        }

        $q->orderBy('lp.tanggal_bayar', 'DESC');
        $q->orderBy('lp.id_pembayaran', 'DESC');

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
    public function getDetailRiwayatBayarById(int $id_Pembayaran): array
    {
        return $this->db->table('log_pembayaran_detail')
            ->select([
                'log_pembayaran_detail.*',
                'kl.item',
                'kl.kategori',
                'log_pembayaran_detail.booking_is_installment',
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
                'lpd.booking_is_installment',
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

    public function hasRecentDuplicate(
        int $idMkdt,
        string $idKeuangan,
        $nominal,
        string $tanggalBayar,
        string $paymentType,
        int $seconds = 30
    ): bool {
        return (bool) $this->where('id_mkdt', $idMkdt)
            ->where('id_keuangan', $idKeuangan)
            ->where('nominal', $nominal)
            ->where('tanggal_bayar', $tanggalBayar)
            ->where('payment_type', $paymentType)
            ->where('is_deleted', 0)
            ->where('created_at >=', date('Y-m-d H:i:s', time() - $seconds))
            ->first();
    }
}
