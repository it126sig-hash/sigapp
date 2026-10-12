<?php

namespace App\Controllers\Web;

use App\Controllers\BaseController;
use App\Services\JalanService;

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

        return view('template', $data);
    }
}
