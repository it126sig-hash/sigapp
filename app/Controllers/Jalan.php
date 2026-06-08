<?php

namespace App\Controllers;

use App\Services\JalanService;
use CodeIgniter\Exceptions\PageNotFoundException;

class Jalan extends BaseController
{
	protected JalanService $jalanService;

	public function __construct()
	{
		$this->jalanService = new JalanService();
	}

	public function index()
	{
		$data['content'] = 'master/jalan';
		$data['data'] = $this->jalanService->getIndexData();

		return view('template', $data);
	}

	public function getAll()
	{
		return $this->json([
			'data' => $this->jalanService->getAll((array) $this->request->getVar()),
		]);
	}

	public function getDatatable()
	{
		return $this->json($this->jalanService->getDataTables((array) $this->request->getVar()));
	}

	public function getOne()
	{
		$id = (int) $this->request->getPost('id_jalan');

		if ($id <= 0) {
			throw new PageNotFoundException();
		}

		$data = $this->jalanService->getOne($id);
		if (!$data) {
			throw new PageNotFoundException();
		}

		$data->token = csrf_hash();

		return $this->response->setJSON($data);
	}

	public function add()
	{
		return $this->json($this->jalanService->add($this->request));
	}

	public function edit()
	{
		return $this->json($this->jalanService->edit($this->request));
	}

	public function remove()
	{
		$id = (int) $this->request->getPost('id_jalan');

		if ($id <= 0) {
			throw new PageNotFoundException();
		}

		return $this->json($this->jalanService->remove($id));
	}

	private function json(array $payload)
	{
		$payload['token'] = $payload['token'] ?? csrf_hash();

		return $this->response->setJSON($payload);
	}
}
