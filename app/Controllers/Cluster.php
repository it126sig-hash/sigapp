<?php

namespace App\Controllers;

use App\Models\ProyekModel;
use App\Services\ClusterService;
use CodeIgniter\Exceptions\PageNotFoundException;

class Cluster extends BaseController
{
	protected ProyekModel $proyekModel;
	protected ClusterService $clusterService;

	public function __construct()
	{
		$this->proyekModel    = new ProyekModel();
		$this->clusterService = new ClusterService();
	}

	public function index()
	{
		$data['data']['proyek']     = $this->proyekModel->select('id_proyek, nama_proyek')->findAll();
		$data['content']            = 'master/cluster';
		$data['data']['controller'] = 'cluster';
		$data['data']['title']      = 'Cluster';

		return view('template', $data);
	}

	public function getDataTable()
	{
		$result = $this->clusterService->getDataTables((array) $this->request->getVar());
		$result['token'] = csrf_hash();

		return $this->response->setJSON($result);
	}

	public function getAll()
	{
		$result = $this->clusterService->getAll((array) $this->request->getVar());

		return $this->response->setJSON([
			'data'  => $result,
			'token' => csrf_hash(),
		]);
	}

	public function getOne()
	{
		$id = (int) $this->request->getPost('id_cluster');

		if ($id <= 0) {
			throw new PageNotFoundException();
		}

		$data = $this->clusterService->getOne($id);
		if (!$data) {
			throw new PageNotFoundException();
		}

		$data->token = csrf_hash();

		return $this->response->setJSON($data);
	}

	public function add()
	{
		$result = $this->clusterService->add((array) $this->request->getPost());
		$result['token'] = csrf_hash();

		return $this->response->setJSON($result);
	}

	public function edit()
	{
		$result = $this->clusterService->edit((array) $this->request->getPost());
		$result['token'] = csrf_hash();

		return $this->response->setJSON($result);
	}

	public function remove()
	{
		$id = (int) $this->request->getPost('id_cluster');
		if ($id <= 0) {
			throw new PageNotFoundException();
		}

		$result = $this->clusterService->remove($id);
		$result['token'] = csrf_hash();

		return $this->response->setJSON($result);
	}

	public function if_where($var, $column, $condition, $query)
	{
		$x = 0;
		foreach ($column as $i) {
			if ($x === 0) {
				$query->like($i, $var['search']['value'] ?? '');
			} else {
				$query->orLike($i, $var['search']['value'] ?? '');
			}
			$query->where($condition);
			$x++;
		}
		return $query;
	}

	public function is_active($id, $texts, $textf)
	{
		return $this->clusterService->isActiveBadge($id, $texts, $textf);
	}
}
