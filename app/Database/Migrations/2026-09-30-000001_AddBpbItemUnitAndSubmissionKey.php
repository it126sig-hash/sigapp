<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddBpbItemUnitAndSubmissionKey extends Migration
{
    private const SUBMISSION_KEY_INDEX = 'uq_bpb_requests_submission_key';

    public function up()
    {
        $this->forge->addColumn('bpb_items', [
            'satuan' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
        ]);

        $this->forge->addColumn('bpb_requests', [
            'submission_key' => ['type' => 'CHAR', 'constraint' => 64, 'null' => true],
        ]);
        $this->db->query('ALTER TABLE `bpb_requests` ADD UNIQUE KEY `' . self::SUBMISSION_KEY_INDEX . '` (`submission_key`)');
    }

    public function down()
    {
        $this->db->query('ALTER TABLE `bpb_requests` DROP INDEX `' . self::SUBMISSION_KEY_INDEX . '`');
        $this->forge->dropColumn('bpb_requests', 'submission_key');
        $this->forge->dropColumn('bpb_items', 'satuan');
    }
}
