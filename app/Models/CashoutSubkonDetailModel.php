<?php

namespace App\Models;

use CodeIgniter\Model;

class CashoutSubkonDetailModel extends Model
{
    protected $table            = 'cashout_subkon_detail';
    protected $primaryKey       = 'id_cashout_subkon_detail';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'id_cashout_subkon',
        'berita_acara',
        'persentase',
        'nominal',
        'tanggal_jatuh_tempo',
        'status',
        'spp_no',
        'spp_tgl',
        'spp_add_by',
        'spp_created_at',
        'pengajuan_cair_tgl',
        'pengajuan_cari_add_by',
        'pengajuan_cari_tgl_rencana_cair',
        'pengajuan_cair_created_at',
        'cek_no',
        'cek_tgl',
        'cek_add_by',
        'cek_created_at',
        'is_paid',
        'paid_by',
        'paid_at',
        'keterangan',
    ];
}
