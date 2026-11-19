<?php

use App\Repositories\BookingPaymentRepository;
use App\Services\BookingFeeMigrationService;
use App\Services\BookingPaymentService;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Database;

final class BookingInstallmentSeparationTest extends CIUnitTestCase
{
    protected $db;

    protected function setUp(): void
    {
        parent::setUp();
        $this->db = Database::connect('tests', true);
        $p = $this->db->getPrefix();
        foreach (['history_log','kavling','jalan','cluster','finance_ledger','mkdt_booking_payment','mkdt_payment_summary','keuangan','log_pembayaran_detail','log_pembayaran','keuangan_item_list','mkdt'] as $t) {
            $this->db->query("DROP TABLE IF EXISTS {$p}{$t}");
        }
        $this->db->query("CREATE TABLE {$p}mkdt (id_mkdt INTEGER PRIMARY KEY, id_kavling INTEGER, booking_fee REAL, booking_tgl TEXT, is_lunas INTEGER DEFAULT 0, status_mkdt TEXT DEFAULT 'Booking', is_batal INTEGER DEFAULT 0, refund_paid INTEGER DEFAULT 0, id_proyek INTEGER)");
        $this->db->query("CREATE TABLE {$p}history_log (id INTEGER PRIMARY KEY AUTOINCREMENT, module TEXT, action TEXT, id_proyek INTEGER, id_kavling INTEGER, reference_type TEXT, reference_id INTEGER, summary TEXT, old_data TEXT, new_data TEXT, metadata TEXT, add_by INTEGER, created_at TEXT)");
        $this->db->query("CREATE TABLE {$p}mkdt_booking_payment (id_mkdt INTEGER PRIMARY KEY, id_pembayaran INTEGER, verified_by INTEGER, verified_at TEXT, replaces_payment_ids TEXT, created_at TEXT, updated_at TEXT)");
        $this->db->query("CREATE TABLE {$p}log_pembayaran (id_pembayaran INTEGER PRIMARY KEY, id_mkdt INTEGER, id_keuangan TEXT, nominal REAL, tanggal_bayar TEXT, payment_type TEXT, keterangan TEXT, is_deleted INTEGER DEFAULT 0, st INTEGER DEFAULT 1, add_by INTEGER, edit_by INTEGER, created_at TEXT, updated_at TEXT)");
        $this->db->query("CREATE TABLE {$p}log_pembayaran_detail (id_pembayaran_detail INTEGER PRIMARY KEY, id_pembayaran INTEGER, id_keuangan_item_list INTEGER, nominal REAL, booking_is_installment INTEGER DEFAULT 0, add_by INTEGER, edit_by INTEGER, created_at TEXT, updated_at TEXT)");
        $this->db->query("CREATE TABLE {$p}keuangan_item_list (id_keuangan_item_list INTEGER PRIMARY KEY, item TEXT, kategori TEXT, deleted_at TEXT)");
        $this->db->query("CREATE TABLE {$p}keuangan (id_keuangan INTEGER PRIMARY KEY, id_mkdt INTEGER, nominal REAL, is_void INTEGER DEFAULT 0)");
        $this->db->query("CREATE TABLE {$p}mkdt_payment_summary (id_mkdt INTEGER PRIMARY KEY, total_um REAL, total_adm REAL, total_bb REAL, total_booking REAL, updated_at TEXT)");
        $this->db->query("CREATE TABLE {$p}finance_ledger (id INTEGER PRIMARY KEY AUTOINCREMENT, direction TEXT, source_type TEXT, source_id INTEGER, source_detail_id INTEGER, id_mkdt INTEGER, id_kavling INTEGER, nominal REAL, tanggal_transaksi TEXT, label TEXT, keterangan TEXT, status TEXT, is_deleted INTEGER DEFAULT 0, deleted_at TEXT, deleted_by INTEGER, add_by INTEGER, created_at TEXT, edit_by INTEGER, updated_at TEXT)");
        $this->db->table('keuangan_item_list')->insertBatch([
            ['id_keuangan_item_list'=>1,'item'=>'Booking','kategori'=>'BO'],
            ['id_keuangan_item_list'=>2,'item'=>'Uang Muka','kategori'=>'UM'],
        ]);
        $this->db->resetDataCache();
    }

    public function testPositiveBookingIsExcludedWhilePromoBookingBecomesUm(): void
    {
        $this->db->table('mkdt')->insertBatch([
            ['id_mkdt'=>1,'booking_fee'=>1500000,'booking_tgl'=>'2025-01-01','is_lunas'=>1],
            ['id_mkdt'=>2,'booking_fee'=>0,'booking_tgl'=>'2025-01-02','is_lunas'=>0],
        ]);
        $this->db->table('keuangan')->insertBatch([
            ['id_keuangan'=>1,'id_mkdt'=>1,'nominal'=>2000000],
            ['id_keuangan'=>2,'id_mkdt'=>2,'nominal'=>1000000],
        ]);
        $this->db->table('log_pembayaran')->insertBatch([
            ['id_pembayaran'=>10,'id_mkdt'=>1,'nominal'=>1500000,'tanggal_bayar'=>'2025-01-01','payment_type'=>'Booking'],
            ['id_pembayaran'=>11,'id_mkdt'=>1,'nominal'=>1000000,'tanggal_bayar'=>'2025-02-01','payment_type'=>'Uang Muka'],
            ['id_pembayaran'=>20,'id_mkdt'=>2,'nominal'=>1000000,'tanggal_bayar'=>'2025-01-02','payment_type'=>'Booking'],
        ]);
        $this->db->table('log_pembayaran_detail')->insertBatch([
            ['id_pembayaran_detail'=>1,'id_pembayaran'=>10,'id_keuangan_item_list'=>1,'nominal'=>1500000,'booking_is_installment'=>0],
            ['id_pembayaran_detail'=>2,'id_pembayaran'=>11,'id_keuangan_item_list'=>2,'nominal'=>1000000,'booking_is_installment'=>0],
            ['id_pembayaran_detail'=>3,'id_pembayaran'=>20,'id_keuangan_item_list'=>1,'nominal'=>1000000,'booking_is_installment'=>1],
        ]);

        $repo = new BookingPaymentRepository($this->db);
        $positive = $repo->installmentSummary(1);
        $promo = $repo->installmentSummary(2);
        $this->assertSame(1000000.0, $positive['sudah_bayar']);
        $this->assertSame(1000000.0, $positive['sisa_tagihan']);
        $this->assertTrue($positive['perlu_rekonsiliasi']);
        $this->assertSame(1000000.0, $promo['sudah_bayar']);
        $this->assertSame('UM', $promo['items'][0]['kategori']);
        $this->assertSame('Uang Muka', $promo['items'][0]['item']);
    }

    public function testVerificationIsIdempotentAndLocksSourceFields(): void
    {
        $this->db->table('mkdt')->insert(['id_mkdt'=>3,'booking_fee'=>1500000,'booking_tgl'=>'2025-07-09','is_lunas'=>0]);
        $this->db->table('log_pembayaran')->insert(['id_pembayaran'=>30,'id_mkdt'=>3,'nominal'=>1500000,'tanggal_bayar'=>'2025-07-09','payment_type'=>'Booking']);
        $this->db->table('log_pembayaran_detail')->insert(['id_pembayaran_detail'=>30,'id_pembayaran'=>30,'id_keuangan_item_list'=>1,'nominal'=>1500000,'booking_is_installment'=>0]);
        $this->db->table('mkdt_booking_payment')->insert(['id_mkdt'=>3,'id_pembayaran'=>30]);

        $service = new BookingPaymentService($this->db);
        $first = $service->verify(3, 7);
        $second = $service->verify(3, 8);
        $this->assertTrue($first['is_verified']);
        $this->assertSame($first['verified_at'], $second['verified_at']);
        $this->assertSame(7, (int) $second['verified_by']);
        $this->expectException(DomainException::class);
        $service->assertEditable(3, 1000000, '2025-07-09');
    }

    public function testVerificationCanCorrectBookingBeforeLocking(): void
    {
        $this->db->table('mkdt')->insert(['id_mkdt'=>5,'booking_fee'=>1500000,'booking_tgl'=>'2025-07-09','is_lunas'=>0]);
        $this->db->table('log_pembayaran')->insert([
            'id_pembayaran'=>50,'id_mkdt'=>5,'nominal'=>1500000,'tanggal_bayar'=>'2025-07-09',
            'payment_type'=>'Booking','keterangan'=>'Booking fee otomatis dari MKDT','is_deleted'=>0,
            'add_by'=>3,'edit_by'=>3,'created_at'=>'2025-07-09 10:00:00','updated_at'=>'2025-07-09 10:00:00',
        ]);
        $this->db->table('log_pembayaran_detail')->insert([
            'id_pembayaran_detail'=>50,'id_pembayaran'=>50,'id_keuangan_item_list'=>1,
            'nominal'=>1500000,'booking_is_installment'=>0,
        ]);
        $this->db->table('mkdt_booking_payment')->insert(['id_mkdt'=>5,'id_pembayaran'=>50]);
        $this->db->table('finance_ledger')->insert([
            'direction'=>'income','source_type'=>'log_pembayaran','source_id'=>50,'id_mkdt'=>5,
            'nominal'=>1500000,'tanggal_transaksi'=>'2025-07-09','label'=>'Booking',
            'status'=>'active','is_deleted'=>0,'add_by'=>3,'created_at'=>'2025-07-09 10:00:00',
            'edit_by'=>3,'updated_at'=>'2025-07-09 10:00:00',
        ]);

        $result = (new BookingPaymentService($this->db))->verify(5, 9, 1750000, '2025-07-10');

        $mkdt = $this->db->table('mkdt')->where('id_mkdt', 5)->get()->getRowArray();
        $payment = $this->db->table('log_pembayaran')->where('id_pembayaran', 50)->get()->getRowArray();
        $detail = $this->db->table('log_pembayaran_detail')->where('id_pembayaran', 50)->get()->getRowArray();
        $ledger = $this->db->table('finance_ledger')->where('source_id', 50)->get()->getRowArray();

        $this->assertTrue($result['is_verified']);
        $this->assertSame(1750000.0, (float) $mkdt['booking_fee']);
        $this->assertSame('2025-07-10', $mkdt['booking_tgl']);
        $this->assertSame(1750000.0, (float) $payment['nominal']);
        $this->assertSame('2025-07-10', $payment['tanggal_bayar']);
        $this->assertSame(1750000.0, (float) $detail['nominal']);
        $this->assertSame(1750000.0, (float) $ledger['nominal']);
        $this->assertSame('2025-07-10', $ledger['tanggal_transaksi']);
        $this->assertSame(9, (int) $result['verified_by']);
    }

    public function testVerifiedPromoCannotBeReallocatedOrDeleted(): void
    {
        $this->db->table('mkdt')->insert(['id_mkdt'=>4,'booking_fee'=>0,'booking_tgl'=>'2025-08-01','is_lunas'=>0]);
        $this->db->table('log_pembayaran')->insert(['id_pembayaran'=>40,'id_mkdt'=>4,'nominal'=>1000000,'tanggal_bayar'=>'2025-08-01','payment_type'=>'Booking']);
        $this->db->table('log_pembayaran_detail')->insert(['id_pembayaran_detail'=>40,'id_pembayaran'=>40,'id_keuangan_item_list'=>1,'nominal'=>1000000,'booking_is_installment'=>1]);
        $this->db->table('mkdt_booking_payment')->insert(['id_mkdt'=>4,'id_pembayaran'=>null,'verified_by'=>7,'verified_at'=>'2026-09-16 10:00:00']);
        $service = new BookingPaymentService($this->db);

        try {
            $service->assertManualAllocationAllowed(4, [['id'=>1,'nominal'=>500000]]);
            $this->fail('Alokasi BO baru harus ditolak setelah verifikasi.');
        } catch (DomainException $e) {
            $this->assertStringContainsString('sudah diverifikasi', $e->getMessage());
        }
        $this->expectException(DomainException::class);
        $service->assertMayDelete(40);
    }

    public function testMigrationSeedsCanonicalBookingFromTheSameMkdt(): void
    {
        $this->db->table('mkdt')->insertBatch([
            ['id_mkdt'=>356,'booking_fee'=>1500000,'booking_tgl'=>'2026-04-07','is_lunas'=>0],
            ['id_mkdt'=>371,'booking_fee'=>2000000,'booking_tgl'=>'2026-06-07','is_lunas'=>0],
        ]);
        $this->db->table('log_pembayaran')->insertBatch([
            ['id_pembayaran'=>639,'id_mkdt'=>356,'nominal'=>1500000,'tanggal_bayar'=>'2026-04-07','payment_type'=>'Booking'],
            ['id_pembayaran'=>1002,'id_mkdt'=>371,'nominal'=>2000000,'tanggal_bayar'=>'2026-09-13','payment_type'=>'Angsuran 1;'],
        ]);
        $this->db->table('log_pembayaran_detail')->insertBatch([
            ['id_pembayaran_detail'=>1011,'id_pembayaran'=>639,'id_keuangan_item_list'=>1,'nominal'=>1500000,'booking_is_installment'=>0],
            ['id_pembayaran_detail'=>1375,'id_pembayaran'=>1002,'id_keuangan_item_list'=>2,'nominal'=>2000000,'booking_is_installment'=>0],
        ]);

        $service = new BookingFeeMigrationService($this->db);
        $this->invokePrivate($service, 'seedExistingLinks');

        $link = $this->db->table('mkdt_booking_payment')->where('id_mkdt', 356)->get()->getRowArray();
        $unrelatedPayment = $this->db->table('log_pembayaran')->where('id_pembayaran', 1002)->get()->getRowArray();
        $this->assertSame(639, (int) $link['id_pembayaran']);
        $this->assertSame(371, (int) $unrelatedPayment['id_mkdt']);
        $this->assertSame('Angsuran 1;', $unrelatedPayment['payment_type']);
        $this->assertSame(0, $this->db->table('mkdt_booking_payment')->where('id_pembayaran', 1002)->countAllResults());
    }

    public function testMigrationKeepsLegacyBookingCandidateOwnedByTheSameMkdt(): void
    {
        $this->db->table('mkdt')->insert(['id_mkdt'=>163,'booking_fee'=>1000000,'booking_tgl'=>'2025-01-07','is_lunas'=>0]);
        $this->db->table('log_pembayaran')->insert([
            'id_pembayaran'=>88,'id_mkdt'=>163,'nominal'=>0,'tanggal_bayar'=>'2025-03-27','payment_type'=>'Booking',
        ]);
        $this->db->table('log_pembayaran_detail')->insert([
            'id_pembayaran_detail'=>525,'id_pembayaran'=>88,'id_keuangan_item_list'=>1,'nominal'=>0,'booking_is_installment'=>0,
        ]);

        $service = new BookingFeeMigrationService($this->db);
        $this->invokePrivate($service, 'seedExistingLinks');

        $link = $this->db->table('mkdt_booking_payment')->where('id_mkdt', 163)->get()->getRowArray();
        $this->assertSame(88, (int) $link['id_pembayaran']);
    }

    public function testSpecialAllocationCorrectionRejectsAReusedPaymentId(): void
    {
        $this->db->table('log_pembayaran')->insert([
            'id_pembayaran'=>987,'id_mkdt'=>999,'nominal'=>1000000,'tanggal_bayar'=>'2024-02-03','payment_type'=>'Booking',
        ]);
        $this->db->table('log_pembayaran_detail')->insert([
            'id_pembayaran_detail'=>1359,'id_pembayaran'=>987,'id_keuangan_item_list'=>1,'nominal'=>1000000,'booking_is_installment'=>0,
        ]);

        $service = new BookingFeeMigrationService($this->db);

        try {
            $this->invokePrivate($service, 'correctAllocation', [987, 'BB', 'Biaya Proses', 202]);
            $this->fail('Koreksi khusus harus ditolak ketika ID pembayaran dimiliki MKDT lain.');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('diharapkan milik MKDT 202', $e->getMessage());
        }

        $detail = $this->db->table('log_pembayaran_detail')->where('id_pembayaran_detail', 1359)->get()->getRowArray();
        $this->assertSame(1, (int) $detail['id_keuangan_item_list']);
    }

    public function testSpecialPaymentSplitRejectsAReusedPaymentId(): void
    {
        $this->db->table('log_pembayaran')->insert([
            'id_pembayaran'=>1001,'id_mkdt'=>362,'nominal'=>2500000,'tanggal_bayar'=>'2026-09-20','payment_type'=>'Booking',
        ]);
        $this->db->table('log_pembayaran_detail')->insert([
            'id_pembayaran_detail'=>1374,'id_pembayaran'=>1001,'id_keuangan_item_list'=>1,'nominal'=>1500000,'booking_is_installment'=>0,
        ]);

        $service = new BookingFeeMigrationService($this->db);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('diharapkan milik MKDT 404');
        $this->invokePrivate($service, 'splitPayment1001');
    }

    public function testSpecialAllocationCorrectionRejectsUnexpectedDetailShape(): void
    {
        $this->db->table('log_pembayaran')->insert([
            'id_pembayaran'=>863,'id_mkdt'=>279,'nominal'=>1000000,'tanggal_bayar'=>'2020-12-07','payment_type'=>'Booking',
        ]);
        $this->db->table('log_pembayaran_detail')->insertBatch([
            ['id_pembayaran_detail'=>1235,'id_pembayaran'=>863,'id_keuangan_item_list'=>1,'nominal'=>750000,'booking_is_installment'=>0],
            ['id_pembayaran_detail'=>1236,'id_pembayaran'=>863,'id_keuangan_item_list'=>1,'nominal'=>250000,'booking_is_installment'=>0],
        ]);

        $service = new BookingFeeMigrationService($this->db);

        try {
            $this->invokePrivate($service, 'correctAllocation', [863, 'UM', null, 279]);
            $this->fail('Koreksi khusus harus ditolak ketika bentuk detail tidak sesuai asumsi.');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('2 detail, 2 detail BO', $e->getMessage());
        }

        $unchanged = $this->db->table('log_pembayaran_detail')->where('id_pembayaran', 863)
            ->where('id_keuangan_item_list', 1)->countAllResults();
        $this->assertSame(2, $unchanged);
    }

    public function testMixedBookingErrorContainsMkdtAndPaymentContext(): void
    {
        $this->db->table('mkdt')->insert(['id_mkdt'=>6,'booking_fee'=>1500000,'booking_tgl'=>'2025-07-09','is_lunas'=>0]);
        $this->db->table('log_pembayaran')->insert([
            'id_pembayaran'=>60,'id_mkdt'=>6,'nominal'=>1500000,'tanggal_bayar'=>'2025-07-09','payment_type'=>'Booking',
        ]);
        $this->db->table('log_pembayaran_detail')->insertBatch([
            ['id_pembayaran_detail'=>60,'id_pembayaran'=>60,'id_keuangan_item_list'=>1,'nominal'=>1000000,'booking_is_installment'=>0],
            ['id_pembayaran_detail'=>61,'id_pembayaran'=>60,'id_keuangan_item_list'=>2,'nominal'=>500000,'booking_is_installment'=>0],
        ]);
        $this->db->table('mkdt_booking_payment')->insert(['id_mkdt'=>6,'id_pembayaran'=>60]);

        try {
            (new BookingPaymentService($this->db))->synchronize(6);
            $this->fail('Pembayaran booking campuran harus ditolak.');
        } catch (DomainException $e) {
            $this->assertStringContainsString('MKDT 6', $e->getMessage());
            $this->assertStringContainsString('ID pembayaran 60', $e->getMessage());
            $this->assertStringContainsString('2 detail dan 1 detail BO', $e->getMessage());
        }
    }

    public function testUnverifyClearsVerificationLogsHistoryUnlocksEditAndAllowsReverify(): void
    {
        $this->db->table('mkdt')->insert([
            'id_mkdt' => 7,
            'booking_fee' => 1500000,
            'booking_tgl' => '2025-07-09',
            'is_lunas' => 0,
            'status_mkdt' => 'Booking',
            'id_proyek' => 13,
        ]);
        $this->db->table('log_pembayaran')->insert([
            'id_pembayaran' => 70, 'id_mkdt' => 7, 'nominal' => 1500000, 'tanggal_bayar' => '2025-07-09',
            'payment_type' => 'Booking', 'keterangan' => 'Booking fee otomatis dari MKDT', 'is_deleted' => 0,
            'add_by' => 3, 'edit_by' => 3, 'created_at' => '2025-07-09 10:00:00', 'updated_at' => '2025-07-09 10:00:00',
        ]);
        $this->db->table('log_pembayaran_detail')->insert([
            'id_pembayaran_detail' => 70, 'id_pembayaran' => 70, 'id_keuangan_item_list' => 1,
            'nominal' => 1500000, 'booking_is_installment' => 0,
        ]);
        $this->db->table('mkdt_booking_payment')->insert([
            'id_mkdt' => 7, 'id_pembayaran' => 70, 'verified_by' => 5, 'verified_at' => '2025-07-10 09:00:00',
        ]);
        $this->db->table('finance_ledger')->insert([
            'direction' => 'income', 'source_type' => 'log_pembayaran', 'source_id' => 70, 'id_mkdt' => 7,
            'nominal' => 1500000, 'tanggal_transaksi' => '2025-07-09', 'label' => 'Booking',
            'status' => 'active', 'is_deleted' => 0, 'add_by' => 3, 'created_at' => '2025-07-09 10:00:00',
            'edit_by' => 3, 'updated_at' => '2025-07-09 10:00:00',
        ]);

        $service = new BookingPaymentService($this->db);

        try {
            $service->assertEditable(7, 2000000, '2025-07-11');
            $this->fail('Booking terverifikasi tidak boleh langsung diedit.');
        } catch (DomainException $e) {
            $this->assertStringContainsString('sudah diverifikasi', $e->getMessage());
        }

        $unverified = $service->unverify(7, 11, 'Nominal booking perlu dikoreksi ke 2 juta');
        $this->assertFalse($unverified['is_verified']);
        $this->assertFalse($unverified['locked']);
        $this->assertNull($unverified['verified_by']);
        $this->assertNull($unverified['verified_at']);
        $this->assertFalse($unverified['can_unverify']);
        $this->assertSame(70, $unverified['id_pembayaran']);

        $link = $this->db->table('mkdt_booking_payment')->where('id_mkdt', 7)->get()->getRowArray();
        $this->assertNull($link['verified_by']);
        $this->assertNull($link['verified_at']);
        $this->assertSame(70, (int) $link['id_pembayaran']);

        $logs = $this->db->table('history_log')->where('reference_id', 7)->get()->getResultArray();
        $this->assertCount(1, $logs);
        $this->assertSame('keuangan', $logs[0]['module']);
        $this->assertSame('batal_verifikasi_booking', $logs[0]['action']);
        $this->assertSame('mkdt_booking_payment', $logs[0]['reference_type']);
        $this->assertSame(13, (int) $logs[0]['id_proyek']);
        $this->assertSame(11, (int) $logs[0]['add_by']);
        $this->assertStringContainsString('Nominal booking perlu dikoreksi ke 2 juta', $logs[0]['summary']);

        $oldData = json_decode((string) $logs[0]['old_data'], true);
        $newData = json_decode((string) $logs[0]['new_data'], true);
        $metadata = json_decode((string) $logs[0]['metadata'], true);
        $this->assertSame(5, $oldData['verified_by']);
        $this->assertSame('2025-07-10 09:00:00', $oldData['verified_at']);
        $this->assertSame(1500000.0, (float) $oldData['booking_fee']);
        $this->assertSame(['verified_by' => null, 'verified_at' => null], $newData);
        $this->assertSame('Nominal booking perlu dikoreksi ke 2 juta', $metadata['reason']);

        // Edit lock is reopened and re-verification with corrected values succeeds.
        $service->assertEditable(7, 2000000, '2025-07-11');
        $reverified = $service->verify(7, 12, 2000000, '2025-07-11');
        $this->assertTrue($reverified['is_verified']);
        $this->assertSame(12, (int) $reverified['verified_by']);
        $this->assertSame(2000000.0, (float) $reverified['nominal']);
        $this->assertSame('2025-07-11', $reverified['tanggal']);
    }

    public function testUnverifyRejectsEmptyReasonAndUnverifiedRecord(): void
    {
        $this->db->table('mkdt')->insertBatch([
            ['id_mkdt' => 8, 'booking_fee' => 1500000, 'booking_tgl' => '2025-07-09', 'id_proyek' => 13],
            ['id_mkdt' => 88, 'booking_fee' => 1500000, 'booking_tgl' => '2025-07-09', 'id_proyek' => 13],
        ]);
        $this->db->table('mkdt_booking_payment')->insert([
            'id_mkdt' => 8, 'id_pembayaran' => 80, 'verified_by' => 5, 'verified_at' => '2025-07-10 09:00:00',
        ]);

        $service = new BookingPaymentService($this->db);

        foreach (['', '  ', 'ab'] as $shortReason) {
            try {
                $service->unverify(8, 11, $shortReason);
                $this->fail('Alasan kosong atau kurang dari 3 karakter harus ditolak.');
            } catch (DomainException $e) {
                $this->assertStringContainsString('minimal 3 karakter', $e->getMessage());
            }
        }

        try {
            $service->unverify(8, 11, str_repeat('a', 501));
            $this->fail('Alasan melebihi 500 karakter harus ditolak.');
        } catch (DomainException $e) {
            $this->assertStringContainsString('maksimal 500 karakter', $e->getMessage());
        }

        // Unverified record (no link row)
        try {
            $service->unverify(88, 11, 'Pembatalan record belum terverifikasi');
            $this->fail('MKDT tanpa verifikasi harus ditolak.');
        } catch (DomainException $e) {
            $this->assertStringContainsString('belum diverifikasi', $e->getMessage());
        }

        // Unverified record (link row exists with verified_at = null)
        $this->db->table('mkdt_booking_payment')->insert(['id_mkdt' => 88, 'id_pembayaran' => 880]);
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('belum diverifikasi');
        $service->unverify(88, 11, 'Pembatalan record dengan verified_at null');
    }

    public function testUnverifyBlocksCancelledAndRefundedMkdt(): void
    {
        $this->db->table('mkdt')->insertBatch([
            ['id_mkdt' => 91, 'booking_fee' => 1500000, 'booking_tgl' => '2025-07-09', 'status_mkdt' => 'Batal', 'is_batal' => 0, 'refund_paid' => 0, 'is_lunas' => 0, 'id_proyek' => 13],
            ['id_mkdt' => 92, 'booking_fee' => 1500000, 'booking_tgl' => '2025-07-09', 'status_mkdt' => 'Booking', 'is_batal' => 1, 'refund_paid' => 0, 'is_lunas' => 0, 'id_proyek' => 13],
            ['id_mkdt' => 93, 'booking_fee' => 1500000, 'booking_tgl' => '2025-07-09', 'status_mkdt' => 'Booking', 'is_batal' => 0, 'refund_paid' => 1, 'is_lunas' => 0, 'id_proyek' => 13],
            ['id_mkdt' => 94, 'booking_fee' => 1500000, 'booking_tgl' => '2025-07-09', 'status_mkdt' => 'Akad', 'is_batal' => 0, 'refund_paid' => 0, 'is_lunas' => 1, 'id_proyek' => 13],
        ]);
        $this->db->table('mkdt_booking_payment')->insertBatch([
            ['id_mkdt' => 91, 'id_pembayaran' => 910, 'verified_by' => 5, 'verified_at' => '2025-07-10 09:00:00'],
            ['id_mkdt' => 92, 'id_pembayaran' => 920, 'verified_by' => 5, 'verified_at' => '2025-07-10 09:00:00'],
            ['id_mkdt' => 93, 'id_pembayaran' => 930, 'verified_by' => 5, 'verified_at' => '2025-07-10 09:00:00'],
            ['id_mkdt' => 94, 'id_pembayaran' => 940, 'verified_by' => 5, 'verified_at' => '2025-07-10 09:00:00'],
        ]);

        $service = new BookingPaymentService($this->db);

        foreach ([91, 92, 93] as $blockedId) {
            try {
                $service->unverify($blockedId, 11, 'Mencoba batal verifikasi transaksi batal/refund');
                $this->fail("MKDT {$blockedId} harus diblokir dari batal verifikasi.");
            } catch (DomainException $e) {
                $this->assertStringContainsString('sudah dibatalkan atau refund sudah dibayarkan', $e->getMessage());
            }

            $link = $this->db->table('mkdt_booking_payment')->where('id_mkdt', $blockedId)->get()->getRowArray();
            $this->assertNotNull($link['verified_at']);
        }

        // Akad & lunas MUST remain permitted for unverification.
        $akadBooking = $service->getBooking(94);
        $this->assertNull($akadBooking['unverify_blocked_reason']);

        $unverifiedAkad = $service->unverify(94, 11, 'Koreksi tanggal booking sebelum audit');
        $this->assertFalse($unverifiedAkad['is_verified']);
        $this->assertNull($unverifiedAkad['verified_at']);
    }

    public function testUnverifyPromoReopensAllocationAndDeleteGuards(): void
    {
        $this->db->table('mkdt')->insert([
            'id_mkdt' => 95, 'booking_fee' => 0, 'booking_tgl' => '2025-08-01', 'is_lunas' => 0, 'id_proyek' => 13,
        ]);
        $this->db->table('log_pembayaran')->insert([
            'id_pembayaran' => 950, 'id_mkdt' => 95, 'nominal' => 1000000, 'tanggal_bayar' => '2025-08-01', 'payment_type' => 'Booking',
        ]);
        $this->db->table('log_pembayaran_detail')->insert([
            'id_pembayaran_detail' => 950, 'id_pembayaran' => 950, 'id_keuangan_item_list' => 1, 'nominal' => 1000000, 'booking_is_installment' => 1,
        ]);
        $this->db->table('mkdt_booking_payment')->insert([
            'id_mkdt' => 95, 'id_pembayaran' => null, 'verified_by' => 7, 'verified_at' => '2026-09-16 10:00:00',
        ]);

        $service = new BookingPaymentService($this->db);

        try {
            $service->assertManualAllocationAllowed(95, [['id' => 1, 'nominal' => 500000]]);
            $this->fail('Alokasi BO baru harus ditolak sebelum unverify.');
        } catch (DomainException $e) {
            $this->assertStringContainsString('sudah diverifikasi', $e->getMessage());
        }

        try {
            $service->assertMayDelete(950);
            $this->fail('Penghapusan pembayaran promo harus ditolak sebelum unverify.');
        } catch (DomainException $e) {
            $this->assertStringContainsString('sudah diverifikasi', $e->getMessage());
        }

        $unverified = $service->unverify(95, 11, 'Koreksi alokasi promo booking');
        $this->assertFalse($unverified['is_verified']);
        $this->assertTrue($unverified['is_promo']);

        // Guards reopen cleanly after unverify.
        $service->assertManualAllocationAllowed(95, [['id' => 1, 'nominal' => 500000]]);
        $service->assertMayDelete(950);

        $reverified = $service->verify(95, 12);
        $this->assertTrue($reverified['is_verified']);
    }

    public function testFindProjectIdByMkdtFallsBackToMkdtProjectForNup(): void
    {
        $this->db->table('mkdt')->insertBatch([
            ['id_mkdt' => 701, 'id_kavling' => null, 'booking_fee' => 1000000, 'booking_tgl' => '2025-09-01', 'id_proyek' => 13],
            ['id_mkdt' => 702, 'id_kavling' => null, 'booking_fee' => 1000000, 'booking_tgl' => '2025-09-01', 'id_proyek' => null],
        ]);

        $repo = new BookingPaymentRepository($this->db);
        $this->assertSame(13, $repo->findProjectIdByMkdt(701));
        $this->assertNull($repo->findProjectIdByMkdt(702));

        $p = $this->db->getPrefix();
        $this->db->query("CREATE TABLE {$p}cluster (id_cluster INTEGER PRIMARY KEY, id_proyek INTEGER)");
        $this->db->query("CREATE TABLE {$p}jalan (id_jalan INTEGER PRIMARY KEY, id_cluster INTEGER)");
        $this->db->query("CREATE TABLE {$p}kavling (id_kavling INTEGER PRIMARY KEY, id_jalan INTEGER)");
        $this->db->resetDataCache();

        $this->db->table('cluster')->insert(['id_cluster' => 5, 'id_proyek' => 15]);
        $this->db->table('jalan')->insert(['id_jalan' => 50, 'id_cluster' => 5]);
        $this->db->table('kavling')->insert(['id_kavling' => 500, 'id_jalan' => 50]);
        $this->db->table('mkdt')->insert([
            'id_mkdt' => 703, 'id_kavling' => 500, 'booking_fee' => 1000000, 'booking_tgl' => '2025-09-01', 'id_proyek' => 13,
        ]);

        // NUP without kavling still resolves from mkdt.id_proyek; kavling transaction resolves from cluster.id_proyek.
        $this->assertSame(13, $repo->findProjectIdByMkdt(701));
        $this->assertSame(15, $repo->findProjectIdByMkdt(703));
    }

    private function invokePrivate(object $service, string $method, array $arguments = []): mixed
    {
        $reflection = new ReflectionMethod($service, $method);
        $reflection->setAccessible(true);
        return $reflection->invokeArgs($service, $arguments);
    }
}
