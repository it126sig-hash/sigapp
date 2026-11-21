<?php

namespace App\Controllers\Web\Mkdt;

use App\Controllers\BaseController;
use App\Services\Mkdt\StockService;

class StockController extends BaseController
{
    protected StockService $stockService;

    public function __construct(?StockService $stockService = null)
    {
        $this->stockService = $stockService ?? new StockService();
    }

    public function index(): string
    {
        $indexData = $this->stockService->getIndexData();

        return view('template', [
            'content' => $indexData['content'],
            'data'    => $indexData['data'],
        ]);
    }
}
