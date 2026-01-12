<?php
// ADEL CODEIGNITER 4 CRUD GENERATOR

namespace App\Controllers;

use App\Controllers\BaseController;

use App\Models\PengaturanWarnaModel;
use App\Models\ProyekModel;

class AksesProyek extends BaseController
{

	protected $db;
	protected $validation;

	public function __construct()
	{
		$this->db = db_connect();
		$this->validation =  \Config\Services::validation();
	}

	public function index()
	{
		$data = [
			'controller'    	=> 'AksesProyek',
			'title'     		=> 'Akses Proyek'
		];

		$data['content'] = 'master/aksesproyek';
		return view('template', $data);
		// return view('', $data);			
	}

	public function getAll()
	{
		$response = array();
		$data['token'] = csrf_hash();

		$data['data'] = array();

		$sql = "
			SELECT 
				proyek.id_proyek,
				proyek.nama_proyek,
				proyek.id_users,
				GROUP_CONCAT(users.username SEPARATOR ',') AS kumpulan_username
			FROM proyek
			LEFT JOIN users
			ON FIND_IN_SET(users.id, proyek.id_users)
			GROUP BY proyek.id_proyek, proyek.nama_proyek, proyek.id_users
		";

		$query = $this->db->query($sql);
		$result = $query->getResult(); // Hasil dalam bentuk array

		foreach ($result as $key => $value) {

			$ops = '<div class="btn-group">';
			$ops .= '	<button type="button" class="btn btn-sm btn-info" onclick="edit(\'' . $value->id_proyek . '\')"><i class="fa fa-edit"></i></button>';
			$ops .= '</div>';

			$data['data'][$key] = array(
				$value->nama_proyek,
				$value->kumpulan_username,
				$ops,
			);
		}

		return $this->response->setJSON($data);
	}

	public function getOne()
	{
		$id = $this->request->getPost('id_proyek');
		
		$sql = "
			SELECT 
				proyek.id_proyek,
				proyek.nama_proyek,
				proyek.id_users,
				GROUP_CONCAT(users.username SEPARATOR ',') AS kumpulan_username
			FROM proyek
			LEFT JOIN users
			ON FIND_IN_SET(users.id, proyek.id_users)
			where id_proyek = ".$id."
			GROUP BY proyek.id_proyek, proyek.nama_proyek, proyek.id_users
		";

		$query = $this->db->query($sql);
		$data = $query->getResult()[0]; // Hasil dalam bentuk array
		$data->token = csrf_hash();

		return $this->response->setJSON($data);
	}
	function getAkses(){
		$id = $this->request->getPost('search');
		
		$sql = "
			SELECT 
				id,
				username
			FROM 
				users
			where 
				username like '%".$id."%'
		";

		$query = $this->db->query($sql);		
		$data['data'] = $query->getResult(); // Hasil dalam bentuk array
		$data['token'] = csrf_hash();

		return $this->response->setJSON($data);
	}
	public function edit()
	{
		$response = array();

		$fields['id_proyek'] = $this->request->getPost('id_proyek');
		$id_users = $this->request->getPost('id_users');

		$semua = '';
		foreach ($id_users as $user_id) {
			$semua .= $user_id.",";
		}

		$this->validation->setRules([
			'id_proyek' => ['label' => 'Tidak ada proyek yang dipilih', 'rules' => 'required|max_length[20]'],
			'id_users' => ['label' => '', 'rules' => 'permit_empty|max_length[20]'],
		]);

		if ($this->validation->run($fields) == FALSE) {
			$response['success'] = false;
			$response['messages'] = $this->validation->listErrors();
		} else {

			if ($this->db->table('proyek')
				->update(['id_users' => $semua], ['id_proyek'=>$fields['id_proyek']]))
			 {

				$response['success'] = true;
				$response['messages'] = 'Berhasil diperbaharui';
			} else {

				$response['success'] = false;
				$response['messages'] = 'Terjadi kesalahan!';
			}
		}

		return $this->response->setJSON($response);
	}
}
