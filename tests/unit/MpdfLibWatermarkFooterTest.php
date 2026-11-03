<?php

namespace Tests\Unit;

use App\Libraries\Mpdf_lib;
use CodeIgniter\Test\CIUnitTestCase;
use Mpdf\Mpdf;

final class MpdfLibWatermarkFooterTest extends CIUnitTestCase
{
    public function testWatermarkFooterEnabledByDefault(): void
    {
        $lib = new Mpdf_lib();

        // 1. Resolver status
        $this->assertTrue($lib->isWatermarkFooterEnabled());

        // 2. HTML watermark footer
        $footerHtml = $lib->buildWatermarkFooterHtml();
        $this->assertStringContainsString('class="sigapp-pdf-watermark-footer"', $footerHtml);
        $this->assertStringContainsString('Dokumen ini dicetak otomatis melalui', $footerHtml);
        $this->assertStringContainsString('SIGAPP', $footerHtml);
        $this->assertStringContainsString('Hal. {PAGENO} / {nbpg}', $footerHtml);

        // 3. Render binary default
        $pdf = $lib->renderBinary('<html><body><h1>Watermark Active Test</h1></body></html>');
        $this->assertStringStartsWith('%PDF-', $pdf);
        $this->assertGreaterThan(500, strlen($pdf));
    }

    public function testWatermarkFooterDisabledViaParam(): void
    {
        $lib = new Mpdf_lib();

        // 1. Explicit resolver parameter
        $this->assertFalse($lib->isWatermarkFooterEnabled(false));
        $this->assertTrue($lib->isWatermarkFooterEnabled(true));

        // 2. Setter method
        $lib->setWatermarkFooter(false);
        $this->assertFalse($lib->isWatermarkFooterEnabled());

        // 3. Render binary with watermarkFooter parameter = false
        $freshLib = new Mpdf_lib();
        $pdf = $freshLib->renderBinary(
            '<html><body><h1>Disabled Param Test</h1></body></html>',
            watermarkFooter: false
        );
        $this->assertStringStartsWith('%PDF-', $pdf);
        $this->assertGreaterThan(500, strlen($pdf));

        // 4. Render binary with setter setWatermarkFooter(false)
        $pdfSetter = (new Mpdf_lib())
            ->setWatermarkFooter(false)
            ->renderBinary('<html><body><h1>Disabled Setter Test</h1></body></html>');
        $this->assertStringStartsWith('%PDF-', $pdfSetter);
        $this->assertGreaterThan(500, strlen($pdfSetter));
    }

    public function testWatermarkFooterMergingWithCustomFooter(): void
    {
        $lib = new Mpdf_lib();

        // 1. Standard string HTML with custom footer
        $customFooter = '<div style="text-align: center;">Kerahasiaan Dokumen Terjamin</div>';
        $pdfCustom = $lib->renderBinary(
            '<html><body><h1>Custom Footer Merge</h1></body></html>',
            footer: $customFooter
        );
        $this->assertStringStartsWith('%PDF-', $pdfCustom);
        $this->assertGreaterThan(500, strlen($pdfCustom));

        // 2. HTML containing existing <htmlpagefooter>
        $htmlWithFooterTag = '<html><body>'
            . '<htmlpagefooter name="customPageFooter">'
            . '<div style="font-size: 8pt;">Dokumen Sah Kantor</div>'
            . '</htmlpagefooter>'
            . '<sethtmlpagefooter name="customPageFooter" value="on" />'
            . '<h1>Page with htmlpagefooter</h1>'
            . '</body></html>';

        $pdfTag = (new Mpdf_lib())->renderBinary($htmlWithFooterTag);
        $this->assertStringStartsWith('%PDF-', $pdfTag);
        $this->assertGreaterThan(500, strlen($pdfTag));

        // 3. Margin bottom adjustment when margin < 8mm
        $capturedMpdf = null;
        $lib->renderBinary(
            '<html><body><h1>Margin Adjustment</h1></body></html>',
            mg: [10, 10, 10, 3],
            beforeOutput: function (Mpdf $mpdf) use (&$capturedMpdf): void {
                $capturedMpdf = $mpdf;
            }
        );
        $this->assertNotNull($capturedMpdf);
        $this->assertGreaterThanOrEqual(8, $capturedMpdf->bMargin);
    }

    public function testWatermarkFooterMultiPageArray(): void
    {
        $lib = new Mpdf_lib();

        // 1. Array of string pages
        $pages = [
            '<h1>Page 1</h1><p>Content page 1</p>',
            '<h1>Page 2</h1><p>Content page 2</p>',
        ];
        $pdfPages = $lib->renderBinary($pages, footer: '<div>First Page Note</div>');
        $this->assertStringStartsWith('%PDF-', $pdfPages);
        $this->assertGreaterThan(1000, strlen($pdfPages));

        // 2. Array of associative array pages with explicit page footers
        $pagesWithFooters = [
            [
                'html' => '<h1>Page 1 with Footer</h1>',
                'footer' => '<div>Section A Footer</div>',
            ],
            [
                'html' => '<h1>Page 2 with Empty Footer</h1>',
                'footer' => '',
            ],
            [
                'html' => '<h1>Page 3 without Footer key</h1>',
            ],
        ];

        $pdfMulti = $lib->renderBinary($pagesWithFooters);
        $this->assertStringStartsWith('%PDF-', $pdfMulti);
        $this->assertGreaterThan(1000, strlen($pdfMulti));
    }
}
