<?php

namespace App\Services;

use App\Libraries\SimpleQrCode;
use CodeIgniter\Database\BaseConnection;
use RuntimeException;

class ReferralQrService
{
    private const DEFAULT_LANDING_PAGE_URL = 'https://sigapp.site';

    private BaseConnection $db;

    public function __construct()
    {
        $this->db = db_connect();
    }

    public function generate(?string $kodeReferal, ?int $idMkdt, ?int $idProyek): array
    {
        $kodeReferal = trim((string) $kodeReferal);
        $idMkdt = (int) ($idMkdt ?? 0);
        $idProyek = (int) ($idProyek ?? 0);

        if ($kodeReferal === '' && $idMkdt <= 0) {
            throw new RuntimeException('Kode referal atau ID MKDT wajib diisi.');
        }

        if ($idProyek <= 0) {
            throw new RuntimeException('Proyek aktif tidak ditemukan.');
        }

        $data = $this->findReferralData($kodeReferal, $idMkdt, $idProyek);
        if (! $data) {
            throw new RuntimeException('Data referal tidak ditemukan pada proyek aktif.');
        }

        $kodeReferal = trim((string) $data->kode_referal);
        if ($kodeReferal === '') {
            throw new RuntimeException('Konsumen ini belum memiliki kode referal.');
        }

        $landingPageUrl = trim((string) ($data->landing_page_url ?: self::DEFAULT_LANDING_PAGE_URL));
        if ($landingPageUrl === '') {
            $landingPageUrl = self::DEFAULT_LANDING_PAGE_URL;
        }

        $targetUrl = $this->buildTargetUrl($landingPageUrl, $kodeReferal);
        $png = $this->renderBrandedPng($targetUrl, [
            'kode_referal' => $kodeReferal,
            'nama_konsumen' => (string) ($data->nama_konsumen ?: '-'),
            'nama_proyek' => (string) ($data->nama_proyek ?: 'SIGAPP'),
            'logo_path' => (string) ($data->logo ?: ''),
        ]);

        return [
            'kode_referal' => $kodeReferal,
            'nama_konsumen' => (string) ($data->nama_konsumen ?: '-'),
            'nama_proyek' => (string) ($data->nama_proyek ?: 'SIGAPP'),
            'target_url' => $targetUrl,
            'filename' => 'qr-referral-' . $this->safeFilename($kodeReferal) . '.png',
            'png' => $png,
        ];
    }

    private function findReferralData(string $kodeReferal, int $idMkdt, int $idProyek): ?object
    {
        $builder = $this->db->table('mkdt')
            ->select('
                mkdt.id_mkdt,
                konsumen.id_konsumen,
                konsumen.kode_referal,
                konsumen.nama_konsumen,
                proyek.id_proyek,
                proyek.nama_proyek,
                proyek.logo,
                proyek.landing_page_url
            ')
            ->join('konsumen', 'konsumen.id_konsumen = mkdt.id_konsumen', 'left')
            ->join('kavling', 'kavling.id_mkdt = mkdt.id_mkdt OR kavling.id_kavling = mkdt.id_kavling', 'left')
            ->join('jalan', 'jalan.id_jalan = kavling.id_jalan', 'left')
            ->join('cluster', 'cluster.id_cluster = jalan.id_cluster', 'left')
            ->join('proyek', 'proyek.id_proyek = cluster.id_proyek', 'left')
            ->where('proyek.id_proyek', $idProyek);

        if ($idMkdt > 0) {
            $builder->where('mkdt.id_mkdt', $idMkdt);
        }

        if ($kodeReferal !== '') {
            $builder->where('konsumen.kode_referal', $kodeReferal);
        }

        return $builder->get()->getRow();
    }

    private function buildTargetUrl(string $landingPageUrl, string $kodeReferal): string
    {
        $separator = str_contains($landingPageUrl, '?') ? '&' : '?';

        return $landingPageUrl . $separator . http_build_query(['kode' => $kodeReferal]);
    }

    private function renderBrandedPng(string $targetUrl, array $data): string
    {
        $canvasWidth = 900;
        $canvasHeight = 1100;
        $image = imagecreatetruecolor($canvasWidth, $canvasHeight);
        $white = imagecolorallocate($image, 255, 255, 255);
        $text = imagecolorallocate($image, 17, 24, 39);
        $muted = imagecolorallocate($image, 75, 85, 99);
        $primary = imagecolorallocate($image, 32, 87, 163);
        $line = imagecolorallocate($image, 229, 231, 235);

        imagefill($image, 0, 0, $white);
        imagerectangle($image, 28, 28, $canvasWidth - 29, $canvasHeight - 29, $line);

        $font = $this->fontPath(false);
        $boldFont = $this->fontPath(true);
        $logoPath = $this->resolveLogoPath((string) ($data['logo_path'] ?? ''));

        if ($logoPath) {
            $this->drawLogo($image, $logoPath, (int) (($canvasWidth - 118) / 2), 48, 118, 118);
        }

        $this->drawCenteredText($image, (string) $data['nama_proyek'], $canvasWidth / 2, 198, 26, $primary, $boldFont);
        $this->drawCenteredText($image, 'Kode Referal Konsumen', $canvasWidth / 2, 232, 14, $muted, $font);

        $qrPng = SimpleQrCode::encodeText($targetUrl)->toPng(10, 4);
        $qrImage = imagecreatefromstring($qrPng);
        if (! $qrImage) {
            imagedestroy($image);
            throw new RuntimeException('Gagal membuat QR code.');
        }

        imagecopyresampled($image, $qrImage, 140, 275, 0, 0, 620, 620, imagesx($qrImage), imagesy($qrImage));
        imagedestroy($qrImage);

        $this->drawCenteredText($image, strtoupper((string) $data['kode_referal']), $canvasWidth / 2, 945, 32, $text, $boldFont);
        $this->drawCenteredText($image, (string) $data['nama_konsumen'], $canvasWidth / 2, 988, 20, $muted, $font);
        $this->drawCenteredText($image, $targetUrl, $canvasWidth / 2, 1034, 13, $muted, $font, 760);

        ob_start();
        imagepng($image);
        $png = ob_get_clean();
        imagedestroy($image);

        if ($png === false || $png === '') {
            throw new RuntimeException('Gagal membuat gambar QR.');
        }

        return $png;
    }

    private function drawLogo($canvas, string $logoPath, int $x, int $y, int $maxWidth, int $maxHeight): void
    {
        $logoContent = @file_get_contents($logoPath);
        $logo = $logoContent ? @imagecreatefromstring($logoContent) : false;
        if (! $logo) {
            return;
        }

        $sourceWidth = imagesx($logo);
        $sourceHeight = imagesy($logo);
        $ratio = min($maxWidth / $sourceWidth, $maxHeight / $sourceHeight, 1);
        $targetWidth = max(1, (int) round($sourceWidth * $ratio));
        $targetHeight = max(1, (int) round($sourceHeight * $ratio));
        $targetX = $x + (int) (($maxWidth - $targetWidth) / 2);
        $targetY = $y + (int) (($maxHeight - $targetHeight) / 2);

        imagecopyresampled($canvas, $logo, $targetX, $targetY, 0, 0, $targetWidth, $targetHeight, $sourceWidth, $sourceHeight);
        imagedestroy($logo);
    }

    private function drawCenteredText($image, string $text, float $centerX, int $baselineY, int $fontSize, int $color, ?string $fontPath, int $maxWidth = 760): void
    {
        $text = trim(preg_replace('/\s+/', ' ', $text) ?? '');
        if ($text === '') {
            $text = '-';
        }

        if ($fontPath && function_exists('imagettfbbox')) {
            while ($fontSize > 9) {
                $box = imagettfbbox($fontSize, 0, $fontPath, $text);
                $width = abs(($box[2] ?? 0) - ($box[0] ?? 0));
                if ($width <= $maxWidth) {
                    break;
                }
                $fontSize--;
            }

            $box = imagettfbbox($fontSize, 0, $fontPath, $text);
            $width = abs(($box[2] ?? 0) - ($box[0] ?? 0));
            imagettftext($image, $fontSize, 0, (int) ($centerX - $width / 2), $baselineY, $color, $fontPath, $text);
            return;
        }

        $font = 5;
        $width = imagefontwidth($font) * strlen($text);
        imagestring($image, $font, (int) ($centerX - $width / 2), $baselineY - 16, $text, $color);
    }

    private function resolveLogoPath(string $logicalPath): ?string
    {
        $logicalPath = trim(str_replace('\\', '/', $logicalPath));
        $logicalPath = ltrim($logicalPath, '/');

        if ($logicalPath === '' || str_contains($logicalPath, "\0") || str_contains($logicalPath, '..') || preg_match('#^[A-Za-z]:#', $logicalPath)) {
            return null;
        }

        $candidates = [
            rtrim(WRITEPATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'protected_uploads' . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $logicalPath),
            FCPATH . str_replace('/', DIRECTORY_SEPARATOR, $logicalPath),
            ROOTPATH . str_replace('/', DIRECTORY_SEPARATOR, $logicalPath),
        ];

        foreach ($candidates as $path) {
            if (is_file($path)) {
                return $path;
            }
        }

        return null;
    }

    private function fontPath(bool $bold): ?string
    {
        $candidates = $bold
            ? ['C:/Windows/Fonts/arialbd.ttf', 'C:/Windows/Fonts/segoeuib.ttf']
            : ['C:/Windows/Fonts/arial.ttf', 'C:/Windows/Fonts/segoeui.ttf'];

        foreach ($candidates as $path) {
            if (is_file($path)) {
                return $path;
            }
        }

        return null;
    }

    private function safeFilename(string $value): string
    {
        $value = preg_replace('/[^A-Za-z0-9_-]+/', '-', $value) ?? 'kode';
        $value = trim($value, '-_');

        return $value !== '' ? strtolower($value) : 'kode';
    }
}
