<?php

use App\Services\SiteplanDataService;
use CodeIgniter\Test\CIUnitTestCase;

final class SiteplanDataServiceTest extends CIUnitTestCase
{
    public function testNormalizeIdListAcceptsLegacyScalarAndMultiClusterArray(): void
    {
        $this->assertSame([7], SiteplanDataService::normalizeIdList('7'));
        $this->assertSame([2, 7], SiteplanDataService::normalizeIdList(['2', 2, '0', '-1', 'abc', 7]));
        $this->assertSame([], SiteplanDataService::normalizeIdList(null));
    }
}
