<?php

use App\Enums\NotificationEvent;
use App\Repositories\Mkdt\BatalBookingRepository;
use App\Repositories\Mkdt\StandingInstructionRepository;
use App\Repositories\Mkdt\StockRepository;
use App\Services\FileAccessService;
use App\Services\Mkdt\BatalBookingService;
use App\Services\Mkdt\MkdtHistoryService;
use App\Services\Mkdt\StandingInstructionService;
use App\Services\Mkdt\StockService;
use App\Services\Notifikasi\NotifikasiService;
use App\Services\Proyek\ActiveProyekService;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\HTTP\Files\UploadedFile;
use CodeIgniter\Test\CIUnitTestCase;

final class MkdtOperationServicesTest extends CIUnitTestCase
{
    public function testBatalBookingCleanNumericSanitizesVariousFormats(): void
    {
        $service = new BatalBookingService(
            $this->createMock(BatalBookingRepository::class),
            $this->createMock(FileAccessService::class),
            $this->createMock(MkdtHistoryService::class),
            $this->createMock(NotifikasiService::class),
            $this->createMock(BaseConnection::class)
        );

        $this->assertSame(0.0, $service->cleanNumeric(null));
        $this->assertSame(0.0, $service->cleanNumeric(''));
        $this->assertSame(1500000.0, $service->cleanNumeric('1,500,000'));
        $this->assertSame(1500000.0, $service->cleanNumeric('1.500.000'));
        $this->assertSame(250000.5, $service->cleanNumeric(250000.5));
    }

    public function testBatalBookingGetDetailHandlesInvalidAndMissingId(): void
    {
        $repo = $this->createMock(BatalBookingRepository::class);
        $repo->expects($this->once())
            ->method('getMkdtDetailForBatal')
            ->with(999)
            ->willReturn(null);

        $service = new BatalBookingService(
            $repo,
            $this->createMock(FileAccessService::class),
            $this->createMock(MkdtHistoryService::class),
            $this->createMock(NotifikasiService::class),
            $this->createMock(BaseConnection::class)
        );

        $zeroResult = $service->getDetail(0);
        $this->assertFalse($zeroResult['found']);
        $this->assertNull($zeroResult['data']);
        $this->assertSame(['uang_muka' => 0.0, 'biaya_biaya' => 0.0], $zeroResult['sudah_bayar']);
        $this->assertSame(['uang_muka' => 0.0, 'biaya_biaya' => 0.0], $zeroResult['total_biaya']);

        $missingResult = $service->getDetail(999);
        $this->assertFalse($missingResult['found']);
        $this->assertNull($missingResult['data']);
    }

    public function testBatalBookingGetDetailComputesTotalsPaidAndAccessUrl(): void
    {
        $mkdtRow = (object) [
            'id_mkdt'                => 42,
            'harga_uang_muka'        => '15,000,000',
            'harga_administrasi'     => '1,000,000',
            'harga_penambahan_um'    => '500,000',
            'harga_diskon_uang_muka' => '1,500,000',
            'harga_ppn'              => '2,000,000',
            'harga_bphtb'            => '3,000,000',
            'harga_biaya_proses'     => '1,000,000',
            'surat_batal'            => 'uploads/batal/20261113/surat.pdf',
        ];

        $logs = [
            (object) ['payment_type' => 'UangMuka', 'nominal' => '5,000,000'],
            (object) ['payment_type' => 'UangMuka', 'nominal' => '2,500,000'],
            (object) ['payment_type' => 'BiayaBiaya', 'nominal' => '1,200,000'],
            (object) ['payment_type' => 'Lainnya', 'nominal' => '999,999'],
        ];

        $repo = $this->createMock(BatalBookingRepository::class);
        $repo->expects($this->once())
            ->method('getMkdtDetailForBatal')
            ->with(42)
            ->willReturn($mkdtRow);
        $repo->expects($this->once())
            ->method('getPembayaranLogsByMkdt')
            ->with(42)
            ->willReturn($logs);

        $fileAccess = $this->createMock(FileAccessService::class);
        $fileAccess->expects($this->once())
            ->method('accessUrl')
            ->with('mkdt_surat_batal', 42)
            ->willReturn('https://example.test/files/mkdt_surat_batal/42');

        $service = new BatalBookingService(
            $repo,
            $fileAccess,
            $this->createMock(MkdtHistoryService::class),
            $this->createMock(NotifikasiService::class),
            $this->createMock(BaseConnection::class)
        );

        $result = $service->getDetail(42);

        $this->assertTrue($result['found']);
        $this->assertSame('https://example.test/files/mkdt_surat_batal/42', $result['data']->surat_batal_access_url);
        $this->assertSame(['uang_muka' => 14000000.0, 'biaya_biaya' => 6000000.0], $result['total_biaya']);
        $this->assertSame(['uang_muka' => 7500000.0, 'biaya_biaya' => 1200000.0], $result['sudah_bayar']);
    }

    public function testCancelBookingValidatesIdsAndStoresSuratBatalBeforeUpdate(): void
    {
        $repo        = $this->createMock(BatalBookingRepository::class);
        $fileAccess  = $this->createMock(FileAccessService::class);
        $history     = $this->createMock(MkdtHistoryService::class);
        $notifikasi  = $this->createMock(NotifikasiService::class);
        $db          = $this->createMock(BaseConnection::class);

        $service = new BatalBookingService($repo, $fileAccess, $history, $notifikasi, $db);

        $invalid = $service->cancelBooking(['id_mkdt' => 0, 'id_kavling' => 0], null, 7, [4]);
        $this->assertFalse($invalid['success']);
        $this->assertFalse($invalid['not_found']);
        $this->assertArrayHasKey('id_mkdt', $invalid['errors']);
        $this->assertArrayHasKey('id_kavling', $invalid['errors']);

        $oldMkdt = (object) [
            'id_mkdt'     => 10,
            'id_konsumen' => 88,
            'status_mkdt' => 'Booking',
            'is_batal'    => 0,
        ];

        $repo->expects($this->once())
            ->method('getMkdtRawById')
            ->with(10)
            ->willReturn($oldMkdt);

        $uploadedFile = $this->createMock(UploadedFile::class);
        $uploadedFile->method('isValid')->willReturn(true);
        $uploadedFile->method('hasMoved')->willReturn(false);
        $uploadedFile->method('getSize')->willReturn(1024);
        $uploadedFile->method('getRandomName')->willReturn('batal_random.pdf');

        $expectedPath = 'uploads/batal/' . date('Ymd') . '/batal_random.pdf';
        $fileAccess->expects($this->once())
            ->method('storeAs')
            ->with($uploadedFile, 'uploads/batal/' . date('Ymd') . '/', 'batal_random.pdf')
            ->willReturn($expectedPath);
        $fileAccess->expects($this->once())
            ->method('accessUrl')
            ->with('mkdt_surat_batal', 10)
            ->willReturn('https://example.test/files/mkdt_surat_batal/10');

        $db->expects($this->once())->method('transStart');
        $db->expects($this->once())->method('transComplete');
        $db->expects($this->once())->method('transStatus')->willReturn(true);

        $repo->expects($this->once())
            ->method('updateMkdtBatal')
            ->with(
                10,
                $this->callback(function (array $data) use ($expectedPath): bool {
                    return ($data['surat_batal'] ?? null) === $expectedPath
                        && ($data['keuangan_batal_oleh'] ?? null) === 7
                        && !isset($data['mkdt_batal_oleh'])
                        && ($data['status_mkdt'] ?? null) === 'Batal'
                        && ($data['is_batal'] ?? null) === 1
                        && ($data['perlu_refund'] ?? null) === 1
                        && ($data['keterangan_batal'] ?? null) === 'Mundur KPR';
                })
            )
            ->willReturn(true);

        $repo->expects($this->once())
            ->method('getProyekIdByKavling')
            ->with(55)
            ->willReturn(3);

        $history->expects($this->once())
            ->method('buildBatalSummary')
            ->willReturn('Booking dibatalkan. Perlu refund; Keterangan: Mundur KPR');
        $history->expects($this->once())
            ->method('log')
            ->with(
                55,
                10,
                MkdtHistoryService::ACTION_BATAL_BOOKING,
                'Booking dibatalkan. Perlu refund; Keterangan: Mundur KPR',
                (array) $oldMkdt,
                $this->arrayHasKey('surat_batal'),
                7
            )
            ->willReturn(true);

        $notifikasi->expects($this->once())
            ->method('tambah_notif')
            ->with(
                '3;4;9',
                'Membatalkan booking',
                7,
                55,
                88,
                NotificationEvent::BATAL_BOOKING,
                3,
                'siteplan/view?id_kavling=55&tab=konsumen'
            )
            ->willReturn(123);

        $ok = $service->cancelBooking([
            'batal-id_mkdt'          => 10,
            'batal-id_kavling'       => 55,
            'batal-keterangan_batal' => 'Mundur KPR',
            'batal-perlu_refund'     => 1,
        ], $uploadedFile, 7, [3]);

        $this->assertTrue($ok['success']);
        $this->assertSame($expectedPath, $ok['data']['surat_batal']);
        $this->assertSame('https://example.test/files/mkdt_surat_batal/10', $ok['data']['surat_batal_access_url']);
    }

    public function testStockServiceFormatsLegacyAndRawPayloadsWithSafeDates(): void
    {
        $row1 = (object) [
            'id_kavling'                  => 11,
            'id_produksi'                 => 22,
            'id_mkdt'                     => null,
            'id_jalan'                    => 5,
            'nama_jalan'                  => 'Jl. Melati',
            'no_kavling'                  => 'A1',
            'id_cluster'                  => 2,
            'nama_cluster'                => 'Cluster Melati',
            'id_proyek'                   => 1,
            'nama_proyek'                 => 'Proyek A',
            'tipe_rumah'                  => '36/72',
            'no_tipe_rumah'               => 'T36',
            'progres_bangunan'            => 100,
            'status_mkdt'                 => null,
            'nama_konsumen'               => null,
            'hp_konsumen'                 => null,
            'keterangan_batal'            => null,
            'tanggal_pembangunan'         => '0000-00-00',
            'tanggal_selesai_pembangunan' => '2026-05-10',
            'uadd_by'                     => 'admin',
            'uedit_by'                    => 'budi',
            'updated_at'                  => '2026-05-11 08:15:00',
        ];

        $repo = $this->createMock(StockRepository::class);
        $repo->expects($this->exactly(2))
            ->method('getStockListDataTables')
            ->willReturn([
                'recordsTotal'    => 5,
                'recordsFiltered' => 1,
                'rows'            => [$row1],
            ]);

        $activeProyek = $this->createMock(ActiveProyekService::class);
        $activeProyek->expects($this->once())->method('resolveAndGet')->willReturn(null);

        $service = new StockService($repo, $activeProyek);

        $indexData = $service->getIndexData();
        $this->assertSame('kavling/list-stock', $indexData['content']);
        $this->assertNull($indexData['data']['proyek']->id_proyek);

        $legacy = $service->getListStock(['draw' => 2, 'start' => 10, 'length' => 10], false);
        $this->assertSame(2, $legacy['draw']);
        $this->assertSame(5, $legacy['recordsTotal']);
        $this->assertSame(1, $legacy['recordsFiltered']);
        $this->assertSame(11, $legacy['data'][0]['no_index']);
        $this->assertSame('-', $legacy['data'][0]['tanggal_pembangunan_formatted']);
        $this->assertSame('10-May-2026', $legacy['data'][0]['tanggal_selesai_pembangunan_formatted']);
        $this->assertSame('budi<br>11-May-2026 08:15', $legacy['data'][0]['terakhir_diperbarui']);

        $raw = $service->getListStock(['draw' => 3, 'start' => 0, 'length' => 10], true);
        $this->assertSame(3, $raw['draw']);
        $this->assertSame('-', $raw['data'][0]['tanggal_pembangunan_formatted']);
        $this->assertSame('10-May-2026', $raw['data'][0]['tanggal_selesai_pembangunan_formatted']);
        $this->assertSame('budi', $raw['data'][0]['updated_by']);
        $this->assertSame('11-May-2026 08:15', $raw['data'][0]['updated_at_formatted']);
        $this->assertArrayNotHasKey('terakhir_diperbarui', $raw['data'][0]);
    }

    public function testStandingInstructionServiceSaveValidatesAndCommits(): void
    {
        $repo       = $this->createMock(StandingInstructionRepository::class);
        $fileAccess = $this->createMock(FileAccessService::class);
        $history    = $this->createMock(MkdtHistoryService::class);
        $db         = $this->createMock(BaseConnection::class);

        $service = new StandingInstructionService($repo, $fileAccess, $history, $db);

        $bad = $service->save(0, [], [], 5);
        $this->assertFalse($bad['success']);
        $this->assertArrayHasKey('id_kavling', $bad['errors']);

        $db->expects($this->once())->method('transStart');
        $db->expects($this->once())->method('transComplete');
        $db->expects($this->once())->method('transStatus')->willReturn(true);

        $repo->expects($this->once())->method('insertSi')->willReturn(99);
        $repo->expects($this->once())->method('getListSiNameById')->with(4)->willReturn('SI Bank');
        $repo->expects($this->once())->method('getKavlingMkdtId')->with(20)->willReturn(77);

        $history->expects($this->once())
            ->method('buildStandingInstructionSummary')
            ->willReturn('Standing instruction disimpan: SI Bank (2026-06-01)');
        $history->expects($this->once())
            ->method('log')
            ->with(
                20,
                77,
                MkdtHistoryService::ACTION_STANDING_INSTRUCTION,
                'Standing instruction disimpan: SI Bank (2026-06-01)',
                null,
                $this->isType('array'),
                5
            )
            ->willReturn(true);

        $ok = $service->save(20, [
            'n4' => ['tanggal_si' => '2026-06-01', 'keterangan' => 'Catatan SI'],
        ], [], 5);

        $this->assertTrue($ok['success']);
        $this->assertCount(1, $ok['saved_items']);
        $this->assertSame('SI Bank', $ok['saved_items'][0]['nama']);
    }
}
