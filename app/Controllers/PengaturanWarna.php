<?php
// ADEL CODEIGNITER 4 CRUD GENERATOR

namespace App\Controllers;

use App\Controllers\BaseController;

use App\Models\PengaturanWarnaModel;

class PengaturanWarna extends BaseController
{
	
    protected $pengaturanWarnaModel;
    protected $validation;
	
	public function __construct()
	{
	    $this->pengaturanWarnaModel = new PengaturanWarnaModel();
       	$this->validation =  \Config\Services::validation();
		
	}
	
	public function index()
	{

	    $data = [
                'controller'    	=> 'pengaturanWarna',
                'title'     		=> 'Pengaturan Warna'				
			];
		
		$data['content'] = 'master/pengaturanWarna';
		return view('template',$data);
		// return view('', $data);
			
	}

	public function getAll()
	{
 		$response = array();	
		 $data['token'] = csrf_hash();	
		
	    $data['data'] = array();
 
		$result = $this->pengaturanWarnaModel->select('config_name, fill, dashed, keterangan, add_by, date_add, edit_by, date_edit')->findAll();
		
		foreach ($result as $key => $value) {
							
			$ops = '<div class="btn-group">';
			$ops .= '	<button type="button" class="btn btn-sm btn-info" onclick="edit(\''. $value->config_name .'\')"><i class="fa fa-edit"></i></button>';
			$ops .= '	<button type="button" class="btn btn-sm btn-danger" onclick="remove(\''. $value->config_name .'\')"><i class="fa fa-trash"></i></button>';
			$ops .= '</div>';
			
			$data['data'][$key] = array(
				$value->config_name,
				"<span class='btn' style='background-color: ".$value->fill."'>$value->fill</span>",
				$value->dashed,
				$value->keterangan,
				$value->add_by,
				$value->date_add,
				$value->edit_by,
				$value->date_edit,

				$ops,
			);
		} 

		return $this->response->setJSON($data);		
	}
	
	public function getOne()
	{
 		$response = array();
		
		$id = $this->request->getPost('config_name');
		
		if ($this->validation->check($id, 'required')) {
			
			$data = $this->pengaturanWarnaModel->where('config_name' ,$id)->first();
			$data->token = csrf_hash();
			
			return $this->response->setJSON($data);	
				
		} else {
			
			throw new \CodeIgniter\Exceptions\PageNotFoundException();

		}	
		
	}	
	
	public function add()
	{

        $response = array();

        $fields['config_name'] = $this->request->getPost('configName');
        $fields['fill'] = $this->request->getPost('fill');
        $fields['dashed'] = $this->request->getPost('dashed');
        $fields['keterangan'] = $this->request->getPost('keterangan');
        $fields['add_by'] = $this->request->getPost('addBy');
        $fields['date_add'] = $this->request->getPost('dateAdd');
        $fields['edit_by'] = $this->request->getPost('editBy');
        $fields['date_edit'] = $this->request->getPost('dateEdit');


        $this->validation->setRules([
            'fill' => ['label' => 'Fill', 'rules' => 'required|max_length[20]'],
            'dashed' => ['label' => 'Dashed', 'rules' => 'permit_empty|max_length[255]'],
            'keterangan' => ['label' => 'Keterangan', 'rules' => 'permit_empty|max_length[255]'],
            'add_by' => ['label' => 'Add by', 'rules' => 'permit_empty|max_length[255]'],
            'date_add' => ['label' => 'Date add', 'rules' => 'permit_empty|valid_date'],
            'edit_by' => ['label' => 'Edit by', 'rules' => 'permit_empty|max_length[255]'],
            'date_edit' => ['label' => 'Date edit', 'rules' => 'permit_empty|valid_date'],

        ]);

        if ($this->validation->run($fields) == FALSE) {

            $response['success'] = false;
            $response['messages'] = $this->validation->listErrors();
			
        } else {

            if ($this->pengaturanWarnaModel->insert($fields)) {
												
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
		
        $fields['config_name'] = $this->request->getPost('configName');
        $fields['fill'] = $this->request->getPost('fill');
        $fields['dashed'] = $this->request->getPost('dashed');
        $fields['keterangan'] = $this->request->getPost('keterangan');
        $fields['add_by'] = $this->request->getPost('addBy');
        $fields['date_add'] = $this->request->getPost('dateAdd');
        $fields['edit_by'] = $this->request->getPost('editBy');
        $fields['date_edit'] = $this->request->getPost('dateEdit');


        $this->validation->setRules([
            'fill' => ['label' => 'Fill', 'rules' => 'required|max_length[20]'],
            'dashed' => ['label' => 'Dashed', 'rules' => 'permit_empty|max_length[255]'],
            'keterangan' => ['label' => 'Keterangan', 'rules' => 'permit_empty|max_length[255]'],
            'add_by' => ['label' => 'Add by', 'rules' => 'permit_empty|max_length[255]'],
            'date_add' => ['label' => 'Date add', 'rules' => 'permit_empty|valid_date'],
            'edit_by' => ['label' => 'Edit by', 'rules' => 'permit_empty|max_length[255]'],
            'date_edit' => ['label' => 'Date edit', 'rules' => 'permit_empty|valid_date'],

        ]);

        if ($this->validation->run($fields) == FALSE) {

            $response['success'] = false;
            $response['messages'] = $this->validation->listErrors();
			
        } else {

            if ($this->pengaturanWarnaModel->update($fields['config_name'], $fields)) {
				
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
		
		$id = $this->request->getPost('config_name');
		
		if (!$this->validation->check($id, 'required|numeric')) {

			throw new \CodeIgniter\Exceptions\PageNotFoundException();
			
		} else {	
		
			if ($this->pengaturanWarnaModel->where('config_name', $id)->delete()) {
								
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