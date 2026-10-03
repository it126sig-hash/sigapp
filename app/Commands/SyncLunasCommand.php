<?php 
namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Services\MkdtSettlementService;

class SyncLunasCommand extends BaseCommand
{
    protected $group = "App";
    protected $name = "app:sync-lunas";
    protected $description = "Backfills is_lunas status for historical MKDT records.";

    public function run(array $params)
    {
        $db = \Config\Database::connect();
        $service = new MkdtSettlementService($db);

        $mkdts = $db->table('mkdt')
            ->select('id_mkdt')
            ->where('is_lunas', 0)
            ->where('status_mkdt !=', 'Batal')
            ->get()->getResultArray();

        $updatedCount = 0;
        CLI::write("Found " . count($mkdts) . " active MKDTs with is_lunas = 0. Syncing...", 'yellow');

        foreach ($mkdts as $mkdt) {
            $id = (int) $mkdt['id_mkdt'];
            $result = $service->synchronize($id);
            if ($result['changed']) {
                $updatedCount++;
                CLI::write("Updated MKDT {$id} to Lunas (Tagihan: {$result['total_tagihan']})", 'green');
            }
        }

        CLI::write("Done. Successfully backfilled {$updatedCount} records.", 'white', 'green');
    }
}
