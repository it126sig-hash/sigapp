<?php

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class DireksiTagihanSignTest extends CIUnitTestCase
{
    public function testViewContainsSignModalAndCanvas(): void
    {
        $output = view('direksi/list-tagihan-pending', [
            'title' => 'Persetujuan Surat Tagihan'
        ]);

        $this->assertStringContainsString('id="modalSignTagihan"', $output);
        $this->assertStringContainsString('id="sign_canvas"', $output);
        $this->assertStringContainsString('id="sign_password"', $output);
        $this->assertStringContainsString('id="mdl_nominal"', $output);
        $this->assertStringContainsString('id="mdl_pembuat"', $output);
        $this->assertStringContainsString('detail-surat-card', $output);
        $this->assertStringContainsString('@media (max-width: 767.98px)', $output);
    }

    public function testViewContainsRejectModalAndHistoryTab(): void
    {
        $output = view('direksi/list-tagihan-pending', [
            'title' => 'Persetujuan Surat Tagihan'
        ]);

        // Tab Navigation
        $this->assertStringContainsString('id="tagihanTab"', $output);
        $this->assertStringContainsString('id="tab-pending-btn"', $output);
        $this->assertStringContainsString('id="tab-history-btn"', $output);
        $this->assertStringContainsString('id="pane-pending"', $output);
        $this->assertStringContainsString('id="pane-history"', $output);

        // DataTables
        $this->assertStringContainsString('id="table-pending-tagihan"', $output);
        $this->assertStringContainsString('id="table-history-tagihan"', $output);

        // Reject Modal
        $this->assertStringContainsString('id="modalRejectTagihan"', $output);
        $this->assertStringContainsString('id="reject_alasan"', $output);
        $this->assertStringContainsString('id="reject_password"', $output);
        $this->assertStringContainsString('id="btn_submit_reject"', $output);
        $this->assertStringContainsString('id="btn_open_reject_from_sign"', $output);

        // AJAX endpoints
        $this->assertStringContainsString('direksi/get_pending_tagihan', $output);
        $this->assertStringContainsString('direksi/get_history_tagihan', $output);
        $this->assertStringContainsString('direksi/sign_tagihan', $output);
        $this->assertStringContainsString('direksi/reject_tagihan', $output);
    }
}
