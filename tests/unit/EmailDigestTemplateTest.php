<?php

use App\Support\NotificationTextFormatter;
use CodeIgniter\Test\CIUnitTestCase;

final class EmailDigestTemplateTest extends CIUnitTestCase
{
    public function testTemplateShowsNormalizedMessageWithoutLabel(): void
    {
        $item = (object) [
            'notif' => 'Status &amp; <script>alert("x")</script><strong>aman</strong>',
            'notif_text' => NotificationTextFormatter::plain('Status &amp; <script>alert("x")</script><strong>aman</strong>'),
            'notif_date' => '2026-09-02 10:00:00',
            'actor_name' => 'User Uji',
            'actor_username' => 'tester',
            'departemen_desc' => 'Pengujian',
            'departemen_name' => 'QA',
            'no_kavling' => 'A-01',
        ];

        $html = view('emails/email_digest', [
            'user' => (object) ['username' => 'penerima'],
            'items' => ['Proyek Uji' => [$item]],
        ]);

        $this->assertStringNotContainsString('Isi Notifikasi', $html);
        $this->assertStringContainsString('Status &amp; alert(&quot;x&quot;)aman', $html);
        $this->assertStringNotContainsString('<script>', $html);
    }
}
