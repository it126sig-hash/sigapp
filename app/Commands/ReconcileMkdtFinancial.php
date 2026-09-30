<?php

namespace App\Commands;

use App\Services\MkdtFinancialReconciliationService;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class ReconcileMkdtFinancial extends BaseCommand
{
    protected $group = 'Finance';
    protected $name = 'finance:reconcile-mkdt';
    protected $description = 'Audit atau terapkan manifest rekonsiliasi komponen keuangan MKDT.';
    protected $usage = 'finance:reconcile-mkdt --report <absolute.json> [--id-mkdt <id>] [--apply <approved.json>]';
    protected $options = [
        '--report' => 'Path JSON absolut untuk hasil audit/apply.',
        '--id-mkdt' => 'Batasi audit ke satu ID MKDT.',
        '--apply' => 'Path JSON absolut manifest yang telah disetujui.',
    ];

    public function run(array $params)
    {
        try {
            $reportPath = (string) (CLI::getOption('report') ?: '');
            $this->assertJsonAbsolutePath($reportPath, false);
            $service = new MkdtFinancialReconciliationService(\Config\Database::connect());
            $applyPath = (string) (CLI::getOption('apply') ?: '');

            if ($applyPath !== '') {
                $this->assertJsonAbsolutePath($applyPath, true);
                $manifest = json_decode((string) file_get_contents($applyPath), true, 512, JSON_THROW_ON_ERROR);
                $report = $service->apply($manifest);
                CLI::write('Manifest diterapkan: ' . count($report['applied']) . '; dilewati: ' . count($report['skipped']), 'green');
            } else {
                $id = CLI::getOption('id-mkdt');
                $report = $service->audit($id !== null && $id !== false && $id !== '' ? (int) $id : null);
                CLI::write('Audit selesai. Manifest usulan: ' . $report['counts']['manifest_rows'] . ' MKDT.', 'green');
            }

            $json = json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
            if (file_put_contents($reportPath, $json) === false) {
                throw new \RuntimeException('Gagal menulis laporan JSON.');
            }
            CLI::write('Laporan: ' . $reportPath);
            return EXIT_SUCCESS;
        } catch (\Throwable $e) {
            CLI::error($e->getMessage());
            return EXIT_ERROR;
        }
    }

    private function assertJsonAbsolutePath(string $path, bool $mustExist): void
    {
        $absolute = preg_match('/^[A-Za-z]:[\\\\\/]/', $path) === 1 || str_starts_with($path, '/');
        if (!$absolute || !str_ends_with(strtolower($path), '.json')) {
            throw new \InvalidArgumentException('Gunakan path JSON absolut.');
        }
        if ($mustExist && !is_file($path)) {
            throw new \InvalidArgumentException('File manifest tidak ditemukan.');
        }
        if (!$mustExist && !is_dir(dirname($path))) {
            throw new \InvalidArgumentException('Direktori laporan tidak ditemukan.');
        }
    }
}
