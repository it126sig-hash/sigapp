<?php

namespace App\Controllers;

/**
 * Backward compatibility stub.
 */
class Config extends BaseController
{
    public function index()
    {
        return redirect()->to('login');
    }

    public function get_shape()
    {
        return $this->response->setJSON([]);
    }
}
