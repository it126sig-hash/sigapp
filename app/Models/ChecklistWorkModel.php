<?php
// ADEL CODEIGNITER 4 CRUD GENERATOR

namespace App\Models;
use CodeIgniter\Model;

class ChecklistWorkModel extends Model {
    
	protected $table = 'checklist_work';
	protected $returnType = 'object';
	protected $useSoftDeletes = false;
	protected $allowedFields = ['id_kavling', 'id_subitem', 'hasil_cek_t', 'hasil_cek_f', 'hasil_cek_v', 'produksi_cek', 'produksi_cek_tgl', 'keterangan_cek_produksi', 'hasil_cek_t_s', 'hasil_cek_f_s', 'hasil_cek_v_s', 'sales_cek', 'sales_cek_tgl', 'keterangan_cek_sales', 'is_done', 'add_by', 'created_at', 'edit_by', 'updated_at'];
	protected $useTimestamps = false;
	protected $createdField  = 'created_at';
	protected $updatedField  = 'updated_at';
	protected $deletedField  = 'deleted_at';
	protected $validationRules    = [];
	protected $validationMessages = [];
	protected $skipValidation     = true;    
	
}