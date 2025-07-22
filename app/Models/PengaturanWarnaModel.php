<?php
// ADEL CODEIGNITER 4 CRUD GENERATOR

namespace App\Models;
use CodeIgniter\Model;

class PengaturanWarnaModel extends Model {
    
	protected $table = 'config_shape';
	protected $primaryKey = 'config_name';
	protected $returnType = 'object';
	protected $useSoftDeletes = false;
	protected $allowedFields = ['fill', 'dashed', 'keterangan', 'add_by', 'date_add', 'edit_by', 'date_edit'];
	protected $useTimestamps = false;
	protected $createdField  = 'created_at';
	protected $updatedField  = 'updated_at';
	protected $deletedField  = 'deleted_at';
	protected $validationRules    = [];
	protected $validationMessages = [];
	protected $skipValidation     = true;    
	
}