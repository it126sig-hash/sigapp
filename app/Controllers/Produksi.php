<?php

namespace App\Controllers;

use App\Models\ProduksiModel;
use App\Models\GambarkerjaModel;
use App\Models\ChecklistSubItemModel;
use CodeIgniter\HTTP\Response;
use App\Models\KavlingModel;
use App\Models\ChecklistWorkModel;
use App\Models\KomplainModel;
use App\Controllers\Notif;
use App\Libraries\Pdf;
use App\Libraries\Mpdf_lib;
use App\Controllers\Home;

use function PHPUnit\Framework\returnSelf;

class Produksi extends BaseController
{
	protected $db;
	protected $produksiModel;
	protected $gambarkerjaModel;
	protected $kavlingModel;
	protected $siModel;
	protected $cwModel;
	protected $validation;
	protected $komplainModel;
	protected $notif;
	protected $mpdf;

	protected $hak_akses;

	public function __construct()
	{
		$this->notif = new Notif();
		$this->produksiModel = new ProduksiModel();
		$this->gambarkerjaModel = new GambarkerjaModel();
		$this->kavlingModel = new KavlingModel();
		$this->siModel = new ChecklistSubItemModel();
		$this->cwModel = new ChecklistWorkModel();
		$this->validation = \Config\Services::validation();
		$this->komplainModel = new KomplainModel();
		$this->db = db_connect();
		$this->mpdf = new Mpdf_lib();
		$this->hak_akses = new Home();
	}
	function get_data_by_id()
	{
		$files = [];
		$id_kavling = $this->request->getVar('id_kavling');
		if ($id_kavling) {
			$files = $this->db->table('file_produksi')
				->select('file_produksi.*, username')
				->join('users', 'file_produksi.upload_by = users.id')
				->where('id_kavling', $id_kavling)
				->get()->getResult();
		}
		if ($this->request->getVar('id_produksi')) {
			$r = $this->produksiModel
				->select('
                    produksi.*, 
                    a.username as tanggal_pembangunan_oleh_u,
                    b.username as tanggal_pembangunan_diubah_oleh_u,
                    c.username as tanggal_selesai_pembangunan_diubah_oleh_u,
					sumurbor,
					sumurbor_tanggal,
					sumurbor_keterangan,
					d.username as sumurbor_oleh_u
                    ')
				->where('produksi.id_produksi', $this->request->getVar('id_produksi'))
				->join('users as a', 'a.id = produksi.tanggal_pembangunan_oleh', 'left')
				->join('users as b', 'b.id = produksi.tanggal_pembangunan_diubah_oleh', 'left')
				->join('users as c', 'c.id = produksi.tanggal_selesai_pembangunan_diubah_oleh', 'left')
				->join('kavling', 'kavling.id_produksi = produksi.id_produksi', 'left')
				->join('users as d', 'd.id = kavling.sumurbor_oleh', 'left')
				->first();
			$r->token = csrf_hash();

			$r->cl = $this->cwModel
				->select("checklist_work.*, username")
				->where('id_kavling', $this->request->getVar('id_kavling'))
				->join('users', 'checklist_work.produksi_cek = users.id')
				->find();
			$r->files = $files;
		} else {
			$r['token'] = csrf_hash();
			$r['files'] = $files;
		}

		return $this->response->setJSON($r);
	}

	function getBayarProduksi()
	{
		$r['token'] = csrf_hash();

		$id_kavling = $this->request->getVar('id_kavling');

		$r['id_kavling'] = $id_kavling;


		$r['list_bayar_produksi'] = $this->db->table('list_bayar_produksi lc')
			->select('lc.id as id_bayar_produksi, lc.item, lc.sort, c.*, u.username as add_by_u, e.username as edit_by_u')
			->join('bayar_produksi c', 'c.id_item_produksi = lc.id and id_kavling = ' . $this->db->escape($id_kavling), 'left')
			->join('users u', 'u.id = c.add_by', 'left')
			->join('users e', 'e.id = c.edit_by', 'left')
			->get()->getResult();

		return $this->response->setJSON($r);
	}
	function saveBayarProduksi()
	{
		$response['token'] = csrf_hash();
		$id = $this->request->getVar('id-bayar_produksi');
		$id_kavling = $this->request->getVar('id_kavling');

		foreach ($id as $i => $v) {
			$data = [];

			$data['id_kavling'] = $id_kavling;


			$s = false;



			if ($v['nominal'] != "" && $v['tanggal_bayar'] != "") {
				if (strpos($i, 'n') === false) {
					// $data['id_item_cashout'] = $v['id_item_cashout'];
					$data['nominal'] = $this->num($v['nominal']);
					$data['keterangan'] = $v['keterangan'];
					$data['tanggal_bayar'] = $v['tanggal_bayar'];

					$data['edit_by'] = user_id();
					$data['updated_at'] = date("Y-m-d H:i:s");

					// $data['id'] = $i;
					$q = $this->db->table('bayar_produksi')
						->where(['id' => $i])
						->update($data);

					$s = $q;
				} else {
					$data['id_item_produksi'] = substr($v['id_item_produksi'], 1);
					$data['nominal'] = $this->num($v['nominal']);
					$data['keterangan'] = $v['keterangan'];
					$data['tanggal_bayar'] = $v['tanggal_bayar'];

					$data['id'] = null;
					$data['add_by'] = user_id();
					$data['created_at'] = date("Y-m-d H:i:s");

					$q = $this->db->table('bayar_produksi')
						->insert($data);

					$s = $q;
				}
			}
		}

		// if ($s) {
		$response['success'] = true;
		$response['messages'] = 'Data berhasil diperbaharui';
		// } else {
		//     $response['success'] = false;
		//     $response['messages'] = 'Terjadi kesalahan saat melakukan perubahan data';
		// }
		return $this->response->setJSON($response);
	}
	function get_data_komplain_by_id()
	{
		if ($this->request->getVar('id_kavling')) {
			$r['komplain'] = $this->kavlingModel
				->select('
                    status_komplain,
                    kavling.id_kavling, 
                    komplain.*,
                    s.username as username_komplain_oleh,
                    p.username as username_ditangani_oleh,
                    ss.username as username_selesai_oleh_sales,
                    sp.username as username_selesai_oleh_produksi,
                    lu.username as username_last_update
                ')
				->join('komplain', 'komplain.id_komplain = kavling.id_komplain')
				->join('users as s', 's.id = komplain.komplain_oleh', 'left')
				->join('users as p', 'p.id = komplain.ditangani_oleh', 'left')
				->join('users as ss', 'ss.id = komplain.selesai_oleh_sales', 'left')
				->join('users as sp', 'sp.id = komplain.selesai_oleh_produksi', 'left')
				->join('users as lu', 'lu.id = komplain.edit_by', 'left')
				->where('id_kavling', $this->request->getVar('id_kavling'))
				->first();
			$r['token'] = csrf_hash();
		} else {
			$r['token'] = csrf_hash();
		}
		return $this->response->setJSON($r);
	}
	function save_komplain_produksi()
	{
		$response['token'] = csrf_hash();

		$terima_komplain = $this->request->getVar('terima_komplain');
		$is_selesai_produksi = $this->request->getVar('is_selesai_produksi');
		$id_kavling = $this->request->getVar('id_kavling');


		if ($terima_komplain == 1 && $is_selesai_produksi != 1) {

			$f['keterangan_ditangani'] = $this->request->getVar('keterangan_ditangani');

			$f['id_komplain'] = $this->request->getVar('id_komplain');
			$f['edit_by'] = user_id();

			$this->validation->setRules([
				'keterangan_ditangani' => [
					'label' => 'Keterangan',
					'rules' => 'required|max_length[255]',
					'errors' => [
						'required' => 'Keterangan harus diisi'
					]
				]
			]);
			if ($this->validation->run($f) == false) {
				$response['success'] = false;
				$response['messages'] = $this->validation->listErrors();
			} else {
				$f['ditangani_oleh'] = user_id();
				$f['ditangani_tgl'] = date("Y-m-d");

				if ($this->komplainModel->update($f['id_komplain'], $f)) {
					$response['success'] = true;
					$response['messages'] = 'Successfully updated';
				} else {
					$response['success'] = false;
					$response['messages'] = 'Terjadi kesalahan';
				}
				$this->kavlingModel->update(
					$id_kavling,
					[
						"status_komplain" => 2
					]
				);
			}
		} else if ($terima_komplain == 1 && $is_selesai_produksi == 1) {
			$f['keterangan_ditangani'] = $this->request->getVar('keterangan_ditangani');
			$f['selesai_keterangan_produksi'] = $this->request->getVar('selesai_keterangan_produksi');
			$f['is_selesai_produksi'] = 1;

			$f['id_komplain'] = $this->request->getVar('id_komplain');
			$f['edit_by'] = user_id();

			$this->validation->setRules([
				'selesai_keterangan_produksi' => [
					'label' => 'Keterangan',
					'rules' => 'required|max_length[255]',
					'errors' => [
						'required' => 'Keterangan harus diisi'
					]
				],
				'upload_komplain_produksi' => [
					'label' => 'File',
					'rules' => 'uploaded[upload_komplain_produksi]'
						. '|mime_in[upload_komplain_produksi,image/jpg,image/jpeg,image/gif,image/png,image/webp]'
						. '|max_size[upload_komplain_produksi,12000]'
						. '|max_dims[upload_komplain_produksi,6000,6000]',
				],
			]);
			if ($this->validation->run($f) == false) {
				$response['success'] = false;
				$response['messages'] = $this->validation->listErrors();
			} else {

				$f['upload_komplain_produksi'] = "";
				foreach ($this->request->getFileMultiple('upload_komplain_produksi') as $img) {
					$name = $img->getRandomName();

					$lok = 'uploads/komplain_produksi/' . date('Ymd') . '/';

					$f['upload_komplain_produksi'] .= $lok . $name . ";";

					$img->move($lok, $name);
				}


				$f['selesai_oleh_produksi'] = user_id();
				$f['selesai_tgl_produksi'] = date("Y-m-d");

				if ($this->komplainModel->update($f['id_komplain'], $f)) {
					$response['success'] = true;
					$response['messages'] = 'Successfully updated';
				} else {
					$response['success'] = false;
					$response['messages'] = 'Terjadi kesalahan';
				}
				$this->kavlingModel->update(
					$id_kavling,
					[
						"status_komplain" => 3
					]
				);
			}
		} else {
			$response['success'] = true;
			$response['messages'] = "";
		}
		return $this->response->setJSON($response);
	}
	protected function findObjectById($id, $array = array())
	{

		foreach ($array as $element) {
			if ($id == $element->id_subitem) {
				return true;
			}
		}

		return false;
	}
	function cekTrue($item, $st)
	{
		if ($st)
			return "$item = Selesai; ";
		return "";
	}


	function save()
	{
		$response['token'] = csrf_hash();

		/********************* upload foto **************************/

		$data = [];
		$id_kavling = $this->request->getVar('id_kavling');
		$lok = 'uploads/produksi/' . date('Ymd') . '/';
		$thumbLok = 'uploads/produksi/' . date('Ymd') . '/thumbnails/';

		if (!is_dir($thumbLok)) {
			mkdir($thumbLok, 0777, true);
		}

		$categories = [
			'rab_dokumen',
			'prod_foto_konstruksi',
			'prod_foto_exterior',
			'prod_foto_interior',
			"jalan_foto",
			"jalan_foto_update",
			'listrik_pln_foto',
			'listrik_disediakan_dokumen',
			'air_komunal',
			'air_tanah',
			'air_pdam',
		];




		foreach ($categories as $category) {
			// Get the files for the specified category
			if ($category != "rab_dokumen") {

				$imgs = $this->request->getFiles()[$category];
				$tgl = $this->request->getVar('tgl_' . $category);
				$ket = $this->request->getVar('ket_' . $category);
	

				$i = 0;
				foreach ($imgs as $img) {
					if ($img->getSize() > 0) {
						$name = $img->getRandomName();
						$img->move($lok, $name);


						// Generate thumbnail
						$image = \Config\Services::image()
							->withFile($lok . $name)
							->resize(150, 150, true, 'height')
							->save($thumbLok . $name);

						$data[] = [
							'id_kavling' => $id_kavling,
							'lokasi' => $lok,
							'file_name' => $name,
							'tgl_capture' => isset($tgl[$i]) ? $tgl[$i]: null,
							'file_keterangan' => isset($ket[$i]) ? $ket[$i] : null,
							'kategori' => $category,
							'upload_at' => date('Y-m-d H:i:s'),
							'upload_by' => user_id(),
						];
						$i++;
					}
				}
			}
		}


		// Batch insert ke tabel 'file_produksi' jika $data tidak kosong
		if (!empty($data)) {
			$this->db->table('file_produksi')->insertBatch($data);
		}

		$var = $this->request->getVar();
		$cats = [
			'listrik_pln',
			'listrik_disediakan_no',
			'listrik_disediakan_tanggal',
			'air_deskripsi_unit',
			'air_pdam_no'
		];

		foreach ($cats as $cat) {
			$f[$cat] = $var[$cat];
		}

		/********************* end upload foto **************************/




		$f['id_produksi'] = $this->request->getVar('id_produksi');

		$f['st_0'] = $this->request->getVar('st_0');
		$f['st_25'] = $this->request->getVar('st_25');
		$f['st_50'] = $this->request->getVar('st_50');
		$f['st_75'] = $this->request->getVar('st_75');
		$f['st_100'] = $this->request->getVar('st_100');

		$f['bp'] = $this->request->getVar('bp');
		$f['slo'] = $this->request->getVar('slo');
		$f['lpa'] = $this->request->getVar('lpa');
		$f['lpa_tanggal'] = $this->request->getVar('lpa_tanggal');
		$f['st_jalan'] = $this->request->getVar('st_jalan');
		$f['st_saluran'] = $this->request->getVar('st_saluran');
		$f['st_air'] = $this->request->getVar('st_air');


		$f['air_jenis'] = $this->request->getVar('air_jenis');
		$f['listrik_jenis'] = $this->request->getVar('listrik_jenis');

		$f['progres_bangunan'] = $this->request->getVar('progres_bangunan');

		$f['keterangan'] = $this->request->getVar('produksi_keterangan');




		//get data ceklist yang sudah ada di tb cheklist_work
		$get_cw = $this->cwModel->select('id_subitem')->where('id_kavling', $id_kavling)->findAll();

		$tgl_pembangunan = $this->request->getVar('tanggal_pembangunan');
		$tgl_pembangunan_old = $this->request->getVar('tanggal_pembangunan_old');
		$tanggal_rencana_selesai_pembangunan = $this->request->getVar('tanggal_rencana_selesai_pembangunan');
		$tanggal_rencana_selesai_pembangunan_old = $this->request->getVar('tanggal_rencana_selesai_pembangunan_old');
		$tanggal_selesai_pembangunan = $this->request->getVar('tanggal_selesai_pembangunan');
		$tanggal_selesai_pembangunan_old = $this->request->getVar('tanggal_selesai_pembangunan_old');

		if ($tgl_pembangunan && $tgl_pembangunan_old == '' || $tanggal_rencana_selesai_pembangunan && $tanggal_rencana_selesai_pembangunan_old == '') {
			$f['tanggal_pembangunan_oleh'] = user_id();
			$f['tanggal_pembangunan_pada'] = date('Y-m-d H:i:s');

			// echo "add tgl bangun";
		} else if ($tgl_pembangunan_old != '' && $tgl_pembangunan != $tgl_pembangunan_old || $tanggal_rencana_selesai_pembangunan_old != '' && $tanggal_rencana_selesai_pembangunan != $tanggal_rencana_selesai_pembangunan_old) {
			$f['tanggal_pembangunan_diubah_oleh'] = user_id();
			$f['tanggal_pembangunan_diubah_pada'] = date('Y-m-d H:i:s');

			// echo "edit tgl bangun";
		}

		if ($tanggal_selesai_pembangunan && $tanggal_selesai_pembangunan_old == '') {
			$f['tanggal_selesai_pembangunan_oleh'] = user_id();
			$f['tanggal_selesai_pembangunan_pada'] = date('Y-m-d H:i:s');
			// echo "add tgl selesai";
		} else if ($tanggal_selesai_pembangunan_old != '' && $tanggal_selesai_pembangunan != $tanggal_selesai_pembangunan_old) {
			$f['tanggal_selesai_pembangunan_diubah_oleh'] = user_id();
			$f['tanggal_selesai_pembangunan_diubah_pada'] = date('Y-m-d H:i:s');
			// echo "edit tgl bangun";
		}


		if ($tgl_pembangunan)
			$f['tanggal_pembangunan'] = $this->request->getVar('tanggal_pembangunan');

		if ($tanggal_selesai_pembangunan) //tanggal selesai pembangunan
			$f['tanggal_selesai_pembangunan'] = $this->request->getVar('tanggal_selesai_pembangunan') ? $this->request->getVar('tanggal_selesai_pembangunan') : null;

		if ($tanggal_rencana_selesai_pembangunan)
			$f['tanggal_rencana_selesai_pembangunan'] = $this->request->getVar('tanggal_rencana_selesai_pembangunan');



		// $response['get_cw'] = $get_cw;
		// echo "<pre>";
		// print_r($f);
		// echo "</pre>";

		// die();

		$sub = $this->siModel->select('id_subitem')->findAll();

		foreach ($sub as $s) {

			//cek apakah subitem cheklist sudah ada di tb checklist_work
			$a = $this->findObjectById($s->id_subitem, $get_cw);

			$hct = isset($this->request->getVar('hasil_cek_t')[$s->id_subitem]) ? 1 : 0;
			$hcf = isset($this->request->getVar('hasil_cek_f')[$s->id_subitem]) ? 1 : 0;
			$hcv = isset($this->request->getVar('hasil_cek_v')[$s->id_subitem]) ? 1 : 0;
			$kcp = $this->request->getVar('keterangan_cek_produksi')[$s->id_subitem];

			$f2 = array();

			if ($hct != "" || $hcf != "" || $hcv != "") {

				$f2['produksi_cek'] = user_id();
				$f2['produksi_cek_tgl'] = date('Y-m-d');
				$f2['keterangan_cek_produksi'] = $kcp;
				$f2['hasil_cek_t'] = $hct;
				$f2['hasil_cek_f'] = $hcf;
				$f2['hasil_cek_v'] = $hcv;

				// $this->cwModel
				// ->where('id_kavling', $this->request->getVar('id_kavling'))
				// ->where('id_subitem', $s->id_subitem)
				// ->delete();

				if ($a) {
					$u = $this->cwModel
						->set($f2)
						->where('id_kavling', $id_kavling)
						->where('id_subitem', $s->id_subitem);
					$u->update();
				} else {
					$f2['id_kavling'] = $id_kavling;
					$f2['id_subitem'] = $s->id_subitem;

					$this->cwModel->insert($f2);
				}
			}
		}

		$notif = "Melakukan perubahan pada progres pembangunan = " . $f['progres_bangunan'] . "%";
		// $id_kavling = $this->request->getVar('id_kavling');

		// $f['sumurbor'] = $this->request->getVar('sumurbor');
		// $f['sumurbor_keterangan'] = $this->request->getVar('sumurbor_keterangan');
		// $f['sumurbor_tanggal'] = $this->request->getVar('sumurbor_tanggal');
		// $f['sumurbor_oleh'] = $this->request->getVar('sumurbor_oleh');

		if ($f['id_produksi'] == null) {

			$f['add_by'] = user_id();
			$f['edit_by'] = user_id();
			$f['created_at'] = date('Y-m-d H:i:s');


			if ($this->produksiModel->insert($f)) {
				$this->kavlingModel->update(
					$id_kavling,
					[
						'id_produksi' => $this->produksiModel->getInsertID(),
						'sumurbor' => $this->request->getVar('sumurbor'),
						'sumurbor_keterangan' => $this->request->getVar('sumurbor_keterangan'),
						'sumurbor_tanggal' => $this->request->getVar('sumurbor_tanggal'),
						'sumurbor_oleh' => user_id(),
						'sumurbor_updated' => date("Y-m-d H:i:s"),
					]
				);

				$this->notif->tambah_notif("7;4;9", $notif, user_id(), $id_kavling, ""); //7 produksi 4 mkdt 9 direksi

				$response['success'] = true;
				$response['messages'] = 'Successfully updated';
			} else {
				$response['success'] = false;
				$response['messages'] = 'Terjadi Kesalahan';
			}
		} else {
			$f['edit_by'] = user_id();
			$f['updated_at'] = date('Y-m-d H:i:s');

			if ($this->produksiModel->update($f['id_produksi'], $f)) {
				$this->kavlingModel->update(
					$id_kavling,
					[
						'sumurbor' => $this->request->getVar('sumurbor'),
						'sumurbor_keterangan' => $this->request->getVar('sumurbor_keterangan'),
						'sumurbor_tanggal' => $this->request->getVar('sumurbor_tanggal'),
						'sumurbor_oleh' => user_id(),
					]
				);

				$this->notif->tambah_notif("7;4;9", $notif, user_id(), $id_kavling, ""); //7 produksi 4 mkdt 9 direksi

				$response['success'] = true;
				$response['messages'] = 'Successfully updated';
			} else {
				$response['success'] = false;
				$response['messages'] = 'Terjadi Kesalahan';
			}
		}
		return $this->response->setJSON($response);
	}
	function saveSLf()
	{
		$id_kavling = implode(',', $this->request->getVar('id_kavling'));

		// var_dump($this->request->getVar('id_proyek'));
		// die();

		$r['token'] = csrf_hash();

		$data['no_slf'] = $this->request->getVar('no_slf');
		$data['tanggal_slf'] = $this->request->getVar('tanggal_slf');
		$data['penanggungjawab'] = $this->request->getVar('penanggungjawab');
		$data['id_proyek'] = $this->request->getVar('id_proyek');
		$data['fungsi_utama'] = $this->request->getVar('fungsi_utama');
		$data['fungsi_tambahan'] = $this->request->getVar('fungsi_tambahan');
		$data['jenis_bangunan'] = $this->request->getVar('jenis_bangunan');
		$data['nama_bangunan'] = $this->request->getVar('nama_bangunan');
		$data['nomor_pendaftaran_bangunan'] = $this->request->getVar('nomor_pendaftaran_bangunan');
		$data['penerbitan_slf_no'] = $this->request->getVar('penerbitan_slf_no');
		$data['penerbitan_slf_tgl'] = $this->request->getVar('penerbitan_slf_tgl');
		$data['perpanjangan_slf_ke'] = $this->request->getVar('perpanjangan_slf_ke');
		$data['persyaratan_administrasi'] = $this->request->getVar('persyaratan_administrasi');
		$data['persyaratan_fungsi_bangunan'] = $this->request->getVar('persyaratan_fungsi_bangunan');
		$data['persyaratan_peruntukan'] = $this->request->getVar('persyaratan_peruntukan');
		$data['persyaratan_tata_bangunan'] = $this->request->getVar('persyaratan_tata_bangunan');
		$data['persyaratan_kelaikan'] = $this->request->getVar('persyaratan_kelaikan');
		$data['persyaratan_fungsi_bangunan'] = $this->request->getVar('persyaratan_fungsi_bangunan');
		$data['persyaratan_peruntukan'] = $this->request->getVar('persyaratan_peruntukan');
		$data['persyaratan_tata_bangunan'] = $this->request->getVar('persyaratan_tata_bangunan');
		$data['persyaratan_fungsi_bangunan'] = $this->request->getVar('persyaratan_fungsi_bangunan');


		$data['id_kavling'] = $id_kavling;
		$data['created_at'] = date("Y-m-d H:i:s");
		$data["add_by"] = user_id();

		$q = $this->db->table('list_slf')->insert($data);

		if ($q) {
			$r['success'] = true;
			$r['messages'] = 'Berhasil menyimpan data';
		} else {
			$r['success'] = false;
			$r['messages'] = 'Gagal menyimpan data';
		}

		return $this->response->setJSON($r);
	}
	function getSlf()
	{
		$id_proyek = $this->request->getVar('id_proyek');

		// Define the subquery for the GROUP_CONCAT function
		$subQuery = $this->db->table('kavling')
			->select('GROUP_CONCAT(concat(jalan.nama_jalan," No. ", kavling.no_kavling ) ORDER BY jalan.nama_jalan, kavling.no_kavling SEPARATOR \', \')', false)
			->join('jalan', 'jalan.id_jalan = kavling.id_jalan')
			->where('FIND_IN_SET(kavling.id_kavling, list_slf.id_kavling)', null, false)
			->getCompiledSelect();


		$builder = $this->db->table('list_slf')
			->select('list_slf.id, list_slf.no_slf, username, list_slf.created_at')
			->select("($subQuery) AS kavling", false)
			->join("users", "users.id = list_slf.add_by")
			->groupBy('list_slf.id');

		$q['data'] = $builder->get()->getResult();

		// foreach ($lists as $list) {
		// $list->id_kavling
		// }
		// $data[''] = $this->db->table('');
		return $this->response->setJSON($q);
	}
	function hapusSLF()
	{
		$id = $this->request->getPost('id');

		if (!$id) {
			return $this->response->setJSON([
				'status' => 'error',
				'message' => 'ID SLF tidak ditemukan'
			]);
		}

		$result = $this->db->table('list_slf')->delete(['id' => $id]);

		if ($result) {
			return $this->response->setJSON([
				'status' => 'success',
				'message' => 'Data SLF berhasil dihapus'
			]);
		} else {
			return $this->response->setJSON([
				'status' => 'error',
				'message' => 'Gagal menghapus data SLF'
			]);
		}
	}


	function getSLFPDF($id)
	{
		if (!$id)
			return false;

		$data['list_slf'] = $this->db->table("list_slf")
			->where('id', $id)
			->get()->getResult()[0];

		$id_kavling = $data['list_slf']->id_kavling;

		$id_kavling = explode(',', $id_kavling);

		$data['kavling'] = $this->db->table('kavling')
			->select('
                `proyek`.`nama_proyek`,
                `proyek`.`alamat_proyek`,
                `proyek`.`kelurahan`,
                `proyek`.`kecamatan`,
                `proyek`.`kota`,
                `proyek`.`provinsi`,
                `proyek`.`nama_pt`,
                `cluster`.`nama_cluster`,
                `jalan`.`nama_jalan`,
                `tipe`.`no_tipe_rumah`,
                `tipe`.`tipe_rumah`,
                `kavling`.`no_kavling`,
                konsumen.nama_konsumen
            ')
			->join('jalan', 'jalan.id_jalan = kavling.id_jalan')
			->join('cluster', 'cluster.id_cluster = jalan.id_cluster')
			->join('proyek', 'cluster.id_proyek = proyek.id_proyek')
			->join('tipe', 'tipe.id_tipe = kavling.id_tipe')

			->join('mkdt', 'mkdt.id_mkdt = kavling.id_mkdt', 'left')
			->join('konsumen', 'konsumen.id_konsumen = mkdt.id_mkdt', 'left')
			->whereIn('kavling.id_kavling', $id_kavling)
			->orderBy('jalan.nama_jalan', 'asc')
			->orderBy('kavling.no_kavling', 'asc')
			->get()->getResult();



		$data['mpdf'] = $this->mpdf;


		$html[0] = view('pdf/slf', $data);
		$filename = date('y-m-d-H-i-s') . '- SLF';
		$html[1] = view('pdf/slf_page3', $data);
		$header = '';

		// $mg = [$kop->pml, $kop->pmr, $kop->pmt, $kop->pmb];

		$this->mpdf->generate($html, $filename, $header);
		exit();
	}
	function getKavling()
	{
		$id_proyek = $this->request->getVar('id_proyek');
		$search = $this->request->getVar('search') ? $this->request->getVar('search') : "";


		$data['token'] = csrf_hash();
		$data['data'] = $this->db->table('kavling')
			->select('
                kavling.id_kavling, 
                kavling.id_mkdt,
                nama_jalan,
                no_kavling,
                nama_konsumen
            ')
			->join('jalan', 'jalan.id_jalan = kavling.id_jalan')
			->join('cluster', 'cluster.id_cluster = jalan.id_cluster')
			->join('proyek', 'proyek.id_proyek = cluster.id_proyek')
			->join('tipe', 'kavling.id_tipe = tipe.id_tipe')
			->join('mkdt', 'mkdt.id_kavling = kavling.id_kavling', 'left')
			->join('konsumen', 'konsumen.id_konsumen = mkdt.id_mkdt', 'left')
			->where('proyek.id_proyek', $id_proyek)
			->groupStart()
			->like('nama_jalan', $search)
			->orLike('nama_konsumen', $search)
			->orLike('no_kavling', $search)
			->groupEnd()
			->get()->getResult();

		return $this->response->setJSON($data);
	}
	function hapus_foto()
	{
		$response = array();
		$response['token'] = csrf_hash();

		$id = $this->request->getPost('id');

		$file = $this->db->table('file_produksi')->where('id', $id)->get()->getRow();
		if ($file) {
			$filePath = $file->lokasi . $file->file_name;
			$trashPath = 'uploads/produksi/trash/' . date('Ymd') . '/';

			if (!is_dir($trashPath)) {
				mkdir($trashPath, 0777, true);
			}

			$trashPath = $trashPath . basename($file->file_name);

			if (rename($filePath, $trashPath)) {
				if ($this->db->table('file_produksi')->delete(['id' => $id])) {
					if (file_exists($filePath)) {
						unlink($filePath);
					}
					$response['success'] = true;
					$response['messages'] = 'File berhasil dipindahkan ke folder trash';
				} else {
					$response['success'] = false;
					$response['messages'] = 'Terjadi kesalahan saat menghapus data file dari database';
				}
			} else {
				$response['success'] = false;
				$response['messages'] = 'Terjadi kesalahan saat memindahkan file ke folder trash';
			}
		} else {
			$response['success'] = false;
			$response['messages'] = 'File tidak ditemukan';
		}

		return $this->response->setJSON($response);
	}
	function get_gambarkerja()
	{
		$id = $this->request->getVar('id_gambar_kerja');
		$pass = $this->request->getVar("pass");
		$true = false;

		if ($pass == "password") {
			$true = true;
		}

		$r = $this->gambarkerjaModel
			->where('id_gambar_kerja', $id)
			->first();

		$filename = base_url($r->lokasi);

		return $this->response->setJSON(['lokasi' => $filename]);

		// header("Content-type:application/pdf");

		// // It will be called downloaded.pdf
		// header("Content-Disposition:attachment;filename=download.pdf");

		// // The PDF source is in original.pdf
		// readfile($filename); 
	}
	function edit_others()
	{
		$response = array();
		$response['token'] = csrf_hash();

		$builder = $this->db->table("others");

		$fields['progres'] = $this->request->getPost('f_progres_jalan');
		$fields['produksi_luas'] = $this->request->getPost('f_produksi_luas');
		$fields['produksi_keterangan'] = $this->request->getPost('f_produksi_keterangan');

		$fields['produksi_edit_by'] = user_id();
		$fields['produksi_updated_at'] = date('Y-m-d H:i:s');

		$id = $this->request->getPost('id_kavling');

		$this->validation->setRules([
			'no_kavling' => [
				'label' => 'No Rumah',
				'rules' => 'permit_empty|max_length[255]'
			]
		]);

		if ($this->validation->run($fields) == FALSE) {
			$response['success'] = false;
			$response['messages'] = $this->validation->listErrors();
		} else {
			$builder->where('id', $id);
			if ($builder->update($fields)) {
				$response['success'] = true;
				$response['messages'] = 'Data berhasil diperbaharui';
			} else {
				$response['success'] = false;
				$response['messages'] = 'Data gagal diperbaharui!';
			}
		}

		return $this->response->setJSON($response);
	}
	protected function num($d)
	{
		// $d = str_replace('.', "", $d);
		$d = str_replace(',', "", $d);

		return $d;
	}
}
