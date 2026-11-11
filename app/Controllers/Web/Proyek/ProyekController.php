<?php

namespace App\Controllers\Web\Proyek;

use App\Controllers\BaseController;

class ProyekController extends BaseController
{
    public function index()
    {
        $data['content']            = 'master/proyek';
        $data['data']['controller'] = 'proyek';
        $data['data']['title']      = 'proyek';

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
