<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class SeedSiteplanLegalPajakColors extends Migration
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
            ['config_name' => 'Belum Isi Data', 'fill' => '#fcffd9', 'keterangan' => 'Belum Isi Data'],
            ['config_name' => 'Data Legal Masuk', 'fill' => '#94a3b8', 'keterangan' => 'Data Legal Masuk'],
            ['config_name' => 'Sertipikat Induk Ada', 'fill' => '#a78bfa', 'keterangan' => 'Sertipikat Induk Ada'],
            ['config_name' => 'Split Sertipikat Diproses', 'fill' => '#c084fc', 'keterangan' => 'Split Sertipikat Diproses'],
            ['config_name' => 'Sertipikat Split Terbit', 'fill' => '#7c3aed', 'keterangan' => 'Sertipikat Split Terbit'],
            ['config_name' => 'PBB Pecah / NOP Terbit', 'fill' => '#38bdf8', 'keterangan' => 'PBB Pecah / NOP Terbit'],
            ['config_name' => 'PBG/IMB Terbit', 'fill' => '#2563eb', 'keterangan' => 'PBG/IMB Terbit'],
            ['config_name' => 'PPJB Dibuat', 'fill' => '#6366f1', 'keterangan' => 'PPJB Dibuat'],
            ['config_name' => 'PPH Dibayar / Validasi', 'fill' => '#f59e0b', 'keterangan' => 'PPH Dibayar / Validasi'],
            ['config_name' => 'BPHTB Dibayar / Validasi', 'fill' => '#d97706', 'keterangan' => 'BPHTB Dibayar / Validasi'],
            ['config_name' => 'AJB Dibuat', 'fill' => '#ec4899', 'keterangan' => 'AJB Dibuat'],
            ['config_name' => 'Balik Nama Sertipikat', 'fill' => '#22c55e', 'keterangan' => 'Balik Nama Sertipikat'],
            ['config_name' => 'Balik Nama PBB', 'fill' => '#16a34a', 'keterangan' => 'Balik Nama PBB'],
            ['config_name' => 'Selesai Legal', 'fill' => '#047857', 'keterangan' => 'Selesai Legal'],
            ['config_name' => 'Belum Input Pajak', 'fill' => '#e5e7eb', 'keterangan' => 'Belum Input Pajak'],
            ['config_name' => 'PPh4(2) Belum Bayar', 'fill' => '#fbbf24', 'keterangan' => 'PPh4(2) Belum Bayar'],
            ['config_name' => 'PPh4(2) Dibayar', 'fill' => '#f59e0b', 'keterangan' => 'PPh4(2) Dibayar'],
            ['config_name' => 'PPN Belum Bayar', 'fill' => '#38bdf8', 'keterangan' => 'PPN Belum Bayar'],
            ['config_name' => 'PPN Dibayar', 'fill' => '#2563eb', 'keterangan' => 'PPN Dibayar'],
            ['config_name' => 'Faktur Pajak Terbit', 'fill' => '#6366f1', 'keterangan' => 'Faktur Pajak Terbit'],
            ['config_name' => 'Selesai Pajak', 'fill' => '#047857', 'keterangan' => 'Selesai Pajak'],
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
