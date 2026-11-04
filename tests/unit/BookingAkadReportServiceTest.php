<?php

namespace Tests\Unit;

use App\Repositories\BookingAkadReportRepository;
use App\Services\BookingAkadReportService;
use CodeIgniter\Test\CIUnitTestCase;
use InvalidArgumentException;

final class BookingAkadReportServiceTest extends CIUnitTestCase
{
    /**
     * Test R1: CATEGORIES constant must contain all 6 transaction statuses.
     */
    public function testCategoriesConstantContainsAllSixStatuses(): void
    {
        $expected = ['booking', 'wawancara', 'sp3k', 'batal', 'akad_indent', 'akad_ajb'];

        $this->assertCount(6, BookingAkadReportService::CATEGORIES);
        $this->assertSame($expected, BookingAkadReportService::CATEGORIES);
    }

    /**
     * Test R1 & R2: Aggregation of 6 statuses and real cohort conversion %.
     */
    public function testSummaryAggregatesSixStatusesAndCalculatesCohortConversion(): void
    {
        $repository = $this->createMock(BookingAkadReportRepository::class);

        // Mock repository returning 6 statuses and cohort tracking
        $repository->expects($this->once())
            ->method('getMonthlySummaryTotals')
            ->with(7, [2026, 2025])
            ->willReturn([
                [
                    'tahun' => 2026,
                    'bulan' => 1,
                    'booking' => 10,
                    'wawancara' => 4,
                    'sp3k' => 3,
                    'batal' => 1,
                    'akad_indent' => 2,
                    'akad_ajb' => 3,
                    'booking_converted_akad' => 5,
                    'total_booking_cohort' => 10,
                ],
                [
                    'tahun' => 2026,
                    'bulan' => 2,
                    'booking' => 20,
                    'wawancara' => 8,
                    'sp3k' => 6,
                    'batal' => 2,
                    'akad_indent' => 4,
                    'akad_ajb' => 6,
                    'booking_converted_akad' => 10,
                    'total_booking_cohort' => 20,
                ],
                [
                    'tahun' => 2025,
                    'bulan' => 1,
                    'booking' => 6,
                    'wawancara' => 2,
                    'sp3k' => 2,
                    'batal' => 0,
                    'akad_indent' => 1,
                    'akad_ajb' => 2,
                    'booking_converted_akad' => 3,
                    'total_booking_cohort' => 6,
                ],
            ]);

        $service = new BookingAkadReportService($repository);
        $result = $service->getSummary(7, 2026, 2025);

        // Verify structure
        $this->assertSame([2026, 2025], $result['years']);
        $this->assertCount(12, $result['months']);

        // Month 1 (Januari 2026): 10 booking, 5 converted to akad => 50.0%
        $jan2026 = $result['months'][0]['values'][2026];
        $this->assertSame(10, $jan2026['booking']);
        $this->assertSame(4, $jan2026['wawancara']);
        $this->assertSame(3, $jan2026['sp3k']);
        $this->assertSame(1, $jan2026['batal']);
        $this->assertSame(2, $jan2026['akad_indent']);
        $this->assertSame(3, $jan2026['akad_ajb']);
        $this->assertSame(50.0, $jan2026['konversi']);

        // Month 2 (Februari 2026): 20 booking, 10 converted to akad => 50.0%
        $feb2026 = $result['months'][1]['values'][2026];
        $this->assertSame(20, $feb2026['booking']);
        $this->assertSame(50.0, $feb2026['konversi']);

        // Month 3 (Maret 2026): unpopulated month defaults to zero
        $mar2026 = $result['months'][2]['values'][2026];
        $this->assertSame(0, $mar2026['booking']);
        $this->assertSame(0.0, $mar2026['konversi']);

        // Year 2025 Month 1: 6 booking, 3 converted => 50.0%
        $jan2025 = $result['months'][0]['values'][2025];
        $this->assertSame(6, $jan2025['booking']);
        $this->assertSame(50.0, $jan2025['konversi']);

        // Annual Totals for 2026: 30 booking, 15 converted => 50.0%
        $totals2026 = $result['totals'][2026];
        $this->assertSame(30, $totals2026['booking']);
        $this->assertSame(12, $totals2026['wawancara']);
        $this->assertSame(9, $totals2026['sp3k']);
        $this->assertSame(3, $totals2026['batal']);
        $this->assertSame(6, $totals2026['akad_indent']);
        $this->assertSame(9, $totals2026['akad_ajb']);
        $this->assertSame(50.0, $totals2026['konversi']);
    }

    /**
     * Test R2 Cohort Conversion Math: 0 bookings yields 0.0% without division-by-zero.
     */
    public function testCohortConversionZeroBookingsYieldsZeroPercent(): void
    {
        $repository = $this->createMock(BookingAkadReportRepository::class);
        $repository->method('getMonthlySummaryTotals')->willReturn([
            ['tahun' => 2026, 'bulan' => 1, 'booking' => 0, 'total_booking_cohort' => 0, 'booking_converted_akad' => 0],
        ]);

        $service = new BookingAkadReportService($repository);
        $result = $service->getSummary(7, 2026);

        $this->assertSame(0.0, $result['months'][0]['values'][2026]['konversi']);
        $this->assertSame(0.0, $result['totals'][2026]['konversi']);
    }

    /**
     * Test R2 Cohort Conversion Math: 10 bookings, 5 akad => 50.0%.
     */
    public function testCohortConversionFiftyPercent(): void
    {
        $repository = $this->createMock(BookingAkadReportRepository::class);
        $repository->method('getMonthlySummaryTotals')->willReturn([
            ['tahun' => 2026, 'bulan' => 1, 'booking' => 10, 'total_booking_cohort' => 10, 'booking_converted_akad' => 5],
        ]);

        $service = new BookingAkadReportService($repository);
        $result = $service->getSummary(7, 2026);

        $this->assertSame(50.0, $result['months'][0]['values'][2026]['konversi']);
    }

    /**
     * Test R2 Cohort Conversion Math: 10 bookings, 10 akad => 100.0%.
     */
    public function testCohortConversionOneHundredPercent(): void
    {
        $repository = $this->createMock(BookingAkadReportRepository::class);
        $repository->method('getMonthlySummaryTotals')->willReturn([
            ['tahun' => 2026, 'bulan' => 1, 'booking' => 10, 'total_booking_cohort' => 10, 'booking_converted_akad' => 10],
        ]);

        $service = new BookingAkadReportService($repository);
        $result = $service->getSummary(7, 2026);

        $this->assertSame(100.0, $result['months'][0]['values'][2026]['konversi']);
    }

    /**
     * Test R2 Cohort Conversion Math: 10 bookings, 0 akad => 0.0%.
     */
    public function testCohortConversionZeroAkadYieldsZeroPercent(): void
    {
        $repository = $this->createMock(BookingAkadReportRepository::class);
        $repository->method('getMonthlySummaryTotals')->willReturn([
            ['tahun' => 2026, 'bulan' => 1, 'booking' => 10, 'total_booking_cohort' => 10, 'booking_converted_akad' => 0],
        ]);

        $service = new BookingAkadReportService($repository);
        $result = $service->getSummary(7, 2026);

        $this->assertSame(0.0, $result['months'][0]['values'][2026]['konversi']);
    }

    /**
     * Test R2 Cohort Conversion Math: 1 decimal place rounding precision.
     */
    public function testCohortConversionRoundingPrecision(): void
    {
        $repository = $this->createMock(BookingAkadReportRepository::class);
        $repository->method('getMonthlySummaryTotals')->willReturn([
            ['tahun' => 2026, 'bulan' => 1, 'booking' => 3, 'total_booking_cohort' => 3, 'booking_converted_akad' => 1], // 33.333% -> 33.3
            ['tahun' => 2026, 'bulan' => 2, 'booking' => 7, 'total_booking_cohort' => 7, 'booking_converted_akad' => 2], // 28.571% -> 28.6
            ['tahun' => 2026, 'bulan' => 3, 'booking' => 6, 'total_booking_cohort' => 6, 'booking_converted_akad' => 1], // 16.666% -> 16.7
        ]);

        $service = new BookingAkadReportService($repository);
        $result = $service->getSummary(7, 2026);

        $this->assertSame(33.3, $result['months'][0]['values'][2026]['konversi']);
        $this->assertSame(28.6, $result['months'][1]['values'][2026]['konversi']);
        $this->assertSame(16.7, $result['months'][2]['values'][2026]['konversi']);
    }

    /**
     * Test R2 Annual Conversion: Calculated as weighted sum of cohorts, NOT arithmetic average of ratios.
     */
    public function testAnnualConversionCalculatedAsWeightedSumRatherThanAverageOfRatios(): void
    {
        $repository = $this->createMock(BookingAkadReportRepository::class);
        $repository->method('getMonthlySummaryTotals')->willReturn([
            ['tahun' => 2026, 'bulan' => 1, 'booking' => 1, 'total_booking_cohort' => 1, 'booking_converted_akad' => 1], // 100.0%
            ['tahun' => 2026, 'bulan' => 2, 'booking' => 100, 'total_booking_cohort' => 100, 'booking_converted_akad' => 10], // 10.0%
        ]);

        $service = new BookingAkadReportService($repository);
        $result = $service->getSummary(7, 2026);

        // Arithmetic average would be (100.0 + 10.0) / 2 = 55.0% or 110 / 12 = 9.17%
        // True weighted cohort conversion is (1 + 10) / (1 + 100) = 11 / 101 * 100 = 10.89% -> 10.9%
        $this->assertSame(10.9, $result['totals'][2026]['konversi']);
        $this->assertNotEquals(55.0, $result['totals'][2026]['konversi']);
    }

    /**
     * Test Validation: Rejection of identical comparison years.
     */
    public function testSummaryRejectsSameComparisonYear(): void
    {
        $service = new BookingAkadReportService($this->createMock(BookingAkadReportRepository::class));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Pilih dua tahun yang berbeda.');

        $service->getSummary(7, 2026, 2026);
    }

    /**
     * Test Validation: Rejection of invalid project ID or year bounds.
     */
    public function testSummaryValidatesProjectIdAndYearBounds(): void
    {
        $service = new BookingAkadReportService($this->createMock(BookingAkadReportRepository::class));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Pilih proyek aktif terlebih dahulu.');
        $service->getSummary(0, 2026);
    }

    /**
     * Test R3: Detail Data Normalization and Acceptance of all 6 Status Categories.
     */
    public function testDetailAcceptsAllSixCategories(): void
    {
        $repository = $this->createMock(BookingAkadReportRepository::class);
        $repository->expects($this->exactly(6))
            ->method('getDetailRows')
            ->willReturn(['recordsTotal' => 0, 'recordsFiltered' => 0, 'data' => []]);

        $service = new BookingAkadReportService($repository);

        foreach (BookingAkadReportService::CATEGORIES as $category) {
            $result = $service->getDetail(7, [
                'draw' => 1,
                'year' => 2026,
                'month' => 4,
                'category' => $category,
            ]);
            $this->assertSame(1, $result['draw']);
            $this->assertIsArray($result['data']);
        }
    }

    /**
     * Test R3: Detail rejects invalid category.
     */
    public function testDetailRejectsInvalidCategory(): void
    {
        $service = new BookingAkadReportService($this->createMock(BookingAkadReportRepository::class));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Kategori laporan tidak valid.');

        $service->getDetail(7, ['year' => 2026, 'month' => 1, 'category' => 'booking_aktif']);
    }

    /**
     * Test R3: Detail formats raw database rows into the clean 7-column contract.
     */
    public function testDetailFormatsRowToSevenColumnContract(): void
    {
        $repository = $this->createMock(BookingAkadReportRepository::class);
        $repository->method('getDetailRows')->willReturn([
            'recordsTotal' => 1,
            'recordsFiltered' => 1,
            'data' => [
                [
                    'id_mkdt' => 147,
                    'id_kavling' => 52,
                    'nama_jalan' => 'Jalan Mawar',
                    'no_kavling' => 'A-12',
                    'sales' => 'Budi Santoso',
                    'nama_konsumen' => 'Ahmad Supardi',
                    'nama_tipe' => '36/60',
                    'is_subsidi' => 1,
                    'booking_tgl' => '2024-10-09',
                    'status_terakhir' => 'Akad AJB',
                    'status_terakhir_badge' => 'success',
                    'catatan_mkdt' => 'Berkas lengkap dan acc bank',
                    'tanggal_status_terakhir' => '2025-04-14',
                ],
            ],
        ]);

        $service = new BookingAkadReportService($repository);
        $result = $service->getDetail(7, ['year' => 2026, 'month' => 4, 'category' => 'akad_ajb']);

        $row = $result['data'][0];
        $this->assertSame(147, $row['id_mkdt']);
        $this->assertSame(52, $row['id_kavling']);
        $this->assertSame([
            'nama_jalan' => 'Jalan Mawar',
            'no_kavling' => 'A-12',
            'sales' => 'Budi Santoso',
        ], $row['kavling']);
        $this->assertSame([
            'nama_konsumen' => 'Ahmad Supardi',
            'tipe_rumah' => '36/60',
            'is_subsidi' => true,
        ], $row['konsumen']);
        $this->assertSame('09-10-2024', $row['tanggal_booking']);
        $this->assertSame('Akad AJB', $row['status_terakhir']);
        $this->assertSame('success', $row['status_terakhir_badge']);
        $this->assertSame('Berkas lengkap dan acc bank', $row['catatan_mkdt']);
        $this->assertSame('14-04-2025', $row['tanggal_status_terakhir']);
    }

    /**
     * Test Edge Cases: Safe handling of zero-dates ('0000-00-00'), null sales, and null fields without PHP notices.
     */
    public function testDetailSafeHandlingOfZeroDatesAndNullFields(): void
    {
        $repository = $this->createMock(BookingAkadReportRepository::class);
        $repository->method('getDetailRows')->willReturn([
            'recordsTotal' => 1,
            'recordsFiltered' => 1,
            'data' => [
                [
                    'id_mkdt' => 99,
                    'id_kavling' => 10,
                    'nama_jalan' => null,
                    'no_kavling' => null,
                    'sales' => null, // Null sales
                    'nama_konsumen' => null,
                    'nama_tipe' => null,
                    'tipe_rumah' => null,
                    'is_subsidi' => null,
                    'booking_tgl' => '0000-00-00', // Zero date
                    'status_terakhir' => null,
                    'catatan_mkdt' => null,
                    'tanggal_status_terakhir' => '0000-00-00 00:00:00', // Zero datetime
                ],
            ],
        ]);

        $service = new BookingAkadReportService($repository);
        $result = $service->getDetail(7, ['year' => 2026, 'month' => 1, 'category' => 'booking']);

        $row = $result['data'][0];
        $this->assertSame('-', $row['kavling']['nama_jalan']);
        $this->assertSame('-', $row['kavling']['no_kavling']);
        $this->assertSame('-', $row['kavling']['sales']);
        $this->assertSame('-', $row['konsumen']['nama_konsumen']);
        $this->assertSame('-', $row['konsumen']['tipe_rumah']);
        $this->assertFalse($row['konsumen']['is_subsidi']);
        $this->assertSame('-', $row['tanggal_booking']);
        $this->assertSame('-', $row['tanggal_status_terakhir']);
        $this->assertSame('-', $row['catatan_mkdt']);
    }

    /**
     * Test Status Priority Hierarchy: Batal > Akad AJB > Akad Indent > SP3K > Wawancara > Booking.
     */
    public function testDetailStatusPriorityHierarchy(): void
    {
        $repository = $this->createMock(BookingAkadReportRepository::class);
        $repository->method('getDetailRows')->willReturn([
            'recordsTotal' => 6,
            'recordsFiltered' => 6,
            'data' => [
                // 1. Batal overrides everything even if akad = 1
                ['id_mkdt' => 1, 'is_batal' => 1, 'akad' => 1, 'sp3k' => 1, 'booking_tgl' => '2026-01-01'],
                // 2. Akad AJB
                ['id_mkdt' => 2, 'is_batal' => 0, 'akad' => 1, 'akad_indent' => 1, 'sp3k' => 1, 'booking_tgl' => '2026-01-01'],
                // 3. Akad Indent
                ['id_mkdt' => 3, 'is_batal' => 0, 'akad' => 0, 'akad_indent' => 1, 'sp3k' => 1, 'booking_tgl' => '2026-01-01'],
                // 4. SP3K
                ['id_mkdt' => 4, 'is_batal' => 0, 'akad' => 0, 'akad_indent' => 0, 'sp3k' => 1, 'wawancara' => 1, 'booking_tgl' => '2026-01-01'],
                // 5. Wawancara
                ['id_mkdt' => 5, 'is_batal' => 0, 'akad' => 0, 'akad_indent' => 0, 'sp3k' => 0, 'wawancara' => 1, 'booking_tgl' => '2026-01-01'],
                // 6. Booking fallback
                ['id_mkdt' => 6, 'is_batal' => 0, 'akad' => 0, 'akad_indent' => 0, 'sp3k' => 0, 'wawancara' => 0, 'booking_tgl' => '2026-01-01'],
            ],
        ]);

        $service = new BookingAkadReportService($repository);
        $result = $service->getDetail(7, ['year' => 2026, 'month' => 1, 'category' => 'booking']);

        $this->assertSame('Batal', $result['data'][0]['status_terakhir']);
        $this->assertSame('danger', $result['data'][0]['status_terakhir_badge']);

        $this->assertSame('Akad AJB', $result['data'][1]['status_terakhir']);
        $this->assertSame('success', $result['data'][1]['status_terakhir_badge']);

        $this->assertSame('Akad Indent', $result['data'][2]['status_terakhir']);
        $this->assertSame('info', $result['data'][2]['status_terakhir_badge']);

        $this->assertSame('SP3K', $result['data'][3]['status_terakhir']);
        $this->assertSame('primary', $result['data'][3]['status_terakhir_badge']);

        $this->assertSame('Wawancara', $result['data'][4]['status_terakhir']);
        $this->assertSame('warning', $result['data'][4]['status_terakhir_badge']);

        $this->assertSame('Booking', $result['data'][5]['status_terakhir']);
        $this->assertSame('secondary', $result['data'][5]['status_terakhir_badge']);
    }

    /**
     * Test Catatan MKDT Fallback: keterangan_batal -> keterangan -> keterangan_status -> '-'.
     */
    public function testDetailCatatanMkdtFallbackHierarchy(): void
    {
        $repository = $this->createMock(BookingAkadReportRepository::class);
        $repository->method('getDetailRows')->willReturn([
            'recordsTotal' => 4,
            'recordsFiltered' => 4,
            'data' => [
                // 1. keterangan_batal takes precedence
                ['id_mkdt' => 1, 'keterangan_batal' => 'Batal oleh user', 'keterangan' => 'Ket 2', 'keterangan_status' => 'Ket 3'],
                // 2. keterangan when keterangan_batal is empty
                ['id_mkdt' => 2, 'keterangan_batal' => '', 'keterangan' => 'Pending bank', 'keterangan_status' => 'Ket 3'],
                // 3. keterangan_status when others empty or whitespace
                ['id_mkdt' => 3, 'keterangan_batal' => null, 'keterangan' => '   ', 'keterangan_status' => 'Status berkas siap'],
                // 4. Fallback to '-' when all empty
                ['id_mkdt' => 4, 'keterangan_batal' => null, 'keterangan' => null, 'keterangan_status' => null],
            ],
        ]);

        $service = new BookingAkadReportService($repository);
        $result = $service->getDetail(7, ['year' => 2026, 'month' => 1, 'category' => 'booking']);

        $this->assertSame('Batal oleh user', $result['data'][0]['catatan_mkdt']);
        $this->assertSame('Pending bank', $result['data'][1]['catatan_mkdt']);
        $this->assertSame('Status berkas siap', $result['data'][2]['catatan_mkdt']);
        $this->assertSame('-', $result['data'][3]['catatan_mkdt']);
    }

    /**
     * Test DataTables Parameter Normalization: paging, search trimming, and order.
     */
    public function testDetailNormalizesDataTableParameters(): void
    {
        $repository = $this->createMock(BookingAkadReportRepository::class);
        $repository->expects($this->once())
            ->method('getDetailRows')
            ->with(7, 2026, 2, 'booking', [
                'draw' => 3,
                'start' => 0,
                'length' => 50,
                'search' => 'Blok B',
                'order_by' => 'booking_akad.booking_tgl',
                'order_dir' => 'DESC',
            ])
            ->willReturn(['recordsTotal' => 0, 'recordsFiltered' => 0, 'data' => []]);

        $service = new BookingAkadReportService($repository);
        $service->getDetail(7, [
            'draw' => 3,
            'start' => -5,
            'length' => 50,
            'year' => 2026,
            'month' => 2,
            'category' => 'booking',
            'search' => ['value' => '  Blok B  '],
            'columns' => [3 => ['name' => 'tanggal_booking']],
            'order' => [['column' => 3, 'dir' => 'desc']],
        ]);
    }
}
