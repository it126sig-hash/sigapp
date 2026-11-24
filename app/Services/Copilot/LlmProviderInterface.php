<?php

namespace App\Services\Copilot;

interface LlmProviderInterface
{
    /**
     * Send chat messages (and optional tool declarations) to the LLM provider.
     *
     * @param array<int, array<string, mixed>> $messages
     * @param array<int, array<string, mixed>> $tools
     * @param array<string, mixed> $options
     *
     * @throws LlmProviderException
     */
    public function chat(array $messages, array $tools = [], array $options = []): LlmResponse;

    public function getProviderName(): string;

    public function getModelName(): string;
}
