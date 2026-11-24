<?php

namespace App\Services\Copilot;

use App\Services\Copilot\Tools\BookingPerBulanTool;
use App\Services\Copilot\Tools\CopilotToolInterface;
use App\Services\Copilot\Tools\JatuhTempoCicilanTool;
use App\Services\Copilot\Tools\KonversiBookingAkadTool;

class ToolRegistry
{
    private CopilotAuthorizer $authorizer;
    private PiiGuard $piiGuard;

    /**
     * @var array<string, CopilotToolInterface>
     */
    private array $tools = [];

    /**
     * @param CopilotToolInterface[]|null $tools
     */
    public function __construct(
        ?CopilotAuthorizer $authorizer = null,
        ?PiiGuard $piiGuard = null,
        ?array $tools = null,
    ) {
        $this->authorizer = $authorizer ?? new CopilotAuthorizer();
        $this->piiGuard = $piiGuard ?? new PiiGuard();

        $toolList = $tools ?? [
            new JatuhTempoCicilanTool($this->authorizer, null, $this->piiGuard),
            new BookingPerBulanTool($this->authorizer, null, $this->piiGuard),
            new KonversiBookingAkadTool($this->authorizer, null, $this->piiGuard),
        ];

        foreach ($toolList as $tool) {
            $this->register($tool);
        }
    }

    public function register(CopilotToolInterface $tool): void
    {
        $this->tools[$tool->getName()] = $tool;
    }

    /**
     * @return string[]
     */
    public function getAllowedToolNamesForUser(int $userId): array
    {
        $allowed = [];
        foreach ($this->tools as $name => $tool) {
            if ($this->authorizer->canUseTool($userId, $name)) {
                $allowed[] = $name;
            }
        }

        return $allowed;
    }

    /**
     * Mengembalikan daftar deklarasi tool yang diizinkan untuk user tersebut.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getDeclarationsForUser(int $userId): array
    {
        $declarations = [];
        foreach ($this->tools as $name => $tool) {
            if ($this->authorizer->canUseTool($userId, $name)) {
                $declarations[] = $tool->getDeclaration();
            }
        }

        return $declarations;
    }

    /**
     * Mengeksekusi tool setelah memverifikasi RBAC per tool dan menyaring output lewat PiiGuard.
     *
     * @param array<string, mixed> $arguments
     * @return array<string, mixed>
     */
    public function execute(int $userId, string $toolName, array $arguments = []): array
    {
        if (! isset($this->tools[$toolName])) {
            return $this->piiGuard->assertSafePayload([
                'status'  => 'unknown_tool',
                'tool'    => $toolName,
                'message' => "Tool '{$toolName}' tidak terdaftar.",
            ]);
        }

        if (! $this->authorizer->canUseTool($userId, $toolName)) {
            return $this->piiGuard->assertSafePayload([
                'status'  => 'forbidden',
                'tool'    => $toolName,
                'message' => 'Anda tidak memiliki hak akses untuk menggunakan tool ini.',
            ]);
        }

        $result = $this->tools[$toolName]->execute($userId, $arguments);

        return $this->piiGuard->assertSafePayload($result);
    }
}
