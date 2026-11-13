<?php

namespace App\Controllers\Web\Mkdt;

use App\Controllers\BaseController;
use App\Services\Mkdt\PosisiKonsumenService;

class PosisiKonsumenController extends BaseController
{
    protected PosisiKonsumenService $posisiKonsumenService;

    public function __construct(?PosisiKonsumenService $posisiKonsumenService = null)
    {
        $this->posisiKonsumenService = $posisiKonsumenService ?? new PosisiKonsumenService();
    }

    public function index($status = null)
    {
        $userId     = (int) (function_exists('user_id') ? (user_id() ?? 0) : 0);
        $userGroups = [];

        if (function_exists('user') && user()) {
            $userGroups = array_map('strval', array_keys(user()->getRoles()));
        }

        $indexData = $this->posisiKonsumenService->getIndexData(
            $status !== null ? (string) $status : null,
            $userId,
            $userGroups
        );

        return view('template', [
            'content' => $indexData['content'],
            'data'    => $indexData['data'],
        ]);
    }
}
