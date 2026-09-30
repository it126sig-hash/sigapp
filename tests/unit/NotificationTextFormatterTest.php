<?php

use App\Support\NotificationTextFormatter;
use CodeIgniter\Test\CIUnitTestCase;

final class NotificationTextFormatterTest extends CIUnitTestCase
{
    public function testPlainTextIsUnchanged(): void
    {
        $this->assertSame('Booking kavling berhasil', NotificationTextFormatter::plain('Booking kavling berhasil'));
    }

    public function testHtmlEntitiesTagsAndWhitespaceAreNormalized(): void
    {
        $value = " Status &amp; <strong>aman</strong><br>\n  untuk <div>semua</div> ";

        $this->assertSame('Status & aman untuk semua', NotificationTextFormatter::plain($value));
    }

    public function testEmptyContentUsesRequestedFallback(): void
    {
        $this->assertSame('-', NotificationTextFormatter::plain('<br>'));
        $this->assertSame('Ada notifikasi baru', NotificationTextFormatter::plain('   ', 'Ada notifikasi baru'));
    }

    public function testKavlingLocationIsPrependedWhenAvailable(): void
    {
        $this->assertSame(
            'Mawar No. A-12 - Status & aman',
            NotificationTextFormatter::withKavling('Status &amp; <strong>aman</strong>', 'Mawar', 'A-12')
        );
    }

    public function testKavlingLocationFallsBackToMessageWhenUnavailable(): void
    {
        $this->assertSame('Status aman', NotificationTextFormatter::withKavling('Status aman'));
    }

    public function testKavlingLocationIsNotDuplicated(): void
    {
        $this->assertSame(
            'Mawar No A-12 sudah diperbarui',
            NotificationTextFormatter::withKavling('Mawar No A-12 sudah diperbarui', 'Mawar', 'A-12')
        );
    }
}
