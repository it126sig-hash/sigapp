<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddMemberGetMemberRecipientFields extends Migration
{
    public function up()
    {
        $fields = [];

        if (!$this->db->fieldExists('paid_promosi_tanggal', 'referral_bonuses')) {
            $fields['paid_promosi_tanggal'] = [
                'type' => 'DATE',
                'null' => true,
                'after' => 'paid_promosi_at',
            ];
        }

        if (!$this->db->fieldExists('paid_promosi_penerima_nama', 'referral_bonuses')) {
            $fields['paid_promosi_penerima_nama'] = [
                'type' => 'VARCHAR',
                'constraint' => 150,
                'null' => true,
                'after' => 'bukti_bayar_promosi',
            ];
        }

        if (!$this->db->fieldExists('paid_promosi_no_rekening', 'referral_bonuses')) {
            $fields['paid_promosi_no_rekening'] = [
                'type' => 'VARCHAR',
                'constraint' => 60,
                'null' => true,
                'after' => 'paid_promosi_penerima_nama',
            ];
        }

        if (!$this->db->fieldExists('paid_promosi_bank', 'referral_bonuses')) {
            $fields['paid_promosi_bank'] = [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
                'after' => 'paid_promosi_no_rekening',
            ];
        }

        if (!$this->db->fieldExists('cair_keuangan_penerima_nama', 'referral_bonuses')) {
            $fields['cair_keuangan_penerima_nama'] = [
                'type' => 'VARCHAR',
                'constraint' => 150,
                'null' => true,
                'after' => 'cair_keuangan_at',
            ];
        }

        if (!$this->db->fieldExists('cair_keuangan_no_rekening', 'referral_bonuses')) {
            $fields['cair_keuangan_no_rekening'] = [
                'type' => 'VARCHAR',
                'constraint' => 60,
                'null' => true,
                'after' => 'cair_keuangan_penerima_nama',
            ];
        }

        if (!$this->db->fieldExists('cair_keuangan_bank', 'referral_bonuses')) {
            $fields['cair_keuangan_bank'] = [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
                'after' => 'cair_keuangan_no_rekening',
            ];
        }

        if ($fields !== []) {
            $this->forge->addColumn('referral_bonuses', $fields);
        }
    }

    public function down()
    {
        foreach ([
            'paid_promosi_tanggal',
            'paid_promosi_penerima_nama',
            'paid_promosi_no_rekening',
            'paid_promosi_bank',
            'cair_keuangan_penerima_nama',
            'cair_keuangan_no_rekening',
            'cair_keuangan_bank',
        ] as $field) {
            if ($this->db->fieldExists($field, 'referral_bonuses')) {
                $this->forge->dropColumn('referral_bonuses', $field);
            }
        }
    }
}
