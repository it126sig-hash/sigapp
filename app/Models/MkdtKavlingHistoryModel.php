<?php

namespace App\Models;

use CodeIgniter\Model;

class MkdtKavlingHistoryModel extends Model
{
    protected $table          = 'mkdt_kavling_history';
    protected $primaryKey     = 'id';
    protected $returnType     = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields  = [
        'id_mkdt',
        'id_konsumen',
        'id_proyek',
        'action_type',
        'from_id_kavling',
        'to_id_kavling',
        'from_kavling_label',
        'to_kavling_label',
        'status_sebelum',
        'status_sesudah',
        'nominal_booking_snapshot',
        'nominal_sudah_bayar_snapshot',
        'catatan',
        'file_lampiran',
        'created_by',
        'created_at',
    ];
    protected $useTimestamps = false;
}
