<?php
// ADEL CODEIGNITER 4 CRUD GENERATOR

namespace App\Models;
use CodeIgniter\Model;

class GambarkerjaModel extends Model {
    
	protected $table = 'gambar_kerja';
	protected $primaryKey = 'id_gambar_kerja';
	protected $returnType = 'object';
	protected $useSoftDeletes = false;
	protected $allowedFields = ['lokasi', 'default_filename', 'keterangan', 'upload_at', 'upload_by', 'id_tipe', 'tipe'];
	protected $useTimestamps = false;
	protected $createdField  = 'created_at';
	protected $updatedField  = 'updated_at';
	protected $deletedField  = 'deleted_at';
	protected $validationRules    = [];
	protected $validationMessages = [];
	protected $skipValidation     = true;    
	
}