<?php

namespace App\Controllers;

use App\Controllers\Api\Siteplan\SiteplanController as ApiSiteplanController;
use App\Controllers\Web\Siteplan\SiteplanController as WebSiteplanController;

/**
 * Backward compatibility shim.
 * Canonical classes:
 * - App\Controllers\Web\Siteplan\SiteplanController
 * - App\Controllers\Api\Siteplan\SiteplanController
 */
class Siteplan extends BaseController
{
    public function index()
    {
        return $this->webController()->index();
    }

    public function view_siteplan(?int $idProyek = null)
    {
        return $this->webController()->view_siteplan($idProyek);
    }

    public function produksi_mobile()
    {
        return $this->webController()->produksi_mobile();
    }

    public function urgentSummary()
    {
        return $this->apiController()->urgentSummary();
    }

    public function getAllKavling()
    {
        return $this->apiController()->getAllKavling();
    }

    public function getKategoriOptions()
    {
        return $this->apiController()->getKategoriOptions();
    }

    public function get_others()
    {
        return $this->apiController()->get_others();
    }

    public function add_kavling()
    {
        return $this->apiController()->add_kavling();
    }

    public function edit_kavling()
    {
        return $this->apiController()->edit_kavling();
    }

    public function edit_others()
    {
        return $this->apiController()->edit_others();
    }

    public function get_kavling_by_multiple_id()
    {
        return $this->apiController()->get_kavling_by_multiple_id();
    }

    public function get_harga_kavling()
    {
        return $this->apiController()->get_harga_kavling();
    }

    public function get_turun_pembangunan()
    {
        return $this->apiController()->get_turun_pembangunan();
    }

    public function set_turun_pembangunan()
    {
        return $this->apiController()->set_turun_pembangunan();
    }

    public function get_kavling_by_id()
    {
        return $this->apiController()->get_kavling_by_id();
    }

    public function get_detail()
    {
        return $this->apiController()->get_detail();
    }

    private function webController(): WebSiteplanController
    {
        $controller = new WebSiteplanController();
        $controller->initController($this->request, $this->response, $this->logger);

        return $controller;
    }

    private function apiController(): ApiSiteplanController
    {
        $controller = new ApiSiteplanController();
        $controller->initController($this->request, $this->response, $this->logger);

        return $controller;
    }
}
