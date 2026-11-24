<?php

use App\Repositories\Mkdt\NupRepository;
use App\Repositories\Transaksi\TransaksiRepository;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Database;

final class TransaksiRepositorySpptbLuasTanahTest extends CIUnitTestCase
{
    private BaseConnection $testDb;
    private TransaksiRepository $transaksiRepository;
    private NupRepository $nupRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testDb = Database::connect([
            'DBDriver'    => 'SQLite3',
            'database'    => ':memory:',
            'DBPrefix'    => '',
            'DBDebug'     => true,
            'foreignKeys' => false,
        ], false);

        $this->createTables();
        $this->seedData();

        $this->transaksiRepository = new TransaksiRepository($this->testDb);
        $this->nupRepository       = new NupRepository($this->testDb);
    }

    protected function tearDown(): void
    {
        $this->testDb->close();
        parent::tearDown();
    }

    public function testGetSpptbDataSelectClausePlacesMkdtBeforeUnitColumnsAndUsesCaseExpression(): void
    {
        $row = $this->transaksiRepository->getSpptbData(1, 373);

        $this->assertNotNull($row);

        $sql = (string) $this->testDb->getLastQuery();
        $this->assertSame(
            1,
            preg_match('/^\s*SELECT\s+(.*?)\s+FROM\s+/is', $sql, $selectMatches),
            'Query getSpptbData() harus berupa SELECT ... FROM yang valid.'
        );

        $selectClause = $selectMatches[1];

        $this->assertSame(
            1,
            preg_match('/(?<![a-z0-9_])[`"]?mkdt[`"]?\.\*/i', $selectClause, $mkdtMatch, PREG_OFFSET_CAPTURE),
            'Klausa SELECT harus menyertakan mkdt.*.'
        );
        $this->assertSame(
            1,
            preg_match('/(?<![a-z0-9_])[`"]?proyek[`"]?\.[`"]?id_proyek[`"]?(?![a-z0-9_])/i', $selectClause, $proyekIdMatch, PREG_OFFSET_CAPTURE),
            'Klausa SELECT harus menyertakan proyek.id_proyek.'
        );
        $this->assertSame(
            1,
            preg_match('/(?<![a-z0-9_])[`"]?kavling[`"]?\.[`"]?status_tanah[`"]?(?![a-z0-9_])/i', $selectClause, $statusTanahMatch, PREG_OFFSET_CAPTURE),
            'Klausa SELECT harus menyertakan kavling.status_tanah.'
        );
        $this->assertSame(
            1,
            preg_match('/\bCASE\b[\s\S]+?\bEND\s+AS\s+[`"]?luas_tanah[`"]?(?![a-z0-9_])/i', $selectClause, $luasTanahCaseMatch, PREG_OFFSET_CAPTURE),
            'Klausa SELECT harus menggunakan ekspresi CASE ... END AS luas_tanah.'
        );

        $mkdtPos          = $mkdtMatch[0][1];
        $proyekIdPos      = $proyekIdMatch[0][1];
        $statusTanahPos   = $statusTanahMatch[0][1];
        $luasTanahCasePos = $luasTanahCaseMatch[0][1];
        $luasTanahExpr    = $luasTanahCaseMatch[0][0];

        $this->assertLessThan(
            $proyekIdPos,
            $mkdtPos,
            'mkdt.* harus ditempatkan sebelum proyek.id_proyek untuk mencegah column shadowing.'
        );
        $this->assertLessThan(
            $luasTanahCasePos,
            $mkdtPos,
            'mkdt.* harus ditempatkan sebelum ekspresi luas_tanah untuk mencegah column shadowing.'
        );
        $this->assertLessThan(
            $statusTanahPos,
            $mkdtPos,
            'mkdt.* harus ditempatkan sebelum kavling.status_tanah untuk mencegah column shadowing.'
        );
        $this->assertDoesNotMatchRegularExpression(
            '/[`"](?:CASE|WHEN|THEN|ELSE|END|COALESCE|NULLIF)\b|\b(?:CASE|WHEN|THEN|ELSE|END|COALESCE|NULLIF)[`"]/i',
            $luasTanahExpr,
            'Ekspresi CASE ... END AS luas_tanah tidak boleh rusak oleh auto-escaping Query Builder.'
        );
        $this->assertSame(1, (int) ($row->id_proyek ?? 0), 'proyek.id_proyek tidak boleh tertimpa oleh mkdt.id_proyek.');
    }

    /**
     * @dataProvider luasTanahResolutionProvider
     */
    public function testGetSpptbDataResolvesLuasTanahAcrossAllFiveCases(
        int $idKavling,
        int $idMkdt,
        string $expectedStatusTanah,
        float $expectedLuasTanah
    ): void {
        $row = $this->transaksiRepository->getSpptbData($idKavling, $idMkdt);

        $this->assertNotNull($row);
        $this->assertObjectHasProperty('luas_tanah', $row);
        $this->assertNotNull($row->luas_tanah);
        $this->assertTrue(is_numeric($row->luas_tanah), 'luas_tanah pada SPPTB harus bernilai numerik.');
        $this->assertSame(
            $expectedLuasTanah,
            (float) $row->luas_tanah,
            sprintf('luas_tanah SPPTB untuk kavling #%d (mkdt #%d) harus %s.', $idKavling, $idMkdt, $expectedLuasTanah)
        );
        $this->assertObjectHasProperty('status_tanah', $row);
        $this->assertSame($expectedStatusTanah, $row->status_tanah);
    }

    /**
     * @dataProvider luasTanahResolutionProvider
     */
    public function testGetKavlingOptionsForTransferIncludesStatusTanahAndResolvesLuasTanahAcrossAllFiveCases(
        int $idKavling,
        int $idMkdt,
        string $expectedStatusTanah,
        float $expectedLuasTanah
    ): void {
        $options = $this->nupRepository->getKavlingOptionsForTransfer(1);

        $indexed = [];
        foreach ($options as $option) {
            $indexed[(int) $option['id_kavling']] = $option;
        }

        $this->assertArrayHasKey($idKavling, $indexed, "Kavling #{$idKavling} harus tersedia di opsi transfer.");
        $item = $indexed[$idKavling];

        $this->assertArrayHasKey('status_tanah', $item);
        $this->assertSame($expectedStatusTanah, $item['status_tanah']);
        $this->assertArrayHasKey('luas_tanah', $item);
        $this->assertNotNull($item['luas_tanah']);
        $this->assertTrue(is_numeric($item['luas_tanah']), 'luas_tanah pada opsi kavling transfer harus bernilai numerik.');
        $this->assertSame($expectedLuasTanah, (float) $item['luas_tanah']);
    }

    public static function luasTanahResolutionProvider(): array
    {
        return [
            'a_standar_stale_72_hargajual_60_tipe_60_dahayu_4_no_2' => [1, 373, 'Standar', 60.0],
            'b_standar_kavling_0_hargajual_60_tipe_60'              => [2, 374, 'Standar', 60.0],
            'c_standar_kavling_72_hargajual_72_tipe_60'             => [3, 375, 'Standar', 72.0],
            'd_standar_no_hargajual_fallback_tipe_60'               => [4, 376, 'Standar', 60.0],
            'e_kelebihan_tanah_107_hargajual_72_tipe_72'            => [5, 377, 'Kelebihan Tanah', 107.0],
        ];
    }

    private function createTables(): void
    {
        $this->testDb->query('CREATE TABLE proyek (
            id_proyek INTEGER PRIMARY KEY,
            nama_proyek TEXT,
            alamat_proyek TEXT,
            kelurahan TEXT,
            kecamatan TEXT,
            kota TEXT,
            provinsi TEXT,
            nama_pt TEXT
        )');

        $this->testDb->query('CREATE TABLE cluster (
            id_cluster INTEGER PRIMARY KEY,
            id_proyek INTEGER NOT NULL,
            nama_cluster TEXT
        )');

        $this->testDb->query('CREATE TABLE jalan (
            id_jalan INTEGER PRIMARY KEY,
            id_cluster INTEGER NOT NULL,
            nama_jalan TEXT
        )');

        $this->testDb->query('CREATE TABLE tipe (
            id_tipe INTEGER PRIMARY KEY,
            no_tipe_rumah TEXT,
            tipe_rumah TEXT,
            lb REAL,
            lt REAL
        )');

        $this->testDb->query('CREATE TABLE hargajual (
            id INTEGER PRIMARY KEY,
            id_tipe INTEGER,
            hargajual REAL,
            lt REAL
        )');

        $this->testDb->query('CREATE TABLE konsumen (
            id_konsumen INTEGER PRIMARY KEY,
            no_spptb TEXT,
            kode_referal TEXT,
            nama_konsumen TEXT,
            nik TEXT,
            npwp TEXT,
            file_npwp TEXT,
            file_ktp TEXT,
            file_data_diri TEXT,
            hp_konsumen TEXT,
            alamat_konsumen TEXT,
            tel_instansi TEXT,
            email_konsumen TEXT,
            sales TEXT,
            nama_instansi TEXT,
            alamat_instansi TEXT,
            email_instansi TEXT,
            alamat_surat TEXT,
            pekerjaan TEXT,
            bidang_pekerjaan TEXT,
            lama_bekerja TEXT,
            status_pernikahan TEXT,
            status_pekerjaan_pasangan TEXT,
            hp_pasangan TEXT,
            nik_pasangan TEXT,
            nama_pasangan TEXT,
            instansi_pasangan TEXT
        )');

        $this->testDb->query('CREATE TABLE referrals (
            id INTEGER PRIMARY KEY,
            id_mkdt_referred INTEGER,
            id_konsumen_referrer INTEGER
        )');

        $this->testDb->query('CREATE TABLE kavling (
            id_kavling INTEGER PRIMARY KEY,
            id_jalan INTEGER NOT NULL,
            id_tipe INTEGER,
            id_mkdt INTEGER,
            id_keuangan INTEGER,
            id_legal INTEGER,
            id_produksi INTEGER,
            id_komplain INTEGER,
            no_kavling TEXT,
            luas_tanah REAL,
            status_tanah TEXT,
            harga_akhir INTEGER,
            harga_akhir_tgl TEXT,
            harga_akhir_oleh INTEGER
        )');

        $this->testDb->query('CREATE TABLE mkdt (
            id_mkdt INTEGER PRIMARY KEY,
            id_kavling INTEGER,
            id_konsumen INTEGER,
            id_hargajual INTEGER,
            id_proyek INTEGER,
            status_mkdt TEXT,
            is_batal INTEGER DEFAULT 0,
            uniq_id TEXT,
            is_ganti_nama TEXT,
            created_at TEXT
        )');
    }

    private function seedData(): void
    {
        $this->testDb->table('proyek')->insert([
            'id_proyek'     => 1,
            'nama_proyek'   => 'Proyek Dahayu',
            'alamat_proyek' => 'Jl. Dahayu Raya',
            'kelurahan'     => 'Kelurahan Test',
            'kecamatan'     => 'Kecamatan Test',
            'kota'          => 'Kota Test',
            'provinsi'      => 'Provinsi Test',
            'nama_pt'       => 'PT Dahayu Test',
        ]);

        $this->testDb->table('cluster')->insert([
            'id_cluster'   => 1,
            'id_proyek'    => 1,
            'nama_cluster' => 'Cluster Dahayu',
        ]);

        $this->testDb->table('jalan')->insert([
            'id_jalan'   => 1,
            'id_cluster' => 1,
            'nama_jalan' => 'DAHAYU 4',
        ]);

        $this->testDb->table('tipe')->insertBatch([
            ['id_tipe' => 10, 'no_tipe_rumah' => '22/60', 'tipe_rumah' => '22/60', 'lb' => 22, 'lt' => 60],
            ['id_tipe' => 20, 'no_tipe_rumah' => '36/72', 'tipe_rumah' => '36/72', 'lb' => 36, 'lt' => 72],
        ]);

        $this->testDb->table('hargajual')->insertBatch([
            ['id' => 101, 'id_tipe' => 10, 'hargajual' => 250000000, 'lt' => 60],
            ['id' => 102, 'id_tipe' => 20, 'hargajual' => 320000000, 'lt' => 72],
            ['id' => 103, 'id_tipe' => 20, 'hargajual' => 450000000, 'lt' => 72],
        ]);

        $this->testDb->table('konsumen')->insertBatch([
            ['id_konsumen' => 1, 'no_spptb' => 'SPPTB-373', 'nama_konsumen' => 'Konsumen A', 'sales' => 'Sales A'],
            ['id_konsumen' => 2, 'no_spptb' => 'SPPTB-374', 'nama_konsumen' => 'Konsumen B', 'sales' => 'Sales B'],
            ['id_konsumen' => 3, 'no_spptb' => 'SPPTB-375', 'nama_konsumen' => 'Konsumen C', 'sales' => 'Sales C'],
            ['id_konsumen' => 4, 'no_spptb' => 'SPPTB-376', 'nama_konsumen' => 'Konsumen D', 'sales' => 'Sales D'],
            ['id_konsumen' => 5, 'no_spptb' => 'SPPTB-377', 'nama_konsumen' => 'Konsumen E', 'sales' => 'Sales E'],
        ]);

        $this->testDb->table('kavling')->insertBatch([
            [
                'id_kavling'   => 1,
                'id_jalan'     => 1,
                'id_tipe'      => 10,
                'id_mkdt'      => 373,
                'no_kavling'   => '2',
                'luas_tanah'   => 72,
                'status_tanah' => 'Standar',
                'harga_akhir'  => 101,
            ],
            [
                'id_kavling'   => 2,
                'id_jalan'     => 1,
                'id_tipe'      => 10,
                'id_mkdt'      => 374,
                'no_kavling'   => '3',
                'luas_tanah'   => 0,
                'status_tanah' => 'Standar',
                'harga_akhir'  => 101,
            ],
            [
                'id_kavling'   => 3,
                'id_jalan'     => 1,
                'id_tipe'      => 10,
                'id_mkdt'      => 375,
                'no_kavling'   => '4',
                'luas_tanah'   => 72,
                'status_tanah' => 'Standar',
                'harga_akhir'  => 102,
            ],
            [
                'id_kavling'   => 4,
                'id_jalan'     => 1,
                'id_tipe'      => 10,
                'id_mkdt'      => 376,
                'no_kavling'   => '5',
                'luas_tanah'   => 72,
                'status_tanah' => 'Standar',
                'harga_akhir'  => null,
            ],
            [
                'id_kavling'   => 5,
                'id_jalan'     => 1,
                'id_tipe'      => 20,
                'id_mkdt'      => 377,
                'no_kavling'   => '6',
                'luas_tanah'   => 107,
                'status_tanah' => 'Kelebihan Tanah',
                'harga_akhir'  => 103,
            ],
        ]);

        $this->testDb->table('mkdt')->insertBatch([
            ['id_mkdt' => 373, 'id_kavling' => 1, 'id_konsumen' => 1, 'id_hargajual' => 101, 'id_proyek' => null, 'status_mkdt' => 'Booking', 'is_batal' => 0],
            ['id_mkdt' => 374, 'id_kavling' => 2, 'id_konsumen' => 2, 'id_hargajual' => 101, 'id_proyek' => null, 'status_mkdt' => 'Booking', 'is_batal' => 0],
            ['id_mkdt' => 375, 'id_kavling' => 3, 'id_konsumen' => 3, 'id_hargajual' => 102, 'id_proyek' => null, 'status_mkdt' => 'Booking', 'is_batal' => 0],
            ['id_mkdt' => 376, 'id_kavling' => 4, 'id_konsumen' => 4, 'id_hargajual' => null, 'id_proyek' => null, 'status_mkdt' => 'Booking', 'is_batal' => 0],
            ['id_mkdt' => 377, 'id_kavling' => 5, 'id_konsumen' => 5, 'id_hargajual' => 103, 'id_proyek' => null, 'status_mkdt' => 'Booking', 'is_batal' => 0],
        ]);
    }
}
