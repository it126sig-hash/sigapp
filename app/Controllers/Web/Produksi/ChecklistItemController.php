<?php

namespace App\Controllers\Web\Produksi;

use App\Controllers\BaseController;
use App\Repositories\Produksi\ChecklistRepository;

class ChecklistItemController extends BaseController
{
    protected ChecklistRepository $checklistRepo;

    public function __construct(?ChecklistRepository $checklistRepo = null)
    {
        $this->checklistRepo = $checklistRepo ?? new ChecklistRepository();
    }

    public function index()
    {
        $data['data']['group']      = $this->checklistRepo->getActiveGroups();
        $data['content']            = 'checklist/checklistItem';
        $data['data']['controller'] = 'checklistItem';
        $data['data']['title']      = 'Checklist Item';

        return view('template', $data);
    }
}
