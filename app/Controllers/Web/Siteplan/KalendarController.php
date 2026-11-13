<?php

namespace App\Controllers\Web\Siteplan;

use App\Controllers\BaseController;
use App\Services\Siteplan\KalendarService;

class KalendarController extends BaseController
{
    protected KalendarService $kalendarService;

    public function __construct(?KalendarService $kalendarService = null)
    {
        $this->kalendarService = $kalendarService ?? new KalendarService();
    }

    public function index()
    {
        $userId = function_exists('user_id') ? (int) (user_id() ?? 0) : 0;

        return view('template', [
            'content' => 'misc/kalender',
            'data'    => $this->kalendarService->getCalendarViewData($userId),
        ]);
    }
}
