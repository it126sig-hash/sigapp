<?php

namespace App\Controllers\Api\Settings;

use App\Controllers\Api\BaseApiController;
use App\Services\Settings\MenuAccessService;
use App\Services\Settings\MenuSettingService;
use App\Repositories\Settings\MenuRepository;

class MenuSettingController extends BaseApiController
{
    protected MenuSettingService $menuSettingService;
    protected MenuAccessService $menuAccessService;
    protected MenuRepository $menuRepo;

    public function __construct()
    {
        $this->menuSettingService = new MenuSettingService();
        $this->menuAccessService  = new MenuAccessService();
        $this->menuRepo           = new MenuRepository();
    }

    public function menuList()
    {
        return $this->respond($this->menuSettingService->getMenuList());
    }

    public function menuGet()
    {
        $id = (int) $this->request->getPost('id');
        $menu = $this->menuSettingService->getMenu($id);

        if (!$menu) {
            return $this->respond([
                'success'  => false,
                'token'    => csrf_hash(),
                'messages' => 'Menu tidak ditemukan',
            ]);
        }

        return $this->respond($menu);
    }

    public function menuSave()
    {
        $result = $this->menuSettingService->saveMenu((array) $this->request->getPost());
        return $this->respond($result);
    }

    public function menuToggle()
    {
        $id = (int) $this->request->getPost('id');
        $result = $this->menuSettingService->toggleMenu($id);
        return $this->respond($result);
    }

    public function groupAccess()
    {
        $groupId = (int) $this->request->getPost('group_id');
        if (!$this->menuRepo->groupExists($groupId)) {
            return $this->respond([
                'success'  => false,
                'token'    => csrf_hash(),
                'messages' => 'Departemen tidak valid',
            ]);
        }

        return $this->respond([
            'success'  => true,
            'token'    => csrf_hash(),
            'menus'    => $this->menuRepo->getFlatMenus(true),
            'selected' => $this->menuAccessService->getGroupMenuIds([$groupId]),
        ]);
    }

    public function groupSave()
    {
        $groupId = (int) $this->request->getPost('group_id');
        if (!$this->menuRepo->groupExists($groupId)) {
            return $this->respond([
                'success'  => false,
                'token'    => csrf_hash(),
                'messages' => 'Departemen tidak valid',
            ]);
        }

        $menuIds = $this->request->getPost('menu_ids') ?? [];
        $this->menuAccessService->syncGroupMenus($groupId, is_array($menuIds) ? $menuIds : []);

        return $this->respond([
            'success'  => true,
            'token'    => csrf_hash(),
            'messages' => 'Akses departemen berhasil disimpan',
        ]);
    }

    public function userAccess()
    {
        $userId = (int) $this->request->getPost('user_id');
        if (!$this->menuRepo->userExists($userId)) {
            return $this->respond([
                'success'  => false,
                'token'    => csrf_hash(),
                'messages' => 'User tidak valid',
            ]);
        }

        $overrides = $this->menuAccessService->getUserOverrides($userId);

        return $this->respond([
            'success'        => true,
            'token'          => csrf_hash(),
            'menus'          => $this->menuRepo->getFlatMenus(true),
            'allow'          => $overrides['allow'],
            'deny'           => $overrides['deny'],
            'group_menu_ids' => $this->menuAccessService->getGroupMenuIds($this->menuAccessService->getUserGroupIds($userId)),
        ]);
    }

    public function userSave()
    {
        $userId = (int) $this->request->getPost('user_id');
        if (!$this->menuRepo->userExists($userId)) {
            return $this->respond([
                'success'  => false,
                'token'    => csrf_hash(),
                'messages' => 'User tidak valid',
            ]);
        }

        $allowIds = $this->request->getPost('allow_ids') ?? [];
        $denyIds = $this->request->getPost('deny_ids') ?? [];
        $this->menuAccessService->syncUserOverrides(
            $userId,
            is_array($allowIds) ? $allowIds : [],
            is_array($denyIds) ? $denyIds : []
        );

        return $this->respond([
            'success'  => true,
            'token'    => csrf_hash(),
            'messages' => 'Akses user berhasil disimpan',
        ]);
    }

    public function siteplanMenuList()
    {
        return $this->respond($this->menuSettingService->getSiteplanMenuList());
    }

    public function siteplanMenuGet()
    {
        $id = (int) $this->request->getPost('id');
        $menu = $this->menuSettingService->getSiteplanMenu($id);

        if (!$menu) {
            return $this->respond([
                'success'  => false,
                'token'    => csrf_hash(),
                'messages' => 'Menu siteplan tidak ditemukan',
            ]);
        }

        return $this->respond($menu);
    }

    public function siteplanMenuSave()
    {
        $result = $this->menuSettingService->saveSiteplanMenu((array) $this->request->getPost());
        return $this->respond($result);
    }

    public function siteplanMenuToggle()
    {
        $id = (int) $this->request->getPost('id');
        $result = $this->menuSettingService->toggleSiteplanMenu($id);
        return $this->respond($result);
    }
}
