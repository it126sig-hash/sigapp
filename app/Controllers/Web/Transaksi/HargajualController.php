<?php

namespace App\Controllers\Web\Transaksi;

use App\Controllers\BaseController;
use App\Services\Transaksi\HargaJualService;

class HargajualController extends BaseController
{
    protected HargaJualService $hargaJualService;

    public function __construct(?HargaJualService $hargaJualService = null)
    {
        $this->hargaJualService = $hargaJualService ?? new HargaJualService();
    }

    public function index()
    {
        if (!$this->hargaJualService->hasAccess((int) user_id())) {
            return view('errors/html/unauthorized', [
                'message' => 'Kamu tidak memiliki akses <a href="">Klik untuk kembali</a>',
            ]);
        }

        $data = [
            'content' => 'hargajual/index',
            'data'    => [
                'proyek'     => $this->hargaJualService->getProyekList(),
                'controller' => 'hargajual',
                'title'      => 'hargajual',
            ],
        ];

        return view('template', $data);
    }
}
