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
}
