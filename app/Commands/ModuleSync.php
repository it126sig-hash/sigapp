<?php

namespace App\Commands;

use App\Libraries\ModuleManager\ModuleDocGenerator;
use App\Libraries\ModuleManager\ModuleScanner;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class ModuleSync extends BaseCommand
{
    protected $group = 'Module';

    protected $name = 'module:sync';

    protected $description = 'Menghasilkan atau memperbarui docs/modules/<Modul>/MODULE.md dari status kode aktual.';

    protected $usage = 'module:sync [ModuleName] [--all] [--check-only]';

    protected $options = [
        '--all'        => 'Sinkronkan seluruh modul yang ada di katalog',
        '--check-only' => 'Hanya periksa apakah ada MODULE.md yang outdated (berguna untuk pre-commit hook/CI)',
    ];

    public function run(array $params)
    {
        $targetModule = $params[0] ?? null;
        $syncAll      = (bool) CLI::getOption('all');
        $checkOnly    = (bool) CLI::getOption('check-only');

        if (!$targetModule && !$syncAll) {
            CLI::error("Tentukan nama modul (contoh: 'php spark module:sync Keuangan') atau gunakan opsi '--all'.");
            return;
        }

        CLI::write("=== SIGAPP MODULE.md Synchronizer ===", 'green');

        $scanner   = new ModuleScanner();
        $scanData  = $scanner->scan();
        $generator = new ModuleDocGenerator();

        $modulesToProcess = [];

        if ($syncAll) {
            $modulesToProcess = array_keys($scanData['modules']);
        } else {
            // Case-insensitive match
            $matchedKey = null;
            foreach (array_keys($scanData['modules']) as $modKey) {
                if (strcasecmp($modKey, $targetModule) === 0) {
                    $matchedKey = $modKey;
                    break;
                }
            }

            if (!$matchedKey) {
                CLI::error("Modul '{$targetModule}' tidak ditemukan di katalog.");
                CLI::write("Modul yang tersedia: " . implode(', ', array_keys($scanData['modules'])), 'yellow');
                return;
            }

            $modulesToProcess = [$matchedKey];
        }

        $docsDir = dirname(APPPATH) . DIRECTORY_SEPARATOR . 'docs' . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR;
        $outdatedCount = 0;

        foreach ($modulesToProcess as $modKey) {
            $modData = $scanData['modules'][$modKey];
            $docPath = $docsDir . $modKey . DIRECTORY_SEPARATOR . 'MODULE.md';

            if ($checkOnly) {
                if (!file_exists($docPath)) {
                    CLI::write("  [MISSING] {$modKey}: MODULE.md belum dibuat.", 'red');
                    $outdatedCount++;
                } else {
                    CLI::write("  [OK] {$modKey}: MODULE.md ada.", 'green');
                }
                continue;
            }

            $savedPath = $generator->syncModuleDoc($modData);
            CLI::write("  [SYNCED] {$modKey} -> " . str_replace(dirname(APPPATH) . DIRECTORY_SEPARATOR, '', $savedPath), 'green');
        }

        if ($checkOnly && $outdatedCount > 0) {
            CLI::error("Terdapat {$outdatedCount} modul yang belum memiliki MODULE.md.");
            exit(1);
        }

        CLI::newLine();
        CLI::write("Sinkronisasi selesai! Dokumentasi siap dibaca di folder docs/modules/.", 'cyan');
    }
}
