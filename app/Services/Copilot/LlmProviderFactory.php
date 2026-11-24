<?php

namespace App\Services\Copilot;

use App\Repositories\Copilot\SettingRepository;
use Config\Copilot;

class LlmProviderFactory
{
    private Copilot $config;
    private SettingRepository $settingRepository;

    /**
     * @var ?callable(string): ?string
     */
    private $envResolver;

    /**
     * @var ?callable(string, string, array, array, int): array
     */
    private $httpTransport;

    /**
     * @param ?callable(string): ?string $envResolver
     * @param ?callable(string, string, array, array, int): array $httpTransport
     */
    public function __construct(
        ?Copilot $config = null,
        ?SettingRepository $settingRepository = null,
        ?callable $envResolver = null,
        ?callable $httpTransport = null,
    ) {
        $this->config = $config ?? config('Copilot');
        $this->settingRepository = $settingRepository ?? new SettingRepository();
        $this->envResolver = $envResolver;
        $this->httpTransport = $httpTransport;
    }

    /**
     * Resolve active LLM provider from Config\Copilot + DB settings + .env API key.
     *
     * @param ?array{provider?: ?string, model?: ?string, base_url?: ?string} $overrides
     *
     * @throws LlmProviderException
     */
    public function make(?array $overrides = null): LlmProviderInterface
    {
        $dbSettings = $this->settingRepository->getAll();

        $dbProvider = strtolower(trim((string) ($dbSettings['provider'] ?? '')));
        $overrideProvider = strtolower(trim((string) ($overrides['provider'] ?? '')));

        $providerName = $overrideProvider !== ''
            ? $overrideProvider
            : ($dbProvider !== '' ? $dbProvider : strtolower(trim($this->config->defaultProvider)));

        $providerConfig = $this->config->providers[$providerName] ?? null;
        if (! is_array($providerConfig)) {
            throw new LlmProviderException("Provider LLM '{$providerName}' tidak dikenali.", 400);
        }

        $switchingAwayFromDbProvider = $overrideProvider !== '' && $dbProvider !== '' && $overrideProvider !== $dbProvider;

        $overrideModel = trim((string) ($overrides['model'] ?? ''));
        $dbModel = $switchingAwayFromDbProvider ? '' : trim((string) ($dbSettings['model'] ?? ''));
        $model = $overrideModel !== ''
            ? $overrideModel
            : ($dbModel !== '' ? $dbModel : (string) ($providerConfig['defaultModel'] ?? ''));

        if ($model === '') {
            throw new LlmProviderException("Model untuk provider '{$providerName}' belum dikonfigurasi.", 400);
        }

        $overrideBaseUrl = trim((string) ($overrides['base_url'] ?? ''));
        $dbBaseUrl = $switchingAwayFromDbProvider ? '' : trim((string) ($dbSettings['base_url'] ?? ''));
        $baseUrl = $overrideBaseUrl !== ''
            ? $overrideBaseUrl
            : ($dbBaseUrl !== '' ? $dbBaseUrl : (string) ($providerConfig['baseUrl'] ?? ''));
        $baseUrl = rtrim($baseUrl, '/');

        if ($baseUrl === '') {
            throw new LlmProviderException("Base URL untuk provider '{$providerName}' belum dikonfigurasi.", 400);
        }

        $apiKeyEnv = (string) ($providerConfig['apiKeyEnv'] ?? '');
        $apiKey = $apiKeyEnv !== '' ? trim($this->resolveEnv($apiKeyEnv)) : '';
        if ($apiKey === '') {
            throw new LlmProviderException(
                "API key belum dikonfigurasi. Set {$apiKeyEnv} di file .env untuk provider '{$providerName}'.",
                400
            );
        }

        $timeout = min(20, max(1, (int) ($providerConfig['timeout'] ?? 20)));
        $driver = (string) ($providerConfig['driver'] ?? '');

        return match ($driver) {
            'gemini' => new GeminiProvider(
                apiKey: $apiKey,
                model: $model,
                baseUrl: $baseUrl,
                timeout: $timeout,
                httpTransport: $this->httpTransport,
            ),
            'openai_compatible' => new OpenAiCompatibleProvider(
                apiKey: $apiKey,
                model: $model,
                baseUrl: $baseUrl,
                timeout: $timeout,
                httpTransport: $this->httpTransport,
                providerName: $providerName,
            ),
            default => throw new LlmProviderException("Driver LLM '{$driver}' tidak didukung.", 400),
        };
    }

    private function resolveEnv(string $key): string
    {
        if ($this->envResolver !== null) {
            return (string) ($this->envResolver)($key);
        }

        return (string) env($key, '');
    }
}
