<?php
// ADEL CODEIGNITER 4 CRUD GENERATOR

namespace App\Controllers;

use App\Controllers\BaseController;

use App\Models\ClusterModel;
use App\Models\ProyekModel;
use Hermawan\DataTables\DataTable;
use App\Services\HistoryService;
use App\Controllers\Notif;

class Cluster extends BaseController
{

	protected $clusterModel;
	protected $proyekModel;
	protected $validation;
	protected $db;
	protected HistoryService $historyService;
	protected Notif $notif;

	public function __construct()
	{
		$this->clusterModel = new ClusterModel();
		$this->proyekModel = new ProyekModel();
		$this->validation =  \Config\Services::validation();
		$this->db = db_connect();
		$this->historyService = new HistoryService();
		$this->notif = new Notif();
	}

	public function index()
	{
		$data['data']['proyek'] = $this->proyekModel->select('id_proyek, nama_proyek')->findAll();

		$data['content'] = 'master/cluster';
		$data['data']['controller'] = 'cluster';
		$data['data']['title'] = 'Cluster';
		return view('template', $data);
	}

	public function getDataTable()
	{
		$response = array();
		$data['token'] = csrf_hash();
		$data['data'] = array();

		$var = [];

		$var = array_merge($var, ["search" => ["value" => "", "regex"=>false]]);
		$data['test2'] = $var;		
		
		$var = $this->request->getVar();
		$id_proyek = resolve_active_proyek_id($var['id_proyek'] ?? null);
		
		$data['test'] = $var;
		// if(!$var['search'])
		// 	$var['search']['value'] = "";

		$colum = ['cluster.nama_cluster'];
		$condition = [];

		$query = $this->db->table('cluster')
			->select('id_cluster, cluster.id_proyek, nama_proyek, nama_cluster, is_active, 
                (SELECT COUNT(kavling.id_kavling) 
                 FROM kavling 
                 JOIN jalan ON jalan.id_jalan = kavling.id_jalan 
                 JOIN produksi ON produksi.id_produksi = kavling.id_produksi 
                 WHERE jalan.id_cluster = cluster.id_cluster 
                 AND produksi.progres_bangunan = 100) as jumlah_bangun,
                (SELECT COUNT(kavling.id_kavling) FROM kavling JOIN jalan ON jalan.id_jalan = kavling.id_jalan JOIN mkdt ON mkdt.id_mkdt = kavling.id_mkdt WHERE jalan.id_cluster = cluster.id_cluster AND mkdt.status_mkdt = \'Akad\' AND mkdt.is_batal = 0) as total_akad,
                (SELECT COUNT(kavling.id_kavling) FROM kavling JOIN jalan ON jalan.id_jalan = kavling.id_jalan JOIN mkdt ON mkdt.id_mkdt = kavling.id_mkdt WHERE jalan.id_cluster = cluster.id_cluster AND mkdt.status_mkdt = \'Booking\' AND mkdt.is_batal = 0) as total_booking,
                (SELECT COUNT(kavling.id_kavling) FROM kavling JOIN jalan ON jalan.id_jalan = kavling.id_jalan LEFT JOIN mkdt ON mkdt.id_mkdt = kavling.id_mkdt WHERE jalan.id_cluster = cluster.id_cluster AND (kavling.id_mkdt IS NULL OR mkdt.status_mkdt = \'Batal\' OR mkdt.is_batal = 1)) as total_belum_terjual')
			->join('proyek', 'proyek.id_proyek = cluster.id_proyek');

		if ($id_proyek)
			$condition = array_merge($condition, ["cluster.id_proyek" => $id_proyek]);

		$result = $this->if_where($var, $colum, $condition, $query);

		$result
			->offset($var['start'])
			->limit($var['length']);

		$x = $result->get();
		$data['draw'] = $var['draw'];

		//count filtered
		$countfiltered = $this->db->table('cluster')
			->select('id_cluster, cluster.id_proyek, nama_proyek, nama_cluster, is_active')
			->join('proyek', 'proyek.id_proyek = cluster.id_proyek');

		$countfiltered = $this->if_where($var, $colum, $condition, $countfiltered);
		$data['recordsFiltered'] = count($countfiltered->get()->getResult());

		$condition = [];
		$countTotal =  $this->db->table('cluster')
			->select('count(cluster.id_cluster) as count')
			->join('proyek', 'proyek.id_proyek = cluster.id_proyek')
			->where($condition);

		$data['recordsTotal'] =  $countTotal->get()->getResult()[0]->count;
		$no = $var['start'];
        foreach ($x->getResult() as $key => $value) {
			$ops = '<div class="btn-group">';
			$ops .= '	<button type="button" class="btn btn-outline-primary waves-effect btn-sm" onclick="edit(' . $value->id_cluster . ')"><i class="fa fa-edit"></i></button>';
			$ops .= '	<button type="button" class="btn btn-outline-danger waves-effect btn-sm" onclick="remove(' . $value->id_cluster . ')"><i class="fa fa-trash"></i></button>';
			$ops .= '</div>';

			$data['data'][$key] = array(
				$value->id_cluster,
				$value->id_proyek,
				$value->nama_proyek,
				$value->nama_cluster,
				$value->jumlah_bangun,
                '<span class="badge badge-light-success badge-pill">' . $value->total_akad . '</span>',
                '<span class="badge badge-light-warning badge-pill">' . $value->total_booking . '</span>',
                '<span class="badge badge-light-secondary badge-pill">' . $value->total_belum_terjual . '</span>',
				$this->is_active($value->is_active, "Aktif", "Tidak"),

				$ops,
			);
		}
        return $this->response->setJSON($data);
	}

	public function getAll()
	{
		$response = array();
		$data['token'] = csrf_hash();
		$data['data'] = array();

		$id_proyek = resolve_active_proyek_id($this->request->getVar('id_proyek'));
		$search = $this->request->getVar('search');
		if(!$search)
			$search = "";

		$result = $this->clusterModel
			->select('id_cluster, cluster.id_proyek, nama_proyek, nama_cluster, is_active')
			->join('proyek', 'proyek.id_proyek = cluster.id_proyek');

		if($id_proyek) {
			$result
				->like('cluster.nama_cluster', $search)
				->where('cluster.id_proyek', $id_proyek);
		}

		foreach ($result->findAll() as $key => $value) {

			$ops = '<div class="btn-group">';
			$ops .= '	<button type="button" class="btn btn-outline-primary waves-effect btn-sm" onclick="edit(' . $value->id_cluster . ')"><i class="fa fa-edit"></i></button>';
			$ops .= '	<button type="button" class="btn btn-outline-danger waves-effect btn-sm" onclick="remove(' . $value->id_cluster . ')"><i class="fa fa-trash"></i></button>';
			$ops .= '</div>';

			$data['data'][$key] = array(
				$value->id_cluster,
				$value->id_proyek,
				$value->nama_proyek,
				$value->nama_cluster,
				$value->is_active,

				$ops,
			);
		}
		return $this->response->setJSON($data);
	}

	function getOne()
	{
		$response = array();

		$id = $this->request->getPost('id_cluster');

		if ($this->validation->check($id, 'required|numeric')) {

			$data = $this->clusterModel->where('id_cluster', $id)->first();
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

		$fields['id_cluster'] = $this->request->getPost('idCluster');
		$fields['id_proyek'] = $this->request->getPost('idProyek');
		$fields['nama_cluster'] = $this->request->getPost('namaCluster');
		$fields['is_active'] = $this->request->getPost('isActive');


		$this->validation->setRules([
			'id_proyek' => ['label' => 'Id proyek', 'rules' => 'permit_empty|max_length[255]'],
			'nama_cluster' => ['label' => 'Nama cluster', 'rules' => 'permit_empty|max_length[255]'],
			'is_active' => ['label' => 'Is active', 'rules' => 'permit_empty|max_length[1]'],

		]);

		if ($this->validation->run($fields) == FALSE) {

			$response['success'] = false;
			$response['messages'] = $this->validation->listErrors();
		} else {

			if ($this->clusterModel->insert($fields)) {
				$insertId = (int) $this->clusterModel->getInsertID();
				$this->historyService->log('master_cluster', [
					'reference_type' => 'cluster',
					'reference_id' => $insertId,
					'action' => 'insert',
					'new_data' => $fields
				]);
				$this->notif->tambah_notif("6", "Menambahkan Master Cluster: " . ($fields['nama_cluster'] ?? ''), user_id(), null, null, \App\Enums\NotificationEvent::MASTER_CLUSTER, $fields['id_proyek']);

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

		$fields['id_cluster'] = $this->request->getPost('idCluster');
		$fields['id_proyek'] = $this->request->getPost('idProyek');
		$fields['nama_cluster'] = $this->request->getPost('namaCluster');
		$fields['is_active'] = $this->request->getPost('isActive');


		$this->validation->setRules([
			'id_proyek' => ['label' => 'Id proyek', 'rules' => 'permit_empty|max_length[255]'],
			'nama_cluster' => ['label' => 'Nama cluster', 'rules' => 'permit_empty|max_length[255]'],
			'is_active' => ['label' => 'Is active', 'rules' => 'permit_empty|max_length[1]'],

		]);

		if ($this->validation->run($fields) == FALSE) {

			$response['success'] = false;
			$response['messages'] = $this->validation->listErrors();
		} else {
			$oldData = $this->clusterModel->find($fields['id_cluster']);

			if ($this->clusterModel->update($fields['id_cluster'], $fields)) {

				$this->historyService->log('master_cluster', [
					'reference_type' => 'cluster',
					'reference_id' => $fields['id_cluster'],
					'action' => 'update',
					'old_data' => $oldData,
					'new_data' => $fields
				]);
				$this->notif->tambah_notif("6", "Mengubah Master Cluster: " . ($fields['nama_cluster'] ?? ''), user_id(), null, null, \App\Enums\NotificationEvent::MASTER_CLUSTER, $fields['id_proyek']);

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

		$id = $this->request->getPost('id_cluster');

		if (!$this->validation->check($id, 'required|numeric')) {

			throw new \CodeIgniter\Exceptions\PageNotFoundException();
		} else {

            $jalanCount = $this->db->table('jalan')->where('id_cluster', $id)->countAllResults();
            if ($jalanCount > 0) {
                $kavlingCount = $this->db->table('kavling')
                                        ->join('jalan', 'jalan.id_jalan = kavling.id_jalan')
                                        ->where('jalan.id_cluster', $id)
                                        ->countAllResults();

                $response['success'] = false;
                $response['messages'] = "Tidak dapat menghapus cluster karena sudah memiliki $jalanCount jalan dan $kavlingCount kavling terdaftar.";
                return $this->response->setJSON($response);
            }

			if ($this->clusterModel->where('id_cluster', $id)->delete()) {

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
        $r = '<span class="badge badge-pill badge-light-danger" text-capitalized="">' . $textf . '</span>';
        if ($id == "1") $r = '<span class="badge badge-pill badge-light-success" text-capitalized="">' . $texts . '</span>';
        return $r;
    }
}
