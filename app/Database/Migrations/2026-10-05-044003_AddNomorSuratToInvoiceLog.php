<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddNomorSuratToInvoiceLog extends Migration
{
    public function up()
    {
        $this->forge->addColumn('invoice_log', [
            'nomor_surat'        => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'is_signed_direktur' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'signed_at'          => ['type' => 'DATETIME', 'null' => true],
            'signed_by'          => ['type' => 'INT', 'null' => true]
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('invoice_log', ['nomor_surat', 'is_signed_direktur', 'signed_at', 'signed_by']);
    }
}
