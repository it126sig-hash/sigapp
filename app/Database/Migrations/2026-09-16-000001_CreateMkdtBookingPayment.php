<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateMkdtBookingPayment extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('mkdt_booking_payment')) {
            $this->forge->addField([
                'id_mkdt' => ['type' => 'INT', 'unsigned' => true],
                'id_pembayaran' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
                'verified_by' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
                'verified_at' => ['type' => 'DATETIME', 'null' => true],
                'replaces_payment_ids' => ['type' => 'TEXT', 'null' => true],
                'created_at' => ['type' => 'DATETIME', 'null' => true],
                'updated_at' => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id_mkdt', true);
            $this->forge->addKey('id_pembayaran');
            $this->forge->createTable('mkdt_booking_payment');
        }
        if (! $this->db->fieldExists('booking_is_installment', 'log_pembayaran_detail')) {
            $this->forge->addColumn('log_pembayaran_detail', [
                'booking_is_installment' => ['type' => 'TINYINT', 'default' => 0],
            ]);
        }
        if (! $this->db->tableExists('booking_fee_migration_log')) {
            $this->forge->addField([
                'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
                'action_key' => ['type' => 'VARCHAR', 'constraint' => 190],
                'id_mkdt' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
                'id_pembayaran' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
                'action' => ['type' => 'VARCHAR', 'constraint' => 80],
                'before_json' => ['type' => 'LONGTEXT', 'null' => true],
                'after_json' => ['type' => 'LONGTEXT', 'null' => true],
                'created_at' => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addUniqueKey('action_key');
            $this->forge->addKey('id_mkdt');
            $this->forge->addKey('id_pembayaran');
            $this->forge->createTable('booking_fee_migration_log');
        }
    }

    public function down()
    {
        throw new \RuntimeException('Restore backup booking: data pembayaran hasil migrasi tidak boleh dihapus melalui rollback schema.');
    }
}
