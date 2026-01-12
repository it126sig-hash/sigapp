<?php
// ADEL CODEIGNITER 4 CRUD GENERATOR

namespace App\Models;

use CodeIgniter\Model;

class MkdtModel extends Model
{

	protected $table = 'mkdt';
	protected $primaryKey = 'id_mkdt';
	protected $returnType = 'object';
	protected $useSoftDeletes = false;
	protected $allowedFields = [
		'id_konsumen',
		'booking_fee',
		'is_booking',
		'is_batal',
		'keterangan_batal',
		'surat_batal',
		'mkdt_batal_oleh',
		'mkdt_batal_tgl',
		'keuangan_batal_oleh',
		'keuangan_batal_tgl',
		'file_surat_batal',
		'status_mkdt',
		'booking_paid',
		'notaris',
		'booking_tgl',
		'tgl_harga',
		'id_hargajual',
		'harga_jual',
		'harga_jual_net',
		'harga_diskon',
		'harga_penambahan',
		'harga_penambahan_tanah',
		'harga_administrasi',
		'harga_uang_muka',
		'harga_ppn',
		'harga_bphtb',
		'harga_biaya_proses',
		'harga_sbum',
		'harga_kpr',
		'harga_kpr_acc',
		'harga_penambahan_um', //harga_turun_kpr
		'keterangan_penambahan_biaya',
		'is_kpr',
		'is_subsidi',
		'is_ganti_nama',
		'is_ganti_kavling',
		'is_ganti_kavling',
		'jenis_subsidi',
		'harga_total',
		'harga_harus_bayar',
		'perintah_bangun',
		'perintah_bangun_tgl',
		'perintah_bangun_oleh',
		'perintah_bangun_file',
		'wawancara_tgl',
		'sp3k_tgl',
		'sp3k_no',
		'sp3k_file',
		'debitur_no',
		'sp3k_tgl_exp',
		'rencana_akad_tgl',
		'akad_tgl',
		'rincian',
		'akad',
		'is_ajb',
		'bast_no',
		'bast_no',
		'bast_file',
		'sp3k',
		'wawancara',
		'keterangan', //mkdt_keterangan
		'id_kavling',
		'is_lunas',
		'add_by',
		'edit_by',
		'uniq_id',
		'keuangan_saved_by',
		'refund_paid',
		'refund',
		'refund_tgl',
		'refund_keterangan',
		'id_cair',
		'bank',
		'id_bank',
		'file_spptb',
		'file_surat_kuasa',
		'dajam_selesai',
		'promo'
	];
	protected $useTimestamps = true;
	protected $createdField  = 'created_at';
	protected $updatedField  = 'updated_at';
	protected $deletedField  = 'deleted_at';
	protected $validationRules    = [];
	protected $validationMessages = [];
	protected $skipValidation     = true;
}
