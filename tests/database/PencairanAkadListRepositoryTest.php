<?php

use App\Repositories\PencairanAkadListRepository;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Database;

final class PencairanAkadListRepositoryTest extends CIUnitTestCase
{
    private BaseConnection $testDb;
    private PencairanAkadListRepository $repository;

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

        $this->createTables();
        $this->seedData();
        $this->repository = new PencairanAkadListRepository($this->testDb);
    }

    protected function tearDown(): void
    {
        $this->testDb->close();
        parent::tearDown();
    }

    public function testPengajuanDateFilterIsInclusiveAndSummaryRemainsCumulative(): void
    {
        $filters = $this->filters([
            'jenis_tanggal' => 'tanggal_pengajuan',
            'tanggal_mulai' => '2026-02-10',
            'tanggal_selesai' => '2026-02-10',
        ]);

        $rows = $this->repository->buildListQuery($filters)->get()->getResultArray();
        $summary = $this->repository->getSummary($filters);

        $this->assertSame([1, 3], array_map('intval', array_column($rows, 'id_mkdt')));
        $this->assertSame(2, $summary['jumlah_kavling']);
        $this->assertSame(5000.0, $summary['total_harga_jual']);
        $this->assertSame(4000.0, $summary['total_acc_kpr']);
        $this->assertSame(1200.0, $summary['total_pengajuan_outstanding']);
        $this->assertSame(500.0, $summary['total_sudah_cair']);
        $this->assertSame(3500.0, $summary['total_sisa']);
    }

    public function testPencairanDateFilterExcludesVoidAndZeroPayments(): void
    {
        $filters = $this->filters([
            'jenis_tanggal' => 'tanggal_pencairan',
            'tanggal_mulai' => '2026-02-15',
            'tanggal_selesai' => '2026-02-15',
        ]);

        $rows = $this->repository->buildListQuery($filters)->get()->getResultArray();
        $summary = $this->repository->getSummary($filters);

        $this->assertSame([1], array_map('intval', array_column($rows, 'id_mkdt')));
        $this->assertSame(1, $summary['jumlah_kavling']);
        $this->assertSame(700.0, $summary['total_pengajuan_outstanding']);
        $this->assertSame(500.0, $summary['total_sudah_cair']);
    }

    public function testAkadBoundaryAndGlobalSearchAreAppliedToSummary(): void
    {
        $filters = $this->filters([
            'jenis_tanggal' => 'tanggal_akad',
            'tanggal_mulai' => '2026-01-01',
            'tanggal_selesai' => '2026-03-01',
        ]);

        $summary = $this->repository->getSummary($filters, 'C-3');

        $this->assertSame(1, $summary['jumlah_kavling']);
        $this->assertSame(3500.0, $summary['total_harga_jual']);
        $this->assertSame(3000.0, $summary['total_sisa']);
    }

    public function testProjectClusterRoadAndStatusFiltersRemainCombined(): void
    {
        $filters = $this->filters([
            'id_proyek' => 10,
            'id_cluster' => 20,
            'id_jalan' => 30,
            'status_cair' => 'sudah_cair',
        ]);

        $rows = $this->repository->buildListQuery($filters)->get()->getResultArray();

        $this->assertSame([], $rows);
    }

    private function filters(array $overrides = []): array
    {
        return array_merge([
            'id_proyek' => 0,
            'id_cluster' => 0,
            'id_jalan' => 0,
            'status_cair' => '',
            'jenis_tanggal' => 'tanggal_akad',
            'tanggal_mulai' => null,
            'tanggal_selesai' => null,
        ], $overrides);
    }

    private function createTables(): void
    {
        $this->testDb->query('CREATE TABLE mkdt (id_mkdt INTEGER PRIMARY KEY, id_kavling INTEGER, id_konsumen INTEGER, akad_tgl TEXT, harga_kpr_acc REAL, is_kpr INTEGER, status_mkdt TEXT)');
        $this->testDb->query('CREATE TABLE kavling (id_kavling INTEGER PRIMARY KEY, id_mkdt INTEGER, id_jalan INTEGER, no_kavling TEXT, id_tipe INTEGER, harga_akhir INTEGER)');
        $this->testDb->query('CREATE TABLE jalan (id_jalan INTEGER PRIMARY KEY, id_cluster INTEGER, nama_jalan TEXT)');
        $this->testDb->query('CREATE TABLE cluster (id_cluster INTEGER PRIMARY KEY, id_proyek INTEGER)');
        $this->testDb->query('CREATE TABLE proyek (id_proyek INTEGER PRIMARY KEY, nama_proyek TEXT)');
        $this->testDb->query('CREATE TABLE konsumen (id_konsumen INTEGER PRIMARY KEY, nama_konsumen TEXT)');
        $this->testDb->query('CREATE TABLE hargajual (id INTEGER PRIMARY KEY, hargajual REAL)');
        $this->testDb->query('CREATE TABLE tipe (id_tipe INTEGER PRIMARY KEY, tipe_rumah TEXT)');
        $this->testDb->query('CREATE TABLE pencairan_akad_plan (id INTEGER PRIMARY KEY, id_mkdt INTEGER, total_hasil_akad REAL)');
        $this->testDb->query('CREATE TABLE pencairan_akad_pengajuan (id INTEGER PRIMARY KEY, id_plan INTEGER, tanggal_pengajuan TEXT, total_pengajuan REAL, total_cair REAL, status TEXT)');
        $this->testDb->query('CREATE TABLE pencairan_akad_payment (id INTEGER PRIMARY KEY, id_pengajuan INTEGER, tanggal_cair TEXT, total_cair REAL)');
    }

    private function seedData(): void
    {
        $this->testDb->table('proyek')->insert(['id_proyek' => 10, 'nama_proyek' => 'Proyek A']);
        $this->testDb->table('cluster')->insert(['id_cluster' => 20, 'id_proyek' => 10]);
        $this->testDb->table('jalan')->insert(['id_jalan' => 30, 'id_cluster' => 20, 'nama_jalan' => 'Blok A']);
        $this->testDb->table('tipe')->insert(['id_tipe' => 40, 'tipe_rumah' => '36/72']);

        $this->testDb->table('konsumen')->insertBatch([
            ['id_konsumen' => 1, 'nama_konsumen' => 'Alpha'],
            ['id_konsumen' => 2, 'nama_konsumen' => 'Beta'],
            ['id_konsumen' => 3, 'nama_konsumen' => 'Charlie'],
        ]);
        $this->testDb->table('hargajual')->insertBatch([
            ['id' => 1, 'hargajual' => 1500],
            ['id' => 2, 'hargajual' => 2500],
            ['id' => 3, 'hargajual' => 3500],
        ]);
        $this->testDb->table('mkdt')->insertBatch([
            ['id_mkdt' => 1, 'id_kavling' => 101, 'id_konsumen' => 1, 'akad_tgl' => '2026-01-01', 'harga_kpr_acc' => 1000, 'is_kpr' => 1, 'status_mkdt' => 'Akad'],
            ['id_mkdt' => 2, 'id_kavling' => 102, 'id_konsumen' => 2, 'akad_tgl' => '2026-02-01', 'harga_kpr_acc' => 2000, 'is_kpr' => 1, 'status_mkdt' => 'Akad'],
            ['id_mkdt' => 3, 'id_kavling' => 103, 'id_konsumen' => 3, 'akad_tgl' => '2026-03-01', 'harga_kpr_acc' => 3000, 'is_kpr' => 1, 'status_mkdt' => 'Akad'],
        ]);
        $this->testDb->table('kavling')->insertBatch([
            ['id_kavling' => 101, 'id_mkdt' => 1, 'id_jalan' => 30, 'no_kavling' => 'C-1', 'id_tipe' => 40, 'harga_akhir' => 1],
            ['id_kavling' => 102, 'id_mkdt' => 2, 'id_jalan' => 30, 'no_kavling' => 'C-2', 'id_tipe' => 40, 'harga_akhir' => 2],
            ['id_kavling' => 103, 'id_mkdt' => 3, 'id_jalan' => 30, 'no_kavling' => 'C-3', 'id_tipe' => 40, 'harga_akhir' => 3],
        ]);
        $this->testDb->table('pencairan_akad_plan')->insertBatch([
            ['id' => 201, 'id_mkdt' => 1, 'total_hasil_akad' => 1000],
            ['id' => 202, 'id_mkdt' => 2, 'total_hasil_akad' => 2000],
            ['id' => 203, 'id_mkdt' => 3, 'total_hasil_akad' => 3000],
        ]);
        $this->testDb->table('pencairan_akad_pengajuan')->insertBatch([
            ['id' => 301, 'id_plan' => 201, 'tanggal_pengajuan' => '2026-02-10', 'total_pengajuan' => 700, 'total_cair' => 400, 'status' => 'active'],
            ['id' => 302, 'id_plan' => 201, 'tanggal_pengajuan' => '2025-12-01', 'total_pengajuan' => 500, 'total_cair' => 100, 'status' => 'partial'],
            ['id' => 303, 'id_plan' => 202, 'tanggal_pengajuan' => '2026-02-10', 'total_pengajuan' => 1000, 'total_cair' => 900, 'status' => 'void'],
            ['id' => 304, 'id_plan' => 202, 'tanggal_pengajuan' => '2026-03-10', 'total_pengajuan' => 600, 'total_cair' => 0, 'status' => 'active'],
            ['id' => 305, 'id_plan' => 203, 'tanggal_pengajuan' => '2026-02-10', 'total_pengajuan' => 500, 'total_cair' => 0, 'status' => 'active'],
        ]);
        $this->testDb->table('pencairan_akad_payment')->insertBatch([
            ['id' => 401, 'id_pengajuan' => 301, 'tanggal_cair' => '2026-02-15', 'total_cair' => 400],
            ['id' => 402, 'id_pengajuan' => 302, 'tanggal_cair' => '2025-12-15', 'total_cair' => 100],
            ['id' => 403, 'id_pengajuan' => 303, 'tanggal_cair' => '2026-02-15', 'total_cair' => 900],
            ['id' => 404, 'id_pengajuan' => 305, 'tanggal_cair' => '2026-02-15', 'total_cair' => 0],
        ]);
    }
}
