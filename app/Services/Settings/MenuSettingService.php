<?php

namespace App\Services\Settings;

use App\Repositories\Settings\MenuRepository;

class MenuSettingService
{
    protected MenuRepository $menuRepo;
    protected SiteplanMenuService $siteplanMenu;
    protected $validation;

    public function __construct()
    {
        $this->menuRepo = new MenuRepository();
        $this->siteplanMenu = new SiteplanMenuService();
        $this->validation = \Config\Services::validation();
    }

    public function getIndexData(): array
    {
        return [
            'groups'          => $this->menuRepo->getGroups(),
            'users'           => $this->menuRepo->getUsers(),
            'parent_options'  => $this->menuRepo->getParentOptions(),
            'siteplan_groups' => $this->siteplanMenu->groupOptions(),
        ];
    }

    public function getMenuList(): array
    {
        $data = [];
        foreach ($this->menuRepo->getFlatMenus(false) as $key => $menu) {
            $status = ((int) $menu->is_active === 1)
                ? '<span class="badge badge-pill badge-success">Aktif</span>'
                : '<span class="badge badge-pill badge-danger">Tidak Aktif</span>';
            $type = ((int) $menu->parent_id === 0)
                ? '<span class="badge badge-pill badge-primary">Parent</span>'
                : '<span class="badge badge-pill badge-warning">Sub Menu</span>';
            $toggleIcon = ((int) $menu->is_active === 1) ? 'fa-times' : 'fa-check';
            $ops = '<div class="btn-group">';
            $ops .= '<button type="button" class="btn btn-sm btn-info" onclick="editMenu(' . (int) $menu->id . ')"><i class="fa fa-edit"></i></button>';
            $ops .= '<button type="button" class="btn btn-sm btn-danger" onclick="toggleMenu(' . (int) $menu->id . ')"><i class="fa ' . $toggleIcon . '"></i></button>';
            $ops .= '</div>';

            $data[$key] = [
                (int) $menu->id,
                str_repeat('&nbsp;&nbsp;&nbsp;', (int) $menu->depth) . esc($menu->name),
                $type,
                esc($menu->url),
                esc($menu->icon),
                (int) $menu->sort_order,
                $status,
                $ops,
            ];
        }

        return [
            'token' => csrf_hash(),
            'data'  => $data,
        ];
    }

    public function getMenu(int $id): ?object
    {
        $menu = $this->menuRepo->findMenuById($id);
        if (!$menu) {
            return null;
        }

        $menu->token = csrf_hash();
        $menu->success = true;

        return $menu;
    }

    public function saveMenu(array $postData): array
    {
        $id = (int) ($postData['id'] ?? 0);
        $parentId = (int) ($postData['parent_id'] ?? 0);
        $name = trim((string) ($postData['name'] ?? ''));
        $slug = trim((string) ($postData['slug'] ?? ''));
        if ($slug === '') {
            $slug = $this->makeSlug($name);
        }

        $fields = [
            'name'       => $name,
            'url'        => trim((string) ($postData['url'] ?? '')),
            'icon'       => trim((string) ($postData['icon'] ?? '')),
            'slug'       => $slug,
            'parent_id'  => $parentId,
            'is_active'  => ((int) ($postData['is_active'] ?? 0)) === 1 ? 1 : 0,
            'sort_order' => (int) ($postData['sort_order'] ?? 0),
            'date_edit'  => date('Y-m-d H:i:s'),
        ];

        $this->validation->setRules([
            'name'       => ['label' => 'Nama menu', 'rules' => 'required|max_length[255]'],
            'url'        => ['label' => 'URL', 'rules' => 'required|max_length[255]'],
            'icon'       => ['label' => 'Icon', 'rules' => 'permit_empty|max_length[255]'],
            'parent_id'  => ['label' => 'Parent', 'rules' => 'permit_empty|integer'],
            'is_active'  => ['label' => 'Status', 'rules' => 'required|in_list[0,1]'],
            'sort_order' => ['label' => 'Urutan', 'rules' => 'permit_empty|integer'],
        ]);

        if (!$this->validation->run($fields)) {
            return [
                'success'  => false,
                'token'    => csrf_hash(),
                'messages' => $this->validation->listErrors(),
            ];
        }

        if ($parentId > 0) {
            $parentMenu = $this->menuRepo->findMenuById($parentId);
            if (!$parentMenu) {
                return [
                    'success'  => false,
                    'token'    => csrf_hash(),
                    'messages' => 'Parent menu tidak valid',
                ];
            }

            if ((int) $parentMenu->parent_id !== 0) {
                return [
                    'success'  => false,
                    'token'    => csrf_hash(),
                    'messages' => 'Parent menu harus menu utama, bukan sub menu',
                ];
            }
        }

        if ($id > 0 && ($parentId === $id || $this->menuRepo->isDescendant($id, $parentId))) {
            return [
                'success'  => false,
                'token'    => csrf_hash(),
                'messages' => 'Parent menu tidak boleh menu sendiri atau turunannya',
            ];
        }

        if ($id > 0) {
            if (!$this->menuRepo->findMenuById($id)) {
                return [
                    'success'  => false,
                    'token'    => csrf_hash(),
                    'messages' => 'Menu tidak ditemukan',
                ];
            }
            $this->menuRepo->updateMenu($id, $fields);
            $message = 'Menu berhasil diperbaharui';
        } else {
            $fields['date_add'] = date('Y-m-d H:i:s');
            $this->menuRepo->insertMenu($fields);
            $message = 'Menu berhasil ditambahkan';
        }

        return [
            'success'        => true,
            'token'          => csrf_hash(),
            'messages'       => $message,
            'parent_options' => $this->menuRepo->getParentOptions(),
        ];
    }

    public function toggleMenu(int $id): array
    {
        $menu = $this->menuRepo->findMenuById($id);
        if (!$menu) {
            return [
                'success'  => false,
                'token'    => csrf_hash(),
                'messages' => 'Menu tidak ditemukan',
            ];
        }

        $this->menuRepo->updateMenu($id, [
            'is_active' => ((int) $menu->is_active === 1) ? 0 : 1,
            'date_edit' => date('Y-m-d H:i:s'),
        ]);

        return [
            'success'  => true,
            'token'    => csrf_hash(),
            'messages' => 'Status menu berhasil diperbaharui',
        ];
    }

    public function getSiteplanMenuList(): array
    {
        $data = [];
        foreach ($this->siteplanMenu->getFlatItems(false) as $key => $menu) {
            $status = ((int) $menu->is_active === 1)
                ? '<span class="badge badge-pill badge-success">Aktif</span>'
                : '<span class="badge badge-pill badge-danger">Tidak Aktif</span>';
            $toggleIcon = ((int) $menu->is_active === 1) ? 'fa-times' : 'fa-check';
            $ops = '<div class="btn-group">';
            $ops .= '<button type="button" class="btn btn-sm btn-info" onclick="editSiteplanMenu(' . (int) $menu->id . ')"><i class="fa fa-edit"></i></button>';
            $ops .= '<button type="button" class="btn btn-sm btn-danger" onclick="toggleSiteplanMenu(' . (int) $menu->id . ')"><i class="fa ' . $toggleIcon . '"></i></button>';
            $ops .= '</div>';

            $data[$key] = [
                (int) $menu->id,
                esc($this->siteplanMenu->groupLabel((int) $menu->id_group)),
                esc((string) $menu->group_label),
                esc((string) $menu->label),
                esc((string) $menu->icon),
                esc((string) $menu->btn_class),
                esc((string) $menu->onclick),
                (int) $menu->sort_order,
                $status,
                $ops,
            ];
        }

        return [
            'token' => csrf_hash(),
            'data'  => $data,
        ];
    }

    public function getSiteplanMenu(int $id): ?object
    {
        $menu = $this->siteplanMenu->findItem($id);
        if (!$menu) {
            return null;
        }

        $menu->success = true;
        $menu->token = csrf_hash();
        $menu->role_ids = $this->siteplanMenu->getItemRoleIds($id);

        return $menu;
    }

    public function saveSiteplanMenu(array $postData): array
    {
        $id = (int) ($postData['id'] ?? 0);
        $label = trim((string) ($postData['label'] ?? ''));
        $itemKey = trim((string) ($postData['item_key'] ?? ''));
        if ($itemKey === '') {
            $itemKey = $this->makeSlug($label);
        }

        $fields = [
            'item_key'    => $itemKey,
            'id_group'    => (int) ($postData['id_group'] ?? 0),
            'label'       => $label,
            'group_label' => trim((string) ($postData['group_label'] ?? '')),
            'onclick'     => trim((string) ($postData['onclick'] ?? '')),
            'icon'        => trim((string) ($postData['icon'] ?? '')),
            'btn_class'   => trim((string) ($postData['btn_class'] ?? '')) ?: 'btn-primary',
            'sort_order'  => (int) ($postData['sort_order'] ?? 0),
            'is_active'   => ((int) ($postData['is_active'] ?? 0)) === 1 ? 1 : 0,
            'extra_id'    => trim((string) ($postData['extra_id'] ?? '')),
            'extra_class' => trim((string) ($postData['extra_class'] ?? '')),
        ];

        $this->validation->setRules([
            'item_key'   => ['label' => 'Key', 'rules' => 'required|alpha_dash|max_length[120]'],
            'id_group'   => ['label' => 'Departemen tampilan', 'rules' => 'permit_empty|integer'],
            'label'      => ['label' => 'Label', 'rules' => 'required|max_length[160]'],
            'btn_class'  => ['label' => 'Class warna', 'rules' => 'required|max_length[120]'],
            'sort_order' => ['label' => 'Urutan', 'rules' => 'permit_empty|integer'],
            'is_active'  => ['label' => 'Status', 'rules' => 'required|in_list[0,1]'],
        ]);

        if (!$this->validation->run($fields)) {
            return [
                'success'  => false,
                'token'    => csrf_hash(),
                'messages' => $this->validation->listErrors(),
            ];
        }

        if ($this->menuRepo->checkDuplicateSiteplanItemKey($fields['item_key'], $id)) {
            return [
                'success'  => false,
                'token'    => csrf_hash(),
                'messages' => 'Key menu siteplan sudah dipakai',
            ];
        }

        $roleIds = $postData['role_ids'] ?? [];
        if (!is_array($roleIds) || !$roleIds) {
            $roleIds = [$fields['id_group']];
        }

        $itemId = $this->siteplanMenu->saveItem($fields, $id);
        $this->siteplanMenu->syncRoles($itemId, $roleIds);

        return [
            'success'  => true,
            'token'    => csrf_hash(),
            'messages' => $id > 0 ? 'Menu siteplan berhasil diperbaharui' : 'Menu siteplan berhasil ditambahkan',
        ];
    }

    public function toggleSiteplanMenu(int $id): array
    {
        if (!$this->siteplanMenu->toggleItem($id)) {
            return [
                'success'  => false,
                'token'    => csrf_hash(),
                'messages' => 'Menu siteplan tidak ditemukan',
            ];
        }

        return [
            'success'  => true,
            'token'    => csrf_hash(),
            'messages' => 'Status menu siteplan berhasil diperbaharui',
        ];
    }

    public function makeSlug(string $name): string
    {
        $slug = strtolower(trim($name));
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);

        return trim((string) $slug, '-');
    }
}
