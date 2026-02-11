<?php

namespace App\Models;

use CodeIgniter\Model;

class CashoutModel extends Model
{
    protected $table            = 'cashout';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false; // Menggunakan is_deleted secara manual sesuai skema SQL
    protected $allowedFields    = [
        'id_kavling',
        'id_item_cashout',
        'nominal',
        'tanggal_bayar',
        'keterangan',
        'is_deleted',
        'deleted_at',
        'deleted_by',
        'add_by',
        'created_at',
        'edit_by',
        'updated_at'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
