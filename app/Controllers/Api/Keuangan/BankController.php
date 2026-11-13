<?php

namespace App\Controllers\Api\Keuangan;

use App\Controllers\Api\BaseApiController;
use App\Services\Keuangan\BankService;
use CodeIgniter\HTTP\ResponseInterface;

class BankController extends BaseApiController
{
    protected BankService $bankService;

    public function __construct()
    {
        $this->bankService = new BankService();
    }

    public function meta(): ResponseInterface
    {
        return $this->success([
            'banks' => $this->bankService->getList(),
            'active_banks' => $this->bankService->getAllActive(),
            'permissions' => [
                'can_view' => true,
                'can_create' => true,
                'can_edit' => true,
                'can_delete' => true,
            ],
        ], 'Metadata bank berhasil dimuat.');
    }

    public function list(): ResponseInterface
    {
        $banks = $this->bankService->getList();

        if ($this->isRawFormat()) {
            $items = array_map(static fn (array $value): array => [
                'id' => (int) $value['id'],
                'bank' => $value['bank'],
                'keterangan' => $value['keterangan'],
                'exp_days' => (int) ($value['exp_days'] ?? 0),
            ], $banks);

            return $this->success([
                'items' => $items,
                'meta' => [
                    'total' => count($items),
                ],
            ], 'Daftar bank berhasil dimuat.');
        }

        // TODO(full-json): Hapus format DataTables HTML ini setelah app/Views/master/listbank.php dipensiunkan.
        $data = [];
        $no = 1;

        foreach ($banks as $value) {
            $ops = '<div class="btn-group">';
            $ops .= '    <button type="button" class="btn btn-sm btn-info" onclick="edit(\''. $value['id'] .'\')"><i class="fa fa-edit"></i></button>';
            $ops .= '    <button type="button" class="btn btn-sm btn-danger" onclick="remove(\''. $value['id'] .'\')"><i class="fa fa-trash"></i></button>';
            $ops .= '</div>';

            $data[] = [
                $no,
                $value['bank'],
                $value['keterangan'],
                $value['exp_days'] . ' Hari',
                $ops,
            ];
            $no++;
        }

        return $this->respond([
            'token' => csrf_hash(),
            'data' => $data,
        ]);
    }

    public function ambil(): ResponseInterface
    {
        $search = $this->request->getGet('search');
        $banks = $this->bankService->getList($search !== null ? (string) $search : null);

        $data = [];
        foreach ($banks as $value) {
            $data[] = [
                'id' => $value['id'],
                'bank' => $value['bank'],
                'keterangan' => $value['keterangan'],
                'exp_days' => $value['exp_days'],
            ];
        }

        return $this->success($data, 'Daftar bank berhasil dimuat.');
    }

    public function ambilSatu(): ResponseInterface
    {
        $input = $this->readRequestData();
        $id = (int) ($input['id'] ?? 0);
        if ($id <= 0) {
            return $this->error('ID bank harus diisi', 400);
        }

        $bank = $this->bankService->getOne($id);
        if (! $bank) {
            return $this->error('Bank tidak ditemukan', 404);
        }

        if ($this->isRawFormat()) {
            return $this->success($bank, 'Data bank berhasil dimuat.');
        }

        return $this->respond(array_merge($bank, [
            'success' => true,
            'messages' => 'Data bank berhasil dimuat.',
            'data' => $bank,
            'token' => csrf_hash(),
        ]));
    }

    public function simpan(): ResponseInterface
    {
        $input = $this->readRequestData();
        $rules = [
            'bank' => 'required|max_length[255]',
            'keterangan' => 'permit_empty|max_length[255]',
            'exp_days' => 'permit_empty|integer',
        ];

        $validation = \Config\Services::validation();
        $validation->setRules($rules);
        if (! $validation->run($input)) {
            return $this->error('Validasi gagal', 400, $validation->getErrors());
        }

        $id = isset($input['id']) && $input['id'] !== '' ? (int) $input['id'] : null;

        try {
            $savedId = $this->bankService->simpan($input, $id);
            return $this->success(
                ['id' => $savedId],
                'Bank berhasil ' . ($id ? 'diperbarui' : 'ditambahkan')
            );
        } catch (\Throwable $e) {
            return $this->error('Gagal menyimpan bank', 500);
        }
    }

    public function hapus(): ResponseInterface
    {
        $input = $this->readRequestData();
        $id = (int) ($input['id'] ?? 0);
        if ($id <= 0) {
            return $this->error('ID bank harus diisi', 400);
        }

        try {
            $this->bankService->hapus($id);
            return $this->success(['id' => $id], 'Bank berhasil dihapus');
        } catch (\Throwable $e) {
            return $this->error('Gagal menghapus bank', 500);
        }
    }
}
