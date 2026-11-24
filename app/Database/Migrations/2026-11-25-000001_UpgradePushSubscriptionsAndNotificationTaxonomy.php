<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use Config\Database;

class UpgradePushSubscriptionsAndNotificationTaxonomy extends Migration
{
    private const PERMOHONAN_URL = 'permohonan';

    public function up()
    {
        $this->upgradePushSubscriptions();
        $this->syncNotificationEventTypes();
        $this->registerPermohonanMenu();
    }

    public function down()
    {
        $this->unregisterPermohonanMenu();
        $this->downgradePushSubscriptions();
    }

    private function upgradePushSubscriptions(): void
    {
        if (! $this->db->tableExists('push_subscriptions')) {
            return;
        }

        $columnsToAdd = [];

        if (! $this->db->fieldExists('provider', 'push_subscriptions')) {
            $columnsToAdd['provider'] = [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => false,
                'default'    => 'web_push',
                'after'      => 'user_id',
            ];
        }

        if (! $this->db->fieldExists('fcm_token', 'push_subscriptions')) {
            $columnsToAdd['fcm_token'] = [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'endpoint_hash',
            ];
        }

        if (! $this->db->fieldExists('platform', 'push_subscriptions')) {
            $columnsToAdd['platform'] = [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
                'default'    => 'web',
                'after'      => 'fcm_token',
            ];
        }

        if ($columnsToAdd !== []) {
            $this->forge->addColumn('push_subscriptions', $columnsToAdd);
        }

        $columnsToModify = [];
        if ($this->db->fieldExists('p256dh_key', 'push_subscriptions')) {
            $columnsToModify['p256dh_key'] = [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'default'    => null,
            ];
        }

        if ($this->db->fieldExists('auth_token', 'push_subscriptions')) {
            $columnsToModify['auth_token'] = [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'default'    => null,
            ];
        }

        if ($columnsToModify !== []) {
            $this->forge->modifyColumn('push_subscriptions', $columnsToModify);
        }
    }

    private function downgradePushSubscriptions(): void
    {
        if (! $this->db->tableExists('push_subscriptions')) {
            return;
        }

        foreach (['platform', 'fcm_token', 'provider'] as $column) {
            if ($this->db->fieldExists($column, 'push_subscriptions')) {
                $this->forge->dropColumn('push_subscriptions', $column);
            }
        }
    }

    private function syncNotificationEventTypes(): void
    {
        if (! $this->db->tableExists('notification_event_types')) {
            return;
        }

        Database::seeder()->call('NotificationEventTypeSeeder');
    }

    private function registerPermohonanMenu(): void
    {
        if (! $this->db->tableExists('menus')) {
            return;
        }

        $existing = $this->db->table('menus')
            ->select('id')
            ->where('url', self::PERMOHONAN_URL)
            ->get()
            ->getRow();

        $now = date('Y-m-d H:i:s');

        if (! $existing) {
            $this->db->table('menus')->insert([
                'name'       => 'Permohonan & Approval',
                'url'        => self::PERMOHONAN_URL,
                'icon'       => 'check-square',
                'slug'       => 'permohonan',
                'parent_id'  => 0,
                'is_active'  => 1,
                'sort_order' => 22,
                'date_add'   => $now,
                'date_edit'  => $now,
            ]);
            $menuId = (int) $this->db->insertID();
        } else {
            $menuId = (int) $existing->id;
        }

        if ($menuId <= 0 || ! $this->db->tableExists('menu_roles')) {
            return;
        }

        $groupIds = range(1, 10);
        if ($this->db->tableExists('auth_groups')) {
            $dbGroups = $this->db->table('auth_groups')->select('id')->get()->getResult();
            foreach ($dbGroups as $group) {
                $gid = (int) $group->id;
                if ($gid > 0 && ! in_array($gid, $groupIds, true)) {
                    $groupIds[] = $gid;
                }
            }
        }

        foreach ($groupIds as $groupId) {
            $exists = $this->db->table('menu_roles')
                ->where('id_groups', $groupId)
                ->where('id_menu', $menuId)
                ->countAllResults();

            if ($exists === 0) {
                $this->db->table('menu_roles')->insert([
                    'id_groups'  => $groupId,
                    'id_menu'    => $menuId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    private function unregisterPermohonanMenu(): void
    {
        if (! $this->db->tableExists('menus')) {
            return;
        }

        $menu = $this->db->table('menus')
            ->select('id')
            ->where('url', self::PERMOHONAN_URL)
            ->get()
            ->getRow();

        if (! $menu) {
            return;
        }

        $menuId = (int) $menu->id;

        if ($this->db->tableExists('menu_roles')) {
            $this->db->table('menu_roles')->where('id_menu', $menuId)->delete();
        }

        $this->db->table('menus')->where('id', $menuId)->delete();
    }
}
