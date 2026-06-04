<?php
// ADEL CODEIGNITER 4 CRUD GENERATOR

namespace App\Controllers;

use App\Controllers\BaseController;

use App\Models\ClusterModel;
use App\Models\KavlingModel;
use App\Models\ProyekModel;
use App\Models\FileHargajualModel;
use App\Services\FileAccessService;

class Hargajual extends BaseController
{

	protected $hjfile;
	protected $clusterModel;
	protected $proyekModel;
	protected $kavlingModel;
	protected $validation;
	protected $db;
	protected $fileAccessService;

	public function __construct()
	{
		$this->clusterModel = new ClusterModel();
		$this->proyekModel = new ProyekModel();
		$this->kavlingModel = new KavlingModel();
		$this->hjfile = new FileHargajualModel();
		$this->validation =  \Config\Services::validation();
		$this->db = db_connect();
		$this->fileAccessService = new FileAccessService();
		
		$akses = $this->db->table('modul_akses')->where('user_id', user_id())->get();
		$hasAccess = false;
		foreach ($akses->getResult() as $row) {
			if ($row->module == 'Hargajual') {
				$hasAccess = true;
				break;
			}
		}
		if (!$hasAccess) {
			$data['message'] = 'Kamu tidak memiliki akses <a href="">Klik untuk kembali</a>';
			echo view('errors/html/unauthorized', $data);
		die();
		}
		
	}

	public function index()
	{
		$data['data']['proyek'] = $this->proyekModel->select('id_proyek, nama_proyek')->findAll();

		$data['content'] = 'hargajual/index';
		$data['data']['controller'] = 'hargajual';
		$data['data']['title'] = 'hargajual';
		return view('template', $data);
	}

	public function getDataTable()
	{
		// if(!$this->request->isAJAX()){
		// 	echo "ewean kuda";
		// 	return;
		// }
		$response = array();
		$data['token'] = csrf_hash();
		$data['data'] = array();

		$var = [];

		$var = array_merge($var, ["search" => ["value" => "", "regex" => false]]);
		// $data['test2'] = $var;

		$var = $this->request->getVar();



		$colum = ['hargajual.hargajual', 'hargajual.kpr', 'hargajual.id_tipe'];
		$condition = ['is_active' => $this->request->getPost('is_active')];

		$query = $this->db->table('hargajual')
			->select('
				hargajual.*,
				proyek.nama_proyek,
				file_hargajual.lokasi,
				file_hargajual.file_name,			
				users.username as uadd_by, 
				c.username as uedit_by, 
				')
			->join('proyek', 'proyek.id_proyek = hargajual.id_proyek')
			->join('file_hargajual', 'file_hargajual.id_filehj = hargajual.id_filehj', 'left')

			->join('users', 'users.id = hargajual.add_by', 'left')
			->join('users as c', 'c.id = hargajual.edit_by', 'left');

		if ($var['id_proyek'])
			$condition = array_merge($condition, ["hargajual.id_proyek" => $var['id_proyek']]);

		$result = $this->if_where($var, $colum, $condition, $query);

		$result
			->orderBy("tgl_harga", "desc")
			->offset($var['start'])
			->limit($var['length']);

		$x = $result->get();
		$data['draw'] = $var['draw'];

		//count filtered
		$countfiltered = $this->db->table('hargajual')
			->select('hargajual.*')
			->join('proyek', 'proyek.id_proyek = hargajual.id_proyek')
			->join('file_hargajual', 'file_hargajual.id_filehj = hargajual.id_filehj', 'left')
			->join('users', 'users.id = hargajual.add_by', 'left')
			->join('users as c', 'c.id = hargajual.edit_by', 'left');

		$countfiltered = $this->if_where($var, $colum, $condition, $countfiltered);
		$data['recordsFiltered'] = count($countfiltered->get()->getResult());

		$condition = [];
		$countTotal =  $this->db->table('hargajual')
			->select('count(hargajual.id) as count')
			->join('proyek', 'proyek.id_proyek = hargajual.id_proyek')
			->join('users', 'users.id = hargajual.add_by', 'left')
			->join('users as c', 'c.id = hargajual.edit_by', 'left')
			->where($condition);

		$data['recordsTotal'] =  $countTotal->get()->getResult()[0]->count;
		$no = $var['start'];
		foreach ($x->getResult() as $key => $value) {
			$ops = '<div class="btn-group">';
			$ops .= '	<button type="button" class="btn btn-outline-primary waves-effect btn-sm" onclick="edit(' . $value->id . ')"><i class="fa fa-edit"></i></button>';
			if ($value->is_active) {
				$ops .= '	<button type="button" class="btn btn-outline-danger waves-effect btn-sm" onclick="remove(' . $value->id . ', ' . $value->is_active . ')"><i class="fa fa-trash"></i></button>';
			} else {
				$ops .= '	<button type="button" class="btn btn-outline-success waves-effect btn-sm" onclick="remove(' . $value->id . ', ' . $value->is_active . ')"><i class="fa fa-check"></i></button>';
			}
			$ops .= '</div>';
			$no++;

			$fu = ($value->id_filehj)? "<br><a target=_blank href='".$this->fileAccessService->accessUrl('file_hargajual', (int) $value->id_filehj)."'>klik untuk melihat softfile <a>":'';

			$data['data'][$key] = array(
				$no,
				$value->nama_proyek . $fu,
				$this->format_tgl($value->tgl_harga),
				$value->row,
				$value->id_tipe,
				$value->lb,
				$value->lt,
				number_format($value->hargajual),
				number_format($value->hargajual_net),
				number_format($value->kpr),
				number_format($value->uang_muka),
				number_format($value->biaya_adm),
				number_format($value->bphtb),
				number_format($value->ppn),

				number_format($value->biaya_proses),
				// number_format($value->uang_muka + $value->bphtb + $value->biaya_adm + $value->biaya_proses),
				$this->is_active($value->is_subsidi, "Subsidi", "Non-Subsidi"),
				$value->keterangan,
				$this->is_active($value->is_active, "Akktif", "Tidak Aktif"),
				$value->uadd_by,
				$this->format_tgl($value->created_at),
				$value->uedit_by,
				$this->format_tgl($value->updated_at),
				$ops,
			);
		}
		return $this->response->setJSON($data);
	}
	function set_harga()
	{
		$response = array();
		$response['token'] = csrf_hash();

		$fields['id_kavling'] = $this->request->getVar('id_kavling');
		$fields['harga_akhir'] = $this->request->getVar('sh-id');
		$fields['harga_akhir_tgl'] = date('Y-m-d');
		$fields['harga_akhir_oleh'] = user_id();
		$fields['edit_by'] = user_id();

		$id = explode(";", $this->request->getPost('id_kavling'));
		$id_last = $id[count($id) - 1];
		$id_len = ($id_last == "") ? count($id) - 1 : count($id);

		$this->validation->setRules([
			'harga' => ['label' => 'Harga', 'rules' => 'permit_empty|max_length[255]']
		]);


		if ($id_len > 0) {
			for ($x = 0; $x < $id_len; $x++) {

				$fields['id_kavling'] = $id[$x];


				if ($this->validation->run($fields) == FALSE) {
					$response['success'] = false;
					$response['messages'] = $this->validation->listErrors();
				} else {
					if ($this->kavlingModel->update($fields['id_kavling'], $fields)) {
						$response['success'] = true;
						$response['messages'] = 'Successfully updated';
					} else {
						$response['success'] = false;
						$response['messages'] = 'Update error!';
					}
				}
			}
		} else {
			if ($this->validation->run($fields) == FALSE) {
				$response['success'] = false;
				$response['messages'] = $this->validation->listErrors();
			} else {

				if ($this->kavlingModel->update($fields['id_kavling'], $fields)) {

					$response['success'] = true;
					$response['messages'] = 'Successfully updated';
				} else {

					$response['success'] = false;
					$response['messages'] = 'Update error!';
				}
			}
		}
		return $this->response->setJSON($response);
	}
	function getAll()
	{

		$data['token'] = csrf_hash();
		$id_proyek = $this->request->getPost('id_proyek');

		$search = "";
		if ($this->request->getPost('search'))
			$search = $this->request->getPost('search');

		$data['data'] = $this->db->table('hargajual')
			->select('
				hargajual.*,
				proyek.nama_proyek,
				users.username as uadd_by, 
				c.username as uedit_by, 
				fhj.lokasi,
				fhj.file_name
			')
			->join('proyek', 'proyek.id_proyek = hargajual.id_proyek')
			->join('users', 'users.id = hargajual.add_by', 'left')
			->join('users as c', 'c.id = hargajual.edit_by', 'left')
			->join('file_hargajual fhj', 'hargajual.id_filehj = fhj.id_filehj', "left")
			->where('hargajual.id_proyek', $id_proyek)
			->where('hargajual.is_active', 1)
			->like('hargajual.id_tipe', $search)
			->orWhere('hargajual.id_proyek', $id_proyek)
			->where('hargajual.is_active', 1)
			->like('hargajual.tgl_harga', $search)
			->orWhere('hargajual.id_proyek', $id_proyek)
			->where('hargajual.is_active', 1)
			->like('hargajual.hargajual', $search)
			->orderBy('hargajual.tgl_harga','DESC')
			->get()->getResult();
		foreach ($data['data'] as $row) {
			if (!empty($row->id_filehj)) {
				$row->access_url = $this->fileAccessService->accessUrl('file_hargajual', (int) $row->id_filehj);
			}
		}
		// $data['token'] = csrf_hash();
		return $this->response->setJSON($data);
	}

	function getOne()
	{
		$id = $this->request->getPost('id_cluster');
		$data = $this->db->table('hargajual')
			->select('
			hargajual.*,
			file_hargajual.lokasi,
			file_hargajual.file_name,
			proyek.nama_proyek,
			tipe.tipe_rumah,
			users.username as uadd_by,
			c.username as uedit_by,
			')
			->join('proyek', 'proyek.id_proyek = hargajual.id_proyek')
			->join('file_hargajual', 'file_hargajual.id_filehj = hargajual.id_filehj', 'left')
			->join('tipe', 'hargajual.id_tipe = tipe.id_tipe', 'left')
			->join('users', 'users.id = hargajual.add_by', 'left')
			->join('users as c', 'c.id = hargajual.edit_by', 'left')
			->where('hargajual.id', $id)->get()->getRow(0);
		$data->token = csrf_hash();
		if (!empty($data->id_filehj)) {
			$data->access_url = $this->fileAccessService->accessUrl('file_hargajual', (int) $data->id_filehj);
		}
		// $data['token'] = csrf_hash();
		return $this->response->setJSON($data);
	}

	public function add()
	{

		$response = array();
		$response['token'] = csrf_hash();

		$id = $this->request->getPost('id');

		if ($id)
			return $this->edit();

		if ($this->request->getFile('id_filehj')->getSize() > 0) {
			$img = $this->request->getFile('id_filehj');

			$name = $img->getRandomName();
			$originalname = $img->getClientName();
			$lok = 'uploads/pricelist/' . date('Ymd') . '/';

			//pindahkan file ke folder uplaod
			$this->fileAccessService->storeAs($img, $lok, $name);

			$sp['lokasi'] = $lok;
			$sp['default_filename'] = $originalname;
			$sp['file_name'] = $name;
			$sp['upload_at'] = date('Y-m-d H:i:s');
			$sp['upload_by'] = user_id();

			$this->hjfile->insert($sp);
			$fields['id_filehj'] = $this->hjfile->getInsertID();
		}


		$fields['id_proyek'] = $this->request->getPost('idProyek');
		$fields['tgl_harga'] = $this->request->getPost('tgl_harga');
		$fields['row'] = $this->request->getPost('row');
		$fields['id_tipe'] = $this->request->getPost('id_tipe');
		$fields['lb'] = $this->request->getPost('lb');
		$fields['lt'] = $this->request->getPost('lt');
		$fields['hargajual'] = $this->num($this->request->getPost('hargajual'));
		$fields['hargajual_net'] = $this->num($this->request->getPost('hargajual_net'));
		$fields['kpr'] = $this->num($this->request->getPost('kpr'));
		$fields['bphtb'] = $this->num($this->request->getPost('bphtb'));
		$fields['uang_muka'] = $this->num($this->request->getPost('uang_muka'));
		$fields['biaya_adm'] = $this->num($this->request->getPost('biaya_adm'));
		$fields['ppn'] = $this->num($this->request->getPost('ppn'));
		$fields['is_subsidi'] = $this->num($this->request->getPost('is_subsidi'));
		$fields['biaya_proses'] = $this->num($this->request->getPost('biaya_proses'));
		$fields['keterangan'] = $this->request->getPost('keterangan');

		$fields['add_by'] = user_id();
		$fields['created_at'] = date("Y-m-d H:i:s");
		$fields['edit_by'] = user_id();
		$fields['updated_at'] = date("Y-m-d H:i:s");

		$this->validation->setRules([
			'id_proyek' => ['label' => 'Id proyek', 'rules' => 'permit_empty|max_length[255]'],
			'hargajual' => ['label' => 'Harga Jual', 'rules' => 'permit_empty|max_length[255]'],
		]);

		if ($this->validation->run($fields) == FALSE) {
			$response['success'] = false;
			$response['messages'] = $this->validation->listErrors();
		} else {

			if ($this->db->table("hargajual")->insert($fields)) {

				$response['success'] = true;
				$response['messages'] = 'Data berhasil diinput';
			} else {

				$response['success'] = false;
				$response['messages'] = 'Insertion error!';
			}
		}

		return $this->response->setJSON($response);
	}

	public function edit()
	{
		if ($this->request->getFile('id_filehj')->getSize() > 0) {
			$img = $this->request->getFile('id_filehj');

			$name = $img->getRandomName();
			$originalname = $img->getClientName();
			$lok = 'uploads/pricelist/' . date('Ymd') . '/';

			//pindahkan file ke folder uplaod
			$this->fileAccessService->storeAs($img, $lok, $name);

			$sp['lokasi'] = $lok;
			$sp['default_filename'] = $originalname;
			$sp['file_name'] = $name;
			$sp['upload_at'] = date('Y-m-d H:i:s');
			$sp['upload_by'] = user_id();

			$this->hjfile->insert($sp);
			$fields['id_filehj'] = $this->hjfile->getInsertID();
		}

		$response = array();
		$response['token'] = csrf_hash();

		$fields['id_proyek'] = $this->request->getPost('idProyek');
		$fields['tgl_harga'] = $this->request->getPost('tgl_harga');
		$fields['row'] = $this->request->getPost('row');
		$fields['id_tipe'] = $this->request->getPost('id_tipe');
		$fields['lb'] = $this->request->getPost('lb');
		$fields['lt'] = $this->request->getPost('lt');
		$fields['hargajual'] = $this->num($this->request->getPost('hargajual'));
		$fields['hargajual_net'] = $this->num($this->request->getPost('hargajual_net'));
		$fields['kpr'] = $this->num($this->request->getPost('kpr'));
		$fields['ppn'] = $this->num($this->request->getPost('ppn'));
		$fields['bphtb'] = $this->num($this->request->getPost('bphtb'));
		$fields['uang_muka'] = $this->num($this->request->getPost('uang_muka'));
		$fields['biaya_adm'] = $this->num($this->request->getPost('biaya_adm'));
		$fields['biaya_proses'] = $this->num($this->request->getPost('biaya_proses'));
		$fields['is_subsidi'] = $this->num($this->request->getPost('is_subsidi'));
		$fields['keterangan'] = $this->request->getPost('keterangan');

		$fields['edit_by'] = user_id();
		$fields['updated_at'] = date("Y-m-d H:i:s");

		$id = $this->request->getPost('id');

		$this->validation->setRules([
			'id_proyek' => ['label' => 'Id proyek', 'rules' => 'permit_empty|max_length[255]'],
			'hargajual' => ['label' => 'Harga Jual', 'rules' => 'permit_empty|max_length[255]'],
		]);

		if ($this->validation->run($fields) == FALSE) {

			$response['success'] = false;
			$response['messages'] = $this->validation->listErrors();
		} else {

			if ($this->db->table('hargajual')->where('id', $id)->update($fields)) {
				$response['success'] = true;
				$response['messages'] = 'Data berhasil diperbaharui';
			} else {

				$response['success'] = false;
				$response['messages'] = 'Opps!! Terjadi kesalahan';
			}
		}

		return $this->response->setJSON($response);
	}

	public function remove()
	{
		$response = array();
		$response['token'] = csrf_hash();

		$fields['is_active'] = $this->request->getPost('is_active') == 1 ? 0 : 1;

		$fields['edit_by'] = user_id();
		$fields['updated_at'] = date("Y-m-d H:i:s");

		$id = $this->request->getPost('id');

		$this->validation->setRules([
			'id' => ['label' => 'Tidak ada Pricelist terpilih', 'rules' => 'permit_empty|max_length[255]'],
		]);

		if ($this->validation->run($fields) == FALSE) {
			$response['success'] = false;
			$response['messages'] = $this->validation->listErrors();
		} else {

			if ($this->db->table('hargajual')->where('id', $id)->update($fields)) {
				$response['success'] = true;
				$response['messages'] = 'Pricelist berhasil dinonaktifkan';
			} else {

				$response['success'] = false;
				$response['messages'] = 'Opps!! Terjadi kesalahan saat menonaktifkan pricelist';
			}
		}

		return $this->response->setJSON($response);
	}
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
		$r = '<span class="btn btn-danger" style="padding:5px">' . $textf . '</span>';
		if ($id == "1") $r = '<span class="btn btn-primary" style="padding:5px">' . $texts . '</span>';
		return $r;
	}
	function format_tgl($tgl)
	{
		$v = date_parse($tgl);

		if ($tgl == "" || $tgl == "0000-00-00" || $tgl == null) return "-";
		if ($v['hour'])
			return date_format(date_create($tgl), "d-M-Y H:i:s");
		return date_format(date_create($tgl), "d-M-Y");
	}
	protected function num($d)
	{
		// $d = str_replace('.', "", $d);
		$d = str_replace(',', "", $d);

		return $d;
	}
}
