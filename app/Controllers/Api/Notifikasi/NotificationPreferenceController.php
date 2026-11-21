<?php

namespace App\Controllers\Api\Notifikasi;

use App\Controllers\Api\BaseApiController;
use App\Repositories\Notifikasi\NotificationRepository;
use App\Services\Notifikasi\NotificationPreferenceService;
use CodeIgniter\HTTP\ResponseInterface;

class NotificationPreferenceController extends BaseApiController
{
    protected NotificationPreferenceService $preferenceService;
    protected NotificationRepository $notificationRepository;

    public function __construct(
        ?NotificationPreferenceService $preferenceService = null,
        ?NotificationRepository $notificationRepository = null
    ) {
        $this->preferenceService = $preferenceService ?? new NotificationPreferenceService();
        $this->notificationRepository = $notificationRepository ?? new NotificationRepository();
    }

    /**
     * Ambil daftar preferensi untuk user yang sedang login
     */
    public function index(): ResponseInterface
    {
        $userId = (int) user_id();
        if ($userId <= 0) {
            return $this->error('Unauthorized', 401);
        }

        $groupId = $this->notificationRepository->getUserGroupId($userId);
        $preferences = $this->preferenceService->getUserPreferences($userId, $groupId > 0 ? $groupId : null);

        return $this->success($preferences);
    }

    /**
     * Simpan perubahan preferensi
     */
    public function save(): ResponseInterface
    {
        $userId = (int) user_id();
        if ($userId <= 0) {
            return $this->error('Unauthorized', 401);
        }

        $postData = $this->request->getJSON(true);
        if (! $postData || ! isset($postData['preferences'])) {
            return $this->error('Invalid data payload', 422);
        }

        try {
            $this->preferenceService->savePreferences($userId, $postData['preferences']);

            return $this->success(null, 'Preferensi notifikasi berhasil disimpan.');
        } catch (\Throwable $e) {
            log_message('error', 'Error saving notification preferences: ' . $e->getMessage());

            return $this->error('Terjadi kesalahan saat menyimpan preferensi.', 500);
        }
    }

    /**
     * Kembalikan preferensi ke default bawaan sistem
     */
    public function reset(): ResponseInterface
    {
        $userId = (int) user_id();
        if ($userId <= 0) {
            return $this->error('Unauthorized', 401);
        }

        try {
            $this->preferenceService->resetToDefaults($userId);

            return $this->success(null, 'Preferensi notifikasi dikembalikan ke default.');
        } catch (\Throwable $e) {
            log_message('error', 'Error resetting notification preferences: ' . $e->getMessage());

            return $this->error('Terjadi kesalahan saat mereset preferensi.', 500);
        }
    }
}
