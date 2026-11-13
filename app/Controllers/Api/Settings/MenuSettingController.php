<?php

namespace App\Controllers\Api\Settings;

use App\Controllers\Api\BaseApiController;
use App\Services\Settings\MenuAccessService;
use App\Services\Settings\MenuSettingService;
use App\Repositories\Settings\MenuRepository;

class MenuSettingController extends BaseApiController
{
    private const MENU_URL = 'menu-setting';

    protected MenuSettingService $menuSettingService;
    protected MenuAccessService $menuAccessService;
    protected MenuRepository $menuRepo;

    public function __construct()
    {
        $this->menuSettingService = new MenuSettingService();
        $this->menuAccessService  = new MenuAccessService();
        $this->menuRepo           = new MenuRepository();
    }

    public function meta()
    {
        if ($denied = $this->ensureMenuAccess(self::MENU_URL)) {
            return $denied;
        }

        return $this->success(array_merge(
            $this->menuSettingService->getIndexData(),
            ['permissions' => ['can_manage' => true]]
        ), 'Metadata menu setting berhasil dimuat');
    }

    public function menuList()
    {
        if ($denied = $this->ensureMenuAccess(self::MENU_URL)) {
            return $denied;
        }

        if ($this->isRawFormat()) {
            $params = $this->readRequestData();
            $params['format'] = 'raw';

            return $this->success(
                $this->menuSettingService->getMenuList($params),
                'Data menu berhasil dimuat'
            );
        }

        return $this->respond($this->menuSettingService->getMenuList());
    }

    public function menuGet()
    {
        if ($denied = $this->ensureMenuAccess(self::MENU_URL)) {
            return $denied;
        }

        $input = $this->readRequestData();
        $id = (int) ($input['id'] ?? 0);
        $raw = $this->isRawFormat();
        $menu = $this->menuSettingService->getMenu($id, $raw);

        if (!$menu) {
            if ($raw) {
                return $this->error('Menu tidak ditemukan', 404);
            }

            return $this->respond([
                'success'  => false,
                'token'    => csrf_hash(),
                'messages' => 'Menu tidak ditemukan',
            ]);
        }

        return $raw ? $this->success($menu) : $this->respond($menu);
    }

    public function menuSave()
    {
        if ($denied = $this->ensureMenuAccess(self::MENU_URL)) {
            return $denied;
        }

        $raw = $this->isRawFormat();
        $result = $this->menuSettingService->saveMenu($this->readRequestData(), $raw);

        return $this->respondResult($result, $raw);
    }

    public function menuToggle()
    {
        if ($denied = $this->ensureMenuAccess(self::MENU_URL)) {
            return $denied;
        }

        $input = $this->readRequestData();
        $id = (int) ($input['id'] ?? 0);
        $raw = $this->isRawFormat();
        $result = $this->menuSettingService->toggleMenu($id, $raw);

        return $this->respondResult($result, $raw);
    }

    public function groupAccess()
    {
        if ($denied = $this->ensureMenuAccess(self::MENU_URL)) {
            return $denied;
        }

        $input = $this->readRequestData();
        $groupId = (int) ($input['group_id'] ?? 0);
        $raw = $this->isRawFormat();

        if (!$this->menuRepo->groupExists($groupId)) {
            if ($raw) {
                $status = $groupId <= 0 ? 422 : 404;
                return $this->error('Departemen tidak valid', $status, ['group_id' => 'Departemen tidak valid']);
            }

            return $this->respond([
                'success'  => false,
                'token'    => csrf_hash(),
                'messages' => 'Departemen tidak valid',
            ]);
        }

        $payload = [
            'menus'    => $this->menuRepo->getFlatMenus(true),
            'selected' => $this->menuAccessService->getGroupMenuIds([$groupId]),
        ];

        if ($raw) {
            return $this->success($payload, 'Akses departemen berhasil dimuat');
        }

        return $this->respond(array_merge([
            'success' => true,
            'token'   => csrf_hash(),
        ], $payload));
    }

    public function groupSave()
    {
        if ($denied = $this->ensureMenuAccess(self::MENU_URL)) {
            return $denied;
        }

        $input = $this->readRequestData();
        $groupId = (int) ($input['group_id'] ?? 0);
        $raw = $this->isRawFormat();

        if (!$this->menuRepo->groupExists($groupId)) {
            if ($raw) {
                $status = $groupId <= 0 ? 422 : 404;
                return $this->error('Departemen tidak valid', $status, ['group_id' => 'Departemen tidak valid']);
            }

            return $this->respond([
                'success'  => false,
                'token'    => csrf_hash(),
                'messages' => 'Departemen tidak valid',
            ]);
        }

        $menuIds = $input['menu_ids'] ?? [];
        $menuIds = is_array($menuIds) ? $menuIds : [];
        $this->menuAccessService->syncGroupMenus($groupId, $menuIds);

        if ($raw) {
            return $this->success([
                'group_id' => $groupId,
                'selected' => $this->menuAccessService->getGroupMenuIds([$groupId]),
            ], 'Akses departemen berhasil disimpan');
        }

        return $this->respond([
            'success'  => true,
            'token'    => csrf_hash(),
            'messages' => 'Akses departemen berhasil disimpan',
        ]);
    }

    public function userAccess()
    {
        if ($denied = $this->ensureMenuAccess(self::MENU_URL)) {
            return $denied;
        }

        $input = $this->readRequestData();
        $userId = (int) ($input['user_id'] ?? 0);
        $raw = $this->isRawFormat();

        if (!$this->menuRepo->userExists($userId)) {
            if ($raw) {
                $status = $userId <= 0 ? 422 : 404;
                return $this->error('User tidak valid', $status, ['user_id' => 'User tidak valid']);
            }

            return $this->respond([
                'success'  => false,
                'token'    => csrf_hash(),
                'messages' => 'User tidak valid',
            ]);
        }

        $overrides = $this->menuAccessService->getUserOverrides($userId);
        $payload = [
            'menus'          => $this->menuRepo->getFlatMenus(true),
            'allow'          => $overrides['allow'],
            'deny'           => $overrides['deny'],
            'group_menu_ids' => $this->menuAccessService->getGroupMenuIds($this->menuAccessService->getUserGroupIds($userId)),
        ];

        if ($raw) {
            return $this->success($payload, 'Akses user berhasil dimuat');
        }

        return $this->respond(array_merge([
            'success' => true,
            'token'   => csrf_hash(),
        ], $payload));
    }

    public function userSave()
    {
        if ($denied = $this->ensureMenuAccess(self::MENU_URL)) {
            return $denied;
        }

        $input = $this->readRequestData();
        $userId = (int) ($input['user_id'] ?? 0);
        $raw = $this->isRawFormat();

        if (!$this->menuRepo->userExists($userId)) {
            if ($raw) {
                $status = $userId <= 0 ? 422 : 404;
                return $this->error('User tidak valid', $status, ['user_id' => 'User tidak valid']);
            }

            return $this->respond([
                'success'  => false,
                'token'    => csrf_hash(),
                'messages' => 'User tidak valid',
            ]);
        }

        $allowIds = $input['allow_ids'] ?? [];
        $denyIds = $input['deny_ids'] ?? [];
        $this->menuAccessService->syncUserOverrides(
            $userId,
            is_array($allowIds) ? $allowIds : [],
            is_array($denyIds) ? $denyIds : []
        );

        if ($raw) {
            $overrides = $this->menuAccessService->getUserOverrides($userId);
            return $this->success([
                'user_id' => $userId,
                'allow'   => $overrides['allow'],
                'deny'    => $overrides['deny'],
            ], 'Akses user berhasil disimpan');
        }

        return $this->respond([
            'success'  => true,
            'token'    => csrf_hash(),
            'messages' => 'Akses user berhasil disimpan',
        ]);
    }

    public function siteplanMenuList()
    {
        if ($denied = $this->ensureMenuAccess(self::MENU_URL)) {
            return $denied;
        }

        if ($this->isRawFormat()) {
            $params = $this->readRequestData();
            $params['format'] = 'raw';

            return $this->success(
                $this->menuSettingService->getSiteplanMenuList($params),
                'Data menu siteplan berhasil dimuat'
            );
        }

        return $this->respond($this->menuSettingService->getSiteplanMenuList());
    }

    public function siteplanMenuGet()
    {
        if ($denied = $this->ensureMenuAccess(self::MENU_URL)) {
            return $denied;
        }

        $input = $this->readRequestData();
        $id = (int) ($input['id'] ?? 0);
        $raw = $this->isRawFormat();
        $menu = $this->menuSettingService->getSiteplanMenu($id, $raw);

        if (!$menu) {
            if ($raw) {
                return $this->error('Menu siteplan tidak ditemukan', 404);
            }

            return $this->respond([
                'success'  => false,
                'token'    => csrf_hash(),
                'messages' => 'Menu siteplan tidak ditemukan',
            ]);
        }

        return $raw ? $this->success($menu) : $this->respond($menu);
    }

    public function siteplanMenuSave()
    {
        if ($denied = $this->ensureMenuAccess(self::MENU_URL)) {
            return $denied;
        }

        $raw = $this->isRawFormat();
        $result = $this->menuSettingService->saveSiteplanMenu($this->readRequestData(), $raw);

        return $this->respondResult($result, $raw);
    }

    public function siteplanMenuToggle()
    {
        if ($denied = $this->ensureMenuAccess(self::MENU_URL)) {
            return $denied;
        }

        $input = $this->readRequestData();
        $id = (int) ($input['id'] ?? 0);
        $raw = $this->isRawFormat();
        $result = $this->menuSettingService->toggleSiteplanMenu($id, $raw);

        return $this->respondResult($result, $raw);
    }

    private function respondResult(array $result, bool $raw)
    {
        if (!$raw) {
            return $this->respond($result);
        }

        if (!($result['success'] ?? false)) {
            return $this->error(
                (string) ($result['messages'] ?? 'Permintaan gagal'),
                (int) ($result['status'] ?? 422),
                $result['errors'] ?? null
            );
        }

        if (!empty($result['created'])) {
            return $this->successCreated($result['data'] ?? null, (string) ($result['messages'] ?? 'Created'));
        }

        return $this->success($result['data'] ?? null, (string) ($result['messages'] ?? 'Success'));
    }
}
