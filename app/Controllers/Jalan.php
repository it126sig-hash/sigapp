<?php
// ADEL CODEIGNITER 4 CRUD GENERATOR

namespace App\Controllers;

use App\Controllers\BaseController;

use App\Models\JalanModel;
use App\Models\ClusterModel;
use App\Models\ProyekModel;

class Jalan extends BaseController
{

	protected $proyekModel;
	protected $clusterModel;
	protected $jalanModel;
	protected $validation;
	protected $db;

	public function __construct()
	{
		$this->jalanModel = new JalanModel();
		$this->proyekModel = new ProyekModel();
		$this->clusterModel = new ClusterModel();
		$this->validation =  \Config\Services::validation();
		$this->db = db_connect();
	}

	public function index()
	{

		// $data = [
		//         'controller'    	=> 'jalan',
		//         'title'     		=> 'Jalan'				
		// 	];

		$data['data']['proyek'] = $this->proyekModel->select('id_proyek, nama_proyek')->findAll();
		$data['data']['cluster'] = $this->clusterModel
			->select('nama_proyek, id_cluster, nama_cluster')
			->join('proyek', 'proyek.id_proyek = cluster.id_proyek')
			->orderBy('nama_proyek', 'asc')
			->findAll();


		$data['content'] = 'master/jalan';

		$data['data']['controller'] = 'jalan';
		$data['data']['title'] = 'Jalan';

		return view('template', $data);
	}
	public function getAll()
	{
 		$response = array();		
		
	    $data['data'] = array();

		$data['token'] = csrf_hash();

		$id_proyek = $this->request->getVar('id_proyek');
		$id_cluster = $this->request->getVar('id_cluster');
		$search = $this->request->getVar('search');
		if(!$search)
			$search = "";
 
		$result = $this->jalanModel
					->select('id_jalan, jalan.id_cluster, nama_cluster, nama_proyek, nama_jalan')
					->join('cluster', 'cluster.id_cluster = jalan.id_cluster')
					->join('proyek', 'proyek.id_proyek = cluster.id_proyek');

		if($id_cluster) {
			$result
				->like('jalan.nama_jalan', $search)
				->where('cluster.id_cluster', $id_cluster)
				->where('cluster.id_proyek', $id_proyek);
		}
		
		foreach ($result->findAll() as $key => $value) {
							
			$ops = '<div class="btn-group">';
			$ops .= '	<button type="button" class="btn btn-sm btn-info" onclick="edit('. $value->id_jalan .')"><i class="fa fa-edit"></i></button>';
			$ops .= '	<button type="button" class="btn btn-sm btn-danger" onclick="remove('. $value->id_jalan .')"><i class="fa fa-trash"></i></button>';
			$ops .= '</div>';
			
			$data['data'][$key] = array(
				$value->id_jalan,
				$value->id_cluster,
				"(".$value->nama_proyek.")".$value->nama_cluster,
				$value->nama_jalan,

				$ops,
			);
		} 

		return $this->response->setJSON($data);		
	}
	public function getDatatable()
	{
		$response = array();
		$data['data'] = array();
		$data['token'] = csrf_hash();

		$var = $this->request->getVar();

		$colum = ['jalan.nama_jalan'];
		$condition = [];
		//get mkdt query 
		$query = $this->db->table('jalan')
			->select('id_jalan, jalan.id_cluster, nama_cluster, nama_proyek, nama_jalan')
			->join('cluster', 'cluster.id_cluster = jalan.id_cluster')
			->join('proyek', 'proyek.id_proyek = cluster.id_proyek');

		if ($var['id_cluster'])
			$condition = array_merge($condition, ["cluster.id_cluster" => $var['id_cluster']]);
		elseif ($var['id_proyek'])
			$condition = array_merge($condition, ["proyek.id_proyek" => $var['id_proyek']]);

		$result = $this->if_where($var, $colum, $condition, $query);

		$result
			->offset($var['start'])
			->limit($var['length']);

		$x = $result->get();

		$data['draw'] = $var['draw'];

		//count filtered
		$countfiltered = $this->db->table('jalan')
			->select('id_jalan, jalan.id_cluster, nama_cluster, nama_proyek, nama_jalan')
			->join('cluster', 'cluster.id_cluster = jalan.id_cluster')
			->join('proyek', 'proyek.id_proyek = cluster.id_proyek');
		$countfiltered = $this->if_where($var, $colum, $condition, $countfiltered);

		$data['recordsFiltered'] = count($countfiltered->get()->getResult());

		$condition = [];

		$countTotal =  $this->db->table('jalan')
			->select('count(id_jalan) as count')
			->join('cluster', 'cluster.id_cluster = jalan.id_cluster')
			->join('proyek', 'proyek.id_proyek = cluster.id_proyek')
			->where($condition);

		$data['recordsTotal'] =  $countTotal->get()->getResult()[0]->count;
		//looping data untuk datatable
        $no = $var['start'];
        foreach ($x->getResult() as $key => $value) {
            $no++;

			$ops = '<div class="btn-group">';
			$ops .= '	<button type="button" class="btn btn-sm btn-info" onclick="edit(' . $value->id_jalan . ')"><i class="fa fa-edit"></i></button>';
			$ops .= '	<button type="button" class="btn btn-sm btn-danger" onclick="remove(' . $value->id_jalan . ')"><i class="fa fa-trash"></i></button>';
			$ops .= '</div>';
            
            $jt = "";

            $data['data'][$key] = array(

                $no,
				$value->id_jalan,
				"(" . $value->nama_proyek . ")" . $value->nama_cluster,
				$value->nama_jalan,

				$ops,
            );	
        }
		return $this->response->setJSON($data);
	}

	public function getOne()
	{
		$id = $this->request->getPost('id_jalan');

		if ($this->validation->check($id, 'required|numeric')) {

			$data = $this->jalanModel->where('id_jalan', $id)->first();

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

		$fields['id_jalan'] = $this->request->getPost('idJalan');
		$fields['id_cluster'] = $this->request->getPost('idCluster');
		$fields['nama_jalan'] = $this->request->getPost('namaJalan');


		$this->validation->setRules([
			'id_cluster' => ['label' => 'Id cluster', 'rules' => 'permit_empty|max_length[255]'],
			'nama_jalan' => ['label' => 'Nama jalan', 'rules' => 'permit_empty|max_length[255]'],

		]);

		if ($this->validation->run($fields) == FALSE) {

			$response['success'] = false;
			$response['messages'] = $this->validation->listErrors();
		} else {

			if ($this->jalanModel->insert($fields)) {

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

		$fields['id_jalan'] = $this->request->getPost('idJalan');
		$fields['id_cluster'] = $this->request->getPost('idCluster');
		$fields['nama_jalan'] = $this->request->getPost('namaJalan');

		$response['token'] = csrf_hash();

		$this->validation->setRules([
			'id_cluster' => ['label' => 'Id cluster', 'rules' => 'permit_empty|max_length[255]'],
			'nama_jalan' => ['label' => 'Nama jalan', 'rules' => 'permit_empty|max_length[255]'],

		]);

		if ($this->validation->run($fields) == FALSE) {

			$response['success'] = false;
			$response['messages'] = $this->validation->listErrors();
		} else {

			if ($this->jalanModel->update($fields['id_jalan'], $fields)) {

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

		$id = $this->request->getPost('id_jalan');

		if (!$this->validation->check($id, 'required|numeric')) {

			throw new \CodeIgniter\Exceptions\PageNotFoundException();
		} else {

			if ($this->jalanModel->where('id_jalan', $id)->delete()) {

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
	function format_tgl($tgl)
	{
		if ($tgl == "" || $tgl == "0000-00-00" || $tgl == null) return "-";
		return  date_format(date_create($tgl), "d-M-Y");
	}
	function is_active($id, $texts, $textf)
	{
		$r = '<span class="badge badge-pill badge-light-danger" text-capitalized="">' . $textf . '</span>';
		if ($id == "1") $r = '<span class="badge badge-pill badge-light-success" text-capitalized="">' . $texts . '</span>';
		return $r;
	}
}
