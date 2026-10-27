<?php

use App\Controllers\FileController;
use App\Services\FileAccessService;
use App\Services\PhotoGpsMetadataService;
use CodeIgniter\Config\Services;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\URI;
use CodeIgniter\HTTP\UserAgent;
use CodeIgniter\Test\CIUnitTestCase;

final class FileControllerDownloadGpsFallbackTest extends CIUnitTestCase
{
    private string $tempFile;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tempFile = tempnam(sys_get_temp_dir(), 'test_photo_');
        $im = imagecreatetruecolor(4, 4);
        imagefill($im, 0, 0, imagecolorallocate($im, 10, 20, 30));
        imagejpeg($im, $this->tempFile, 90);
        imagedestroy($im);
    }

    protected function tearDown(): void
    {
        unset($_GET['download'], $_GET['gps']);

        if (is_file($this->tempFile)) {
            @unlink($this->tempFile);
        }

        parent::tearDown();
    }

    public function testFallsBackToOriginalFileWhenGpsEmbeddingFails(): void
    {
        // Mock FileAccessService
        $fileAccessMock = $this->createMock(FileAccessService::class);
        $fileAccessMock->method('resolve')
            ->willReturn([
                'absolute_path' => $this->tempFile,
                'mime_type' => 'image/jpeg',
                'file_name' => 'original_photo.jpg',
                'roles' => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                'record' => (object) ['foto_lat' => -6.9, 'foto_lng' => 107.5],
            ]);

        // Mock PhotoGpsMetadataService that throws an exception
        $photoGpsMock = $this->createMock(PhotoGpsMetadataService::class);
        $photoGpsMock->method('prepareDownload')
            ->willThrowException(new RuntimeException('Simulated GPS preparation failure'));

        $_GET['download'] = '1';
        $_GET['gps'] = '1';
        $uri = new URI('http://localhost/files/file_produksi/538?download=1&gps=1');
        $request = new IncomingRequest(new \Config\App(), $uri, 'php://input', new UserAgent());
        Services::injectMock('request', $request);

        $controller = new class($fileAccessMock, $photoGpsMock) extends FileController {
            public function __construct($fileAccess, $photoGps)
            {
                parent::__construct();
                $this->fileAccessService = $fileAccess;
                $this->photoGpsMetadataService = $photoGps;
            }
        };

        $controller->initController($request, Services::response(), Services::logger());

        $response = $controller->show('file_produksi', 538);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertStringContainsString('attachment', (string) $response->getHeaderLine('Content-Disposition'));
        $this->assertStringContainsString('original_photo.jpg', (string) $response->getHeaderLine('Content-Disposition'));
    }
}
