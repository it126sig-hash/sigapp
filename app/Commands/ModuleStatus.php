<?php

namespace App\Commands;

use App\Libraries\ModuleManager\ModuleScanner;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class ModuleStatus extends BaseCommand
{
    protected $group = 'Module';

    protected $name = 'module:status';

    protected $description = 'Menampilkan tabel dashboard status dan skor kesiapan 16 Modul Bisnis SIGAPP.';

    protected $usage = 'module:status';

    public function run(array $params)
    {
        CLI::write("=== SIGAPP Modular Status Dashboard ===", 'green');
        CLI::newLine();

        $scanner  = new ModuleScanner();
        $scanData = $scanner->scan();

        $rows            = [];
        $totalScore      = 0;
        $refactoredCount = 0;
        $mappedCount     = 0;

        foreach ($scanData['modules'] as $key => $m) {
            $score = $m['readiness_score'];
            $totalScore += $score;

            if ($m['status'] === 'REFACTORED') {
                $refactoredCount++;
            } elseif ($m['status'] === 'MAPPED') {
                $mappedCount++;
            }

            $scoreDisplay = $score . '%';
            if ($score >= 80) {
                $scoreDisplay = CLI::color($scoreDisplay, 'green');
            } elseif ($score >= 60) {
                $scoreDisplay = CLI::color($scoreDisplay, 'yellow');
            } else {
                $scoreDisplay = CLI::color($scoreDisplay, 'red');
            }

            $statusDisplay = $m['status'];
            if ($statusDisplay === 'REFACTORED') {
                $statusDisplay = CLI::color($statusDisplay, 'green');
            } elseif ($statusDisplay === 'MAPPED') {
                $statusDisplay = CLI::color($statusDisplay, 'cyan');
            } else {
                $statusDisplay = CLI::color($statusDisplay, 'light_gray');
            }

            $rows[] = [
                $key,
                $statusDisplay,
                $scoreDisplay,
                count($m['controllers']),
                count($m['services']),
                count($m['repositories']),
                count($m['models']),
                count($m['routes']),
                count($m['owner_tables']),
            ];
        }

        $headers = [
            'Modul',
            'Status',
            'Score',
            'Ctrl',
            'Serv',
            'Repo',
            'Model',
            'Route',
            'Tables',
        ];

        CLI::table($rows, $headers);

        $avgScore = round($totalScore / count($scanData['modules']), 1);

        CLI::newLine();
        CLI::write("Statistik Keseluruhan:", 'yellow');
        CLI::write("  - Total Modul: " . count($scanData['modules']));
        CLI::write("  - Rata-rata Skor Kesiapan: {$avgScore}%");
        CLI::write("  - Modul Refactored (REFACTORED, Score >= 80%): {$refactoredCount} dari " . count($scanData['modules']));
        CLI::write("  - Modul Terpetakan (MAPPED, Score < 80%): {$mappedCount} dari " . count($scanData['modules']));
        CLI::newLine();
        CLI::write("Tips: Jalankan 'php spark module:sync <Modul>' untuk update dokumen MODULE.md setelah refaktor.", 'cyan');
    }
}
