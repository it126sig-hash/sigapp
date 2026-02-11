<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;
use App\Repositories\NotifRepository;
use App\Repositories\CashOutRepository;
use Hermawan\DataTables\DataTable;


class CashOutService
{
    protected $repo;
    protected $db;
    protected $notif;

    public function __construct()
    {
        $this->repo = model(CashOutRepository::class);
        $this->db = \Config\Database::connect();

        $this->notif = new NotifRepository();
    }

    function insert($data)
    {
        $this->db->transStart();
        $this->repo->insert($data);
        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            return false;
        }
        return true;
    }
    function delete($id)
    {
        $this->db->transStart();

        // 1. Ambil data row-nya dulu
        $dataRow = $this->repo->find($id);
        // var_dump($dataRow);
        // die();
        $idKavling = $dataRow ? $dataRow->id_kavling : null;

        // 2. Baru hapus
        $this->repo->softDelete($id);

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            return false;
        }

        // Kamu bisa mengembalikan id_kavling atau melakukan aksi lain di sini
        return $idKavling;
    }

    private function num($v)
    {
        // if ($v === null || $v === '')
        //     return null;
        // $v = (string) $v;
        // $v = str_replace(',', "", $v);
        // return (int) round((float) $v);
        $v = str_replace(',', "", $v);
        return $v;
    }

    function format_tgl($tgl)
    {
        if ($tgl == "" || $tgl == "0000-00-00" || $tgl == null)
            return "-";
        return date_format(date_create($tgl), "d-M-Y");
    }
    function is_active($id, $texts, $textf)
    {
        $r = '<span class="btn btn-primary btn-sm" text-capitalized="">' . $textf . '</span>';
        if ($id == "1")
            $r = '<span class="btn btn-success btn-sm" text-capitalized="">' . $texts . '</span>';
        return $r;
    }
}
