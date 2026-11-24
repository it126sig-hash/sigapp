<?php

namespace App\Services\Copilot;

use RuntimeException;

class PiiGuard
{
    /**
     * Key names forbidden in Copilot tool payloads (exact lowercase match).
     *
     * @var string[]
     */
    private const FORBIDDEN_EXACT_KEYS = [
        'nik',
        'npwp',
        'ktp',
        'kk',
        'no_kk',
        'hp',
        'no_hp',
        'hp_konsumen',
        'hp_pasangan',
        'tel_instansi',
        'telepon',
        'no_telepon',
        'email',
        'alamat',
        'rekening',
        'no_rek',
        'atas_nama',
        'password',
        'hash',
        'pasangan',
        'pekerjaan',
        'instansi',
        'gaji',
        'void_reason',
        'keterangan',
    ];

    /**
     * Key segments forbidden in underscore/non-alnum split tokens (case-insensitive).
     *
     * @var string[]
     */
    private const FORBIDDEN_SEGMENTS = [
        'nik',
        'npwp',
        'ktp',
        'kk',
        'hp',
        'tel',
        'telp',
        'telepon',
        'email',
        'alamat',
        'rekening',
        'rek',
        'password',
        'hash',
        'pasangan',
        'pekerjaan',
        'instansi',
        'gaji',
        'keterangan',
    ];

    /**
     * Key prefixes forbidden (case-insensitive).
     *
     * @var string[]
     */
    private const FORBIDDEN_PREFIXES = [
        'file_',
    ];

    /**
     * Memvalidasi payload output tool secara rekursif.
     * Melempar RuntimeException jika terdapat key sensitif, dan menyensor nilai string berisi pola NIK/KK/NPWP.
     *
     * @param array<mixed> $payload
     * @return array<mixed>
     */
    public function assertSafePayload(array $payload): array
    {
        return $this->sanitizeArray($payload);
    }

    /**
     * Memeriksa apakah nama key termasuk field sensitif/PII yang dilarang.
     */
    public function isForbiddenKey(string $key): bool
    {
        $normalized = strtolower(trim($key));
        if ($normalized === '') {
            return false;
        }

        if (in_array($normalized, self::FORBIDDEN_EXACT_KEYS, true)) {
            return true;
        }

        foreach (self::FORBIDDEN_PREFIXES as $prefix) {
            if (str_starts_with($normalized, $prefix)) {
                return true;
            }
        }

        if (str_contains($normalized, 'atas_nama') || str_contains($normalized, 'void_reason')) {
            return true;
        }

        $segments = preg_split('/[^a-z0-9]+/', $normalized, -1, PREG_SPLIT_NO_EMPTY);
        if (! is_array($segments)) {
            return false;
        }

        foreach ($segments as $segment) {
            if (in_array($segment, self::FORBIDDEN_SEGMENTS, true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Menyensor pola angka 16 digit (NIK/KK) dan format NPWP dari teks bebas.
     *
     * @return array{text: string, redacted: bool}
     */
    public function redactText(string $text): array
    {
        $redacted = false;

        $result = preg_replace_callback(
            '/\b\d{2}\.\d{3}\.\d{3}\.\d-\d{3}\.\d{3}\b/',
            static function () use (&$redacted): string {
                $redacted = true;
                return '[REDACTED_NPWP]';
            },
            $text
        );

        $result = preg_replace_callback(
            '/\b\d{16}\b/',
            static function () use (&$redacted): string {
                $redacted = true;
                return '[REDACTED_ID]';
            },
            $result ?? $text
        );

        return [
            'text'     => $result ?? $text,
            'redacted' => $redacted,
        ];
    }

    /**
     * @param array<mixed> $data
     * @return array<mixed>
     */
    private function sanitizeArray(array $data): array
    {
        $sanitized = [];

        foreach ($data as $key => $value) {
            if (is_string($key) && $this->isForbiddenKey($key)) {
                throw new RuntimeException("PiiGuard memblokir field sensitif '{$key}' pada output Copilot.");
            }

            if (is_array($value)) {
                $sanitized[$key] = $this->sanitizeArray($value);
            } elseif (is_object($value)) {
                $sanitized[$key] = $this->sanitizeArray(get_object_vars($value));
            } elseif (is_string($value)) {
                $redacted = $this->redactText($value);
                $sanitized[$key] = $redacted['text'];
            } else {
                $sanitized[$key] = $value;
            }
        }

        return $sanitized;
    }
}
