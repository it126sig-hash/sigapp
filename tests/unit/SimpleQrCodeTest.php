<?php

namespace Tests\Unit;

use App\Libraries\SimpleQrCode;
use CodeIgniter\Test\CIUnitTestCase;
use ReflectionProperty;

final class SimpleQrCodeTest extends CIUnitTestCase
{
    public function testAlignmentPatternsAreDrawnAcrossTimingLinesForVersionsSixToEight(): void
    {
        $cases = [
            [100, 6, 32, 32],
            [110, 7, 4, 20],
            [130, 8, 4, 22],
        ];

        foreach ($cases as [$length, $version, $row, $column]) {
            $qr = SimpleQrCode::encodeText(str_repeat('a', $length));
            $versionProperty = new ReflectionProperty(SimpleQrCode::class, 'version');
            $functionModulesProperty = new ReflectionProperty(SimpleQrCode::class, 'isFunction');
            $functionModules = $functionModulesProperty->getValue($qr);

            $this->assertSame($version, $versionProperty->getValue($qr));
            $this->assertSame(
                [true, true, true, true, true],
                array_slice($functionModules[$row], $column, 5),
                'The alignment pattern centered on the timing line must replace the timing modules.'
            );
            $this->assertNotSame('', $qr->toPng(2, 4));
        }
    }
}
