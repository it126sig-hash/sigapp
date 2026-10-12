<?php

namespace App\Controllers;

use App\Services\ProyekService;
use CodeIgniter\Exceptions\PageNotFoundException;

class AksesProyek extends BaseController
{
	protected ProyekService $proyekService;

	public function __construct()
	{
		$this->proyekService = new ProyekService();
	}

	public function index()
	{
		$data = [
			'controller' => 'AksesProyek',
			'title'      => 'Akses Proyek',
			'content'    => 'master/aksesproyek',
		];

		return view('template', $data);
	}

	public function getAll()
	{
		$data = $this->proyekService->getAksesList();

		return $this->response->setJSON([
			'data'  => $data,
			'token' => csrf_hash(),
		]);
	}

	public function getOne()
	{
		$id = (int) $this->request->getPost('id_proyek');

		if ($id <= 0) {
			throw new PageNotFoundException();
		}

		$data = $this->proyekService->getAksesOne($id);
		if (!$data) {
			throw new PageNotFoundException();
		}

		$data->token = csrf_hash();

		return $this->response->setJSON($data);
	}

	public function getAkses()
	{
		$search = (string) ($this->request->getPost('search') ?? '');
		$data   = $this->proyekService->searchAksesUsers($search);

		return $this->response->setJSON([
			'data'  => $data,
			'token' => csrf_hash(),
		]);
	}

	public function edit()
	{
		$result = $this->proyekService->updateAkses((array) $this->request->getPost());
		$result['token'] = csrf_hash();

		return $this->response->setJSON($result);
	}
}
