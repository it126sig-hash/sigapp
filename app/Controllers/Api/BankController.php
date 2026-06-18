<?php

namespace App\Controllers\Api;

use App\Models\ListBankModel;
use App\Services\BankService;
use CodeIgniter\HTTP\ResponseInterface;

class BankController extends BaseApiController
{
    protected BankService $bankService;
    protected ListBankModel $listBankModel;

    public function __construct()
    {
        $this->bankService = new BankService();
        $this->listBankModel = new ListBankModel();
    }

    public function list(): ResponseInterface
    {
        $banks = $this->listBankModel->getAllBanks();

        $data = [];
        $no = 1;

        foreach ($banks as $key => $value) {
            $ops = '<div class="btn-group">';
            $ops .= '    <button type="button" class="btn btn-sm btn-info" onclick="edit(\''. $value['id'] .'\')"><i class="fa fa-edit"></i></button>';
            $ops .= '    <button type="button" class="btn btn-sm btn-danger" onclick="remove(\''. $value['id'] .'\')"><i class="fa fa-trash"></i></button>';
            $ops .= '</div>';

            $data[] = [
                $no,
                $value['bank'],
                $value['keterangan'],
                $value['exp_days'] . " Hari",
                $ops,
            ];
            $no++;
        }

        return $this->respond([
            'token' => csrf_hash(),
            'data' => $data
        ]);
    }
    public function ambil(): ResponseInterface
    {
        $banks = $this->listBankModel->getAllBanks();

        $data = [];

        foreach ($banks as $key => $value) {
            $data[] = [
                'id' => $value['id'],
                'bank' => $value['bank'],
                'keterangan' => $value['keterangan'],
                'exp_days' => $value['exp_days']
            ];
        }

        return $this->respond([
            'token' => csrf_hash(),
            'data' => $data
        ]);
    }

    public function ambilSatu(): ResponseInterface
    {
        $id = $this->request->getPost('id');
        if (!$id) {
            return $this->error('ID bank harus diisi', 400);
        }

        $bank = $this->listBankModel->getBankById($id);

        if (!$bank) {
            return $this->error('Bank tidak ditemukan', 404);
        }

        $bank['token'] = csrf_hash();
        return $this->respond($bank);
    }

    public function simpan(): ResponseInterface
    {
        $rules = [
            'bank' => 'required|max_length[255]',
            'keterangan' => 'permit_empty|max_length[255]',
            'exp_days' => 'permit_empty|integer'
        ];

        if (!$this->validate($rules)) {
            return $this->error('Validasi gagal', 400, $this->validator->getErrors());
        }

        $id = $this->request->getPost('id');
        $postData = $this->request->getPost();

        try {
            $this->bankService->simpan($postData, $id ? (int)$id : null);
            return $this->respond([
                'success' => true,
                'messages' => 'Bank berhasil ' . ($id ? 'diperbarui' : 'ditambahkan'),
                'token' => csrf_hash()
            ]);
        } catch (\Exception $e) {
            return $this->error('Gagal menyimpan bank', 500);
        }
    }

    public function hapus(): ResponseInterface
    {
        $id = $this->request->getPost('id');
        if (!$id) {
            return $this->error('ID bank harus diisi', 400);
        }

        try {
            $this->bankService->hapus((int)$id);
            return $this->respond([
                'success' => true,
                'messages' => 'Bank berhasil dihapus',
                'token' => csrf_hash()
            ]);
        } catch (\Exception $e) {
            return $this->error('Gagal menghapus bank', 500);
        }
    }
}
