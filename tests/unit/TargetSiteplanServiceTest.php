<?php

use App\Repositories\Siteplan\TargetSiteplanRepository;
use App\Services\HistoryService;
use App\Services\Siteplan\TargetSiteplanService;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Database;

final class TargetSiteplanServiceTest extends CIUnitTestCase
{
    private BaseConnection $conn;
    private TargetSiteplanService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->conn = Database::connect();
        $this->resetSchema();
        $this->seedFixtures();

        $historyService = new HistoryService();
        $repository = new TargetSiteplanRepository($this->conn, $historyService);
        $this->service = new TargetSiteplanService(
            $repository,
            null,
            null,
            null,
            $historyService,
            $this->conn
        );
    }

    public function testAddKavlingToYearMasterActiveAndManualAndVersioningHistory(): void
    {
        $empty = $this->service->getByProjectAndYear(1, 2026);
        $this->assertNull($empty['target']);
        $this->assertSame([], $empty['kavlings']);
        $this->assertSame([], $empty['history']);

        // v1: tambah kavling 101 dengan master tipe aktif (id_tipe = 10)
        $res1 = $this->service->addKavlingToYear([
            'id_proyek'    => 1,
            'tahun_target' => 2026,
            'id_kavling'   => 101,
            'id_tipe'      => 10,
        ], 7);

        $this->assertNotNull($res1['target']);
        $idTarget = (int) $res1['target']->id_target;
        $this->assertSame(2026, (int) $res1['target']->tahun_target);
        $this->assertCount(1, $res1['kavlings']);
        $this->assertSame(101, $res1['kavlings'][0]->id_kavling);
        $this->assertSame(10, $res1['kavlings'][0]->id_tipe);
        $this->assertNull($res1['kavlings'][0]->tipe_manual);
        $this->assertSame('T36 / 36/72', $res1['kavlings'][0]->tipe_label);

        $this->assertCount(1, $res1['history']);
        $v1 = $res1['history'][0];
        $this->assertSame(1, $v1->version);
        $this->assertSame('v1', $v1->version_label);
        $this->assertSame('target_add_kavling', $v1->aksi);
        $this->assertNull($v1->before_snapshot);
        $this->assertIsArray($v1->after_snapshot);
        $this->assertCount(1, $v1->after_snapshot['kavlings']);
        $this->assertSame(1, $v1->diff_summary['added_count']);
        $this->assertSame(0, $v1->diff_summary['total_before']);
        $this->assertSame(1, $v1->diff_summary['total_after']);
        $this->assertSame('mkdt_user', $v1->requested_by_username);

        // v2: tambah kavling 102 (status NUP -> diizinkan) dengan tipe manual
        $res2 = $this->service->addKavlingToYear([
            'id_proyek'    => 1,
            'tahun_target' => 2026,
            'id_kavling'   => 102,
            'tipe_manual'  => 'Custom Corner 50/100',
        ], 7);

        $this->assertSame($idTarget, (int) $res2['target']->id_target);
        $this->assertCount(2, $res2['kavlings']);

        $byKavling = [];
        foreach ($res2['kavlings'] as $row) {
            $byKavling[$row->id_kavling] = $row;
        }
        $this->assertNull($byKavling[102]->id_tipe);
        $this->assertSame('Custom Corner 50/100', $byKavling[102]->tipe_manual);
        $this->assertSame('Custom Corner 50/100', $byKavling[102]->tipe_label);

        $this->assertCount(2, $res2['history']);
        $latest = $res2['history'][0];
        $oldest = $res2['history'][1];
        $this->assertSame(2, $latest->version);
        $this->assertSame('v2', $latest->version_label);
        $this->assertSame(1, $oldest->version);
        $this->assertSame('v1', $oldest->version_label);
        $this->assertIsArray($latest->before_snapshot);
        $this->assertCount(1, $latest->before_snapshot['kavlings']);
        $this->assertIsArray($latest->after_snapshot);
        $this->assertCount(2, $latest->after_snapshot['kavlings']);
        $this->assertSame(1, $latest->diff_summary['added_count']);
        $this->assertSame(1, $latest->diff_summary['total_before']);
        $this->assertSame(2, $latest->diff_summary['total_after']);

        // Duplikat kavling pada tahun yang sama ditolak jika update_if_exists tidak diaktifkan
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('sudah terdaftar');
        $this->service->addKavlingToYear([
            'id_proyek'    => 1,
            'tahun_target' => 2026,
            'id_kavling'   => 101,
            'id_tipe'      => 10,
        ], 7);
    }

    public function testAddKavlingRejectsActiveConsumer(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('konsumen aktif');

        $this->service->addKavlingToYear([
            'id_proyek'    => 1,
            'tahun_target' => 2026,
            'id_kavling'   => 103, // Booking aktif
            'id_tipe'      => 10,
        ], 7);
    }

    public function testAddKavlingRejectsInactiveTipe(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('tidak aktif');

        $this->service->addKavlingToYear([
            'id_proyek'    => 1,
            'tahun_target' => 2026,
            'id_kavling'   => 101,
            'id_tipe'      => 11, // is_active = 0
        ], 7);
    }

    public function testUpdateKavlingTipeManualToMasterActiveAndRejectsInactiveMaster(): void
    {
        $created = $this->service->addKavlingToYear([
            'id_proyek'    => 1,
            'tahun_target' => 2026,
            'id_kavling'   => 104, // batal booking -> diizinkan
            'tipe_manual'  => 'Custom Awal',
        ], 7);

        $idTargetKavling = (int) $created['kavlings'][0]->id_target_kavling;

        try {
            $this->service->updateKavlingTipe([
                'id_target_kavling' => $idTargetKavling,
                'id_tipe'           => 11, // nonaktif
            ], 7);
            $this->fail('Seharusnya menolak tipe nonaktif');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('tidak aktif', strtolower($e->getMessage()));
        }

        $updated = $this->service->updateKavlingTipe([
            'id_target_kavling' => $idTargetKavling,
            'id_tipe'           => 10,
        ], 7);

        $this->assertCount(1, $updated['kavlings']);
        $this->assertSame(10, $updated['kavlings'][0]->id_tipe);
        $this->assertNull($updated['kavlings'][0]->tipe_manual);
        $this->assertSame('T36 / 36/72', $updated['kavlings'][0]->tipe_label);

        $this->assertCount(2, $updated['history']);
        $latest = $updated['history'][0];
        $this->assertSame(2, $latest->version);
        $this->assertSame('v2', $latest->version_label);
        $this->assertSame('target_update_tipe', $latest->aksi);
        $this->assertSame(1, $latest->diff_summary['updated_count']);
        $this->assertSame('Custom Awal', $latest->diff_summary['updated'][0]['before_label']);
        $this->assertSame('T36 / 36/72', $latest->diff_summary['updated'][0]['after_label']);
    }

    public function testRemoveKavlingAllowsZeroKavlingsAndRecordsVersionedHistory(): void
    {
        $created = $this->service->addKavlingToYear([
            'id_proyek'    => 1,
            'tahun_target' => 2026,
            'id_kavling'   => 101,
            'id_tipe'      => 10,
        ], 7);

        $idTarget = (int) $created['target']->id_target;

        $afterRemove = $this->service->removeKavling([
            'id_target'  => $idTarget,
            'id_kavling' => 101,
        ], 7);

        $this->assertSame($idTarget, (int) $afterRemove['target']->id_target);
        $this->assertSame([], $afterRemove['kavlings']);
        $this->assertCount(2, $afterRemove['history']);

        $latest = $afterRemove['history'][0];
        $this->assertSame(2, $latest->version);
        $this->assertSame('v2', $latest->version_label);
        $this->assertSame('target_remove_kavling', $latest->aksi);
        $this->assertSame(1, $latest->diff_summary['removed_count']);
        $this->assertSame(1, $latest->diff_summary['total_before']);
        $this->assertSame(0, $latest->diff_summary['total_after']);
    }

    public function testSavePreservesExistingTipeAndRejectsActiveConsumer(): void
    {
        $created = $this->service->addKavlingToYear([
            'id_proyek'    => 1,
            'tahun_target' => 2026,
            'id_kavling'   => 101,
            'id_tipe'      => 10,
        ], 7);

        $idTarget = (int) $created['target']->id_target;

        $saved = $this->service->save([
            'id_target'    => $idTarget,
            'id_proyek'    => 1,
            'tahun_target' => 2026,
            'deskripsi'    => 'Target Revisi',
            'id_kavling'   => [101, 102],
        ]);

        $this->assertCount(2, $saved['kavlings']);
        $byKavling = [];
        foreach ($saved['kavlings'] as $row) {
            $byKavling[$row->id_kavling] = $row;
        }
        $this->assertSame(10, $byKavling[101]->id_tipe);
        $this->assertSame('T36 / 36/72', $byKavling[101]->tipe_label);
        $this->assertNull($byKavling[102]->id_tipe);

        $latest = $saved['history'][0];
        $this->assertSame(2, $latest->version);
        $this->assertSame('v2', $latest->version_label);
        $this->assertSame(1, $latest->diff_summary['added_count']);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('konsumen aktif');
        $this->service->save([
            'id_target'    => $idTarget,
            'id_proyek'    => 1,
            'tahun_target' => 2026,
            'deskripsi'    => 'Harus Gagal',
            'id_kavling'   => [101, 103],
        ]);
    }

    public function testPlanningRequestTipeChangeValidationsAndPendingGuard(): void
    {
        $created = $this->service->addKavlingToYear([
            'id_proyek'    => 1,
            'tahun_target' => 2026,
            'id_kavling'   => 101,
            'id_tipe'      => 10,
        ], 7);
        $idTarget = (int) $created['target']->id_target;

        $added102 = $this->service->addKavlingToYear([
            'id_proyek'    => 1,
            'tahun_target' => 2026,
            'id_kavling'   => 102,
            'id_tipe'      => 10,
        ], 7);

        $byKavling = [];
        foreach ($added102['kavlings'] as $row) {
            $byKavling[$row->id_kavling] = $row;
        }

        // Gagal jika tipe baru sama dengan tipe target saat ini
        try {
            $this->service->requestTipeChange([
                'id_target_kavling' => $byKavling[101]->id_target_kavling,
                'new_id_tipe'       => 10,
            ], 6);
            $this->fail('Seharusnya menolak tipe yang sama');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('harus berbeda', strtolower($e->getMessage()));
        }

        // Sukses request master aktif (kavling 101 -> id_tipe 12)
        $req1 = $this->service->requestTipeChange([
            'id_target_kavling' => $byKavling[101]->id_target_kavling,
            'new_id_tipe'       => 12,
            'catatan_request'   => 'Upgrade tipe blok depan',
        ], 6);

        $this->assertSame('pending', $req1['request']->status);
        $this->assertSame(10, $req1['request']->old_id_tipe);
        $this->assertSame(12, $req1['request']->new_id_tipe);
        $this->assertSame('T36 / 36/72', $req1['request']->old_tipe_label);
        $this->assertSame('T54 / 54/108', $req1['request']->new_tipe_label);
        $this->assertSame('planning_user', $req1['request']->request_by_username);
        $this->assertSame(['all' => 1, 'pending' => 1, 'approved' => 0, 'rejected' => 0], $req1['counts']);

        // Gagal jika masih ada pending request untuk kavling target yang sama
        try {
            $this->service->requestTipeChange([
                'id_target'       => $idTarget,
                'id_kavling'      => 101,
                'new_tipe_manual' => 'Custom Double',
            ], 6);
            $this->fail('Seharusnya menolak duplicate pending request');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('menunggu persetujuan', strtolower($e->getMessage()));
        }

        // Sukses request tipe manual (kavling 102)
        $req2 = $this->service->requestTipeChange([
            'id_target'       => $idTarget,
            'id_kavling'      => 102,
            'new_tipe_manual' => 'Custom Planning 60/120',
            'catatan_request' => 'Request tipe khusus sudut',
        ], 6);

        $this->assertSame('pending', $req2['request']->status);
        $this->assertNull($req2['request']->new_id_tipe);
        $this->assertSame('Custom Planning 60/120', $req2['request']->new_tipe_manual);
        $this->assertSame('Custom Planning 60/120', $req2['request']->new_tipe_label);
        $this->assertSame(['all' => 2, 'pending' => 2, 'approved' => 0, 'rejected' => 0], $req2['counts']);

        // Gagal jika kavling memiliki konsumen aktif (simulasikan kavling 103 terlanjur ada di target)
        $this->conn->table('target_siteplan_kavling')->insert([
            'id_target'  => $idTarget,
            'id_kavling' => 103,
            'id_tipe'    => 10,
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('konsumen aktif');
        $this->service->requestTipeChange([
            'id_target'   => $idTarget,
            'id_kavling'  => 103,
            'new_id_tipe' => 12,
        ], 6);
    }

    public function testMkdtApproveRequestUpdatesOnlyTargetKavlingAndPreservesMasterKavlingAndSeparatesHistoryUsernames(): void
    {
        $created = $this->service->addKavlingToYear([
            'id_proyek'    => 1,
            'tahun_target' => 2026,
            'id_kavling'   => 101,
            'id_tipe'      => 10,
        ], 7);
        $idTarget = (int) $created['target']->id_target;

        // Pastikan master kavling 101 awalnya id_tipe = 10
        $masterBefore = $this->conn->table('kavling')->where('id_kavling', 101)->get()->getRow();
        $this->assertSame(10, (int) $masterBefore->id_tipe);

        // Planning (user id 6 = planning_user) mengajukan ubah tipe ke 12
        $requested = $this->service->requestTipeChange([
            'id_target'       => $idTarget,
            'id_kavling'      => 101,
            'new_id_tipe'     => 12,
            'catatan_request' => 'Permintaan revisi siteplan Q2',
        ], 6);
        $idRequest = (int) $requested['request']->id_request;

        // MKDT (user id 7 = mkdt_user) menyetujui request
        $approved = $this->service->reviewRequest($idRequest, 'approve', 'ACC sesuai target Q2', 7);

        $this->assertSame('approved', $approved['request']->status);
        $this->assertSame('planning_user', $approved['request']->request_by_username);
        $this->assertSame('mkdt_user', $approved['request']->approve_by_username);
        $this->assertSame('ACC sesuai target Q2', $approved['request']->catatan_approval);
        $this->assertSame(['all' => 1, 'pending' => 0, 'approved' => 1, 'rejected' => 0], $approved['counts']);

        // target_siteplan_kavling terupdate ke tipe baru
        $this->assertCount(1, $approved['kavlings']);
        $this->assertSame(12, $approved['kavlings'][0]->id_tipe);
        $this->assertNull($approved['kavlings'][0]->tipe_manual);
        $this->assertSame('T54 / 54/108', $approved['kavlings'][0]->tipe_label);

        // Master kavling.id_tipe TETAP tidak berubah!
        $masterAfter = $this->conn->table('kavling')->where('id_kavling', 101)->get()->getRow();
        $this->assertSame(10, (int) $masterAfter->id_tipe);

        // History mencatat versi baru dengan pemisahan username requester (Planning) dan approver (MKDT)
        $this->assertCount(2, $approved['history']);
        $latest = $approved['history'][0];
        $this->assertSame(2, $latest->version);
        $this->assertSame('v2', $latest->version_label);
        $this->assertSame('target_approve_tipe', $latest->aksi);
        $this->assertSame('ACC Ubah Tipe Target', $latest->action_label);
        $this->assertSame('planning_user', $latest->requested_by_username);
        $this->assertSame('mkdt_user', $latest->approved_by_username);
        $this->assertSame($idRequest, $latest->id_request);
        $this->assertSame('Permintaan revisi siteplan Q2', $latest->catatan_request);
        $this->assertSame('ACC sesuai target Q2', $latest->catatan_approval);
        $this->assertIsArray($latest->before_snapshot);
        $this->assertIsArray($latest->after_snapshot);
        $this->assertSame(10, $latest->before_snapshot['kavlings'][0]['id_tipe']);
        $this->assertSame(12, $latest->after_snapshot['kavlings'][0]['id_tipe']);
        $this->assertSame(1, $latest->diff_summary['updated_count']);
        $this->assertSame(10, $latest->diff_summary['updated'][0]['before_id_tipe']);
        $this->assertSame(12, $latest->diff_summary['updated'][0]['after_id_tipe']);

        // Jika tipe master dinonaktifkan sebelum approval, approve harus ditolak
        $req2 = $this->service->requestTipeChange([
            'id_target'   => $idTarget,
            'id_kavling'  => 101,
            'new_id_tipe' => 10,
        ], 6);
        $this->conn->table('tipe')->where('id_tipe', 10)->update(['is_active' => 0]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('tidak aktif');
        $this->service->reviewRequest((int) $req2['request']->id_request, 'approve', null, 7);
    }

    public function testMkdtRejectRequestLeavesTargetKavlingMasterKavlingAndHistoryUnchanged(): void
    {
        $created = $this->service->addKavlingToYear([
            'id_proyek'    => 1,
            'tahun_target' => 2026,
            'id_kavling'   => 101,
            'id_tipe'      => 10,
        ], 7);
        $idTarget = (int) $created['target']->id_target;
        $this->assertCount(1, $created['history']);

        $requested = $this->service->requestTipeChange([
            'id_target'       => $idTarget,
            'id_kavling'      => 101,
            'new_tipe_manual' => 'Custom Reject',
            'catatan_request' => 'Usulan tipe custom',
        ], 6);
        $idRequest = (int) $requested['request']->id_request;

        $rejected = $this->service->reviewRequest($idRequest, 'reject', 'Tidak sesuai master plan', 7);

        $this->assertSame('rejected', $rejected['request']->status);
        $this->assertSame('Tidak sesuai master plan', $rejected['request']->catatan_approval);
        $this->assertSame('planning_user', $rejected['request']->request_by_username);
        $this->assertSame('mkdt_user', $rejected['request']->approve_by_username);
        $this->assertSame(['all' => 1, 'pending' => 0, 'approved' => 0, 'rejected' => 1], $rejected['counts']);

        // target_siteplan_kavling & kavling master tidak berubah
        $this->assertCount(1, $rejected['kavlings']);
        $this->assertSame(10, $rejected['kavlings'][0]->id_tipe);
        $this->assertNull($rejected['kavlings'][0]->tipe_manual);

        $masterAfter = $this->conn->table('kavling')->where('id_kavling', 101)->get()->getRow();
        $this->assertSame(10, (int) $masterAfter->id_tipe);

        // Jumlah versi history tidak bertambah
        $this->assertCount(1, $rejected['history']);

        // Tidak dapat direview ulang
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('sudah diproses');
        $this->service->reviewRequest($idRequest, 'approve', 'Coba ulang', 7);
    }

    public function testBatchAddKavlingToYearRecordsSingleHistoryAndRejectsActiveConsumer(): void
    {
        // Batch tambah kavling 101 & 102 -> 1 entri history v1
        $res1 = $this->service->addKavlingToYear([
            'id_proyek'    => 1,
            'tahun_target' => 2026,
            'id_kavlings'  => [101, 102],
            'id_tipe'      => 10,
        ], 7);

        $this->assertCount(2, $res1['kavlings']);
        $this->assertSame(2, $res1['batch_summary']['added_count']);
        $this->assertSame(0, $res1['batch_summary']['skipped_count']);
        $this->assertCount(1, $res1['history']);
        $this->assertSame(1, $res1['history'][0]->version);
        $this->assertSame('target_add_kavling', $res1['history'][0]->aksi);
        $this->assertSame(2, $res1['history'][0]->diff_summary['added_count']);
        $this->assertSame(0, $res1['history'][0]->diff_summary['total_before']);
        $this->assertSame(2, $res1['history'][0]->diff_summary['total_after']);

        // Batch campuran (102 sudah ada -> skip, 104 baru -> insert) -> 1 entri history v2
        $res2 = $this->service->addKavlingToYear([
            'id_proyek'    => 1,
            'tahun_target' => 2026,
            'id_kavlings'  => '102,104',
            'tipe_manual'  => 'Custom Batch',
        ], 7);

        $this->assertCount(3, $res2['kavlings']);
        $this->assertSame(1, $res2['batch_summary']['added_count']);
        $this->assertSame(1, $res2['batch_summary']['skipped_count']);
        $this->assertSame([104], $res2['batch_summary']['added_kavling_ids']);
        $this->assertSame([102], $res2['batch_summary']['skipped_kavling_ids']);
        $this->assertCount(2, $res2['history']);
        $this->assertSame(2, $res2['history'][0]->version);
        $this->assertSame(1, $res2['history'][0]->diff_summary['added_count']);
        $this->assertSame(2, $res2['history'][0]->diff_summary['total_before']);
        $this->assertSame(3, $res2['history'][0]->diff_summary['total_after']);

        // Batch yang mengandung kavling berkonsumen aktif (103) wajib ditolak
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('konsumen aktif');
        $this->service->addKavlingToYear([
            'id_proyek'    => 1,
            'tahun_target' => 2026,
            'id_kavlings'  => [101, 103],
            'id_tipe'      => 10,
        ], 7);
    }

    public function testBatchUpdateKavlingTipeRecordsSingleHistoryVersion(): void
    {
        $created = $this->service->addKavlingToYear([
            'id_proyek'    => 1,
            'tahun_target' => 2026,
            'id_kavlings'  => [101, 102, 104],
            'id_tipe'      => 10,
        ], 7);

        $byKavling = [];
        foreach ($created['kavlings'] as $row) {
            $byKavling[$row->id_kavling] = $row;
        }

        $updated = $this->service->updateKavlingTipe([
            'id_target_kavlings' => [
                $byKavling[101]->id_target_kavling,
                $byKavling[104]->id_target_kavling,
            ],
            'id_tipe' => 12,
        ], 7);

        $afterByKavling = [];
        foreach ($updated['kavlings'] as $row) {
            $afterByKavling[$row->id_kavling] = $row;
        }

        $this->assertSame(12, $afterByKavling[101]->id_tipe);
        $this->assertSame(10, $afterByKavling[102]->id_tipe);
        $this->assertSame(12, $afterByKavling[104]->id_tipe);
        $this->assertSame('T54 / 54/108', $afterByKavling[101]->tipe_label);
        $this->assertSame('T54 / 54/108', $afterByKavling[104]->tipe_label);
        $this->assertSame(2, $updated['batch_summary']['updated_count']);

        // Hanya ada 2 versi history (v1 add batch, v2 update batch)
        $this->assertCount(2, $updated['history']);
        $latest = $updated['history'][0];
        $this->assertSame(2, $latest->version);
        $this->assertSame('v2', $latest->version_label);
        $this->assertSame('target_update_tipe', $latest->aksi);
        $this->assertSame(2, $latest->diff_summary['updated_count']);
    }

    public function testGetKavlingsIncludesPlanningRequestStatusFromKavlingRequest(): void
    {
        $this->conn->query('DROP TABLE IF EXISTS db_kavling_request');
        $this->conn->query('CREATE TABLE db_kavling_request (
            id_request INTEGER PRIMARY KEY AUTOINCREMENT,
            id_proyek INTEGER,
            jenis_request TEXT,
            id_kavling INTEGER NULL,
            id_cluster INTEGER NULL,
            id_jalan INTEGER NULL,
            id_tipe INTEGER NULL,
            tipe_manual TEXT NULL,
            keterangan TEXT NULL,
            status TEXT DEFAULT "pending",
            alasan_tolak TEXT NULL,
            created_by INTEGER NULL,
            created_at TEXT NULL,
            updated_at TEXT NULL
        )');
        $this->conn->resetDataCache();

        $created = $this->service->addKavlingToYear([
            'id_proyek'    => 1,
            'tahun_target' => 2026,
            'id_kavlings'  => [101, 102, 104],
            'id_tipe'      => 10,
        ], 7);
        $idTarget = (int) $created['target']->id_target;

        // 101 punya pending ubah_tipe, 102 punya approved ubah_tipe, 104 hanya punya tambah_baru
        $this->conn->table('kavling_request')->insertBatch([
            [
                'id_proyek'     => 1,
                'jenis_request' => 'ubah_tipe',
                'id_kavling'    => 101,
                'id_tipe'       => 12,
                'status'        => 'pending',
                'created_by'    => 7,
                'created_at'    => '2026-11-16 09:00:00',
            ],
            [
                'id_proyek'     => 1,
                'jenis_request' => 'ubah_tipe',
                'id_kavling'    => 102,
                'id_tipe'       => 12,
                'status'        => 'approved',
                'created_by'    => 7,
                'created_at'    => '2026-11-15 08:00:00',
            ],
            [
                'id_proyek'     => 1,
                'jenis_request' => 'tambah_baru',
                'id_kavling'    => 104,
                'id_tipe'       => 12,
                'status'        => 'pending',
                'created_by'    => 7,
                'created_at'    => '2026-11-16 10:00:00',
            ],
        ]);

        $detail = $this->service->get($idTarget);
        $byKavling = [];
        foreach ($detail['kavlings'] as $row) {
            $byKavling[$row->id_kavling] = $row;
        }

        $this->assertSame(1, $byKavling[101]->pending_planning_request_id);
        $this->assertSame('pending', $byKavling[101]->planning_request_status);
        $this->assertSame('2026-11-16 09:00:00', $byKavling[101]->planning_request_created_at);

        $this->assertNull($byKavling[102]->pending_planning_request_id);
        $this->assertSame('approved', $byKavling[102]->planning_request_status);
        $this->assertSame('2026-11-15 08:00:00', $byKavling[102]->planning_request_created_at);

        $this->assertNull($byKavling[104]->pending_planning_request_id);
        $this->assertNull($byKavling[104]->planning_request_status);
        $this->assertNull($byKavling[104]->planning_request_created_at);
    }

    public function testKavlingRequestBatchAndPendingDeduplication(): void
    {
        $this->conn->query('DROP TABLE IF EXISTS db_proyek');
        $this->conn->query('CREATE TABLE db_proyek (
            id_proyek INTEGER PRIMARY KEY,
            nama_proyek TEXT
        )');
        $this->conn->table('proyek')->insert([
            'id_proyek'   => 1,
            'nama_proyek' => 'Proyek Uji',
        ]);

        // Pastikan tabel users memiliki kolom name seperti di MySQL produksi
        $this->conn->query('DROP TABLE IF EXISTS db_users');
        $this->conn->query('CREATE TABLE db_users (
            id INTEGER PRIMARY KEY,
            username TEXT,
            name TEXT NULL
        )');
        $this->conn->table('users')->insert([
            'id'       => 7,
            'username' => 'mkdt_user',
            'name'     => 'MKDT User',
        ]);

        $this->conn->query('DROP TABLE IF EXISTS db_kavling_request_kavling');
        $this->conn->query('DROP TABLE IF EXISTS db_kavling_request');
        $this->conn->query('CREATE TABLE db_kavling_request (
            id_request INTEGER PRIMARY KEY AUTOINCREMENT,
            id_proyek INTEGER,
            jenis_request TEXT,
            id_kavling INTEGER NULL,
            id_cluster INTEGER NULL,
            id_jalan INTEGER NULL,
            id_tipe INTEGER NULL,
            tipe_manual TEXT NULL,
            keterangan TEXT NULL,
            status TEXT DEFAULT "pending",
            alasan_tolak TEXT NULL,
            created_by INTEGER NULL,
            created_at TEXT NULL,
            updated_at TEXT NULL
        )');
        $this->conn->query('CREATE TABLE db_kavling_request_kavling (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            id_request INTEGER,
            id_kavling INTEGER,
            old_id_tipe INTEGER NULL,
            created_at TEXT NULL
        )');
        $this->conn->resetDataCache();

        $capturedNotifications = [];
        $notifStub = new class ($capturedNotifications) extends \App\Services\NotifikasiService {
            private array $captured;

            public function __construct(array &$captured)
            {
                $this->captured = &$captured;
            }

            public function create(\App\Data\NotificationData $data, \App\Data\NotificationAudience $audience): int
            {
                $this->captured[] = [
                    'data'     => $data,
                    'audience' => $audience,
                ];

                return count($this->captured);
            }
        };

        $reqModel = new \App\Models\KavlingRequestModel($this->conn);
        $reqService = new \App\Services\Transaksi\KavlingRequestService($this->conn, $reqModel, $notifStub);

        // Single submit berhasil untuk kavling 101
        $single = $reqService->submitRequest([
            'id_proyek'     => 1,
            'jenis_request' => 'ubah_tipe',
            'id_kavling'    => 101,
            'id_tipe'       => 12,
            'keterangan'    => 'Upgrade single',
        ], 7);
        $this->assertGreaterThan(0, (int) $single['id_request']);
        $this->assertCount(1, $capturedNotifications);

        // Single submit ulang untuk kavling 101 yang masih pending -> wajib ditolak
        try {
            $reqService->submitRequest([
                'id_proyek'     => 1,
                'jenis_request' => 'ubah_tipe',
                'id_kavling'    => 101,
                'id_tipe'       => 12,
            ], 7);
            $this->fail('Seharusnya menolak duplikasi pending single request');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('pending', strtolower($e->getMessage()));
        }

        // Batch submit untuk [101 (pending -> skip), 102 (baru -> insert), 104 (baru manual -> insert)]
        $batch = $reqService->submitRequest([
            'id_proyek'     => 1,
            'jenis_request' => 'ubah_tipe',
            'items'         => [
                ['id_kavling' => 101, 'id_tipe' => 12, 'keterangan' => 'Dup'],
                ['id_kavling' => 102, 'id_tipe' => 12, 'keterangan' => 'Ubah 102'],
                ['id_kavling' => 104, 'tipe_manual' => 'Custom 72/120', 'keterangan' => 'Ubah 104'],
            ],
        ], 7);

        $this->assertSame(2, $batch['submitted_count']);
        $this->assertSame(2, $batch['request_count']);
        $this->assertSame(2, $batch['kavling_count']);
        $this->assertCount(2, $batch['groups']);
        $this->assertSame(12, $batch['groups'][0]['id_tipe']);
        $this->assertSame([102], $batch['groups'][0]['id_kavlings']);
        $this->assertNull($batch['groups'][1]['id_tipe']);
        $this->assertSame('Custom 72/120', $batch['groups'][1]['tipe_manual']);
        $this->assertSame([104], $batch['groups'][1]['id_kavlings']);
        $this->assertSame(1, $batch['skipped_count']);
        $this->assertCount(1, $batch['skipped_pending']);
        $this->assertSame(101, $batch['skipped_pending'][0]['id_kavling']);

        $childRows = $this->conn->table('kavling_request_kavling')
            ->orderBy('id', 'asc')
            ->get()
            ->getResult();
        $this->assertCount(3, $childRows);
        $this->assertSame(101, (int) $childRows[0]->id_kavling);
        $this->assertSame(10, (int) $childRows[0]->old_id_tipe);
        $this->assertSame(102, (int) $childRows[1]->id_kavling);
        $this->assertSame((int) $batch['created_ids'][0], (int) $childRows[1]->id_request);
        $this->assertSame(104, (int) $childRows[2]->id_kavling);
        $this->assertSame((int) $batch['created_ids'][1], (int) $childRows[2]->id_request);

        $this->assertCount(2, $capturedNotifications);
        $this->assertSame(\App\Enums\NotificationEvent::REQUEST_KAVLING, $capturedNotifications[1]['data']->eventType);
        $this->assertSame('6', $capturedNotifications[1]['audience']->legacyGroupTarget());

        // Batch jika seluruh item sudah pending -> ditolak
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('pending');
        $reqService->submitRequest([
            'id_proyek'     => 1,
            'jenis_request' => 'ubah_tipe',
            'items'         => [
                ['id_kavling' => 101, 'id_tipe' => 12],
                ['id_kavling' => 102, 'id_tipe' => 12],
            ],
        ], 7);
    }

    public function testKavlingRequestBatchGroupedByTargetTipe(): void
    {
        $this->conn->query('DROP TABLE IF EXISTS db_proyek');
        $this->conn->query('CREATE TABLE db_proyek (
            id_proyek INTEGER PRIMARY KEY,
            nama_proyek TEXT
        )');
        $this->conn->table('proyek')->insert([
            'id_proyek'   => 1,
            'nama_proyek' => 'Proyek Uji',
        ]);

        $this->conn->query('DROP TABLE IF EXISTS db_users');
        $this->conn->query('CREATE TABLE db_users (
            id INTEGER PRIMARY KEY,
            username TEXT,
            name TEXT NULL
        )');
        $this->conn->table('users')->insert([
            'id'       => 7,
            'username' => 'mkdt_user',
            'name'     => 'MKDT User',
        ]);

        $this->conn->query('DROP TABLE IF EXISTS db_kavling_request_kavling');
        $this->conn->query('DROP TABLE IF EXISTS db_kavling_request');
        $this->conn->query('CREATE TABLE db_kavling_request (
            id_request INTEGER PRIMARY KEY AUTOINCREMENT,
            id_proyek INTEGER,
            jenis_request TEXT,
            id_kavling INTEGER NULL,
            id_cluster INTEGER NULL,
            id_jalan INTEGER NULL,
            id_tipe INTEGER NULL,
            tipe_manual TEXT NULL,
            keterangan TEXT NULL,
            status TEXT DEFAULT "pending",
            alasan_tolak TEXT NULL,
            created_by INTEGER NULL,
            created_at TEXT NULL,
            updated_at TEXT NULL
        )');
        $this->conn->query('CREATE TABLE db_kavling_request_kavling (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            id_request INTEGER,
            id_kavling INTEGER,
            old_id_tipe INTEGER NULL,
            created_at TEXT NULL
        )');
        $this->conn->resetDataCache();

        $this->conn->table('kavling')->insertBatch([
            [
                'id_kavling' => 105,
                'id_jalan'   => 1,
                'id_tipe'    => 12,
                'id_mkdt'    => null,
                'no_kavling' => 'A5',
            ],
            [
                'id_kavling' => 106,
                'id_jalan'   => 1,
                'id_tipe'    => 12,
                'id_mkdt'    => null,
                'no_kavling' => 'A6',
            ],
            [
                'id_kavling' => 107,
                'id_jalan'   => 1,
                'id_tipe'    => 10,
                'id_mkdt'    => null,
                'no_kavling' => 'A7',
            ],
            [
                'id_kavling' => 108,
                'id_jalan'   => 1,
                'id_tipe'    => null,
                'id_mkdt'    => null,
                'no_kavling' => 'A8',
            ],
        ]);

        $capturedNotifications = [];
        $notifStub = new class ($capturedNotifications) extends \App\Services\NotifikasiService {
            private array $captured;

            public function __construct(array &$captured)
            {
                $this->captured = &$captured;
            }

            public function create(\App\Data\NotificationData $data, \App\Data\NotificationAudience $audience): int
            {
                $this->captured[] = [
                    'data'     => $data,
                    'audience' => $audience,
                ];

                return count($this->captured);
            }
        };

        $reqModel = new \App\Models\KavlingRequestModel($this->conn);
        $reqService = new \App\Services\Transaksi\KavlingRequestService($this->conn, $reqModel, $notifStub);

        $result = $reqService->submitRequest([
            'id_proyek'     => 1,
            'jenis_request' => 'ubah_tipe',
            'items'         => [
                ['id_kavling' => 105, 'id_tipe' => 10, 'keterangan' => 'Penyesuaian blok belakang'],
                ['id_kavling' => 106, 'id_tipe' => 10, 'keterangan' => 'Penyesuaian blok belakang'],
                ['id_kavling' => 101, 'id_tipe' => 12, 'keterangan' => 'Upgrade hook'],
                ['id_kavling' => 102, 'id_tipe' => 12, 'keterangan' => ''],
                ['id_kavling' => 108, 'id_tipe' => 12, 'keterangan' => 'Kavling tanpa tipe awal'],
                ['id_kavling' => 104, 'tipe_manual' => 'Custom 31/72', 'keterangan' => 'Tipe khusus A'],
                ['id_kavling' => 107, 'tipe_manual' => ' custom 31/72 ', 'keterangan' => 'Tipe khusus B'],
            ],
        ], 7);

        $this->assertSame(3, $result['submitted_count']);
        $this->assertSame(3, $result['request_count']);
        $this->assertSame(7, $result['kavling_count']);
        $this->assertSame(0, $result['skipped_count']);
        $this->assertCount(3, $result['created_ids']);
        $this->assertCount(3, $result['groups']);

        $this->assertSame(10, $result['groups'][0]['id_tipe']);
        $this->assertSame(2, $result['groups'][0]['kavling_count']);
        $this->assertSame([105, 106], $result['groups'][0]['id_kavlings']);

        $this->assertSame(12, $result['groups'][1]['id_tipe']);
        $this->assertSame(3, $result['groups'][1]['kavling_count']);
        $this->assertSame([101, 102, 108], $result['groups'][1]['id_kavlings']);

        $this->assertNull($result['groups'][2]['id_tipe']);
        $this->assertSame('Custom 31/72', $result['groups'][2]['tipe_manual']);
        $this->assertSame(2, $result['groups'][2]['kavling_count']);
        $this->assertSame([104, 107], $result['groups'][2]['id_kavlings']);

        // Pastikan tepat 3 baris header di db_kavling_request (bukan 7) dan id_kavling = null pada grup > 1 kavling
        $requestRows = $this->conn->table('kavling_request')
            ->orderBy('id_request', 'asc')
            ->get()
            ->getResult();
        $this->assertCount(3, $requestRows);

        $this->assertNull($requestRows[0]->id_kavling);
        $this->assertSame(10, (int) $requestRows[0]->id_tipe);
        $this->assertNull($requestRows[0]->tipe_manual);
        $this->assertSame('Penyesuaian blok belakang', $requestRows[0]->keterangan);

        $this->assertNull($requestRows[1]->id_kavling);
        $this->assertSame(12, (int) $requestRows[1]->id_tipe);
        $this->assertNull($requestRows[1]->tipe_manual);
        $this->assertSame('Upgrade hook; Kavling tanpa tipe awal', $requestRows[1]->keterangan);

        $this->assertNull($requestRows[2]->id_kavling);
        $this->assertNull($requestRows[2]->id_tipe);
        $this->assertSame('Custom 31/72', $requestRows[2]->tipe_manual);
        $this->assertSame('Tipe khusus A; Tipe khusus B', $requestRows[2]->keterangan);

        // Pastikan seluruh 7 anak tersimpan di db_kavling_request_kavling beserta old_id_tipe
        $childRows = $this->conn->table('kavling_request_kavling')
            ->orderBy('id', 'asc')
            ->get()
            ->getResult();
        $this->assertCount(7, $childRows);

        $childByKavling = [];
        foreach ($childRows as $child) {
            $childByKavling[(int) $child->id_kavling] = $child;
        }
        $this->assertSame((int) $requestRows[0]->id_request, (int) $childByKavling[105]->id_request);
        $this->assertSame(12, (int) $childByKavling[105]->old_id_tipe);
        $this->assertSame((int) $requestRows[0]->id_request, (int) $childByKavling[106]->id_request);
        $this->assertSame(12, (int) $childByKavling[106]->old_id_tipe);
        $this->assertSame((int) $requestRows[1]->id_request, (int) $childByKavling[101]->id_request);
        $this->assertSame(10, (int) $childByKavling[101]->old_id_tipe);
        $this->assertSame((int) $requestRows[1]->id_request, (int) $childByKavling[102]->id_request);
        $this->assertSame(10, (int) $childByKavling[102]->old_id_tipe);
        $this->assertSame((int) $requestRows[1]->id_request, (int) $childByKavling[108]->id_request);
        $this->assertNull($childByKavling[108]->old_id_tipe);
        $this->assertSame((int) $requestRows[2]->id_request, (int) $childByKavling[104]->id_request);
        $this->assertSame(10, (int) $childByKavling[104]->old_id_tipe);
        $this->assertSame((int) $requestRows[2]->id_request, (int) $childByKavling[107]->id_request);
        $this->assertSame(10, (int) $childByKavling[107]->old_id_tipe);

        // Pastikan tepat 1 notifikasi ringkasan dikirim ke grup 6
        $this->assertCount(1, $capturedNotifications);
        $notif = $capturedNotifications[0];
        $this->assertSame(\App\Enums\NotificationEvent::REQUEST_KAVLING, $notif['data']->eventType);
        $this->assertSame('6', $notif['audience']->legacyGroupTarget());
        $this->assertNull($notif['data']->idKavling);
        $this->assertSame('siteplan/view', $notif['data']->actionUrl);
        $this->assertStringContainsString('3 request perubahan tipe untuk 7 kavling', $notif['data']->message);
        $this->assertStringContainsString('T36 (36/72): 2 kavling', $notif['data']->message);
        $this->assertStringContainsString('T54 (54/108): 3 kavling', $notif['data']->message);
        $this->assertStringContainsString('Custom 31/72: 2 kavling', $notif['data']->message);
    }

    public function testRemoveKavlingBatchRecordsSingleHistoryAndBatchSummary(): void
    {
        $created = $this->service->addKavlingToYear([
            'id_proyek'    => 1,
            'tahun_target' => 2026,
            'id_kavlings'  => [101, 102, 104],
            'id_tipe'      => 10,
        ], 7);

        $byKavling = [];
        foreach ($created['kavlings'] as $row) {
            $byKavling[(int) $row->id_kavling] = (int) $row->id_target_kavling;
        }

        $id1 = $byKavling[101];
        $id2 = $byKavling[102];

        $removed = $this->service->removeKavling([
            'id_target_kavlings' => [$id1, $id2],
        ], 7);

        $this->assertCount(1, $removed['kavlings']);
        $this->assertSame(104, (int) $removed['kavlings'][0]->id_kavling);

        $this->assertSame(2, $removed['batch_summary']['removed_count']);
        $this->assertSame([$id1, $id2], $removed['batch_summary']['id_target_kavlings']);
        $this->assertSame([101, 102], $removed['batch_summary']['removed_kavling_ids']);

        // Hanya ada 2 versi history (v1 add batch, v2 remove batch -> tepat 1 entri history baru)
        $this->assertCount(2, $removed['history']);
        $latest = $removed['history'][0];
        $this->assertSame(2, $latest->version);
        $this->assertSame('v2', $latest->version_label);
        $this->assertSame('target_remove_kavling', $latest->aksi);
        $this->assertSame(2, $latest->diff_summary['removed_count']);
        $this->assertSame(3, $latest->diff_summary['total_before']);
        $this->assertSame(1, $latest->diff_summary['total_after']);
        $this->assertCount(2, $latest->diff_summary['removed']);
    }

    private function resetSchema(): void
    {
        foreach ([
            'db_history_log',
            'db_kavling_request_kavling',
            'db_kavling_request',
            'db_target_siteplan_request',
            'db_target_siteplan_kavling',
            'db_target_siteplan',
            'db_kavling',
            'db_jalan',
            'db_cluster',
            'db_proyek',
            'db_tipe',
            'db_mkdt',
            'db_users',
        ] as $table) {
            $this->conn->query("DROP TABLE IF EXISTS {$table}");
        }

        $this->conn->query('CREATE TABLE db_users (
            id INTEGER PRIMARY KEY,
            username TEXT
        )');

        $this->conn->query('CREATE TABLE db_cluster (
            id_cluster INTEGER PRIMARY KEY,
            id_proyek INTEGER,
            nama_cluster TEXT
        )');

        $this->conn->query('CREATE TABLE db_jalan (
            id_jalan INTEGER PRIMARY KEY,
            id_cluster INTEGER,
            nama_jalan TEXT
        )');

        $this->conn->query('CREATE TABLE db_tipe (
            id_tipe INTEGER PRIMARY KEY,
            id_proyek INTEGER,
            no_tipe_rumah TEXT NULL,
            tipe_rumah TEXT NULL,
            is_subsidi INTEGER NULL,
            is_active INTEGER DEFAULT 1
        )');

        $this->conn->query('CREATE TABLE db_mkdt (
            id_mkdt INTEGER PRIMARY KEY,
            status_mkdt TEXT NULL,
            is_batal INTEGER DEFAULT 0
        )');

        $this->conn->query('CREATE TABLE db_kavling (
            id_kavling INTEGER PRIMARY KEY,
            id_jalan INTEGER,
            id_tipe INTEGER NULL,
            id_mkdt INTEGER NULL,
            no_kavling TEXT
        )');

        $this->conn->query('CREATE TABLE db_target_siteplan (
            id_target INTEGER PRIMARY KEY AUTOINCREMENT,
            id_proyek INTEGER,
            tahun_target INTEGER,
            deskripsi TEXT NULL,
            status INTEGER DEFAULT 1,
            add_by INTEGER NULL,
            edit_by INTEGER NULL,
            created_at TEXT NULL,
            updated_at TEXT NULL,
            deleted_at TEXT NULL
        )');

        $this->conn->query('CREATE TABLE db_target_siteplan_kavling (
            id_target_kavling INTEGER PRIMARY KEY AUTOINCREMENT,
            id_target INTEGER,
            id_kavling INTEGER,
            id_tipe INTEGER NULL,
            tipe_manual TEXT NULL,
            created_at TEXT NULL,
            updated_by INTEGER NULL,
            updated_at TEXT NULL
        )');

        $this->conn->query('CREATE TABLE db_target_siteplan_request (
            id_request INTEGER PRIMARY KEY AUTOINCREMENT,
            id_target INTEGER,
            id_kavling INTEGER,
            old_id_tipe INTEGER NULL,
            old_tipe_manual TEXT NULL,
            new_id_tipe INTEGER NULL,
            new_tipe_manual TEXT NULL,
            catatan_request TEXT NULL,
            status TEXT DEFAULT "pending",
            request_by INTEGER NULL,
            request_at TEXT NULL,
            approve_by INTEGER NULL,
            approve_at TEXT NULL,
            catatan_approval TEXT NULL
        )');

        $this->conn->query('CREATE TABLE db_kavling_request_kavling (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            id_request INTEGER,
            id_kavling INTEGER,
            old_id_tipe INTEGER NULL,
            created_at TEXT NULL
        )');

        $this->conn->query('CREATE TABLE db_history_log (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            module TEXT,
            reference_type TEXT NULL,
            reference_id INTEGER NULL,
            id_proyek INTEGER NULL,
            id_kavling INTEGER NULL,
            action TEXT,
            summary TEXT NULL,
            old_data TEXT NULL,
            new_data TEXT NULL,
            metadata TEXT NULL,
            add_by INTEGER NULL,
            created_at TEXT NULL
        )');

        $this->conn->resetDataCache();
    }

    private function seedFixtures(): void
    {
        $this->conn->table('users')->insertBatch([
            [
                'id'       => 6,
                'username' => 'planning_user',
            ],
            [
                'id'       => 7,
                'username' => 'mkdt_user',
            ],
        ]);

        $this->conn->table('cluster')->insert([
            'id_cluster'   => 1,
            'id_proyek'    => 1,
            'nama_cluster' => 'Cluster A',
        ]);

        $this->conn->table('jalan')->insert([
            'id_jalan'   => 1,
            'id_cluster' => 1,
            'nama_jalan' => 'Jl. Mawar',
        ]);

        $this->conn->table('tipe')->insertBatch([
            [
                'id_tipe'       => 10,
                'id_proyek'     => 1,
                'no_tipe_rumah' => 'T36',
                'tipe_rumah'    => '36/72',
                'is_subsidi'    => 1,
                'is_active'     => 1,
            ],
            [
                'id_tipe'       => 11,
                'id_proyek'     => 1,
                'no_tipe_rumah' => 'T45',
                'tipe_rumah'    => '45/90',
                'is_subsidi'    => 0,
                'is_active'     => 0,
            ],
            [
                'id_tipe'       => 12,
                'id_proyek'     => 1,
                'no_tipe_rumah' => 'T54',
                'tipe_rumah'    => '54/108',
                'is_subsidi'    => 0,
                'is_active'     => 1,
            ],
        ]);

        $this->conn->table('mkdt')->insertBatch([
            [
                'id_mkdt'     => 201,
                'status_mkdt' => 'NUP',
                'is_batal'    => 0,
            ],
            [
                'id_mkdt'     => 202,
                'status_mkdt' => 'Booking',
                'is_batal'    => 0,
            ],
            [
                'id_mkdt'     => 203,
                'status_mkdt' => 'Booking',
                'is_batal'    => 1,
            ],
        ]);

        $this->conn->table('kavling')->insertBatch([
            [
                'id_kavling' => 101,
                'id_jalan'   => 1,
                'id_tipe'    => 10,
                'id_mkdt'    => null,
                'no_kavling' => 'A1',
            ],
            [
                'id_kavling' => 102,
                'id_jalan'   => 1,
                'id_tipe'    => 10,
                'id_mkdt'    => 201, // NUP -> diizinkan
                'no_kavling' => 'A2',
            ],
            [
                'id_kavling' => 103,
                'id_jalan'   => 1,
                'id_tipe'    => 10,
                'id_mkdt'    => 202, // Booking aktif -> ditolak
                'no_kavling' => 'A3',
            ],
            [
                'id_kavling' => 104,
                'id_jalan'   => 1,
                'id_tipe'    => 10,
                'id_mkdt'    => 203, // Batal -> diizinkan
                'no_kavling' => 'A4',
            ],
        ]);
    }
}
