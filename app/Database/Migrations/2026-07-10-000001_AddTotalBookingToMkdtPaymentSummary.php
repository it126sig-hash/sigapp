<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTotalBookingToMkdtPaymentSummary extends Migration
{
    public function up()
    {
        if (!$this->db->tableExists('mkdt_payment_summary') || $this->db->fieldExists('total_booking', 'mkdt_payment_summary')) {
            return;
        }

        $this->forge->addColumn('mkdt_payment_summary', [
            'total_booking' => [
                'type'    => 'DOUBLE',
                'null'    => true,
                'default' => 0,
                'after'   => 'total_adm',
            ],
        ]);
    }

    public function down()
    {
        if ($this->db->tableExists('mkdt_payment_summary') && $this->db->fieldExists('total_booking', 'mkdt_payment_summary')) {
            $this->forge->dropColumn('mkdt_payment_summary', 'total_booking');
        }
    }
}
