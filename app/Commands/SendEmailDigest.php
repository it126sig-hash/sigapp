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
        $sent = $service->processQueue();
        
        CLI::write("Selesai. $sent email berhasil dikirim.", 'green');
    }
}
