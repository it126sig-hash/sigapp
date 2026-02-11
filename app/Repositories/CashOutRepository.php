<?php

namespace App\Repositories;

use CodeIgniter\Model;

class CashOutRepository extends Model
{
    protected $table = 'cashout';
    protected $primaryKey = 'id';
    protected $returnType = 'object';
    protected $useTimestamps = true;
    protected $useSoftDeletes = true;
    protected $allowedFields = [
        'id_item_cashout',
        'id_kavling',
        'nominal',
        'tanggal_bayar',
        'keterangan',
        'add_by',
        'edit_by',
    ];
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    public function getListItem(string $search)
    {
        return $this->db->table('list_cashout lc')->where('lc.item LIKE', '%' . $search . '%')->get()->getResult();
    }
    public function getRiwayatBayarCashOutByIDKavling(int $id_kavling)
    {
        return $this->select(
            'cashout.*,
                lc.item,
                u.username as uadd_by,
                e.username as uedit_by'
        )
            ->join('list_cashout lc', 'lc.id = cashout.id_item_cashout')
            ->join('users u', 'u.id = cashout.add_by', 'left')
            ->join('users e', 'e.id = cashout.edit_by', 'left')
            ->where('cashout.is_deleted', 0)
            ->where('cashout.id_kavling', $id_kavling)
            ->orderBy('cashout.tanggal_bayar', 'desc')
            ->get()
            ->getResult();
    }
    public function softDelete($id)
    {
        return $this->db->table('cashout')
            ->where('id', $id)
            ->update(['is_deleted' => 1, 'deleted_at' => date('Y-m-d H:i:s')]);
    }
}
