<?php

namespace App\Commands;

use App\Exceptions\ServiceException;
use App\Services\Copilot\CopilotOrchestrator;
use App\Services\Copilot\LlmProviderException;
use App\Services\Copilot\LlmProviderFactory;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class CopilotAsk extends BaseCommand
{
    protected $group       = 'Copilot';
    protected $name        = 'copilot:ask';
    protected $description = 'Kirim pesan uji ke LLM provider AI Copilot yang aktif.';
    protected $usage       = 'copilot:ask "<pesan>" [--user <id>] [--provider gemini] [--model gemini-flash-latest]';
    protected $arguments   = [
        'prompt' => 'Pesan atau pertanyaan untuk dikirim ke LLM',
    ];
    protected $options     = [
        '--user'     => 'ID user SIGAPP untuk menjalankan mode CopilotOrchestrator (tool calling + RBAC + audit)',
        '--provider' => 'Override provider sementara (gemini, openai, groq, openrouter, custom)',
        '--model'    => 'Override model sementara',
    ];

    public function run(array $params)
    {
        $prompt = trim((string) ($params[0] ?? ''));
        if ($prompt === '') {
            CLI::error('Prompt wajib diisi. Contoh: php spark copilot:ask "halo"');
            return;
        }

        $overrides = [];
        $providerOpt = CLI::getOption('provider');
        if (is_string($providerOpt) && trim($providerOpt) !== '') {
            $overrides['provider'] = trim($providerOpt);
        }
        $modelOpt = CLI::getOption('model');
        if (is_string($modelOpt) && trim($modelOpt) !== '') {
            $overrides['model'] = trim($modelOpt);
        }

        $userOpt = CLI::getOption('user');
        if ($userOpt !== null && trim((string) $userOpt) !== '') {
            $userId = (int) $userOpt;
            if ($userId <= 0) {
                CLI::error('Opsi --user harus berupa ID user integer positif.');
                return;
            }

            try {
                $orchestrator = new CopilotOrchestrator();
                CLI::write(sprintf('Menjalankan CopilotOrchestrator untuk user #%d...', $userId), 'yellow');
                $result = $orchestrator->handleUserMessage($userId, $prompt, 'cli', null, $overrides);

                CLI::write(
                    sprintf('Provider: %s (%s)', $result['provider'], $result['model']),
                    'cyan'
                );
                if ($result['tools_used'] !== []) {
                    CLI::write('Tools digunakan: ' . implode(', ', $result['tools_used']), 'cyan');
                }
                CLI::write($result['reply'], 'green');
            } catch (ServiceException | LlmProviderException $e) {
                CLI::error($e->getMessage());
            } catch (\Throwable $e) {
                CLI::error('Gagal memproses CopilotOrchestrator: ' . $e->getMessage());
            }

            return;
        }

        try {
            $factory = new LlmProviderFactory();
            $provider = $factory->make($overrides !== [] ? $overrides : null);

            CLI::write(
                sprintf('Mengirim ke %s (%s)...', $provider->getProviderName(), $provider->getModelName()),
                'yellow'
            );

            $response = $provider->chat([
                ['role' => 'user', 'content' => $prompt],
            ]);

            if ($response->text !== '') {
                CLI::write($response->text, 'green');
            } else {
                CLI::write('[Respons teks kosong, finishReason: ' . $response->finishReason . ']', 'yellow');
            }
        } catch (LlmProviderException $e) {
            CLI::error($e->getMessage());
        } catch (\Throwable $e) {
            CLI::error('Gagal memanggil LLM: ' . $e->getMessage());
        }
    }
}
