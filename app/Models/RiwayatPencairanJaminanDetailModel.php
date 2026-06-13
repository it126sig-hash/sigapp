<?php

namespace App\Models;

use CodeIgniter\Model;

class RiwayatPencairanJaminanDetailModel extends Model
{
    protected $table         = 'riwayat_pencairan_jaminan_detail';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $allowedFields = [
        'id_pengajuan',
        'id_dana_akad',
        'id_list_dajam',
        'nominal_pengajuan',
        'nominal_cair',
        'tanggal_cair',
        'keterangan_cair',
        'status_cair',
        'add_by',
        'edit_by',
    ];
}
