<?php

namespace App\Services;

class SiteplanMenuService
{
    private const GROUP_LABELS = [
        0  => 'Others',
        3  => 'Keuangan',
        4  => 'MKDT',
        6  => 'Planning',
        7  => 'Produksi',
        8  => 'Sales',
        9  => 'Direksi',
        11 => 'Target',
    ];

    private const GROUP_CONTAINER_IDS = [
        0  => 'others_menu',
        3  => 'keuangan_menu',
        4  => 'mkdt_menu',
        6  => 'planning_menu',
        7  => 'produksi_menu',
        8  => 'sales_menu',
        9  => 'direksi_menu',
        11 => 'target_menu',
    ];

    protected $db;

    public function __construct()
    {
        $this->db = db_connect();
    }

    public function renderForRole(int $roleId): string
    {
        if (!$this->tablesReady()) {
            return '';
        }

        if ($roleId === 1) {
            return $this->renderAllGroupMenus();
        }

        $targetGroup = array_key_exists($roleId, self::GROUP_CONTAINER_IDS) ? $roleId : 0;
        $items = $this->getItemsForAccessGroup($roleId);
        if (!$items && $roleId !== 0) {
            $items = $this->getItemsForAccessGroup(0);
            $targetGroup = 0;
        }

        return $this->renderGroupMenu($targetGroup, $items, false);
    }

    public function getFlatItems(bool $activeOnly = false): array
    {
        if (!$this->tablesReady()) {
            return [];
        }

        $builder = $this->db->table('siteplan_menu_items')
            ->select('*')
            ->orderBy('id_group', 'ASC')
            ->orderBy('sort_order', 'ASC')
            ->orderBy('id', 'ASC');

        if ($activeOnly) {
            $builder->where('is_active', 1);
        }

        return $builder->get()->getResult();
    }

    public function findItem(int $id)
    {
        if (!$this->tablesReady() || $id <= 0) {
            return null;
        }

        return $this->db->table('siteplan_menu_items')->where('id', $id)->get()->getRow();
    }

    public function saveItem(array $fields, int $id = 0): int
    {
        $fields['updated_at'] = date('Y-m-d H:i:s');

        if ($id > 0) {
            $this->db->table('siteplan_menu_items')->where('id', $id)->update($fields);
            return $id;
        }

        $fields['created_at'] = $fields['updated_at'];
        $this->db->table('siteplan_menu_items')->insert($fields);

        return (int) $this->db->insertID();
    }

    public function toggleItem(int $id): bool
    {
        $item = $this->findItem($id);
        if (!$item) {
            return false;
        }

        $this->db->table('siteplan_menu_items')
            ->where('id', $id)
            ->update([
                'is_active'  => ((int) $item->is_active === 1) ? 0 : 1,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

        return true;
    }

    public function syncRoles(int $itemId, array $groupIds): void
    {
        $groupIds = array_values(array_unique(array_map('intval', $groupIds)));
        $this->db->transStart();
        $this->db->table('siteplan_menu_roles')->where('id_siteplan_menu_item', $itemId)->delete();
        foreach ($groupIds as $groupId) {
            $this->db->table('siteplan_menu_roles')->insert([
                'id_group'               => $groupId,
                'id_siteplan_menu_item'  => $itemId,
                'created_at'             => date('Y-m-d H:i:s'),
                'updated_at'             => date('Y-m-d H:i:s'),
            ]);
        }
        $this->db->transComplete();
    }

    public function getItemRoleIds(int $itemId): array
    {
        if (!$this->tablesReady() || $itemId <= 0) {
            return [];
        }

        $rows = $this->db->table('siteplan_menu_roles')
            ->select('id_group')
            ->where('id_siteplan_menu_item', $itemId)
            ->get()
            ->getResult();

        return array_values(array_unique(array_map(static fn ($row) => (int) $row->id_group, $rows)));
    }

    public function groupOptions(): array
    {
        $options = [
            (object) [
                'id'          => 0,
                'name'        => self::GROUP_LABELS[0],
                'description' => 'Fallback',
            ],
        ];

        $rows = $this->db->table('auth_groups')
            ->select('id, name, description')
            ->orderBy('id', 'ASC')
            ->get()
            ->getResult();

        $existing = [0 => true];
        foreach ($rows as $row) {
            $options[] = $row;
            $existing[(int) $row->id] = true;
        }

        foreach (self::GROUP_LABELS as $id => $name) {
            if (isset($existing[$id])) {
                continue;
            }
            $options[] = (object) [
                'id'          => $id,
                'name'        => $name,
                'description' => 'Siteplan',
            ];
        }

        return $options;
    }

    public function groupLabel(int $groupId): string
    {
        return self::GROUP_LABELS[$groupId] ?? 'Departemen ' . $groupId;
    }

    private function renderAllGroupMenus(): string
    {
        $items = $this->getFlatItems(true);
        $byGroup = [];
        foreach ($items as $item) {
            $byGroup[(int) $item->id_group][] = $item;
        }

        $html = '';
        foreach (self::GROUP_CONTAINER_IDS as $groupId => $containerId) {
            $html .= $this->renderGroupMenu($groupId, $byGroup[$groupId] ?? [], true);
        }

        return $html;
    }

    private function getItemsForAccessGroup(int $groupId): array
    {
        if (!$this->tablesReady()) {
            return [];
        }

        return $this->db->table('siteplan_menu_items i')
            ->select('i.*')
            ->join('siteplan_menu_roles r', 'r.id_siteplan_menu_item = i.id')
            ->where('r.id_group', $groupId)
            ->where('i.is_active', 1)
            ->orderBy('i.sort_order', 'ASC')
            ->orderBy('i.id', 'ASC')
            ->get()
            ->getResult();
    }

    private function renderGroupMenu(int $groupId, array $items, bool $hidden): string
    {
        $containerId = self::GROUP_CONTAINER_IDS[$groupId] ?? self::GROUP_CONTAINER_IDS[0];
        $hiddenClass = $hidden ? ' hidden' : '';
        $html = '<div id="' . esc($containerId) . '" class="float div_menu' . $hiddenClass . '" data-siteplan-role="' . $groupId . '">';
        $lastGroupLabel = null;

        foreach ($items as $item) {
            $groupLabel = trim((string) ($item->group_label ?? ''));
            if ($groupLabel !== '' && $groupLabel !== $lastGroupLabel) {
                $html .= '<div class="menu-group-label d-none">' . esc($groupLabel) . '</div>';
                $lastGroupLabel = $groupLabel;
            }

            $html .= $this->renderItem($item);
        }

        $html .= '</div>';

        return $html;
    }

    private function renderItem($item): string
    {
        $key = (string) ($item->item_key ?? '');
        if ($key === 'planning_manual_selection') {
            return '<div class="custom-control custom-switch custom-control-inline">
                <input onchange="hapus_seleksi()" type="checkbox" value="1" class="custom-control-input" id="tambah_jalan" name="tambah_jalan" />
                <label class="custom-control-label" for="tambah_jalan">Manual Seleksi</label>
            </div>';
        }

        if ($key === 'planning_toggle_legend') {
            return '<button id="planning_toggle_btn" class="btn-icon btn btn-info btn-round btn-sm my-float" data-toggle="collapse" data-target="#planningCollapse" aria-expanded="false" aria-controls="planningCollapse"><i class="fas fa-palette"></i> ' . esc($item->label) . '</button>
                <div class="collapse" id="planningCollapse"><div class="card card-body"></div></div>';
        }

        if ($key === 'produksi_tambah_jalan_state') {
            return '<input type="checkbox" value="1" class="d-none" id="produksi_tambah_jalan" name="produksi_tambah_jalan" />';
        }

        if ($key === 'produksi_tambah_jalan_hint') {
            return '<div id="produksi_add_jalan_hint" class="my-float text-warning font-weight-bold d-none">' . esc($item->label) . '</div>';
        }

        $id = trim((string) ($item->extra_id ?? ''));
        $idAttr = $id !== '' ? ' id="' . esc($id) . '"' : '';
        $onclick = trim((string) ($item->onclick ?? ''));
        $onclickAttr = $onclick !== '' ? ' onclick="' . esc($onclick) . '"' : '';
        $icon = trim((string) ($item->icon ?? ''));
        $iconHtml = $icon !== '' ? '<i class="' . esc($icon) . '"></i> ' : '';
        $btnClass = trim((string) ($item->btn_class ?? 'btn-primary'));
        $extraClass = trim((string) ($item->extra_class ?? ''));
        $classes = trim('btn-icon btn ' . ($btnClass ?: 'btn-primary') . ' btn-round btn-sm my-float ' . $extraClass);
        $styleAttr = in_array($key, ['planning_selection_done', 'planning_selection_cancel'], true) ? ' style="display: none;"' : '';

        return '<button' . $idAttr . ' type="button" class="' . esc($classes) . '"' . $onclickAttr . $styleAttr . '>' . $iconHtml . esc($item->label) . '</button>';
    }

    private function tablesReady(): bool
    {
        return $this->db->tableExists('siteplan_menu_items') && $this->db->tableExists('siteplan_menu_roles');
    }
}
