<?php

namespace App\Models;

use CodeIgniter\Model;

class BankKprDisbursementModel extends Model
{
    protected $table = 'bank_kpr_disbursement';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $allowedFields = [
        'id_mkdt',
        'id_kavling',
        'id_bank',
        'nominal_plafon',
        'nominal_cair',
        'nominal_retensi',
        'tanggal_cair',
        'rekening_tujuan',
        'no_referensi',
        'file_bukti',
        'keterangan',
        'status',
        'void_reason',
        'add_by',
        'edit_by',
        'deleted_by',
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';
}
