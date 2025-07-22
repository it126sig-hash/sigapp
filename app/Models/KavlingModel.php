<?php
// ADEL CODEIGNITER 4 CRUD GENERATOR

namespace App\Models;

use CodeIgniter\Model;

class KavlingModel extends Model
{

	protected $table = 'kavling';
	protected $primaryKey = 'id_kavling';
	protected $returnType = 'object';
	protected $useSoftDeletes = false;
	protected $allowedFields = [
			'id_jalan',
			'id_tipe',
			'no_kavling',
			'id_legal',
			'id_produksi',
			'id_keuangan',
			'id_mkdt',
			'id_config',
			'harga_akhir',
			'harga_akhir_tgl',
			'harga_akhir_oleh',
			'points',
			'status_kavling',
			'add_by',
			'edit_by',
			'is_checked',
			'is_serah_terima',
			'id_serah_terima',
			'id_komplain',
			'status_komplain',
			'luas_tanah',

			

			'status_tanah',
			'perintah_bangun_oleh',
			'perintah_bangun_file',
			'perintah_bangun_tgl',
			'perintah_bangun'
		];
	protected $useTimestamps = true;
	protected $createdField  = 'created_at';
	protected $updatedField  = 'updated_at';
	protected $deletedField  = 'deleted_at';
	protected $validationRules    = [];
	protected $validationMessages = [];
	protected $skipValidation     = true;
}
