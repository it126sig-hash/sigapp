<?php

namespace App\Commands;

use App\Libraries\ModuleManager\ModuleScanner;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class ModuleDeadcode extends BaseCommand
{
    protected $group = 'Module';

    protected $name = 'module:deadcode';

    protected $description = 'Mendeteksi kandidat dead code (metode tanpa callers) menggunakan engine codegraph.';

    protected $usage = 'module:deadcode [ModuleName] [--all] [--limit=<n>]';

    protected $options = [
        '--all'   => 'Scan dead code untuk seluruh modul',
        '--limit' => 'Batas maksimal metode yang di-scan per modul (default: 30)',
    ];

    public function run(array $params)
    {
        $targetModule = $params[0] ?? null;
        $scanAll      = (bool) CLI::getOption('all');
        $limit        = (int) (CLI::getOption('limit') ?: 30);

        if (!$targetModule && !$scanAll) {
            CLI::error("Tentukan nama modul (contoh: 'php spark module:deadcode Transaksi') atau gunakan '--all'.");
            return;
        }

        CLI::write("=== SIGAPP Dead Code Auditor (Powered by CodeGraph) ===", 'green');

        $scanner  = new ModuleScanner();
        $scanData = $scanner->scan();

        $modulesToProcess = [];
        if ($scanAll) {
            $modulesToProcess = array_keys($scanData['modules']);
        } else {
            $matchedKey = null;
            foreach (array_keys($scanData['modules']) as $modKey) {
                if (strcasecmp($modKey, $targetModule) === 0) {
                    $matchedKey = $modKey;
                    break;
                }
            }

            if (!$matchedKey) {
                CLI::error("Modul '{$targetModule}' tidak ditemukan di katalog.");
                return;
            }
            $modulesToProcess = [$matchedKey];
        }

        foreach ($modulesToProcess as $modKey) {
            $mod = $scanData['modules'][$modKey];
            CLI::newLine();
            CLI::write("Scanning modul: [{$modKey}] ...", 'yellow');

            // Kumpulkan route handlers untuk membedakan entry points
            $routeHandlers = [];
            foreach ($mod['routes'] as $r) {
                $parts = explode('::', $r['handler']);
                if (isset($parts[1])) {
                    $action = explode('/', $parts[1])[0];
                    $routeHandlers[strtolower($action)] = true;
                }
            }

            // Kumpulkan method dari controllers, services, repositories
            $methodsToCheck = [];
            
            foreach ($mod['controllers'] as $c) {
                foreach ($c['public_methods'] as $mSig) {
                    $mName = explode('(', $mSig)[0];
                    if (!isset($routeHandlers[strtolower($mName)])) {
                        $methodsToCheck[] = [
                            'layer' => 'Controller',
                            'file'  => $c['filename'],
                            'name'  => $mName,
                        ];
                    }
                }
            }

            foreach ($mod['services'] as $s) {
                foreach ($s['public_methods'] as $mSig) {
                    $mName = explode('(', $mSig)[0];
                    $methodsToCheck[] = [
                        'layer' => 'Service',
                        'file'  => $s['filename'],
                        'name'  => $mName,
                    ];
                }
            }

            foreach ($mod['repositories'] as $r) {
                foreach ($r['public_methods'] as $mSig) {
                    $mName = explode('(', $mSig)[0];
                    $methodsToCheck[] = [
                        'layer' => 'Repository',
                        'file'  => $r['filename'],
                        'name'  => $mName,
                    ];
                }
            }

            if (empty($methodsToCheck)) {
                CLI::write("  Tidak ada metode internal yang perlu diperiksa.", 'light_gray');
                continue;
            }

            $deadCandidates = [];
            $checkedCount = 0;

            foreach (array_slice($methodsToCheck, 0, $limit) as $item) {
                $checkedCount++;
                $mName = $item['name'];

                // Panggil codegraph CLI
                $cmd = "codegraph callers " . escapeshellarg($mName) . " 2>&1";
                $output = shell_exec($cmd) ?? '';

                if (str_contains($output, 'No callers found') || str_contains($output, 'Callers of') && !str_contains($output, 'app/')) {
                    $deadCandidates[] = $item;
                    CLI::write("  [!] 0 Callers: {$item['layer']} {$item['file']}::{$mName}()", 'red');
                }
            }

            CLI::write("  Selesai memeriksa {$checkedCount} metode: ditemukan " . count($deadCandidates) . " kandidat dead code.", 'cyan');

            // Simpan temuan jika ada
            if (!empty($deadCandidates)) {
                $deadLogDir = WRITEPATH . 'modules' . DIRECTORY_SEPARATOR;
                if (!is_dir($deadLogDir)) mkdir($deadLogDir, 0755, true);
                file_put_contents(
                    $deadLogDir . 'deadcode_' . $modKey . '.json',
                    json_encode($deadCandidates, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
                );
            }
        }

        CLI::newLine();
        CLI::write("Audit dead code selesai!", 'green');
    }
}
