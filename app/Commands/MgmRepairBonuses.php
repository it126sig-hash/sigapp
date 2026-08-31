<?php

namespace App\Commands;

use App\Services\ReferralService;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class MgmRepairBonuses extends BaseCommand
{
    protected $group = 'MGM';

    protected $name = 'mgm:repair-bonuses';

    protected $description = 'Backfill missing MGM referral bonus rows for active referrals.';

    protected $usage = 'mgm:repair-bonuses [--dry-run]';

    protected $options = [
        '--dry-run' => 'Show missing bonus rows without writing changes.',
    ];

    public function run(array $params)
    {
        $dryRun = (bool) CLI::getOption('dry-run');
        $db = \Config\Database::connect();

        if (!$db->tableExists('referrals') || !$db->tableExists('referral_bonuses') || !$db->tableExists('referral_bonus_stages')) {
            CLI::error('MGM tables are incomplete. Run migrations first.');
            return;
        }

        $rows = $db->table('referrals r')
            ->select('
                r.id,
                r.id_mkdt_referred,
                r.id_proyek,
                mk.status_mkdt,
                COUNT(rb.id) AS existing_bonus_count,
                SUM(CASE WHEN rb.id IS NULL THEN 1 ELSE 0 END) AS missing_bonus_count
            ')
            ->join('mkdt mk', 'mk.id_mkdt = r.id_mkdt_referred')
            ->join('referral_bonus_stages st', 'st.id_proyek = r.id_proyek AND st.is_active = 1 AND LOWER(st.trigger_status_mkdt) = LOWER(mk.status_mkdt)')
            ->join('referral_bonuses rb', 'rb.id_referral = r.id AND rb.id_stage = st.id', 'left')
            ->where("COALESCE(r.status, 'active') =", 'active')
            ->groupBy('r.id, r.id_mkdt_referred, r.id_proyek, mk.status_mkdt')
            ->having('missing_bonus_count >', 0)
            ->orderBy('r.id', 'asc')
            ->get()
            ->getResultArray();

        if ($dryRun) {
            CLI::write('Dry run: ' . count($rows) . ' referral(s) need bonus repair.', count($rows) > 0 ? 'yellow' : 'green');
            foreach ($rows as $row) {
                CLI::write('Referral #' . $row['id'] . ' MKDT #' . $row['id_mkdt_referred'] . ' status ' . $row['status_mkdt'] . ' missing ' . $row['missing_bonus_count'] . ' bonus row(s).');
            }
            return;
        }

        $service = new ReferralService();
        $processed = 0;

        foreach ($rows as $row) {
            $service->checkAndActivateBonuses((int) $row['id_mkdt_referred'], (string) $row['status_mkdt']);
            $processed++;
        }

        CLI::write("Repaired {$processed} referral(s).", 'green');
    }
}
