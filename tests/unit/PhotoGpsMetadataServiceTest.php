<?php

use App\Services\PhotoGpsMetadataService;
use CodeIgniter\Test\CIUnitTestCase;

final class PhotoGpsMetadataServiceTest extends CIUnitTestCase
{
    private array $paths = [];

    protected function tearDown(): void
    {
        foreach ($this->paths as $path) {
            if (is_file($path)) {
                unlink($path);
            }
        }

        parent::tearDown();
    }

    public function testAddsSouthWestGpsExifToJpegWithoutChangingSource(): void
    {
        $source = $this->createImage('jpg');
        $sourceHash = sha1_file($source);
        $result = (new PhotoGpsMetadataService())->prepareDownload($this->file($source, 'image/jpeg', -6.208763, -106.845599));

        $this->assertNotNull($result);
        $this->paths[] = $result['absolute_path'];
        $this->assertSame($sourceHash, sha1_file($source));
        $this->assertSame('image/jpeg', $result['mime_type']);
        $this->assertStringEndsWith('.jpg', $result['file_name']);

        $gps = exif_read_data($result['absolute_path'], 'GPS', true, false)['GPS'];
        $this->assertSame('S', $gps['GPSLatitudeRef']);
        $this->assertSame('W', $gps['GPSLongitudeRef']);
        $this->assertEqualsWithDelta(6.208763, $this->gpsDecimal($gps['GPSLatitude']), 0.000001);
        $this->assertEqualsWithDelta(106.845599, $this->gpsDecimal($gps['GPSLongitude']), 0.000001);
    }

    public function testAddsNorthEastGpsExifForZeroCoordinate(): void
    {
        $source = $this->createImage('jpg');
        $result = (new PhotoGpsMetadataService())->prepareDownload($this->file($source, 'image/jpeg', 0, 0));

        $this->assertNotNull($result);
        $this->paths[] = $result['absolute_path'];
        $gps = exif_read_data($result['absolute_path'], 'GPS', true, false)['GPS'];
        $this->assertSame('N', $gps['GPSLatitudeRef']);
        $this->assertSame('E', $gps['GPSLongitudeRef']);
        $this->assertSame(0.0, $this->gpsDecimal($gps['GPSLatitude']));
        $this->assertSame(0.0, $this->gpsDecimal($gps['GPSLongitude']));
    }

    public function testConvertsPngToJpegWithGpsExif(): void
    {
        $source = $this->createImage('png');
        $result = (new PhotoGpsMetadataService())->prepareDownload($this->file($source, 'image/png', 6.175392, 106.827153));

        $this->assertNotNull($result);
        $this->paths[] = $result['absolute_path'];
        $this->assertSame('image/jpeg', $result['mime_type']);
        $this->assertSame('image/jpeg', mime_content_type($result['absolute_path']));
        $this->assertArrayHasKey('GPSLatitude', exif_read_data($result['absolute_path'], 'GPS', true, false)['GPS']);
    }

    public function testConvertsWebpToJpegWithGpsExif(): void
    {
        if (!function_exists('imagewebp')) {
            $this->markTestSkipped('GD WebP tidak tersedia pada environment test.');
        }

        $source = $this->createImage('webp');
        $result = (new PhotoGpsMetadataService())->prepareDownload($this->file($source, 'image/webp', 6.175392, 106.827153));

        $this->assertNotNull($result);
        $this->paths[] = $result['absolute_path'];
        $this->assertSame('image/jpeg', $result['mime_type']);
        $this->assertSame('image/jpeg', mime_content_type($result['absolute_path']));
        $this->assertArrayHasKey('GPSLongitude', exif_read_data($result['absolute_path'], 'GPS', true, false)['GPS']);
    }

    public function testLeavesPhotoWithoutCoordinateUntouched(): void
    {
        $source = $this->createImage('jpg');

        $result = (new PhotoGpsMetadataService())->prepareDownload($this->file($source, 'image/jpeg', null, null));

        $this->assertNull($result);
    }

    private function file(string $path, string $mimeType, ?float $latitude, ?float $longitude): array
    {
        return [
            'absolute_path' => $path,
            'mime_type' => $mimeType,
            'file_name' => 'foto.' . pathinfo($path, PATHINFO_EXTENSION),
            'record' => (object) ['foto_lat' => $latitude, 'foto_lng' => $longitude],
        ];
    }

    private function createImage(string $format): string
    {
        $path = tempnam(sys_get_temp_dir(), 'sigapp-gps-test-');
        $this->paths[] = $path;
        $image = imagecreatetruecolor(4, 4);
        imagefill($image, 0, 0, imagecolorallocate($image, 32, 87, 163));

        try {
            $written = match ($format) {
                'png' => imagepng($image, $path),
                'webp' => imagewebp($image, $path, 90),
                default => imagejpeg($image, $path, 90),
            };
            $this->assertTrue($written);
        } finally {
            imagedestroy($image);
        }

        return $path;
    }

    private function gpsDecimal(string|array $value): float
    {
        if (is_array($value)) {
            $value = implode(',', $value);
        }

        $parts = array_map(static function (string $part): float {
            [$numerator, $denominator] = array_map('floatval', explode('/', trim($part)));

            return $numerator / $denominator;
        }, explode(',', $value));

        return $parts[0] + ($parts[1] / 60) + ($parts[2] / 3600);
    }
}
