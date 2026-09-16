<?php

use App\Repositories\BookingPaymentRepository;
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
        foreach (['finance_ledger','mkdt_booking_payment','mkdt_payment_summary','keuangan','log_pembayaran_detail','log_pembayaran','keuangan_item_list','mkdt'] as $t) {
            $this->db->query("DROP TABLE IF EXISTS {$p}{$t}");
        }
        $this->db->query("CREATE TABLE {$p}mkdt (id_mkdt INTEGER PRIMARY KEY, id_kavling INTEGER, booking_fee REAL, booking_tgl TEXT, is_lunas INTEGER DEFAULT 0)");
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
}
