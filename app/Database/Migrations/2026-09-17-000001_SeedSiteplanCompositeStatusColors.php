<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class SeedSiteplanCompositeStatusColors extends Migration
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
            ['config_name' => 'Wawancara', 'fill' => '#8b5cf6', 'keterangan' => 'Tahap wawancara konsumen'],
            ['config_name' => 'Akad Indent', 'fill' => '#0ea5e9', 'keterangan' => 'Akad indent'],
            ['config_name' => 'Belum Lunas', 'fill' => '#fbbf24', 'keterangan' => 'Tagihan belum lunas'],
            ['config_name' => 'Pencairan Hasil Akad', 'fill' => '#22c55e', 'keterangan' => 'Dana hasil akad sudah dicairkan'],
        ];
    }
}
