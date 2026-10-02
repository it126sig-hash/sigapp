<?php

use App\Repositories\Keuangan\Cashout\CashoutKavlingRepo;
use App\Services\Keuangan\Cashout\CashoutKavlingService;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Database;

final class CashoutKavlingClassificationTest extends CIUnitTestCase
{
    private BaseConnection $testDb;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testDb = Database::connect([
            'DBDriver' => 'SQLite3',
            'database' => ':memory:',
            'DBPrefix' => '',
            'DBDebug' => true,
            'foreignKeys' => false,
        ], false);

        $this->testDb->query('CREATE TABLE proyek (id_proyek INTEGER PRIMARY KEY)');
        $this->testDb->query('CREATE TABLE cluster (id_cluster INTEGER PRIMARY KEY, id_proyek INTEGER)');
        $this->testDb->query('CREATE TABLE jalan (id_jalan INTEGER PRIMARY KEY, id_cluster INTEGER, nama_jalan TEXT)');
        $this->testDb->query('CREATE TABLE tipe (id_tipe INTEGER PRIMARY KEY, tipe_rumah TEXT, no_tipe_rumah TEXT)');
        $this->testDb->query('CREATE TABLE konsumen (id_konsumen INTEGER PRIMARY KEY, nama_konsumen TEXT)');
        $this->testDb->query('CREATE TABLE mkdt (id_mkdt INTEGER PRIMARY KEY, id_konsumen INTEGER, status_mkdt TEXT)');
        $this->testDb->query('CREATE TABLE kavling (id_kavling INTEGER PRIMARY KEY, id_mkdt INTEGER, id_jalan INTEGER, id_tipe INTEGER, no_kavling TEXT)');
        $this->testDb->query('CREATE TABLE list_cashout (id INTEGER PRIMARY KEY, item TEXT)');
        $this->testDb->query('CREATE TABLE cashout (id INTEGER PRIMARY KEY, id_kavling INTEGER, id_item_cashout INTEGER, nominal REAL, tanggal_bayar TEXT, keterangan TEXT, is_deleted INTEGER)');
        $this->testDb->query('CREATE TABLE finance_ledger (id INTEGER PRIMARY KEY, id_kavling INTEGER, direction TEXT, source_type TEXT, nominal REAL, tanggal_transaksi TEXT, label TEXT, keterangan TEXT, status TEXT, is_deleted INTEGER)');
        $this->testDb->query('CREATE TABLE referrals (id INTEGER PRIMARY KEY, id_mkdt_referred INTEGER, id_konsumen_referrer INTEGER, status TEXT)');
        $this->testDb->query('CREATE TABLE referral_bonus_stages (id INTEGER PRIMARY KEY, nama_tahapan TEXT)');
        $this->testDb->query('CREATE TABLE referral_bonuses (id INTEGER PRIMARY KEY, id_referral INTEGER, id_stage INTEGER, status TEXT, nominal_cair_keuangan REAL, tanggal_cair_keuangan TEXT, cair_keuangan_at TEXT, keterangan TEXT)');

        $this->testDb->table('proyek')->insert(['id_proyek' => 10]);
        $this->testDb->table('cluster')->insert(['id_cluster' => 20, 'id_proyek' => 10]);
        $this->testDb->table('jalan')->insert(['id_jalan' => 30, 'id_cluster' => 20, 'nama_jalan' => 'Blok A']);
        $this->testDb->table('tipe')->insert(['id_tipe' => 40, 'tipe_rumah' => '36', 'no_tipe_rumah' => '36']);
        $this->testDb->table('konsumen')->insertBatch([
            ['id_konsumen' => 1, 'nama_konsumen' => 'Pembeli'],
            ['id_konsumen' => 2, 'nama_konsumen' => 'Member'],
        ]);
        $this->testDb->table('mkdt')->insert(['id_mkdt' => 201, 'id_konsumen' => 1, 'status_mkdt' => 'Akad']);
        $this->testDb->table('kavling')->insert(['id_kavling' => 101, 'id_mkdt' => 201, 'id_jalan' => 30, 'id_tipe' => 40, 'no_kavling' => '1']);
        $this->testDb->table('list_cashout')->insertBatch([
            ['id' => 1, 'item' => 'BPHTB'],
            ['id' => 2, 'item' => 'PPh 4(2)'],
        ]);
        $this->testDb->table('cashout')->insertBatch([
            ['id' => 1, 'id_kavling' => 101, 'id_item_cashout' => 1, 'nominal' => 100, 'tanggal_bayar' => '2026-09-01', 'is_deleted' => 0],
            ['id' => 2, 'id_kavling' => 101, 'id_item_cashout' => 2, 'nominal' => 25, 'tanggal_bayar' => '2026-09-02', 'is_deleted' => 0],
            ['id' => 3, 'id_kavling' => 101, 'id_item_cashout' => 1, 'nominal' => 500, 'tanggal_bayar' => '2026-09-03', 'is_deleted' => 1],
        ]);
        $this->testDb->table('finance_ledger')->insertBatch([
            ['id' => 1, 'id_kavling' => 101, 'direction' => 'expense', 'source_type' => 'bayar_produksi', 'nominal' => 30, 'tanggal_transaksi' => '2026-09-04', 'label' => 'LPA', 'status' => 'active', 'is_deleted' => 0],
            ['id' => 2, 'id_kavling' => 101, 'direction' => 'expense', 'source_type' => 'cashout_subkon_allocation', 'nominal' => 40, 'tanggal_transaksi' => '2026-09-05', 'label' => 'Subkon', 'status' => 'active', 'is_deleted' => 0],
            ['id' => 3, 'id_kavling' => 101, 'direction' => 'expense', 'source_type' => 'pajak_pph42', 'nominal' => 50, 'tanggal_transaksi' => '2026-09-06', 'label' => 'PPh', 'status' => 'active', 'is_deleted' => 0],
            ['id' => 4, 'id_kavling' => 101, 'direction' => 'expense', 'source_type' => 'pajak_ppn', 'nominal' => 60, 'tanggal_transaksi' => '2026-09-07', 'label' => 'PPN', 'status' => 'active', 'is_deleted' => 0],
            ['id' => 5, 'id_kavling' => 101, 'direction' => 'expense', 'source_type' => 'bayar_produksi', 'nominal' => 200, 'tanggal_transaksi' => '2026-09-08', 'label' => 'Void', 'status' => 'void', 'is_deleted' => 0],
            ['id' => 6, 'id_kavling' => 101, 'direction' => 'expense', 'source_type' => 'cashout_subkon_allocation', 'nominal' => 300, 'tanggal_transaksi' => '2026-09-09', 'label' => 'Deleted', 'status' => 'active', 'is_deleted' => 1],
        ]);
        $this->testDb->table('referrals')->insert(['id' => 1, 'id_mkdt_referred' => 201, 'id_konsumen_referrer' => 2, 'status' => 'active']);
        $this->testDb->table('referral_bonus_stages')->insertBatch([
            ['id' => 1, 'nama_tahapan' => 'Booking'],
            ['id' => 2, 'nama_tahapan' => 'Akad'],
        ]);
        $this->testDb->table('referral_bonuses')->insertBatch([
            ['id' => 1, 'id_referral' => 1, 'id_stage' => 1, 'status' => 'cair', 'nominal_cair_keuangan' => 70, 'tanggal_cair_keuangan' => '2026-09-10', 'cair_keuangan_at' => '2026-09-10 12:00:00'],
            ['id' => 2, 'id_referral' => 1, 'id_stage' => 2, 'status' => 'diajukan_keuangan', 'nominal_cair_keuangan' => 90, 'tanggal_cair_keuangan' => null, 'cair_keuangan_at' => null],
        ]);
    }

    protected function tearDown(): void
    {
        $this->testDb->close();
        parent::tearDown();
    }

    public function testCashoutRowsIncludeOnlyRealizedCategories(): void
    {
        $repo = new CashoutKavlingRepo($this->testDb);
        $rows = $repo->getCashoutRowsByKavling(101);

        $this->assertSame(265.0, array_sum(array_map(static fn ($row) => (float) $row->nominal, $rows)));
        $departments = array_map(static fn ($row) => $row->departemen, $rows);
        sort($departments);
        $this->assertSame(['Keuangan', 'Keuangan', 'MGM', 'Produksi', 'Subkon'], $departments);
    }

    public function testRecapTotalExcludesTaxButShowsItsOwnColumn(): void
    {
        $repo = new CashoutKavlingRepo($this->testDb);
        $result = $repo->getDataTables(['id_proyek' => 10, 'draw' => 1, 'start' => 0, 'length' => 10]);
        $row = $result['rows'][0];

        $this->assertSame(125.0, (float) $row->total_cashout_keu);
        $this->assertSame(30.0, (float) $row->total_produksi);
        $this->assertSame(40.0, (float) $row->total_subkon);
        $this->assertSame(70.0, (float) $row->total_mgm);
        $this->assertSame(110.0, (float) $row->total_pajak);
    }

    public function testRecapAndDetailKeepTaxVisibleWithoutAddingItToCashout(): void
    {
        $repo = new CashoutKavlingRepo($this->testDb);
        $details = $repo->getDetailList(101);
        $byDepartment = [];
        foreach ($details as $detail) {
            $byDepartment[$detail->departemen] = ($byDepartment[$detail->departemen] ?? 0) + (float) $detail->nominal;
        }

        $this->assertSame(110.0, $byDepartment['Pajak']);
        $this->assertSame(70.0, $byDepartment['MGM']);

        $service = new CashoutKavlingService();
        $repoProperty = new ReflectionProperty($service, 'repo');
        $repoProperty->setValue($service, $repo);
        $response = $service->getDataTables(['id_proyek' => 10, 'draw' => 1, 'start' => 0, 'length' => 10]);
        $cells = $response['data'][0];

        $this->assertCount(10, $cells);
        $this->assertSame('70', $cells[7]);
        $this->assertStringContainsString('110', $cells[8]);
        $this->assertSame('<strong>265</strong>', $cells[9]);
    }
}
