<?php

namespace App\Models;

use CodeIgniter\Model;

class TiketMasalahFotoModel extends Model
{
    protected $table            = 'tiket_masalah_foto';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['id_tiket_masalah', 'file_path', 'file_name', 'uploaded_by'];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = '';
}
