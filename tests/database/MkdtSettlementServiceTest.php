<?php

use App\Database\Migrations\SynchronizeMkdtSettlementStatus;
use App\Services\MkdtSettlementService;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Database;

require_once APPPATH . 'Database/Migrations/2026-09-22-000001_SynchronizeMkdtSettlementStatus.php';

final class MkdtSettlementServiceTest extends CIUnitTestCase
{
    protected $db;

    protected function setUp(): void
    {
        parent::setUp();
        $this->db = Database::connect('tests', true);
        $prefix = $this->db->getPrefix();

        foreach (['keuangan', 'log_pembayaran_detail', 'log_pembayaran', 'keuangan_item_list', 'mkdt'] as $table) {
            $this->db->query("DROP TABLE IF EXISTS {$prefix}{$table}");
        }

        $this->db->query("CREATE TABLE {$prefix}mkdt (
            id_mkdt INTEGER PRIMARY KEY,
            is_lunas INTEGER NOT NULL DEFAULT 0
        )");
        $this->db->query("CREATE TABLE {$prefix}keuangan (
            id_keuangan INTEGER PRIMARY KEY,
            id_mkdt INTEGER NOT NULL,
            nominal REAL NOT NULL,
            is_void INTEGER NOT NULL DEFAULT 0
        )");
        $this->db->query("CREATE TABLE {$prefix}log_pembayaran (
            id_pembayaran INTEGER PRIMARY KEY,
            id_mkdt INTEGER NOT NULL,
            nominal REAL NOT NULL,
            payment_type TEXT,
            is_deleted INTEGER NOT NULL DEFAULT 0
        )");
        $this->db->query("CREATE TABLE {$prefix}log_pembayaran_detail (
            id_pembayaran_detail INTEGER PRIMARY KEY,
            id_pembayaran INTEGER NOT NULL,
            id_keuangan_item_list INTEGER NOT NULL,
            nominal REAL NOT NULL,
            booking_is_installment INTEGER NOT NULL DEFAULT 0
        )");
        $this->db->query("CREATE TABLE {$prefix}keuangan_item_list (
            id_keuangan_item_list INTEGER PRIMARY KEY,
            item TEXT NOT NULL,
            kategori TEXT NOT NULL,
            deleted_at TEXT
        )");
        $this->db->table('keuangan_item_list')->insertBatch([
            ['id_keuangan_item_list' => 1, 'item' => 'Booking', 'kategori' => 'BO'],
            ['id_keuangan_item_list' => 2, 'item' => 'Uang Muka', 'kategori' => 'UM'],
        ]);
        $this->db->resetDataCache();
    }

    protected function tearDown(): void
    {
        $this->db->resetDataCache();
        parent::tearDown();
    }

    public function testSettlementRequiresPositiveBillsAndEnoughValidPayments(): void
    {
        $this->db->table('mkdt')->insertBatch([
            ['id_mkdt' => 1, 'is_lunas' => 1],
            ['id_mkdt' => 2, 'is_lunas' => 1],
            ['id_mkdt' => 3, 'is_lunas' => 0],
            ['id_mkdt' => 4, 'is_lunas' => 0],
        ]);
        $this->db->table('keuangan')->insertBatch([
            ['id_keuangan' => 2, 'id_mkdt' => 2, 'nominal' => 100, 'is_void' => 0],
            ['id_keuangan' => 3, 'id_mkdt' => 3, 'nominal' => 100, 'is_void' => 0],
            ['id_keuangan' => 4, 'id_mkdt' => 4, 'nominal' => 100, 'is_void' => 0],
        ]);
        $this->insertAllocatedPayment(20, 2, 50);
        $this->insertAllocatedPayment(30, 3, 100);
        $this->insertAllocatedPayment(40, 4, 150);

        $service = new MkdtSettlementService($this->db);

        $this->assertSame(0, $service->synchronize(1)['is_lunas']);
        $this->assertSame(0, $service->synchronize(2)['is_lunas']);
        $this->assertSame(1, $service->synchronize(3)['is_lunas']);
        $this->assertSame(1, $service->synchronize(4)['is_lunas']);
        $this->assertFalse($service->synchronize(4)['changed']);
    }

    public function testSettlementUsesCanonicalBookingRefundAndLegacyRules(): void
    {
        $this->db->table('mkdt')->insert(['id_mkdt' => 10, 'is_lunas' => 0]);
        $this->db->table('keuangan')->insert(['id_keuangan' => 10, 'id_mkdt' => 10, 'nominal' => 100, 'is_void' => 0]);
        $this->db->table('log_pembayaran')->insertBatch([
            ['id_pembayaran' => 101, 'id_mkdt' => 10, 'nominal' => 100, 'payment_type' => 'Booking', 'is_deleted' => 0],
            ['id_pembayaran' => 102, 'id_mkdt' => 10, 'nominal' => 100, 'payment_type' => 'Refund;', 'is_deleted' => 0],
            ['id_pembayaran' => 103, 'id_mkdt' => 10, 'nominal' => 100, 'payment_type' => 'UM', 'is_deleted' => 1],
            ['id_pembayaran' => 104, 'id_mkdt' => 10, 'nominal' => 60, 'payment_type' => 'Booking', 'is_deleted' => 0],
            ['id_pembayaran' => 105, 'id_mkdt' => 10, 'nominal' => 40, 'payment_type' => 'Uang Muka;', 'is_deleted' => 0],
        ]);
        $this->db->table('log_pembayaran_detail')->insertBatch([
            ['id_pembayaran_detail' => 101, 'id_pembayaran' => 101, 'id_keuangan_item_list' => 1, 'nominal' => 100, 'booking_is_installment' => 0],
            ['id_pembayaran_detail' => 102, 'id_pembayaran' => 102, 'id_keuangan_item_list' => 2, 'nominal' => 100, 'booking_is_installment' => 0],
            ['id_pembayaran_detail' => 103, 'id_pembayaran' => 103, 'id_keuangan_item_list' => 2, 'nominal' => 100, 'booking_is_installment' => 0],
            ['id_pembayaran_detail' => 104, 'id_pembayaran' => 104, 'id_keuangan_item_list' => 1, 'nominal' => 60, 'booking_is_installment' => 1],
        ]);

        $result = (new MkdtSettlementService($this->db))->synchronize(10);

        $this->assertSame(100.0, $result['sudah_bayar']);
        $this->assertSame(1, $result['is_lunas']);
    }

    public function testSettlementMovesBothDirectionsAfterPaymentOrBillChanges(): void
    {
        $this->db->table('mkdt')->insert(['id_mkdt' => 20, 'is_lunas' => 0]);
        $this->db->table('keuangan')->insert(['id_keuangan' => 20, 'id_mkdt' => 20, 'nominal' => 100, 'is_void' => 0]);
        $this->insertAllocatedPayment(200, 20, 100);
        $service = new MkdtSettlementService($this->db);

        $this->assertSame(1, $service->synchronize(20)['is_lunas']);

        $this->db->table('log_pembayaran')->where('id_pembayaran', 200)->update(['is_deleted' => 1]);
        $this->assertSame(0, $service->synchronize(20)['is_lunas']);

        $this->db->table('log_pembayaran')->where('id_pembayaran', 200)->update(['is_deleted' => 0]);
        $this->db->table('keuangan')->where('id_keuangan', 20)->update(['is_void' => 1]);
        $this->assertSame(0, $service->synchronize(20)['is_lunas']);

        $this->db->table('keuangan')->where('id_keuangan', 20)->update(['is_void' => 0]);
        $this->assertSame(1, $service->synchronize(20)['is_lunas']);
    }

    public function testBackfillRepairsBothDirectionsAndIsIdempotent(): void
    {
        $this->db->table('mkdt')->insertBatch([
            ['id_mkdt' => 31, 'is_lunas' => 0],
            ['id_mkdt' => 32, 'is_lunas' => 1],
            ['id_mkdt' => 33, 'is_lunas' => 1],
        ]);
        $this->db->table('keuangan')->insertBatch([
            ['id_keuangan' => 31, 'id_mkdt' => 31, 'nominal' => 100, 'is_void' => 0],
            ['id_keuangan' => 32, 'id_mkdt' => 32, 'nominal' => 100, 'is_void' => 0],
        ]);
        $this->insertAllocatedPayment(310, 31, 100);
        $this->insertAllocatedPayment(320, 32, 50);

        $migration = new SynchronizeMkdtSettlementStatus(Database::forge($this->db));
        $migration->up();
        $migration->up();

        $rows = $this->db->table('mkdt')->select('id_mkdt, is_lunas')->orderBy('id_mkdt')->get()->getResultArray();
        $this->assertEquals([
            ['id_mkdt' => 31, 'is_lunas' => 1],
            ['id_mkdt' => 32, 'is_lunas' => 0],
            ['id_mkdt' => 33, 'is_lunas' => 0],
        ], $rows);
        $this->assertArrayHasKey('idx_log_pembayaran_mkdt_deleted', $this->db->getIndexData('log_pembayaran'));
        $this->assertArrayHasKey('idx_keuangan_mkdt_void', $this->db->getIndexData('keuangan'));
    }

    private function insertAllocatedPayment(int $idPayment, int $idMkdt, float $nominal): void
    {
        $this->db->table('log_pembayaran')->insert([
            'id_pembayaran' => $idPayment,
            'id_mkdt' => $idMkdt,
            'nominal' => $nominal,
            'payment_type' => 'Uang Muka',
            'is_deleted' => 0,
        ]);
        $this->db->table('log_pembayaran_detail')->insert([
            'id_pembayaran_detail' => $idPayment,
            'id_pembayaran' => $idPayment,
            'id_keuangan_item_list' => 2,
            'nominal' => $nominal,
            'booking_is_installment' => 0,
        ]);
    }
}
