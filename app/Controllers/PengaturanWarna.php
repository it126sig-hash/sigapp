<?php

namespace App\Controllers;

use App\Controllers\Web\Settings\PengaturanWarnaController as WebController;
use App\Controllers\Api\Settings\PengaturanWarnaController as ApiController;

/**
 * Backward compatibility delegator shim.
 * Canonical Web: App\Controllers\Web\Settings\PengaturanWarnaController
 * Canonical Api: App\Controllers\Api\Settings\PengaturanWarnaController
 */
class PengaturanWarna extends BaseController
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

    public function getAll()
    {
        return $this->api->getAll();
    }

    public function getOne()
    {
        return $this->api->getOne();
    }

    public function add()
    {
        return $this->api->add();
    }

    public function edit()
    {
        return $this->api->edit();
    }

    public function remove()
    {
        return $this->api->remove();
    }
}
