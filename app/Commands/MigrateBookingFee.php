<?php

namespace App\Commands;

use App\Services\BookingFeeMigrationService;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Database;
use App\Database\Migrations\CreateMkdtBookingPayment;

class MigrateBookingFee extends BaseCommand
{
    protected $group = 'Finance';
    protected $name = 'booking:migrate';
    protected $description = 'Migrasi booking fee MKDT secara idempotent dan tulis laporan JSON sebelum/sesudah.';
    protected $usage = 'booking:migrate --report <absolute-path> [--database <name>] [--install-schema]';
    protected $options = [
        '--report' => 'Absolute path for the JSON report.',
        '--database' => 'Explicit database name; useful for the required rehearsal clone.',
        '--install-schema' => 'Apply the booking schema to the selected database first.',
    ];

    public function run(array $params)
    {
        $reportPath = CLI::getOption('report');
        if (! $reportPath || ! str_ends_with(strtolower((string) $reportPath), '.json')) {
            CLI::error('Gunakan --report dengan path JSON absolut.');
            return EXIT_ERROR;
        }
        if (! is_dir(dirname((string) $reportPath))) {
            CLI::error('Direktori laporan tidak ditemukan.');
            return EXIT_ERROR;
        }
        try {
            $databaseName = (string) (CLI::getOption('database') ?: '');
            $db = Database::connect();
            if ($databaseName !== '') {
                if (! preg_match('/^[A-Za-z0-9_]+$/', $databaseName)) throw new \InvalidArgumentException('Nama database tidak valid.');
                $config = config('Database')->default;
                $config['database'] = $databaseName;
                $db = Database::connect($config, false);
            }
            if (CLI::getOption('install-schema')) {
                require_once APPPATH . 'Database/Migrations/2026-09-16-000001_CreateMkdtBookingPayment.php';
                (new CreateMkdtBookingPayment(Database::forge($db)))->up();
            }
            $report = (new BookingFeeMigrationService($db))->run();
            $report['database'] = $databaseName ?: config('Database')->default['database'];
            $report['generated_at'] = date(DATE_ATOM);
            $json = json_encode($report, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR);
            if (file_put_contents((string) $reportPath, $json) === false) throw new \RuntimeException('Gagal menulis laporan.');
            CLI::write('Migrasi selesai. Laporan: ' . $reportPath, 'green');
            CLI::write('Sumber: ' . count($report['after']) . ' MKDT; anomali: ' . count($report['anomalies']));
            return EXIT_SUCCESS;
        } catch (\Throwable $e) {
            CLI::error($e->getMessage());
            return EXIT_ERROR;
        }
    }
}
