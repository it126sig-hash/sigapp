<?php
// ADEL CODEIGNITER 4 CRUD GENERATOR

namespace App\Controllers;

use App\Services\ProyekService;
use CodeIgniter\Exceptions\PageNotFoundException;

class Proyek extends BaseController
{
	protected ProyekService $proyekService;

	public function __construct()
	{
		$this->proyekService = new ProyekService();
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

		return $this->json([
			'data' => $this->proyekService->getAll((array) $this->request->getVar()),
		]);
	}

	public function getOne()
	{
		$id = (int) $this->request->getPost('id_proyek');

		if ($id <= 0) {
			throw new PageNotFoundException();
		}

		$data = $this->proyekService->getOne($id);
		if (!$data) {
			throw new PageNotFoundException();
		}

		$data->token = csrf_hash();

		return $this->response->setJSON($data);
	}

	public function add()
	{
		return $this->json($this->proyekService->add($this->request));
	}

	public function edit()
	{
		return $this->json($this->proyekService->edit($this->request));
	}

	function getSiteplanList($id = null)
	{
		$idProyek = (int) (($id) ? $id : $this->request->getVar('idProyek'));
		return $this->proyekService->getSiteplanList($idProyek);
	}

	public function remove()
	{
		return $this->json($this->proyekService->remove((int) $this->request->getPost('id_proyek')));
	}

	private function json(array $payload)
	{
		$payload['token'] = $payload['token'] ?? csrf_hash();

		return $this->response->setJSON($payload);
	}
}
