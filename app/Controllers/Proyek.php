<?php
// ADEL CODEIGNITER 4 CRUD GENERATOR

namespace App\Controllers;

use App\Services\ActiveProyekService;
use App\Services\ProyekService;
use CodeIgniter\Exceptions\PageNotFoundException;

class Proyek extends BaseController
{
	protected ProyekService $proyekService;
	protected ActiveProyekService $activeProyekService;

	public function __construct()
	{
		$this->proyekService = new ProyekService();
		$this->activeProyekService = new ActiveProyekService();
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

	public function getAccessible()
	{
		if (!$this->request->isAJAX()) {
			return $this->response->setJSON([]);
		}

		$list = $this->activeProyekService->getAccessibleList((int) user_id());
		$activeId = $this->activeProyekService->getActiveId();

		return $this->json([
			'success'   => true,
			'active_id' => $activeId,
			'data'      => array_map(static function ($row) {
				return [
					'id_proyek'       => (int) $row->id_proyek,
					'nama_proyek'     => $row->nama_proyek,
					'logo_access_url' => $row->logo_access_url ?? '',
				];
			}, $list),
		]);
	}

	public function setActive()
	{
		if (!$this->request->isAJAX()) {
			return $this->response->setJSON(['success' => false, 'message' => 'Invalid request.']);
		}

		$idProyek = (int) $this->request->getPost('id_proyek');

		return $this->json($this->activeProyekService->setActive($idProyek));
	}

	private function json(array $payload)
	{
		$payload['token'] = $payload['token'] ?? csrf_hash();

		return $this->response->setJSON($payload);
	}
}
