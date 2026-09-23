<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class TestIcon extends BaseCommand
{
    protected $group       = 'Testing';
    protected $name        = 'test:icon';
    protected $description = 'Test web push icon';

    /**
     * Actually execute a command.
     *
     * @param array $params
     */
    public function run(array $params)
    {
        $idProyek = 11;
        $proyekModel = new \App\Models\ProyekModel();
        $proyek = $proyekModel->find($idProyek);
        CLI::write("Proyek 11 logo: " . ($proyek->logo ?? 'none'));

        $fileAccessService = new \App\Services\FileAccessService();
        $originalPath = $fileAccessService->existingPath($proyek->logo ?? '');
        CLI::write("Original path: " . ($originalPath ?: 'not found'));
        if ($originalPath) {
            CLI::write("Original exists: " . (file_exists($originalPath) ? 'Yes' : 'No'));
        }
        
        $iconService = new \App\Services\NotificationIconService();
        $path = $iconService->getIconPath($idProyek);
        
        CLI::write("Final Path: " . $path);
    }
}
