<?php

namespace App\Commands;

use App\Services\Copilot\TelegramService;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class CopilotTelegramPoll extends BaseCommand
{
    protected $group       = 'Copilot';
    protected $name        = 'copilot:telegram-poll';
    protected $description = 'Jalankan long-polling Telegram Bot untuk development lokal tanpa HTTPS tunnel.';
    protected $usage       = 'copilot:telegram-poll [--once] [--timeout=20]';
    protected $options     = [
        '--once'    => 'Ambil satu batch update lalu selesai',
        '--timeout' => 'Timeout long-polling Telegram dalam detik (default 20, atau 0 saat --once tanpa --timeout)',
    ];

    public function run(array $params)
    {
        $telegram = new TelegramService();
        if ($telegram->getBotToken() === '') {
            CLI::error('TELEGRAM_BOT_TOKEN belum dikonfigurasi di .env.');
            return;
        }

        $onceOpt = CLI::getOption('once');
        $once = $onceOpt !== null && ! in_array(strtolower((string) $onceOpt), ['0', 'false', 'no'], true);

        $timeoutOpt = CLI::getOption('timeout');
        $timeout = $timeoutOpt !== null ? (int) $timeoutOpt : ($once ? 0 : 20);
        $timeout = max(0, min(50, $timeout));

        $offset = 0;
        CLI::write(
            $once
                ? "Menjalankan Telegram getUpdates sekali jalan (timeout={$timeout}s)..."
                : "Memulai long-polling Telegram (timeout={$timeout}s). Tekan Ctrl+C untuk berhenti.",
            'yellow'
        );

        do {
            try {
                $updates = $telegram->getUpdates($offset, $timeout);
                foreach ($updates as $update) {
                    if (! is_array($update)) {
                        continue;
                    }

                    $updateId = isset($update['update_id']) && is_numeric($update['update_id'])
                        ? (int) $update['update_id']
                        : 0;

                    if ($updateId >= $offset) {
                        $offset = $updateId + 1;
                    }

                    $result = $telegram->handleWebhookUpdate($update);
                    CLI::write(
                        sprintf('[update #%d] status=%s', $updateId, (string) ($result['status'] ?? 'ok')),
                        'cyan'
                    );
                }

                if ($once && $updates === []) {
                    CLI::write('Tidak ada update baru.', 'green');
                }
            } catch (\Throwable $e) {
                CLI::error('Polling error: ' . $e->getMessage());
                if ($once) {
                    return;
                }
                sleep(3);
            }
        } while (! $once);
    }
}
