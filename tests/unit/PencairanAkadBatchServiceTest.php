<?php

namespace Tests\Unit;

use App\Services\PencairanAkadService;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Database;

/**
 * @internal
 */
final class PencairanAkadBatchServiceTest extends CIUnitTestCase
{
    private PencairanAkadService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $db = Database::connect();
        $tableName = $db->prefixTable('pencairan_akad_batch');
        $db->query("CREATE TABLE IF NOT EXISTS {$tableName} (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            nomor_batch VARCHAR(50),
            tanggal_pengajuan DATE,
            tanggal_rencana_cair DATE,
            catatan TEXT,
            lampiran_surat VARCHAR(255),
            total_pengajuan DECIMAL(15,2) DEFAULT 0,
            total_cair DECIMAL(15,2) DEFAULT 0,
            status VARCHAR(20) DEFAULT 'active',
            void_reason TEXT,
            add_by INTEGER,
            edit_by INTEGER,
            created_at DATETIME,
            updated_at DATETIME
        )");

        $this->service = new PencairanAkadService();
    }

    public function testGenerateBatchNumberFormat(): void
    {
        $batchNo = $this->service->generateBatchNumber();
        $expectedPrefix = 'PA-' . date('Ym') . '-';

        $this->assertStringStartsWith($expectedPrefix, $batchNo);
        $this->assertMatchesRegularExpression('/^PA-\d{6}-\d{4}$/', $batchNo);
    }

    public function testStoreBatchPengajuanRejectsEmptyKavlings(): void
    {
        $result = $this->service->storeBatchPengajuan([
            'kavlings' => [],
            'tanggal_pengajuan' => '2026-10-14',
        ], null, 1);

        $this->assertFalse($result['success']);
        $this->assertSame('Pilih minimal satu kavling untuk diajukan', $result['message']);
    }

    public function testStoreBatchPengajuanRejectsMissingTanggalPengajuan(): void
    {
        $result = $this->service->storeBatchPengajuan([
            'kavlings' => [
                ['id_kavling' => 1, 'id_mkdt' => 1, 'items' => [1]],
            ],
            'tanggal_pengajuan' => '',
        ], null, 1);

        $this->assertFalse($result['success']);
        $this->assertSame('Tanggal pengajuan harus diisi', $result['message']);
    }

    public function testCairkanBatchNonExistentBatch(): void
    {
        $result = $this->service->cairkanBatch([
            'id_batch' => 999999,
            'mode' => 'all',
            'tanggal_cair' => '2026-10-14',
            'cara_bayar' => 'transfer',
            'id_rekening' => 1,
        ], 1);

        $this->assertFalse($result['success']);
        $this->assertSame('Batch pengajuan tidak ditemukan', $result['message']);
    }

    public function testVoidBatchNonExistentBatch(): void
    {
        $result = $this->service->voidBatch(999999, 'Alasan test', 1);

        $this->assertFalse($result['success']);
        $this->assertSame('Batch pengajuan tidak ditemukan', $result['message']);
    }
}
