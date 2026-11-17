<?php

use App\Repositories\Siteplan\OthersRepository;
use App\Repositories\Siteplan\TargetSiteplanRepository;
use App\Repositories\Transaksi\KavlingRepository;
use App\Services\FileAccessService;
use App\Services\HistoryService;
use App\Services\Siteplan\SiteplanVisualStatusService;
use App\Services\Siteplan\TargetSiteplanService;
use App\Services\SiteplanDataService;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Database;

final class SiteplanDataServiceTest extends CIUnitTestCase
{
    public function testNormalizeIdListAcceptsLegacyScalarAndMultiClusterArray(): void
    {
        $this->assertSame([7], SiteplanDataService::normalizeIdList('7'));
        $this->assertSame([2, 7], SiteplanDataService::normalizeIdList(['2', 2, '0', '-1', 'abc', 7]));
        $this->assertSame([], SiteplanDataService::normalizeIdList(null));
    }

    /**
     * @dataProvider roleTargetProvider
     */
    public function testGetKavlingsAlwaysIncludesTargetKavlingAndTargetYears(int $idRole, ?string $rawYear, ?int $expectedYear): void
    {
        $kavlingRows = [
            (object) ['id_kavling' => 10, 'no_kavling' => 'A1'],
        ];
        $expectedTargetMap = [
            10 => [
                'id_target' => '5',
                'tahun_target' => '2026',
                'deskripsi' => 'Target 2026',
                'id_tipe' => 3,
                'tipe_manual' => null,
                'tipe_label' => 'T36 / 36/72',
                'target_is_subsidi' => 1,
            ],
        ];
        $expectedYears = [2027, 2026];

        $kavlingRepo = $this->createMock(KavlingRepository::class);
        $kavlingRepo->expects($this->once())
            ->method('getAll')
            ->with(12, null, null, $idRole, $this->isType('array'))
            ->willReturn($kavlingRows);

        $othersRepo = $this->createMock(OthersRepository::class);

        $visualService = $this->createMock(SiteplanVisualStatusService::class);
        if ($idRole === 0) {
            $visualService->expects($this->once())
                ->method('appendVisualRows')
                ->with($kavlingRows)
                ->willReturn($kavlingRows);
        } else {
            $visualService->expects($this->never())->method('appendVisualRows');
        }

        $targetService = $this->createMock(TargetSiteplanService::class);
        $targetService->expects($this->once())
            ->method('getKavlingTargetMap')
            ->with(12, $expectedYear)
            ->willReturn($expectedTargetMap);
        $targetService->expects($this->once())
            ->method('getAvailableYears')
            ->with(12)
            ->willReturn($expectedYears);

        $fileAccessService = $this->createMock(FileAccessService::class);

        $service = new SiteplanDataService(
            $kavlingRepo,
            $othersRepo,
            $visualService,
            $targetService,
            $fileAccessService
        );

        $params = [
            'id_proyek' => 12,
            'id_role' => $idRole,
        ];
        if ($rawYear !== null) {
            $params['tahun_target'] = $rawYear;
        }

        $result = $service->getKavlings($params);

        $this->assertSame($kavlingRows, $result['data']);
        $this->assertSame($expectedTargetMap, $result['target_kavling']);
        $this->assertSame($expectedYears, $result['target_years']);
    }

    public static function roleTargetProvider(): array
    {
        return [
            'composite role without year' => [0, null, null],
            'mkdt role with year' => [4, '2026', 2026],
            'legal role with invalid year' => [5, '0', null],
            'legacy target role' => [11, '2027', 2027],
        ];
    }

    public function testTargetRepositoryReturnsAvailableYearsAndTargetKavlingMapWithTipe(): void
    {
        $db = Database::connect();
        $db->query('DROP TABLE IF EXISTS db_target_siteplan_kavling');
        $db->query('DROP TABLE IF EXISTS db_target_siteplan');
        $db->query('DROP TABLE IF EXISTS db_tipe');

        $db->query('CREATE TABLE db_target_siteplan (
            id_target INTEGER PRIMARY KEY,
            id_proyek INTEGER,
            tahun_target INTEGER,
            deskripsi TEXT,
            status INTEGER,
            deleted_at TEXT NULL
        )');
        $db->query('CREATE TABLE db_target_siteplan_kavling (
            id_target_kavling INTEGER PRIMARY KEY,
            id_target INTEGER,
            id_kavling INTEGER,
            id_tipe INTEGER NULL,
            tipe_manual TEXT NULL
        )');
        $db->query('CREATE TABLE db_tipe (
            id_tipe INTEGER PRIMARY KEY,
            no_tipe_rumah TEXT NULL,
            tipe_rumah TEXT NULL,
            is_subsidi INTEGER NULL
        )');

        $db->table('tipe')->insertBatch([
            ['id_tipe' => 10, 'no_tipe_rumah' => 'T36', 'tipe_rumah' => '36/72', 'is_subsidi' => 1],
            ['id_tipe' => 11, 'no_tipe_rumah' => null, 'tipe_rumah' => '45/90', 'is_subsidi' => 0],
        ]);
        $db->table('target_siteplan')->insertBatch([
            ['id_target' => 1, 'id_proyek' => 9, 'tahun_target' => 2025, 'deskripsi' => 'Target 2025', 'status' => 1, 'deleted_at' => null],
            ['id_target' => 2, 'id_proyek' => 9, 'tahun_target' => 2027, 'deskripsi' => 'Target 2027', 'status' => 1, 'deleted_at' => null],
            ['id_target' => 3, 'id_proyek' => 9, 'tahun_target' => 2027, 'deskripsi' => 'Batch 2 2027', 'status' => 1, 'deleted_at' => null],
            ['id_target' => 4, 'id_proyek' => 9, 'tahun_target' => 2024, 'deskripsi' => 'Nonaktif', 'status' => 0, 'deleted_at' => null],
            ['id_target' => 5, 'id_proyek' => 9, 'tahun_target' => 2023, 'deskripsi' => 'Deleted', 'status' => 1, 'deleted_at' => '2026-01-01 00:00:00'],
            ['id_target' => 6, 'id_proyek' => 8, 'tahun_target' => 2028, 'deskripsi' => 'Proyek lain', 'status' => 1, 'deleted_at' => null],
        ]);
        $db->table('target_siteplan_kavling')->insertBatch([
            ['id_target_kavling' => 1, 'id_target' => 1, 'id_kavling' => 101, 'id_tipe' => 10, 'tipe_manual' => null],
            ['id_target_kavling' => 2, 'id_target' => 2, 'id_kavling' => 102, 'id_tipe' => null, 'tipe_manual' => 'Custom Corner 50/100'],
            ['id_target_kavling' => 3, 'id_target' => 3, 'id_kavling' => 103, 'id_tipe' => 11, 'tipe_manual' => null],
        ]);

        $historyService = $this->createMock(HistoryService::class);
        $repo = new TargetSiteplanRepository($db, $historyService);

        $this->assertSame([], $repo->getAvailableYears(0));
        $this->assertSame([2027, 2025], $repo->getAvailableYears(9));

        $allMap = $repo->getKavlingTargetMap(9);
        $this->assertArrayHasKey(101, $allMap);
        $this->assertArrayHasKey(102, $allMap);
        $this->assertArrayHasKey(103, $allMap);
        $this->assertSame('1', $allMap[101]['id_target']);
        $this->assertSame('2025', $allMap[101]['tahun_target']);
        $this->assertSame('Target 2025', $allMap[101]['deskripsi']);
        $this->assertSame(10, $allMap[101]['id_tipe']);
        $this->assertNull($allMap[101]['tipe_manual']);
        $this->assertSame('T36 / 36/72', $allMap[101]['tipe_label']);
        $this->assertSame(1, $allMap[101]['target_is_subsidi']);

        $this->assertNull($allMap[102]['id_tipe']);
        $this->assertSame('Custom Corner 50/100', $allMap[102]['tipe_manual']);
        $this->assertSame('Custom Corner 50/100', $allMap[102]['tipe_label']);
        $this->assertNull($allMap[102]['target_is_subsidi']);

        $this->assertSame('45/90', $allMap[103]['tipe_label']);
        $this->assertSame(0, $allMap[103]['target_is_subsidi']);

        $filtered2027 = $repo->getKavlingTargetMap(9, 2027);
        $this->assertArrayNotHasKey(101, $filtered2027);
        $this->assertArrayHasKey(102, $filtered2027);
        $this->assertArrayHasKey(103, $filtered2027);
    }
}
