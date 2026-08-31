<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Services\EmailDigestService;

class SendEmailDigest extends BaseCommand
{
    protected $group       = 'Notifikasi';
    protected $name        = 'notif:send-digest';
    protected $description = 'Kirim email digest untuk notifikasi yang tertunda';

    public function run(array $params)
    {
        CLI::write('Mulai memproses antrian email digest...', 'yellow');
        
        $service = new EmailDigestService();
        $stats = $service->processQueue();
        
        CLI::write(
            sprintf(
                'Selesai. Email sent: %d, failed: %d. Queue sent: %d, failed: %d. Calendar created: %d, skipped: %d, failed: %d.',
                $stats['emails_sent'] ?? 0,
                $stats['emails_failed'] ?? 0,
                $stats['queues_sent'] ?? 0,
                $stats['queues_failed'] ?? 0,
                $stats['calendar_created'] ?? 0,
                $stats['calendar_skipped'] ?? 0,
                $stats['calendar_failed'] ?? 0
            ),
            ($stats['emails_failed'] ?? 0) > 0 || ($stats['queues_failed'] ?? 0) > 0 || ($stats['calendar_failed'] ?? 0) > 0 ? 'red' : 'green'
        );
    }
}
