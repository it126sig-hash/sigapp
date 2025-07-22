<?php
// ADEL CODEIGNITER 4 CRUD GENERATOR

namespace App\Models;
use CodeIgniter\Model;

class ProduksiModel extends Model
{

	protected $table = 'produksi';
	protected $primaryKey = 'id_produksi';
	protected $returnType = 'object';
	protected $useSoftDeletes = false;
	protected $allowedFields = [
		'progres_bangunan',
		'st_0',
		'st_25',
		'st_50',
		'st_75',
		'st_100',
		'slo',
		'bp',
		'lpa',
		'lpa_tanggal',
		'st_jalan',
		'st_saluran',
		'st_air',
		'slf_no',
		'slf_tanggal',
		'surat_pernyataan_no',
		'surat_pernyataan_npwp',
		'surat_pernyataan_nama',
		'surat_pernyataan_tanggal',
		'listrik_pln',
		'listrik_disediakan_no',
		'listrik_disediakan_tanggal',
		'air_deskripsi_unit',
		'air_pdam_no',
		'keterangan',

		'tanggal_pembangunan',
		'tanggal_rencana_selesai_pembangunan',
		'tanggal_selesai_pembangunan',
		'tanggal_pembangunan_oleh',
		'tanggal_pembangunan_pada',
		'tanggal_pembangunan_diubah_oleh',
		'tanggal_pembangunan_diubah_pada',
		'tanggal_selesai_pembangunan_oleh',
		'tanggal_selesai_pembangunan_pada',
		'tanggal_selesai_pembangunan_diubah_oleh',
		'tanggal_selesai_pembangunan_diubah_pada',

		'air_jenis',
		'listrik_jenis',
		'add_by',
		'edit_by'
	];
	protected $useTimestamps = true;
	protected $createdField = 'created_at';
	protected $updatedField = 'updated_at';
	protected $deletedField = 'deleted_at';
	protected $validationRules = [];
	protected $validationMessages = [];
	protected $skipValidation = true;

}