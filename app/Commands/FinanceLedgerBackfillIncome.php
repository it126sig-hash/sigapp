<?php

namespace App\Commands;

use App\Services\FinanceLedgerService;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class FinanceLedgerBackfillIncome extends BaseCommand
{
    protected $group = 'Finance';

    protected $name = 'finance:ledger-backfill-income';

    protected $description = 'Backfill finance_ledger income rows from active log_pembayaran records.';

    protected $usage = 'finance:ledger-backfill-income [--dry-run]';

    protected $options = [
        '--dry-run' => 'Count and total active log_pembayaran records without writing ledger rows.',
    ];

    public function run(array $params)
    {
        $dryRun = (bool) CLI::getOption('dry-run');
        $db = \Config\Database::connect();

        if (!$db->tableExists('finance_ledger')) {
            CLI::error('Table finance_ledger does not exist. Run migrations first.');
            return;
        }

        $summary = $db->table('log_pembayaran')
            ->select('COUNT(*) AS total_rows, COALESCE(SUM(nominal), 0) AS total_nominal')
            ->where('COALESCE(is_deleted, 0) = 0', null, false)
            ->get()
            ->getRow();

        if ($dryRun) {
            CLI::write(
                'Dry run: ' . (int) $summary->total_rows . ' active payment(s), total Rp ' . number_format((float) $summary->total_nominal, 2),
                'yellow'
            );
            return;
        }

        $ledger = new FinanceLedgerService();
        $processed = 0;
        $failed = 0;

        $rows = $db->table('log_pembayaran')
            ->select('id_pembayaran')
            ->where('COALESCE(is_deleted, 0) = 0', null, false)
            ->orderBy('id_pembayaran', 'asc')
            ->get()
            ->getResultArray();

        foreach ($rows as $row) {
            try {
                $ledger->recordIncomeFromLogPembayaran((int) $row['id_pembayaran']);
                $processed++;
            } catch (\Throwable $e) {
                $failed++;
                CLI::write('Failed id_pembayaran ' . $row['id_pembayaran'] . ': ' . $e->getMessage(), 'red');
            }
        }

        CLI::write("Backfilled {$processed} payment(s), failed {$failed}.", $failed > 0 ? 'yellow' : 'green');
    }
}
