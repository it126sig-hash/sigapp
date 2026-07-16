<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class SeedSiteplanPencairanAkadColors extends Migration
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
            ['config_name' => 'Hasil Akad Belum Cair', 'fill' => '#f59e0b', 'keterangan' => 'Belum ada pengajuan pencairan hasil akad'],
            ['config_name' => 'Pengajuan Pencairan Hasil Akad', 'fill' => '#2563eb', 'keterangan' => 'Sudah diajukan pencairan hasil akad, belum lunas/cair penuh'],
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
