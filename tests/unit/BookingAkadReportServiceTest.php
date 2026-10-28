<?php

use App\Repositories\BookingAkadReportRepository;
use App\Services\BookingAkadReportService;
use CodeIgniter\Test\CIUnitTestCase;

final class BookingAkadReportServiceTest extends CIUnitTestCase
{
    public function testSummaryCombinesBookingAndAkadAndCalculatesRatio(): void
    {
        $repository = $this->createMock(BookingAkadReportRepository::class);
        $repository->expects($this->once())
            ->method('getMonthlyBookingTotals')
            ->with(7, [2026, 2025])
            ->willReturn([
                ['tahun' => 2026, 'bulan' => 1, 'jumlah_booking' => 10, 'booking_aktif' => 8, 'booking_batal' => 2],
                ['tahun' => 2025, 'bulan' => 1, 'jumlah_booking' => 4, 'booking_aktif' => 3, 'booking_batal' => 1],
            ]);
        $repository->expects($this->once())
            ->method('getMonthlyAkadTotals')
            ->with(7, [2026, 2025])
            ->willReturn([
                ['tahun' => 2026, 'bulan' => 1, 'jumlah_akad' => 5],
                ['tahun' => 2025, 'bulan' => 1, 'jumlah_akad' => 2],
            ]);

        $result = (new BookingAkadReportService($repository))->getSummary(7, 2026, 2025);

        $this->assertCount(12, $result['months']);
        $this->assertSame('Januari', $result['months'][0]['month_label']);
        $this->assertSame(10, $result['months'][0]['values'][2026]['jumlah_booking']);
        $this->assertSame(8, $result['months'][0]['values'][2026]['booking_aktif']);
        $this->assertSame(2, $result['months'][0]['values'][2026]['booking_batal']);
        $this->assertSame(5, $result['months'][0]['values'][2026]['jumlah_akad']);
        $this->assertSame(50.0, $result['months'][0]['values'][2026]['rasio_akad']);

        $this->assertSame(4, $result['months'][0]['values'][2025]['jumlah_booking']);
        $this->assertSame(3, $result['months'][0]['values'][2025]['booking_aktif']);
        $this->assertSame(1, $result['months'][0]['values'][2025]['booking_batal']);
        $this->assertSame(2, $result['months'][0]['values'][2025]['jumlah_akad']);
        $this->assertSame(50.0, $result['months'][0]['values'][2025]['rasio_akad']);

        // Test totals
        $this->assertSame(10, $result['totals'][2026]['jumlah_booking']);
        $this->assertSame(8, $result['totals'][2026]['booking_aktif']);
        $this->assertSame(2, $result['totals'][2026]['booking_batal']);
        $this->assertSame(5, $result['totals'][2026]['jumlah_akad']);
        $this->assertSame(50.0, $result['totals'][2026]['rasio_akad']);
    }

    public function testSummaryRejectsSameComparisonYear(): void
    {
        $service = new BookingAkadReportService($this->createMock(BookingAkadReportRepository::class));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Pilih dua tahun yang berbeda.');

        $service->getSummary(7, 2026, 2026);
    }

    public function testDetailNormalizesPagingSearchAndOrder(): void
    {
        $repository = $this->createMock(BookingAkadReportRepository::class);
        $repository->expects($this->once())
            ->method('getDetailRows')
            ->with(7, 2026, 2, 'booking_aktif', [
                'draw' => 2,
                'start' => 0,
                'length' => 100,
                'search' => 'B-01',
                'order_by' => 'booking_akad.tanggal_transaksi',
                'order_dir' => 'DESC',
            ])
            ->willReturn(['recordsTotal' => 5, 'recordsFiltered' => 1, 'data' => [['nominal' => 1000000]]]);

        $result = (new BookingAkadReportService($repository))->getDetail(7, [
            'draw' => 2,
            'start' => -10,
            'length' => 999,
            'year' => 2026,
            'month' => 2,
            'category' => 'booking_aktif',
            'search' => ['value' => ' B-01 '],
            'columns' => [4 => ['name' => 'tanggal_transaksi']],
            'order' => [['column' => 4, 'dir' => 'desc']],
        ]);

        $this->assertSame(2, $result['draw']);
        $this->assertSame(5, $result['recordsTotal']);
        $this->assertSame(1, $result['recordsFiltered']);
    }

    public function testDetailRejectsUnknownCategory(): void
    {
        $service = new BookingAkadReportService($this->createMock(BookingAkadReportRepository::class));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Kategori laporan tidak valid.');

        $service->getDetail(7, ['year' => 2026, 'month' => 1, 'category' => 'unknown_cat']);
    }
}
