<?php
// ADEL CODEIGNITER 4 CRUD GENERATOR

namespace App\Controllers;

use App\Controllers\BaseController;

use App\Models\ChecklistItemModel;
use App\Models\ChecklistGroupModel;

class ChecklistItem extends BaseController
{

	protected $checklistItemModel;
	protected $validation;

	public function __construct()
	{
		$this->checklistItemModel = new ChecklistItemModel();
		$this->cgModel = new ChecklistGroupModel();
		$this->validation =  \Config\Services::validation();
	}

	public function index()
	{
		$data['data']['group'] = $this->cgModel->select('id_group, nama_group, is_active')->findAll();
		$data['content'] = 'checklist/checklistItem';
		$data['data']['controller'] = 'checklistItem';
		$data['data']['title'] = 'Checklist Item';

		return view('template', $data);
	}

	public function getAll()
	{
		$response = array();

		$data['data'] = array();
		$data['token'] = csrf_hash();

		$result = $this->checklistItemModel
			->select('id_item, checklist_item.is_active, checklist_item.id_group, nama_item, checklist_item.add_by, checklist_item.created_at, checklist_item.edit_by, checklist_item.updated_at, nama_group')
			->join('checklist_group', 'checklist_group.id_group = checklist_item.id_group')
			->findAll();

		foreach ($result as $key => $value) {
			if ($value->is_active == 1) {
				$is_active = '<span class="badge badge-pill badge-light-success" text-capitalized="">Aktif</span>';
				$x = "fa-times";
			} else {
				$is_active = '<span class="badge badge-pill badge-light-danger" text-capitalized="">Tidak Aktif</span>';
				$x = "fa-check";
			}

			$ops = '<div class="btn-group">';
			$ops .= '	<button type="button" class="btn btn-sm btn-info" onclick="edit(' . $value->id_item . ')"><i class="fa fa-edit"></i></button>';
			$ops .= '	<button type="button" class="btn btn-sm btn-danger" onclick="remove(' . $value->id_item . ', ' . $value->is_active . ')"><i class="fa ' . $x . '"></i></button>';
			$ops .= '</div>';

			$data['data'][$key] = array(
				$value->id_item,
				$value->nama_group,
				$value->nama_item,
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

	public function getOne()
	{
		$response = array();

		$id = $this->request->getPost('id_item');

		if ($this->validation->check($id, 'required|numeric')) {

			$data = $this->checklistItemModel->where('id_item', $id)->first();
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

		$fields['id_item'] = $this->request->getPost('idItem');
		$fields['id_group'] = $this->request->getPost('idGroup');
		$fields['nama_item'] = $this->request->getPost('namaItem');
		$fields['add_by'] = user_id();
		$fields['edit_by'] = user_id();

		$this->validation->setRules([
			'id_group' => ['label' => 'Id group', 'rules' => 'required|numeric|max_length[11]'],
			'nama_item' => ['label' => 'Nama item', 'rules' => 'required|max_length[500]'],
		]);

		$ex = explode(";", $this->request->getPost('namaItem'));
		$c_ex = count($ex);


		if ($this->validation->run($fields) == FALSE) {

			$response['success'] = false;
			$response['messages'] = $this->validation->listErrors();

		} else {
			if ($c_ex > 1) {
				if ($ex[$c_ex - 1] == "") {
					$c_ex = $c_ex - 1;
				}
				for($a = 0; $a < $c_ex; $a++){
					$fields['nama_item'] = $ex[$a];

					if ($this->checklistItemModel->insert($fields)) {
						$response['success'] = true;
						$response['messages'] = 'Data has been inserted successfully';
					} else {		
						$response['success'] = false;
						$response['messages'] = 'Insertion error!';
					}
				}

			}else{
				$fields['nama_item'] = $ex;
				if ($this->checklistItemModel->insert($fields)) {
					$response['success'] = true;
					$response['messages'] = 'Data has been inserted successfully';
				} else {		
					$response['success'] = false;
					$response['messages'] = 'Insertion error!';
				}
			}

		}
		return $this->response->setJSON($response);

	}

	public function edit()
	{

		$response = array();
		$response['token'] = csrf_hash();

		$fields['id_subitem'] = $this->request->getPost('idSubitem');
		$fields['id_item'] = $this->request->getPost('idItem');
		$fields['id_group'] = $this->request->getPost('idGroup');
		$fields['nama_item'] = $this->request->getPost('namaItem');
		$fields['edit_by'] = user_id();


		$this->validation->setRules([
			'id_group' => ['label' => 'Id group', 'rules' => 'required|numeric|max_length[11]'],
			'nama_item' => ['label' => 'Nama item', 'rules' => 'required|max_length[50]']
		]);

		if ($this->validation->run($fields) == FALSE) {

			$response['success'] = false;
			$response['messages'] = $this->validation->listErrors();
		} else {

			if ($this->checklistItemModel->update($fields['id_item'], $fields)) {

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
		$response['token'] = csrf_hash();

		$id = $this->request->getPost('id_item');
		$is_active = $this->checklistItemModel->select('is_active')->where('id_item', $id)->first();
		$is_active = ($is_active->is_active == 0) ? 1 : 0;

		if (!$this->validation->check($id, 'required|numeric')) {

			throw new \CodeIgniter\Exceptions\PageNotFoundException();
		} else {

			if ($this->checklistItemModel->update($id, array("is_active" => $is_active))) {

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
