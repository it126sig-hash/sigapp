<?php
// ADEL CODEIGNITER 4 CRUD GENERATOR

namespace App\Controllers;

use App\Controllers\BaseController;
use \App\Models\MenuModel;
use App\Models\TipeModel;
use App\Models\ProyekModel;
use App\Models\GambarkerjaModel;
use CodeIgniter\Files\File;


class Tipe extends BaseController
{

	protected $tipeModel;
	protected $validation;
	protected $proyekModel;
	protected $gambarkerjaModel;
	protected $db;

	public function __construct()
	{
		$this->tipeModel = new TipeModel();
		$this->proyekModel = new ProyekModel();
		$this->gambarkerjaModel = new GambarkerjaModel();
		$this->validation =  \Config\Services::validation();
		$this->db = db_connect();
	}

	public function index()
	{
		$data['data']['proyek'] = $this->proyekModel
			->select("id_proyek, nama_proyek")
			->findAll();

		$data['content'] = 'master/tipe';
		$data['data']['controller'] = 'tipe';
		$data['data']['title'] = 'Tipe';
		return view('template', $data);
	}

	public function getAll()
	{
		$response = array();
		$data['token'] = csrf_hash();
		$data['data'] = array();

		$var = $this->request->getVar();

		$result = $this->tipeModel
			->select('id_tipe, tipe.id_proyek,no_tipe_rumah, tipe_rumah, lb, lt, harga, keterangan, nama_proyek')
			->join('proyek', 'proyek.id_proyek = tipe.id_proyek')
			->where('tipe.id_proyek', $var['id_proyek']);

		if ($this->request->getVar('search'))
			$result->like("no_tipe_rumah", $this->request->getVar('search'));

		// $no = 1;
		foreach ($result->find() as $key => $value) {

			$ops = '<div class="btn-group">';
			$ops .= '	<button type="button" class="btn btn-outline-primary waves-effect btn-sm" onclick="edit(' . $value->id_tipe . ')"><i class="fa fa-edit"></i></button>';
			// $ops .= '	<button type="button" class="btn btn-sm btn-danger" onclick="remove('. $value->id_tipe .')"><i class="fa fa-trash"></i></button>';
			$ops .= '</div>';

			$data['data'][$key] = array(
				// $no,
				$value->id_tipe,
				$value->nama_proyek,
				$value->no_tipe_rumah,
				$value->tipe_rumah,
				$value->lb,
				$value->lt,
				// $value->harga,
				$value->keterangan,

				$ops,
			);
			// $no++;
		}

		return $this->response->setJSON($data);
	}
	public function getDataTables()
	{
		$response = array();
		$data['token'] = csrf_hash();
		$data['data'] = array();

		$var = [];

		$var = array_merge($var, ["search" => ["value" => "", "regex" => false]]);
		$data['test2'] = $var;

		$var = $this->request->getVar();

		$colum = ['tipe.no_tipe_rumah'];
		$condition = [];

		$query = $this->db->table('tipe')
			->select('id_tipe, tipe.id_proyek,no_tipe_rumah, tipe_rumah, lb, lt, harga, keterangan, nama_proyek, is_subsidi')
			->join('proyek', 'proyek.id_proyek = tipe.id_proyek');

		// $id_proyek = $var['id_proyek']?$var['id_proyek']:"-";
		$condition = array_merge($condition, ["tipe.id_proyek" => $var['id_proyek']]);


		$result = $this->if_where($var, $colum, $condition, $query);

		$result
			->offset($var['start'])
			->limit($var['length']);

		$x = $result->get();
		$data['draw'] = $var['draw'];

		//count filtered
		$countfiltered = $this->db->table('tipe')
			->select('id_tipe, tipe.id_proyek,no_tipe_rumah, tipe_rumah, lb, lt, harga, keterangan, nama_proyek')
			->join('proyek', 'proyek.id_proyek = tipe.id_proyek');

		$countfiltered = $this->if_where($var, $colum, $condition, $countfiltered);
		$data['recordsFiltered'] = count($countfiltered->get()->getResult());

		$condition = [];
		$countTotal =  $this->db->table('tipe')
			->select('count(tipe.id_tipe) as count')
			->join('proyek', 'proyek.id_proyek = tipe.id_proyek')
			->where($condition);

		$data['recordsTotal'] =  $countTotal->get()->getResult()[0]->count;
		$no = $var['start'];
		foreach ($x->getResult() as $key => $value) {
			$ops = '<div class="btn-group">';
			$ops .= '	<button type="button" class="btn btn-outline-primary waves-effect btn-sm" onclick="edit(' . $value->id_tipe . ')"><i class="fa fa-edit"></i></button>';
			$ops .= '	<button type="button" class="btn btn-outline-danger waves-effect btn-sm" onclick="remove(' . $value->id_tipe . ')"><i class="fa fa-trash"></i></button>';
			$ops .= '</div>';

			$data['data'][$key] = array(
				$value->id_tipe,
				$value->nama_proyek,
				$value->no_tipe_rumah,
				$value->tipe_rumah,
				$this->is_active($value->is_subsidi, "Subsidi", "Non-Subsidi"),
				$value->lb,
				$value->lt,
				// $value->harga,
				$value->keterangan,
				$ops,
			);
		}
		return $this->response->setJSON($data);
	}
	public function getOne()
	{
		$response = array();

		$id = $this->request->getPost('id_tipe');

		if ($this->validation->check($id, 'required|numeric')) {
			$data = $this->tipeModel->where('id_tipe', $id)->first();
			$data->token = csrf_hash();

			//get data gambar kerja
			$data->gambarkerja = $this->getGambarKerjaList();

			return $this->response->setJSON($data);
		} else {

			throw new \CodeIgniter\Exceptions\PageNotFoundException();
		}
	}
	function random_color()
	{
		// Generate random RGB values
		$r = mt_rand(0, 255);
		$g = mt_rand(0, 255);
		$b = mt_rand(0, 255);

		// Convert RGB to hexadecimal
		$hex = sprintf("#%02x%02x%02x", $r, $g, $b);

		return $hex;
	}
	function generateSimilarColors($baseColor, $variation = 20, $count = 1) {
		// Convert HEX to RGB
		list($r, $g, $b) = sscanf($baseColor, "#%02x%02x%02x");
	
		$colors = [];
		for ($i = 0; $i < $count; $i++) {
			// Generate random variation within the range
			$newR = max(0, min(255, $r + rand(-$variation, $variation)));
			$newG = max(0, min(255, $g + rand(-$variation, $variation)));
			$newB = max(0, min(255, $b + rand(-$variation, $variation)));
	
			// Convert back to HEX
			$colors[] = sprintf("#%02x%02x%02x", $newR, $newG, $newB);
		}
	
		return $colors[0];
	}
	
	public function add()
	{

		$response = array();
		$response['token'] = csrf_hash();

		$fields['id_tipe'] = $this->request->getPost('idTipe');
		$fields['id_proyek'] = $this->request->getPost('idProyek');
		$fields['no_tipe_rumah'] = $this->request->getPost('no_tipe_rumah');
		$fields['tipe_rumah'] = $this->request->getPost('tipeRumah');
		$fields['is_subsidi'] = $this->request->getPost('isSubsidi');
		$fields['lb'] = $this->request->getPost('lb');
		$fields['lt'] = $this->request->getPost('lt');
		$fields['harga'] = $this->request->getPost('harga');
		$fields['keterangan'] = $this->request->getPost('keterangan');


		$fields['id_gambar_kerja'] = null;
		$fields['id_gambar_tipe'] = null;
		$fields['id_gambar_denah'] = null;

		$getConf = $this->db->table("config_shape")->select("config_name")->get()->getResult();
		$st = false;
		foreach ($getConf as $g) {
			if ($g->config_name == $fields['tipe_rumah']) {
				$st = true;
				break;
			}
		}

		

		$color = ($fields['is_subsidi'] == 1) ? '#fbff00' : '#718096';
		// var_dump($this->generateSimilarColors($color));die();
		//add tipe ke configColor
		if (!$st) {
			$conf = [
				'config_name' => $fields['tipe_rumah'],
				'fill' => $this->generateSimilarColors($color)
				// 'fill' => $this->random_color()
			];
			$this->db->table("config_shape")->insert($conf);
		}


		$this->validation->setRules([
			'id_proyek' => ['label' => 'Id proyek', 'rules' => 'permit_empty|max_length[255]'],
			'tipe_rumah' => ['label' => 'Tipe rumah', 'rules' => 'permit_empty|max_length[255]'],
			'lb' => ['label' => 'Lb', 'rules' => 'permit_empty'],
			'lt' => ['label' => 'Lt', 'rules' => 'permit_empty'],
			'harga' => ['label' => 'Harga', 'rules' => 'permit_empty'],
			'keterangan' => ['label' => 'Keterangan', 'rules' => 'permit_empty'],
			'gambar_kerja' => [
				'label' => 'File',
				'rules' => 'uploaded[gambar_kerja]'
					. '|mime_in[gambar_kerja,application/pdf]'
					. '|max_size[gambar_kerja,12000]',
				'errors' => [
					'uploaded' => 'Harus mengunggah Gambar Kerja ',
					'mime_in' => 'Gambar Kerja harus berupa file pdf',
					'max_size' => 'Gambar Kerja tidak lebih dari 12MB'
				]
				// . '|max_dims[gambar_kerja,6000,6000]',
			],
			'gambar_tipe' => [
				'label' => 'File',
				'rules' => 'uploaded[gambar_tipe]'
					. '|mime_in[gambar_tipe,image/jpeg,image/png]'
					. '|max_size[gambar_tipe,12000]',
				'errors' => [
					'uploaded' => 'Harus mengunggah Gambar Ilustrasi ',
					'mime_in' => 'Gambar Ilustrasi harus berupa file gambar jpg/png',
					'max_size' => 'Gambar Ilustrasi tidak lebih dari 12MB'
				]
				// . '|max_dims[gambar_kerja,6000,6000]',
			],
			'gambar_denah' => [
				'label' => 'File',
				'rules' => 'uploaded[gambar_denah]'
					. '|mime_in[gambar_denah,image/jpeg,image/png]'
					. '|max_size[gambar_denah,12000]',
				'errors' => [
					'uploaded' => 'Harus mengunggah Denah Arsitektural ',
					'mime_in' => 'Denah Arsitektural harus berupa file gambar jpg/png',
					'max_size' => 'Denah Arsitektural tidak lebih dari 12MB'
				]
				// . '|max_dims[gambar_kerja,6000,6000]',
			],

		]);


		if ($this->validation->run($fields) == FALSE) {
			$response['success'] = false;
			$response['messages'] = $this->validation->listErrors();
		} else {

			$proyek = $this->proyekModel->where('id_proyek', $fields['id_proyek'])->first();

			/************************ upload gambar kerja *****************************/
			$img = $this->request->getFile('gambar_kerja');
			$originalname = $img->getClientName();
			$name = $img->getRandomName();

			$lok = 'uploads/gambarkerja/' . date('Ymd') . '/';
			

			$img->move($lok, $name);

			
			$f['lokasi'] = $lok . $name;
			$f['default_filename'] = $originalname;
			$f['keterangan'] = "tipe: " . $fields['tipe_rumah']
				. ", Proyek: " . $proyek->nama_proyek
				. ", Nomor: " . $fields['no_tipe_rumah'];
			$f['upload_at'] = date('Y-m-d H:i:s');
			$f['upload_by'] = user_id();

			$this->gambarkerjaModel->insert($f);

			$fields['id_gambar_kerja'] = $this->gambarkerjaModel->getInsertID();

			
			/************************ end of upload gambar kerja *****************************/

			/************************ upload gambar kerja *****************************/
			$img = $this->request->getFile('gambar_tipe');
			$originalname = $img->getClientName();
			$name = $img->getRandomName();

			$lok = 'uploads/gambartipe/' . date('Ymd') . '/';
		
			$img->move($lok, $name);

			
			$f['lokasi'] = $lok . $name;
			$f['default_filename'] = $originalname;
			$f['keterangan'] = "tipe: " . $fields['tipe_rumah']
				. ", Proyek: " . $proyek->nama_proyek
				. ", Nomor: " . $fields['no_tipe_rumah'];
			$f['tipe'] = "gambar ilustrasi";
			$f['upload_at'] = date('Y-m-d H:i:s');
			$f['upload_by'] = user_id();

			$this->gambarkerjaModel->insert($f);

			$fields['id_gambar_tipe'] = $this->gambarkerjaModel->getInsertID();

			
			
			/************************ end of upload gambar kerja *****************************/

			/************************ upload gambar kerja *****************************/
			$img = $this->request->getFile('gambar_denah');
			$originalname = $img->getClientName();
			$name = $img->getRandomName();

			$lok = 'uploads/gambardenah/' . date('Ymd') . '/';
		
			$img->move($lok, $name);

			
			$f['lokasi'] = $lok . $name;
			$f['default_filename'] = $originalname;
			$f['keterangan'] = "tipe: " . $fields['tipe_rumah']
				. ", Proyek: " . $proyek->nama_proyek
				. ", Nomor: " . $fields['no_tipe_rumah'];
			$f['tipe'] = "gambar denah";
			$f['upload_at'] = date('Y-m-d H:i:s');
			$f['upload_by'] = user_id();

			$this->gambarkerjaModel->insert($f);

			$fields['id_gambar_denah'] = $this->gambarkerjaModel->getInsertID();

			
			
			/************************ end of upload gambar kerja *****************************/

			
			// $fields['siteplan'] = 

			if ($this->tipeModel->insert($fields)) {
				//set id_tipe di gambar_kerja
				$this->gambarkerjaModel->update($fields['id_gambar_kerja'], ['id_tipe' => $this->tipeModel->getInsertID()]);
				$this->gambarkerjaModel->update($fields['id_gambar_tipe'], ['id_tipe' => $this->tipeModel->getInsertID()]);
				$this->gambarkerjaModel->update($fields['id_gambar_denah'], ['id_tipe' => $this->tipeModel->getInsertID()]);

				$response['success'] = true;
				$response['messages'] = 'Data has been inserted successfully';
			} else {

				$response['success'] = false;
				$response['messages'] = 'Insertion error!';
			}
		}

		return $this->response->setJSON($response);
	}

	public function edit()
	{

		$response = array();
		$response['token'] = csrf_hash();

		$fields['id_tipe'] = $this->request->getPost('idTipe');
		$fields['id_proyek'] = $this->request->getPost('idProyek');
		$fields['no_tipe_rumah'] = $this->request->getPost('no_tipe_rumah');
		$fields['tipe_rumah'] = $this->request->getPost('tipeRumah');
		$fields['is_subsidi'] = $this->request->getPost('isSubsidi');
		$fields['lb'] = $this->request->getPost('lb');
		$fields['lt'] = $this->request->getPost('lt');
		$fields['harga'] = $this->request->getPost('harga');
		$fields['keterangan'] = $this->request->getPost('keterangan');

		$id_gambar_kerja = $this->request->getPost('id_gambar_kerja');

		$noup = $this->request->getPost('no_up');

		$getConf = $this->db->table("config_shape")->select("config_name")->get()->getResult();
		$st = false;
		foreach ($getConf as $g) {
			if ($g->config_name == $fields['tipe_rumah']) {
				$st = true;
				break;
			}
		}
		//add tipe ke configColor
		if (!$st) {
			$conf = [
				'config_name' => $fields['tipe_rumah'],
				'fill' => $this->random_color()
			];
			$this->db->table("config_shape")->insert($conf);
		}

		if ($noup == 0) {
			$this->validation->setRules([
				'id_proyek' => ['label' => 'Id proyek', 'rules' => 'permit_empty|max_length[255]'],
				'tipe_rumah' => ['label' => 'Tipe rumah', 'rules' => 'permit_empty|max_length[255]'],
				'lb' => ['label' => 'Lb', 'rules' => 'permit_empty'],
				'lt' => ['label' => 'Lt', 'rules' => 'permit_empty'],
				'harga' => ['label' => 'Harga', 'rules' => 'permit_empty'],
				'keterangan' => ['label' => 'Keterangan', 'rules' => 'permit_empty'],
				'gambar_kerja' => [
					'label' => 'File',
					'rules' => 'uploaded[gambar_kerja]'
						. '|mime_in[gambar_kerja,application/pdf]'
						. '|max_size[gambar_kerja,12000]',
					// . '|max_dims[gambar_kerja,6000,6000]',
				],

			]);
		} else {
			$this->validation->setRules([
				'id_proyek' => ['label' => 'Id proyek', 'rules' => 'permit_empty|max_length[255]'],
				'no_tipe_rumah' => ['label' => 'Nomor', 'rules' => 'permit_empty|max_length[255]'],
				'tipe_rumah' => ['label' => 'Tipe rumah', 'rules' => 'permit_empty|max_length[255]'],
				'lb' => ['label' => 'Lb', 'rules' => 'permit_empty'],
				'lt' => ['label' => 'Lt', 'rules' => 'permit_empty'],
				'harga' => ['label' => 'Harga', 'rules' => 'permit_empty'],
				'keterangan' => ['label' => 'Keterangan', 'rules' => 'permit_empty'],
			]);
		}

		if ($this->validation->run($fields) == FALSE) {
			$response['success'] = false;
			$response['messages'] = $this->validation->listErrors();
		} else {
			$proyek = $this->proyekModel->where('id_proyek', $fields['id_proyek'])->first();

			if ($noup == 0) {
				/************************ upload gambar kerja *****************************/
				$img = $this->request->getFile('gambar_kerja');
				$originalname = $img->getClientName();
				$name = $img->getRandomName();

				$lok = 'uploads/gambarkerja/' . date('Ymd') . '/';
				

				$img->move($lok, $name);

				$f['id_gambar_kerja'] = '';
				$f['id_tipe'] = $fields['id_tipe'];
				$f['lokasi'] = $lok . $name;
				$f['default_filename'] = $originalname;
				$f['keterangan'] = "tipe: " . $fields['tipe_rumah']
					. ", Proyek: " . $proyek->nama_proyek
					. ", Nomor: " . $fields['no_tipe_rumah'];
				$f['upload_at'] = date('Y-m-d H:i:s');
				$f['upload_by'] = user_id();

				$this->gambarkerjaModel->insert($f);

				$fields['id_gambar_kerja'] = $this->gambarkerjaModel->getInsertID();

				/************************ upload gambar kerja *****************************/
			}

			/************************ upload gambar kerja *****************************/
			if ($this->request->getPost('no_up_gambar_tipe') == 0) {
				$img = $this->request->getFile('gambar_tipe');
				$originalname = $img->getClientName();
				$name = $img->getRandomName();

				$lok = 'uploads/gambartipe/' . date('Ymd') . '/';
			
				$img->move($lok, $name);

				
				$f['lokasi'] = $lok . $name;
				$f['default_filename'] = $originalname;
				$f['keterangan'] = "tipe: " . $fields['tipe_rumah']
					. ", Proyek: " . $proyek->nama_proyek
					. ", Nomor: " . $fields['no_tipe_rumah'];
				$f['tipe'] = "gambar ilustrasi";
				$f['upload_at'] = date('Y-m-d H:i:s');
				$f['upload_by'] = user_id();

				$this->gambarkerjaModel->insert($f);

				$fields['id_gambar_tipe'] = $this->gambarkerjaModel->getInsertID();
			}
			/************************ end of upload gambar kerja *****************************/


			/************************ upload gambar kerja *****************************/
			if ($this->request->getPost('no_up_gambar_denah') == 0) {
				$img = $this->request->getFile('gambar_denah');
				$originalname = $img->getClientName();
				$name = $img->getRandomName();

				$lok = 'uploads/gambardenah/' . date('Ymd') . '/';
			
				$img->move($lok, $name);

				
				$f['lokasi'] = $lok . $name;
				$f['default_filename'] = $originalname;
				$f['keterangan'] = "tipe: " . $fields['tipe_rumah']
					. ", Proyek: " . $proyek->nama_proyek
					. ", Nomor: " . $fields['no_tipe_rumah'];
				$f['tipe'] = "gambar denah";
				$f['upload_at'] = date('Y-m-d H:i:s');
				$f['upload_by'] = user_id();

				$this->gambarkerjaModel->insert($f);

				$fields['id_gambar_denah'] = $this->gambarkerjaModel->getInsertID();
			}

			if ($this->tipeModel->update($fields['id_tipe'], $fields)) {
				$response['success'] = true;
				$response['messages'] = 'Successfully updated';
			} else {
				$response['success'] = false;
				$response['messages'] = 'Update error!';
			}
		}

		return $this->response->setJSON($response);
	}
	function getGambarKerjaList($id = null)
	{
		$id_tipe = ($id) ? $id : $this->request->getVar('id_tipe');
		// $r['token'] = csrf_hash();
		return $this->db->table('gambar_kerja')
			->select('
				gambar_kerja.*,
                u.username as uadd_by
            ')
			->join("users as u", "u.id = gambar_kerja.upload_by", "left")
			->where('id_tipe', $id_tipe)
			->where('tipe', "gambarkerja")
			->orderBy('upload_at', 'desc')
			->get()->getResult();
		// return $this->response->setJSON($r);
	}
	public function remove()
	{
		$response = array();
		$response['token'] = csrf_hash();
		$id = $this->request->getPost('id_tipe');

		if (!$this->validation->check($id, 'required|numeric')) {

			throw new \CodeIgniter\Exceptions\PageNotFoundException();
		} else {

			if ($this->tipeModel->where('id_tipe', $id)->delete()) {

				$response['success'] = true;
				$response['messages'] = 'Deletion succeeded';
			} else {

				$response['success'] = false;
				$response['messages'] = 'Deletion error!';
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
		$r = '<span class="btn btn-outline-secondary" text-capitalized="">' . $textf . '</span>';
		if ($id == "1") $r = '<span class="btn  btn-outline-warning" text-capitalized="">' . $texts . '</span>';
		return $r;
	}
}
