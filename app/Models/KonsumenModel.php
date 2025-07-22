<?php
// ADEL CODEIGNITER 4 CRUD GENERATOR

namespace App\Models;
use CodeIgniter\Model;

class KonsumenModel extends Model {
    
	protected $table = 'konsumen';
	protected $primaryKey = 'id_konsumen';
	protected $returnType = 'object';
	protected $useSoftDeletes = false;
	protected $allowedFields = [
		'no_spptb',
		'nama_konsumen',
		'nik',
		'file_ktp',
		'file_npwp',
		'file_data_diri',
		'npwp',
		'alamat_konsumen',
		'hp_konsumen',
		'id_kavling',
		'email_konsumen',
		'status_konsumen',
		'status_pernikahan',
		'nama_pasangan',
		'nik_pasangan',
		'nama_instansi',
		'alamat_instansi',
		'tel_instansi',
		'sales',
		'is_akad',
		'keterangan',
		'uniq_id',
		'refund',
		'refund_tgl',
		'add_by',
		'edit_by'
	];
	protected $useTimestamps = true;
	protected $createdField  = 'created_at';
	protected $updatedField  = 'updated_at';
	protected $deletedField  = 'deleted_at';
	protected $validationRules    = [];
	protected $validationMessages = [];
	protected $skipValidation     = true;    
	
}