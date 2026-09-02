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
}
