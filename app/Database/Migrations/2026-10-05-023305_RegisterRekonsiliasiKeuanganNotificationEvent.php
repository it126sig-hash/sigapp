<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RegisterRekonsiliasiKeuanganNotificationEvent extends Migration
{
    public function up()
    {
        $now = date('Y-m-d H:i:s');
        $this->db->table('notification_event_types')->ignore(true)->insert([
            'event_type'       => 'rekonsiliasi_keuangan',
            'category'         => 'keuangan',
            'label'            => 'Pembayaran Perlu Rekonsiliasi',
            'description'      => 'Saat ada pembayaran dengan alokasi yang tidak sesuai list tagihan MKDT',
            'default_in_app'   => 1,
            'default_email'    => 1,
            'default_web_push' => 1,
            'is_mandatory'     => 0,
            'relevant_groups'  => '4',
            'sort_order'       => 23,
            'created_at'       => $now,
            'updated_at'       => $now,
        ]);
    }

    public function down()
    {
        $this->db->table('notification_event_types')->where('event_type', 'rekonsiliasi_keuangan')->delete();
    }
}
