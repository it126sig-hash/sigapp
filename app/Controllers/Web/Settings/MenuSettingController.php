<?php

namespace App\Controllers\Web\Settings;

use App\Controllers\BaseController;
use App\Services\Settings\MenuSettingService;

class MenuSettingController extends BaseController
{
    protected MenuSettingService $menuSettingService;

    public function __construct()
    {
        $this->menuSettingService = new MenuSettingService();
    }

    public function index()
    {
        $data['content'] = 'menu/setting';
        $data['data'] = array_merge([
            'controller' => 'menu-setting',
            'title'      => 'Setting Menu',
        ], $this->menuSettingService->getIndexData());

        return view('template', $data);
    }
}
