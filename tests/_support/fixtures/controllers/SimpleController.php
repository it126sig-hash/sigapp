<?php

namespace Tests\Support\Fixtures\Controllers;

use App\Models\UserModel;

/**
 * Simple test controller with minimal complexity
 */
class SimpleController
{
    private UserModel $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    public function index()
    {
        $users = $this->userModel->findAll();
        return view('users/index', ['users' => $users]);
    }

    public function show($id)
    {
        $user = $this->userModel->find($id);
        return view('users/show', ['user' => $user]);
    }
}
