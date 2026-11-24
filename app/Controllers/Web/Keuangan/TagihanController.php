<?php

namespace App\Controllers\Web\Keuangan;

use App\Controllers\BaseController;
use App\Services\Keuangan\KeuanganService;

class TagihanController extends BaseController
{
    protected KeuanganService $keuanganService;

    public function __construct(?KeuanganService $keuanganService = null)
    {
        $this->keuanganService = $keuanganService ?? new KeuanganService();
    }

    public function listTagihan(): string
    {
        $data['content'] = 'keuangan/list-tagihan';
        $data['data']['controller'] = 'Keuangan';
        $data['data']['title'] = 'List Tagihan';
        $data['data']['li_keu'] = json_encode($this->keuanganService->getListItemKeuangan());

        return view('template', $data);
    }

    public function riwayatBayarIndex(): string
    {
        $data['content'] = 'keuangan/riwayat-bayar';
        $data['data']['controller'] = 'Tagihan';
        $data['data']['title'] = 'Riwayat Pembayaran';

        return view('template', $data);
    }
}
