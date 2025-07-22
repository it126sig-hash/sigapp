<?php
// ADEL CODEIGNITER 4 CRUD GENERATOR

namespace App\Models;
use CodeIgniter\Model;

class KeuanganModel extends Model {
    
	protected $table = 'keuangan';
	protected $primaryKey = 'id_keuangan';
	protected $returnType = 'object';
	protected $useSoftDeletes = false;
	protected $allowedFields = ['jatuh_tempo_tgl', 'berita_acara', 'nominal', 'keterangan', 'sudah_dibayar','st', 'status', 'add_by', 'edit_by', 'id_mkdt'];
	protected $useTimestamps = true;
	protected $createdField  = 'created_at';
	protected $updatedField  = 'updated_at';
	protected $deletedField  = 'deleted_at';
	protected $validationRules    = [];
	protected $validationMessages = [];
	protected $skipValidation     = true;    
	
}