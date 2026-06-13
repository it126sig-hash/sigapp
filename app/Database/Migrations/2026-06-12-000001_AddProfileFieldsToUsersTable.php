<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddProfileFieldsToUsersTable extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('users')) {
            return;
        }

        $fields = [];

        if (! $this->db->fieldExists('name', 'users')) {
            $fields['name'] = [
                'type'       => 'VARCHAR',
                'constraint' => 120,
                'null'       => true,
                'after'      => 'username',
            ];
        }

        if (! $this->db->fieldExists('profile_photo', 'users')) {
            $fields['profile_photo'] = [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'name',
            ];
        }

        if (! empty($fields)) {
            $this->forge->addColumn('users', $fields);
        }
    }

    public function down()
    {
        if (! $this->db->tableExists('users')) {
            return;
        }

        $dropFields = [];

        foreach (['profile_photo', 'name'] as $field) {
            if ($this->db->fieldExists($field, 'users')) {
                $dropFields[] = $field;
            }
        }

        if (! empty($dropFields)) {
            $this->forge->dropColumn('users', $dropFields);
        }
    }
}
