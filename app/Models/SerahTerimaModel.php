<?php
// ADEL CODEIGNITER 4 CRUD GENERATOR

namespace App\Models;
use CodeIgniter\Model;

class SerahTerimaModel extends Model {
    
	protected $table = 'serah_terima';
	protected $primaryKey = 'id_serah_terima';
	protected $returnType = 'object';
	protected $useSoftDeletes = false;
	protected $allowedFields = ['serah_terima_oleh', 'serah_terima_ke', 'serah_terima_tgl', 'serah_terima_keterangan', 'add_by', 'edit_by'];
	protected $useTimestamps = true;
	protected $createdField  = 'created_at';
	protected $updatedField  = 'updated_at';
	protected $deletedField  = 'deleted_at';
	protected $validationRules    = [];
	protected $validationMessages = [];
	protected $skipValidation     = true;    
	
}