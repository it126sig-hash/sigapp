<?php

use App\Services\PencairanAkadService;
use CodeIgniter\Test\CIUnitTestCase;

final class PencairanAkadListFilterTest extends CIUnitTestCase
{
    public function testSingleDateBecomesInclusiveOneDayRange(): void
    {
        $this->assertSame(
            ['2026-02-10', '2026-02-10'],
            $this->normalize('2026-02-10')
        );
    }

    public function testReversedRangeIsNormalized(): void
    {
        $this->assertSame(
            ['2026-02-01', '2026-02-28'],
            $this->normalize('2026-02-28 to 2026-02-01')
        );
    }

    public function testInvalidRangeIsIgnored(): void
    {
        $this->assertSame([null, null], $this->normalize('31-02-2026'));
    }

    private function normalize(string $range): array
    {
        $reflection = new ReflectionClass(PencairanAkadService::class);
        $service = $reflection->newInstanceWithoutConstructor();
        $method = $reflection->getMethod('normalizeListDateRange');
        $method->setAccessible(true);

        return $method->invoke($service, $range);
    }
}
