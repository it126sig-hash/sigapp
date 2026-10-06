<?php

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class DireksiTagihanViewTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        helper(['auth', 'url']);
    }

    public function testViewDirectlyRendersWithoutViewException(): void
    {
        // View direksi/list-tagihan-pending should render cleanly without ViewException
        $output = view('direksi/list-tagihan-pending', [
            'title' => 'Persetujuan Surat Tagihan'
        ]);

        $this->assertNotEmpty($output);
        $this->assertStringContainsString('id="table-pending-tagihan"', $output);
        $this->assertStringContainsString('Daftar Surat Tagihan Menunggu Persetujuan', $output);
        $this->assertStringNotContainsString('layouts/master', $output);
    }
}
