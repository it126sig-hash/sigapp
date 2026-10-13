<?php

namespace App\Controllers\Web\UserManagement;

use App\Controllers\BaseController;
use App\Services\UserManagement\ProfilService;

class ProfilController extends BaseController
{
    protected ProfilService $profilService;

    public function __construct()
    {
        $this->profilService = new ProfilService();
    }

    public function index()
    {
        $userId          = (int) user_id();
        $data['content'] = 'user/profil';
        $data['data']    = $this->profilService->getIndexData($userId);

        return view('template', $data);
    }

    public function update()
    {
        $userId   = (int) user_id();
        $postData = (array) $this->request->getPost();
        $photo    = $this->request->getFile('profile_photo');

        $result = $this->profilService->updateProfile($userId, $postData, $photo);

        if (!$result['success']) {
            return redirect()->back()->withInput()->with('errors', $result['messages']);
        }

        return redirect()->to(base_url('profil'))->with('message', $result['messages']);
    }
}
