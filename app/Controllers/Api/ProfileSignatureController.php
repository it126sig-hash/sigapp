<?php

namespace App\Controllers\Api;

use App\Services\Bpb\ProfileSignatureService;
use RuntimeException;

class ProfileSignatureController extends BaseApiController
{
    private ProfileSignatureService $service;

    public function __construct()
    {
        $this->service = new ProfileSignatureService();
    }

    public function show($id = null)
    {
        return $this->success($this->service->get((int) user_id()));
    }

    public function image()
    {
        try {
            $path = $this->service->absolutePath((int) user_id());
            return $this->response->setHeader('Content-Type', 'image/png')->setHeader('Cache-Control', 'private, no-store')->setBody(file_get_contents($path));
        } catch (RuntimeException $e) {
            return $this->error($e->getMessage(), 404);
        }
    }

    public function save()
    {
        $data = $this->request->getJSON(true) ?: $this->request->getPost();
        try {
            return $this->success($this->service->save((int) user_id(), (string) ($data['password'] ?? ''), (string) ($data['signature_data'] ?? '')), 'Tanda tangan profil disimpan.');
        } catch (RuntimeException $e) {
            return $this->error($e->getMessage(), 422);
        }
    }

    public function delete($id = null)
    {
        $data = $this->request->getJSON(true) ?: $this->request->getPost();
        try {
            $this->service->delete((int) user_id(), (string) ($data['password'] ?? ''));
            return $this->success(null, 'Tanda tangan profil dihapus.');
        } catch (RuntimeException $e) {
            return $this->error($e->getMessage(), 422);
        }
    }
}
