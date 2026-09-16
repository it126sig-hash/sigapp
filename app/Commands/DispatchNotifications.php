<?php

namespace App\Commands;

use App\Services\NotificationDispatchService;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class DispatchNotifications extends BaseCommand
{
    protected $group       = 'Notifikasi';
    protected $name        = 'notif:dispatch';
    protected $description = 'Memproses delivery outbox notifikasi untuk shared hosting cron';

    protected $options = [
        '--channel' => 'Channel delivery: web_push',
        '--limit' => 'Jumlah maksimum delivery yang diproses',
    ];

    public function run(array $params)
    {
        $channel = CLI::getOption('channel') ?: 'web_push';
        $limit = (int) (CLI::getOption('limit') ?: 100);

        CLI::write("Memproses delivery {$channel}...", 'yellow');
        $stats = (new NotificationDispatchService())->dispatch($channel, $limit);

        CLI::write(sprintf(
            'Selesai. Claimed: %d, sent: %d, retrying: %d, failed: %d, skipped: %d. %s',
            $stats['claimed'] ?? 0,
            $stats['sent'] ?? 0,
            $stats['retrying'] ?? 0,
            $stats['failed'] ?? 0,
            $stats['skipped'] ?? 0,
            $stats['message'] ?? ''
        ), ($stats['failed'] ?? 0) > 0 ? 'red' : 'green');
    }
}
