<?php

namespace Tests\Unit;

use App\Services\Bpb\BpbPdfService;
use App\Services\Bpb\BpbService;
use App\Services\FileAccessService;
use CodeIgniter\Test\CIUnitTestCase;
use Mpdf\Mpdf;
use ReflectionClass;
use ReflectionMethod;
use setasign\Fpdi\PdfParser\StreamReader;

final class BpbPdfServiceTest extends CIUnitTestCase
{
    public function testSignatureDateUsesIndonesianDateInJakartaTime(): void
    {
        $service = (new ReflectionClass(BpbPdfService::class))->newInstanceWithoutConstructor();

        $this->assertTrue(method_exists(BpbPdfService::class, 'formatSignatureDate'));
        $formatter = new ReflectionMethod(BpbPdfService::class, 'formatSignatureDate');

        $this->assertSame('30 September 2026', $formatter->invoke($service, '2026-09-29 17:30:00 UTC'));
        $this->assertSame('-', $formatter->invoke($service, ''));
    }

    public function testRenderAppendsAspectRatioImagePagesAndPreservesEveryPdfPageSize(): void
    {
        $imagePaths = [
            tempnam(sys_get_temp_dir(), 'bpb-image-landscape-'),
            tempnam(sys_get_temp_dir(), 'bpb-image-portrait-'),
            tempnam(sys_get_temp_dir(), 'bpb-image-panorama-'),
        ];
        $attachmentPdfPath = tempnam(sys_get_temp_dir(), 'bpb-attachment-');
        foreach ($imagePaths as $imagePath) {
            $this->assertNotFalse($imagePath);
        }
        $this->assertNotFalse($attachmentPdfPath);

        try {
            foreach ([[640, 360], [360, 640], [4000, 500]] as $index => [$width, $height]) {
                $image = imagecreatetruecolor($width, $height);
                $blue = imagecolorallocate($image, 32, 87, 163);
                imagefilledrectangle($image, 0, 0, $width - 1, $height - 1, $blue);
                imagepng($image, $imagePaths[$index]);
                imagedestroy($image);
            }

            $sourcePdf = new Mpdf(['format' => 'A4', 'margin_left' => 0, 'margin_right' => 0, 'margin_top' => 0, 'margin_bottom' => 0]);
            $sourcePdf->WriteHTML('<html><body>Attachment page one</body></html>');
            $sourcePdf->AddPageByArray(['orientation' => 'L', 'sheet-size' => 'A4-L']);
            $sourcePdf->WriteHTML('<html><body>Attachment page two</body></html>');
            $attachmentBytes = $sourcePdf->Output('', \Mpdf\Output\Destination::STRING_RETURN);
            file_put_contents($attachmentPdfPath, $attachmentBytes);
            $sourceInspector = new Mpdf();
            $sourcePageCount = $sourceInspector->setSourceFile(StreamReader::createByString($attachmentBytes));
            $sourceSizes = [];
            for ($page = 1; $page <= $sourcePageCount; $page++) {
                $sourceTemplate = $sourceInspector->importPage($page);
                $sourceSizes[] = $sourceInspector->getTemplateSize($sourceTemplate);
            }
            $filePaths = [
                'request/image-landscape.png' => $imagePaths[0],
                'request/image-portrait.png' => $imagePaths[1],
                'request/image-panorama.png' => $imagePaths[2],
                'purchase/source.pdf' => $attachmentPdfPath,
            ];
            $fileAccess = new class($filePaths) extends FileAccessService {
                public function __construct(private array $paths) {}

                public function privatePath(string $logicalPath): string
                {
                    return $this->paths[$logicalPath] ?? throw new \RuntimeException('Unknown test file.');
                }
            };
            $bpbService = new class extends BpbService {
                public function __construct() {}
            };
            $detail = [
                'id' => 1,
                'nomor' => '001/BPB/IX/2026',
                'status' => 'purchased',
                'status_label' => 'Sudah Dibeli',
                'applicant_name' => 'Pemohon Uji',
                'applicant_department' => 'Divisi Uji',
                'submitted_at' => '2026-09-30 10:00:00',
                'created_at' => '2026-09-30 09:00:00',
                'current_document_hash' => str_repeat('a', 64),
                'verification_token' => 'qr-final-pdf-test-token-20260930',
                'items' => [[
                    'nama_barang' => 'Barang uji',
                    'jumlah' => '1.00',
                    'satuan' => 'pcs',
                    'keterangan' => '',
                ]],
                'signatures' => [],
                'files' => [
                    ['id' => 1, 'category' => 'request', 'logical_path' => 'request/image-landscape.png', 'original_name' => 'image-landscape.png'],
                    ['id' => 2, 'category' => 'request', 'logical_path' => 'request/image-portrait.png', 'original_name' => 'image-portrait.png'],
                    ['id' => 3, 'category' => 'request', 'logical_path' => 'request/image-panorama.png', 'original_name' => 'image-panorama.png'],
                    ['id' => 4, 'category' => 'purchase', 'logical_path' => 'purchase/source.pdf', 'original_name' => 'source.pdf'],
                ],
            ];

            $pdf = (new BpbPdfService($fileAccess, $bpbService))->render($detail, 1);
            $reader = new Mpdf();
            $pageCount = $reader->setSourceFile(StreamReader::createByString($pdf));

            $this->assertSame(6, $pageCount, 'Expected form, three image pages, and both source PDF pages.');
            $expectedSizes = [[210.0, 148.0], [297.0, 167.0625], [167.0625, 297.0], [297.0, 37.125]];
            foreach ($sourceSizes as $sourceSize) {
                $expectedSizes[] = [(float) $sourceSize['width'], (float) $sourceSize['height']];
            }
            for ($page = 1; $page <= $pageCount; $page++) {
                $template = $reader->importPage($page);
                $size = $reader->getTemplateSize($template);
                $this->assertIsArray($size);
                $this->assertEqualsWithDelta($expectedSizes[$page - 1][0], $size['width'], 1.0, 'Unexpected page width at page ' . $page . ': ' . json_encode($size));
                $this->assertEqualsWithDelta($expectedSizes[$page - 1][1], $size['height'], 1.0, 'Unexpected page height at page ' . $page . ': ' . json_encode($size));
            }
        } finally {
            foreach ($imagePaths as $imagePath) {
                @unlink($imagePath);
            }
            @unlink($attachmentPdfPath);
        }
    }
}
