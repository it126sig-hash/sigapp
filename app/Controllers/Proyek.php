<?php
// ADEL CODEIGNITER 4 CRUD GENERATOR

namespace App\Controllers;

use App\Controllers\BaseController;

use App\Models\ProyekModel;
use App\Models\SiteplanuploadModel;
use CodeIgniter\Files\File;
use phpDocumentor\Reflection\DocBlock\Tags\Var_;

class Proyek extends BaseController
{

	protected $proyekModel;
	protected $validation;
	protected $sp;
	protected $db;

	public function __construct()
	{
		$this->proyekModel = new ProyekModel();
		$this->sp = new SiteplanuploadModel();
		$this->db = db_connect();
		$this->validation =  \Config\Services::validation();
	}

	public function index()
	{
		$data['content'] = 'master/proyek';
		$data['data']['controller'] = 'proyek';
		$data['data']['title'] = 'proyek';
		return view('template', $data);
	}

	public function getAll()
	{
		if (!$this->request->isAJAX())
			return $this->response->setJSON([]);

		$response = array();

		$data['data'] = array();
		$data['token'] = csrf_hash();

		$result = $this->proyekModel->select('id_proyek, nama_proyek, alamat_proyek, kelurahan, kecamatan, kota, provinsi, siteplan, logo');

		if ($this->request->getVar('search'))
			$result->like("nama_proyek", $this->request->getVar('search'));

		$x = 1;
		foreach ($result->find() as $key => $value) {

			if (in_groups(6) || in_groups(1)) {
				$ops = '<div class="btn-group">';
				$ops .= '	<button type="button" class="btn btn-outline-primary waves-effect btn-sm" onclick="edit(' . $value->id_proyek . ')"><i class="fas fa-edit"></i></button>';
				$ops .= '	<button type="button" class="btn btn-outline-danger waves-effect  btn-sm" onclick="remove(' . $value->id_proyek . ')"><i class="fa fa-trash"></i></button>';
				$ops .= '</div>';
			} else {
				$ops = "";
			}


			$data['data'][$key] = array(
				$x,
				$value->nama_proyek,
				$value->alamat_proyek,
				"<img width='50px' src='" . base_url() . "/" . $value->logo . "'>",

				$ops,
			);
			$data['data'][$key]['id_proyek'] = $value->id_proyek;
			$x++;
		}

		return $this->response->setJSON($data);
	}

	public function getOne()
	{
		$response = array();

		$id = $this->request->getPost('id_proyek');

		if ($this->validation->check($id, 'required|numeric')) {

			$data = $this->proyekModel->where('id_proyek', $id)->first();
			$data->token = csrf_hash();

			//get data gambar kerja
			$data->list_siteplan = $this->getSiteplanList($id);

			return $this->response->setJSON($data);
		} else {

			throw new \CodeIgniter\Exceptions\PageNotFoundException();
		}
	}

	public function add()
	{

		$response = array();

		$fields['id_proyek'] = $this->request->getPost('idProyek');
		$fields['nama_proyek'] = $this->request->getPost('namaProyek');
		$fields['alamat_proyek'] = $this->request->getPost('alamatProyek');
		$fields['kelurahan'] = $this->request->getPost('kelurahanProyek');
		$fields['kecamatan'] = $this->request->getPost('kecamatanProyek');
		$fields['kota'] = $this->request->getPost('kotaProyek');
		$fields['provinsi'] = $this->request->getPost('provinsiProyek');
		$fields['siteplan'] = '';
		$fields['logo'] = '';

		$response['token'] = csrf_hash();


		$this->validation->setRules([
			'nama_proyek' => ['label' => 'Nama proyek', 'rules' => 'permit_empty|max_length[255]'],
			'alamat_proyek' => ['label' => 'Alamat proyek', 'rules' => 'permit_empty|max_length[255]'],
			// 'siteplan' => ['label' => 'Siteplan', 'rules' => 'permit_empty|max_length[255]'],
			'file' => [
				'label' => 'Image File',
				'rules' => 'uploaded[file]'
					. '|is_image[file]'
					. '|mime_in[file,image/jpg,image/jpeg,image/gif,image/png,image/webp]'
					. '|max_size[file,12000]'
					. '|max_dims[file,9999,9999]',
			],
			'logo' => [
				'label' => 'Image File',
				'rules' => 'uploaded[logo]'
					. '|is_image[logo]'
					. '|mime_in[logo,image/jpg,image/jpeg,image/gif,image/png,image/webp]'
					. '|max_size[logo,12000]'
					. '|max_dims[logo,6000,6000]',
			],
		]);
		if ($this->validation->run($fields) == FALSE) {
			$response['success'] = false;
			$response['messages'] = $this->validation->listErrors();
		} else {

			//siteplan aplod
			$img = $this->request->getFile('file');

			$name = $img->getRandomName();
			$originalname = $img->getClientName();
			$fileExtension = $img->getMimeType();

			$img->move('uploads/siteplan/', $name);

			$img = $this->request->getFile('file');

			$newloc = 'uploads/siteplan/' . $name;

			//cuma untuk get width height image yang udah di upload
			$info = \Config\Services::image()
				->withFile($newloc)
				->getFile()
				->getProperties(true);

			$sp['file_name'] = $originalname;
			$sp['location'] = $newloc;
			$sp['width'] = $info['width'];
			$sp['height'] = $info['height'];
			$sp['file_type'] = $fileExtension;
			$sp['upload_at'] = date('Y-m-d H:i:s');
			$sp['upload_by'] = user_id();



			$fields['siteplan'] = $newloc;


			//logo aplod
			$logo = $this->request->getFile('logo');
			$name2 = $logo->getRandomName();
			$logo->move('uploads/logo/', $name2);

			$fields['logo'] = 'uploads/logo/' . $name2;


			if ($this->proyekModel->insert($fields)) {

				$sp['id_proyek'] = $this->proyekModel->getInsertID();
				// insert ke table siteplan_upload
				$this->sp->insert($sp);

				$response['success'] = true;
				$response['messages'] = 'Data has been inserted successfully';
			} else {

				$response['success'] = false;
				$response['messages'] = 'Insertion error!';
			}
		}

		// $this->response->setJSON(["jsonrpc" => "2.0", "result" => null, "id" => "id"]);
		return $this->response->setJSON($response);
	}

	public function edit()
	{

		$response = array();
		$response['token'] = csrf_hash();

		$fields['id_proyek'] = $this->request->getPost('idProyek');
		$fields['nama_proyek'] = $this->request->getPost('namaProyek');
		$fields['alamat_proyek'] = $this->request->getPost('alamatProyek');
		$fields['kelurahan'] = $this->request->getPost('kelurahanProyek');
		$fields['kecamatan'] = $this->request->getPost('kecamatanProyek');
		$fields['kota'] = $this->request->getPost('kotaProyek');
		$fields['provinsi'] = $this->request->getPost('provinsiProyek');
		// $fields['siteplan'] = $this->request->getPost('siteplan');
		// $fields['logo'] = $this->request->getPost('logo');

		$noup = $this->request->getPost('no_up');
		$noupl = $this->request->getPost('no_up_logo');

		$vf = ['file' => [
			'label' => 'Image File',
			'rules' => 'uploaded[file]'
				. '|is_image[file]'
				. '|mime_in[file,image/jpg,image/jpeg,image/gif,image/png,image/webp]'
				. '|max_size[file,12000]'
				. '|max_dims[file,6000,6000]',
		]];
		$vl = ['logon' => [
			'label' => 'Image File',
			'rules' => 'uploaded[logon]'
				. '|is_image[logon]'
				. '|mime_in[logon,image/jpg,image/jpeg,image/gif,image/png,image/webp]'
				. '|max_size[logon,12000]'
				. '|max_dims[logon,6000,6000]',
		]];
		$v = [
			'nama_proyek' => ['label' => 'Nama proyek', 'rules' => 'permit_empty|max_length[255]'],
			'alamat_proyek' => ['label' => 'Alamat proyek', 'rules' => 'permit_empty|max_length[255]']
		];

		$a = $v;
		if ($noup == 0)
			$a = array_merge($v, $vf);

		if ($noupl == 0)
			$a = array_merge($v, $vl);

		// var_dump($a);
		// return $this->response->setJSON($response);
		// die();

		$this->validation->setRules($a);



		if ($this->validation->run($fields) == FALSE) {

			$response['success'] = false;
			$response['messages'] = $this->validation->listErrors();
		} else {
			//edit upload siteplan
			if ($noup == 0) {
				$img = $this->request->getFile('file');
				$originalname = $img->getClientName();
				$fileExtension = $img->getMimeType();
				$name = $img->getRandomName();
				$img->move('uploads/siteplan/', $name);

				$newloc = 'uploads/siteplan/' . $name;

				//cuma untuk get width height image yang udah di upload
				$info = \Config\Services::image()
					->withFile($newloc)
					->getFile()
					->getProperties(true);

				$sp['id_proyek'] = $this->request->getPost('idProyek');
				$sp['file_name'] = $originalname;
				$sp['location'] = $newloc;
				$sp['width'] = $info['width'];
				$sp['height'] = $info['height'];
				$sp['file_type'] = $fileExtension;
				$sp['upload_at'] = date('Y-m-d H:i:s');
				$sp['upload_by'] = user_id();

				// insert ke table siteplan_upload
				$this->sp->insert($sp);

				$fields['siteplan'] = $newloc;
			}

			//edit upload logo
			if ($noupl == 0) {
				$logo = $this->request->getFile('logon');
				$name2 = $logo->getRandomName();
				$logo->move('uploads/logo/', $name2);

				$fields['logo'] = 'uploads/logo/' . $name2;
			}

			if ($this->proyekModel->update($fields['id_proyek'], $fields)) {

				$response['success'] = true;
				$response['messages'] = 'Successfully updated';
			} else {

				$response['success'] = false;
				$response['messages'] = 'Update error!';
			}
		}

		return $this->response->setJSON($response);
	}
	function getSiteplanList($id = null)
	{
		$id_tipe = ($id) ? $id : $this->request->getVar('idProyek');
		// $r['token'] = csrf_hash();
		return $this->db->table('siteplan_upload')
			->select('
				siteplan_upload.*,
                u.username as uadd_by
            ')
			->join("users as u", "u.id = siteplan_upload.upload_by", "left")
			->where('id_proyek', $id_tipe)
			->orderBy('upload_at', 'desc')
			->get()->getResult();
		// return $this->response->setJSON($r);
	}

	public function remove()
	{
		$response = array();
		$response['token'] = csrf_hash();

		$id = $this->request->getPost('id_proyek');

		if (!$this->validation->check($id, 'required|numeric')) {

			throw new \CodeIgniter\Exceptions\PageNotFoundException();
		} else {

			if ($this->proyekModel->where('id_proyek', $id)->delete()) {

				$response['success'] = true;
				$response['messages'] = 'Deletion succeeded';
			} else {

				$response['success'] = false;
				$response['messages'] = 'Deletion error!';
			}
		}

		return $this->response->setJSON($response);
	}
}
