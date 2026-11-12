<?php

namespace App\Repositories\Produksi;

use App\Models\ChecklistGroupModel;
use App\Models\ChecklistItemModel;
use App\Models\ChecklistSubItemModel;

class ChecklistRepository
{
    protected ChecklistGroupModel   $groupModel;
    protected ChecklistItemModel    $itemModel;
    protected ChecklistSubItemModel $subItemModel;
    protected $db;

    public function __construct()
    {
        $this->groupModel    = new ChecklistGroupModel();
        $this->itemModel     = new ChecklistItemModel();
        $this->subItemModel  = new ChecklistSubItemModel();
        $this->db            = \Config\Database::connect();
    }

    // ─── CHECKLIST GROUP ───

    public function getAllGroups(): array
    {
        return $this->groupModel
            ->select('id_group, nama_group, add_by, created_at, edit_by, updated_at, is_active')
            ->orderBy('id_group', 'ASC')
            ->findAll();
    }

    public function getActiveGroups(): array
    {
        return $this->groupModel
            ->select('id_group, nama_group, is_active')
            ->orderBy('nama_group', 'ASC')
            ->findAll();
    }

    public function getGroupById(int $id): ?object
    {
        return $this->groupModel->where('id_group', $id)->first() ?: null;
    }

    public function insertGroup(array $data): int|string|false
    {
        return $this->groupModel->insert($data);
    }

    public function updateGroup(int $id, array $data): bool
    {
        return (bool) $this->groupModel->update($id, $data);
    }

    public function toggleGroupStatus(int $id): bool
    {
        $group = $this->getGroupById($id);
        if (!$group) {
            return false;
        }

        $newStatus = ((int) $group->is_active === 1) ? 0 : 1;
        return (bool) $this->groupModel->update($id, ['is_active' => $newStatus]);
    }

    // ─── CHECKLIST ITEM ───

    public function getAllItems(): array
    {
        return $this->itemModel
            ->select('
                id_item,
                checklist_item.is_active,
                checklist_item.id_group,
                nama_item,
                checklist_item.add_by,
                checklist_item.created_at,
                checklist_item.edit_by,
                checklist_item.updated_at,
                nama_group
            ')
            ->join('checklist_group', 'checklist_group.id_group = checklist_item.id_group')
            ->orderBy('checklist_group.nama_group', 'ASC')
            ->orderBy('checklist_item.nama_item', 'ASC')
            ->findAll();
    }

    public function getItemById(int $id): ?object
    {
        return $this->itemModel->where('id_item', $id)->first() ?: null;
    }

    public function getItemsByGroup(int $idGroup): array
    {
        return $this->itemModel
            ->select('id_item, nama_item, is_active')
            ->where('id_group', $idGroup)
            ->findAll();
    }

    public function insertItem(array $data): int|string|false
    {
        return $this->itemModel->insert($data);
    }

    public function updateItem(int $id, array $data): bool
    {
        return (bool) $this->itemModel->update($id, $data);
    }

    public function toggleItemStatus(int $id): bool
    {
        $item = $this->getItemById($id);
        if (!$item) {
            return false;
        }

        $newStatus = ((int) $item->is_active === 1) ? 0 : 1;
        return (bool) $this->itemModel->update($id, ['is_active' => $newStatus]);
    }

    // ─── CHECKLIST SUBITEM ───

    public function getAllSubItems(): array
    {
        return $this->subItemModel
            ->select('
                id_subitem, 
                checklist_subitem.id_item, 
                checklist_subitem.is_active,
                nama_subitem, 
                checklist_subitem.add_by, 
                checklist_subitem.created_at, 
                checklist_subitem.edit_by, 
                checklist_subitem.updated_at,
                nama_item,
                nama_group
            ')
            ->join('checklist_item', 'checklist_item.id_item = checklist_subitem.id_item')
            ->join('checklist_group', 'checklist_item.id_group = checklist_group.id_group')
            ->orderBy('checklist_group.nama_group', 'ASC')
            ->orderBy('checklist_item.nama_item', 'ASC')
            ->orderBy('checklist_subitem.nama_subitem', 'ASC')
            ->findAll();
    }

    public function getSubItemById(int $id): ?object
    {
        return $this->subItemModel->where('id_subitem', $id)->first() ?: null;
    }

    public function insertSubItem(array $data): int|string|false
    {
        return $this->subItemModel->insert($data);
    }

    public function updateSubItem(int $id, array $data): bool
    {
        return (bool) $this->subItemModel->update($id, $data);
    }

    public function toggleSubItemStatus(int $id): bool
    {
        $subItem = $this->getSubItemById($id);
        if (!$subItem) {
            return false;
        }

        $newStatus = ((int) $subItem->is_active === 1) ? 0 : 1;
        return (bool) $this->subItemModel->update($id, ['is_active' => $newStatus]);
    }

    public function getActiveGroupsWithItems(): array
    {
        $items = [];
        $groups = $this->groupModel->select('id_group, nama_group, is_active')->findAll();

        foreach ($groups as $group) {
            $groupItems = $this->itemModel
                ->select('id_item, nama_item, is_active')
                ->where('id_group', $group->id_group)
                ->findAll();

            foreach ($groupItems as $ci) {
                $isActive = ((int) $group->is_active === 0 || (int) $ci->is_active === 0) ? 0 : 1;
                $items[] = [
                    'id_item'   => $ci->id_item,
                    'nama_item' => "({$group->nama_group}) {$ci->nama_item}",
                    'is_active' => $isActive,
                ];
            }
        }

        return $items;
    }
}
