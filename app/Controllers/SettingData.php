<?php

namespace App\Controllers;

use App\Controllers\Web\Settings\SettingDataController as WebController;
use App\Controllers\Api\Settings\SettingDataController as ApiController;

/**
 * Backward compatibility delegator shim.
 * Canonical Web: App\Controllers\Web\Settings\SettingDataController
 * Canonical Api: App\Controllers\Api\Settings\SettingDataController
 */
class SettingData extends BaseController
{
    protected WebController $web;
    protected ApiController $api;

    public function __construct()
    {
        $this->web = new WebController();
        $this->api = new ApiController();
    }

    public function index()
    {
        return $this->web->index();
    }

    public function list(string $key)
    {
        return $this->api->list($key);
    }

    public function get(string $key)
    {
        return $this->api->get($key);
    }

    public function save(string $key)
    {
        return $this->api->save($key);
    }

    public function delete(string $key)
    {
        return $this->api->delete($key);
    }
}
