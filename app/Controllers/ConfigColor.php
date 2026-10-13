<?php

namespace App\Controllers;

/**
 * Backward compatibility stub.
 * Canonical route for color configuration is /configcolor handled by PengaturanWarnaController.
 */
class ConfigColor extends BaseController
{
    public function index()
    {
        return redirect()->to('/configcolor');
    }
}
