<?php
// ADEL CODEIGNITER 4 CRUD GENERATOR

namespace App\Controllers;

use App\Controllers\BaseController;

use App\Models\ChecklistGroupModel;

class ChecklistGroup extends BaseController
{

	protected $checklistGroupModel;
	protected $validation;

	public function __construct()
	{
		$this->checklistGroupModel = new ChecklistGroupModel();
		$this->validation =  \Config\Services::validation();
	}

	public function index()
	{
		$data['content'] = 'checklist/checklistGroup';
		$data['data']['controller'] = 'checklistGroup';
		$data['data']['title'] = 'Checklist Ruang';

		return view('template', $data);
	}

	function getAll()
	{
		
		$response = array();
		$data['token'] = csrf_hash();
		$data['data'] = array();

		$result = $this->checklistGroupModel->select('id_group, nama_group, add_by, created_at, edit_by, updated_at, is_active	')->findAll();

		foreach ($result as $key => $value) {
			if ($value->is_active == 1) {
				$is_active = '<span class="badge badge-pill badge-light-success" text-capitalized="">Aktif</span>';
				$x = "fa-times";
			} else {
				$is_active = '<span class="badge badge-pill badge-light-danger" text-capitalized="">Tidak Aktif</span>';
				$x = "fa-check";
			}

			$ops = '<div class="btn-group">';
			$ops .= '	<button type="button" class="btn btn-sm btn-info" onclick="edit(' . $value->id_group . ')"><i class="fa fa-edit"></i></button>';
			$ops .= '	<button type="button" class="btn btn-sm btn-danger" onclick="remove(' . $value->id_group . ', ' . $value->is_active . ')"><i class="fa ' . $x . '"></i></button>';
			$ops .= '</div>';



			$data['data'][$key] = array(
				$value->id_group,
				$value->nama_group,
				$is_active,
				$value->add_by,
				$value->created_at,
				$value->edit_by,
				$value->updated_at,

				$ops,
			);
		}

		return $this->response->setJSON($data);
	}

	function getOne()
	{
		if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest')) {
			$response = array();

			$id = $this->request->getPost('id_group');

			if ($this->validation->check($id, 'required|numeric')) {

				$data = $this->checklistGroupModel->where('id_group', $id)->first();
				$data->token = csrf_hash();
				return $this->response->setJSON($data);
			} else {

				throw new \CodeIgniter\Exceptions\PageNotFoundException();
			}
		} else {
			echo "oops";
		}
	}

	function add()
	{

		$response = array();
		$response['token'] = csrf_hash();

		$fields['id_group'] = $this->request->getPost('idGroup');
		$fields['nama_group'] = $this->request->getPost('namaGroup');
		$fields['add_by'] = user_id();
		$fields['edit_by'] = user_id();


		$this->validation->setRules([
			'nama_group' => ['label' => 'Nama group', 'rules' => 'required|max_length[50]']
		]);

		if ($this->validation->run($fields) == FALSE) {

			$response['success'] = false;
			$response['messages'] = $this->validation->listErrors();
		} else {

			if ($this->checklistGroupModel->insert($fields)) {

				$response['success'] = true;
				$response['messages'] = 'Data has been inserted successfully';
			} else {

				$response['success'] = false;
				$response['messages'] = 'Insertion error!';
			}
		}

		return $this->response->setJSON($response);
	}

	function edit()
	{

		$response = array();
		$response['token'] = csrf_hash();


		$fields['id_group'] = $this->request->getPost('idGroup');
		$fields['nama_group'] = $this->request->getPost('namaGroup');
		$fields['edit_by'] = user_id();


		$this->validation->setRules([
			'nama_group' => ['label' => 'Nama group', 'rules' => 'required|max_length[50]'],
		]);

		if ($this->validation->run($fields) == FALSE) {

			$response['success'] = false;
			$response['messages'] = $this->validation->listErrors();
		} else {

			if ($this->checklistGroupModel->update($fields['id_group'], $fields)) {

				$response['success'] = true;
				$response['messages'] = 'Successfully updated';
			} else {

				$response['success'] = false;
				$response['messages'] = 'Update error!';
			}
		}

		return $this->response->setJSON($response);
	}

	function remove()
	{
		$response = array();
		$response['token'] = csrf_hash();

		$id = $this->request->getPost('id_group');
		$is_active = $this->checklistGroupModel->select('is_active')->where('id_group', $id)->first();
		$is_active = ($is_active->is_active == 0) ? 1 : 0;

		if (!$this->validation->check($id, 'required|numeric')) {

			throw new \CodeIgniter\Exceptions\PageNotFoundException();
		} else {

			if ($this->checklistGroupModel->update($id, array("is_active" => $is_active))) {

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
