<?php

namespace App\Controllers;

use App\Controllers\Web\Settings\MenuSettingController as WebController;
use App\Controllers\Api\Settings\MenuSettingController as ApiController;

/**
 * Backward compatibility delegator shim.
 * Canonical Web: App\Controllers\Web\Settings\MenuSettingController
 * Canonical Api: App\Controllers\Api\Settings\MenuSettingController
 */
class MenuSetting extends BaseController
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

    public function menuList()
    {
        return $this->api->menuList();
    }

    public function menuGet()
    {
        return $this->api->menuGet();
    }

    public function menuSave()
    {
        return $this->api->menuSave();
    }

    public function menuToggle()
    {
        return $this->api->menuToggle();
    }

    public function groupAccess()
    {
        return $this->api->groupAccess();
    }

    public function groupSave()
    {
        return $this->api->groupSave();
    }

    public function userAccess()
    {
        return $this->api->userAccess();
    }

    public function userSave()
    {
        return $this->api->userSave();
    }

    public function siteplanMenuList()
    {
        return $this->api->siteplanMenuList();
    }

    public function siteplanMenuGet()
    {
        return $this->api->siteplanMenuGet();
    }

    public function siteplanMenuSave()
    {
        return $this->api->siteplanMenuSave();
    }

    public function siteplanMenuToggle()
    {
        return $this->api->siteplanMenuToggle();
    }
}
