<?php

namespace App\Controllers\Web\Keuangan;

use App\Controllers\BaseController;
use App\Services\Keuangan\DireksiService;
use CodeIgniter\HTTP\ResponseInterface;

class DireksiController extends BaseController
{
    protected DireksiService $direksiService;

    public function __construct(?DireksiService $direksiService = null)
    {
        $this->direksiService = $direksiService ?? new DireksiService();
    }

    public function tagihan(): string
    {
        $data['content'] = 'direksi/list-tagihan-pending';
        $data['data']['title'] = 'Persetujuan Surat Tagihan';

        return view('template', $data);
    }

    public function export_xlsx(): ResponseInterface
    {
        $table = (string) $this->request->getVar('table');

        return $this->response->setJSON(
            $this->direksiService->exportXlsx($table)
        );
    }
}
