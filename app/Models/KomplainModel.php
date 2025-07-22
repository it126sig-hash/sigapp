<?php
// ADEL CODEIGNITER 4 CRUD GENERATOR

namespace App\Models;
use CodeIgniter\Model;

class KomplainModel extends Model {
    
	protected $table = 'komplain';
	protected $primaryKey = 'id_komplain';
	protected $returnType = 'object';
	protected $useSoftDeletes = false;
	protected $allowedFields = ['komplain_oleh', 'keterangan_komplain', 'komplain_tgl', 'ditangani_oleh', 'ditangani_tgl', 'keterangan_ditangani', 'is_selesai_produksi', 'is_selesai_sales', 'selesai_oleh_produksi', 'selesai_tgl_produksi', 'selesai_oleh_sales', 'selesai_tgl_sales', 'selesai_keterangan_produksi', 'selesai_keterangan_sales', 'add_by', 'edit_by', 'upload_komplain_sales', 'upload_komplain_produksi'];
	protected $useTimestamps = true;
	protected $createdField  = 'created_at';
	protected $updatedField  = 'updated_at';
	protected $deletedField  = 'deleted_at';
	protected $validationRules    = [];
	protected $validationMessages = [];
	protected $skipValidation     = true;    
	
}