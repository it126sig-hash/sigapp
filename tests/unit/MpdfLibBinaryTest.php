<?php

namespace Tests\Unit;

use App\Libraries\Mpdf_lib;
use CodeIgniter\Test\CIUnitTestCase;

final class MpdfLibBinaryTest extends CIUnitTestCase
{
    public function testRenderBinaryReturnsPdfWithoutPrintingIt(): void
    {
        ob_start();
        $pdf = (new Mpdf_lib())->renderBinary(
            '<html><body><h1>BPB response test</h1><p>PDF bytes stay in the response body.</p></body></html>',
            '',
            [8, 8, 8, 8],
            'A4-L'
        );
        $printed = ob_get_clean();

        $this->assertSame('', $printed);
        $this->assertStringStartsWith('%PDF-', $pdf);
        $this->assertGreaterThan(500, strlen($pdf));
        $this->assertStringNotContainsString('debugbar_loader', $pdf);
    }
}
