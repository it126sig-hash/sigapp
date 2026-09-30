<?php

use App\Database\Migrations\BackfillMkdtPaymentSummaryBooking;
use App\Repositories\LogPembayaranRepository;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Database;

require_once APPPATH . 'Database/Migrations/2026-09-14-000001_BackfillMkdtPaymentSummaryBooking.php';

final class BookingPaymentAggregationTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->db = Database::connect('tests', true);
        $prefix = $this->db->getPrefix();

        foreach (['log_pembayaran_detail', 'log_pembayaran', 'keuangan_item_list', 'mkdt_payment_summary'] as $table) {
            $this->db->query("DROP TABLE IF EXISTS {$prefix}{$table}");
        }

        $this->db->query("CREATE TABLE {$prefix}log_pembayaran (
            id_pembayaran INTEGER PRIMARY KEY,
            id_mkdt INTEGER NOT NULL,
            nominal REAL NOT NULL,
            payment_type TEXT,
            is_deleted INTEGER NOT NULL DEFAULT 0,
            created_at TEXT,
            updated_at TEXT
        )");
        $this->db->query("CREATE TABLE {$prefix}log_pembayaran_detail (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            id_pembayaran INTEGER NOT NULL,
            id_keuangan_item_list INTEGER NOT NULL,
            nominal REAL NOT NULL
        )");
        $this->db->query("CREATE TABLE {$prefix}keuangan_item_list (
            id_keuangan_item_list INTEGER PRIMARY KEY,
            item TEXT NOT NULL,
            kategori TEXT NOT NULL
        )");
        $this->db->query("CREATE TABLE {$prefix}mkdt_payment_summary (
            id_mkdt INTEGER PRIMARY KEY,
            total_um REAL DEFAULT 0,
            total_bb REAL DEFAULT 0,
            total_adm REAL DEFAULT 0,
            total_booking REAL DEFAULT 0,
            updated_at TEXT
        )");

        $this->db->table('keuangan_item_list')->insertBatch([
            ['id_keuangan_item_list' => 1, 'item' => 'Booking', 'kategori' => 'BO'],
            ['id_keuangan_item_list' => 2, 'item' => 'Uang Muka', 'kategori' => 'UM'],
        ]);
    }

    public function testTotalAndBreakdownIncludeActiveBookingButExcludeDeletedPayments(): void
    {
        $this->db->table('log_pembayaran')->insertBatch([
            ['id_pembayaran' => 1, 'id_mkdt' => 356, 'nominal' => 1500000, 'payment_type' => 'Booking', 'is_deleted' => 0],
            ['id_pembayaran' => 2, 'id_mkdt' => 356, 'nominal' => 1000000, 'payment_type' => 'Uang Muka;', 'is_deleted' => 0],
            ['id_pembayaran' => 3, 'id_mkdt' => 356, 'nominal' => 500000, 'payment_type' => 'Booking', 'is_deleted' => 1],
            ['id_pembayaran' => 4, 'id_mkdt' => 357, 'nominal' => 750000, 'payment_type' => 'Booking', 'is_deleted' => 0],
        ]);
        $this->db->table('log_pembayaran_detail')->insertBatch([
            ['id_pembayaran' => 1, 'id_keuangan_item_list' => 1, 'nominal' => 1500000],
            ['id_pembayaran' => 2, 'id_keuangan_item_list' => 2, 'nominal' => 1000000],
            ['id_pembayaran' => 3, 'id_keuangan_item_list' => 1, 'nominal' => 500000],
        ]);

        $repository = new LogPembayaranRepository($this->db);

        $this->assertSame(2500000.0, $repository->getTotalBayarByIdMkdt(356));
        $this->assertSame(750000.0, $repository->getTotalBayarByIdMkdt(357));

        $breakdown = $repository->getPaidItemSummaryByIdMkdt(356);
        $this->assertCount(2, $breakdown);
        $this->assertSame('BO', $breakdown[0]['kategori']);
        $this->assertEquals(1500000.0, $breakdown[0]['total_nominal']);
        $this->assertSame('UM', $breakdown[1]['kategori']);
        $this->assertEquals(1000000.0, $breakdown[1]['total_nominal']);
    }

    public function testBackfillRecalculatesBookingSummaryAndIsIdempotent(): void
    {
        $this->db->table('log_pembayaran')->insertBatch([
            ['id_pembayaran' => 1, 'id_mkdt' => 356, 'nominal' => 1500000, 'payment_type' => 'Booking', 'is_deleted' => 0],
            ['id_pembayaran' => 2, 'id_mkdt' => 356, 'nominal' => 500000, 'payment_type' => 'Booking', 'is_deleted' => 1],
            ['id_pembayaran' => 3, 'id_mkdt' => 357, 'nominal' => 750000, 'payment_type' => 'Booking;', 'is_deleted' => 0],
        ]);
        $this->db->table('log_pembayaran_detail')->insertBatch([
            ['id_pembayaran' => 1, 'id_keuangan_item_list' => 1, 'nominal' => 1500000],
            ['id_pembayaran' => 2, 'id_keuangan_item_list' => 1, 'nominal' => 500000],
        ]);
        $this->db->table('mkdt_payment_summary')->insertBatch([
            ['id_mkdt' => 356, 'total_booking' => 0],
            ['id_mkdt' => 358, 'total_booking' => 400000],
        ]);

        $migration = new BackfillMkdtPaymentSummaryBooking(Database::forge($this->db));
        $migration->up();
        $migration->up();

        $summaries = $this->db->table('mkdt_payment_summary')
            ->select('id_mkdt, total_booking')
            ->orderBy('id_mkdt', 'ASC')
            ->get()
            ->getResultArray();

        $this->assertEquals([
            ['id_mkdt' => 356, 'total_booking' => 1500000.0],
            ['id_mkdt' => 357, 'total_booking' => 750000.0],
            ['id_mkdt' => 358, 'total_booking' => 0.0],
        ], $summaries);
    }
}
