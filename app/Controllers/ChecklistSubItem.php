<?php
// ADEL CODEIGNITER 4 CRUD GENERATOR

namespace App\Controllers;

use App\Controllers\BaseController;

use App\Models\ChecklistSubItemModel;
use App\Models\ChecklistItemModel;
use App\Models\ChecklistGroupModel;

class ChecklistSubItem extends BaseController
{

	protected $checklistSubItemModel;
	protected $validation;

	public function __construct()
	{
		$this->checklistSubItemModel = new ChecklistSubItemModel();
		$this->ciModel = new ChecklistItemModel();
		$this->cgModel = new ChecklistGroupModel();
		$this->validation =  \Config\Services::validation();
	}

	public function index()
	{
		$data['data']['item'] = array();
		$g = $this->cgModel->select('id_group, nama_group, is_active')->findAll();
		$x = 0;
		foreach ($g as $g) {
			$ci = $this->ciModel->select('id_item, nama_item, is_active')->where("id_group", $g->id_group)->findAll();
			foreach ($ci as $ci) {
				if ($g->is_active == 0 || $ci->is_active == 0) {
					$is_active = 0;
				} else {
					$is_active = 1;
				}

				$data['data']['item'][$x] = array(
					"id_item" => $ci->id_item,
					"nama_item" => "($g->nama_group)" . $ci->nama_item,
					"is_active" => $is_active
				);
				$x++;
			}
		}

		$data['content'] = 'checklist/checklistSubItem';
		$data['data']['controller'] = 'checklistSubItem';
		$data['data']['title'] = 'Checklist Sub Item';

		return view('template', $data);
	}

	public function getAll()
	{
		$response = array();

		$data['data'] = array();
		$data['token'] = csrf_hash();

		$result = $this->checklistSubItemModel
			->select('
				id_subitem, 
				checklist_subitem.id_item, 
				checklist_subitem.is_active,
				nama_subitem, 
				checklist_subitem.add_by, 
				checklist_subitem.created_at, 
				checklist_subitem.edit_by, 
				checklist_subitem.updated_at,
				nama_item, nama_group
			')
			->join('checklist_item', 'checklist_item.id_item = checklist_subitem.id_item')
			->join('checklist_group', 'checklist_item.id_group = checklist_group.id_group')
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
			$ops .= '	<button type="button" class="btn btn-sm btn-info" onclick="edit(' . $value->id_subitem . ')"><i class="fa fa-edit"></i></button>';
			$ops .= '	<button type="button" class="btn btn-sm btn-danger" onclick="remove(' . $value->id_subitem . ', ' . $value->is_active . ')"><i class="fa ' . $x . '"></i></button>';
			$ops .= '</div>';

			$data['data'][$key] = array(
				$value->id_subitem,
				"($value->nama_group)$value->nama_item",
				$value->nama_subitem,
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

		$id = $this->request->getPost('id_subitem');

		if ($this->validation->check($id, 'required|numeric')) {

			$data = $this->checklistSubItemModel->where('id_subitem', $id)->first();
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

		$fields['id_subitem'] = $this->request->getPost('idSubitem');
		$fields['id_item'] = $this->request->getPost('idItem');
		$fields['nama_subitem'] = $this->request->getPost('namaSubitem');
		$fields['add_by'] = user_id();
		$fields['edit_by'] = user_id();


		$this->validation->setRules([
			'id_item' => ['label' => 'Id item', 'rules' => 'required|numeric|max_length[11]'],
			'nama_subitem' => ['label' => 'Nama subitem', 'rules' => 'required|max_length[500]']
		]);

		$ex = explode(";", $this->request->getPost('namaSubitem'));
		$c_ex = count($ex);

		if ($this->validation->run($fields) == FALSE) {

			$response['success'] = false;
			$response['messages'] = $this->validation->listErrors();
		} else {

			if ($c_ex > 1) {
				if ($ex[$c_ex - 1] == "") {
					$c_ex = $c_ex - 1;
				}
				for ($a = 0; $a < $c_ex; $a++) {
					$fields['nama_subitem'] = $ex[$a];

					if ($this->checklistSubItemModel->insert($fields)) {
						$response['success'] = true;
						$response['messages'] = 'Data has been inserted successfully';
					} else {
						$response['success'] = false;
						$response['messages'] = 'Insertion error!';
					}
				}
			} else {
				$fields['nama_subitem'] = $ex;
				if ($this->checklistSubItemModel->insert($fields)) {
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
		$fields['nama_subitem'] = $this->request->getPost('namaSubitem');
		$fields['add_by'] = $this->request->getPost('addBy');
		$fields['created_at'] = $this->request->getPost('createdAt');
		$fields['edit_by'] = $this->request->getPost('editBy');
		$fields['updated_at'] = $this->request->getPost('updatedAt');


		$this->validation->setRules([
			'id_item' => ['label' => 'Id item', 'rules' => 'required|numeric|max_length[11]'],
			'nama_subitem' => ['label' => 'Nama subitem', 'rules' => 'required|max_length[50]'],
			'add_by' => ['label' => 'Add by', 'rules' => 'permit_empty|max_length[30]'],
			'created_at' => ['label' => 'Created at', 'rules' => 'permit_empty|valid_date'],
			'edit_by' => ['label' => 'Edit by', 'rules' => 'permit_empty|max_length[30]'],
			'updated_at' => ['label' => 'Updated at', 'rules' => 'permit_empty|valid_date'],

		]);

		if ($this->validation->run($fields) == FALSE) {

			$response['success'] = false;
			$response['messages'] = $this->validation->listErrors();
		} else {

			if ($this->checklistSubItemModel->update($fields['id_subitem'], $fields)) {

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

		$id = $this->request->getPost('id_subitem');
		$is_active = $this->checklistSubItemModel->select('is_active')->where('id_subitem', $id)->first();
		$is_active = ($is_active->is_active == 0) ? 1 : 0;


		if (!$this->validation->check($id, 'required|numeric')) {

			throw new \CodeIgniter\Exceptions\PageNotFoundException();
		} else {

			if ($this->checklistSubItemModel->update($id, array("is_active" => $is_active))) {

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
