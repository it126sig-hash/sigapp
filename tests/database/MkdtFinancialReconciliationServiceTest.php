<?php

use App\Services\MkdtFinancialReconciliationService;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Database;

final class MkdtFinancialReconciliationServiceTest extends CIUnitTestCase
{
    protected $db;
    private MkdtFinancialReconciliationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->db = Database::connect('tests', true);
        $p = $this->db->getPrefix();
        foreach (['history_log', 'mkdt'] as $table) {
            $this->db->query("DROP TABLE IF EXISTS {$p}{$table}");
        }
        $this->db->query("CREATE TABLE {$p}mkdt (
            id_mkdt INTEGER PRIMARY KEY, id_kavling INTEGER, harga_biaya_proses REAL DEFAULT 0,
            harga_penambahan_um REAL DEFAULT 0, updated_at TEXT, edit_by INTEGER
        )");
        $this->db->query("CREATE TABLE {$p}history_log (
            id INTEGER PRIMARY KEY AUTOINCREMENT, module TEXT, reference_type TEXT, reference_id INTEGER,
            id_kavling INTEGER, action TEXT, summary TEXT, old_data TEXT, new_data TEXT,
            metadata TEXT, add_by INTEGER, created_at TEXT
        )");
        $this->db->resetDataCache();
        $this->db->table('mkdt')->insertBatch([
            ['id_mkdt' => 354, 'id_kavling' => 99, 'harga_biaya_proses' => 0, 'harga_penambahan_um' => 159_500_000, 'updated_at' => '2026-09-10 16:53:33'],
            ['id_mkdt' => 355, 'id_kavling' => 100, 'harga_biaya_proses' => 0, 'harga_penambahan_um' => 0, 'updated_at' => '2026-09-11 10:00:00'],
        ]);
        $this->service = new MkdtFinancialReconciliationService($this->db);
    }

    public function testApplyIsLoggedAndIdempotent(): void
    {
        $manifest = ['rows' => [[
            'id_mkdt' => 354,
            'expected_updated_at' => '2026-09-10 16:53:33',
            'before' => ['harga_biaya_proses' => 0, 'harga_penambahan_um' => 159_500_000],
            'changes' => ['harga_biaya_proses' => 3_000_000, 'harga_penambahan_um' => 0],
            'reason' => 'approved test',
        ]]];

        $first = $this->service->apply($manifest, 7);
        $second = $this->service->apply($manifest, 7);
        $row = $this->db->table('mkdt')->where('id_mkdt', 354)->get()->getRowArray();

        $this->assertCount(1, $first['applied']);
        $this->assertCount(1, $second['skipped']);
        $this->assertSame(3_000_000.0, (float) $row['harga_biaya_proses']);
        $this->assertSame(0.0, (float) $row['harga_penambahan_um']);
        $this->assertSame(1, $this->db->table('history_log')->where('reference_id', 354)->countAllResults());
    }

    public function testStaleManifestIsRejectedWithoutWrite(): void
    {
        $manifest = ['rows' => [[
            'id_mkdt' => 355,
            'expected_updated_at' => '2026-09-01 00:00:00',
            'before' => ['harga_biaya_proses' => 0],
            'changes' => ['harga_biaya_proses' => 1_000_000],
        ]]];

        try {
            $this->service->apply($manifest, 7);
            $this->fail('Manifest basi harus ditolak.');
        } catch (DomainException $e) {
            $this->assertStringContainsString('basi', $e->getMessage());
        }

        $row = $this->db->table('mkdt')->where('id_mkdt', 355)->get()->getRowArray();
        $this->assertSame(0.0, (float) $row['harga_biaya_proses']);
        $this->assertSame(0, $this->db->table('history_log')->countAllResults());
    }
}
