<?php

namespace App\Controllers\Web;

use App\Controllers\BaseController;

class MemberGetMemberController extends BaseController
{
    protected string $activeMenu = 'member-get-member';

    public function index()
    {
        $data = [
            'title' => 'Member Get Member',
        ];
        return view('template', [
            'content' => 'mgm/index',
            'data' => $data,
        ]);
    }
}
