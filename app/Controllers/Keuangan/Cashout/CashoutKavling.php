<?php

namespace App\Controllers\Keuangan\Cashout;

use App\Controllers\BaseController;
use App\Models\ChecklistSubItemModel;
use App\Models\ProyekModel;
use App\Services\ActiveProyekService;
use App\Services\Keuangan\Cashout\CashoutKavlingService;

class CashoutKavling extends BaseController
{
    protected $cashoutKavlingService;
    protected $activeProyekService;
    protected $proyekModel;
    protected $siModel;
    protected $db;

    public function __construct()
    {
        $this->cashoutKavlingService = new CashoutKavlingService();
        $this->activeProyekService = new ActiveProyekService();
        $this->proyekModel = new ProyekModel();
        $this->siModel = new ChecklistSubItemModel();
        $this->db = \Config\Database::connect();
    }

    public function index()
    {
        $idProyek = $this->activeProyekService->getActiveId();
        if ($idProyek === null) {
            return redirect()->to(base_url('dashboard'))->with('error', 'Silahkan pilih proyek terlebih dahulu');
        }

        $data = [
            'content' => 'cashout_kavling/index',
            'data' => [
                'title' => 'Rekap Cashout per Kavling',
                'proyek' => $this->proyekModel->find($idProyek),
                'pph' => $this->db->table('pph')->where('deleted_at', null)->get()->getResult(),
                'ppn' => $this->db->table('ppn')->where('deleted_at', null)->get()->getResult(),
                'list' => [],
            ],
        ];

        if (in_groups(['1', '7', '8'])) {
            $data['data']['list'] = $this->siModel
                ->select('
                    checklist_group.nama_group,
                    checklist_item.nama_item,
                    checklist_subitem.id_subitem,
                    checklist_subitem.nama_subitem,
                ')
                ->join('checklist_item', 'checklist_item.id_item = checklist_subitem.id_item')
                ->join('checklist_group', 'checklist_item.id_group = checklist_group.id_group')
                ->where('checklist_item.is_active = 1')
                ->where('checklist_group.is_active = 1')
                ->where('checklist_subitem.is_active = 1')
                ->orderBy('checklist_item.id_group', 'asc')
                ->orderBy('checklist_item.id_item', 'asc')
                ->findAll();
        }

        return view('template', $data);
    }

    public function getDataTables()
    {
        return $this->json($this->cashoutKavlingService->getDataTables($this->request->getVar()));
    }

    public function getDetailList()
    {
        return $this->json(
            $this->cashoutKavlingService->getDetailList((int) $this->request->getPost('id_kavling'))
        );
    }

    private function json(array $payload)
    {
        $payload['token'] = $payload['token'] ?? csrf_hash();
        return $this->response->setJSON($payload);
    }
}
