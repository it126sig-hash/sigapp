<?php

namespace App\Services;

use App\Repositories\ProyekRepository;

class ActiveProyekService
{
    private const SESSION_KEY = 'id_proyek';
    private const PICKER_PENDING_KEY = 'proyek_picker_pending';
    private const SESSION_INITIALIZED_KEY = 'proyek_session_initialized';

    private ProyekRepository $proyekRepository;
    private FileAccessService $fileAccessService;

    public function __construct()
    {
        $this->proyekRepository = new ProyekRepository();
        $this->fileAccessService = new FileAccessService();
    }

    public function getActiveId(): ?int
    {
        $id = session(self::SESSION_KEY);

        return $id ? (int) $id : null;
    }

    public function getActive(): ?object
    {
        $id = $this->getActiveId();
        if (!$id) {
            return null;
        }

        return $this->enrichProyek($this->proyekRepository->getById($id));
    }

    public function resolveOnRequest(): ?int
    {
        $this->bootstrapForRequest();

        return $this->getActiveId();
    }

    public function bootstrapForRequest(): void
    {
        if (! function_exists('logged_in') || ! logged_in()) {
            return;
        }

        $userId = (int) user_id();

        if (! session()->has(self::SESSION_INITIALIZED_KEY)) {
            session()->set(self::SESSION_INITIALIZED_KEY, true);
            session()->set(self::PICKER_PENDING_KEY, true);
        }

        $this->ensureDefaultActive($userId);
    }

    public function needsSelection(): bool
    {
        if (! session(self::PICKER_PENDING_KEY)) {
            return false;
        }

        return ! empty($this->getAccessibleList((int) user_id()));
    }

    public function dismissSelectionPicker(): void
    {
        session()->set(self::PICKER_PENDING_KEY, false);
    }

    public function resolveAndGet(): ?object
    {
        $id = $this->resolveOnRequest();
        if (!$id) {
            return null;
        }

        return $this->enrichProyek($this->proyekRepository->getById($id));
    }

    private function ensureDefaultActive(int $userId): void
    {
        $activeId = $this->getActiveId();

        if ($activeId && $this->userCanAccess($activeId, $userId)) {
            return;
        }

        if ($activeId) {
            session()->remove(self::SESSION_KEY);
        }

        $accessible = $this->getAccessibleList($userId);
        if (empty($accessible)) {
            return;
        }

        session()->set(self::SESSION_KEY, (int) $accessible[0]->id_proyek);
    }

    public function getAccessibleList(int $userId): array
    {
        $isAdmin = in_groups(['1']);
        $rows = $this->proyekRepository->getAccessibleForUser($userId, $isAdmin);

        foreach ($rows as $row) {
            $this->enrichProyek($row);
        }

        return $rows;
    }

    public function setActive(int $idProyek): array
    {
        $userId = (int) user_id();

        if ($idProyek <= 0) {
            return ['success' => false, 'message' => 'Proyek tidak valid.'];
        }

        $proyek = $this->proyekRepository->getById($idProyek);
        if (!$proyek) {
            return ['success' => false, 'message' => 'Proyek tidak ditemukan.'];
        }

        if (!$this->userCanAccessProyek($proyek, $userId)) {
            return ['success' => false, 'message' => 'Anda tidak memiliki akses ke proyek ini.'];
        }

        session()->set(self::SESSION_KEY, $idProyek);
        $this->dismissSelectionPicker();

        return [
            'success' => true,
            'message' => 'Proyek aktif berhasil diubah.',
            'proyek'  => $this->enrichProyek($proyek),
        ];
    }

    public function userCanAccess(int $idProyek, int $userId): bool
    {
        if (in_groups(['1'])) {
            return $this->proyekRepository->getById($idProyek) !== null;
        }

        $proyek = $this->proyekRepository->getById($idProyek);

        return $this->userCanAccessProyek($proyek, $userId);
    }

    private function userCanAccessProyek(?object $proyek, int $userId): bool
    {
        if (!$proyek) {
            return false;
        }

        if (in_groups(['1'])) {
            return true;
        }

        if (!isset($proyek->id_users) || $proyek->id_users === null || $proyek->id_users === '') {
            return false;
        }

        $allowedUserIds = array_filter(array_map('trim', explode(',', (string) $proyek->id_users)));

        return in_array((string) $userId, $allowedUserIds, true);
    }

    private function enrichProyek(?object $proyek): ?object
    {
        if (!$proyek) {
            return null;
        }

        $id = (int) $proyek->id_proyek;
        $proyek->logo_access_url = $this->fileAccessService->accessUrl('proyek_logo', $id);
        $proyek->siteplan_access_url = $this->fileAccessService->accessUrl('proyek_siteplan', $id);

        return $proyek;
    }
}
