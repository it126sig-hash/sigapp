<?php

namespace App\Controllers\Api\UserManagement;

use App\Controllers\Api\BaseApiController;
use App\Services\UserManagement\ProfilService;

class ProfilController extends BaseApiController
{
    protected ProfilService $profilService;

    public function __construct()
    {
        $this->profilService = new ProfilService();
    }

    public function update($id = null)
    {
        $userId   = (int) user_id();
        $postData = (array) $this->request->getPost();
        $photo    = $this->request->getFile('profile_photo');

        $result = $this->profilService->updateProfile($userId, $postData, $photo);

        $code = $result['success'] ? 200 : 400;
        return $this->respond(array_merge($result, [
            'token' => csrf_hash(),
        ]), $code);
    }
}
