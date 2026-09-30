<?php

namespace Tests\Unit;

use App\Services\Bpb\BpbNumberFormatter;
use CodeIgniter\Test\CIUnitTestCase;

final class BpbNumberFormatterTest extends CIUnitTestCase
{
    public function testFormatsRomanMonthAndMinimumThreeDigits(): void
    {
        $this->assertSame('001/BPB/IX/2026', BpbNumberFormatter::format(1, 9, 2026));
        $this->assertSame('1234/BPB/XII/2026', BpbNumberFormatter::format(1234, 12, 2026));
    }
}
