<?php

namespace Tests\Unit;

use App\Repositories\SalesExternalRepository;
use App\Services\SalesExternalService;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class FeeSalesWorkflowTest extends CIUnitTestCase
{
    public function testMainFeeSalesViewContainsRequiredElements(): void
    {
        $output = view('sales/fee_sales', [
            'title' => 'Fee Sales External',
            'permissions' => [
                'can_manage' => true,
                'can_cairkan' => true,
                'can_sign_mkdt' => true,
                'can_sign_keuangan' => true,
            ],
            'filters' => [
                'proyek' => [],
                'sales' => [],
                'status' => [],
            ],
        ]);

        $this->assertStringContainsString('id="pane-fee-group"', $output);
        $this->assertStringContainsString('id="table-pengajuan-group"', $output);
        $this->assertStringContainsString('Tanggal & Pembuat', $output);
        $this->assertStringContainsString('Sales & Tgl Sales', $output);
        $this->assertStringNotContainsString('<th>Pembuat</th>', $output);
        $this->assertStringContainsString('id="modal-create-group"', $output);
        $this->assertStringContainsString('id="batch-tgl_pengajuan_sales"', $output);
        $this->assertStringContainsString('id="batch-tgl_pengajuan"', $output);
        $this->assertStringContainsString('id="modal-group-detail"', $output);
        $this->assertStringContainsString('id="detail-group-tanggal-sales"', $output);
        $this->assertStringContainsString('id="detail-group-void-alert"', $output);
        $this->assertStringContainsString('id="btn-sign-mkdt-detail"', $output);
        $this->assertStringContainsString('id="btn-sign-keuangan-detail"', $output);
        $this->assertStringContainsString('id="btn-void-group-detail"', $output);
        $this->assertStringContainsString('id="btn-export-group-pdf"', $output);
        $this->assertStringContainsString('id="btn-share-group-detail"', $output);
    }

    public function testSharedPengajuanViewRendersActivePengajuan(): void
    {
        $group = (object) [
            'id' => 10,
            'nomor_pengajuan' => 'PGJ/FEE/KEU-PST/20261022/ABCD',
            'tanggal_pengajuan' => '2026-10-22',
            'status' => 'diajukan',
            'share_token' => bin2hex(random_bytes(32)),
            'total_nominal' => 5000000,
            'total_unit' => 1,
            'creator_name' => 'Staf Marketing',
            'ttd_pembuat_nama' => 'Staf Marketing',
            'ttd_pembuat_at' => '2026-10-22 10:00:00',
            'ttd_pembuat_path' => null,
            'ttd_mkdt_by' => null,
            'ttd_mkdt_id' => null,
            'ttd_mkdt_nama' => null,
            'ttd_mkdt_at' => null,
            'ttd_mkdt_path' => null,
            'ttd_keuangan_by' => null,
            'ttd_keuangan_id' => null,
            'ttd_keuangan_nama' => null,
            'ttd_keuangan_at' => null,
            'ttd_keuangan_path' => null,
            'void_reason' => null,
            'voided_by_name' => null,
            'voided_at' => null,
            'items' => [
                (object) [
                    'id_termin' => 1,
                    'nama_konsumen' => 'Budi Santoso',
                    'nama_jalan' => 'Jl. Mawar',
                    'no_kavling' => 'A-01',
                    'sales_nama' => 'Sales Satu',
                    'sales_has_pks' => 1,
                    'harga_net_ajb' => 500000000,
                    'harga_jual_net' => 500000000,
                    'harga_jual' => 520000000,
                    'total_fee' => 10000000,
                    'termin_yang_diajukan' => 1,
                    'termin_persen' => 50,
                    'nominal_diajukan' => 5000000,
                    'termin_status' => 'pengajuan_spp',
                    'termin_tgl_cair' => null,
                    'termin_no_cek' => null,
                ]
            ],
        ];

        $output = view('sales/pengajuan_fee_shared', [
            'group' => $group,
            'permissions' => [
                'is_logged_in' => true,
                'can_sign_mkdt' => true,
                'can_sign_keuangan' => true,
            ],
        ]);

        $this->assertStringContainsString('PGJ/FEE/KEU-PST/20261022/ABCD', $output);
        $this->assertStringContainsString('Budi Santoso', $output);
        $this->assertStringContainsString('Sales Satu', $output);
        $this->assertStringContainsString('Rp 500.000.000', $output);
        $this->assertStringContainsString('Rp 5.000.000', $output);
        $this->assertStringContainsString('TTD MKDT', $output);
        $this->assertStringContainsString('TTD Keuangan', $output);
        $this->assertStringContainsString('class="d-block d-md-none" style="height: 90px;"', $output);
        $this->assertStringNotContainsString('Pengajuan ini telah DIBATALKAN / VOID', $output);

        // Verify jQuery (vendors.min.js) and SweetAlert2 are loaded before any inline jQuery execution
        $this->assertStringContainsString('app-assets/vendors/js/vendors.min.js', $output);
        $this->assertStringContainsString('app-assets/vendors/js/extensions/sweetalert2.all.min.js', $output);
        $vendorsPos = strpos($output, 'app-assets/vendors/js/vendors.min.js');
        $swalPos = strpos($output, 'app-assets/vendors/js/extensions/sweetalert2.all.min.js');
        $inlineJqueryPos = strpos($output, '$(function');
        $this->assertNotFalse($vendorsPos);
        $this->assertNotFalse($swalPos);
        $this->assertNotFalse($inlineJqueryPos);
        $this->assertLessThan($swalPos, $vendorsPos, 'vendors.min.js must be loaded before sweetalert2');
        $this->assertLessThan($inlineJqueryPos, $vendorsPos, 'vendors.min.js must be loaded before inline $(function');
    }

    public function testSharedPengajuanViewRendersVoidState(): void
    {
        $group = (object) [
            'id' => 11,
            'nomor_pengajuan' => 'PGJ/FEE/KEU-PST/20261022/DEAD',
            'tanggal_pengajuan' => '2026-10-22',
            'status' => 'void',
            'share_token' => bin2hex(random_bytes(32)),
            'total_nominal' => 5000000,
            'total_unit' => 1,
            'creator_name' => 'Staf Marketing',
            'ttd_pembuat_nama' => 'Staf Marketing',
            'ttd_pembuat_at' => '2026-10-22 10:00:00',
            'ttd_pembuat_path' => null,
            'ttd_mkdt_by' => null,
            'ttd_mkdt_id' => null,
            'ttd_mkdt_nama' => null,
            'ttd_mkdt_at' => null,
            'ttd_mkdt_path' => null,
            'ttd_keuangan_by' => null,
            'ttd_keuangan_id' => null,
            'ttd_keuangan_nama' => null,
            'ttd_keuangan_at' => null,
            'ttd_keuangan_path' => null,
            'void_reason' => 'Salah input nominal termin konsumen',
            'voided_by_name' => 'Supervisor Keuangan',
            'voided_at' => '2026-10-22 11:30:00',
            'items' => [],
        ];

        $output = view('sales/pengajuan_fee_shared', [
            'group' => $group,
            'permissions' => [
                'is_logged_in' => true,
                'can_sign_mkdt' => true,
                'can_sign_keuangan' => true,
            ],
        ]);

        $this->assertStringContainsString('Void / Dibatalkan', $output);
        $this->assertStringContainsString('Dibatalkan oleh', $output);
        $this->assertStringContainsString('Salah input nominal termin konsumen', $output);
        $this->assertStringContainsString('Supervisor Keuangan', $output);
        // Signature buttons should NOT appear when void
        $this->assertStringNotContainsString('btn-trigger-sign ml-25', $output);
    }

    public function testPdfPengajuanViewRendersHeaderAndSignatures(): void
    {
        $group = (object) [
            'id' => 12,
            'nomor_pengajuan' => 'PGJ/FEE/KEU-PST/20261022/9999',
            'tanggal_pengajuan' => '2026-10-22',
            'status' => 'diajukan',
            'creator_username' => 'staf1',
        ];

        $item1 = (object) [
            'id_mkdt' => 101,
            'nama_konsumen' => 'Konsumen Demo 1',
            'nama_jalan' => 'Boulevard',
            'no_kavling' => 'B-02',
            'sales_nama' => 'Agent Pro',
            'sales_has_pks' => 1,
            'tipe_nama' => 'Type 45',
            'harga_jual' => 450000000,
            'harga_diskon_hargajual' => 0,
            'is_ajb' => 'PPJB',
            'total_fee' => 9000000,
            'termin_yang_diajukan' => 1,
            'persen' => 30,
            'nominal_diajukan' => 2700000,
            'tgl_booking' => '2026-10-01',
            'akad_tgl' => '2026-10-25',
            'skema_termin' => '30_60_10',
            'all_termin' => [
                1 => (object) ['nominal' => 2700000, 'status' => 'pengajuan_spp', 'no_cek' => null, 'tgl_cair' => null],
                2 => (object) ['nominal' => 5400000, 'status' => 'belum_diajukan', 'no_cek' => null, 'tgl_cair' => null],
                3 => (object) ['nominal' => 900000, 'status' => 'belum_diajukan', 'no_cek' => null, 'tgl_cair' => null],
            ],
        ];

        $item2 = (object) [
            'id_mkdt' => 102,
            'nama_konsumen' => 'Konsumen Demo 2',
            'nama_jalan' => 'Boulevard',
            'no_kavling' => 'B-03',
            'sales_nama' => 'Agent Pro',
            'sales_has_pks' => 1,
            'tipe_nama' => 'Type 45',
            'harga_jual' => 450000000,
            'harga_diskon_hargajual' => 0,
            'is_ajb' => 'PPJB',
            'total_fee' => 9000000,
            'termin_yang_diajukan' => 1,
            'persen' => 30,
            'nominal_diajukan' => 2700000,
            'tgl_booking' => '2026-10-01',
            'akad_tgl' => '2026-10-10',
            'skema_termin' => '30_60_10',
            'all_termin' => [
                1 => (object) ['nominal' => 2700000, 'status' => 'pengajuan_spp', 'no_cek' => null, 'tgl_cair' => null],
            ],
        ];

        $item3 = (object) [
            'id_mkdt' => 103,
            'nama_konsumen' => 'Konsumen Demo 3 Null Akad',
            'nama_jalan' => 'Melati',
            'no_kavling' => 'C-01',
            'sales_nama' => 'Agent Pro',
            'sales_has_pks' => 1,
            'tipe_nama' => 'Type 36',
            'harga_jual' => 300000000,
            'harga_diskon_hargajual' => 0,
            'is_ajb' => 'PPJB',
            'total_fee' => 6000000,
            'termin_yang_diajukan' => 1,
            'persen' => 30,
            'nominal_diajukan' => 1800000,
            'tgl_booking' => '2026-10-01',
            'akad_tgl' => null,
            'skema_termin' => '30_60_10',
            'all_termin' => [
                1 => (object) ['nominal' => 1800000, 'status' => 'pengajuan_spp', 'no_cek' => null, 'tgl_cair' => null],
            ],
        ];

        $salesGroups = [
            'Agent Pro' => [$item1, $item2, $item3],
        ];

        $signatures = [
            'pembuat' => ['nama' => 'Staf Input', 'at' => '2026-10-22 09:00:00', 'data' => ''],
            'mkdt' => ['nama' => 'Manager MKDT', 'at' => '2026-10-22 10:00:00', 'data' => ''],
            'keuangan' => ['nama' => '', 'at' => null, 'data' => ''],
        ];

        $output = view('sales/pdf_pengajuan_fee', [
            'group' => $group,
            'salesGroups' => $salesGroups,
            'signatures' => $signatures,
        ]);

        $this->assertStringContainsString('No: PGJ/FEE/KEU-PST/20261022/9999', $output);
        $this->assertStringContainsString('SALES: AGENT PRO', $output);
        $this->assertStringNotContainsString('PKS (KONTRAK)', $output);
        $this->assertStringNotContainsString('NON-PKS (REGULER)', $output);
        $this->assertStringContainsString('font-size: 5.8pt;', $output);
        $this->assertStringContainsString('font-size: 5.5pt;', $output);
        $this->assertStringContainsString('font-size: 4.8pt;', $output);
        $this->assertStringContainsString('font-size: 9pt;', $output);
        $this->assertStringContainsString('Konsumen Demo 1', $output);
        $this->assertStringContainsString('Konsumen Demo 2', $output);
        $this->assertStringContainsString('Konsumen Demo 3 Null Akad', $output);
        $posItem2 = strpos($output, 'Konsumen Demo 2');
        $posItem1 = strpos($output, 'Konsumen Demo 1');
        $posItem3 = strpos($output, 'Konsumen Demo 3 Null Akad');
        $this->assertLessThan($posItem1, $posItem2, 'Items must be sorted by akad_tgl ASC in PDF');
        $this->assertLessThan($posItem3, $posItem1, 'Items with null akad_tgl must sort to the end');
        $this->assertStringContainsString('Staf Input', $output);
        $this->assertStringContainsString('Manager MKDT', $output);
        $this->assertStringContainsString('(Belum Ditandatangani)', $output);
    }

    public function testNomorPengajuanRandomPattern(): void
    {
        $dateStr = date('Ymd');
        $kode = 'KEU-TEST';
        $randHex = sprintf('%04X', random_int(0, 0xFFFF));
        $nomor = "PGJ/FEE/{$kode}/{$dateStr}/{$randHex}";

        $this->assertMatchesRegularExpression('/^PGJ\/FEE\/[A-Z0-9_-]+\/\d{8}\/[0-9A-F]{4}$/', $nomor);
    }

    public function testServiceCreatePengajuanValidationThrowsOnMissingSalesDate(): void
    {
        $service = new SalesExternalService();
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Tanggal pengajuan dari sales wajib diisi.');
        $service->createPengajuanGroup([
            'tanggal_pengajuan_sales' => '',
            'tanggal_pengajuan' => '2026-10-22',
            'signature_data' => 'dummy',
            'items' => [['id_mkdt' => 1]],
        ], 1);
    }

    public function testServiceCreatePengajuanValidationThrowsOnMissingDate(): void
    {
        $service = new SalesExternalService();
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Tanggal pengajuan pencairan wajib diisi.');
        $service->createPengajuanGroup([
            'tanggal_pengajuan_sales' => '2026-10-21',
            'tanggal_pengajuan' => '',
            'signature_data' => 'dummy',
            'items' => [['id_mkdt' => 1]],
        ], 1);
    }

    public function testServiceCreatePengajuanValidationThrowsOnMissingSignature(): void
    {
        $service = new SalesExternalService();
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Tanda tangan pengajuan wajib diisi.');
        $service->createPengajuanGroup([
            'tanggal_pengajuan_sales' => '2026-10-21',
            'tanggal_pengajuan' => '2026-10-22',
            'signature_data' => '',
            'items' => [['id_mkdt' => 1]],
        ], 1);
    }

    public function testServiceCreatePengajuanValidationThrowsOnEmptyItems(): void
    {
        $service = new SalesExternalService();
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Pilih setidaknya 1 kavling untuk diajukan.');
        $service->createPengajuanGroup([
            'tanggal_pengajuan_sales' => '2026-10-21',
            'tanggal_pengajuan' => '2026-10-22',
            'signature_data' => 'dummy',
            'items' => [],
        ], 1);
    }

    public function testFeeSalesJsHasNoManageButtonAndHasCompactTtd(): void
    {
        $js = file_get_contents(FCPATH . 'assets/js/sales/fee_sales.js');
        preg_match('/function renderGroupItemsTable\(items\)\s*\{(.*?)\n    \}/s', $js, $matches);
        $this->assertNotEmpty($matches, 'renderGroupItemsTable function must be present');
        $this->assertStringNotContainsString('btn-manage-fee', $matches[1], 'btn-manage-fee must be removed from renderGroupItemsTable');
        $this->assertStringContainsString('py-0 px-50 font-small-1 btn-quick-sign', $js, 'Compact TTD button styling must be present');
    }

    public function testServiceVoidThrowsOnEmptyReason(): void
    {
        $service = new SalesExternalService();
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Alasan void wajib diisi.');
        $service->voidPengajuanGroup(1, '   ', 1);
    }

    public function testServiceSignThrowsOnInvalidRole(): void
    {
        $service = new SalesExternalService();
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Role penanda tangan tidak valid.');
        $service->signPengajuanGroup(1, 'admin', ['password' => 'secret123'], 1);
    }

    public function testSharedPengajuanViewWithExactRepositoryStructure(): void
    {
        $group = (object) [
            'id' => 15,
            'nomor_pengajuan' => 'PGJ/FEE/KEU-PST/20261022/E7A1',
            'tanggal_pengajuan' => '2026-10-22',
            'catatan' => 'Pengajuan termin 1',
            'status' => 'diajukan',
            'share_token' => '4dfe0064a3239652bd3d49c02655897a',
            'creator_username' => 'staf_admin',
            'nama_proyek' => 'Grand Permata',
            'kode_keuangan' => 'KEU-PST',
            'voided_by_name' => null,
            'voided_at' => null,
            'void_reason' => null,
            'ttd_pembuat_by' => 5,
            'ttd_pembuat_nama' => 'Staf Input',
            'ttd_pembuat_path' => null,
            'ttd_pembuat_at' => '2026-10-22 09:30:00',
            'ttd_mkdt_by' => null,
            'ttd_mkdt_nama' => null,
            'ttd_mkdt_path' => null,
            'ttd_mkdt_at' => null,
            'ttd_keuangan_by' => null,
            'ttd_keuangan_nama' => null,
            'ttd_keuangan_path' => null,
            'ttd_keuangan_at' => null,
            'total_nominal' => 7500000,
            'total_unit' => 1,
            'items' => [
                (object) [
                    'id_pengajuan_item' => 1,
                    'id_pengajuan' => 15,
                    'id_fee_sales' => 10,
                    'id_termin' => 20,
                    'termin_yang_diajukan' => 1,
                    'nominal_diajukan' => 7500000,
                    'termin_persen' => 50,
                    'termin_nominal' => 7500000,
                    'termin_status' => 'pengajuan_spp',
                    'termin_nomor_spp' => 'SPP-001',
                    'termin_tgl_spp' => '2026-10-22',
                    'termin_tgl_cair' => null,
                    'termin_no_cek' => null,
                    'termin_keterangan' => null,
                    'termin_lampiran_spp' => null,
                    'termin_lampiran_bukti_cair' => null,
                    'id_mkdt' => 101,
                    'id_sales' => 2,
                    'sales_nama' => 'Rina Agen',
                    'sales_keterangan' => null,
                    'harga_net_ajb' => 750000000,
                    'fee_sales_harga_net_ajb' => 750000000,
                    'fee_harga_net_ajb' => 750000000,
                    'persentase_fee' => 2.0,
                    'total_fee' => 15000000,
                    'skema_termin' => '50_50',
                    'fee_status' => 'aktif',
                    'is_kpr' => 1,
                    'is_ajb' => 'PPJB',
                    'is_subsidi' => 0,
                    'akad_indent' => null,
                    'booking_tgl' => '2026-10-01',
                    'akad_tgl' => null,
                    'harga_jual' => 800000000,
                    'harga_jual_net' => 750000000,
                    'harga_diskon_hargajual' => 50000000,
                    'harga_penambahan_tanah' => 0,
                    'nama_konsumen' => 'Hendra Setiawan',
                    'hp_konsumen' => '081234567890',
                    'nama_instansi' => 'PT Jaya',
                    'no_kavling' => 'C-05',
                    'nama_jalan' => 'Boulevard Barat',
                    'tipe_rumah' => 'Type 60',
                    'sales_bank' => 'BCA',
                    'sales_no_rek' => '123456789',
                    'sales_has_pks' => 1,
                ],
            ],
        ];

        $output = view('sales/pengajuan_fee_shared', [
            'group' => $group,
            'permissions' => [
                'is_logged_in' => true,
                'can_sign_mkdt' => true,
                'can_sign_keuangan' => false,
            ],
        ]);

        $this->assertStringContainsString('PGJ/FEE/KEU-PST/20261022/E7A1', $output);
        $this->assertStringContainsString('Hendra Setiawan', $output);
        $this->assertStringContainsString('Rina Agen', $output);
        $this->assertStringContainsString('Rp 750.000.000', $output);
        $this->assertStringContainsString('Rp 7.500.000', $output);
        $this->assertStringContainsString('PKS', $output);
        $this->assertStringContainsString('Termin 1 (50%)', $output);
    }

    public function testSharedPengajuanViewWithFeeSalesHargaNetAjbProperty(): void
    {
        $group = (object) [
            'id' => 16,
            'nomor_pengajuan' => 'PGJ/FEE/KEU-PST/20261022/TEST',
            'tanggal_pengajuan' => '2026-10-22',
            'status' => 'diajukan',
            'share_token' => '4dfe0064a3239652bd3d49c02655897a',
            'total_nominal' => 3000000,
            'total_unit' => 1,
            'creator_username' => 'marketing1',
            'items' => [
                (object) [
                    'nama_konsumen' => 'Dewi Sartika',
                    'nama_jalan' => 'Jl. Kenanga',
                    'no_kavling' => 'K-12',
                    'sales_nama' => 'Sales Lepas',
                    'sales_has_pks' => 0,
                    // harga_net_ajb is omitted, only fee_sales_harga_net_ajb is provided
                    'fee_sales_harga_net_ajb' => 600000000,
                    'total_fee' => 6000000,
                    'termin_yang_diajukan' => 1,
                    'termin_persen' => 50,
                    'nominal_diajukan' => 3000000,
                    'termin_status' => 'pengajuan_spp',
                ],
            ],
        ];

        $output = view('sales/pengajuan_fee_shared', [
            'group' => $group,
            'permissions' => ['can_sign_mkdt' => false, 'can_sign_keuangan' => false],
        ]);

        $this->assertStringContainsString('Dewi Sartika', $output);
        $this->assertStringContainsString('Rp 600.000.000', $output);
        $this->assertStringContainsString('Non-PKS', $output);
    }

    public function testSharedPengajuanViewWithMinimalOrMissingProperties(): void
    {
        // Object where most optional properties are missing entirely
        $group = (object) [
            'id' => 17,
            'items' => [
                (object) [
                    // empty item with zero properties defined
                ],
            ],
        ];

        $output = view('sales/pengajuan_fee_shared', [
            'group' => $group,
            'permissions' => [],
        ]);

        $this->assertStringContainsString('#17', $output);
        $this->assertStringContainsString('Rp 0', $output);
        $this->assertStringContainsString('Semua Proyek', $output);
    }

    public function testSharedPengajuanViewRendersCairItemsWithCekAndDate(): void
    {
        $group = (object) [
            'id' => 18,
            'nomor_pengajuan' => 'PGJ/FEE/KEU-PST/20261022/CAIR',
            'tanggal_pengajuan' => '2026-10-22',
            'status' => 'selesai',
            'share_token' => 'cair_token_123',
            'total_nominal' => 4000000,
            'total_unit' => 1,
            'creator_username' => 'staf_finance',
            'items' => [
                (object) [
                    'nama_konsumen' => 'Agus Priyono',
                    'nama_jalan' => 'Jl. Flamboyan',
                    'no_kavling' => 'F-03',
                    'sales_nama' => 'Sales Pro',
                    'sales_has_pks' => 1,
                    'harga_net_ajb' => 400000000,
                    'total_fee' => 8000000,
                    'termin_yang_diajukan' => 2,
                    'termin_persen' => 50,
                    'nominal_diajukan' => 4000000,
                    'termin_status' => 'sudah_cair',
                    'termin_no_cek' => 'CEK-889977',
                    'termin_tgl_cair' => '2026-10-25',
                ],
            ],
        ];

        $output = view('sales/pengajuan_fee_shared', [
            'group' => $group,
            'permissions' => [],
        ]);

        $this->assertStringContainsString('Cek: CEK-889977', $output);
        $this->assertStringContainsString('25/10/2026', $output);
        $this->assertStringContainsString('Cair', $output);
    }

    public function testSharedPengajuanViewWithOnlyHargaJualNetFallback(): void
    {
        $group = (object) [
            'id' => 19,
            'nomor_pengajuan' => 'PGJ/FEE/KEU-PST/20261022/NET1',
            'status' => 'cair_sebagian',
            'items' => [
                (object) [
                    'nama_konsumen' => 'Siti Aminah',
                    'harga_jual_net' => 450000000,
                    'termin_yang_diajukan' => '',
                    'termin_persen' => 30,
                    'nominal_diajukan' => 1350000,
                ],
            ],
        ];

        $output = view('sales/pengajuan_fee_shared', [
            'group' => $group,
            'permissions' => [],
        ]);

        $this->assertStringContainsString('Cair Sebagian', $output);
        $this->assertStringContainsString('Rp 450.000.000', $output);
        $this->assertStringContainsString('Termin - (30%)', $output);
    }

    public function testSharedPengajuanViewWithOnlyHargaJualFallback(): void
    {
        $group = (object) [
            'id' => 20,
            'status' => 'diajukan',
            'items' => [
                (object) [
                    'nama_konsumen' => 'Rudi Hartono',
                    'harga_jual' => 380000000,
                    'termin_yang_diajukan' => 1,
                    'termin_persen' => 100,
                    'nominal_diajukan' => 3800000,
                ],
            ],
        ];

        $output = view('sales/pengajuan_fee_shared', [
            'group' => $group,
            'permissions' => [],
        ]);

        $this->assertStringContainsString('Rp 380.000.000', $output);
        $this->assertStringContainsString('Termin 1 (100%)', $output);
    }

    public function testSharedPengajuanViewWithFullSignatures(): void
    {
        $group = (object) [
            'id' => 21,
            'nomor_pengajuan' => 'PGJ/FEE/KEU-PST/20261022/SIGN',
            'status' => 'diajukan',
            'ttd_pembuat_nama' => 'Staff Admin',
            'ttd_pembuat_path' => 'fee_sales/ttd_pembuat.png',
            'ttd_pembuat_at' => '2026-10-22 09:00:00',
            'ttd_mkdt_by' => 12,
            'ttd_mkdt_nama' => 'Manager MKDT',
            'ttd_mkdt_path' => 'fee_sales/ttd_mkdt.png',
            'ttd_mkdt_at' => '2026-10-22 10:00:00',
            'ttd_keuangan_by' => 15,
            'ttd_keuangan_nama' => 'Manager Keuangan',
            'ttd_keuangan_path' => 'fee_sales/ttd_keuangan.png',
            'ttd_keuangan_at' => '2026-10-22 11:00:00',
            'items' => [],
        ];

        $output = view('sales/pengajuan_fee_shared', [
            'group' => $group,
            'permissions' => [
                'can_sign_mkdt' => 1,
                'can_sign_keuangan' => 1,
            ],
        ]);

        $this->assertStringContainsString('Staff Admin', $output);
        $this->assertStringContainsString('Manager MKDT', $output);
        $this->assertStringContainsString('Manager Keuangan', $output);
        $this->assertStringContainsString('Disetujui MKDT', $output);
        $this->assertStringContainsString('Disetujui Keuangan', $output);
    }

    public function testTemplateLayoutRendersSharedPengajuanWithCorrectScriptSequence(): void
    {
        $group = (object) [
            'id' => 99,
            'nomor_pengajuan' => 'PGJ/FEE/TEST/2026/001',
            'status' => 'diajukan',
            'items' => [],
        ];

        $viewHtml = view('sales/pengajuan_fee_shared', [
            'group' => $group,
            'permissions' => [
                'can_manage' => 1,
                'can_submit_spp' => 1,
                'can_cairkan' => 1,
                'can_sign_mkdt' => 0,
                'can_sign_keuangan' => 0,
            ],
        ]);

        $footerHtml = view('template/footer');

        // Combined sequence simulation as rendered by template.php ($content followed by footer)
        $combinedHtml = $viewHtml . "\n" . $footerHtml;

        // Verify script presence
        $this->assertStringContainsString('app-assets/vendors/js/vendors.min.js', $combinedHtml);
        $this->assertStringContainsString('app-assets/vendors/js/extensions/sweetalert2.all.min.js', $combinedHtml);
        $this->assertStringContainsString('app-menu.min.js', $combinedHtml);
        $this->assertStringContainsString('app.min.js', $combinedHtml);
        $this->assertStringContainsString('active-proyek.js', $combinedHtml);
        $this->assertStringContainsString('scripts.js', $combinedHtml);

        // Sequence check: vendors.min.js in view must appear before sweetalert2, before inline $(function, and before footer theme scripts
        $firstVendorsPos = strpos($combinedHtml, 'app-assets/vendors/js/vendors.min.js');
        $swalPos = strpos($combinedHtml, 'app-assets/vendors/js/extensions/sweetalert2.all.min.js');
        $inlineScriptPos = strpos($combinedHtml, '$(function () {');
        $appMenuPos = strpos($combinedHtml, 'app-menu.min.js');
        $scriptsJsPos = strpos($combinedHtml, 'assets/js/scripts.js');

        $this->assertNotFalse($firstVendorsPos);
        $this->assertNotFalse($swalPos);
        $this->assertNotFalse($inlineScriptPos);
        $this->assertNotFalse($appMenuPos);
        $this->assertNotFalse($scriptsJsPos);

        $this->assertLessThan($swalPos, $firstVendorsPos, 'vendors.min.js must be loaded before sweetalert2');
        $this->assertLessThan($inlineScriptPos, $firstVendorsPos, 'vendors.min.js must be loaded before inline $(function');
        $this->assertLessThan($appMenuPos, $firstVendorsPos, 'vendors.min.js must be loaded before app-menu.min.js in footer');
        $this->assertLessThan($scriptsJsPos, $firstVendorsPos, 'vendors.min.js must be loaded before scripts.js in footer');

        // Verify footer has jQuery fallback safeguard before theme scripts
        $this->assertStringContainsString("if (typeof jQuery === 'undefined')", $footerHtml);
        $this->assertStringContainsString("app-assets/vendors/js/vendors.min.js", $footerHtml);
        $footerFallbackPos = strpos($footerHtml, "if (typeof jQuery === 'undefined')");
        $footerAppMenuPos = strpos($footerHtml, 'app-menu.min.js');
        $this->assertLessThan($footerAppMenuPos, $footerFallbackPos, 'jQuery fallback must precede app-menu.min.js');
    }

    public function testPencairanAkadImportViewLoadsVendorsBeforeScripts(): void
    {
        $output = view('keuangan/pencairan-akad-import', [
            'controller' => 'Keuangan',
            'title' => 'Import Hasil Pencairan Akad',
        ]);

        $this->assertStringContainsString('app-assets/vendors/js/vendors.min.js', $output);
        $this->assertStringContainsString('app-assets/vendors/js/extensions/sweetalert2.all.min.js', $output);
        $vendorsPos = strpos($output, 'app-assets/vendors/js/vendors.min.js');
        $swalPos = strpos($output, 'app-assets/vendors/js/extensions/sweetalert2.all.min.js');
        $inlineJqueryPos = strpos($output, '$(function');
        $this->assertNotFalse($vendorsPos);
        $this->assertNotFalse($swalPos);
        $this->assertNotFalse($inlineJqueryPos);
        $this->assertLessThan($swalPos, $vendorsPos, 'vendors.min.js must precede sweetalert2');
        $this->assertLessThan($inlineJqueryPos, $vendorsPos, 'vendors.min.js must precede inline $(function');
    }

    public function testFeeSalesPengajuanModelAllowedFieldsIncludesTanggalPengajuanSales(): void
    {
        $model = new \App\Models\FeeSalesPengajuanModel();
        $refProp = new \ReflectionProperty($model, 'allowedFields');
        $refProp->setAccessible(true);
        $allowedFields = $refProp->getValue($model);

        $this->assertIsArray($allowedFields);
        $this->assertContains('tanggal_pengajuan_sales', $allowedFields);
        $this->assertContains('tanggal_pengajuan', $allowedFields);
    }

    public function testFeeSalesJsFormatShortDateHandlesDatetimeString(): void
    {
        $js = file_get_contents(FCPATH . 'assets/js/sales/fee_sales.js');
        $this->assertStringContainsString('split(" ")[0]', $js, 'formatShortDate must extract date portion before hyphen splitting');

        // Extract the actual formatShortDate function definition from fee_sales.js
        preg_match('/function formatShortDate\(d\)\s*\{(.*?)\n    \}/s', $js, $fnMatches);
        $this->assertNotEmpty($fnMatches, 'formatShortDate function must be found in fee_sales.js');

        // Execute actual extracted JS function in node via temporary file to avoid shell escaping pitfalls
        $tempJs = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'test_format_date_' . uniqid() . '.js';
        $scriptContent = "function formatShortDate(d) {\n" . $fnMatches[1] . "\n}\n" . 'process.stdout.write(formatShortDate("2026-10-22 15:30:00"));';
        file_put_contents($tempJs, $scriptContent);
        try {
            $output = shell_exec('node ' . escapeshellarg($tempJs));
            $this->assertSame('22/10/2026', trim((string) $output));
        } finally {
            if (file_exists($tempJs)) {
                unlink($tempJs);
            }
        }
    }

    public function testSharedPengajuanViewRendersTanggalSalesWhenPresent(): void
    {
        $group = (object) [
            'id' => 30,
            'nomor_pengajuan' => 'PGJ/FEE/TEST/2026/SALES-DATE',
            'status' => 'diajukan',
            'tanggal_pengajuan' => '2026-10-22',
            'tanggal_pengajuan_sales' => '2026-10-20',
            'items' => [],
        ];

        $output = view('sales/pengajuan_fee_shared', [
            'group' => $group,
            'permissions' => [],
        ]);

        $this->assertStringContainsString('Tanggal Pengajuan: <strong>22 Oct 2026</strong>', $output);
        $this->assertStringContainsString('Tgl Sales: <strong>20 Oct 2026</strong>', $output);
    }

    public function testSalesExternalRepositoryHasTanggalPengajuanSalesInSelect(): void
    {
        $repoFile = file_get_contents(APPPATH . 'Repositories/SalesExternalRepository.php');
        $this->assertStringContainsString("'fsp.tanggal_pengajuan_sales'", $repoFile);
        $this->assertStringContainsString("->orLike('fsp.tanggal_pengajuan_sales'", $repoFile);
        $this->assertStringContainsString("->orLike('fsp.ttd_pembuat_nama'", $repoFile);
    }

    public function testNotificationEventEnumContainsAllFiveFeeSalesEvents(): void
    {
        $all = \App\Enums\NotificationEvent::all();
        $expected = [
            \App\Enums\NotificationEvent::FEE_SALES_PENGAJUAN_CREATED => 'fee_sales_pengajuan_created',
            \App\Enums\NotificationEvent::FEE_SALES_TERMIN_GENERATED  => 'fee_sales_termin_generated',
            \App\Enums\NotificationEvent::FEE_SALES_HARGA_NET_UPDATED => 'fee_sales_harga_net_updated',
            \App\Enums\NotificationEvent::FEE_SALES_TTD_SIGNED        => 'fee_sales_ttd_signed',
            \App\Enums\NotificationEvent::FEE_SALES_PENCAIRAN         => 'fee_sales_pencairan',
        ];

        foreach ($expected as $constVal => $code) {
            $this->assertSame($code, $constVal);
            $this->assertContains($code, $all);
            $this->assertSame($code, \App\Enums\NotificationEvent::fromLegacyType($code));
            $this->assertSame($code, \App\Enums\NotificationEvent::fromLegacyType('  ' . strtoupper($code) . '  '));
        }

        // Legacy alias mappings
        $this->assertSame('fee_sales_pengajuan_created', \App\Enums\NotificationEvent::fromLegacyType('fee_sales_pengajuan'));
        $this->assertSame('fee_sales_termin_generated', \App\Enums\NotificationEvent::fromLegacyType('fee_sales_termin'));
        $this->assertSame('fee_sales_harga_net_updated', \App\Enums\NotificationEvent::fromLegacyType('fee_sales_harga_net'));
        $this->assertSame('fee_sales_ttd_signed', \App\Enums\NotificationEvent::fromLegacyType('fee_sales_ttd'));
        $this->assertSame('fee_sales_pencairan', \App\Enums\NotificationEvent::fromLegacyType('fee_sales_cair'));

        // Edge cases
        $this->assertNull(\App\Enums\NotificationEvent::fromLegacyType(null));
        $this->assertNull(\App\Enums\NotificationEvent::fromLegacyType(''));
        $this->assertNull(\App\Enums\NotificationEvent::fromLegacyType('   '));
        $this->assertSame('booking_baru', \App\Enums\NotificationEvent::fromLegacyType('booking_baru'));
    }

    private function ensureSqliteNotificationTables(\CodeIgniter\Database\BaseConnection $db): void
    {
        $tEvents = $db->prefixTable('notification_event_types');
        $tPrefs  = $db->prefixTable('user_notification_preferences');
        $tGroups = $db->prefixTable('auth_groups_users');

        $db->query("CREATE TABLE IF NOT EXISTS {$tEvents} (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            event_type VARCHAR(100) NOT NULL UNIQUE,
            category VARCHAR(100) NOT NULL,
            label VARCHAR(255) NOT NULL,
            description TEXT NULL,
            default_in_app INTEGER DEFAULT 1,
            default_email INTEGER DEFAULT 1,
            default_web_push INTEGER DEFAULT 1,
            is_mandatory INTEGER DEFAULT 0,
            relevant_groups VARCHAR(100) NULL,
            sort_order INTEGER DEFAULT 0,
            created_at DATETIME NULL,
            updated_at DATETIME NULL
        )");

        $db->query("CREATE TABLE IF NOT EXISTS {$tPrefs} (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            event_type VARCHAR(100) NOT NULL,
            in_app INTEGER DEFAULT 1,
            email INTEGER DEFAULT 1,
            web_push INTEGER DEFAULT 1,
            is_locked INTEGER DEFAULT 0,
            created_at DATETIME NULL,
            updated_at DATETIME NULL
        )");

        $db->query("CREATE TABLE IF NOT EXISTS {$tGroups} (
            group_id INTEGER NOT NULL,
            user_id INTEGER NOT NULL
        )");

        $tNotif = $db->prefixTable('notification');
        $tRecip = $db->prefixTable('notification_recipients');
        $db->query("CREATE TABLE IF NOT EXISTS {$tNotif} (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            type VARCHAR(100) NULL,
            notif TEXT NULL
        )");
        $db->query("CREATE TABLE IF NOT EXISTS {$tRecip} (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            notification_id INTEGER NOT NULL,
            user_id INTEGER NOT NULL,
            in_app_visible INTEGER DEFAULT 1,
            updated_at DATETIME NULL
        )");
    }

    public function testNotificationEventTypeTableContainsSeededFeeSalesEvents(): void
    {
        $db = \Config\Database::connect();
        $this->ensureSqliteNotificationTables($db);

        $seeder = new \App\Database\Seeds\NotificationEventTypeSeeder(new \Config\Database(), $db);
        $seeder->run();

        $rows = $db->table('notification_event_types')
            ->where('category', 'Fee Sales')
            ->orderBy('sort_order', 'ASC')
            ->get()
            ->getResultArray();

        $this->assertCount(5, $rows);
        $codes = array_column($rows, 'event_type');
        $this->assertSame([
            'fee_sales_pengajuan_created',
            'fee_sales_termin_generated',
            'fee_sales_harga_net_updated',
            'fee_sales_ttd_signed',
            'fee_sales_pencairan',
        ], $codes);

        foreach ($rows as $row) {
            $this->assertSame('3;4', $row['relevant_groups']);
            $this->assertSame(0, (int) $row['is_mandatory']);
        }
    }

    public function testNotificationPreferenceServiceRespectsRelevantGroupsDefault(): void
    {
        $db = \Config\Database::connect();
        $this->ensureSqliteNotificationTables($db);

        $seeder = new \App\Database\Seeds\NotificationEventTypeSeeder(new \Config\Database(), $db);
        $seeder->run();

        $prefService = new \App\Services\NotificationPreferenceService($db);

        // Group 3 (Keuangan) and Group 4 (MKDT) should default Fee Sales events to true
        $keuanganPrefs = $prefService->getUserPreferences(999991, 3);
        $keuanganFeeSales = array_values(array_filter($keuanganPrefs, static fn (array $p): bool => $p['category'] === 'Fee Sales'));
        $this->assertCount(5, $keuanganFeeSales);
        foreach ($keuanganFeeSales as $item) {
            $this->assertTrue($item['in_app'], "Keuangan user should default in_app=true for {$item['event_type']}");
            $this->assertTrue($item['email'], "Keuangan user should default email=true for {$item['event_type']}");
            $this->assertTrue($item['web_push'], "Keuangan user should default web_push=true for {$item['event_type']}");
        }

        $mkdtPrefs = $prefService->getUserPreferences(999992, 4);
        $mkdtFeeSales = array_values(array_filter($mkdtPrefs, static fn (array $p): bool => $p['category'] === 'Fee Sales'));
        $this->assertCount(5, $mkdtFeeSales);
        foreach ($mkdtFeeSales as $item) {
            $this->assertTrue($item['in_app'], "MKDT user should default in_app=true for {$item['event_type']}");
        }

        // Group 5 (Legal) should default Fee Sales events to false
        $legalPrefs = $prefService->getUserPreferences(999993, 5);
        $legalFeeSales = array_values(array_filter($legalPrefs, static fn (array $p): bool => $p['category'] === 'Fee Sales'));
        $this->assertCount(5, $legalFeeSales);
        foreach ($legalFeeSales as $item) {
            $this->assertFalse($item['in_app'], "Non-relevant group (Legal=5) should default in_app=false for {$item['event_type']}");
            $this->assertFalse($item['email'], "Non-relevant group (Legal=5) should default email=false for {$item['event_type']}");
            $this->assertFalse($item['web_push'], "Non-relevant group (Legal=5) should default web_push=false for {$item['event_type']}");
        }

        // Multi-group user: belongs to group 8 (Promosi) and group 4 (MKDT), passed with first group 8
        $db->table('auth_groups_users')->where('user_id', 999994)->delete();
        $db->table('auth_groups_users')->insert(['group_id' => 8, 'user_id' => 999994]);
        $db->table('auth_groups_users')->insert(['group_id' => 4, 'user_id' => 999994]);
        $multiGroupPrefs = $prefService->getUserPreferences(999994, 8);
        $multiGroupFeeSales = array_values(array_filter($multiGroupPrefs, static fn (array $p): bool => $p['category'] === 'Fee Sales'));
        $this->assertCount(5, $multiGroupFeeSales);
        foreach ($multiGroupFeeSales as $item) {
            $this->assertTrue($item['in_app'], "Multi-group user with group 4 membership should default in_app=true for {$item['event_type']}");
        }

        // Resetting preferences for a non-relevant group user (group 5) syncs in_app_visible to 0
        $db->table('auth_groups_users')->where('user_id', 999993)->delete();
        $db->table('auth_groups_users')->insert(['group_id' => 5, 'user_id' => 999993]);
        $db->table('notification')->insert([
            'id'    => 9001,
            'type'  => \App\Enums\NotificationEvent::FEE_SALES_PENGAJUAN_CREATED,
            'notif' => 'Pengajuan Fee Sales Baru',
        ]);
        $db->table('notification_recipients')->insert([
            'id'              => 9001,
            'notification_id' => 9001,
            'user_id'         => 999993,
            'in_app_visible'  => 1,
        ]);
        $prefService->resetToDefaults(999993);
        $recipRow = $db->table('notification_recipients')->where('id', 9001)->get()->getRow();
        $this->assertSame(0, (int) $recipRow->in_app_visible);

        // General events without relevant_groups restriction (e.g. kavling_added) should still default to true for Group 5
        $legalSiteplan = array_values(array_filter($legalPrefs, static fn (array $p): bool => $p['event_type'] === 'kavling_added'));
        $this->assertCount(1, $legalSiteplan);
        $this->assertTrue($legalSiteplan[0]['in_app']);

        // Legacy event code resolution in isAllowed()
        $this->assertTrue($prefService->isAllowed(999991, 'fee_sales_pengajuan', 'in_app'));
        $db->table('user_notification_preferences')->insert([
            'user_id'    => 999991,
            'event_type' => \App\Enums\NotificationEvent::FEE_SALES_PENGAJUAN_CREATED,
            'in_app'     => 0,
            'email'      => 1,
            'web_push'   => 1,
            'is_locked'  => 0,
        ]);
        $this->assertFalse($prefService->isAllowed(999991, 'fee_sales_pengajuan', 'in_app'));
        $this->assertTrue($prefService->isAllowed(999991, 'fee_sales_pengajuan', 'email'));
    }

    public function testSalesExternalServiceNotificationDispatchAndSuppression(): void
    {
        $capturedCalls = [];
        $mockNotif = $this->createMock(\App\Services\NotifikasiService::class);
        $mockNotif->method('tambah_notif')
            ->willReturnCallback(function (
                $target,
                $notif,
                $add_by,
                $id_kavling,
                $id_konsumen,
                $type = null,
                $id_proyek = null,
                ?string $actionUrl = null,
                ?string $eventType = null
            ) use (&$capturedCalls) {
                $capturedCalls[] = [
                    'target'      => $target,
                    'notif'       => $notif,
                    'add_by'      => $add_by,
                    'id_kavling'  => $id_kavling,
                    'id_konsumen' => $id_konsumen,
                    'type'        => $type,
                    'id_proyek'   => $id_proyek,
                    'action_url'  => $actionUrl,
                    'event_type'  => $eventType,
                ];
                return count($capturedCalls);
            });

        $service = new SalesExternalService($mockNotif);

        $refMethod = new \ReflectionMethod($service, 'sendFeeSalesNotification');
        $refMethod->setAccessible(true);

        $refMethod->invoke(
            $service,
            \App\Enums\NotificationEvent::FEE_SALES_PENGAJUAN_CREATED,
            'Pengajuan Fee Sales Baru',
            'Pengajuan fee sales PGJ/FEE/KEU/20261023/ABCD telah dibuat.',
            2,
            10,
            20,
            'fee-sales/pengajuan/tok123',
            77,
            5
        );

        $refMethod->invoke(
            $service,
            \App\Enums\NotificationEvent::FEE_SALES_TERMIN_GENERATED,
            'Generate Termin Fee Sales',
            'Berhasil men-generate 3 termin fee sales.',
            2,
            10,
            20,
            'fee-sales?open_mkdt=55',
            88,
            5
        );

        $refMethod->invoke(
            $service,
            \App\Enums\NotificationEvent::FEE_SALES_HARGA_NET_UPDATED,
            'Update Harga Net AJB Fee Sales',
            'Harga Net AJB diperbarui.',
            2,
            10,
            20,
            'fee-sales?open_mkdt=55',
            88,
            5
        );

        $refMethod->invoke(
            $service,
            \App\Enums\NotificationEvent::FEE_SALES_TTD_SIGNED,
            'TTD MKDT Pengajuan Fee Sales',
            'Pengajuan telah ditandatangani.',
            2,
            10,
            20,
            'fee-sales/pengajuan/tok123',
            77,
            5
        );

        $refMethod->invoke(
            $service,
            \App\Enums\NotificationEvent::FEE_SALES_PENCAIRAN,
            'Pencairan Fee Sales',
            'Pencairan telah dikonfirmasi.',
            2,
            10,
            20,
            'fee-sales/pengajuan/tok123',
            77,
            5
        );

        $this->assertCount(5, $capturedCalls);
        $this->assertSame('fee_sales_pengajuan_created', $capturedCalls[0]['event_type']);
        $this->assertSame('fee_sales_pengajuan_created', $capturedCalls[0]['type']);
        $this->assertSame('3;4', $capturedCalls[0]['target']);
        $this->assertSame(5, $capturedCalls[0]['add_by']);
        $this->assertSame(10, $capturedCalls[0]['id_kavling']);
        $this->assertSame(20, $capturedCalls[0]['id_konsumen']);
        $this->assertSame(2, $capturedCalls[0]['id_proyek']);
        $this->assertSame('fee-sales/pengajuan/tok123', $capturedCalls[0]['action_url']);

        $this->assertSame('fee_sales_termin_generated', $capturedCalls[1]['event_type']);
        $this->assertSame('fee-sales?open_mkdt=55', $capturedCalls[1]['action_url']);

        $this->assertSame('fee_sales_harga_net_updated', $capturedCalls[2]['event_type']);
        $this->assertSame('fee_sales_ttd_signed', $capturedCalls[3]['event_type']);
        $this->assertSame('fee_sales_pencairan', $capturedCalls[4]['event_type']);
    }

    public function testFrontendScriptsContainFeeSalesNotificationAndOpenMkdtHandlers(): void
    {
        $scriptsJs = file_get_contents(FCPATH . 'assets/js/scripts.js');
        $this->assertStringContainsString('type && type.startsWith("fee_sales")', $scriptsJs);
        $this->assertStringContainsString('window.location.href = base_url + "/notif/open/" + id_notif;', $scriptsJs);

        $feeSalesJs = file_get_contents(FCPATH . 'assets/js/sales/fee_sales.js');
        $this->assertStringContainsString('urlParams.get("open_mkdt")', $feeSalesJs);
        $this->assertStringContainsString('openDetailModal(openMkdt)', $feeSalesJs);
    }

    public function testSalesExternalServiceMethodsEmitExpectedNotificationsWithSqliteFixtures(): void
    {
        $db = \Config\Database::connect();
        $p = static fn (string $t): string => $db->prefixTable($t);

        $db->query("CREATE TABLE IF NOT EXISTS {$p('mkdt')} (
            id_mkdt INTEGER PRIMARY KEY AUTOINCREMENT,
            id_kavling INTEGER DEFAULT 10,
            id_konsumen INTEGER DEFAULT 20,
            id_hargajual INTEGER DEFAULT 0,
            status_mkdt VARCHAR(50) DEFAULT 'Akad',
            is_kpr INTEGER DEFAULT 1,
            is_ajb VARCHAR(20) DEFAULT 'AJB',
            is_subsidi INTEGER DEFAULT 0,
            is_batal INTEGER DEFAULT 0,
            akad_indent INTEGER DEFAULT 0,
            akad_tgl DATE NULL,
            harga_jual REAL DEFAULT 500000000,
            harga_jual_net REAL DEFAULT 480000000,
            harga_net_ajb REAL DEFAULT 480000000
        )");
        $db->query("CREATE TABLE IF NOT EXISTS {$p('sales')} (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            nama VARCHAR(150) NOT NULL,
            kontak VARCHAR(100) NULL,
            bank VARCHAR(100) NULL,
            no_rek VARCHAR(100) NULL,
            proyek TEXT NULL,
            fee_subsidi REAL DEFAULT 1.5,
            fee_komersil REAL DEFAULT 2.0,
            tanggal_pks DATE NULL,
            tanggal_exp_pks DATE NULL,
            lampiran_pks VARCHAR(255) NULL,
            status VARCHAR(30) DEFAULT 'active',
            has_pks INTEGER DEFAULT 1,
            catatan TEXT NULL,
            created_by INTEGER NULL,
            updated_by INTEGER NULL,
            created_at DATETIME NULL,
            updated_at DATETIME NULL,
            deleted_at DATETIME NULL
        )");
        $db->query("CREATE TABLE IF NOT EXISTS {$p('fee_sales')} (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            id_mkdt INTEGER NOT NULL,
            id_sales INTEGER NULL,
            sales_nama VARCHAR(150) NULL,
            sales_keterangan TEXT NULL,
            harga_net_ajb REAL DEFAULT 0,
            persentase_fee REAL DEFAULT 0,
            total_fee REAL DEFAULT 0,
            skema_termin VARCHAR(30) DEFAULT '30_60_10',
            status VARCHAR(30) DEFAULT 'draft',
            catatan TEXT NULL,
            created_by INTEGER NULL,
            updated_by INTEGER NULL,
            created_at DATETIME NULL,
            updated_at DATETIME NULL
        )");
        $db->query("CREATE TABLE IF NOT EXISTS {$p('fee_sales_termin')} (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            id_fee_sales INTEGER NOT NULL,
            termin_ke INTEGER NOT NULL,
            persen REAL DEFAULT 0,
            nominal REAL DEFAULT 0,
            status VARCHAR(30) DEFAULT 'belum_diajukan',
            nomor_spp VARCHAR(100) NULL,
            tanggal_pengajuan_spp DATE NULL,
            lampiran_spp VARCHAR(255) NULL,
            tanggal_cair DATE NULL,
            no_cek VARCHAR(100) NULL,
            lampiran_bukti_cair VARCHAR(255) NULL,
            keterangan TEXT NULL,
            created_by INTEGER NULL,
            updated_by INTEGER NULL,
            created_at DATETIME NULL,
            updated_at DATETIME NULL
        )");
        $db->query("CREATE TABLE IF NOT EXISTS {$p('proyek')} (
            id_proyek INTEGER PRIMARY KEY AUTOINCREMENT,
            kode_keuangan VARCHAR(50) DEFAULT 'KEU'
        )");
        $db->query("CREATE TABLE IF NOT EXISTS {$p('fee_sales_pengajuan')} (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            id_proyek INTEGER NULL,
            share_token VARCHAR(64) NULL,
            nomor_pengajuan VARCHAR(100) NULL,
            tanggal_pengajuan DATE NULL,
            tanggal_pengajuan_sales DATE NULL,
            catatan TEXT NULL,
            lampiran_spp VARCHAR(255) NULL,
            status VARCHAR(30) DEFAULT 'diajukan',
            ttd_pembuat_by INTEGER NULL,
            ttd_pembuat_nama VARCHAR(150) NULL,
            ttd_pembuat_path VARCHAR(255) NULL,
            ttd_pembuat_method VARCHAR(30) NULL,
            ttd_pembuat_at DATETIME NULL,
            ttd_mkdt_by INTEGER NULL,
            ttd_mkdt_nama VARCHAR(150) NULL,
            ttd_mkdt_path VARCHAR(255) NULL,
            ttd_mkdt_method VARCHAR(30) NULL,
            ttd_mkdt_at DATETIME NULL,
            ttd_keuangan_by INTEGER NULL,
            ttd_keuangan_nama VARCHAR(150) NULL,
            ttd_keuangan_path VARCHAR(255) NULL,
            ttd_keuangan_method VARCHAR(30) NULL,
            ttd_keuangan_at DATETIME NULL,
            voided_by INTEGER NULL,
            voided_at DATETIME NULL,
            void_reason TEXT NULL,
            created_by INTEGER NULL,
            updated_by INTEGER NULL,
            created_at DATETIME NULL,
            updated_at DATETIME NULL
        )");
        $db->query("CREATE TABLE IF NOT EXISTS {$p('fee_sales_pengajuan_item')} (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            id_pengajuan INTEGER NOT NULL,
            id_fee_sales INTEGER NOT NULL,
            id_termin INTEGER NOT NULL,
            termin_ke INTEGER DEFAULT 1,
            nominal REAL DEFAULT 0,
            created_at DATETIME NULL
        )");

        $db->table('proyek')->insert([
            'id_proyek'     => 3,
            'kode_keuangan' => 'KEU',
        ]);
        $db->table('mkdt')->insert([
            'id_mkdt'        => 501,
            'id_kavling'     => 10,
            'id_konsumen'    => 20,
            'harga_jual'     => 500000000,
            'harga_jual_net' => 480000000,
            'harga_net_ajb'  => 480000000,
        ]);
        $db->table('mkdt')->insert([
            'id_mkdt'        => 502,
            'id_kavling'     => 11,
            'id_konsumen'    => 21,
            'harga_jual'     => 600000000,
            'harga_jual_net' => 580000000,
            'harga_net_ajb'  => 580000000,
        ]);

        $capturedCalls = [];
        $mockNotif = $this->createMock(\App\Services\NotifikasiService::class);
        $mockNotif->method('tambah_notif')
            ->willReturnCallback(function (
                $target,
                $notif,
                $add_by,
                $id_kavling,
                $id_konsumen,
                $type = null,
                $id_proyek = null,
                ?string $actionUrl = null,
                ?string $eventType = null
            ) use (&$capturedCalls) {
                $capturedCalls[] = [
                    'target'      => $target,
                    'notif'       => $notif,
                    'add_by'      => $add_by,
                    'id_kavling'  => $id_kavling,
                    'id_konsumen' => $id_konsumen,
                    'type'        => $type,
                    'id_proyek'   => $id_proyek,
                    'action_url'  => $actionUrl,
                    'event_type'  => $eventType,
                ];
                return count($capturedCalls);
            });

        $mockRepo = $this->createMock(SalesExternalRepository::class);
        $mockRepo->method('getFeeDetailByMkdt')->willReturnCallback(static function (int $idMkdt) use ($db) {
            $mkdt = $db->table('mkdt')->where('id_mkdt', $idMkdt)->get()->getRow();
            $feeRows = $db->table('fee_sales')->where('id_mkdt', $idMkdt)->get()->getResult();
            $feeList = [];
            foreach ($feeRows as $fr) {
                $fr->termin = $db->table('fee_sales_termin')->where('id_fee_sales', $fr->id)->orderBy('termin_ke', 'ASC')->get()->getResult();
                $feeList[] = $fr;
            }
            $firstFee = $feeList[0] ?? null;
            return (object) [
                'id_mkdt'           => $idMkdt,
                'id_kavling'        => (int) ($mkdt->id_kavling ?? 10),
                'id_konsumen'       => (int) ($mkdt->id_konsumen ?? 20),
                'id_proyek'         => 3,
                'no_kavling'        => $idMkdt === 502 ? 'A-02' : 'A-01',
                'nama_konsumen'     => $idMkdt === 502 ? 'Siti Aminah' : 'Budi Santoso',
                'nama_proyek'       => 'Grand Permata',
                'is_subsidi'        => 0,
                'akad_indent'       => 0,
                'harga_jual_net'    => (float) ($mkdt->harga_jual_net ?? 480000000),
                'harga_net_ajb'     => (float) ($mkdt->harga_net_ajb ?? 480000000),
                'master_sales_nama' => 'Sales Satu',
                'sales_konsumen'    => 'Sales Satu',
                'id_fee_sales'      => $firstFee ? (int) $firstFee->id : null,
                'termin'            => $firstFee ? $firstFee->termin : [],
                'fee_list'          => $feeList,
            ];
        });
        $mockRepo->method('hasGeneratedTerminByMkdt')->willReturn(false);
        $mockRepo->method('userSnapshot')->willReturn([
            'id'            => 7,
            'name'          => 'MKDT Approver',
            'password_hash' => \Myth\Auth\Password::hash('secret123'),
        ]);

        $service = new class ($mockNotif) extends SalesExternalService {
            protected function storePengajuanSignature(int $idPengajuan, int $userId, string $method, string $canvasData): string
            {
                return "fee_sales/pengajuan/{$idPengajuan}/signatures/test_sig.png";
            }
        };
        $refRepo = new \ReflectionProperty(SalesExternalService::class, 'salesRepo');
        $refRepo->setAccessible(true);
        $refRepo->setValue($service, $mockRepo);

        // 1. saveFeeHeader with sendNotification=false (suppressed)
        $resSuppressed = $service->saveFeeHeader([
            'id_mkdt'       => 501,
            'sales_nama'    => 'Sales Satu',
            'harga_net_ajb' => 490000000,
            'skema_termin'  => '30_60_10',
        ], 7, false);
        $this->assertTrue($resSuppressed['success']);
        $this->assertCount(0, $capturedCalls, 'No notification should fire when sendNotification=false');

        // 2. saveFeeHeader with price change + termin generation and sendNotification=true
        $resHeader = $service->saveFeeHeader([
            'id_mkdt'       => 501,
            'id_fee_sales'  => $resSuppressed['id_fee_sales'],
            'sales_nama'    => 'Sales Satu',
            'harga_net_ajb' => 500000000,
            'skema_termin'  => '30_60_10',
        ], 7, true);
        $this->assertTrue($resHeader['success']);
        $this->assertCount(2, $capturedCalls);
        $this->assertSame(\App\Enums\NotificationEvent::FEE_SALES_HARGA_NET_UPDATED, $capturedCalls[0]['event_type']);
        $this->assertSame('fee-sales?open_mkdt=501', $capturedCalls[0]['action_url']);
        $this->assertSame(\App\Enums\NotificationEvent::FEE_SALES_TERMIN_GENERATED, $capturedCalls[1]['event_type']);
        $this->assertSame('fee-sales?open_mkdt=501', $capturedCalls[1]['action_url']);

        // 3. submitPengajuanSpp
        $termin1 = $db->table('fee_sales_termin')
            ->where('id_fee_sales', $resHeader['id_fee_sales'])
            ->where('termin_ke', 1)
            ->get()
            ->getRow();
        $this->assertNotNull($termin1);

        $capturedCalls = [];
        $resSpp = $service->submitPengajuanSpp((int) $termin1->id, [
            'nomor_spp'             => 'SPP/FEE/001',
            'tanggal_pengajuan_spp' => '2026-10-23',
            'keterangan'            => 'Pengajuan termin 1',
        ], 7);
        $this->assertTrue($resSpp['success']);
        $this->assertCount(1, $capturedCalls);
        $this->assertSame(\App\Enums\NotificationEvent::FEE_SALES_PENGAJUAN_CREATED, $capturedCalls[0]['event_type']);
        $this->assertSame('fee-sales?open_mkdt=501', $capturedCalls[0]['action_url']);

        // 4. confirmPencairan (single termin)
        $capturedCalls = [];
        $resCair = $service->confirmPencairan((int) $termin1->id, [
            'tanggal_cair' => '2026-10-23',
            'no_cek'       => 'CEK-001',
            'keterangan'   => 'Cair termin 1',
        ], 7);
        $this->assertTrue($resCair['success']);
        $this->assertCount(1, $capturedCalls);
        $this->assertSame(\App\Enums\NotificationEvent::FEE_SALES_PENCAIRAN, $capturedCalls[0]['event_type']);

        // 5. createPengajuanGroup on uninitialized unit 502 (auto-calls saveFeeHeader(..., false) and emits ONLY 1 FEE_SALES_PENGAJUAN_CREATED)
        $capturedCalls = [];
        $resCreateGroup = $service->createPengajuanGroup([
            'id_proyek'               => 3,
            'tanggal_pengajuan_sales' => '2026-10-22',
            'tanggal_pengajuan'       => '2026-10-23',
            'signature_method'        => 'canvas',
            'signature_data'          => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=',
            'items'                   => [
                [
                    'id_mkdt'   => 502,
                    'termin_ke' => 1,
                ],
            ],
        ], 7);
        $this->assertTrue($resCreateGroup['success']);
        $this->assertCount(1, $capturedCalls, 'createPengajuanGroup should suppress auto-header notifications and emit only 1 FEE_SALES_PENGAJUAN_CREATED');
        $this->assertSame(\App\Enums\NotificationEvent::FEE_SALES_PENGAJUAN_CREATED, $capturedCalls[0]['event_type']);
        $this->assertSame('fee-sales/pengajuan/' . $resCreateGroup['share_token'], $capturedCalls[0]['action_url']);
        $this->assertSame(11, $capturedCalls[0]['id_kavling']);
        $this->assertSame(21, $capturedCalls[0]['id_konsumen']);

        // 6. signPengajuanGroup & confirmPencairanGroup (batch pengajuan group)
        $termin2 = $db->table('fee_sales_termin')
            ->where('id_fee_sales', $resHeader['id_fee_sales'])
            ->where('termin_ke', 2)
            ->get()
            ->getRow();
        $this->assertNotNull($termin2);

        $db->table('fee_sales_pengajuan')->insert([
            'id'                => 77,
            'id_proyek'         => 3,
            'share_token'       => 'tok_group_77',
            'nomor_pengajuan'   => 'PGJ/FEE/KEU/20261023/7777',
            'tanggal_pengajuan' => '2026-10-23',
            'status'            => 'diajukan',
        ]);
        $db->table('fee_sales_pengajuan_item')->insert([
            'id_pengajuan' => 77,
            'id_fee_sales' => $resHeader['id_fee_sales'],
            'id_termin'    => (int) $termin2->id,
            'termin_ke'    => 2,
            'nominal'      => (float) $termin2->nominal,
        ]);

        $mockRepo->method('getPengajuanGroupDetail')->willReturn((object) [
            'id'              => 77,
            'id_proyek'       => 3,
            'share_token'     => 'tok_group_77',
            'nomor_pengajuan' => 'PGJ/FEE/KEU/20261023/7777',
            'status'          => 'diajukan',
            'items'           => [
                (object) [
                    'id_termin'         => (int) $termin2->id,
                    'id_fee_sales'      => (int) $resHeader['id_fee_sales'],
                    'termin_status'     => 'pengajuan_spp',
                    'nominal'           => (float) $termin2->nominal,
                    'termin_nominal'    => (float) $termin2->nominal,
                    'termin_no_cek'     => null,
                    'termin_keterangan' => null,
                    'id_kavling'        => 10,
                    'id_konsumen'       => 20,
                    'item_id_proyek'    => 3,
                ],
            ],
        ]);

        $capturedCalls = [];
        $resSign = $service->signPengajuanGroup(77, 'mkdt', [
            'password'         => 'secret123',
            'signature_method' => 'canvas',
            'signature_data'   => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=',
        ], 7);
        $this->assertTrue($resSign['success']);
        $this->assertCount(1, $capturedCalls);
        $this->assertSame(\App\Enums\NotificationEvent::FEE_SALES_TTD_SIGNED, $capturedCalls[0]['event_type']);
        $this->assertSame('fee-sales/pengajuan/tok_group_77', $capturedCalls[0]['action_url']);
        $this->assertStringContainsString('MKDT Approver (MKDT)', $capturedCalls[0]['notif']);

        $capturedCalls = [];
        $resGroupCair = $service->confirmPencairanGroup(77, [
            'tanggal_cair' => '2026-10-23',
            'no_cek'       => 'CEK-GRP-77',
            'keterangan'   => 'Cair grup',
        ], 7);
        $this->assertTrue($resGroupCair['success']);
        $this->assertCount(1, $capturedCalls);
        $this->assertSame(\App\Enums\NotificationEvent::FEE_SALES_PENCAIRAN, $capturedCalls[0]['event_type']);
        $this->assertSame('fee-sales/pengajuan/tok_group_77', $capturedCalls[0]['action_url']);
    }
}




