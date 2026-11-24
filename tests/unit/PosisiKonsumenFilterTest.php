<?php

use App\Repositories\Mkdt\PosisiKonsumenRepository;
use App\Services\Mkdt\PosisiKonsumenService;
use App\Services\PrintService;
use App\Services\Proyek\ActiveProyekService;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Database;

final class PosisiKonsumenFilterTest extends CIUnitTestCase
{
    private PosisiKonsumenRepository $repository;
    /** @var array<int, string> */
    private array $createdProgressFiles = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new PosisiKonsumenRepository(Database::connect('tests', true));
    }

    protected function tearDown(): void
    {
        foreach ($this->createdProgressFiles as $path) {
            if (is_file($path)) {
                @unlink($path);
            }
        }
        $this->createdProgressFiles = [];
        parent::tearDown();
    }

    public function testNormalizeStatusKavlingSupportsFiveStatusesBookingAliasAndDefaultFallback(): void
    {
        $this->assertSame('semua', $this->repository->normalizeStatusKavling('semua'));
        $this->assertSame('aktif', $this->repository->normalizeStatusKavling('aktif'));
        $this->assertSame('indent', $this->repository->normalizeStatusKavling('indent'));
        $this->assertSame('akad', $this->repository->normalizeStatusKavling('akad'));
        $this->assertSame('ajb_indent', $this->repository->normalizeStatusKavling('ajb_indent'));

        // Case & whitespace normalization + backward compatibility alias
        $this->assertSame('aktif', $this->repository->normalizeStatusKavling('booking'));
        $this->assertSame('aktif', $this->repository->normalizeStatusKavling(' BOOKING '));
        $this->assertSame('ajb_indent', $this->repository->normalizeStatusKavling(' AJB_INDENT '));

        // Null, empty, and unknown values fall back to 'aktif'
        $this->assertSame('aktif', $this->repository->normalizeStatusKavling(null));
        $this->assertSame('aktif', $this->repository->normalizeStatusKavling(''));
        $this->assertSame('aktif', $this->repository->normalizeStatusKavling('tidak_valid'));
    }

    public function testApplyStatusKavlingFilterGeneratesExpectedSqlForAllFiveStatuses(): void
    {
        $prefix = Database::connect('tests', true)->DBPrefix;

        // 1. semua: Booking + Akad
        $semuaSql = $this->compileStatusKavlingSql('semua');
        $this->assertStringContainsString("`{$prefix}mkdt`.`status_mkdt` IN ('Booking','Akad')", $semuaSql);
        $this->assertStringNotContainsString('COALESCE(mkdt.akad, 0)', $semuaSql);
        $this->assertStringNotContainsString('COALESCE(mkdt.akad_indent, 0)', $semuaSql);

        // 2. aktif: akad = 0 AND akad_indent = 0
        $aktifSql = $this->compileStatusKavlingSql('aktif');
        $this->assertStringContainsString("`{$prefix}mkdt`.`status_mkdt` IN ('Booking','Akad')", $aktifSql);
        $this->assertStringContainsString('COALESCE(mkdt.akad, 0) = 0', $aktifSql);
        $this->assertStringContainsString('COALESCE(mkdt.akad_indent, 0) = 0', $aktifSql);

        // 3. indent: akad = 0 AND akad_indent = 1
        $indentSql = $this->compileStatusKavlingSql('indent');
        $this->assertStringContainsString("`{$prefix}mkdt`.`status_mkdt` IN ('Booking','Akad')", $indentSql);
        $this->assertStringContainsString('COALESCE(mkdt.akad, 0) = 0', $indentSql);
        $this->assertStringContainsString('COALESCE(mkdt.akad_indent, 0) = 1', $indentSql);

        // 4. akad: akad = 1
        $akadSql = $this->compileStatusKavlingSql('akad');
        $this->assertStringContainsString("`{$prefix}mkdt`.`status_mkdt` IN ('Booking','Akad')", $akadSql);
        $this->assertStringContainsString('COALESCE(mkdt.akad, 0) = 1', $akadSql);
        $this->assertStringNotContainsString('COALESCE(mkdt.akad_indent, 0)', $akadSql);

        // 5. ajb_indent: akad_indent = 1 (covers akad = 1 & akad_indent = 1 as well as akad = 0 & akad_indent = 1)
        $ajbIndentSql = $this->compileStatusKavlingSql('ajb_indent');
        $this->assertStringContainsString("`{$prefix}mkdt`.`status_mkdt` IN ('Booking','Akad')", $ajbIndentSql);
        $this->assertStringContainsString('COALESCE(mkdt.akad_indent, 0) = 1', $ajbIndentSql);
        $this->assertStringNotContainsString('COALESCE(mkdt.akad, 0) = 0', $ajbIndentSql);
    }

    public function testApplyBaseFiltersAndSearchArePublicAndReuseStatusKavlingRules(): void
    {
        $prefix  = Database::connect('tests', true)->DBPrefix;
        $builder = $this->repository->getBaseQuery(null);
        $this->repository->applyBaseFilters($builder, [
            'id_proyek'      => 7,
            'status_kavling' => 'booking',
        ]);
        $this->repository->applyBaseSearch($builder, 'Budi');

        $sql = $builder->getCompiledSelect();
        $this->assertStringContainsString("`{$prefix}proyek`.`id_proyek` = 7", $sql);
        $this->assertStringContainsString('COALESCE(mkdt.akad, 0) = 0', $sql);
        $this->assertStringContainsString('COALESCE(mkdt.akad_indent, 0) = 0', $sql);
        $this->assertStringContainsString("`{$prefix}konsumen`.`nama_konsumen` LIKE '%Budi%'", $sql);
    }

    public function testGetIndexDataMetaContainsFiveUpdatedStatusKavlingOptions(): void
    {
        $repo = $this->createMock(PosisiKonsumenRepository::class);
        $repo->method('getPphList')->willReturn([]);
        $repo->method('getPpnList')->willReturn([]);
        $repo->method('getLiKeu')->willReturn([]);
        $repo->method('getConfigShapeMap')->willReturn([]);

        $activeProyekService = $this->createMock(ActiveProyekService::class);
        $activeProyekService->method('resolveAndGet')->willReturn((object) [
            'id_proyek' => 1,
            'id_users'  => '1',
        ]);

        $service   = new PosisiKonsumenService($repo, $activeProyekService);
        $indexData = $service->getIndexData(null, 1, []);

        $this->assertSame([
            ['value' => 'semua', 'label' => 'Semua Status'],
            ['value' => 'aktif', 'label' => 'Kavling Aktif'],
            ['value' => 'indent', 'label' => 'Akad Indent'],
            ['value' => 'akad', 'label' => 'Akad AJB'],
            ['value' => 'ajb_indent', 'label' => 'Akad AJB & Indent'],
        ], $indexData['meta']['status_kavling_options']);
    }

    public function testSanitizeExportTokenAndGetExportProgressDefaultsWithoutFile(): void
    {
        $repo                = $this->createMock(PosisiKonsumenRepository::class);
        $activeProyekService = $this->createMock(ActiveProyekService::class);
        $service             = new PosisiKonsumenService($repo, $activeProyekService);

        $this->assertSame('abc_123-XYZ', $service->sanitizeExportToken('../../abc_123-XYZ/..'));

        $progress = $service->getExportProgress('../token_belum_ada_99');
        $this->assertSame([
            'token'   => 'token_belum_ada_99',
            'percent' => 5,
            'stage'   => 'init',
            'message' => 'Menyiapkan data export...',
            'done'    => false,
        ], $progress);
    }

    public function testWriteAndReadExportProgressCacheFile(): void
    {
        $repo                = $this->createMock(PosisiKonsumenRepository::class);
        $activeProyekService = $this->createMock(ActiveProyekService::class);
        $service             = new PosisiKonsumenService($repo, $activeProyekService);

        $token     = 'unit_test_progress_' . bin2hex(random_bytes(4));
        $cacheFile = rtrim(WRITEPATH, '/\\') . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . 'export_progress_' . $token . '.json';
        $this->createdProgressFiles[] = $cacheFile;

        PosisiKonsumenService::writeExportProgress($token, 45, 'processing', 'Memproses 9 / 20 data...', false);
        $mid = $service->getExportProgress($token);
        $this->assertSame(45, $mid['percent']);
        $this->assertSame('processing', $mid['stage']);
        $this->assertSame('Memproses 9 / 20 data...', $mid['message']);
        $this->assertFalse($mid['done']);

        PosisiKonsumenService::writeExportProgress($token, 100, 'done', 'Export selesai.', true);
        $done = $service->getExportProgress($token);
        $this->assertSame(100, $done['percent']);
        $this->assertSame('done', $done['stage']);
        $this->assertTrue($done['done']);
    }

    public function testExportPoskonForwardsFiltersAndProgressTokenAndMarksDone(): void
    {
        $repo = $this->createMock(PosisiKonsumenRepository::class);
        $repo->expects($this->once())
            ->method('insertRiwayatExport')
            ->willReturn(true);

        $activeProyekService = $this->createMock(ActiveProyekService::class);
        $token               = 'unit_export_' . bin2hex(random_bytes(4));
        $cacheFile           = rtrim(WRITEPATH, '/\\') . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . 'export_progress_' . $token . '.json';
        $this->createdProgressFiles[] = $cacheFile;

        $printService = $this->createMock(PrintService::class);
        $printService->expects($this->once())
            ->method('exportPoskonXlsx')
            ->with(
                7,
                2,
                null,
                'aktif',
                $this->callback(function (array $filters): bool {
                    return ($filters['status_kavling'] ?? null) === 'indent'
                        && ($filters['bulan_field'] ?? null) === 'indent'
                        && ($filters['bulan'] ?? null) === '11'
                        && ($filters['tahun'] ?? null) === '2026'
                        && ($filters['sp3k'] ?? null) === '1'
                        && ($filters['wawancara'] ?? null) === '1'
                        && ($filters['search'] ?? null) === 'Budi'
                        && !array_key_exists('export_token', $filters);
                }),
                $token
            )
            ->willReturn([
                'status'     => true,
                'message'    => 'File berhasil disimpan di server',
                'tipe'       => 'xlsx',
                'randomName' => 'abc.xlsx',
                'filename'   => 'Poskon.xlsx',
                'path'       => 'upload/poskon/202611/',
                'file'       => 'data:application/octet-stream;base64,AA==',
            ]);

        $service = new PosisiKonsumenService($repo, $activeProyekService, $printService);
        $result  = $service->exportPoskon('xlsx', 'aktif', [
            'id_proyek'      => 7,
            'id_cluster'     => 2,
            'id_jalan'       => '',
            'status_kavling' => 'indent',
            'bulan_field'    => 'indent',
            'bulan'          => '11',
            'tahun'          => '2026',
            'sp3k'           => '1',
            'wawancara'      => '1',
            'search'         => 'Budi',
            'export_token'   => '../' . $token,
        ], 99);

        $this->assertTrue($result['status']);
        $progress = $service->getExportProgress($token);
        $this->assertSame(100, $progress['percent']);
        $this->assertSame('done', $progress['stage']);
        $this->assertTrue($progress['done']);
    }

    public function testPdfPoskonViewHandlesEmptyRowsSafely(): void
    {
        $html = view('pdf/poskon', [
            'poskon'      => [],
            'status'      => 'aktif',
            'nama_proyek' => 'Grand Harmoni',
        ]);

        $this->assertStringContainsString('Grand Harmoni', $html);
        $this->assertStringContainsString('Tidak ada data sesuai filter', $html);
    }

    private function compileStatusKavlingSql(string $statusKavling): string
    {
        $builder = $this->repository->getBaseQuery(null);
        $this->repository->applyStatusKavlingFilter($builder, $statusKavling);

        return $builder->getCompiledSelect();
    }
}
