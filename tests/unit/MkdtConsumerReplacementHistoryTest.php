<?php

use App\Repositories\TransaksiRepository;
use App\Services\FileAccessService;
use App\Services\HistoryService;
use App\Services\MkdtHistoryService;
use CodeIgniter\Test\CIUnitTestCase;

final class MkdtConsumerReplacementHistoryTest extends CIUnitTestCase
{
    public function testHistoryCombinesNewSnapshotsAndLegacyRowsChronologically(): void
    {
        $historyService = $this->createMock(HistoryService::class);
        $historyService->method('getByReferenceAction')->willReturn([
            (object) [
                'id' => 7,
                'username' => 'mkdt-user',
                'created_at' => '2026-02-01 10:00:00',
                'summary' => 'Konsumen Lama ke Konsumen Baru',
                'old_data' => [
                    'spptb_data' => [
                        'id_mkdt' => 20,
                        'id_konsumen' => 12,
                        'nama_konsumen' => 'Konsumen Lama',
                        'no_spptb' => 'SPPTB-001',
                        'file_spptb' => 'uploads/spptb/lama.pdf',
                    ],
                ],
            ],
        ]);

        $transaksiRepository = $this->createMock(TransaksiRepository::class);
        $transaksiRepository->method('getSpptbData')->willReturn((object) ['id_mkdt' => 20]);
        $transaksiRepository->method('getLegacyReplacementSpptbData')->willReturn([
            (object) [
                'id_mkdt' => 10,
                'id_konsumen' => 5,
                'nama_konsumen' => 'Konsumen Legacy',
                'no_spptb' => 'SPPTB-LEGACY',
                'file_spptb' => 'uploads/spptb/legacy.pdf',
                'created_at' => '2025-01-01 09:00:00',
            ],
        ]);

        $fileAccessService = $this->createMock(FileAccessService::class);
        $fileAccessService->method('pathUrl')->willReturnCallback(
            static fn (string $source, string $path): string => "path://{$source}/{$path}"
        );
        $fileAccessService->method('accessUrl')->willReturnCallback(
            static fn (string $source, int $id): string => "id://{$source}/{$id}"
        );

        $service = new MkdtHistoryService($historyService, $transaksiRepository, $fileAccessService);
        $result = $service->getConsumerReplacementHistory(20, 30);

        $this->assertCount(2, $result);
        $this->assertSame('Konsumen Legacy', $result[0]['nama_konsumen']);
        $this->assertSame('SPPTB-LEGACY', $result[0]['no_spptb']);
        $this->assertSame('id://mkdt_file_spptb/10', $result[0]['file_spptb_access_url']);
        $this->assertSame('Konsumen Lama', $result[1]['nama_konsumen']);
        $this->assertSame('SPPTB-001', $result[1]['no_spptb']);
        $this->assertSame('path://mkdt_file_spptb/uploads/spptb/lama.pdf', $result[1]['file_spptb_access_url']);
        $this->assertSame('mkdt-user', $result[1]['changed_by']);
        $this->assertArrayNotHasKey('sort_at', $result[1]);
    }

    public function testHistoryKeepsReplacementWithoutSignedSpptbVisible(): void
    {
        $historyService = $this->createMock(HistoryService::class);
        $historyService->method('getByReferenceAction')->willReturn([
            (object) [
                'id' => 8,
                'created_at' => '2026-03-01 10:00:00',
                'old_data' => [
                    'spptb_data' => [
                        'id_mkdt' => 20,
                        'id_konsumen' => 13,
                        'nama_konsumen' => 'Tanpa File',
                        'no_spptb' => 'SPPTB-002',
                        'file_spptb' => null,
                    ],
                ],
            ],
        ]);

        $transaksiRepository = $this->createMock(TransaksiRepository::class);
        $transaksiRepository->method('getSpptbData')->willReturn((object) ['id_mkdt' => 20]);
        $transaksiRepository->method('getLegacyReplacementSpptbData')->willReturn([]);
        $fileAccessService = $this->createMock(FileAccessService::class);

        $service = new MkdtHistoryService($historyService, $transaksiRepository, $fileAccessService);
        $result = $service->getConsumerReplacementHistory(20, 30);

        $this->assertCount(1, $result);
        $this->assertSame('Tanpa File', $result[0]['nama_konsumen']);
        $this->assertNull($result[0]['file_spptb_access_url']);
    }

    public function testHistoryRejectsMismatchedMkdtAndKavling(): void
    {
        $historyService = $this->createMock(HistoryService::class);
        $historyService->expects($this->never())->method('getByReferenceAction');
        $transaksiRepository = $this->createMock(TransaksiRepository::class);
        $transaksiRepository->method('getSpptbData')->willReturn(null);
        $fileAccessService = $this->createMock(FileAccessService::class);

        $service = new MkdtHistoryService($historyService, $transaksiRepository, $fileAccessService);

        $this->assertSame([], $service->getConsumerReplacementHistory(20, 999));
    }
}
