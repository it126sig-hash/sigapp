<?php

namespace App\Controllers\Web\Proyek;

use App\Controllers\BaseController;
use App\Services\Proyek\JalanService;

class JalanController extends BaseController
{
    protected JalanService $jalanService;

    public function __construct()
    {
        $this->jalanService = new JalanService();
    }

    public function index()
    {
        $data['content']            = 'master/jalan';
        $data['data']               = $this->jalanService->getIndexData();
        $data['data']['controller'] = 'jalan';

        if ($this->wantsJson()) {
            return $this->response->setJSON([
                'success' => true,
                'data'    => $data['data'],
            ]);
        }

        return view('template', $data);
    }

    private function wantsJson(): bool
    {
        $accept = (string) $this->request->getHeaderLine('Accept');
        return str_contains($accept, 'application/json')
            || !empty($this->request->getHeaderLine('X-Client-Platform'))
            || $this->request->getVar('format') === 'json';
    }
}
