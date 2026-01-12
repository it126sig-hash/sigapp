<?php

namespace App\Controllers;

use App\Models\ListBankModel;
use CodeIgniter\HTTP\ResponseInterface;

class ListBank extends BaseController
{
    protected $listBankModel;

    public function __construct()
    {
        $this->listBankModel = new ListBankModel();
    }

    // GET all banks
    public function index()
    {
        $data = [
            'title' => 'List Bank',
            'banks' => $this->listBankModel->getAllBanks()
        ];


        $data['content'] = 'master/listbank';
        return view('template', $data);
        // return view('', $data);
    }

    // GET bank by ID
    public function show($id = null)
    {
        if (!$id) {
            return $this->failValidationError('ID bank harus diisi');
        }

        $bank = $this->listBankModel->getBankById($id);

        if (!$bank) {
            return $this->failNotFound('Bank tidak ditemukan');
        }

        $data = [
            'title' => 'Detail Bank',
            'bank' => $bank
        ];

        return view('list_bank/show', $data);
    }

    // GET form create
    public function create()
    {
        $data = [
            'title' => 'Tambah Bank Baru'
        ];

        return view('list_bank/create', $data);
    }

    // POST create new bank
    public function store()
    {
        $rules = [
            'bank' => 'required|max_length[255]',
            'keterangan' => 'permit_empty|max_length[255]',
            'exp_days' => 'permit_empty|integer'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'bank' => $this->request->getPost('bank'),
            'keterangan' => $this->request->getPost('keterangan'),
            'exp_days' => $this->request->getPost('exp_days') ?: 0
        ];

        if ($this->listBankModel->insertBank($data)) {
            return redirect()->to('/listbank')->with('success', 'Bank berhasil ditambahkan');
        } else {
            return redirect()->back()->withInput()->with('error', 'Gagal menambahkan bank');
        }
    }

    // GET form edit
    public function edit($id = null)
    {
        if (!$id) {
            return redirect()->to('/listbank')->with('error', 'ID bank harus diisi');
        }

        $bank = $this->listBankModel->getBankById($id);

        if (!$bank) {
            return redirect()->to('/listbank')->with('error', 'Bank tidak ditemukan');
        }

        $data = [
            'title' => 'Edit Bank',
            'bank' => $bank
        ];

        return view('list_bank/edit', $data);
    }

    // PUT/POST update bank
    public function update($id = null)
    {
        if (!$id) {
            return redirect()->to('/listbank')->with('error', 'ID bank harus diisi');
        }

        $bank = $this->listBankModel->getBankById($id);

        if (!$bank) {
            return redirect()->to('/listbank')->with('error', 'Bank tidak ditemukan');
        }

        $rules = [
            'bank' => 'required|max_length[255]',
            'keterangan' => 'permit_empty|max_length[255]',
            'exp_days' => 'permit_empty|integer'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'bank' => $this->request->getPost('bank'),
            'keterangan' => $this->request->getPost('keterangan'),
            'exp_days' => $this->request->getPost('exp_days') ?: 0
        ];

        if ($this->listBankModel->updateBank($id, $data)) {
            return redirect()->to('/listbank')->with('success', 'Bank berhasil diupdate');
        } else {
            return redirect()->back()->withInput()->with('error', 'Gagal mengupdate bank');
        }
    }

    // DELETE bank
    public function delete($id = null)
    {
        if (!$id) {
            return $this->respond(['message' => 'ID bank harus diisi'], 400);
        }

        $bank = $this->listBankModel->getBankById($id);

        if (!$bank) {
            return $this->respond(['message' => 'Bank tidak ditemukan'], 404);
        }

        if ($this->listBankModel->deleteBank($id)) {
            return redirect()->to('/listbank')->with('success', 'Bank berhasil dihapus');
        } else {
            return redirect()->to('/listbank')->with('error', 'Gagal menghapus bank');
        }
    }

    // API Methods
    // GET API - Get all banks
    public function apiIndex()
    {
        $search = $this->request->getGet('search');
        $banks = $this->listBankModel->getAllBanks($search);
        return $this->response->setJSON($banks);
    }
    function getDataTables(){
   
 		$response = array();	
		$data['token'] = csrf_hash();	
		
	    $data['data'] = array();
 
        // $search = $this->request->getGet('search');
        $banks = $this->listBankModel->getAllBanks();
        // var_dump($banks[0]['id']); die();

        $no = 1;
		
		foreach ($banks as $key => $value) {
							
			$ops = '<div class="btn-group">';
			$ops .= '	<button type="button" class="btn btn-sm btn-info" onclick="edit(\''. $value['id'] .'\')"><i class="fa fa-edit"></i></button>';
			$ops .= '	<button type="button" class="btn btn-sm btn-danger" onclick="remove(\''. $value['id'] .'\')"><i class="fa fa-trash"></i></button>';
			$ops .= '</div>';
			
			$data['data'][$key] = array(
                $no, 
				$value['bank'],
				$value['keterangan'],
				$value['exp_days'] . " Hari",

				$ops,
			);
            $no++;
		} 

		return $this->response->setJSON($data);		
    }

    // GET API - Get bank by ID
    public function apiShow($id = null)
    {
        if (!$id) {
            return $this->failValidationError('ID bank harus diisi');
        }

        $bank = $this->listBankModel->getBankById($id);

        if (!$bank) {
            return $this->failNotFound('Bank tidak ditemukan');
        }

        return $this->respond($bank);
    }

    // POST API - Create new bank
    public function apiStore()
    {
        $rules = [
            'bank' => 'required|max_length[255]',
            'keterangan' => 'permit_empty|max_length[255]',
            'exp_days' => 'permit_empty|integer'
        ];

        if (!$this->validate($rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $data = [
            'bank' => $this->request->getJSON()->bank,
            'keterangan' => $this->request->getJSON()->keterangan ?? null,
            'exp_days' => $this->request->getJSON()->exp_days ?? 0
        ];

        $insertId = $this->listBankModel->insertBank($data);

        if ($insertId) {
            $newBank = $this->listBankModel->getBankById($insertId);
            return $this->respondCreated(['message' => 'Bank berhasil ditambahkan', 'data' => $newBank]);
        } else {
            return $this->failServerError('Gagal menambahkan bank');
        }
    }

    // PUT API - Update bank
    public function apiUpdate($id = null)
    {
        if (!$id) {
            return $this->failValidationError('ID bank harus diisi');
        }

        $bank = $this->listBankModel->getBankById($id);

        if (!$bank) {
            return $this->failNotFound('Bank tidak ditemukan');
        }

        $rules = [
            'bank' => 'required|max_length[255]',
            'keterangan' => 'permit_empty|max_length[255]',
            'exp_days' => 'permit_empty|integer'
        ];

        if (!$this->validate($rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $data = [
            'bank' => $this->request->getJSON()->bank,
            'keterangan' => $this->request->getJSON()->keterangan ?? null,
            'exp_days' => $this->request->getJSON()->exp_days ?? 0
        ];

        if ($this->listBankModel->updateBank($id, $data)) {
            $updatedBank = $this->listBankModel->getBankById($id);
            return $this->respond(['message' => 'Bank berhasil diupdate', 'data' => $updatedBank]);
        } else {
            return $this->failServerError('Gagal mengupdate bank');
        }
    }

    // DELETE API - Delete bank
    public function apiDelete($id = null)
    {
        if (!$id) {
            return $this->failValidationError('ID bank harus diisi');
        }

        $bank = $this->listBankModel->getBankById($id);

        if (!$bank) {
            return $this->failNotFound('Bank tidak ditemukan');
        }

        if ($this->listBankModel->deleteBank($id)) {
            return $this->respondDeleted(['message' => 'Bank berhasil dihapus']);
        } else {
            return $this->failServerError('Gagal menghapus bank');
        }
    }

    // Search banks
    public function search()
    {
        $keyword = $this->request->getGet('keyword');

        if (!$keyword) {
            return redirect()->to('/listbank');
        }

        $data = [
            'title' => 'Pencarian Bank',
            'banks' => $this->listBankModel->searchBanks($keyword),
            'keyword' => $keyword
        ];

        return view('list_bank/index', $data);
    }
}
