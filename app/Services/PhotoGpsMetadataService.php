<?php

namespace App\Services;

use lsolesen\pel\PelEntryAscii;
use lsolesen\pel\PelEntryByte;
use lsolesen\pel\PelEntryRational;
use lsolesen\pel\PelExif;
use lsolesen\pel\PelIfd;
use lsolesen\pel\PelJpeg;
use lsolesen\pel\PelTag;
use lsolesen\pel\PelTiff;
use RuntimeException;

class PhotoGpsMetadataService
{
    private const JPEG_MIME_TYPES = ['image/jpeg', 'image/jpg'];
    private const CONVERTIBLE_MIME_TYPES = ['image/png', 'image/webp'];

    /**
     * Creates a temporary JPEG download with GPS EXIF data.
     * Returns null when this file has no valid stored coordinate or is not an image.
     */
    public function prepareDownload(array $file): ?array
    {
        $coordinates = $this->coordinates($file['record'] ?? null);
        if ($coordinates === null) {
            return null;
        }

        $sourcePath = (string) ($file['absolute_path'] ?? '');
        $mimeType = strtolower((string) ($file['mime_type'] ?? ''));
        if (!is_file($sourcePath) || !is_readable($sourcePath)) {
            throw new RuntimeException('File sumber foto tidak dapat dibaca.');
        }

        if (!in_array($mimeType, array_merge(self::JPEG_MIME_TYPES, self::CONVERTIBLE_MIME_TYPES), true)) {
            return null;
        }

        $temporaryPath = tempnam(sys_get_temp_dir(), 'sigapp-gps-');
        if ($temporaryPath === false) {
            throw new RuntimeException('Gagal menyiapkan file unduhan sementara.');
        }

        try {
            if (in_array($mimeType, self::JPEG_MIME_TYPES, true)) {
                if (!copy($sourcePath, $temporaryPath)) {
                    throw new RuntimeException('Gagal menyalin foto untuk unduhan.');
                }
            } else {
                $this->convertToJpeg($sourcePath, $temporaryPath);
            }

            $this->embedGps($temporaryPath, $coordinates['latitude'], $coordinates['longitude']);

            $prepared = $file;
            $prepared['absolute_path'] = $temporaryPath;
            $prepared['mime_type'] = 'image/jpeg';
            $prepared['file_name'] = $this->jpegFileName((string) ($file['file_name'] ?? 'foto'));
            $prepared['temporary_path'] = $temporaryPath;

            return $prepared;
        } catch (\Throwable $e) {
            @unlink($temporaryPath);
            throw $e;
        }
    }

    public function cleanup(?string $temporaryPath): void
    {
        if ($temporaryPath && is_file($temporaryPath)) {
            @unlink($temporaryPath);
        }
    }

    private function coordinates(mixed $record): ?array
    {
        if (!is_object($record) || !isset($record->foto_lat, $record->foto_lng)
            || !is_numeric($record->foto_lat) || !is_numeric($record->foto_lng)) {
            return null;
        }

        $latitude = (float) $record->foto_lat;
        $longitude = (float) $record->foto_lng;
        if (!is_finite($latitude) || !is_finite($longitude)
            || $latitude < -90 || $latitude > 90 || $longitude < -180 || $longitude > 180) {
            return null;
        }

        return compact('latitude', 'longitude');
    }

    private function convertToJpeg(string $sourcePath, string $destinationPath): void
    {
        $source = @imagecreatefromstring((string) file_get_contents($sourcePath));
        if ($source === false) {
            throw new RuntimeException('Format foto tidak dapat dikonversi menjadi JPEG.');
        }

        try {
            $width = imagesx($source);
            $height = imagesy($source);
            $canvas = imagecreatetruecolor($width, $height);
            if ($canvas === false) {
                throw new RuntimeException('Gagal menyiapkan kanvas JPEG.');
            }

            try {
                $white = imagecolorallocate($canvas, 255, 255, 255);
                imagefill($canvas, 0, 0, $white);
                imagealphablending($canvas, true);
                imagecopy($canvas, $source, 0, 0, 0, 0, $width, $height);

                if (!imagejpeg($canvas, $destinationPath, 90)) {
                    throw new RuntimeException('Gagal mengonversi foto menjadi JPEG.');
                }
            } finally {
                imagedestroy($canvas);
            }
        } finally {
            imagedestroy($source);
        }
    }

    private function embedGps(string $jpegPath, float $latitude, float $longitude): void
    {
        $jpeg = new PelJpeg($jpegPath);
        $exif = $jpeg->getExif() ?? new PelExif();
        $tiff = $exif->getTiff() ?? new PelTiff();
        $ifd0 = $tiff->getIfd() ?? new PelIfd(PelIfd::IFD0);
        $gps = $ifd0->getSubIfd(PelIfd::GPS) ?? new PelIfd(PelIfd::GPS);

        $gps->addEntry(new PelEntryByte(PelTag::GPS_VERSION_ID, 2, 3, 0, 0));
        $gps->addEntry(new PelEntryAscii(PelTag::GPS_LATITUDE_REF, $latitude < 0 ? 'S' : 'N'));
        $gps->addEntry(new PelEntryRational(PelTag::GPS_LATITUDE, ...$this->degreesMinutesSeconds($latitude)));
        $gps->addEntry(new PelEntryAscii(PelTag::GPS_LONGITUDE_REF, $longitude < 0 ? 'W' : 'E'));
        $gps->addEntry(new PelEntryRational(PelTag::GPS_LONGITUDE, ...$this->degreesMinutesSeconds($longitude)));
        $gps->addEntry(new PelEntryAscii(PelTag::GPS_MAP_DATUM, 'WGS-84'));

        $ifd0->addSubIfd($gps);
        $tiff->setIfd($ifd0);
        $exif->setTiff($tiff);
        $jpeg->setExif($exif);

        if ($jpeg->saveFile($jpegPath) === false) {
            throw new RuntimeException('Gagal menulis metadata GPS pada foto.');
        }
    }

    private function degreesMinutesSeconds(float $coordinate): array
    {
        $absolute = abs($coordinate);
        $degrees = (int) floor($absolute);
        $minutesFloat = ($absolute - $degrees) * 60;
        $minutes = (int) floor($minutesFloat);
        $seconds = (int) round(($minutesFloat - $minutes) * 60 * 1000000);

        if ($seconds >= 60000000) {
            $seconds = 0;
            $minutes++;
        }
        if ($minutes >= 60) {
            $minutes = 0;
            $degrees++;
        }

        return [[$degrees, 1], [$minutes, 1], [$seconds, 1000000]];
    }

    private function jpegFileName(string $fileName): string
    {
        $name = pathinfo($fileName, PATHINFO_FILENAME);

        return ($name !== '' ? $name : 'foto') . '.jpg';
    }
}
