<?php

namespace App\Models;

use CodeIgniter\Model;

class FeeSalesTerminModel extends Model
{
    protected $table            = 'fee_sales_termin';
    protected $primaryKey       = 'id';
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';

    protected $allowedFields = [
        'id_fee_sales',
        'termin_ke',
        'persen',
        'nominal',
        'status',
        'nomor_spp',
        'tanggal_pengajuan_spp',
        'tanggal_cair',
        'no_cek',
        'keterangan',
        'lampiran_spp',
        'lampiran_bukti_cair',
        'created_by',
        'updated_by',
    ];
}
