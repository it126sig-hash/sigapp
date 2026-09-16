<?php

namespace App\Support;

final class NotificationTextFormatter
{
    public static function plain($value, string $fallback = '-'): string
    {
        $decoded = html_entity_decode((string) $value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $decoded = preg_replace('/<\s*(br|\/p|\/div|\/li)\s*\/?>/i', ' ', $decoded) ?? $decoded;
        $text = trim(strip_tags($decoded));
        $text = preg_replace('/\s+/u', ' ', $text) ?? $text;

        return $text === '' ? $fallback : $text;
    }

    public static function withKavling($value, $namaJalan = null, $noKavling = null, string $fallback = '-'): string
    {
        $message = self::plain($value, $fallback);
        $jalan = self::plain($namaJalan ?? '', '');
        $kavling = self::plain($noKavling ?? '', '');

        $location = trim($jalan . ($kavling !== '' ? ' No. ' . $kavling : ''));
        if ($location === '') {
            return $message;
        }

        if (self::normalizedContains($message, $location)) {
            return $message;
        }

        return $location . ' - ' . $message;
    }

    private static function normalizedContains(string $haystack, string $needle): bool
    {
        $normalize = static fn (string $text): string => trim(preg_replace('/[^a-z0-9]+/i', ' ', strtolower($text)) ?? '');
        $haystack = $normalize($haystack);
        $needle = $normalize($needle);

        return $needle !== '' && str_contains($haystack, $needle);
    }
}
