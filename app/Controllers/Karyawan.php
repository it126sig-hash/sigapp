<?php
// ADEL CODEIGNITER 4 CRUD GENERATOR

namespace App\Controllers;

use App\Controllers\BaseController;

use App\Models\KaryawanModel;
use App\Models\DivisiModel;
use App\Models\LevelModel;
// use App\Models\AuthgroupsModel;
// use App\Models\AuthPermissionsModel;
use Myth\Auth\Authorization\PermissionModel;
use Myth\Auth\Authorization\GroupModel;

class Karyawan extends BaseController
{

	protected $karyawanModel;
	protected $validation;

	public function __construct()
	{
		$this->karyawanModel = new KaryawanModel();
		$this->divisiModel = new DivisiModel();
		$this->levelModel = new LevelModel();
		$this->validation =  \Config\Services::validation();
		$this->authGroup = new GroupModel();
		$this->authPermission = new PermissionModel();
	}

	public function index()
	{
		$data['data']['divisi'] = $this->authGroup->findAll();
		$data['data']['level'] = $this->authPermission->findAll();

		$data['content'] = 'master/karyawan';

		$data['data']['controller'] = 'karyawan';
		$data['data']['title'] = 'Karyawan';

		return view('template', $data);
	}

	public function getAll()
	{
		// $response = array();
		$data['token'] = csrf_hash();
		$data['data'] = array();

		$result = $this->karyawanModel
			->select('nik, nama_karyawan, karyawan.id_divisi, status, auth_groups.name as divisi, auth_permissions.name as level')
			->join('auth_groups', 'auth_groups.id = karyawan.id_divisi')
			->join('auth_permissions', 'auth_permissions.id = karyawan.id_level')
			->findAll();

		foreach ($result as $key => $value) {

			$ops = '<div class="btn-group">';
			$ops .= '	<button type="button" class="btn btn-sm btn-info" onclick="edit(' . $value->nik . ')"><i class="fa fa-edit"></i></button>';

			if ($value->status == 1) {
				$status  = '<span class="badge badge-pill badge-light-primary mr-1">Aktif</span>';
				$ops 	.= '	<button type="button" class="btn btn-sm btn-danger" onclick="remove(' . $value->nik . ', ' . $value->status . ')"><i class="fa fa-times"></i></button>';
			} else {
				$status  = '<span class="badge badge-pill badge-light-warning mr-1">Tidak Aktif</span>';
				$ops	.= '	<button type="button" class="btn btn-sm btn-success" onclick="remove(' . $value->nik . ', ' . $value->status . ')"><i class="fa fa-check"></i></button>';
			}

			$ops .= '</div>';

			$data['data'][$key] = array(
				$value->nik,
				$value->nama_karyawan,
				$value->divisi,
				$value->level,
				$status,
				$ops,
			);
		}

		return $this->response->setJSON($data);
	}

	public function getOne()
	{
		$response = array();

		$id = $this->request->getPost('nik');

		if ($this->validation->check($id, 'required|numeric')) {

			$data = $this->karyawanModel->where('nik', $id)->first();
			$data->token = csrf_hash();

			return $this->response->setJSON($data);
		} else {

			throw new \CodeIgniter\Exceptions\PageNotFoundException();
		}
	}

	public function add()
	{

		$response = array();
		$response['token'] = csrf_hash();

		$fields['nik'] = $this->request->getPost('nik');
		$fields['nama_karyawan'] = $this->request->getPost('nama_karyawan');
		$fields['id_divisi'] = $this->request->getPost('id_divisi');
		$fields['id_level'] = $this->request->getPost('id_level');
		$fields['status'] = $this->request->getPost('status');


		$this->validation->setRules([
			'nik' => ['label' => 'NIK', 'rules' => 'required|max_length[255]'],
			'nama_karyawan' => ['label' => 'Nama karyawan', 'rules' => 'required|max_length[255]'],
			'id_divisi' => ['label' => 'Divisi', 'rules' => 'permit_empty|max_length[255]'],
			'id_level' => ['label' => 'Level', 'rules' => 'permit_empty|max_length[255]'],
			'status' => ['label' => 'Status', 'rules' => 'permit_empty|max_length[255]'],
		]);

		if ($this->validation->run($fields) == FALSE) {

			$response['success'] = false;
			$response['messages'] = $this->validation->listErrors();
		} else {

			if ($this->karyawanModel->insert($fields)) {

				$response['success'] = true;
				$response['messages'] = 'Data has been inserted successfully';
			} else {

				$response['success'] = false;
				$response['messages'] = 'Insertion error!';
			}
		}

		return $this->response->setJSON($response);
	}
	function changeGroup($userId, $groupId)
	{
		$this->authGroup->removeUserFromAllGroups(intval($userId));
		$this->authGroup->addUserToGroup(intval($userId), intval($groupId));
	}
	function chnagePermission($userId, $permissionId)
	{
		$this->authPermission->removePermissionFromUser(intval($permissionId), intval($userId));
		$this->authPermission->addPermissionToUser(intval($permissionId), intval($userId));
	}
	public function edit()
	{

		$response = array();

		$fields['nik'] = $this->request->getPost('nik');
		$fields['nama_karyawan'] = $this->request->getPost('nama_karyawan');
		$fields['id_divisi'] = $this->request->getPost('id_divisi');
		$fields['id_level'] = $this->request->getPost('id_level');
		$fields['status'] = $this->request->getPost('status');

		$response['token'] = csrf_hash();

		//get karyawan data
		$k = $this->karyawanModel->where('nik', $this->request->getPost('nik'))->first();


		$this->validation->setRules([
			'nik' => ['label' => 'NIK', 'rules' => 'required|max_length[255]'],
			'nama_karyawan' => ['label' => 'Nama karyawan', 'rules' => 'required|max_length[255]'],
			'id_divisi' => ['label' => 'Divisi', 'rules' => 'permit_empty|max_length[255]'],
			'id_level' => ['label' => 'Level', 'rules' => 'permit_empty|max_length[255]'],
			'status' => ['label' => 'Status', 'rules' => 'permit_empty|max_length[255]'],
		]);

		if ($this->validation->run($fields) == FALSE) {

			$response['success'] = false;
			$response['messages'] = $this->validation->listErrors();
		} else {

			if ($this->karyawanModel->update($fields['nik'], $fields)) {

				//cahnge group/divisi
				if ($k->id_user) {
					$this->changeGroup($k->id_user, $fields['id_divisi']);
					$this->chnagePermission($k->id_user, $fields['id_level']);
				}


				$response['success'] = true;
				$response['messages'] = 'Successfully updated';
			} else {

				$response['success'] = false;
				$response['messages'] = 'Update error!';
			}
		}

		return $this->response->setJSON($response);
	}

	public function remove()
	{
		$response = array();
		$st = $this->request->getPost('status');

		$response['token'] = csrf_hash();

		$f['nik'] = $this->request->getPost('nik');
		$f['status'] = ($st == 1) ? 0 : 1;

		if ($this->karyawanModel->update($f['nik'], $f)) {

			$response['success'] = true;
			$response['messages'] = 'Berhasil diubah';
		} else {

			$response['success'] = false;
			$response['messages'] = 'Update error!';
		}

		return $this->response->setJSON($response);
	}
}
