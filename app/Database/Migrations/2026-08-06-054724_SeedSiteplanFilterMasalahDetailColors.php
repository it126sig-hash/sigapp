<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class SeedSiteplanFilterMasalahDetailColors extends Migration
{
    public function up()
    {
        if (!$this->db->tableExists('config_shape')) {
            return;
        }

        foreach ($this->colors() as $row) {
            $this->upsertColor($row);
        }
    }

    public function down()
    {
        if (!$this->db->tableExists('config_shape')) {
            return;
        }

        $names = array_column($this->colors(), 'config_name');
        if ($names) {
            $this->db->table('config_shape')->whereIn('config_name', $names)->delete();
        }
    }

    private function colors(): array
    {
        return [
            ['config_name' => 'Masalah Progress', 'fill' => '#eab308', 'keterangan' => 'Masalah Progress'], // Yellow
            ['config_name' => 'Masalah Selesai', 'fill' => '#22c55e', 'keterangan' => 'Masalah Selesai'], // Green
            ['config_name' => 'Masalah Batal', 'fill' => '#9ca3af', 'keterangan' => 'Masalah Batal'], // Gray
            ['config_name' => 'Masalah Hold', 'fill' => '#f97316', 'keterangan' => 'Masalah Hold'], // Orange
            ['config_name' => 'Masalah Baru Dibuat', 'fill' => '#3b82f6', 'keterangan' => 'Masalah Baru Dibuat'], // Blue
        ];
    }

    private function upsertColor(array $seed): void
    {
        $row = [
            'fill'        => $seed['fill'],
            'stroke'      => '#000',
            'strokeWidth' => 0,
            'dashed'      => '',
            'tipe'        => 'status',
            'keterangan'  => $seed['keterangan'],
        ];

        $exists = $this->db->table('config_shape')
            ->where('config_name', $seed['config_name'])
            ->countAllResults();

        if ($exists > 0) {
            $this->db->table('config_shape')
                ->where('config_name', $seed['config_name'])
                ->update($row);
            return;
        }

        $row['config_name'] = $seed['config_name'];
        $this->db->table('config_shape')->insert($row);
    }
}
