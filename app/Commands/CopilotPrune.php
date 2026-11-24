<?php

namespace App\Commands;

use App\Repositories\Copilot\MessageRepository;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class CopilotPrune extends BaseCommand
{
    protected $group       = 'Copilot';
    protected $name        = 'copilot:prune';
    protected $description = 'Hapus riwayat pesan AI Copilot yang lebih tua dari N hari.';
    protected $usage       = 'copilot:prune [--days=90]';
    protected $options     = [
        '--days' => 'Batas retensi hari sebelum pesan dihapus (default 90)',
    ];

    public function run(array $params)
    {
        $daysOpt = CLI::getOption('days');
        $defaultDays = (int) (config('Copilot')->retentionDays ?? 90);
        $days = $daysOpt !== null ? (int) $daysOpt : $defaultDays;

        if ($days <= 0) {
            CLI::error('Opsi --days harus berupa integer positif.');
            return;
        }

        $deleted = (new MessageRepository())->pruneOlderThanDays($days);

        CLI::write(
            sprintf('Berhasil menghapus %d pesan Copilot yang lebih lama dari %d hari.', $deleted, $days),
            'green'
        );
    }
}
