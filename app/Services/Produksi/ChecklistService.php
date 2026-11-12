<?php

namespace App\Services\Produksi;

use App\Repositories\Produksi\ChecklistRepository;

class ChecklistService
{
    protected ChecklistRepository $repo;

    public function __construct(?ChecklistRepository $repo = null)
    {
        $this->repo = $repo ?? new ChecklistRepository();
    }

    // ─── CHECKLIST GROUP ───

    public function getAllGroups(bool $asDatatable = false): array
    {
        $groups = $this->repo->getAllGroups();

        if (!$asDatatable) {
            return $groups;
        }

        $data = [];
        foreach ($groups as $value) {
            $isActiveBadge = ((int) $value->is_active === 1)
                ? '<span class="badge badge-pill badge-light-success">Aktif</span>'
                : '<span class="badge badge-pill badge-light-danger">Tidak Aktif</span>';
            $icon = ((int) $value->is_active === 1) ? 'fa-times' : 'fa-check';

            $ops  = '<div class="btn-group">';
            $ops .= '    <button type="button" class="btn btn-sm btn-info" onclick="edit(' . $value->id_group . ')"><i class="fa fa-edit"></i></button>';
            $ops .= '    <button type="button" class="btn btn-sm btn-danger" onclick="remove(' . $value->id_group . ', ' . $value->is_active . ')"><i class="fa ' . $icon . '"></i></button>';
            $ops .= '</div>';

            $data[] = [
                $value->id_group,
                $value->nama_group,
                $isActiveBadge,
                $value->add_by,
                $value->created_at,
                $value->edit_by,
                $value->updated_at,
                $ops,
            ];
        }

        return $data;
    }

    public function getGroup(int $id): ?object
    {
        return $this->repo->getGroupById($id);
    }

    public function createGroup(array $data, int $userId): array
    {
        $namaGroup = trim((string) ($data['nama_group'] ?? $data['namaGroup'] ?? ''));
        if ($namaGroup === '') {
            return ['success' => false, 'messages' => 'Nama group harus diisi'];
        }

        $fields = [
            'nama_group' => $namaGroup,
            'add_by'     => $userId,
            'edit_by'    => $userId,
            'is_active'  => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        $inserted = $this->repo->insertGroup($fields);
        if (!$inserted) {
            return ['success' => false, 'messages' => 'Gagal menyimpan data group'];
        }

        return [
            'success'  => true,
            'messages' => 'Data has been inserted successfully',
            'id'       => $inserted,
        ];
    }

    public function updateGroup(int $id, array $data, int $userId): array
    {
        $namaGroup = trim((string) ($data['nama_group'] ?? $data['namaGroup'] ?? ''));
        if ($namaGroup === '') {
            return ['success' => false, 'messages' => 'Nama group harus diisi'];
        }

        $fields = [
            'nama_group' => $namaGroup,
            'edit_by'    => $userId,
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        $updated = $this->repo->updateGroup($id, $fields);
        if (!$updated) {
            return ['success' => false, 'messages' => 'Gagal memperbarui data group'];
        }

        return ['success' => true, 'messages' => 'Successfully updated'];
    }

    public function deleteGroup(int $id): array
    {
        $success = $this->repo->toggleGroupStatus($id);
        return [
            'success'  => $success,
            'messages' => $success ? 'Deletion succeeded' : 'Deletion error!',
        ];
    }

    // ─── CHECKLIST ITEM ───

    public function getAllItems(bool $asDatatable = false): array
    {
        $items = $this->repo->getAllItems();

        if (!$asDatatable) {
            return $items;
        }

        $data = [];
        foreach ($items as $value) {
            $isActiveBadge = ((int) $value->is_active === 1)
                ? '<span class="badge badge-pill badge-light-success">Aktif</span>'
                : '<span class="badge badge-pill badge-light-danger">Tidak Aktif</span>';
            $icon = ((int) $value->is_active === 1) ? 'fa-times' : 'fa-check';

            $ops  = '<div class="btn-group">';
            $ops .= '    <button type="button" class="btn btn-sm btn-info" onclick="edit(' . $value->id_item . ')"><i class="fa fa-edit"></i></button>';
            $ops .= '    <button type="button" class="btn btn-sm btn-danger" onclick="remove(' . $value->id_item . ', ' . $value->is_active . ')"><i class="fa ' . $icon . '"></i></button>';
            $ops .= '</div>';

            $data[] = [
                $value->id_item,
                $value->nama_group,
                $value->nama_item,
                $isActiveBadge,
                $value->add_by,
                $value->created_at,
                $value->edit_by,
                $value->updated_at,
                $ops,
            ];
        }

        return $data;
    }

    public function getItem(int $id): ?object
    {
        return $this->repo->getItemById($id);
    }

    public function createItem(array $data, int $userId): array
    {
        $idGroup  = (int) ($data['id_group'] ?? $data['idGroup'] ?? 0);
        $namaItem = trim((string) ($data['nama_item'] ?? $data['namaItem'] ?? ''));

        if ($idGroup <= 0 || $namaItem === '') {
            return ['success' => false, 'messages' => 'Group dan Nama item harus diisi'];
        }

        $rawItems = array_filter(array_map('trim', explode(';', $namaItem)), static fn ($v) => $v !== '');
        if (empty($rawItems)) {
            $rawItems = [$namaItem];
        }

        $now = date('Y-m-d H:i:s');
        foreach ($rawItems as $item) {
            $this->repo->insertItem([
                'id_group'   => $idGroup,
                'nama_item'  => $item,
                'is_active'  => 1,
                'add_by'     => $userId,
                'edit_by'    => $userId,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        return ['success' => true, 'messages' => 'Data has been inserted successfully'];
    }

    public function updateItem(int $id, array $data, int $userId): array
    {
        $idGroup  = (int) ($data['id_group'] ?? $data['idGroup'] ?? 0);
        $namaItem = trim((string) ($data['nama_item'] ?? $data['namaItem'] ?? ''));

        if ($idGroup <= 0 || $namaItem === '') {
            return ['success' => false, 'messages' => 'Group dan Nama item harus diisi'];
        }

        $updated = $this->repo->updateItem($id, [
            'id_group'   => $idGroup,
            'nama_item'  => $namaItem,
            'edit_by'    => $userId,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        return [
            'success'  => $updated,
            'messages' => $updated ? 'Successfully updated' : 'Update error!',
        ];
    }

    public function deleteItem(int $id): array
    {
        $success = $this->repo->toggleItemStatus($id);
        return [
            'success'  => $success,
            'messages' => $success ? 'Deletion succeeded' : 'Deletion error!',
        ];
    }

    // ─── CHECKLIST SUBITEM ───

    public function getAllSubItems(bool $asDatatable = false): array
    {
        $subItems = $this->repo->getAllSubItems();

        if (!$asDatatable) {
            return $subItems;
        }

        $data = [];
        foreach ($subItems as $value) {
            $isActiveBadge = ((int) $value->is_active === 1)
                ? '<span class="badge badge-pill badge-light-success">Aktif</span>'
                : '<span class="badge badge-pill badge-light-danger">Tidak Aktif</span>';
            $icon = ((int) $value->is_active === 1) ? 'fa-times' : 'fa-check';

            $ops  = '<div class="btn-group">';
            $ops .= '    <button type="button" class="btn btn-sm btn-info" onclick="edit(' . $value->id_subitem . ')"><i class="fa fa-edit"></i></button>';
            $ops .= '    <button type="button" class="btn btn-sm btn-danger" onclick="remove(' . $value->id_subitem . ', ' . $value->is_active . ')"><i class="fa ' . $icon . '"></i></button>';
            $ops .= '</div>';

            $data[] = [
                $value->id_subitem,
                "({$value->nama_group}) {$value->nama_item}",
                $value->nama_subitem,
                $isActiveBadge,
                $value->add_by,
                $value->created_at,
                $value->edit_by,
                $value->updated_at,
                $ops,
            ];
        }

        return $data;
    }

    public function getSubItem(int $id): ?object
    {
        return $this->repo->getSubItemById($id);
    }

    public function createSubItem(array $data, int $userId): array
    {
        $idItem      = (int) ($data['id_item'] ?? $data['idItem'] ?? 0);
        $namaSubitem = trim((string) ($data['nama_subitem'] ?? $data['namaSubitem'] ?? ''));

        if ($idItem <= 0 || $namaSubitem === '') {
            return ['success' => false, 'messages' => 'Item dan Nama subitem harus diisi'];
        }

        $rawSubItems = array_filter(array_map('trim', explode(';', $namaSubitem)), static fn ($v) => $v !== '');
        if (empty($rawSubItems)) {
            $rawSubItems = [$namaSubitem];
        }

        $now = date('Y-m-d H:i:s');
        foreach ($rawSubItems as $subItem) {
            $this->repo->insertSubItem([
                'id_item'      => $idItem,
                'nama_subitem' => $subItem,
                'is_active'    => 1,
                'add_by'       => $userId,
                'edit_by'      => $userId,
                'created_at'   => $now,
                'updated_at'   => $now,
            ]);
        }

        return ['success' => true, 'messages' => 'Data has been inserted successfully'];
    }

    public function updateSubItem(int $id, array $data, int $userId): array
    {
        $idItem      = (int) ($data['id_item'] ?? $data['idItem'] ?? 0);
        $namaSubitem = trim((string) ($data['nama_subitem'] ?? $data['namaSubitem'] ?? ''));

        if ($idItem <= 0 || $namaSubitem === '') {
            return ['success' => false, 'messages' => 'Item dan Nama subitem harus diisi'];
        }

        $updated = $this->repo->updateSubItem($id, [
            'id_item'      => $idItem,
            'nama_subitem' => $namaSubitem,
            'edit_by'      => $userId,
            'updated_at'   => date('Y-m-d H:i:s'),
        ]);

        return [
            'success'  => $updated,
            'messages' => $updated ? 'Successfully updated' : 'Update error!',
        ];
    }

    public function deleteSubItem(int $id): array
    {
        $success = $this->repo->toggleSubItemStatus($id);
        return [
            'success'  => $success,
            'messages' => $success ? 'Deletion succeeded' : 'Deletion error!',
        ];
    }

    public function getActiveGroups(): array
    {
        return $this->repo->getActiveGroups();
    }

    public function getActiveGroupsWithItems(): array
    {
        return $this->repo->getActiveGroupsWithItems();
    }
}
