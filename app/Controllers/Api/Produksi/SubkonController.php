<?php

namespace App\Controllers\Api\Produksi;

use App\Controllers\Api\BaseApiController;
use App\Repositories\Produksi\ProduksiRepository;
use CodeIgniter\HTTP\ResponseInterface;

class SubkonController extends BaseApiController
{
    protected ProduksiRepository $repo;

    public function __construct(?ProduksiRepository $repo = null)
    {
        $this->repo = $repo ?? new ProduksiRepository();
    }

    public function getList(): ResponseInterface
    {
        $search = trim((string) ($this->request->getVar('search') ?? ''));
        $data   = $this->repo->getSubkonList($search);

        // If requested via legacy route /subkon/list/ambil without raw format requested, preserve direct array for legacy select2
        $uri = $this->request->getUri()->getPath();
        $isLegacyRoute = str_contains($uri, 'subkon/list/ambil');
        $wantsEnvelope = $this->request->getGet('format') === 'raw'
            || $this->request->hasHeader('X-Client-Platform')
            || str_starts_with($uri, 'api/');

        if ($isLegacyRoute && !$wantsEnvelope) {
            return $this->response->setJSON($data);
        }

        return $this->success($data, 'Data subkon berhasil diambil');
    }
}
