<?php

namespace App\Controllers\Web\Proyek;

use App\Controllers\BaseController;
use App\Services\Proyek\ProyekService;

class TipeController extends BaseController
{
    protected ProyekService $proyekService;

    public function __construct()
    {
        $this->proyekService = new ProyekService();
    }

    public function index()
    {
        $data['data']['proyek']     = $this->proyekService->getDropdownList();
        $data['content']            = 'master/tipe';
        $data['data']['controller'] = 'tipe';
        $data['data']['title']      = 'Tipe';

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
