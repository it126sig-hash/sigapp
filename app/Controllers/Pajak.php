<?php
// ADEL CODEIGNITER 4 CRUD GENERATOR

namespace App\Controllers;

use App\Controllers\BaseController;

use App\Models\ClusterModel;
use App\Models\ProyekModel;

class Pajak extends BaseController
{

	protected $clusterModel;
	protected $proyekModel;
	protected $validation;
	protected $db;
	protected $notif;

	public function __construct()
	{
		$this->notif = new Notif();
		$this->clusterModel = new ClusterModel();
		$this->proyekModel = new ProyekModel();
		$this->validation =  \Config\Services::validation();
		$this->db = db_connect();
	}

	// public function index()
	// {
	// 	$data['data']['proyek'] = $this->proyekModel->select('id_proyek, nama_proyek')->findAll();

	// 	$data['content'] = 'hargajual/index';
	// 	$data['data']['controller'] = 'hargajual';
	// 	$data['data']['title'] = 'hargajual';
	// 	return view('template', $data);
	// }

	// public function getDataTable()
	// {
	// 	// if(!$this->request->isAJAX()){
	// 	// 	echo "ewean kuda";
	// 	// 	return;
	// 	// }
	// 	$response = array();
	// 	$data['token'] = csrf_hash();
	// 	$data['data'] = array();

	// 	$var = [];

	// 	$var = array_merge($var, ["search" => ["value" => "", "regex"=>false]]);
	// 	// $data['test2'] = $var;		

	// 	$var = $this->request->getVar();

	// 	// $data['test'] = $var;
	// 	// if(!$var['search'])
	// 	// 	$var['search']['value'] = "";

	// 	$colum = ['hargajual.hargajual', 'hargajual.kpr', 'tipe.tipe_rumah'];
	// 	$condition = [];

	// 	$query = $this->db->table('hargajual')
	// 		->select('
	// 			hargajual.*,
	// 			proyek.nama_proyek,
	// 			tipe.tipe_rumah,
	// 			users.username as uadd_by, 
	// 			c.username as uedit_by, 
	// 			')
	// 		->join('proyek', 'proyek.id_proyek = hargajual.id_proyek')
	// 		->join('tipe', 'hargajual.id_tipe = tipe.id_tipe', 'left')
	// 		->join('users', 'users.id = hargajual.add_by', 'left')
	// 		->join('users as c', 'c.id = hargajual.edit_by', 'left');

	// 	if ($var['id_proyek'])
	// 		$condition = array_merge($condition, ["hargajual.id_proyek" => $var['id_proyek']]);

	// 	$result = $this->if_where($var, $colum, $condition, $query);

	// 	$result
	// 		->offset($var['start'])
	// 		->limit($var['length']);

	// 	$x = $result->get();
	// 	$data['draw'] = $var['draw'];

	// 	//count filtered
	// 	$countfiltered = $this->db->table('hargajual')
	// 		->select('hargajual.*')
	// 		->join('proyek', 'proyek.id_proyek = hargajual.id_proyek')
	// 		->join('tipe', 'hargajual.id_tipe = tipe.id_tipe', 'left')
	// 		->join('users', 'users.id = hargajual.add_by', 'left')
	// 		->join('users as c', 'c.id = hargajual.edit_by', 'left');

	// 	$countfiltered = $this->if_where($var, $colum, $condition, $countfiltered);
	// 	$data['recordsFiltered'] = count($countfiltered->get()->getResult());

	// 	$condition = [];
	// 	$countTotal =  $this->db->table('hargajual')
	// 		->select('count(hargajual.id) as count')
	// 		->join('proyek', 'proyek.id_proyek = hargajual.id_proyek')
	// 		->join('tipe', 'hargajual.id_tipe = tipe.id_tipe', 'left')
	// 		->join('users', 'users.id = hargajual.add_by', 'left')
	// 		->join('users as c', 'c.id = hargajual.edit_by', 'left')
	// 		->where($condition);

	// 	$data['recordsTotal'] =  $countTotal->get()->getResult()[0]->count;
	// 	$no = $var['start'];
	//     foreach ($x->getResult() as $key => $value) {
	// 		$ops = '<div class="btn-group">';
	// 		$ops .= '	<button type="button" class="btn btn-outline-primary waves-effect btn-sm" onclick="edit(' . $value->id . ')"><i class="fa fa-edit"></i></button>';
	// 		$ops .= '	<button type="button" class="btn btn-outline-danger waves-effect btn-sm" onclick="remove(' . $value->id . ')"><i class="fa fa-trash"></i></button>';
	// 		$ops .= '</div>';
	// 		$no++;

	// 		$data['data'][$key] = array(
	// 			$no,
	// 			$value->nama_proyek,
	// 			$this->format_tgl($value->tgl_harga),
	// 			$value->row,
	// 			$value->tipe_rumah,
	// 			$value->lb,
	// 			$value->lt,
	// 			number_format($value->hargajual),
	// 			number_format($value->kpr),
	// 			number_format($value->uang_muka),
	// 			number_format($value->bphtb),
	// 			number_format($value->biaya_adm),
	// 			number_format($value->biaya_proses),
	// 			number_format($value->uang_muka + $value->bphtb + $value->biaya_adm + $value->biaya_proses),
	// 			$value->keterangan,
	// 			$value->uadd_by,
	// 			$this->format_tgl($value->created_at),
	// 			$value->uedit_by,
	// 			$this->format_tgl($value->updated_at),
	// 			$ops,
	// 		);

	// 	}
	//     return $this->response->setJSON($data);
	// }

	// public function getAll()
	// {
	// 	$response = array();
	// 	$data['token'] = csrf_hash();
	// 	$data['data'] = array();

	// 	$id_proyek = $this->request->getVar('id_proyek');
	// 	$search = $this->request->getVar('search');
	// 	if(!$search)
	// 		$search = "";

	// 	$result = $this->clusterModel
	// 		->select('id_cluster, cluster.id_proyek, nama_proyek, nama_cluster, is_active')
	// 		->join('proyek', 'proyek.id_proyek = cluster.id_proyek');

	// 	if($id_proyek) {
	// 		$result
	// 			->like('cluster.nama_cluster', $search)
	// 			->where('cluster.id_proyek', $id_proyek);
	// 	}

	// 	foreach ($result->findAll() as $key => $value) {

	// 		$ops = '<div class="btn-group">';
	// 		$ops .= '	<button type="button" class="btn btn-outline-primary waves-effect btn-sm" onclick="edit(' . $value->id_cluster . ')"><i class="fa fa-edit"></i></button>';
	// 		$ops .= '	<button type="button" class="btn btn-outline-danger waves-effect btn-sm" onclick="remove(' . $value->id_cluster . ')"><i class="fa fa-trash"></i></button>';
	// 		$ops .= '</div>';

	// 		$data['data'][$key] = array(
	// 			$value->id_cluster,
	// 			$value->id_proyek,
	// 			$value->nama_proyek,
	// 			$value->nama_cluster,
	// 			$value->is_active,

	// 			$ops,
	// 		);
	// 	}
	// 	return $this->response->setJSON($data);
	// }

	function getOne()
	{

		if ($this->request->isAJAX()) {
			$id_kavling = $this->request->getPost('id_kavling');
			$id_mkdt = $this->request->getPost('id_mkdt');
			$data = $this->db->table('kavling')
				->select('
					konsumen.nama_konsumen,
					konsumen.npwp,
					konsumen.alamat_konsumen,
					konsumen.file_npwp,
					konsumen.hp_konsumen,
					legal.pbb_pecah_nop,
					mkdt.harga_ppn,
					mkdt.harga_jual_net,
					mkdt.akad_tgl,
					mkdt.is_subsidi,
					pajak.*,
					c.username as uadd_by,
					d.username as uadd_by,
					
				')
				->join('mkdt', 'kavling.id_mkdt = mkdt.id_mkdt', 'left')
				->join('konsumen', 'konsumen.id_konsumen = mkdt.id_konsumen', 'left')
				->join('legal', 'legal.id_legal = kavling.id_legal', 'left')
				->join('pajak', 'pajak.id = kavling.id_pajak', 'left')
				->join('users as c', 'c.id = pajak.add_by', 'left')
				->join('users as d', 'c.id = pajak.edit_by', 'left')
				->where('kavling.id_kavling', $id_kavling)
				->get()->getRow(0);

			$query = '
			(SELECT 
				file_upload.*,
				c.username as uupload_by
				FROM `file_upload`
				left join users  as c on file_upload.upload_by = c.id

				WHERE `id_kavling` = ' . $id_kavling . '			
				AND `kategori` = 2
				ORDER BY `upload_at` DESC
				LIMIT 1 )
			UNION ALL
			( SELECT 
				file_upload.*,
				c.username as uupload_by
				FROM `file_upload`
				left join users  as c on file_upload.upload_by = c.id
				WHERE `id_kavling` = ' . $id_kavling . '
				AND `kategori` = 6
				ORDER BY `upload_at` DESC
				LIMIT 1)
			';

			// Query pertama untuk kategori 9

			// Menggabungkan kedua query dengan UNION ALL
			$query = $this->db->query($query);

			// Menjalankan query dan mendapatkan hasil
			$data->file_ajb = $query->getResult();


			//getFile
			$query = '
			(SELECT 
				file_upload.*,
				c.username as uupload_by
				FROM `file_upload`
				left join users  as c on file_upload.upload_by = c.id
				WHERE `id_kavling` = ' . $id_kavling . '
				AND `id_group` = 10
				AND `kategori` = 9
				ORDER BY `upload_at` DESC
				LIMIT 1 )
			UNION ALL
			( SELECT 
				file_upload.*,
				c.username as uupload_by
				FROM `file_upload`
				left join users  as c on file_upload.upload_by = c.id
				WHERE `id_kavling` = ' . $id_kavling . '
				AND `id_group` = 10
				AND `kategori` = 10
				ORDER BY `upload_at` DESC
				LIMIT 1)
			';

			// Query pertama untuk kategori 9

			// Menggabungkan kedua query dengan UNION ALL
			$query = $this->db->query($query);

			// Menjalankan query dan mendapatkan hasil
			$data->file = $query->getResult();


			//load file ppn
			$query = '
			(SELECT 
				file_upload.*,
				c.username as uupload_by
				FROM `file_upload`
				left join users  as c on file_upload.upload_by = c.id

				WHERE `id_kavling` = ' . $id_kavling . '
				AND `id_group` = 10
				AND `kategori` = 11
				ORDER BY `upload_at` DESC
				LIMIT 1 )
			UNION ALL
			( SELECT 
				file_upload.*,
				c.username as uupload_by
				FROM `file_upload`
				left join users  as c on file_upload.upload_by = c.id
				WHERE `id_kavling` = ' . $id_kavling . '
				AND `id_group` = 10
				AND `kategori` = 12
				ORDER BY `upload_at` DESC
				LIMIT 1)
				UNION ALL
			( SELECT 
				file_upload.*,
				c.username as uupload_by
				FROM `file_upload`
				left join users  as c on file_upload.upload_by = c.id
				WHERE `id_kavling` = ' . $id_kavling . '
				AND `id_group` = 10
				AND `kategori` = 13
				ORDER BY `upload_at` DESC
				LIMIT 1)
			';

			// Query pertama untuk kategori 9

			// Menggabungkan kedua query dengan UNION ALL
			$query = $this->db->query($query);

			// Menjalankan query dan mendapatkan hasil
			$data->file_ppn = $query->getResult();


			// if (!$data) {
			// 	$data = $this->db->table('konsumen')
			// 		->select('
			// 		konsumen.nama_konsumen
			// 		')
			// 		->join('mkdt', 'konsumen.id_konsumen = mkdt.id_konsumen')
			// 		->where('mkdt.id_mkdt', $id_mkdt)->get()->getRow(0);
			// }
			$data->token = csrf_hash();

			// $data['token'] = csrf_hash();
			return $this->response->setJSON($data);
		}
	}

	public function save()
	{

		$response = array();
		$response['token'] = csrf_hash();
		$id_kavling = $this->request->getPost('id_kavling');

		$pph42_nilai = $this->num($this->request->getPost('pph42_nilai'));

		// echo $pph42_nilai; die();

		$fields['id'] = $this->request->getPost('id');
		$fields['id_mkdt'] = $this->request->getPost('id_mkdt');
		// $fields['id_kavling'] = $id_kavling;

		$fields['pph42_tarif'] = $this->request->getPost('pph42_tarif');
		$fields['pph42_nilai'] = $pph42_nilai;
		$fields['pph42_id_billing'] = $this->request->getPost('pph42_id_billing');
		$fields['pph42_ntpn'] = $this->request->getPost('pph42_ntpn');
		$fields['pph42_tgl_bayar'] = $this->request->getPost('pph42_tgl_bayar');
		$fields['pph42_keterangan'] = $this->request->getPost('pph42_keterangan');


		$ppn_nilai = $this->num($this->request->getPost('ppn_nilai'));
		$ppn_tgl_bayar = $this->request->getPost('ppn_tgl_bayar');

		$fields['ppn_tarif'] = $this->request->getPost('ppn_tarif');
		$fields['ppn_nilai'] = $ppn_nilai;
		$fields['ppn_id_billing'] = $this->request->getPost('ppn_id_billing');
		$fields['ppn_ntpn'] = $this->request->getPost('ppn_ntpn');
		$fields['ppn_tgl_bayar'] = $ppn_tgl_bayar;
		$fields['ppn_keterangan'] = $this->request->getPost('ppn_keterangan');
		$fields['ppn_no_faktur'] = $this->request->getPost('ppn_no_faktur');

		// var_dump($this->request->getPost('pph42_nilai')); die();

		/************************ upload BPN *****************************/
		if ($this->request->getFile('pph42_file-bpn')->getSize() > 0) {
			$img = $this->request->getFile('pph42_file-bpn');

			$name = $img->getRandomName();

			$lok = 'uploads/l/' . date('Ymd') . '/';

			$img->move($lok, $name);

			$f = [
				'id_kavling' => $id_kavling,
				'id_group' => 10,  //id untuk pajak
				'lokasi' => $lok . $name,
				'file_name' => $img->getClientName(),
				'kategori' => 10,
				'default_filename' => $this->request->getPost('pph42_kategori-bpn'),
				'keterangan' => $this->request->getPost('pph42_file_keterangan-bpn'),
				'upload_at' => date('Y-m-d H:i:s'),
				'upload_by' => user_id(),
			];

			$this->db->table('file_upload')
				->insert($f);
		}
		/************************ upload E-billing *****************************/
		if ($this->request->getFile('pph42_file-ebilling')->getSize() > 0) {
			$img = $this->request->getFile('pph42_file-ebilling');

			$name = $img->getRandomName();

			$lok = 'uploads/l/' . date('Ymd') . '/';

			$img->move($lok, $name);

			$f = [
				'id_kavling' => $id_kavling,
				'id_group' => 10,  //id untuk pajak
				'lokasi' => $lok . $name,
				'file_name' => $img->getClientName(),
				'kategori' => 9,
				'default_filename' => $this->request->getPost('pph42_kategori-ebilling'),
				'keterangan' => $this->request->getPost('pph42_file_keterangan-ebilling'),
				'upload_at' => date('Y-m-d H:i:s'),
				'upload_by' => user_id(),
			];
			$this->db->table('file_upload')
				->insert($f);
		}

		/************************ upload BPN *****************************/
		if ($this->request->getFile('ppn_file-bpn')->getSize() > 0) {
			$img = $this->request->getFile('ppn_file-bpn');

			$name = $img->getRandomName();

			$lok = 'uploads/l/' . date('Ymd') . '/';

			$img->move($lok, $name);

			$f = [
				'id_kavling' => $id_kavling,
				'id_group' => 10,  //id untuk pajak
				'lokasi' => $lok . $name,
				'file_name' => $img->getClientName(),
				'kategori' => 12,
				'default_filename' => $this->request->getPost('ppn_kategori-bpn'),
				'keterangan' => $this->request->getPost('ppn_file_keterangan-bpn'),
				'upload_at' => date('Y-m-d H:i:s'),
				'upload_by' => user_id(),
			];

			$this->db->table('file_upload')
				->insert($f);
		}
		/************************ upload E-billing *****************************/
		if ($this->request->getFile('ppn_file-faktur')->getSize() > 0) {
			$img = $this->request->getFile('ppn_file-faktur');

			$name = $img->getRandomName();

			$lok = 'uploads/l/' . date('Ymd') . '/';

			$img->move($lok, $name);

			$f = [
				'id_kavling' => $id_kavling,
				'id_group' => 10,  //id untuk pajak
				'lokasi' => $lok . $name,
				'file_name' => $img->getClientName(),
				'kategori' => 13,
				'default_filename' => $this->request->getPost('ppn_kategori-ebilling'),
				'keterangan' => $this->request->getPost('ppn_file_keterangan-ebilling'),
				'upload_at' => date('Y-m-d H:i:s'),
				'upload_by' => user_id(),
			];
			$this->db->table('file_upload')
				->insert($f);
		}
		/************************ upload E-billing *****************************/
		if ($this->request->getFile('ppn_file-ebilling')->getSize() > 0) {
			$img = $this->request->getFile('ppn_file-ebilling');

			$name = $img->getRandomName();

			$lok = 'uploads/l/' . date('Ymd') . '/';

			$img->move($lok, $name);

			$f = [
				'id_kavling' => $id_kavling,
				'id_group' => 10,  //id untuk pajak
				'lokasi' => $lok . $name,
				'file_name' => $img->getClientName(),
				'kategori' => 11,
				'default_filename' => $this->request->getPost('ppn_kategori-ebilling'),
				'keterangan' => $this->request->getPost('ppn_file_keterangan-ebilling'),
				'upload_at' => date('Y-m-d H:i:s'),
				'upload_by' => user_id(),
			];
			$this->db->table('file_upload')
				->insert($f);
		}

		// var_dump($fields);die();

		$this->validation->setRules([
			'id_mkdt' => ['label' => 'Tidak ada data konsumen', 'rules' => 'permit_empty|max_length[255]']
		]);

		if (!$fields['id']) {
			if ($this->validation->run($fields) == FALSE) {
				$response['success'] = false;
				$response['messages'] = $this->validation->listErrors();
			} else {

				$fields['add_by'] = user_id();
				$fields['created_at'] = date("Y-m-d H:i:s");
				$q = $this->db->table("pajak")->insert($fields);
				if ($q) {
					$this->db->table('kavling')->update(
						['id_pajak' => $this->db->insertID()],
						['id_kavling' => $this->request->getPost('id_kavling')]
					);
					//insert ke log
					if ($pph42_nilai > 0) {
						$notif = 'Melakukan pembayaran PPH42';
						$this->notif->tambah_notif("3;5", $notif, user_id(), $id_kavling, 0); //keuangan legal
					}

					if($ppn_tgl_bayar){
						$notif = 'Melakukan pembayaran PPN';
						$this->notif->tambah_notif("3;5", $notif, user_id(), $id_kavling, 0); //keuangan legal		
					}

					$response['success'] = true;
					$response['messages'] = 'Data berhasil diinput';
				} else {
					$response['success'] = false;
					$response['messages'] = 'Kesalahan saat mengisi data!';
				}
			}
		} else {
			$fields['edit_by'] = user_id();
			$fields['updated_at'] = date("Y-m-d H:i:s");

			if ($q = $this->db->table("pajak")->where('id', $fields['id'])->update($fields)) {
				if ($fields['pph42_nilai'] > 0) {
					$notif = 'Melakukan perubahan pembayaran/detail pada PPH42';
					$this->notif->tambah_notif("3;5", $notif, user_id(), $id_kavling, 0); //keuangan legal
				}

				if($ppn_tgl_bayar){
					$notif = 'Melakukan perubahan pembayaran/detail pada PPH42';
					$this->notif->tambah_notif("3;5", $notif, user_id(), $id_kavling, 0); //keuangan legal		
				}

				$response['success'] = true;
				$response['messages'] = 'Data berhasil diubah';
			} else {
				$response['success'] = false;
				$response['messages'] = 'Kesalahan saat merubah data!';
			}
		}





		return $this->response->setJSON($response);
	}

	// public function edit()
	// {

	// 	$response = array();
	// 	$response['token'] = csrf_hash();

	// 	$fields['id_proyek'] = $this->request->getPost('idProyek');
	// 	$fields['tgl_harga'] = $this->request->getPost('tgl_harga');
	// 	$fields['row'] = $this->request->getPost('row');
	// 	$fields['id_tipe'] = $this->request->getPost('id_tipe');
	// 	$fields['lb'] = $this->request->getPost('lb');
	// 	$fields['lt'] = $this->request->getPost('lt');
	// 	$fields['hargajual'] = $this->num($this->request->getPost('hargajual'));
	// 	$fields['kpr'] = $this->num($this->request->getPost('kpr'));
	// 	$fields['bphtb'] = $this->num($this->request->getPost('bphtb'));
	// 	$fields['uang_muka'] = $this->num($this->request->getPost('uang_muka'));
	// 	$fields['biaya_adm'] = $this->num($this->request->getPost('biaya_adm'));
	// 	$fields['biaya_proses'] = $this->num($this->request->getPost('biaya_proses'));
	// 	$fields['keterangan'] = $this->request->getPost('keterangan');

	// 	$fields['edit_by'] = user_id();
	// 	$fields['updated_at'] = date("Y-m-d H:i:s");

	// 	$id = $this->request->getPost('id');

	// 	$this->validation->setRules([
	// 		'id_proyek' => ['label' => 'Id proyek', 'rules' => 'permit_empty|max_length[255]'],
	// 		'hargajual' => ['label' => 'Harga Jual', 'rules' => 'permit_empty|max_length[255]'],
	// 	]);

	// 	if ($this->validation->run($fields) == FALSE) {

	// 		$response['success'] = false;
	// 		$response['messages'] = $this->validation->listErrors();
	// 	} else {

	// 		if ($this->db->table('hargajual')->where('id', $id)->update($fields)) {
	// 			$response['success'] = true;
	// 			$response['messages'] = 'Data berhasil diperbaharui';
	// 		} else {

	// 			$response['success'] = false;
	// 			$response['messages'] = 'Opps!! Terjadi kesalahan';
	// 		}
	// 	}

	// 	return $this->response->setJSON($response);
	// }

	// public function remove()
	// {
	// 	$response = array();
	// 	$response['token'] = csrf_hash();

	// 	$fields['status'] = 0;

	// 	$fields['edit_by'] = user_id();
	// 	$fields['updated_at'] = date("Y-m-d H:i:s");

	// 	$id = $this->request->getPost('id');

	// 	$this->validation->setRules([
	// 		'id_proyek' => ['label' => 'Id proyek', 'rules' => 'permit_empty|max_length[255]'],
	// 	]);

	// 	if ($this->validation->run($fields) == FALSE) {

	// 		$response['success'] = false;
	// 		$response['messages'] = $this->validation->listErrors();
	// 	} else {

	// 		if ($this->db->table('promo')->where('id', $id)->update($fields)) {
	// 			$response['success'] = true;
	// 			$response['messages'] = 'Promo berhasil dinonaktifkan';
	// 		} else {

	// 			$response['success'] = false;
	// 			$response['messages'] = 'Opps!! Terjadi kesalahan';
	// 		}
	// 	}

	// 	return $this->response->setJSON($response);
	// }
	function if_where($var, $column, $condition, $query)
	{
		$x = 0;
		foreach ($column as $i) {
			if ($x === 0) {
				$query->like($i, $var['search']['value']);
			} else {
				$query->orLike($i, $var['search']['value']);
			}
			$query->where($condition);
			$x++;
		}
		return $query;
	}
	function is_active($id, $texts, $textf)
	{
		$r = '<span class="badge badge-pill badge-light-danger" text-capitalized="">' . $textf . '</span>';
		if ($id == "1") $r = '<span class="badge badge-pill badge-light-success" text-capitalized="">' . $texts . '</span>';
		return $r;
	}
	function format_tgl($tgl)
	{
		$v = date_parse($tgl);

		if ($tgl == "" || $tgl == "0000-00-00" || $tgl == null) return "-";
		if ($v['hour'])
			return date_format(date_create($tgl), "d-M-Y h:i:s");
		return date_format(date_create($tgl), "d-M-Y");
	}
	protected function num($d)
	{
		$d = str_replace('.', "", $d);
		$d = str_replace(',', "", $d);

		return $d;
	}
}
