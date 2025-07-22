<?php
// ADEL CODEIGNITER 4 CRUD GENERATOR

namespace App\Models;
use CodeIgniter\Model;

class LogPembayaranModel extends Model {
    
	protected $table = 'log_pembayaran';
	protected $primaryKey = 'id_pembayaran';
	protected $returnType = 'object';
	protected $useSoftDeletes = false;
	protected $allowedFields = ['id_mkdt','id_keuangan', 'nominal', 'tanggal_bayar', 'payment_type', 'keterangan', 'add_by', 'edit_by'];
	protected $useTimestamps = true;
	protected $createdField  = 'created_at';
	protected $updatedField  = 'updated_at';
	protected $deletedField  = 'deleted_at';
	protected $validationRules    = [];
	protected $validationMessages = [];
	protected $skipValidation     = true;    
	
}