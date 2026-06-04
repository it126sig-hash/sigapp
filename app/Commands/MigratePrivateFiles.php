<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class MigratePrivateFiles extends BaseCommand
{
    protected $group = 'Files';

    protected $name = 'files:migrate-private';

    protected $description = 'Copy or move public upload files into writable/protected_uploads.';

    protected $usage = 'files:migrate-private [--dry-run] [--move]';

    protected $options = [
        '--dry-run' => 'Show what would be copied without writing files.',
        '--move'    => 'Move files instead of copying them.',
    ];

    public function run(array $params)
    {
        $dryRun = (bool) CLI::getOption('dry-run');
        $move = (bool) CLI::getOption('move');

        $sources = [
            FCPATH . 'uploads' => WRITEPATH . 'protected_uploads' . DIRECTORY_SEPARATOR . 'uploads',
            FCPATH . 'upload'  => WRITEPATH . 'protected_uploads' . DIRECTORY_SEPARATOR . 'upload',
        ];

        $total = 0;
        $done = 0;
        $skipped = 0;

        foreach ($sources as $sourceRoot => $targetRoot) {
            if (!is_dir($sourceRoot)) {
                CLI::write("Skip missing: {$sourceRoot}", 'yellow');
                continue;
            }

            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($sourceRoot, \FilesystemIterator::SKIP_DOTS)
            );

            foreach ($files as $file) {
                if (!$file->isFile()) {
                    continue;
                }

                $total++;
                $relative = ltrim(str_replace($sourceRoot, '', $file->getPathname()), DIRECTORY_SEPARATOR);
                $target = rtrim($targetRoot, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $relative;

                if (is_file($target) && filesize($target) === $file->getSize()) {
                    $skipped++;
                    continue;
                }

                if (!$dryRun) {
                    $targetDir = dirname($target);
                    if (!is_dir($targetDir)) {
                        mkdir($targetDir, 0775, true);
                    }

                    $ok = $move ? rename($file->getPathname(), $target) : copy($file->getPathname(), $target);
                    if (!$ok) {
                        CLI::error("Failed: {$file->getPathname()}");
                        continue;
                    }
                }

                $done++;
            }
        }

        $mode = $dryRun ? 'Dry run' : ($move ? 'Moved' : 'Copied');
        CLI::write("{$mode}: {$done} file(s), skipped {$skipped}, scanned {$total}.", 'green');
    }
}
