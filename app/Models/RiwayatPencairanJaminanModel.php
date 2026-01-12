<?php

namespace App\Models;

use CodeIgniter\Model;

class RiwayatPencairanJaminanModel extends Model
{
    protected $table         = 'riwayat_pencairan_jaminan';
    protected $primaryKey    = 'id';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'id_kavling','tanggal_pengajuan','tanggal_cair','keterangan','status_cair','surat_path','created_by','updated_by'
    ];

    protected $returnType    = 'array';
}
