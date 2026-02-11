<?php

namespace App\Models;

use CodeIgniter\Model;

class CashoutSubkonPaymentModel extends Model
{
    protected $table            = 'cashout_subkon_payment';
    protected $primaryKey       = 'id_cashout_subkon_payment';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'id_cashout_subkon',
        'nominal',
        'tanggal_bayar',
        'keterangan',
        'created_at',
        'add_by',
        'is_deleted',
        'deleted_at',
        'deleted_by'
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = ''; // Tidak ada updated_at di tabel ini
}
