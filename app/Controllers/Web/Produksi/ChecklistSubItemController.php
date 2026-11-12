<?php

namespace App\Controllers\Web\Produksi;

use App\Controllers\BaseController;
use App\Repositories\Produksi\ChecklistRepository;

class ChecklistSubItemController extends BaseController
{
    protected ChecklistRepository $checklistRepo;

    public function __construct(?ChecklistRepository $checklistRepo = null)
    {
        $this->checklistRepo = $checklistRepo ?? new ChecklistRepository();
    }

    public function index()
    {
        $data['data']['item']       = $this->checklistRepo->getActiveGroupsWithItems();
        $data['content']            = 'checklist/checklistSubItem';
        $data['data']['controller'] = 'checklistSubItem';
        $data['data']['title']      = 'Checklist Sub Item';

        return view('template', $data);
    }
}
