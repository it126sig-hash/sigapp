<?php

namespace App\Services\Copilot;

class LlmResponse
{
    /**
     * @param array<int, array{id: string, name: string, arguments: array<string, mixed>}> $toolCalls
     * @param array<string, mixed> $rawMessage
     * @param array<string, mixed> $usage
     */
    public function __construct(
        public readonly string $text,
        public readonly array $toolCalls = [],
        public readonly array $rawMessage = [],
        public readonly string $finishReason = 'stop',
        public readonly array $usage = [],
    ) {
    }

    public function hasToolCalls(): bool
    {
        return $this->toolCalls !== [];
    }
}
