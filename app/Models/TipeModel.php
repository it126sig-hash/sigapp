<?php
// ADEL CODEIGNITER 4 CRUD GENERATOR

namespace App\Models;
use CodeIgniter\Model;

class TipeModel extends Model {
    
	protected $table = 'tipe';
	protected $primaryKey = 'id_tipe';
	protected $returnType = 'object';
	protected $useSoftDeletes = false;
	protected $allowedFields = ['id_proyek', 'tipe_rumah','no_tipe_rumah', 'id_gambar_kerja','lb', 'lt', 'harga', 'keterangan', 'is_subsidi'];
	protected $useTimestamps = false;
	protected $createdField  = 'created_at';
	protected $updatedField  = 'updated_at';
	protected $deletedField  = 'deleted_at';
	protected $validationRules    = [];
	protected $validationMessages = [];
	protected $skipValidation     = true;    
	
}