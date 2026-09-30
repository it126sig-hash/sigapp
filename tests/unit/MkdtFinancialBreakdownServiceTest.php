<?php

use App\Services\MkdtFinancialBreakdownService;
use CodeIgniter\Test\CIUnitTestCase;

final class MkdtFinancialBreakdownServiceTest extends CIUnitTestCase
{
    private MkdtFinancialBreakdownService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new MkdtFinancialBreakdownService();
    }

    public function testTargetsSeparateUmAdmAndOtherCosts(): void
    {
        $targets = $this->service->targets([
            'harga_uang_muka' => 10_000_000,
            'harga_diskon_uang_muka' => 1_000_000,
            'harga_sbum' => 2_000_000,
            'harga_administrasi' => 500_000,
            'harga_bphtb' => 1_000_000,
            'harga_biaya_proses' => 2_000_000,
            'harga_ppn' => 3_000_000,
            'harga_penambahan' => 4_000_000,
            'harga_penambahan_tanah' => 5_000_000,
            'harga_kpr' => 100_000_000,
            'harga_kpr_acc' => 90_000_000,
        ]);

        $this->assertSame(7_000_000.0, $targets['um']);
        $this->assertSame(500_000.0, $targets['adm']);
        $this->assertSame(25_000_000.0, $targets['bb']);
        $this->assertArrayNotHasKey('Booking Fee', $targets['components']);
    }

    public function testUmNeverBecomesNegative(): void
    {
        $targets = $this->service->targets([
            'harga_uang_muka' => 1_000_000,
            'harga_diskon_uang_muka' => 750_000,
            'harga_sbum' => 500_000,
        ]);
        $this->assertSame(0.0, $targets['um']);
    }

    public function testTurunKprIsZeroWhenAccIsZero(): void
    {
        $this->assertSame(0.0, $this->service->validTurunKpr([
            'harga_kpr' => 159_500_000,
            'harga_kpr_acc' => 0,
        ]));
        $this->assertSame(9_500_000.0, $this->service->validTurunKpr([
            'harga_kpr' => 159_500_000,
            'harga_kpr_acc' => 150_000_000,
        ]));
    }

    public function testAllInUsesAllInAmountWhenComponentsDiffer(): void
    {
        $this->service->validateContractAndSchedule([
            'is_allin' => 1,
            'harga_allin' => 15_000_000,
            'harga_uang_muka' => 10_000_000,
        ], [
            'nominal' => ['5,000,000', '10,000,000'],
        ]);

        $this->addToAssertionCount(1);
    }

    public function testAllInRejectsScheduleThatDoesNotMatchAllInAmount(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('harga all in');

        $this->service->validateContractAndSchedule([
            'is_allin' => 1,
            'harga_allin' => 15_000_000,
            'harga_uang_muka' => 10_000_000,
        ], [
            'nominal' => [10_000_000],
        ]);
    }

    public function testNonAllInRejectsScheduleThatDoesNotMatchComponents(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('total komponen efektif MKDT');

        $this->service->validateContractAndSchedule([
            'is_allin' => 0,
            'harga_uang_muka' => 10_000_000,
        ], [
            'nominal' => [9_000_000],
        ]);
    }

    public function testBookingFeeIsExcludedFromScheduleTarget(): void
    {
        $this->service->validateContractAndSchedule([
            'is_allin' => 0,
            'harga_uang_muka' => 10_000_000,
            'booking_fee' => 2_000_000,
        ], [
            'nominal' => [10_000_000],
        ]);

        $this->addToAssertionCount(1);
    }

    public function testZeroTargetAndZeroPaymentIsZeroPercent(): void
    {
        $this->assertSame(
            ['label' => '0%', 'status' => 'normal', 'percentage' => 0],
            $this->service->progress(0, 0)
        );
    }

    public function testPaymentWithoutTargetNeedsReconciliation(): void
    {
        $this->assertSame('reconcile', $this->service->progress(0, 100_000)['status']);
    }

    public function testPercentageAboveOneHundredIsNotCapped(): void
    {
        $progress = $this->service->progress(1_000_000, 1_250_000);
        $this->assertSame('125%', $progress['label']);
        $this->assertSame('overpaid', $progress['status']);
    }
}
