<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTanggalPengajuanSalesToFeeSalesPengajuan extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('fee_sales_pengajuan')) {
            return;
        }

        if (! $this->db->fieldExists('tanggal_pengajuan_sales', 'fee_sales_pengajuan')) {
            $this->forge->addColumn('fee_sales_pengajuan', [
                'tanggal_pengajuan_sales' => [
                    'type'  => 'DATE',
                    'null'  => true,
                    'after' => 'tanggal_pengajuan',
                ],
            ]);
        }

        $this->db->query('UPDATE fee_sales_pengajuan SET tanggal_pengajuan_sales = tanggal_pengajuan WHERE tanggal_pengajuan_sales IS NULL');
    }

    public function down()
    {
        if (! $this->db->tableExists('fee_sales_pengajuan')) {
            return;
        }

        if ($this->db->fieldExists('tanggal_pengajuan_sales', 'fee_sales_pengajuan')) {
            $this->forge->dropColumn('fee_sales_pengajuan', 'tanggal_pengajuan_sales');
        }
    }
}
