<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddHasilAkadReport extends Migration
{
    private const PARENT_SLUG = 'laporan';
    private const CHILD_URL = 'laporan/hasil-akad';
    private const GROUPS = [1, 2, 3, 4, 7, 8, 9];

    public function up()
    {
        if ($this->db->tableExists('menus')) {
            $parentId = $this->ensureParentMenu();
            $childId = $this->ensureChildMenu($parentId);
            $this->ensureRoleAccess($parentId);
            $this->ensureRoleAccess($childId);
        }

        $this->addIndexIfMissing('mkdt', 'idx_mkdt_hasil_akad_report', ['status_mkdt', 'is_kpr', 'akad_tgl', 'id_kavling']);
        $this->addIndexIfMissing('pencairan_akad_pengajuan', 'idx_pa_pengajuan_hasil_akad_report', ['tanggal_pengajuan', 'status', 'id_plan']);
        $this->addIndexIfMissing('pencairan_akad_payment', 'idx_pa_payment_hasil_akad_report', ['tanggal_cair', 'id_pengajuan']);
    }

    public function down()
    {
        $this->dropIndexIfExists('pencairan_akad_payment', 'idx_pa_payment_hasil_akad_report');
        $this->dropIndexIfExists('pencairan_akad_pengajuan', 'idx_pa_pengajuan_hasil_akad_report');
        $this->dropIndexIfExists('mkdt', 'idx_mkdt_hasil_akad_report');

        if (! $this->db->tableExists('menus')) {
            return;
        }

        $child = $this->db->table('menus')->select('id')->where('url', self::CHILD_URL)->get()->getRow();
        if (! $child) {
            return;
        }

        $this->deleteAccessRows((int) $child->id);
        $this->db->table('menus')->where('id', (int) $child->id)->delete();
    }

    private function ensureParentMenu(): int
    {
        $parent = $this->db->table('menus')
            ->select('id')
            ->groupStart()
                ->where('slug', self::PARENT_SLUG)
                ->orGroupStart()
                    ->where('name', 'Laporan')
                    ->where('parent_id', 0)
                ->groupEnd()
            ->groupEnd()
            ->get()
            ->getRow();
        if ($parent) {
            return (int) $parent->id;
        }

        $maxOrder = $this->db->table('menus')->selectMax('sort_order')->where('parent_id', 0)->get()->getRow();
        $now = date('Y-m-d H:i:s');
        $this->db->table('menus')->insert([
            'name' => 'Laporan', 'url' => '#', 'icon' => 'bar-chart-2', 'slug' => self::PARENT_SLUG,
            'parent_id' => 0, 'is_active' => 1, 'sort_order' => ((int) ($maxOrder->sort_order ?? 0)) + 1,
            'date_add' => $now, 'date_edit' => $now,
        ]);

        return (int) $this->db->insertID();
    }

    private function ensureChildMenu(int $parentId): int
    {
        $child = $this->db->table('menus')->select('id')->where('url', self::CHILD_URL)->get()->getRow();
        if ($child) {
            return (int) $child->id;
        }

        $maxOrder = $this->db->table('menus')->selectMax('sort_order')->where('parent_id', $parentId)->get()->getRow();
        $now = date('Y-m-d H:i:s');
        $this->db->table('menus')->insert([
            'name' => 'Hasil Akad', 'url' => self::CHILD_URL, 'icon' => 'circle', 'slug' => 'laporan-hasil-akad',
            'parent_id' => $parentId, 'is_active' => 1, 'sort_order' => ((int) ($maxOrder->sort_order ?? 0)) + 1,
            'date_add' => $now, 'date_edit' => $now,
        ]);

        return (int) $this->db->insertID();
    }

    private function ensureRoleAccess(int $menuId): void
    {
        if ($menuId <= 0 || ! $this->db->tableExists('menu_roles')) {
            return;
        }

        $now = date('Y-m-d H:i:s');
        foreach (self::GROUPS as $groupId) {
            $exists = $this->db->table('menu_roles')
                ->where('id_groups', $groupId)
                ->where('id_menu', $menuId)
                ->countAllResults() > 0;
            if (! $exists) {
                $this->db->table('menu_roles')->insert([
                    'id_groups' => $groupId, 'id_menu' => $menuId, 'created_at' => $now, 'updated_at' => $now,
                ]);
            }
        }
    }

    private function deleteAccessRows(int $menuId): void
    {
        if ($this->db->tableExists('menu_roles')) {
            $this->db->table('menu_roles')->where('id_menu', $menuId)->delete();
        }
        if ($this->db->tableExists('menu_user_access')) {
            $this->db->table('menu_user_access')->where('id_menu', $menuId)->delete();
        }
    }

    private function addIndexIfMissing(string $table, string $index, array $columns): void
    {
        if (! $this->db->tableExists($table) || $this->indexExists($table, $index)) {
            return;
        }

        $quotedColumns = implode(', ', array_map(static fn (string $column): string => "`{$column}`", $columns));
        $this->db->query("ALTER TABLE `{$table}` ADD INDEX `{$index}` ({$quotedColumns})");
    }

    private function dropIndexIfExists(string $table, string $index): void
    {
        if ($this->db->tableExists($table) && $this->indexExists($table, $index)) {
            $this->db->query("ALTER TABLE `{$table}` DROP INDEX `{$index}`");
        }
    }

    private function indexExists(string $table, string $index): bool
    {
        return $this->db->query("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$index])->getNumRows() > 0;
    }
}
