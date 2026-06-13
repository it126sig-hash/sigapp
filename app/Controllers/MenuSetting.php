<?php

namespace App\Controllers;

use App\Models\MenuModel;
use App\Services\MenuAccessService;
use App\Services\SiteplanMenuService;
use CodeIgniter\Exceptions\PageNotFoundException;

class MenuSetting extends BaseController
{
    protected $db;
    protected $menuModel;
    protected $menuAccess;
    protected $siteplanMenu;
    protected $validation;

    public function __construct()
    {
        $this->db = db_connect();
        $this->menuModel = new MenuModel();
        $this->menuAccess = new MenuAccessService();
        $this->siteplanMenu = new SiteplanMenuService();
        $this->validation = \Config\Services::validation();
    }

    public function index()
    {
        $this->guardAdmin();

        $data['content'] = 'menu/setting';
        $data['data'] = [
            'controller' => 'menu-setting',
            'title' => 'Setting Menu',
            'groups' => $this->getGroups(),
            'users' => $this->getUsers(),
            'parent_options' => $this->getParentOptions(),
            'siteplan_groups' => $this->siteplanMenu->groupOptions(),
        ];

        return view('template', $data);
    }

    public function menuList()
    {
        $this->guardAdmin(true);

        $data = [
            'token' => csrf_hash(),
            'data' => [],
        ];

        foreach ($this->getFlatMenus(false) as $key => $menu) {
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

            $data['data'][$key] = [
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

        return $this->response->setJSON($data);
    }

    public function menuGet()
    {
        $this->guardAdmin(true);

        $id = (int) $this->request->getPost('id');
        $menu = $this->menuModel->where('id', $id)->first();
        if (!$menu) {
            return $this->response->setJSON([
                'success' => false,
                'token' => csrf_hash(),
                'messages' => 'Menu tidak ditemukan',
            ]);
        }

        $menu->token = csrf_hash();
        $menu->success = true;

        return $this->response->setJSON($menu);
    }

    public function menuSave()
    {
        $this->guardAdmin(true);

        $id = (int) $this->request->getPost('id');
        $parentId = (int) $this->request->getPost('parent_id');
        $fields = [
            'name' => trim((string) $this->request->getPost('name')),
            'url' => trim((string) $this->request->getPost('url')),
            'icon' => trim((string) $this->request->getPost('icon')),
            'slug' => trim((string) $this->request->getPost('slug')),
            'parent_id' => $parentId,
            'is_active' => (int) $this->request->getPost('is_active') === 1 ? 1 : 0,
            'sort_order' => (int) $this->request->getPost('sort_order'),
            'date_edit' => date('Y-m-d H:i:s'),
        ];

        if ($fields['slug'] === '') {
            $fields['slug'] = $this->makeSlug($fields['name']);
        }

        $this->validation->setRules([
            'name' => ['label' => 'Nama menu', 'rules' => 'required|max_length[255]'],
            'url' => ['label' => 'URL', 'rules' => 'required|max_length[255]'],
            'icon' => ['label' => 'Icon', 'rules' => 'permit_empty|max_length[255]'],
            'parent_id' => ['label' => 'Parent', 'rules' => 'permit_empty|integer'],
            'is_active' => ['label' => 'Status', 'rules' => 'required|in_list[0,1]'],
            'sort_order' => ['label' => 'Urutan', 'rules' => 'permit_empty|integer'],
        ]);

        if (!$this->validation->run($fields)) {
            return $this->jsonError($this->validation->listErrors());
        }

        $parentMenu = null;
        if ($parentId > 0) {
            $parentMenu = $this->menuModel->where('id', $parentId)->first();
            if (!$parentMenu) {
                return $this->jsonError('Parent menu tidak valid');
            }

            if ((int) $parentMenu->parent_id !== 0) {
                return $this->jsonError('Parent menu harus menu utama, bukan sub menu');
            }
        }

        if ($id > 0 && ($parentId === $id || $this->isDescendant($id, $parentId))) {
            return $this->jsonError('Parent menu tidak boleh menu sendiri atau turunannya');
        }

        if ($id > 0) {
            if (!$this->menuModel->where('id', $id)->first()) {
                return $this->jsonError('Menu tidak ditemukan');
            }
            $this->menuModel->update($id, $fields);
            $message = 'Menu berhasil diperbaharui';
        } else {
            $fields['date_add'] = date('Y-m-d H:i:s');
            $this->menuModel->insert($fields);
            $message = 'Menu berhasil ditambahkan';
        }

        return $this->response->setJSON([
            'success' => true,
            'token' => csrf_hash(),
            'messages' => $message,
            'parent_options' => $this->getParentOptions(),
        ]);
    }

    public function menuToggle()
    {
        $this->guardAdmin(true);

        $id = (int) $this->request->getPost('id');
        $menu = $this->menuModel->where('id', $id)->first();
        if (!$menu) {
            return $this->jsonError('Menu tidak ditemukan');
        }

        $this->menuModel->update($id, [
            'is_active' => ((int) $menu->is_active === 1) ? 0 : 1,
            'date_edit' => date('Y-m-d H:i:s'),
        ]);

        return $this->response->setJSON([
            'success' => true,
            'token' => csrf_hash(),
            'messages' => 'Status menu berhasil diperbaharui',
        ]);
    }

    public function groupAccess()
    {
        $this->guardAdmin(true);

        $groupId = (int) $this->request->getPost('group_id');
        if (!$this->groupExists($groupId)) {
            return $this->jsonError('Departemen tidak valid');
        }

        return $this->response->setJSON([
            'success' => true,
            'token' => csrf_hash(),
            'menus' => $this->getFlatMenus(true),
            'selected' => $this->menuAccess->getGroupMenuIds([$groupId]),
        ]);
    }

    public function groupSave()
    {
        $this->guardAdmin(true);

        $groupId = (int) $this->request->getPost('group_id');
        if (!$this->groupExists($groupId)) {
            return $this->jsonError('Departemen tidak valid');
        }

        $menuIds = $this->request->getPost('menu_ids') ?? [];
        $this->menuAccess->syncGroupMenus($groupId, is_array($menuIds) ? $menuIds : []);

        return $this->response->setJSON([
            'success' => true,
            'token' => csrf_hash(),
            'messages' => 'Akses departemen berhasil disimpan',
        ]);
    }

    public function userAccess()
    {
        $this->guardAdmin(true);

        $userId = (int) $this->request->getPost('user_id');
        if (!$this->userExists($userId)) {
            return $this->jsonError('User tidak valid');
        }

        $overrides = $this->menuAccess->getUserOverrides($userId);

        return $this->response->setJSON([
            'success' => true,
            'token' => csrf_hash(),
            'menus' => $this->getFlatMenus(true),
            'allow' => $overrides['allow'],
            'deny' => $overrides['deny'],
            'group_menu_ids' => $this->menuAccess->getGroupMenuIds($this->menuAccess->getUserGroupIds($userId)),
        ]);
    }

    public function userSave()
    {
        $this->guardAdmin(true);

        $userId = (int) $this->request->getPost('user_id');
        if (!$this->userExists($userId)) {
            return $this->jsonError('User tidak valid');
        }

        $allowIds = $this->request->getPost('allow_ids') ?? [];
        $denyIds = $this->request->getPost('deny_ids') ?? [];
        $this->menuAccess->syncUserOverrides($userId, is_array($allowIds) ? $allowIds : [], is_array($denyIds) ? $denyIds : []);

        return $this->response->setJSON([
            'success' => true,
            'token' => csrf_hash(),
            'messages' => 'Akses user berhasil disimpan',
        ]);
    }

    public function siteplanMenuList()
    {
        $this->guardAdmin(true);

        $data = [
            'token' => csrf_hash(),
            'data' => [],
        ];

        foreach ($this->siteplanMenu->getFlatItems(false) as $key => $menu) {
            $status = ((int) $menu->is_active === 1)
                ? '<span class="badge badge-pill badge-success">Aktif</span>'
                : '<span class="badge badge-pill badge-danger">Tidak Aktif</span>';
            $toggleIcon = ((int) $menu->is_active === 1) ? 'fa-times' : 'fa-check';
            $ops = '<div class="btn-group">';
            $ops .= '<button type="button" class="btn btn-sm btn-info" onclick="editSiteplanMenu(' . (int) $menu->id . ')"><i class="fa fa-edit"></i></button>';
            $ops .= '<button type="button" class="btn btn-sm btn-danger" onclick="toggleSiteplanMenu(' . (int) $menu->id . ')"><i class="fa ' . $toggleIcon . '"></i></button>';
            $ops .= '</div>';

            $data['data'][$key] = [
                (int) $menu->id,
                esc($this->siteplanMenu->groupLabel((int) $menu->id_group)),
                esc($menu->group_label),
                esc($menu->label),
                esc($menu->icon),
                esc($menu->btn_class),
                esc($menu->onclick),
                (int) $menu->sort_order,
                $status,
                $ops,
            ];
        }

        return $this->response->setJSON($data);
    }

    public function siteplanMenuGet()
    {
        $this->guardAdmin(true);

        $id = (int) $this->request->getPost('id');
        $menu = $this->siteplanMenu->findItem($id);
        if (!$menu) {
            return $this->jsonError('Menu siteplan tidak ditemukan');
        }

        $menu->success = true;
        $menu->token = csrf_hash();
        $menu->role_ids = $this->siteplanMenu->getItemRoleIds($id);

        return $this->response->setJSON($menu);
    }

    public function siteplanMenuSave()
    {
        $this->guardAdmin(true);

        $id = (int) $this->request->getPost('id');
        $itemKey = trim((string) $this->request->getPost('item_key'));
        $label = trim((string) $this->request->getPost('label'));
        if ($itemKey === '') {
            $itemKey = $this->makeSlug($label);
        }

        $fields = [
            'item_key'    => $itemKey,
            'id_group'    => (int) $this->request->getPost('id_group'),
            'label'       => $label,
            'group_label' => trim((string) $this->request->getPost('group_label')),
            'onclick'     => trim((string) $this->request->getPost('onclick')),
            'icon'        => trim((string) $this->request->getPost('icon')),
            'btn_class'   => trim((string) $this->request->getPost('btn_class')) ?: 'btn-primary',
            'sort_order'  => (int) $this->request->getPost('sort_order'),
            'is_active'   => (int) $this->request->getPost('is_active') === 1 ? 1 : 0,
            'extra_id'    => trim((string) $this->request->getPost('extra_id')),
            'extra_class' => trim((string) $this->request->getPost('extra_class')),
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
            return $this->jsonError($this->validation->listErrors());
        }

        $duplicate = $this->db->table('siteplan_menu_items')
            ->select('id')
            ->where('item_key', $fields['item_key'])
            ->where('id !=', $id)
            ->get()
            ->getRow();
        if ($duplicate) {
            return $this->jsonError('Key menu siteplan sudah dipakai');
        }

        $roleIds = $this->request->getPost('role_ids') ?? [];
        if (!is_array($roleIds) || !$roleIds) {
            $roleIds = [$fields['id_group']];
        }

        $this->db->transStart();
        $itemId = $this->siteplanMenu->saveItem($fields, $id);
        $this->siteplanMenu->syncRoles($itemId, $roleIds);
        $this->db->transComplete();

        return $this->response->setJSON([
            'success' => true,
            'token' => csrf_hash(),
            'messages' => $id > 0 ? 'Menu siteplan berhasil diperbaharui' : 'Menu siteplan berhasil ditambahkan',
        ]);
    }

    public function siteplanMenuToggle()
    {
        $this->guardAdmin(true);

        $id = (int) $this->request->getPost('id');
        if (!$this->siteplanMenu->toggleItem($id)) {
            return $this->jsonError('Menu siteplan tidak ditemukan');
        }

        return $this->response->setJSON([
            'success' => true,
            'token' => csrf_hash(),
            'messages' => 'Status menu siteplan berhasil diperbaharui',
        ]);
    }

    private function guardAdmin(bool $json = false): void
    {
        if (in_groups('1')) {
            return;
        }

        if ($json) {
            $this->response->setStatusCode(403)
                ->setJSON([
                    'success' => false,
                    'token' => csrf_hash(),
                    'messages' => 'Akses hanya untuk admin',
                ])
                ->send();
            exit;
        }

        throw new PageNotFoundException('Halaman tidak ditemukan');
    }

    private function jsonError($messages)
    {
        return $this->response->setJSON([
            'success' => false,
            'token' => csrf_hash(),
            'messages' => $messages,
        ]);
    }

    private function getGroups(): array
    {
        return $this->db->table('auth_groups')
            ->select('id, name, description')
            ->orderBy('id', 'ASC')
            ->get()
            ->getResult();
    }

    private function getUsers(): array
    {
        return $this->db->table('users')
            ->select('users.id, users.username, karyawan.nama_karyawan, auth_groups.name AS group_name')
            ->join('karyawan', 'karyawan.id_user = users.id', 'left')
            ->join('auth_groups_users', 'auth_groups_users.user_id = users.id', 'left')
            ->join('auth_groups', 'auth_groups.id = auth_groups_users.group_id', 'left')
            ->where('users.active', 1)
            ->orderBy('karyawan.nama_karyawan', 'ASC')
            ->orderBy('users.username', 'ASC')
            ->get()
            ->getResult();
    }

    private function getParentOptions(): array
    {
        $options = [(object) ['id' => 0, 'name' => 'Root']];
        $menus = $this->db->table('menus')
            ->select('id, name')
            ->where('parent_id', 0)
            ->orderBy('sort_order', 'ASC')
            ->orderBy('id', 'ASC')
            ->get()
            ->getResult();

        foreach ($menus as $menu) {
            $options[] = $menu;
        }

        return $options;
    }

    private function getFlatMenus(bool $activeOnly): array
    {
        $builder = $this->db->table('menus')
            ->select('id, name, url, icon, slug, parent_id, is_active, sort_order')
            ->orderBy('parent_id', 'ASC')
            ->orderBy('sort_order', 'ASC')
            ->orderBy('id', 'ASC');

        if ($activeOnly) {
            $builder->where('is_active', 1);
        }

        $menus = $builder->get()->getResult();
        $children = [];
        foreach ($menus as $menu) {
            $children[(int) $menu->parent_id][] = $menu;
        }

        $flat = [];
        $walk = function (int $parentId, int $depth) use (&$walk, &$flat, $children) {
            foreach ($children[$parentId] ?? [] as $menu) {
                $menu->depth = $depth;
                $flat[] = $menu;
                $walk((int) $menu->id, $depth + 1);
            }
        };
        $walk(0, 0);

        return $flat;
    }

    private function groupExists(int $groupId): bool
    {
        return $groupId > 0 && $this->db->table('auth_groups')->where('id', $groupId)->countAllResults() > 0;
    }

    private function userExists(int $userId): bool
    {
        return $userId > 0 && $this->db->table('users')->where('id', $userId)->countAllResults() > 0;
    }

    private function isDescendant(int $menuId, int $parentId): bool
    {
        if ($parentId <= 0) {
            return false;
        }

        $current = $this->menuModel->where('id', $parentId)->first();
        while ($current) {
            if ((int) $current->parent_id === $menuId) {
                return true;
            }

            if ((int) $current->parent_id <= 0) {
                return false;
            }

            $current = $this->menuModel->where('id', (int) $current->parent_id)->first();
        }

        return false;
    }

    private function makeSlug(string $name): string
    {
        $slug = strtolower(trim($name));
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);

        return trim((string) $slug, '-');
    }
}
