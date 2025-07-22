<?php

namespace App\Controllers;

use App\Models\ProyekModel;


class ConfigColor extends BaseController
{
    protected $db;
    protected $proyekModel;
    protected $notif;
    public function __construct()
    {
        $this->proyekModel = new ProyekModel();
        $this->notif = new Notif();
        $this->db = db_connect();
    }
    public function index()
    {
        // $data['content']['data'] = "";

        // $data['content'] = 'master/configcolor';
        // return view('template',$data);
    }
 
}
