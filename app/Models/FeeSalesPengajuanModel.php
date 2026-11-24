<?php

namespace App\Models;

use CodeIgniter\Model;

class FeeSalesPengajuanModel extends Model
{
    protected $table            = 'fee_sales_pengajuan';
    protected $primaryKey       = 'id';
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';

    protected $allowedFields = [
        'id_proyek',
        'share_token',
        'nomor_pengajuan',
        'tanggal_pengajuan',
        'tanggal_pengajuan_sales',
        'catatan',
        'lampiran_spp',
        'status',
        'ttd_pembuat_by',
        'ttd_pembuat_nama',
        'ttd_pembuat_path',
        'ttd_pembuat_method',
        'ttd_pembuat_at',
        'ttd_mkdt_by',
        'ttd_mkdt_nama',
        'ttd_mkdt_path',
        'ttd_mkdt_method',
        'ttd_mkdt_at',
        'ttd_keuangan_by',
        'ttd_keuangan_nama',
        'ttd_keuangan_path',
        'ttd_keuangan_method',
        'ttd_keuangan_at',
        'voided_by',
        'voided_at',
        'void_reason',
        'rejected_by',
        'rejected_role',
        'rejected_at',
        'reject_reason',
        'suggested_harga_net_ajb',
        'created_by',
        'updated_by',
    ];
}
