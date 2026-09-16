<?php

use App\Repositories\ReferralRepository;
use CodeIgniter\Database\BaseResult;
use CodeIgniter\Database\ConnectionInterface;
use CodeIgniter\Test\CIUnitTestCase;

final class ReferralRepositoryDateFilterTest extends CIUnitTestCase
{
    public function testBookingFilterUsesTypeSafeDatePredicate(): void
    {
        $repository = $this->repositoryExpectingSql(function (string $sql): void {
            $this->assertStringContainsString('YEAR(mk.booking_tgl) > 0', $sql);
            $this->assertStringNotContainsString("mk.booking_tgl <> ''", $sql);
            $this->assertStringNotContainsString("mk.booking_tgl <> '0000-00-00'", $sql);
        });

        $repository->getListMGM(12, [
            'filter_status' => 'booking',
            'tanggal_mulai' => '2024-01-01',
            'tanggal_selesai' => '2026-12-31',
        ]);
    }

    public function testPayoutFilterUsesTypeSafePredicatesForBothPayoutDates(): void
    {
        $repository = $this->repositoryExpectingSql(function (string $sql): void {
            $this->assertStringContainsString('YEAR(rb.tanggal_cair_keuangan) > 0', $sql);
            $this->assertStringContainsString('YEAR(rb.paid_promosi_tanggal) > 0', $sql);
            $this->assertStringNotContainsString("<> ''", $sql);
            $this->assertStringNotContainsString("<> '0000-00-00'", $sql);
        });

        $repository->getSubRowsByReferrer(64, 12, [
            'filter_status' => 'cair_bonus_booking',
            'tanggal_mulai' => '2024-01-01',
            'tanggal_selesai' => '2026-12-31',
        ]);
    }

    private function repositoryExpectingSql(callable $assertSql): ReferralRepository
    {
        $result = $this->getMockBuilder(BaseResult::class)
            ->disableOriginalConstructor()
            ->getMock();

        $db = $this->createMock(ConnectionInterface::class);
        $db->expects($this->once())
            ->method('query')
            ->with(
                $this->callback(function (string $sql) use ($assertSql): bool {
                    $assertSql($sql);
                    return true;
                }),
                $this->isType('array')
            )
            ->willReturn($result);

        return new ReferralRepository($db);
    }
}
