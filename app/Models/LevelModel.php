<?php
// ADEL CODEIGNITER 4 CRUD GENERATOR

namespace App\Models;
use CodeIgniter\Model;

class LevelModel extends Model {
    
	protected $table = 'level';
	protected $primaryKey = 'id_level';
	protected $returnType = 'object';
	protected $useSoftDeletes = false;
	protected $allowedFields = ['level'];
	protected $useTimestamps = false;
	protected $createdField  = 'created_at';
	protected $updatedField  = 'updated_at';
	protected $deletedField  = 'deleted_at';
	protected $validationRules    = [];
	protected $validationMessages = [];
	protected $skipValidation     = true;    
	
}