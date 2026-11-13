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

    public function getMenuList(array $params = []): array
    {
        $menus = $this->menuRepo->getFlatMenus(false);

        if (($params['format'] ?? '') === 'raw') {
            $paged = $this->paginateItems(
                $menus,
                $params,
                static fn (object $menu): array => [
                    (string) $menu->name,
                    (string) $menu->url,
                    (string) ($menu->slug ?? ''),
                    (string) ($menu->icon ?? ''),
                ]
            );

            $items = array_map(static function (object $menu): array {
                return [
                    'id'         => (int) $menu->id,
                    'parent_id'  => (int) $menu->parent_id,
                    'depth'      => (int) $menu->depth,
                    'name'       => (string) $menu->name,
                    'type'       => ((int) $menu->parent_id === 0) ? 'parent' : 'submenu',
                    'url'        => (string) $menu->url,
                    'icon'       => (string) ($menu->icon ?? ''),
                    'slug'       => (string) ($menu->slug ?? ''),
                    'sort_order' => (int) $menu->sort_order,
                    'is_active'  => (int) $menu->is_active,
                ];
            }, $paged['items']);

            return [
                'items' => $items,
                'meta'  => $paged['meta'],
            ];
        }

        $data = [];
        foreach ($menus as $key => $menu) {
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

    public function getMenu(int $id, bool $raw = false): ?object
    {
        $menu = $this->menuRepo->findMenuById($id);
        if (!$menu) {
            return null;
        }

        if ($raw) {
            return (object) [
                'id'         => (int) $menu->id,
                'name'       => (string) $menu->name,
                'url'        => (string) $menu->url,
                'icon'       => (string) ($menu->icon ?? ''),
                'slug'       => (string) ($menu->slug ?? ''),
                'parent_id'  => (int) $menu->parent_id,
                'is_active'  => (int) $menu->is_active,
                'sort_order' => (int) $menu->sort_order,
            ];
        }

        $menu->token = csrf_hash();
        $menu->success = true;

        return $menu;
    }

    public function saveMenu(array $postData, bool $raw = false): array
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

        $this->validation->reset();
        $this->validation->setRules([
            'name'       => ['label' => 'Nama menu', 'rules' => 'required|max_length[255]'],
            'url'        => ['label' => 'URL', 'rules' => 'required|max_length[255]'],
            'icon'       => ['label' => 'Icon', 'rules' => 'permit_empty|max_length[255]'],
            'parent_id'  => ['label' => 'Parent', 'rules' => 'permit_empty|integer'],
            'is_active'  => ['label' => 'Status', 'rules' => 'required|in_list[0,1]'],
            'sort_order' => ['label' => 'Urutan', 'rules' => 'permit_empty|integer'],
        ]);

        if (!$this->validation->run($fields)) {
            if ($raw) {
                return [
                    'success'  => false,
                    'status'   => 422,
                    'messages' => 'Validasi gagal.',
                    'errors'   => $this->validation->getErrors(),
                ];
            }

            return [
                'success'  => false,
                'token'    => csrf_hash(),
                'messages' => $this->validation->listErrors(),
            ];
        }

        if ($parentId > 0) {
            $parentMenu = $this->menuRepo->findMenuById($parentId);
            if (!$parentMenu) {
                return $this->failSave('Parent menu tidak valid', $raw, 422, ['parent_id' => 'Parent menu tidak valid']);
            }

            if ((int) $parentMenu->parent_id !== 0) {
                return $this->failSave('Parent menu harus menu utama, bukan sub menu', $raw, 422, ['parent_id' => 'Parent menu harus menu utama, bukan sub menu']);
            }
        }

        if ($id > 0 && ($parentId === $id || $this->menuRepo->isDescendant($id, $parentId))) {
            return $this->failSave('Parent menu tidak boleh menu sendiri atau turunannya', $raw, 422, ['parent_id' => 'Parent menu tidak boleh menu sendiri atau turunannya']);
        }

        if ($id > 0) {
            if (!$this->menuRepo->findMenuById($id)) {
                return $this->failSave('Menu tidak ditemukan', $raw, 404);
            }
            $this->menuRepo->updateMenu($id, $fields);
            $savedId = $id;
            $message = 'Menu berhasil diperbaharui';
        } else {
            $fields['date_add'] = date('Y-m-d H:i:s');
            $savedId = $this->menuRepo->insertMenu($fields);
            $message = 'Menu berhasil ditambahkan';
        }

        $parentOptions = $this->menuRepo->getParentOptions();

        if ($raw) {
            return [
                'success'  => true,
                'created'  => $id <= 0,
                'status'   => $id <= 0 ? 201 : 200,
                'messages' => $message,
                'data'     => [
                    'id'             => $savedId,
                    'parent_options' => $parentOptions,
                ],
            ];
        }

        return [
            'success'        => true,
            'token'          => csrf_hash(),
            'messages'       => $message,
            'parent_options' => $parentOptions,
        ];
    }

    public function toggleMenu(int $id, bool $raw = false): array
    {
        $menu = $this->menuRepo->findMenuById($id);
        if (!$menu) {
            return $this->failSave('Menu tidak ditemukan', $raw, 404);
        }

        $newStatus = ((int) $menu->is_active === 1) ? 0 : 1;
        $this->menuRepo->updateMenu($id, [
            'is_active' => $newStatus,
            'date_edit' => date('Y-m-d H:i:s'),
        ]);

        if ($raw) {
            return [
                'success'  => true,
                'messages' => 'Status menu berhasil diperbaharui',
                'data'     => [
                    'id'        => $id,
                    'is_active' => $newStatus,
                ],
            ];
        }

        return [
            'success'  => true,
            'token'    => csrf_hash(),
            'messages' => 'Status menu berhasil diperbaharui',
        ];
    }

    public function getSiteplanMenuList(array $params = []): array
    {
        $itemsRaw = $this->siteplanMenu->getFlatItems(false);

        if (($params['format'] ?? '') === 'raw') {
            $paged = $this->paginateItems(
                $itemsRaw,
                $params,
                function (object $menu): array {
                    return [
                        (string) ($menu->item_key ?? ''),
                        $this->siteplanMenu->groupLabel((int) $menu->id_group),
                        (string) ($menu->group_label ?? ''),
                        (string) ($menu->label ?? ''),
                        (string) ($menu->icon ?? ''),
                        (string) ($menu->btn_class ?? ''),
                        (string) ($menu->onclick ?? ''),
                    ];
                }
            );

            $items = array_map(function (object $menu): array {
                return [
                    'id'               => (int) $menu->id,
                    'item_key'         => (string) ($menu->item_key ?? ''),
                    'id_group'         => (int) $menu->id_group,
                    'department_label' => $this->siteplanMenu->groupLabel((int) $menu->id_group),
                    'group_label'      => (string) ($menu->group_label ?? ''),
                    'label'            => (string) ($menu->label ?? ''),
                    'icon'             => (string) ($menu->icon ?? ''),
                    'btn_class'        => (string) ($menu->btn_class ?? ''),
                    'onclick'          => (string) ($menu->onclick ?? ''),
                    'extra_id'         => (string) ($menu->extra_id ?? ''),
                    'extra_class'      => (string) ($menu->extra_class ?? ''),
                    'sort_order'       => (int) $menu->sort_order,
                    'is_active'        => (int) $menu->is_active,
                ];
            }, $paged['items']);

            return [
                'items' => $items,
                'meta'  => $paged['meta'],
            ];
        }

        $data = [];
        foreach ($itemsRaw as $key => $menu) {
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

    public function getSiteplanMenu(int $id, bool $raw = false): ?object
    {
        $menu = $this->siteplanMenu->findItem($id);
        if (!$menu) {
            return null;
        }

        $roleIds = $this->siteplanMenu->getItemRoleIds($id);

        if ($raw) {
            return (object) [
                'id'          => (int) $menu->id,
                'item_key'    => (string) ($menu->item_key ?? ''),
                'id_group'    => (int) $menu->id_group,
                'group_label' => (string) ($menu->group_label ?? ''),
                'label'       => (string) ($menu->label ?? ''),
                'onclick'     => (string) ($menu->onclick ?? ''),
                'icon'        => (string) ($menu->icon ?? ''),
                'btn_class'   => (string) ($menu->btn_class ?? ''),
                'sort_order'  => (int) $menu->sort_order,
                'is_active'   => (int) $menu->is_active,
                'extra_id'    => (string) ($menu->extra_id ?? ''),
                'extra_class' => (string) ($menu->extra_class ?? ''),
                'role_ids'    => $roleIds,
            ];
        }

        $menu->success = true;
        $menu->token = csrf_hash();
        $menu->role_ids = $roleIds;

        return $menu;
    }

    public function saveSiteplanMenu(array $postData, bool $raw = false): array
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

        $this->validation->reset();
        $this->validation->setRules([
            'item_key'   => ['label' => 'Key', 'rules' => 'required|alpha_dash|max_length[120]'],
            'id_group'   => ['label' => 'Departemen tampilan', 'rules' => 'permit_empty|integer'],
            'label'      => ['label' => 'Label', 'rules' => 'required|max_length[160]'],
            'btn_class'  => ['label' => 'Class warna', 'rules' => 'required|max_length[120]'],
            'sort_order' => ['label' => 'Urutan', 'rules' => 'permit_empty|integer'],
            'is_active'  => ['label' => 'Status', 'rules' => 'required|in_list[0,1]'],
        ]);

        if (!$this->validation->run($fields)) {
            if ($raw) {
                return [
                    'success'  => false,
                    'status'   => 422,
                    'messages' => 'Validasi gagal.',
                    'errors'   => $this->validation->getErrors(),
                ];
            }

            return [
                'success'  => false,
                'token'    => csrf_hash(),
                'messages' => $this->validation->listErrors(),
            ];
        }

        if ($id > 0 && !$this->siteplanMenu->findItem($id)) {
            return $this->failSave('Menu siteplan tidak ditemukan', $raw, 404);
        }

        if ($this->menuRepo->checkDuplicateSiteplanItemKey($fields['item_key'], $id)) {
            return $this->failSave('Key menu siteplan sudah dipakai', $raw, 422, ['item_key' => 'Key menu siteplan sudah dipakai']);
        }

        $roleIds = $postData['role_ids'] ?? [];
        if (!is_array($roleIds) || !$roleIds) {
            $roleIds = [$fields['id_group']];
        }

        $itemId = $this->siteplanMenu->saveItem($fields, $id);
        $this->siteplanMenu->syncRoles($itemId, $roleIds);

        $message = $id > 0 ? 'Menu siteplan berhasil diperbaharui' : 'Menu siteplan berhasil ditambahkan';

        if ($raw) {
            return [
                'success'  => true,
                'created'  => $id <= 0,
                'status'   => $id <= 0 ? 201 : 200,
                'messages' => $message,
                'data'     => ['id' => $itemId],
            ];
        }

        return [
            'success'  => true,
            'token'    => csrf_hash(),
            'messages' => $message,
        ];
    }

    public function toggleSiteplanMenu(int $id, bool $raw = false): array
    {
        $item = $this->siteplanMenu->findItem($id);
        if (!$item || !$this->siteplanMenu->toggleItem($id)) {
            return $this->failSave('Menu siteplan tidak ditemukan', $raw, 404);
        }

        if ($raw) {
            return [
                'success'  => true,
                'messages' => 'Status menu siteplan berhasil diperbaharui',
                'data'     => [
                    'id'        => $id,
                    'is_active' => ((int) $item->is_active === 1) ? 0 : 1,
                ],
            ];
        }

        return [
            'success'  => true,
            'token'    => csrf_hash(),
            'messages' => 'Status menu siteplan berhasil diperbaharui',
        ];
    }

    private function paginateItems(array $rows, array $params, callable $searchFields): array
    {
        $search = trim((string) ($params['search'] ?? ''));
        if ($search !== '') {
            $rows = array_values(array_filter($rows, static function ($row) use ($search, $searchFields): bool {
                foreach ($searchFields($row) as $value) {
                    if ($value !== '' && mb_stripos((string) $value, $search) !== false) {
                        return true;
                    }
                }

                return false;
            }));
        }

        $page = max(1, (int) ($params['page'] ?? 1));
        $perPage = max(1, min(100, (int) ($params['per_page'] ?? 25)));
        $total = count($rows);

        return [
            'items' => array_slice($rows, ($page - 1) * $perPage, $perPage),
            'meta'  => [
                'page'     => $page,
                'per_page' => $perPage,
                'total'    => $total,
            ],
        ];
    }

    private function failSave(string $message, bool $raw, int $status = 422, ?array $errors = null): array
    {
        if ($raw) {
            $res = [
                'success'  => false,
                'status'   => $status,
                'messages' => $message,
            ];
            if ($errors !== null) {
                $res['errors'] = $errors;
            }
            return $res;
        }

        return [
            'success'  => false,
            'token'    => csrf_hash(),
            'messages' => $message,
        ];
    }

    public function makeSlug(string $name): string
    {
        $slug = strtolower(trim($name));
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);

        return trim((string) $slug, '-');
    }
}
