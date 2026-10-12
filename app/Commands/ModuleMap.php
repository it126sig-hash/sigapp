<?php

namespace App\Commands;

use App\Libraries\ModuleManager\ModuleScanner;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class ModuleMap extends BaseCommand
{
    protected $group = 'Module';

    protected $name = 'module:map';

    protected $description = 'Memetakan seluruh file dan rute aplikasi ke dalam 16 Modul Bisnis SIGAPP.';

    protected $usage = 'module:map [--output=<path>]';

    protected $options = [
        '--output' => 'Lokasi file output JSON (default: writable/modules/module_map.json)',
    ];

    public function run(array $params)
    {
        CLI::write("=== SIGAPP Modular Mapper ===", 'green');
        CLI::write("Memindai direktori controllers, services, repositories, models, views, dan assets...", 'yellow');

        $scanner = new ModuleScanner();
        $result  = $scanner->scan();

        $outputPath = CLI::getOption('output');
        if (empty($outputPath)) {
            $outputPath = WRITEPATH . 'modules' . DIRECTORY_SEPARATOR . 'module_map.json';
        }

        $dir = dirname($outputPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        file_put_contents($outputPath, json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        CLI::newLine();
        CLI::write("Pemetaan selesai! Rangkuman:", 'green');
        CLI::write("  - Total Modul Bisnis: " . $result['total_modules']);
        CLI::write("  - Output tersimpan di: " . $outputPath);

        $unmappedCount = count($result['unmapped_files']['controllers'])
            + count($result['unmapped_files']['services'])
            + count($result['unmapped_files']['repositories'])
            + count($result['unmapped_files']['models']);

        if ($unmappedCount > 0) {
            CLI::write("  - File belum terpetakan (unmapped): " . $unmappedCount . " file", 'yellow');
            foreach ($result['unmapped_files'] as $category => $files) {
                if (!empty($files)) {
                    CLI::write("    * " . ucfirst($category) . ": " . implode(', ', array_map('basename', $files)), 'light_gray');
                }
            }
        } else {
            CLI::write("  - Seluruh file berhasil dipetakan 100%!", 'green');
        }

        CLI::newLine();
        CLI::write("Jalankan 'php spark module:status' untuk melihat tabel kesiapan modul.", 'cyan');
        CLI::write("Jalankan 'php spark module:sync --all' untuk menghasilkan dokumen MODULE.md awal.", 'cyan');
    }
}
