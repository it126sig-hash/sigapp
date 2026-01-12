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
		'id_konsumen',
		'no_spptb',
		'nama_konsumen',
		'npwp',
		'nik',
		'alamat_konsumen',
		'hp_konsumen',
		'file_npwp',
		'file_ktp',
		'file_data_diri',
		'email_konsumen',
		'nama_instansi',
		'alamat_instansi',
		'tel_instansi',
		'email_instansi',
		'alamat_surat',
		'pekerjaan',
		'lama_bekerja',
		'bidang_pekerjaan',
		'status_pernikahan',
		'nama_pasangan',
		'nik_pasangan',
		'hp_pasangan',
		'status_pekerjaan_pasangan',
		'instansi_pasangan',
		'sales',
		'id_kavling',
		'status_konsumen',
		'is_akad',
		'status',
		'keterangan',
		'refund',
		'refund_tgl',
		'uniq_id',
		'add_by',
		'created_at',
		'edit_by',
		'updated_at'
	];
	protected $useTimestamps = true;
	protected $createdField  = 'created_at';
	protected $updatedField  = 'updated_at';
	protected $deletedField  = 'deleted_at';
	protected $validationRules    = [];
	protected $validationMessages = [];
	protected $skipValidation     = true;    
	
}