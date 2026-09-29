<?php

use App\Repositories\HasilAkadReportRepository;
use App\Services\HasilAkadReportService;
use CodeIgniter\Test\CIUnitTestCase;

final class HasilAkadReportServiceTest extends CIUnitTestCase
{
    public function testSummaryCombinesIndependentDatesAndFillsEmptyMonths(): void
    {
        $repository = $this->createMock(HasilAkadReportRepository::class);
        $repository->expects($this->once())
            ->method('getMonthlyAkadTotals')
            ->with(7, [2026, 2024])
            ->willReturn([
                ['tahun' => 2026, 'bulan' => 1, 'total_penjualan' => 500000000, 'jumlah_kavling' => 2, 'total_acc_kpr' => 350000000],
                ['tahun' => 2024, 'bulan' => 1, 'total_penjualan' => 200000000, 'jumlah_kavling' => 1, 'total_acc_kpr' => 150000000],
            ]);
        $repository->expects($this->once())
            ->method('getMonthlyPengajuanTotals')
            ->with(7, [2026, 2024])
            ->willReturn([
                ['tahun' => 2026, 'bulan' => 2, 'total_pengajuan' => 175000000],
            ]);
        $repository->expects($this->once())
            ->method('getMonthlyPaymentTotals')
            ->with(7, [2026, 2024])
            ->willReturn([
                ['tahun' => 2026, 'bulan' => 3, 'total_cair' => 100000000],
            ]);

        $result = (new HasilAkadReportService($repository))->getSummary(7, 2026, 2024);

        $this->assertCount(12, $result['months']);
        $this->assertSame('Januari', $result['months'][0]['month_label']);
        $this->assertSame(500000000.0, $result['months'][0]['values'][2026]['total_penjualan']);
        $this->assertSame(2, $result['months'][0]['values'][2026]['jumlah_kavling']);
        $this->assertSame(350000000.0, $result['months'][0]['values'][2026]['total_acc_kpr']);
        $this->assertSame(175000000.0, $result['months'][1]['values'][2026]['total_pengajuan']);
        $this->assertSame(100000000.0, $result['months'][2]['values'][2026]['total_cair']);
        $this->assertSame(0.0, $result['months'][3]['values'][2026]['total_penjualan']);
        $this->assertSame(500000000.0, $result['totals'][2026]['total_penjualan']);
        $this->assertSame(2, $result['totals'][2026]['jumlah_kavling']);
        $this->assertSame(175000000.0, $result['totals'][2026]['total_pengajuan']);
        $this->assertSame(100000000.0, $result['totals'][2026]['total_cair']);
    }

    public function testSummaryRejectsSameComparisonYear(): void
    {
        $service = new HasilAkadReportService($this->createMock(HasilAkadReportRepository::class));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Pilih dua tahun yang berbeda.');

        $service->getSummary(7, 2026, 2026);
    }

    public function testDetailNormalizesPagingSearchAndOrder(): void
    {
        $repository = $this->createMock(HasilAkadReportRepository::class);
        $repository->expects($this->once())
            ->method('getDetailRows')
            ->with(7, 2026, 2, 'pengajuan', [
                'draw' => 4,
                'start' => 0,
                'length' => 100,
                'search' => 'A-12',
                'order_by' => 'hasil_akad.nominal',
                'order_dir' => 'DESC',
            ])
            ->willReturn(['recordsTotal' => 3, 'recordsFiltered' => 1, 'data' => [['nominal' => 12500000]]]);

        $result = (new HasilAkadReportService($repository))->getDetail(7, [
            'draw' => 4,
            'start' => -5,
            'length' => 500,
            'year' => 2026,
            'month' => 2,
            'category' => 'pengajuan',
            'search' => ['value' => ' A-12 '],
            'columns' => [4 => ['name' => 'nominal']],
            'order' => [['column' => 4, 'dir' => 'desc']],
        ]);

        $this->assertSame(4, $result['draw']);
        $this->assertSame(3, $result['recordsTotal']);
        $this->assertSame(1, $result['recordsFiltered']);
    }

    public function testDetailRejectsUnknownCategory(): void
    {
        $service = new HasilAkadReportService($this->createMock(HasilAkadReportRepository::class));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Kategori laporan tidak valid.');

        $service->getDetail(7, ['year' => 2026, 'month' => 1, 'category' => 'void']);
    }
}
