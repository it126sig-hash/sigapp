<?php

namespace App\Services;

class UserAgentDeviceParser
{
    public function parse(?string $userAgent): array
    {
        $userAgent = trim((string) $userAgent);
        $browser = $this->browser($userAgent);
        $platform = $this->platform($userAgent);

        return [
            'browser' => $browser,
            'platform' => $platform,
            'device_label' => $browser !== null && $platform !== null
                ? "{$browser} di {$platform}"
                : 'Perangkat tidak dikenal',
        ];
    }

    private function browser(string $userAgent): ?string
    {
        if (preg_match('/\bEdg(?:e|A|iOS)?\//i', $userAgent)) {
            return 'Edge';
        }
        if (preg_match('/\bOPR\//i', $userAgent)) {
            return 'Opera';
        }
        if (preg_match('/\b(?:Chrome|CriOS)\//i', $userAgent)) {
            return 'Chrome';
        }
        if (preg_match('/\b(?:Firefox|FxiOS)\//i', $userAgent)) {
            return 'Firefox';
        }
        if (preg_match('/\bVersion\/[^ ]+.*\bSafari\//i', $userAgent)) {
            return 'Safari';
        }

        return null;
    }

    private function platform(string $userAgent): ?string
    {
        if (stripos($userAgent, 'Android') !== false) {
            return 'Android';
        }
        if (preg_match('/\b(?:iPhone|iPad|iPod)\b/i', $userAgent)) {
            return 'iOS';
        }
        if (stripos($userAgent, 'Windows') !== false) {
            return 'Windows';
        }
        if (stripos($userAgent, 'Macintosh') !== false || stripos($userAgent, 'Mac OS X') !== false) {
            return 'macOS';
        }
        if (stripos($userAgent, 'Linux') !== false) {
            return 'Linux';
        }

        return null;
    }
}
