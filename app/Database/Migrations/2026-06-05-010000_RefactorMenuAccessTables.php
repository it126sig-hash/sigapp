<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RefactorMenuAccessTables extends Migration
{
    public function up()
    {
        $this->ensureMenuSortOrder();
        $legacyRows = $this->backupLegacyMenuRoles();
        $this->createNormalizedMenuRoles($legacyRows);
        $this->createMenuUserAccess();
        $this->ensureSettingMenu();
    }

    public function down()
    {
        $this->forge->dropTable('menu_user_access', true);

        if ($this->db->tableExists('menu_roles')) {
            $this->forge->dropTable('menu_roles', true);
        }

        $this->forge->addField([
            'id_groups' => [
                'type'       => 'INT',
                'constraint' => 11,
            ],
            'id_menu' => [
                'type' => 'TEXT',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id_groups', true);
        $this->forge->createTable('menu_roles', true);

        if ($this->db->tableExists('menu_roles_legacy')) {
            $rows = $this->db->table('menu_roles_legacy')->get()->getResult();
            foreach ($rows as $row) {
                $this->db->table('menu_roles')->insert([
                    'id_groups' => (int) $row->id_groups,
                    'id_menu' => $row->id_menu,
                ]);
            }
        }
    }

    private function ensureMenuSortOrder(): void
    {
        if (!$this->db->tableExists('menus')) {
            return;
        }

        $fields = $this->db->getFieldNames('menus');
        if (!in_array('sort_order', $fields, true)) {
            $this->forge->addColumn('menus', [
                'sort_order' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'default'    => 0,
                    'after'      => 'is_active',
                ],
            ]);
        }

        $this->db->query('UPDATE menus SET sort_order = id WHERE sort_order IS NULL OR sort_order = 0');
        $this->addIndexIfMissing('menus', 'idx_menus_parent_id', ['parent_id']);
        $this->addIndexIfMissing('menus', 'idx_menus_is_active', ['is_active']);
    }

    private function backupLegacyMenuRoles(): array
    {
        if (!$this->db->tableExists('menu_roles')) {
            return [];
        }

        $fields = $this->db->getFieldNames('menu_roles');
        if (in_array('id', $fields, true)) {
            return [];
        }

        $rows = $this->db->table('menu_roles')->get()->getResultArray();

        if (!$this->db->tableExists('menu_roles_legacy')) {
            $this->forge->addField([
                'id_groups' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                ],
                'id_menu' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
            ]);
            $this->forge->addKey('id_groups', true);
            $this->forge->createTable('menu_roles_legacy', true);

            foreach ($rows as $row) {
                $this->db->table('menu_roles_legacy')->insert([
                    'id_groups' => (int) $row['id_groups'],
                    'id_menu' => $row['id_menu'],
                ]);
            }
        }

        return $rows;
    }

    private function createNormalizedMenuRoles(array $legacyRows): void
    {
        if ($this->db->tableExists('menu_roles')) {
            $fields = $this->db->getFieldNames('menu_roles');
            if (in_array('id', $fields, true)) {
                $this->addIndexIfMissing('menu_roles', 'idx_menu_roles_group', ['id_groups']);
                $this->addUniqueIndexIfMissing('menu_roles', 'uq_menu_roles_group_menu', ['id_groups', 'id_menu']);
                return;
            }

            $this->forge->dropTable('menu_roles', true);
        }

        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'id_groups' => [
                'type'       => 'INT',
                'constraint' => 11,
            ],
            'id_menu' => [
                'type'       => 'INT',
                'constraint' => 11,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('menu_roles', true);
        $this->addIndexIfMissing('menu_roles', 'idx_menu_roles_group', ['id_groups']);
        $this->addUniqueIndexIfMissing('menu_roles', 'uq_menu_roles_group_menu', ['id_groups', 'id_menu']);

        $validMenuIds = $this->getValidMenuLookup();
        $now = date('Y-m-d H:i:s');
        $inserted = [];

        foreach ($legacyRows as $row) {
            $groupId = (int) $row['id_groups'];
            $menuIds = array_filter(array_map('intval', explode(',', (string) $row['id_menu'])));

            foreach ($menuIds as $menuId) {
                $key = $groupId . ':' . $menuId;
                if (!isset($validMenuIds[$menuId]) || isset($inserted[$key])) {
                    continue;
                }

                $this->db->table('menu_roles')->insert([
                    'id_groups' => $groupId,
                    'id_menu' => $menuId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
                $inserted[$key] = true;
            }
        }
    }

    private function createMenuUserAccess(): void
    {
        if (!$this->db->tableExists('menu_user_access')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'id_user' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                ],
                'id_menu' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                ],
                'access_type' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 10,
                ],
                'created_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
                'updated_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->createTable('menu_user_access', true);
        }

        $this->addIndexIfMissing('menu_user_access', 'idx_menu_user_access_user', ['id_user']);
        $this->addUniqueIndexIfMissing('menu_user_access', 'uq_menu_user_access_user_menu', ['id_user', 'id_menu']);
    }

    private function ensureSettingMenu(): void
    {
        if (!$this->db->tableExists('menus')) {
            return;
        }

        $exists = $this->db->table('menus')
            ->where('url', 'menu-setting')
            ->countAllResults();

        if ($exists > 0) {
            return;
        }

        $maxOrder = $this->db->table('menus')->selectMax('sort_order')->get()->getRow();
        $this->db->table('menus')->insert([
            'name' => 'Setting Menu',
            'url' => 'menu-setting',
            'icon' => 'settings',
            'slug' => 'menu-setting',
            'parent_id' => 2,
            'is_active' => 1,
            'sort_order' => ((int) ($maxOrder->sort_order ?? 0)) + 1,
            'date_add' => date('Y-m-d H:i:s'),
            'date_edit' => date('Y-m-d H:i:s'),
        ]);
    }

    private function getValidMenuLookup(): array
    {
        if (!$this->db->tableExists('menus')) {
            return [];
        }

        $rows = $this->db->table('menus')->select('id')->get()->getResult();
        $lookup = [];
        foreach ($rows as $row) {
            $lookup[(int) $row->id] = true;
        }

        return $lookup;
    }

    private function addIndexIfMissing(string $table, string $index, array $columns): void
    {
        if ($this->indexExists($table, $index)) {
            return;
        }

        $columnList = implode('`, `', $columns);
        $this->db->query("ALTER TABLE `{$table}` ADD INDEX `{$index}` (`{$columnList}`)");
    }

    private function addUniqueIndexIfMissing(string $table, string $index, array $columns): void
    {
        if ($this->indexExists($table, $index)) {
            return;
        }

        $columnList = implode('`, `', $columns);
        $this->db->query("ALTER TABLE `{$table}` ADD UNIQUE INDEX `{$index}` (`{$columnList}`)");
    }

    private function indexExists(string $table, string $index): bool
    {
        $rows = $this->db->query("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$index])->getResult();

        return count($rows) > 0;
    }
}
