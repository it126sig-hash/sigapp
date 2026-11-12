<?php

namespace App\Controllers\Api\Transaksi;

use App\Controllers\Api\BaseApiController;
use App\Repositories\Transaksi\KavlingRepository;
use CodeIgniter\HTTP\ResponseInterface;

class KavlingController extends BaseApiController
{
    protected KavlingRepository $kavlingRepo;

    public function __construct(?KavlingRepository $kavlingRepo = null)
    {
        $this->kavlingRepo = $kavlingRepo ?? new KavlingRepository();
    }

    public function getList(): ResponseInterface
    {
        $id_proyek         = $this->request->getVar('id_proyek') ?? '';
        $search            = $this->request->getVar('search') ?? '';
        $limit             = $this->request->getVar('limit') ?? null;
        $is_cashout_subkon = $this->request->getVar('is_cashout_subkon') ?? 0;
        $id_cluster        = $this->request->getVar('id_cluster') ?? null;
        $id_jalan          = $this->request->getVar('id_jalan') ?? null;
        $only_available    = $this->request->getVar('only_available') ?? 0;

        $kavling = $this->kavlingRepo->getKavlingList(
            $id_proyek,
            $search,
            $limit,
            $is_cashout_subkon,
            $id_cluster,
            $id_jalan,
            $only_available
        );

        if ($this->request->getVar('format') === 'raw') {
            return $this->success($kavling, 'Daftar kavling berhasil diambil');
        }

        return $this->response->setJSON($kavling);
    }
}
