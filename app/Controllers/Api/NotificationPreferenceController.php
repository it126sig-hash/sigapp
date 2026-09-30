<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Services\NotificationPreferenceService;

class NotificationPreferenceController extends BaseController
{
    protected NotificationPreferenceService $preferenceService;

    public function __construct()
    {
        $this->preferenceService = new NotificationPreferenceService();
    }

    /**
     * Ambil daftar preferensi untuk user yang sedang login
     */
    public function index()
    {
        $userId = user_id();
        if (!$userId) {
            return $this->response->setJSON(['success' => false, 'messages' => 'Unauthorized'])->setStatusCode(401);
        }

        // Ambil data user saat ini untuk mengetahui group role (untuk relevant_groups)
        $db = db_connect();
        $groupRow = $db->table('auth_groups_users')->where('user_id', $userId)->get()->getRow();
        $groupId = $groupRow ? (int)$groupRow->group_id : null;

        $preferences = $this->preferenceService->getUserPreferences($userId, $groupId);

        return $this->response->setJSON([
            'success' => true,
            'data' => $preferences
        ]);
    }

    /**
     * Simpan perubahan preferensi
     */
    public function save()
    {
        $userId = user_id();
        if (!$userId) {
            return $this->response->setJSON(['success' => false, 'messages' => 'Unauthorized'])->setStatusCode(401);
        }

        $postData = $this->request->getJSON(true);
        if (!$postData || !isset($postData['preferences'])) {
            return $this->response->setJSON(['success' => false, 'messages' => 'Invalid data payload']);
        }

        try {
            $this->preferenceService->savePreferences($userId, $postData['preferences']);
            return $this->response->setJSON([
                'success' => true,
                'messages' => 'Preferensi notifikasi berhasil disimpan'
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Error saving notification preferences: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'messages' => 'Terjadi kesalahan saat menyimpan preferensi'
            ])->setStatusCode(500);
        }
    }

    /**
     * Kembalikan preferensi ke default bawaan sistem
     */
    public function reset()
    {
        $userId = user_id();
        if (!$userId) {
            return $this->response->setJSON(['success' => false, 'messages' => 'Unauthorized'])->setStatusCode(401);
        }

        try {
            $this->preferenceService->resetToDefaults($userId);
            return $this->response->setJSON([
                'success' => true,
                'messages' => 'Preferensi notifikasi dikembalikan ke default'
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Error resetting notification preferences: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'messages' => 'Terjadi kesalahan saat mereset preferensi'
            ])->setStatusCode(500);
        }
    }
}
