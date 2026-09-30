<?php

use App\Services\TransaksiService;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Database;

final class ConsumerReplacementServiceTest extends CIUnitTestCase
{
    protected $db;

    protected function setUp(): void
    {
        parent::setUp();
        $this->db = Database::connect('tests', true);
        $prefix = $this->db->getPrefix();
        foreach (['keuangan', 'log_pembayaran', 'konsumen', 'kavling', 'mkdt'] as $table) {
            $this->db->query("DROP TABLE IF EXISTS {$prefix}{$table}");
        }
        $this->db->query("CREATE TABLE {$prefix}mkdt (
            id_mkdt INTEGER PRIMARY KEY,
            id_kavling INTEGER NOT NULL,
            id_konsumen INTEGER NOT NULL,
            file_spptb TEXT,
            file_surat_kuasa TEXT,
            edit_by INTEGER,
            updated_at TEXT
        )");
        $this->db->query("CREATE TABLE {$prefix}kavling (
            id_kavling INTEGER PRIMARY KEY,
            id_mkdt INTEGER NOT NULL
        )");
        $this->db->query("CREATE TABLE {$prefix}konsumen (
            id_konsumen INTEGER PRIMARY KEY AUTOINCREMENT,
            no_spptb TEXT,
            nama_konsumen TEXT,
            nik TEXT,
            id_kavling INTEGER,
            status TEXT,
            uniq_id TEXT,
            add_by INTEGER,
            edit_by INTEGER,
            created_at TEXT,
            updated_at TEXT
        )");
        $this->db->query("CREATE TABLE {$prefix}keuangan (
            id_keuangan INTEGER PRIMARY KEY,
            id_mkdt INTEGER NOT NULL,
            nominal REAL NOT NULL
        )");
        $this->db->query("CREATE TABLE {$prefix}log_pembayaran (
            id_pembayaran INTEGER PRIMARY KEY,
            id_mkdt INTEGER NOT NULL,
            nominal REAL NOT NULL
        )");

        $this->db->table('konsumen')->insert([
            'id_konsumen' => 10,
            'no_spptb' => 'SPPTB-001',
            'nama_konsumen' => 'Konsumen Lama',
            'status' => 'Normal',
            'uniq_id' => 'TX-1',
        ]);
        $this->db->table('mkdt')->insert([
            'id_mkdt' => 20,
            'id_kavling' => 30,
            'id_konsumen' => 10,
            'file_spptb' => 'old-spptb.pdf',
            'file_surat_kuasa' => 'old-kuasa.pdf',
        ]);
        $this->db->table('kavling')->insert(['id_kavling' => 30, 'id_mkdt' => 20]);
        $this->db->table('keuangan')->insert(['id_keuangan' => 40, 'id_mkdt' => 20, 'nominal' => 1000]);
        $this->db->table('log_pembayaran')->insert(['id_pembayaran' => 50, 'id_mkdt' => 20, 'nominal' => 500]);
    }

    public function testReplacementKeepsMkdtAndFinancialRelationsWhileCreatingNewConsumer(): void
    {
        $service = $this->serviceWithDoubles();
        $result = $service->replaceConsumer(20, 30, 10, [
            'nama_konsumen' => 'Konsumen Baru',
            'nik' => '1234567890',
        ], [], '', 99);

        $this->assertTrue($result['success']);
        $this->assertSame(20, $result['id_mkdt']);
        $this->assertSame(10, $result['id_konsumen_lama']);
        $this->assertNotSame(10, $result['id_konsumen_baru']);

        $mkdt = $this->db->table('mkdt')->where('id_mkdt', 20)->get()->getRowArray();
        $newConsumer = $this->db->table('konsumen')->where('id_konsumen', $result['id_konsumen_baru'])->get()->getRowArray();
        $oldConsumer = $this->db->table('konsumen')->where('id_konsumen', 10)->get()->getRowArray();
        $this->assertSame($result['id_konsumen_baru'], (int) $mkdt['id_konsumen']);
        $this->assertNull($mkdt['file_spptb']);
        $this->assertNull($mkdt['file_surat_kuasa']);
        $this->assertSame('SPPTB-001', $newConsumer['no_spptb']);
        $this->assertSame('Ganti Nama', $oldConsumer['status']);
        $this->assertSame(1, $this->db->table('keuangan')->where('id_mkdt', 20)->countAllResults());
        $this->assertSame(1, $this->db->table('log_pembayaran')->where('id_mkdt', 20)->countAllResults());
    }

    public function testStaleConsumerReturnsConflictWithoutChangingAnything(): void
    {
        $service = $this->serviceWithDoubles();
        $result = $service->replaceConsumer(20, 30, 999, ['nama_konsumen' => 'Baru'], [], '', 99);

        $this->assertFalse($result['success']);
        $this->assertSame(409, $result['status_code']);
        $this->assertSame(10, (int) $this->db->table('mkdt')->where('id_mkdt', 20)->get()->getRow()->id_konsumen);
        $this->assertSame(1, $this->db->table('konsumen')->countAllResults());
    }

    public function testAlreadyReplacedConsumerReturnsConflictWithoutChangingAnything(): void
    {
        $service = $this->serviceWithDoubles(oldConsumerStatus: 'Ganti Nama');
        $result = $service->replaceConsumer(20, 30, 10, ['nama_konsumen' => 'Baru'], [], '', 99);

        $this->assertFalse($result['success']);
        $this->assertSame(409, $result['status_code']);
        $this->assertSame(10, (int) $this->db->table('mkdt')->where('id_mkdt', 20)->get()->getRow()->id_konsumen);
        $this->assertSame(1, $this->db->table('konsumen')->countAllResults());
    }

    public function testHistoryFailureRollsBackConsumerAndMkdtChanges(): void
    {
        $service = $this->serviceWithDoubles(historySucceeds: false);
        $result = $service->replaceConsumer(20, 30, 10, ['nama_konsumen' => 'Baru'], [], '', 99);

        $this->assertFalse($result['success']);
        $this->assertSame(10, (int) $this->db->table('mkdt')->where('id_mkdt', 20)->get()->getRow()->id_konsumen);
        $this->assertSame('Normal', $this->db->table('konsumen')->where('id_konsumen', 10)->get()->getRow()->status);
        $this->assertSame(1, $this->db->table('konsumen')->countAllResults());
    }

    public function testInvalidReferralRollsBackConsumerAndMkdtChanges(): void
    {
        $service = $this->serviceWithDoubles(referralSucceeds: false);
        $result = $service->replaceConsumer(20, 30, 10, ['nama_konsumen' => 'Baru'], [], 'INVALID', 99);

        $this->assertFalse($result['success']);
        $this->assertSame(10, (int) $this->db->table('mkdt')->where('id_mkdt', 20)->get()->getRow()->id_konsumen);
        $this->assertSame(1, $this->db->table('konsumen')->countAllResults());
    }

    public function testInvalidUploadRollsBackBeforeCreatingConsumer(): void
    {
        $service = $this->serviceWithDoubles();
        $invalidUpload = new class {
            public function getError(): int { return UPLOAD_ERR_CANT_WRITE; }
            public function isValid(): bool { return false; }
            public function hasMoved(): bool { return false; }
        };
        $result = $service->replaceConsumer(
            20,
            30,
            10,
            ['nama_konsumen' => 'Baru'],
            ['file_ktp' => $invalidUpload],
            '',
            99
        );

        $this->assertFalse($result['success']);
        $this->assertSame(10, (int) $this->db->table('mkdt')->where('id_mkdt', 20)->get()->getRow()->id_konsumen);
        $this->assertSame(1, $this->db->table('konsumen')->countAllResults());
    }

    private function serviceWithDoubles(
        bool $historySucceeds = true,
        bool $referralSucceeds = true,
        string $oldConsumerStatus = 'Normal'
    ): TransaksiService
    {
        $service = new TransaksiService();
        $db = $this->db;
        $context = (object) [
            'id_mkdt' => 20,
            'id_kavling' => 30,
            'id_konsumen' => 10,
            'kavling_id_mkdt' => 20,
            'no_spptb' => 'SPPTB-001',
            'nama_konsumen' => 'Konsumen Lama',
            'konsumen_status' => $oldConsumerStatus,
            'uniq_id' => 'TX-1',
            'status_mkdt' => 'Booking',
        ];

        $this->setProperty($service, 'db', $db);
        $this->setProperty($service, 'mkdt', new class($db) {
            public function __construct(private $db) {}
            public function update(int $id, array $data): bool {
                return (bool) $this->db->table('mkdt')->where('id_mkdt', $id)->update($data);
            }
        });
        $this->setProperty($service, 'transaksiRepo', new class($context) {
            public function __construct(private object $context) {}
            public function findNikUsage(): array { return []; }
            public function lockConsumerReplacementContext(): object { return $this->context; }
            public function getSpptbData(): object {
                return (object) array_merge((array) $this->context, ['file_ktp' => null, 'file_npwp' => null]);
            }
        });
        $this->setProperty($service, 'konsumenService', new class($db) {
            public function __construct(private $db) {}
            public function upsert(?int $id, array $data): int {
                $allowed = array_intersect_key($data, array_flip([
                    'no_spptb', 'nama_konsumen', 'nik', 'id_kavling', 'status', 'uniq_id', 'add_by', 'edit_by',
                ]));
                $this->db->table('konsumen')->insert($allowed);
                return (int) $this->db->insertID();
            }
        });
        $this->setProperty($service, 'keuRepo', new class {
            public function getTagihanOnlyByID(): array { return [(object) ['id_keuangan' => 40, 'nominal' => 1000]]; }
        });
        $this->setProperty($service, 'kavlingRepo', new class {
            public function getIdProyekByKavling(): int { return 1; }
        });
        $this->setProperty($service, 'referralService', new class($referralSucceeds) {
            public function __construct(private bool $success) {}
            public function generateKodeReferal(): string { return 'AB0011'; }
            public function syncReferralForMkdt(): array {
                return ['success' => $this->success, 'message' => 'Referral tidak valid', 'activated_bonus_ids' => []];
            }
            public function notifyMgmBonusEligible(): void {}
        });
        $this->setProperty($service, 'mkdtHistoryService', new class($historySucceeds) {
            public function __construct(private bool $success) {}
            public function log(): bool { return $this->success; }
        });
        $this->setProperty($service, 'notif', new class {
            public function tambah_notif(): bool { return true; }
        });

        return $service;
    }

    private function setProperty(object $target, string $name, mixed $value): void
    {
        $property = new ReflectionProperty($target, $name);
        $property->setValue($target, $value);
    }
}
