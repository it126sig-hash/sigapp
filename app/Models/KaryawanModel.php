<?php
// ADEL CODEIGNITER 4 CRUD GENERATOR

namespace App\Models;
use CodeIgniter\Model;

class KaryawanModel extends Model {
    
	protected $table = 'karyawan';
	protected $primaryKey = 'nik';
	protected $returnType = 'object';
	protected $useAutoIncrement = false;
	protected $useSoftDeletes = false;
	protected $allowedFields = ['nik','nama_karyawan', 'id_user', 'id_divisi', 'id_level', 'status'];
	protected $useTimestamps = false;
	protected $createdField  = 'created_at';
	protected $updatedField  = 'updated_at';
	protected $deletedField  = 'deleted_at';
	protected $validationRules    = [];
	protected $validationMessages = [];
	protected $skipValidation     = true;    
	
}