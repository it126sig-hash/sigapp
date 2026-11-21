<?php

namespace App\Controllers\Api\Notifikasi;

use App\Controllers\Api\BaseApiController;
use App\Repositories\Notifikasi\NotificationRepository;
use App\Services\FileAccessService;
use App\Services\Notifikasi\NotifikasiService;
use App\Services\SiteplanUrgentService;
use App\Support\NotificationTextFormatter;
use CodeIgniter\HTTP\ResponseInterface;

class NotifikasiController extends BaseApiController
{
    protected $group_id;
    protected NotificationRepository $notificationRepository;
    protected SiteplanUrgentService $siteplanUrgentService;
    protected NotifikasiService $notifikasiService;
    protected FileAccessService $fileAccessService;

    public function __construct(
        ?NotificationRepository $notificationRepository = null,
        ?SiteplanUrgentService $siteplanUrgentService = null,
        ?NotifikasiService $notifikasiService = null,
        ?FileAccessService $fileAccessService = null
    ) {
        $this->notificationRepository = $notificationRepository ?? new NotificationRepository();
        $this->siteplanUrgentService = $siteplanUrgentService ?? new SiteplanUrgentService();
        $this->notifikasiService = $notifikasiService ?? new NotifikasiService();
        $this->fileAccessService = $fileAccessService ?? new FileAccessService();

        $groupId = (int) (session()->group_id ?? 0);
        if ($groupId <= 0 && function_exists('user_id') && user_id()) {
            $groupId = $this->notificationRepository->getUserGroupId((int) user_id());
            if ($groupId > 0) {
                session()->set('group_id', $groupId);
            }
        }

        $this->group_id = $groupId === 1 ? '' : $groupId;
    }

    public function tambah_notif(
        $target = null,
        $notif = null,
        $add_by = null,
        $id_kavling = null,
        $id_konsumen = null,
        $type = null,
        $id_proyek = null,
        ?string $actionUrl = null,
        ?string $eventType = null
    ) {
        if ($target === null && $this->request && $this->request->getPost('target') !== null) {
            $target = $this->request->getPost('target');
            $notif = $this->request->getPost('notif');
            $add_by = $this->request->getPost('add_by') ?: (function_exists('user_id') ? user_id() : 0);
            $id_kavling = $this->request->getPost('id_kavling');
            $id_konsumen = $this->request->getPost('id_konsumen');
            $type = $this->request->getPost('type');
            $id_proyek = $this->request->getPost('id_proyek');
            $actionUrl = $this->request->getPost('action_url');
            $eventType = $this->request->getPost('event_type');
        }

        $createdId = $this->notifikasiService->tambah_notif(
            $target,
            $notif,
            $add_by,
            $id_kavling,
            $id_konsumen,
            $type,
            $id_proyek,
            $actionUrl,
            $eventType
        );

        if ($this->isRawApiRequest()) {
            return $this->success(['id' => $createdId], 'Notifikasi berhasil ditambahkan.');
        }

        return $createdId;
    }

    public function summary()
    {
        $idProyek = $this->resolveIdProyek();
        $activityUnreadCount = $this->getUnreadActivityCount($idProyek > 0 ? $idProyek : null);

        $payload = [
            'token' => csrf_hash(),
            'urgent_total' => 0,
            'activity_unread_count' => $activityUnreadCount,
            'badge_total' => $activityUnreadCount,
            'summary_only' => true,
        ];

        if ($this->isRawApiRequest()) {
            return $this->success($payload);
        }

        return $this->response->setJSON($payload);
    }

    public function getSummary()
    {
        return $this->summary();
    }

    public function center()
    {
        $idProyek = $this->resolveIdProyek();
        $groupId = $this->getCurrentGroupId();
        $userId = function_exists('user_id') ? (int) user_id() : 0;

        $urgent = $idProyek > 0
            ? $this->siteplanUrgentService->getUrgentSummary($idProyek, $groupId, $userId)
            : $this->siteplanUrgentService->emptySummary();

        $activity = $this->sanitizeActivityItems($this->getActivity(false, 0, $idProyek > 0 ? $idProyek : null, 10));
        $activityUnreadCount = $this->getUnreadActivityCount($idProyek > 0 ? $idProyek : null);
        $urgentTotal = (int) ($urgent['total'] ?? 0);

        $payload = [
            'token' => csrf_hash(),
            'urgent_total' => $urgentTotal,
            'activity_unread_count' => $activityUnreadCount,
            'badge_total' => $urgentTotal + $activityUnreadCount,
            'urgent' => $urgent,
            'activity' => [
                'items' => $activity,
            ],
        ];

        if ($this->isRawApiRequest()) {
            return $this->success($payload);
        }

        return $this->response->setJSON($payload);
    }

    public function getCenter()
    {
        return $this->center();
    }

    public function snooze()
    {
        $idProyek = (int) ($this->request->getPost('id_proyek') ?: session()->get('id_proyek'));
        $itemKey = trim((string) $this->request->getPost('item_key'));
        $minutes = (int) $this->request->getPost('minutes');
        $allowedMinutes = [15, 60, 240];

        if ($idProyek <= 0 || $itemKey === '' || !in_array($minutes, $allowedMinutes, true)) {
            if ($this->isRawApiRequest()) {
                return $this->error('Data snooze tidak valid.', 422);
            }

            return $this->response->setStatusCode(422)->setJSON([
                'success' => false,
                'token' => csrf_hash(),
                'messages' => 'Data snooze tidak valid',
            ]);
        }

        $userId = function_exists('user_id') ? (int) user_id() : 0;
        $success = $this->siteplanUrgentService->snoozeUrgentItem($userId, $idProyek, $itemKey, $minutes);

        if ($this->isRawApiRequest()) {
            return $success
                ? $this->success(null, 'Notifikasi urgent ditunda.')
                : $this->error('Gagal menunda notifikasi.', 500);
        }

        return $this->response->setJSON([
            'success' => $success,
            'token' => csrf_hash(),
            'messages' => $success ? 'Notifikasi urgent ditunda' : 'Gagal menunda notifikasi',
        ]);
    }

    public function load()
    {
        $all = (bool) $this->request->getVar('all');
        $offset = (int) ($this->request->getVar('offset') ?? 0);
        $rawIdProyek = $this->request->getVar('id_proyek');
        $idProyek = $rawIdProyek === 'all' ? 0 : (int) ($rawIdProyek ?: session()->get('id_proyek'));

        $items = $this->sanitizeActivityItems($this->getActivity($all, $offset, $idProyek > 0 ? $idProyek : null));

        if ($this->isRawApiRequest()) {
            return $this->success([
                'items' => $items,
                'offset' => $offset,
            ]);
        }

        return $this->response->setJSON([
            'token' => csrf_hash(),
            'notif' => $items,
        ]);
    }

    public function loadNotif($all = false)
    {
        return $this->load();
    }

    public function getNotif($all = false)
    {
        $offset = 0;
        $items = $this->sanitizeActivityItems($this->getActivity($all, $offset));
        $unreadCount = $this->getUnreadActivityCount();

        return $this->response->setJSON([
            'token' => csrf_hash(),
            'notif' => $items,
            'unread_count' => $unreadCount,
        ]);
    }

    public function list(): ResponseInterface
    {
        $all = (bool) $this->request->getGet('all');
        $offset = (int) ($this->request->getGet('offset') ?? 0);
        $limit = (int) ($this->request->getGet('limit') ?? 10);
        $rawIdProyek = $this->request->getGet('id_proyek');
        $idProyek = $rawIdProyek === 'all' ? 0 : (int) ($rawIdProyek ?: session()->get('id_proyek'));

        $items = $this->sanitizeActivityItems($this->getActivity($all, $offset, $idProyek > 0 ? $idProyek : null, $limit));
        $unreadCount = $this->getUnreadActivityCount($idProyek > 0 ? $idProyek : null);

        return $this->success([
            'items' => $items,
            'unread_count' => $unreadCount,
            'offset' => $offset,
            'limit' => $limit,
        ]);
    }

    public function markAsRead($id)
    {
        $userId = function_exists('user_id') ? (int) user_id() : 0;
        $success = $this->notificationRepository->markAsReadForUser((int) $id, $userId);

        if (! $success) {
            if ($this->isRawApiRequest()) {
                return $this->error('Notifikasi tidak ditemukan atau sudah dibaca.', 404);
            }

            return $this->response->setStatusCode(404)->setJSON([
                'status' => 'not_found',
                'token' => csrf_hash(),
            ]);
        }

        if ($this->isRawApiRequest()) {
            return $this->success(null, 'Notifikasi ditandai sudah dibaca.');
        }

        return $this->response->setJSON(['status' => 'success', 'token' => csrf_hash()]);
    }

    public function markAllAsRead()
    {
        $userId = function_exists('user_id') ? (int) user_id() : 0;
        $success = $this->notificationRepository->markAllAsReadForUser($userId);

        if ($this->isRawApiRequest()) {
            return $this->success(null, 'Semua notifikasi ditandai sudah dibaca.');
        }

        return $this->response->setJSON(['status' => 'success', 'token' => csrf_hash()]);
    }

    public function getActivity($all = false, $offset = null, $id_proyek = null, $limit = 10): array
    {
        $userId = function_exists('user_id') ? (int) user_id() : 0;

        return $this->notificationRepository->listForUser(
            $userId,
            $this->getCurrentGroupId(),
            $id_proyek ? (int) $id_proyek : null,
            (int) ($offset ?? 0),
            (int) $limit,
            (bool) $all
        );
    }

    public function getActivityRoute()
    {
        $all = (bool) $this->request->getVar('all');
        $offset = (int) ($this->request->getVar('offset') ?? 0);
        $rawIdProyek = $this->request->getVar('id_proyek');
        $idProyek = $rawIdProyek === 'all' ? 0 : (int) ($rawIdProyek ?: session()->get('id_proyek'));

        $items = $this->sanitizeActivityItems($this->getActivity($all, $offset, $idProyek > 0 ? $idProyek : null));

        if ($this->isRawApiRequest()) {
            return $this->success([
                'items' => $items,
            ]);
        }

        return $this->response->setJSON([
            'token' => csrf_hash(),
            'notif' => $items,
        ]);
    }

    protected function resolveIdProyek(): int
    {
        $rawIdProyek = $this->request->getGet('id_proyek');
        if ($rawIdProyek === 'all') {
            return 0;
        }

        return (int) ($rawIdProyek ?: session()->get('id_proyek'));
    }

    protected function isRawApiRequest(): bool
    {
        $uri = $this->request->getUri()->getPath();
        $format = $this->request->getGet('format');

        return str_contains($uri, 'api/notif') || $format === 'raw';
    }

    protected function getCurrentGroupId(): int
    {
        $groupId = (int) (session()->group_id ?? 0);
        if ($groupId > 0) {
            return $groupId;
        }

        $userId = function_exists('user_id') ? (int) user_id() : 0;
        $groupId = $this->notificationRepository->getUserGroupId($userId);
        if ($groupId > 0) {
            session()->set('group_id', $groupId);
            $this->group_id = $groupId === 1 ? '' : $groupId;
        }

        return $groupId;
    }

    protected function getUnreadActivityCount(?int $idProyek = null): int
    {
        $userId = function_exists('user_id') ? (int) user_id() : 0;

        return $this->notificationRepository->unreadCountForUser(
            $userId,
            $this->getCurrentGroupId(),
            $idProyek
        );
    }

    protected function sanitizeActivityItems(array $items): array
    {
        foreach ($items as &$item) {
            if (is_object($item)) {
                $item->notif_text = $this->plainNotificationText($item->notif ?? '', $item->nama_jalan ?? null, $item->no_kavling ?? null);
                if (!empty($item->id_proyek)) {
                    $item->logo_access_url = $this->fileAccessService->accessUrl('proyek_logo', (int) $item->id_proyek);
                }
            } elseif (is_array($item)) {
                $item['notif_text'] = $this->plainNotificationText($item['notif'] ?? '', $item['nama_jalan'] ?? null, $item['no_kavling'] ?? null);
                if (!empty($item['id_proyek'])) {
                    $item['logo_access_url'] = $this->fileAccessService->accessUrl('proyek_logo', (int) $item['id_proyek']);
                }
            }
        }

        return $items;
    }

    protected function plainNotificationText($value, $namaJalan = null, $noKavling = null): string
    {
        return NotificationTextFormatter::withKavling($value, $namaJalan, $noKavling);
    }
}
