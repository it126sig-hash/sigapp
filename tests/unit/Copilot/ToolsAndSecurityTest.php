<?php

use App\Repositories\Copilot\JatuhTempoRepository;
use App\Repositories\Mkdt\BookingAkadReportRepository;
use App\Repositories\Proyek\ProyekRepository;
use App\Services\Copilot\CopilotAuthorizer;
use App\Services\Copilot\PiiGuard;
use App\Services\Copilot\ToolRegistry;
use App\Services\Copilot\Tools\BookingPerBulanTool;
use App\Services\Copilot\Tools\JatuhTempoCicilanTool;
use App\Services\Copilot\Tools\KonversiBookingAkadTool;
use App\Services\Mkdt\BookingAkadReportService;
use App\Services\Settings\MenuAccessService;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Database;

final class ToolsAndSecurityTest extends CIUnitTestCase
{
    public function testPiiGuardBlocksForbiddenKeysCaseInsensitivelyAndRecursively(): void
    {
        $guard = new PiiGuard();

        $forbiddenKeys = [
            'nik',
            'NIK',
            'nik_pasangan',
            'npwp',
            'hp_konsumen',
            'hp_pasangan',
            'tel_instansi',
            'alamat_konsumen',
            'email',
            'file_ktp',
            'file_spptb',
            'keterangan',
            'keterangan_batal',
            'void_reason',
            'no_kk',
            'no_rek',
            'atas_nama',
            'password',
            'hash',
            'pekerjaan',
            'instansi',
            'gaji',
        ];

        foreach ($forbiddenKeys as $key) {
            try {
                $guard->assertSafePayload([
                    'status' => 'ok',
                    'rows'   => [
                        [$key => 'secret-value'],
                    ],
                ]);
                $this->fail("Expected RuntimeException for forbidden key '{$key}'.");
            } catch (RuntimeException $e) {
                $this->assertStringContainsString($key, $e->getMessage());
            }
        }
    }

    public function testPiiGuardAllowsOfficialSafeKeysAndRedactsNikAndNpwpText(): void
    {
        $guard = new PiiGuard();

        $safePayload = [
            'status'          => 'ok',
            'as_of_date'      => '2026-11-24',
            'cutoff_date'     => '2026-12-01',
            'days'            => 7,
            'limit'           => 50,
            'kategori'        => 'semua',
            'nama_konsumen'   => 'Budi Santoso',
            'no_kavling'      => 'A-12',
            'nama_jalan'      => 'Jl. Merpati',
            'nama_cluster'    => 'Cluster Melati',
            'nama_proyek'     => 'SANGGAR INDAH PALASTRI',
            'berita_acara'    => 'Cicilan 1 (ref 3201012345678901 / 12.345.678.9-012.345)',
            'jatuh_tempo_tgl' => '2026-11-20',
            'nominal'         => 2500000.0,
            'hari_terlambat'  => 4,
            'months'          => [
                [
                    'month'                  => 1,
                    'month_label'            => 'Januari',
                    'booking'                => 5,
                    'total_booking_cohort'   => 5,
                    'booking_converted_akad' => 3,
                    'konversi'               => 60.0,
                    'akad_indent'            => 1,
                    'akad_ajb'               => 2,
                    'batal'                  => 0,
                ],
            ],
        ];

        $sanitized = $guard->assertSafePayload($safePayload);

        $this->assertSame('Budi Santoso', $sanitized['nama_konsumen']);
        $this->assertSame('Cicilan 1 (ref [REDACTED_ID] / [REDACTED_NPWP])', $sanitized['berita_acara']);

        $redacted = $guard->redactText('NIK 3204010101900001 dan NPWP 09.254.294.3-407.000 terdeteksi');
        $this->assertTrue($redacted['redacted']);
        $this->assertSame('NIK [REDACTED_ID] dan NPWP [REDACTED_NPWP] terdeteksi', $redacted['text']);

        $clean = $guard->redactText('Tagihan Uang Muka Kavling B-05');
        $this->assertFalse($clean['redacted']);
        $this->assertSame('Tagihan Uang Muka Kavling B-05', $clean['text']);
    }

    public function testAuthorizerAndToolRegistryEnforceRbacPerGroup(): void
    {
        $groupMap = [
            101 => [1],  // Admin
            103 => [3],  // Keuangan
            104 => [4],  // MKDT
            105 => [5],  // Legal
            106 => [6],  // Planning
            107 => [7],  // Produksi
            108 => [8],  // Sales
            109 => [9],  // Direksi
            110 => [10], // Pajak
            199 => [5],  // Denied menu user
        ];

        $menuAccess = $this->createMock(MenuAccessService::class);
        $menuAccess->method('getUserGroupIds')
            ->willReturnCallback(static fn (int $uid): array => $groupMap[$uid] ?? []);
        $menuAccess->method('canAccessUrl')
            ->willReturnCallback(static fn (int $uid, string $url): bool => $url === 'copilot' && $uid !== 199 && isset($groupMap[$uid]));

        $proyekRepo = $this->createMock(ProyekRepository::class);
        $proyekRepo->method('getAccessibleForUser')->willReturn([
            (object) ['id_proyek' => 11, 'nama_proyek' => 'ALAM SANGGAR INDAH Ext'],
            (object) ['id_proyek' => 13, 'nama_proyek' => 'SANGGAR INDAH PALASTRI'],
        ]);

        $authorizer = new CopilotAuthorizer($menuAccess, $proyekRepo);
        $registry = new ToolRegistry($authorizer, new PiiGuard());

        // Allowed groups for get_jatuh_tempo_cicilan: 1, 3, 4, 8, 9
        foreach ([101, 103, 104, 108, 109] as $allowedUid) {
            $names = $registry->getAllowedToolNamesForUser($allowedUid);
            $this->assertSame(
                ['get_jatuh_tempo_cicilan', 'get_booking_per_bulan', 'get_konversi_booking_akad'],
                $names,
                "User {$allowedUid} should have access to all 3 tools."
            );
            $this->assertCount(3, $registry->getDeclarationsForUser($allowedUid));
        }

        // Denied groups for get_jatuh_tempo_cicilan: 5 (Legal), 6 (Planning), 7 (Produksi), 10 (Pajak)
        foreach ([105, 106, 107, 110] as $restrictedUid) {
            $names = $registry->getAllowedToolNamesForUser($restrictedUid);
            $this->assertSame(
                ['get_booking_per_bulan', 'get_konversi_booking_akad'],
                $names,
                "User {$restrictedUid} must NOT have get_jatuh_tempo_cicilan."
            );
            $this->assertCount(2, $registry->getDeclarationsForUser($restrictedUid));

            $execResult = $registry->execute($restrictedUid, 'get_jatuh_tempo_cicilan', []);
            $this->assertSame('forbidden', $execResult['status']);
        }

        // User without copilot menu access gets 0 tools
        $this->assertSame([], $registry->getAllowedToolNamesForUser(199));
        $this->assertSame([], $registry->getDeclarationsForUser(199));
        $this->assertSame('forbidden', $registry->execute(199, 'get_booking_per_bulan', [])['status']);
    }

    public function testAuthorizerProjectScopeResolutionHandlesSingleMultiAndForbidden(): void
    {
        $menuAccess = $this->createMock(MenuAccessService::class);
        $menuAccess->method('getUserGroupIds')->willReturnCallback(static fn (int $uid): array => $uid === 1 ? [1] : [3]);
        $menuAccess->method('canAccessUrl')->willReturn(true);

        $proyekRepo = $this->createMock(ProyekRepository::class);
        $proyekRepo->method('getAccessibleForUser')->willReturnCallback(static function (int $uid, bool $isAdmin): array {
            if ($uid === 10) {
                return [(object) ['id_proyek' => 13, 'nama_proyek' => 'SANGGAR INDAH PALASTRI']];
            }

            return [
                (object) ['id_proyek' => 11, 'nama_proyek' => 'ALAM SANGGAR INDAH Ext'],
                (object) ['id_proyek' => 13, 'nama_proyek' => 'SANGGAR INDAH PALASTRI'],
            ];
        });

        $authorizer = new CopilotAuthorizer($menuAccess, $proyekRepo);

        // 1 project without argument -> auto select
        $single = $authorizer->resolveTargetProject(10);
        $this->assertSame('ok', $single['status']);
        $this->assertSame([13], $single['project_ids']);
        $this->assertSame('SANGGAR INDAH PALASTRI', $single['selected_project']['nama_proyek']);

        // >1 project without argument -> needs_project_selection
        $multiNoArg = $authorizer->resolveTargetProject(20, null, null, true);
        $this->assertSame('needs_project_selection', $multiNoArg['status']);
        $this->assertCount(2, $multiNoArg['available_projects']);

        // >1 project with "semua" & allowMultiDefault = true -> ok with all project_ids
        $multiAll = $authorizer->resolveTargetProject(20, 'semua', null, true);
        $this->assertSame('ok', $multiAll['status']);
        $this->assertSame([11, 13], $multiAll['project_ids']);

        // >1 project with partial name match -> ok
        $partial = $authorizer->resolveTargetProject(20, 'Palastri');
        $this->assertSame('ok', $partial['status']);
        $this->assertSame([13], $partial['project_ids']);

        // Forbidden / unknown project -> forbidden_or_not_found
        $forbidden = $authorizer->resolveTargetProject(10, 'Proyek Rahasia');
        $this->assertSame('forbidden_or_not_found', $forbidden['status']);
        $this->assertCount(1, $forbidden['available_projects']);

        $forbiddenById = $authorizer->resolveTargetProject(10, null, 999);
        $this->assertSame('forbidden_or_not_found', $forbiddenById['status']);
    }

    public function testLiveDbJatuhTempoRepositoryAndBookingAkadToolsMatchSourceOfTruth(): void
    {
        $db = $this->connectLiveDefaultDb();

        try {
            // Find an active Admin user and a project with booking data in DB
            $adminRow = $db->table('auth_groups_users')->select('user_id')->where('group_id', 1)->get()->getRow();
            $this->assertNotNull($adminRow, 'Database must have at least one Admin user in auth_groups_users.');
            $adminUserId = (int) $adminRow->user_id;

            $projectRow = $db->table('mkdt m')
                ->select('p.id_proyek, p.nama_proyek')
                ->join('proyek p', 'p.id_proyek = m.id_proyek')
                ->where('m.booking_tgl IS NOT NULL', null, false)
                ->orderBy('m.booking_tgl', 'DESC')
                ->get()
                ->getRow();

            if (! $projectRow) {
                $projectRow = $db->table('proyek')->select('id_proyek, nama_proyek')->get()->getRow();
            }
            $this->assertNotNull($projectRow, 'Database must have at least one project.');

            $idProyek = (int) $projectRow->id_proyek;
            $namaProyek = (string) $projectRow->nama_proyek;
            $tahun = 2026;

            // 1. Live DB test for JatuhTempoRepository
            $jtRepo = new JatuhTempoRepository($db);
            $today = new DateTimeImmutable('2026-11-24');
            $jtData = $jtRepo->getJatuhTempo([$idProyek], 7, 10, $today);

            $this->assertSame('2026-11-24', $jtData['as_of_date']);
            $this->assertSame('2026-12-01', $jtData['cutoff_date']);
            $this->assertArrayHasKey('summary', $jtData);
            $this->assertArrayHasKey('terlambat', $jtData['summary']);
            $this->assertArrayHasKey('akan_jatuh_tempo', $jtData['summary']);
            $this->assertLessThanOrEqual(10, count($jtData['terlambat']));
            $this->assertLessThanOrEqual(10, count($jtData['akan_jatuh_tempo']));

            $guard = new PiiGuard();
            $guard->assertSafePayload($jtData);

            $allowedRowKeys = [
                'id_keuangan',
                'id_proyek',
                'nama_proyek',
                'nama_cluster',
                'nama_jalan',
                'no_kavling',
                'nama_konsumen',
                'berita_acara',
                'jatuh_tempo_tgl',
                'nominal',
            ];

            foreach ($jtData['terlambat'] as $row) {
                $keys = array_keys($row);
                sort($keys);
                $expected = array_merge($allowedRowKeys, ['hari_terlambat']);
                sort($expected);
                $this->assertSame($expected, $keys);
                $this->assertLessThan('2026-11-24', $row['jatuh_tempo_tgl']);
                $this->assertGreaterThan(0, $row['hari_terlambat']);
            }

            foreach ($jtData['akan_jatuh_tempo'] as $row) {
                $keys = array_keys($row);
                sort($keys);
                $expected = array_merge($allowedRowKeys, ['hari_menuju_jatuh_tempo']);
                sort($expected);
                $this->assertSame($expected, $keys);
                $this->assertGreaterThanOrEqual('2026-11-24', $row['jatuh_tempo_tgl']);
                $this->assertLessThanOrEqual('2026-12-01', $row['jatuh_tempo_tgl']);
            }

            // 2. Live DB test comparing BookingPerBulanTool & KonversiBookingAkadTool against BookingAkadReportService
            $reportService = new BookingAkadReportService(new BookingAkadReportRepository($db));
            $expectedSummary = $reportService->getSummary($idProyek, $tahun);

            $menuAccess = new MenuAccessService();
            $proyekRepo = new ProyekRepository();
            $this->bindDbConnection($menuAccess, $db);
            $this->bindDbConnection($proyekRepo, $db);

            $authorizer = new CopilotAuthorizer($menuAccess, $proyekRepo);
            $registry = new ToolRegistry($authorizer, $guard, [
                new JatuhTempoCicilanTool($authorizer, $jtRepo, $guard),
                new BookingPerBulanTool($authorizer, $reportService, $guard),
                new KonversiBookingAkadTool($authorizer, $reportService, $guard),
            ]);

            $bookingResult = $registry->execute($adminUserId, 'get_booking_per_bulan', [
                'proyek' => $namaProyek,
                'tahun'  => $tahun,
            ]);

            $this->assertSame('ok', $bookingResult['status']);
            $this->assertSame($idProyek, $bookingResult['proyek']['id_proyek']);
            $this->assertSame($expectedSummary['totals'][$tahun]['booking'], $bookingResult['total_booking']);
            $this->assertCount(12, $bookingResult['months']);

            foreach ($bookingResult['months'] as $idx => $monthRow) {
                $mNum = $monthRow['month'];
                $this->assertSame(
                    $expectedSummary['months'][$idx]['values'][$tahun]['booking'],
                    $monthRow['booking'],
                    "Month {$mNum} booking mismatch"
                );
            }

            $konversiResult = $registry->execute($adminUserId, 'get_konversi_booking_akad', [
                'proyek' => $namaProyek,
                'tahun'  => $tahun,
            ]);

            $this->assertSame('ok', $konversiResult['status']);
            $this->assertSame($expectedSummary['totals'][$tahun]['total_booking_cohort'], $konversiResult['totals']['total_booking_cohort']);
            $this->assertSame($expectedSummary['totals'][$tahun]['booking_converted_akad'], $konversiResult['totals']['booking_converted_akad']);
            $this->assertSame($expectedSummary['totals'][$tahun]['konversi'], $konversiResult['totals']['konversi']);
            $this->assertSame($expectedSummary['totals'][$tahun]['akad_indent'], $konversiResult['totals']['akad_indent']);
            $this->assertSame($expectedSummary['totals'][$tahun]['akad_ajb'], $konversiResult['totals']['akad_ajb']);
            $this->assertSame($expectedSummary['totals'][$tahun]['batal'], $konversiResult['totals']['batal']);

            foreach ($konversiResult['months'] as $idx => $monthRow) {
                $expectedMonth = $expectedSummary['months'][$idx]['values'][$tahun];
                $this->assertSame($expectedMonth['total_booking_cohort'], $monthRow['total_booking_cohort']);
                $this->assertSame($expectedMonth['booking_converted_akad'], $monthRow['booking_converted_akad']);
                $this->assertSame($expectedMonth['konversi'], $monthRow['konversi']);
                $this->assertSame($expectedMonth['akad_indent'], $monthRow['akad_indent']);
                $this->assertSame($expectedMonth['akad_ajb'], $monthRow['akad_ajb']);
                $this->assertSame($expectedMonth['batal'], $monthRow['batal']);
            }
        } finally {
            $db->close();
        }
    }

    private function connectLiveDefaultDb(): BaseConnection
    {
        try {
            $db = Database::connect('default', false);
            $db->connect();
            return $db;
        } catch (Throwable $e) {
            $this->markTestSkipped('Local MySQL database is not accessible: ' . $e->getMessage());
        }
    }

    private function bindDbConnection(object $target, BaseConnection $db): void
    {
        $prop = new ReflectionProperty($target, 'db');
        $prop->setAccessible(true);
        $prop->setValue($target, $db);
    }
}
