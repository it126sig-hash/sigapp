<?php

namespace App\Models;

use CodeIgniter\Model;

class SalesModel extends Model
{
    protected $table            = 'sales';
    protected $primaryKey       = 'id';
    protected $returnType       = 'object';
    protected $useSoftDeletes   = true;
    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';
    protected $deletedField     = 'deleted_at';

    protected $allowedFields = [
        'nama',
        'kontak',
        'bank',
        'no_rek',
        'proyek',
        'fee_subsidi',
        'fee_komersil',
        'tanggal_pks',
        'tanggal_exp_pks',
        'lampiran_pks',
        'status',
        'catatan',
        'created_by',
        'updated_by',
    ];

    protected $validationRules = [
        'nama' => 'required|min_length[2]|max_length[100]',
    ];
}
