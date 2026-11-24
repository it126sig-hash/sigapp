<?php

namespace App\Services\Copilot\Tools;

interface CopilotToolInterface
{
    public function getName(): string;

    public function getDescription(): string;

    /**
     * Mengembalikan skema deklarasi function tool (format OpenAI/Gemini compatible).
     *
     * @return array<string, mixed>
     */
    public function getDeclaration(): array;

    /**
     * Menjalankan tool secara read-only untuk userId tertentu.
     *
     * @param array<string, mixed> $arguments
     * @return array<string, mixed>
     */
    public function execute(int $userId, array $arguments): array;
}
