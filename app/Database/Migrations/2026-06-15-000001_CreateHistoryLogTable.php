<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateHistoryLogTable extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('history_log')) {
            return;
        }

        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'module' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            'reference_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'reference_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'id_kavling' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'id_proyek' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'action' => [
                'type'       => 'VARCHAR',
                'constraint' => 80,
            ],
            'summary' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'old_data' => [
                'type' => 'LONGTEXT',
                'null' => true,
            ],
            'new_data' => [
                'type' => 'LONGTEXT',
                'null' => true,
            ],
            'metadata' => [
                'type' => 'LONGTEXT',
                'null' => true,
            ],
            'legacy_table' => [
                'type'       => 'VARCHAR',
                'constraint' => 80,
                'null'       => true,
            ],
            'legacy_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'add_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('module');
        $this->forge->addKey('id_kavling');
        $this->forge->addKey('id_proyek');
        $this->forge->addKey('add_by');
        $this->forge->addKey('created_at');
        $this->forge->addKey(['module', 'id_kavling', 'created_at']);
        $this->forge->addKey(['module', 'id_proyek', 'created_at']);
        $this->forge->addKey(['reference_type', 'reference_id']);
        $this->forge->addKey(['legacy_table', 'legacy_id']);
        $this->forge->createTable('history_log', true);

        $this->addUniqueIndexIfMissing('history_log', 'uq_history_log_legacy', ['legacy_table', 'legacy_id']);
    }

    public function down()
    {
        $this->forge->dropTable('history_log', true);
    }

    private function addUniqueIndexIfMissing(string $table, string $index, array $columns): void
    {
        $rows = $this->db->query("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$index])->getResult();
        if (count($rows) > 0) {
            return;
        }

        $columnList = implode('`, `', $columns);
        $this->db->query("ALTER TABLE `{$table}` ADD UNIQUE INDEX `{$index}` (`{$columnList}`)");
    }
}
