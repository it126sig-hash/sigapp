<?php

use App\Services\GoogleCalendarService;
use CodeIgniter\Test\CIUnitTestCase;

final class GoogleCalendarServiceTest extends CIUnitTestCase
{
    public function testBuildsKavlingUrgentTicketSummary(): void
    {
        $service = new TestableGoogleCalendarService();

        $summary = $service->summary((object) [
            'ref_type' => 'kavling',
            'kavling_nama_jalan' => 'Mawar',
            'kavling_no_kavling' => 'A-12',
            'kavling_tipe_rumah' => '36',
            'keterangan' => 'Jalan retak di depan rumah',
        ]);

        $this->assertSame('[SIGAPP] Mawar No A-12/36: Jalan retak di depan rumah', $summary);
    }

    public function testBuildsOthersUrgentTicketSummary(): void
    {
        $service = new TestableGoogleCalendarService();

        $summary = $service->summary((object) [
            'ref_type' => 'others',
            'others_nama_jalan' => 'Boulevard',
            'others_tipe' => 'rth',
            'others_nama' => 'Taman Tengah',
            'keterangan' => 'Rumput rusak dan perlu dicek',
        ]);

        $this->assertSame('[SIGAPP] Boulevard/RTH Taman Tengah: Rumput rusak dan perlu dicek', $summary);
    }

    public function testSummaryNormalizesHtmlEntitiesAndWhitespace(): void
    {
        $service = new TestableGoogleCalendarService();

        $summary = $service->summary((object) [
            'ref_type' => 'kavling',
            'kavling_nama_jalan' => ' Anggrek &amp; Melati ',
            'kavling_no_kavling' => '<strong>B-08</strong>',
            'kavling_tipe_rumah' => "45\nPremium",
            'keterangan' => " Retak &amp; bocor<br> perlu <b>cek</b> ",
        ]);

        $this->assertSame('[SIGAPP] Anggrek & Melati No B-08/45 Premium: Retak & bocor perlu cek', $summary);
    }

    public function testSummaryUsesCleanFallbackWhenLocationFieldsAreMissing(): void
    {
        $service = new TestableGoogleCalendarService();

        $summary = $service->summary((object) [
            'ref_type' => 'kavling',
            'lokasi' => '  Area <b>sementara</b>  ',
            'nama_proyek' => 'Proyek A',
            'keterangan' => '',
        ]);

        $this->assertSame('[SIGAPP] Area sementara', $summary);
    }
}

final class TestableGoogleCalendarService extends GoogleCalendarService
{
    public function __construct()
    {
    }

    public function summary(object $ticket): string
    {
        return $this->buildEventSummary($ticket);
    }
}
