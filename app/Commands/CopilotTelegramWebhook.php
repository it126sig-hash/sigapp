<?php

namespace App\Commands;

use App\Services\Copilot\TelegramService;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class CopilotTelegramWebhook extends BaseCommand
{
    protected $group       = 'Copilot';
    protected $name        = 'copilot:telegram-webhook';
    protected $description = 'Daftarkan atau hapus webhook Bot Telegram AI Copilot.';
    protected $usage       = 'copilot:telegram-webhook --url=https://domain.anda/api/public/copilot/telegram/webhook [--drop]';
    protected $options     = [
        '--url'  => 'URL HTTPS publik endpoint webhook Telegram (/api/public/copilot/telegram/webhook)',
        '--drop' => 'Hapus webhook yang terdaftar (opsional: hapus pending updates)',
    ];

    public function run(array $params)
    {
        $telegram = new TelegramService();
        if ($telegram->getBotToken() === '') {
            CLI::error('TELEGRAM_BOT_TOKEN belum dikonfigurasi di .env.');
            return;
        }

        $dropOpt = CLI::getOption('drop');
        $shouldDrop = $dropOpt !== null && ! in_array(strtolower((string) $dropOpt), ['0', 'false', 'no'], true);

        if ($shouldDrop) {
            try {
                CLI::write('Menghapus webhook Telegram...', 'yellow');
                $res = $telegram->deleteWebhook(true);
                CLI::write('Webhook Telegram berhasil dihapus: ' . ($res['description'] ?? 'OK'), 'green');
            } catch (\Throwable $e) {
                CLI::error($e->getMessage());
            }

            return;
        }

        $url = trim((string) (CLI::getOption('url') ?? ''));
        if ($url === '' || filter_var($url, FILTER_VALIDATE_URL) === false) {
            CLI::error('Opsi --url wajib diisi dengan URL HTTPS yang valid. Contoh: php spark copilot:telegram-webhook --url=https://sigapp.example.com/api/public/copilot/telegram/webhook');
            return;
        }

        if (strtolower((string) parse_url($url, PHP_URL_SCHEME)) !== 'https') {
            CLI::write('Peringatan: Telegram Bot API mensyaratkan URL HTTPS untuk webhook.', 'yellow');
        }

        $secret = $telegram->getWebhookSecret();
        if ($secret === '') {
            CLI::error('TELEGRAM_WEBHOOK_SECRET belum dikonfigurasi di .env.');
            return;
        }

        try {
            CLI::write("Mendaftarkan webhook Telegram ke {$url}...", 'yellow');
            $res = $telegram->setWebhook($url, $secret);
            CLI::write('Webhook Telegram berhasil didaftarkan: ' . ($res['description'] ?? 'OK'), 'green');
        } catch (\Throwable $e) {
            CLI::error($e->getMessage());
        }
    }
}
