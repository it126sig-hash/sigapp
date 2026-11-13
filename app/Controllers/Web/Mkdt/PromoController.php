<?php

namespace App\Controllers\Web\Mkdt;

use App\Controllers\BaseController;
use App\Services\Mkdt\PromoService;

class PromoController extends BaseController
{
    protected PromoService $promoService;

    public function __construct(?PromoService $promoService = null)
    {
        $this->promoService = $promoService ?? new PromoService();
    }

    public function index()
    {
        $indexData = $this->promoService->getIndexData();

        $data = [
            'content' => 'promo/index',
            'data'    => [
                'proyek'     => $indexData['proyek'],
                'controller' => 'promo',
                'title'      => 'Promo',
            ],
        ];

        return view('template', $data);
    }
}
