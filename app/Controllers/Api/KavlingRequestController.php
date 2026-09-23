<?php

namespace App\Controllers\Api;

use App\Repositories\KavlingRepository;
use App\Services\KavlingRequestService;
use Throwable;

class KavlingRequestController extends BaseApiController
{
    protected KavlingRequestService $service;
    protected KavlingRepository $kavlingRepo;

    public function __construct()
    {
        $this->service = new KavlingRequestService();
        $this->kavlingRepo = new KavlingRepository();
    }

    /**
     * Submit pengajuan request penambahan atau ubah tipe kavling
     */
    public function submit()
    {
        $userId = (int) user_id();
        if ($userId <= 0) {
            return $this->error('Sesi telah berakhir, silakan login kembali.', 401);
        }

        $rules = [
            'id_proyek'     => 'required|is_natural_no_zero',
            'jenis_request' => 'required|in_list[tambah_baru,ubah_tipe]',
        ];

        if (!$this->validate($rules)) {
            return $this->error('Validasi gagal', 422, $this->validator->getErrors());
        }

        try {
            $postData = $this->request->getPost();
            $result = $this->service->submitRequest($postData, $userId);

            return $this->success($result, $result['message']);
        } catch (Throwable $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * List kavling untuk select2 dropdown pada proyek tertentu
     */
    public function kavlingList()
    {
        $idProyek = $this->request->getPost('id_proyek');
        $search   = $this->request->getPost('search') ?? '';
        $limit    = 30;

        if (empty($idProyek)) {
            return $this->respond([
                'results' => [],
                'token'   => csrf_hash(),
            ]);
        }

        $list = $this->kavlingRepo->getKavlingList($idProyek, $search, $limit);

        $results = [];
        foreach ($list as $item) {
            $results[] = [
                'id'   => $item->id_kavling,
                'text' => $item->nama_jalan . ' No. ' . $item->no_kavling,
            ];
        }

        return $this->respond([
            'results' => $results,
            'token'   => csrf_hash(),
        ]);
    }

    /**
     * Menampilkan daftar request kavling untuk tim Planning / Admin
     */
    public function list()
    {
        if (!in_groups(['1', '6'])) {
            return $this->error('Akses ditolak. Hanya tim Planning yang dapat mengakses data ini.', 403);
        }

        $idProyek = (int) ($this->request->getPost('id_proyek') ?: 0);
        if ($idProyek <= 0) {
            return $this->error('ID Proyek diperlukan', 400);
        }

        $status = $this->request->getPost('status');
        try {
            $data = $this->service->getList($idProyek, $status);
            return $this->success($data);
        } catch (Throwable $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * Mengubah status request kavling (contoh: approved/selesai, pending, atau rejected)
     */
    public function updateStatus()
    {
        if (!in_groups(['1', '6'])) {
            return $this->error('Akses ditolak. Hanya tim Planning yang dapat mengubah status request.', 403);
        }

        $idRequest = (int) ($this->request->getPost('id_request') ?: 0);
        $status    = $this->request->getPost('status');

        if ($idRequest <= 0 || empty($status)) {
            return $this->error('Parameter id_request dan status wajib diisi.', 400);
        }

        try {
            $result = $this->service->updateStatus($idRequest, $status, (int) user_id());
            return $this->success($result, $result['message']);
        } catch (Throwable $e) {
            return $this->error($e->getMessage(), 400);
        }
    }
}
