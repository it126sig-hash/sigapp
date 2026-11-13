<?php

namespace App\Controllers;

use App\Controllers\Api\Siteplan\KalendarController as ApiKalendarController;
use App\Controllers\Web\Siteplan\KalendarController as WebKalendarController;

/**
 * Backward compatibility shim.
 * Canonical classes:
 * - App\Controllers\Web\Siteplan\KalendarController
 * - App\Controllers\Api\Siteplan\KalendarController
 */
class Kalendar extends BaseController
{
    public function index()
    {
        $controller = new WebKalendarController();
        $controller->initController($this->request, $this->response, $this->logger);

        return $controller->index();
    }

    public function getEvents()
    {
        $controller = new ApiKalendarController();
        $controller->initController($this->request, $this->response, $this->logger);

        return $controller->getEvents();
    }
}
