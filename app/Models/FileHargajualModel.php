<?php
// ADEL CODEIGNITER 4 CRUD GENERATOR

namespace App\Models;
use CodeIgniter\Model;

class FileHargajualModel extends Model {
    
	protected $table = 'file_hargajual';
	protected $primaryKey = 'id_filehj';
	protected $returnType = 'object';
	protected $useSoftDeletes = false;
	protected $allowedFields = ['lokasi', 'default_filename', 'file_name','upload_at', 'upload_by'];
	protected $useTimestamps = false;
	protected $createdField  = 'created_at';
	protected $updatedField  = 'updated_at';
	protected $deletedField  = 'deleted_at';
	protected $validationRules    = [];
	protected $validationMessages = [];
	protected $skipValidation     = true;    
	
}