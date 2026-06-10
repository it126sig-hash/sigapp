<?php
// ADEL CODEIGNITER 4 CRUD GENERATOR

namespace App\Models;
use CodeIgniter\Model;

class TipeModel extends Model {
    
	protected $table = 'tipe';
	protected $primaryKey = 'id_tipe';
	protected $returnType = 'object';
	protected $useSoftDeletes = false;
	protected $allowedFields = [
		'id_proyek',
		'tipe_rumah',
		'no_tipe_rumah',
		'id_gambar_kerja',
		'lb',
		'lt',
		'jumlah_kamar_tidur',
		'jumlah_kamar_mandi',
		'spesifikasi_teknis_atap',
		'spesifikasi_teknis_dinding',
		'spesifikasi_teknis_lantai',
		'spesifikasi_teknis_pondasi',
		'harga',
		'keterangan',
		'is_subsidi',
		'id_gambar_denah',
		'id_gambar_tipe',
	];
	protected $useTimestamps = false;
	protected $createdField  = 'created_at';
	protected $updatedField  = 'updated_at';
	protected $deletedField  = 'deleted_at';
	protected $validationRules    = [];
	protected $validationMessages = [];
	protected $skipValidation     = true;    
	
}
