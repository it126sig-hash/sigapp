<?php

namespace App\Controllers\Web\Settings;

use App\Controllers\BaseController;
use App\Services\Settings\SettingDataService;

class SettingDataController extends BaseController
{
    protected SettingDataService $settingDataService;

    public function __construct()
    {
        $this->settingDataService = new SettingDataService();
    }

    public function index()
    {
        $data['content'] = 'setting/data';
        $data['data'] = [
            'controller' => 'setting-data',
            'title'      => 'Setting Data',
            'tables'     => $this->settingDataService->getAllConfigs(),
        ];

        return view('template', $data);
    }
}
