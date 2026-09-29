<?php

use App\Database\Migrations\ReconcileMkdt354Sbum;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Database;

require_once APPPATH . 'Database/Migrations/2026-09-25-000001_ReconcileMkdt354Sbum.php';

final class ReconcileMkdt354SbumMigrationTest extends CIUnitTestCase
{
    protected $db;

    protected function setUp(): void
    {
        parent::setUp();
        $this->db = Database::connect('tests', true);
        $prefix = $this->db->getPrefix();

        foreach (['history_log', 'mkdt'] as $table) {
            $this->db->query("DROP TABLE IF EXISTS {$prefix}{$table}");
        }

        $this->db->query("CREATE TABLE {$prefix}mkdt (
            id_mkdt INTEGER PRIMARY KEY, id_kavling INTEGER,
            harga_uang_muka REAL DEFAULT 0, harga_diskon_uang_muka REAL DEFAULT 0,
            harga_sbum REAL DEFAULT 0, harga_biaya_proses REAL DEFAULT 0,
            harga_penambahan_um REAL DEFAULT 0, harga_kpr_acc REAL DEFAULT 0,
            updated_at TEXT
        )");
        $this->db->query("CREATE TABLE {$prefix}history_log (
            id INTEGER PRIMARY KEY AUTOINCREMENT, module TEXT, reference_type TEXT,
            reference_id INTEGER, id_kavling INTEGER, action TEXT, summary TEXT,
            old_data TEXT, new_data TEXT, metadata TEXT, add_by INTEGER, created_at TEXT
        )");
        $this->db->resetDataCache();
    }

    public function testMigrationAppliesApprovedSbumAndDirectCorrectionsIdempotently(): void
    {
        $this->insertBaseline();
        $migration = new ReconcileMkdt354Sbum(Database::forge($this->db));

        $migration->up();
        $migration->up();

        $row = $this->db->table('mkdt')->where('id_mkdt', 354)->get()->getRowArray();
        $this->assertSame(4_000_000.0, (float) $row['harga_sbum']);
        $this->assertSame(3_000_000.0, (float) $row['harga_biaya_proses']);
        $this->assertSame(0.0, (float) $row['harga_penambahan_um']);
        $this->assertSame(1, $this->db->table('history_log')->where('reference_id', 354)->countAllResults());
    }

    public function testMigrationStopsWhenBusinessFieldsHaveChanged(): void
    {
        $this->insertBaseline(['harga_sbum' => 1_000_000]);
        $migration = new ReconcileMkdt354Sbum(Database::forge($this->db));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('migration SBUM dihentikan');
        $migration->up();
    }

    public function testMigrationAcceptsDirectCorrectionsAppliedEarlier(): void
    {
        $this->insertBaseline([
            'harga_biaya_proses' => 3_000_000,
            'harga_penambahan_um' => 0,
        ]);

        (new ReconcileMkdt354Sbum(Database::forge($this->db)))->up();

        $row = $this->db->table('mkdt')->where('id_mkdt', 354)->get()->getRowArray();
        $this->assertSame(4_000_000.0, (float) $row['harga_sbum']);
        $this->assertSame(3_000_000.0, (float) $row['harga_biaya_proses']);
        $this->assertSame(0.0, (float) $row['harga_penambahan_um']);
    }

    private function insertBaseline(array $overrides = []): void
    {
        $this->db->table('mkdt')->insert(array_merge([
            'id_mkdt' => 354,
            'id_kavling' => 504,
            'harga_uang_muka' => 6_500_000,
            'harga_diskon_uang_muka' => 0,
            'harga_sbum' => 0,
            'harga_biaya_proses' => 0,
            'harga_penambahan_um' => 159_500_000,
            'harga_kpr_acc' => 0,
            'updated_at' => '2026-09-10 16:53:33',
        ], $overrides));
    }
}
