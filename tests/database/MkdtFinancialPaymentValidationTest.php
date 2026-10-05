<?php

use App\Services\MkdtFinancialBreakdownService;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Database;

final class MkdtFinancialPaymentValidationTest extends CIUnitTestCase
{
    protected $db;
    private MkdtFinancialBreakdownService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->db = Database::connect('tests', true);
        $p = $this->db->getPrefix();
        foreach (['log_pembayaran_detail', 'log_pembayaran', 'keuangan_item_list', 'keuangan', 'mkdt'] as $table) {
            $this->db->query("DROP TABLE IF EXISTS {$p}{$table}");
        }
        $this->db->query("CREATE TABLE {$p}mkdt (
            id_mkdt INTEGER PRIMARY KEY, is_lunas INTEGER DEFAULT 0,
            harga_uang_muka REAL DEFAULT 0, harga_diskon_uang_muka REAL DEFAULT 0, harga_sbum REAL DEFAULT 0,
            harga_administrasi REAL DEFAULT 0, harga_bphtb REAL DEFAULT 0, harga_biaya_proses REAL DEFAULT 0,
            harga_ppn REAL DEFAULT 0, harga_penambahan REAL DEFAULT 0, harga_penambahan_tanah REAL DEFAULT 0,
            harga_penambahan_um REAL DEFAULT 0, harga_kpr REAL DEFAULT 0, harga_kpr_acc REAL DEFAULT 0
        )");
        $this->db->query("CREATE TABLE {$p}keuangan (id_keuangan INTEGER PRIMARY KEY, id_mkdt INTEGER, nominal REAL, is_void INTEGER DEFAULT 0)");
        $this->db->query("CREATE TABLE {$p}keuangan_item_list (id_keuangan_item_list INTEGER PRIMARY KEY, item TEXT, kategori TEXT, deleted_at TEXT)");
        $this->db->query("CREATE TABLE {$p}log_pembayaran (id_pembayaran INTEGER PRIMARY KEY, id_mkdt INTEGER, nominal REAL, payment_type TEXT, is_deleted INTEGER DEFAULT 0)");
        $this->db->query("CREATE TABLE {$p}log_pembayaran_detail (id_pembayaran_detail INTEGER PRIMARY KEY, id_pembayaran INTEGER, id_keuangan_item_list INTEGER, nominal REAL, booking_is_installment INTEGER DEFAULT 0)");
        $this->db->table('keuangan_item_list')->insertBatch([
            ['id_keuangan_item_list' => 1, 'item' => 'Booking', 'kategori' => 'BO'],
            ['id_keuangan_item_list' => 2, 'item' => 'Uang Muka', 'kategori' => 'UM'],
            ['id_keuangan_item_list' => 7, 'item' => 'Biaya Proses', 'kategori' => 'BB'],
        ]);
        $this->db->table('mkdt')->insert([
            'id_mkdt' => 1, 'harga_uang_muka' => 3_000_000, 'harga_biaya_proses' => 2_000_000,
        ]);
        $this->db->table('keuangan')->insert(['id_keuangan' => 1, 'id_mkdt' => 1, 'nominal' => 5_000_000]);
        $this->service = new MkdtFinancialBreakdownService($this->db);
    }

    public function testGenericInstallmentMayBeBrokenDownIntoMultipleItems(): void
    {
        $discrepancies = $this->service->validatePaymentAllocation(1, [
            ['id' => 2, 'nominal' => 3_000_000],
            ['id' => 7, 'nominal' => 2_000_000],
        ], 5_000_000);
        $this->assertEmpty($discrepancies);
    }

    public function testHeaderAndDetailMustBalance(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Jumlah breakdown');
        $this->service->validatePaymentAllocation(1, [['id' => 2, 'nominal' => 2_000_000]], 3_000_000);
    }

    public function testAllocationCannotExceedItemTarget(): void
    {
        $discrepancies = $this->service->validatePaymentAllocation(1, [['id' => 2, 'nominal' => 3_500_000]], 3_500_000);
        $this->assertNotEmpty($discrepancies);
        $this->assertStringContainsString('melebihi sisa target', $discrepancies[0]);
    }

    public function testDeletedAndRefundedPaymentsDoNotConsumeTarget(): void
    {
        $this->db->table('log_pembayaran')->insertBatch([
            ['id_pembayaran' => 10, 'id_mkdt' => 1, 'nominal' => 3_000_000, 'payment_type' => 'Uang Muka', 'is_deleted' => 1],
            ['id_pembayaran' => 11, 'id_mkdt' => 1, 'nominal' => 3_000_000, 'payment_type' => 'Refund', 'is_deleted' => 0],
        ]);
        $this->db->table('log_pembayaran_detail')->insertBatch([
            ['id_pembayaran_detail' => 10, 'id_pembayaran' => 10, 'id_keuangan_item_list' => 2, 'nominal' => 3_000_000],
            ['id_pembayaran_detail' => 11, 'id_pembayaran' => 11, 'id_keuangan_item_list' => 2, 'nominal' => 3_000_000],
        ]);

        $discrepancies = $this->service->validatePaymentAllocation(1, [['id' => 2, 'nominal' => 3_000_000]], 3_000_000);
        $this->assertEmpty($discrepancies);
    }

    public function testTargetMustExistBeforePayment(): void
    {
        $this->db->table('mkdt')->where('id_mkdt', 1)->update(['harga_biaya_proses' => 0]);
        $discrepancies = $this->service->validatePaymentAllocation(1, [['id' => 7, 'nominal' => 1_000_000]], 1_000_000);
        $this->assertNotEmpty($discrepancies);
        $this->assertStringContainsString('belum tersedia (Rp 0)', $discrepancies[0]);
    }
}
