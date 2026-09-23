<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class SeedSiteplanMkdtMarketColors extends Migration
{
    public function up()
    {
        if (!$this->db->tableExists('config_shape')) {
            return;
        }

        foreach ($this->colors() as $row) {
            $exists = $this->db->table('config_shape')
                ->where('config_name', $row['config_name'])
                ->countAllResults();

            if ($exists > 0) {
                continue;
            }

            $this->db->table('config_shape')->insert([
                'config_name' => $row['config_name'],
                'fill' => $row['fill'],
                'stroke' => '#000',
                'strokeWidth' => 0,
                'dashed' => '',
                'tipe' => 'status',
                'keterangan' => $row['keterangan'],
            ]);
        }
    }

    public function down()
    {
        if (!$this->db->tableExists('config_shape')) {
            return;
        }

        foreach ($this->colors() as $row) {
            $this->db->table('config_shape')
                ->where('config_name', $row['config_name'])
                ->where('fill', $row['fill'])
                ->where('keterangan', $row['keterangan'])
                ->delete();
        }
    }

    private function colors(): array
    {
        return [
            ['config_name' => 'Booking Subsidi', 'fill' => '#007bff', 'keterangan' => 'Booking kavling subsidi'],
            ['config_name' => 'Booking Komersil', 'fill' => '#4c1d95', 'keterangan' => 'Booking kavling komersil'],
            ['config_name' => 'Wawancara Subsidi', 'fill' => '#8b5cf6', 'keterangan' => 'Wawancara kavling subsidi'],
            ['config_name' => 'Wawancara Komersil', 'fill' => '#6d28d9', 'keterangan' => 'Wawancara kavling komersil'],
            ['config_name' => 'SP3K Subsidi', 'fill' => '#24fbff', 'keterangan' => 'SP3K kavling subsidi'],
            ['config_name' => 'SP3K Komersil', 'fill' => '#0e7490', 'keterangan' => 'SP3K kavling komersil'],
            ['config_name' => 'Akad Indent Subsidi', 'fill' => '#0ea5e9', 'keterangan' => 'Akad indent kavling subsidi'],
            ['config_name' => 'Akad Indent Komersil', 'fill' => '#0369a1', 'keterangan' => 'Akad indent kavling komersil'],
            ['config_name' => 'Akad Subsidi', 'fill' => '#dc3545', 'keterangan' => 'Akad kavling subsidi'],
            ['config_name' => 'Akad Komersil', 'fill' => '#575656', 'keterangan' => 'Akad kavling komersil'],
        ];
    }
}
