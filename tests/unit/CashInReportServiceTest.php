<?php

use App\Repositories\CashInReportRepository;
use App\Services\CashInReportService;
use CodeIgniter\Test\CIUnitTestCase;

final class CashInReportServiceTest extends CIUnitTestCase
{
    public function testSummaryCombinesThreeIncomeSourcesAndFillsEmptyMonths(): void
    {
        $repository = $this->createMock(CashInReportRepository::class);
        $repository->expects($this->once())
            ->method('getMonthlyPaymentTotals')
            ->with(7, [2026, 2024])
            ->willReturn([
                ['tahun' => 2026, 'bulan' => 1, 'booking_fee' => 10000000, 'uang_muka' => 25000000],
                ['tahun' => 2024, 'bulan' => 1, 'booking_fee' => 5000000, 'uang_muka' => 15000000],
            ]);
        $repository->expects($this->once())
            ->method('getMonthlyAkadTotals')
            ->with(7, [2026, 2024])
            ->willReturn([
                ['tahun' => 2026, 'bulan' => 1, 'hasil_akad' => 100000000],
            ]);

        $result = (new CashInReportService($repository))->getSummary(7, 2026, 2024);

        $this->assertCount(12, $result['months']);
        $this->assertSame('Januari', $result['months'][0]['month_label']);
        $this->assertSame(135000000.0, $result['months'][0]['values'][2026]['total']);
        $this->assertSame(20000000.0, $result['months'][0]['values'][2024]['total']);
        $this->assertSame(0.0, $result['months'][1]['values'][2026]['total']);
        $this->assertSame(135000000.0, $result['totals'][2026]['total']);
        $this->assertSame(20000000.0, $result['totals'][2024]['total']);
    }

    public function testSummaryRejectsSameComparisonYear(): void
    {
        $service = new CashInReportService($this->createMock(CashInReportRepository::class));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Pilih dua tahun yang berbeda.');

        $service->getSummary(7, 2026, 2026);
    }

    public function testDetailRejectsRefundAsUnsupportedCategory(): void
    {
        $service = new CashInReportService($this->createMock(CashInReportRepository::class));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Kategori pendapatan tidak valid.');

        $service->getDetail(7, [
            'year' => 2026,
            'month' => 1,
            'category' => 'refund',
        ]);
    }

    public function testDetailNormalizesPagingSearchAndOrder(): void
    {
        $repository = $this->createMock(CashInReportRepository::class);
        $repository->expects($this->once())
            ->method('getDetailRows')
            ->with(
                7,
                2026,
                2,
                'all',
                [
                    'draw' => 4,
                    'start' => 0,
                    'length' => 100,
                    'search' => 'A-12',
                    'order_by' => 'cash_in.nominal',
                    'order_dir' => 'DESC',
                ]
            )
            ->willReturn([
                'recordsTotal' => 3,
                'recordsFiltered' => 1,
                'data' => [['nominal' => 12500000]],
            ]);

        $result = (new CashInReportService($repository))->getDetail(7, [
            'draw' => 4,
            'start' => -5,
            'length' => 500,
            'year' => 2026,
            'month' => 2,
            'category' => 'all',
            'search' => ['value' => ' A-12 '],
            'columns' => [4 => ['name' => 'nominal']],
            'order' => [['column' => 4, 'dir' => 'desc']],
        ]);

        $this->assertSame(4, $result['draw']);
        $this->assertSame(3, $result['recordsTotal']);
        $this->assertSame(1, $result['recordsFiltered']);
        $this->assertSame([['nominal' => 12500000]], $result['data']);
    }

    public function testAvailableYearsIncludeCurrentAndPreviousYear(): void
    {
        $repository = $this->createMock(CashInReportRepository::class);
        $repository->method('getAvailableYearBounds')->willReturn(['min' => 2022, 'max' => 2024]);

        $years = (new CashInReportService($repository))->getAvailableYears(7);
        $currentYear = (int) date('Y');

        $this->assertSame($currentYear, $years[0]);
        $this->assertContains($currentYear - 1, $years);
        $this->assertSame(2022, $years[array_key_last($years)]);
    }
}
