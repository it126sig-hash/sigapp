<?php

use App\Repositories\KavlingRepository;
use App\Repositories\OthersRepository;
use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Database;

final class KavlingCompositeQueryTest extends CIUnitTestCase
{
    public function testCompositeQueryUsesAggregatedJoinsAndExplicitMkdtAliases(): void
    {
        $repository = new KavlingRepository(Database::connect());
        $reflection = new ReflectionClass($repository);

        $baseQuery = $reflection->getMethod('baseQuery');
        $builder = $baseQuery->invoke($repository);
        $this->assertInstanceOf(BaseBuilder::class, $builder);

        foreach (['addDivisiSelect', 'addCompositeVisualSelect', 'addCompositeFinanceSelect', 'addPencairanAkadSelect'] as $methodName) {
            $method = $reflection->getMethod($methodName);
            $methodName === 'addDivisiSelect'
                ? $method->invoke($repository, $builder, 0)
                : $method->invoke($repository, $builder);
        }

        $sql = $builder->getCompiledSelect(false);

        $this->assertStringContainsString('hargajual`.`is_subsidi', $sql);
        $this->assertStringContainsString('mkdt`.`id_konsumen` AS `visual_id_konsumen', $sql);
        $this->assertStringContainsString('kavling`.`perintah_bangun` AS `is_turun_pembangunan', $sql);
        $this->assertStringNotContainsString('mkdt_perintah_bangun', $sql);
        $this->assertStringContainsString('COUNT(*) AS tagihan_aktif_count', $sql);
        $this->assertStringContainsString('MIN(CASE', $sql);
        $this->assertStringContainsString('WHEN sudah_dibayar = 0 AND jatuh_tempo_tgl IS NOT NULL', $sql);
        $this->assertStringContainsString('WHERE is_void = 0', $sql);
        $this->assertStringContainsString('GROUP BY id_mkdt', $sql);
        $this->assertStringContainsString('GROUP BY id_plan', $sql);
        $this->assertStringContainsString("status <> 'void'", $sql);
        $this->assertStringContainsString("status IN ('active', 'partial')", $sql);
        $this->assertStringContainsString('pa_pengajuan_outstanding_count', $sql);
        $this->assertStringNotContainsString('SELECT jatuh_tempo_tgl FROM keuangan WHERE', $sql);
    }

    public function testActiveTiketSelectAddsDerivedTableAndColumns(): void
    {
        $repository = new KavlingRepository(Database::connect());
        $reflection = new ReflectionClass($repository);

        $baseQuery = $reflection->getMethod('baseQuery');
        $builder = $baseQuery->invoke($repository);

        $activeTiketMethod = $reflection->getMethod('addActiveTiketSelect');
        $activeTiketMethod->invoke($repository, $builder);

        $sql = $builder->getCompiledSelect(false);

        $this->assertStringContainsString('active_tiket_id', $sql);
        $this->assertStringContainsString('active_tiket_prioritas', $sql);
        $this->assertStringContainsString('active_tiket_status', $sql);
        $this->assertStringContainsString("tm.ref_type = 'kavling'", $sql);
        $this->assertStringContainsString("tm.status NOT IN ('selesai', 'batal', 'hold')", $sql);
        $this->assertStringContainsString('act_tm.ref_id = kavling.id_kavling', $sql);
    }

    public function testOthersActiveTiketSelectAddsDerivedTableAndColumns(): void
    {
        $repository = new OthersRepository(Database::connect());
        $reflection = new ReflectionClass($repository);

        $this->assertTrue($reflection->hasMethod('addActiveTiketSelect'));
        $activeTiketMethod = $reflection->getMethod('addActiveTiketSelect');
        $this->assertTrue($activeTiketMethod->isPrivate());

        // Verify getAll invokes addActiveTiketSelect
        $sourceFile = ($reflection->getParentClass() && $reflection->getParentClass()->getFileName())
            ? $reflection->getParentClass()->getFileName()
            : $reflection->getFileName();
        $repoSource = (string) file_get_contents($sourceFile);
        $this->assertStringContainsString('$this->addActiveTiketSelect($builder);', $repoSource);

        $db = Database::connect();
        $builder = $db->table('others')
            ->select('others.*, a.username as planning_add, b.username as produksi_add,
                c.username as legal_add, d.username as planning_edit, e.username as produksi_edit,
                f.username as legal_edit, jalan.nama_jalan, cluster.id_cluster, cluster.nama_cluster')
            ->join('jalan', 'jalan.id_jalan = others.id_jalan', 'left')
            ->join('cluster', 'cluster.id_cluster = jalan.id_cluster', 'left')
            ->join('proyek', 'proyek.id_proyek = cluster.id_proyek', 'left')
            ->join('users as a', 'a.id = others.planning_add_by', 'left')
            ->join('users as b', 'b.id = others.produksi_add_by', 'left')
            ->join('users as c', 'c.id = others.legal_add_by', 'left')
            ->join('users as d', 'd.id = others.planning_edit_by', 'left')
            ->join('users as e', 'e.id = others.produksi_edit_by', 'left')
            ->join('users as f', 'f.id = others.legal_edit_by', 'left');

        $activeTiketMethod->invoke($repository, $builder);

        $sql = $builder->getCompiledSelect(false);

        $this->assertStringContainsString('active_tiket_id', $sql);
        $this->assertStringContainsString('active_tiket_prioritas', $sql);
        $this->assertStringContainsString('active_tiket_status', $sql);
        $this->assertStringContainsString("tm.ref_type = 'others'", $sql);
        $this->assertStringContainsString("tm.status NOT IN ('selesai', 'batal', 'hold')", $sql);
        $this->assertStringContainsString('act_tm.ref_id = others.id', $sql);
        $this->assertStringContainsString('nama_jalan', $sql);
        $this->assertStringContainsString('nama_cluster', $sql);
    }

    public function testOthersScopeFilteringIncludesActiveTiket(): void
    {
        $db = Database::connect();
        $db->query('CREATE TABLE IF NOT EXISTS db_others (id INTEGER PRIMARY KEY, id_jalan INTEGER, scope TEXT, planning_add_by INTEGER, produksi_add_by INTEGER, legal_add_by INTEGER, planning_edit_by INTEGER, produksi_edit_by INTEGER, legal_edit_by INTEGER)');
        $this->assertTrue($db->fieldExists('scope', 'others'));

        $repository = new OthersRepository($db);
        $reflection = new ReflectionClass($repository);

        $sourceFile = ($reflection->getParentClass() && $reflection->getParentClass()->getFileName())
            ? $reflection->getParentClass()->getFileName()
            : $reflection->getFileName();
        $repoSource = (string) file_get_contents($sourceFile);
        $this->assertStringContainsString("orWhere('act_tm.active_tiket_id IS NOT NULL', null, false)", $repoSource);

        $builder = $db->table('others')
            ->select('others.*')
            ->join('jalan', 'jalan.id_jalan = others.id_jalan', 'left');

        $activeTiketMethod = $reflection->getMethod('addActiveTiketSelect');
        $activeTiketMethod->invoke($repository, $builder);

        $allowedScopes = ['siteplan', 'produksi'];
        $builder->groupStart()
            ->whereIn('others.scope', $allowedScopes)
            ->orWhere('others.scope IS NULL', null, false)
            ->orWhere('act_tm.active_tiket_id IS NOT NULL', null, false)
            ->groupEnd();

        $sql = $builder->getCompiledSelect(false);
        $this->assertStringContainsString('act_tm.active_tiket_id IS NOT NULL', $sql);
        $this->assertStringContainsString('active_tiket_id', $sql);
        $this->assertStringContainsString('others.scope', $sql);
    }

    /**
     * @dataProvider divisiModeProvider
     */
    public function testAllDivisiModesIncludeMkdtStatusFieldsWithoutDuplicateAliases(int $idDivisi): void
    {
        $repository = new KavlingRepository(Database::connect());
        $reflection = new ReflectionClass($repository);

        $builder = $reflection->getMethod('baseQuery')->invoke($repository);
        $reflection->getMethod('addDivisiSelect')->invoke($repository, $builder, $idDivisi);

        if ($idDivisi === 0) {
            foreach (['addCompositeVisualSelect', 'addCompositeFinanceSelect', 'addPencairanAkadSelect'] as $methodName) {
                $reflection->getMethod($methodName)->invoke($repository, $builder);
            }
        } elseif ($idDivisi === 3) {
            $reflection->getMethod('addPencairanAkadSelect')->invoke($repository, $builder);
        }

        $reflection->getMethod('addActiveTiketSelect')->invoke($repository, $builder);

        $sql = $builder->getCompiledSelect(false);

        $requiredMkdtSnippets = [
            'mkdt`.`status_mkdt`',
            'mkdt`.`is_batal`',
            'mkdt`.`booking_tgl`',
            'mkdt`.`wawancara`',
            'mkdt`.`wawancara_tgl`',
            'mkdt`.`sp3k`',
            'mkdt`.`sp3k_tgl`',
            'mkdt`.`akad_indent`',
            'mkdt`.`akad_indent_tgl`',
            'mkdt`.`akad`',
            'mkdt`.`akad_tgl`',
            'mkdt`.`is_kpr`',
            'mkdt`.`is_subsidi` as `mkdt_is_subsidi`',
            'hargajual`.`is_subsidi`',
        ];

        foreach ($requiredMkdtSnippets as $snippet) {
            $this->assertStringContainsString($snippet, $sql, "Divisi {$idDivisi} missing {$snippet}");
        }

        // Verify top-level SELECT clause has no duplicate column/alias names
        $fromPos = stripos($sql, "\nFROM ");
        if ($fromPos === false) {
            $fromPos = stripos($sql, ' FROM ');
        }
        $this->assertNotFalse($fromPos);

        $selectClause = substr($sql, strlen('SELECT '), $fromPos - strlen('SELECT '));
        $expressions = $this->splitTopLevelSelectExpressions($selectClause);
        $aliases = [];
        foreach ($expressions as $expr) {
            $expr = trim($expr);
            if ($expr === '' || str_ends_with($expr, '.*')) {
                continue;
            }
            if (preg_match('/\bAS\s+`?([a-zA-Z0-9_]+)`?\s*$/i', $expr, $m)) {
                $alias = $m[1];
            } elseif (preg_match('/\.`?([a-zA-Z0-9_]+)`?\s*$/', $expr, $m)) {
                $alias = $m[1];
            } else {
                $alias = trim($expr, "` \t\n\r");
            }
            $this->assertArrayNotHasKey($alias, $aliases, "Duplicate column/alias '{$alias}' in divisi {$idDivisi}");
            $aliases[$alias] = true;
        }
    }

    public static function divisiModeProvider(): array
    {
        return [
            'divisi 0 (composite)' => [0],
            'divisi 3 (keuangan)' => [3],
            'divisi 4 (mkdt)' => [4],
            'divisi 5 (legal)' => [5],
            'divisi 6 (planning)' => [6],
            'divisi 7 (produksi)' => [7],
            'divisi 8 (p2k)' => [8],
            'divisi 10 (pajak)' => [10],
        ];
    }

    private function splitTopLevelSelectExpressions(string $selectClause): array
    {
        $parts = [];
        $current = '';
        $depth = 0;
        $inSingleQuote = false;
        $length = strlen($selectClause);

        for ($i = 0; $i < $length; $i++) {
            $ch = $selectClause[$i];
            if ($ch === "'" && ($i === 0 || $selectClause[$i - 1] !== '\\')) {
                $inSingleQuote = !$inSingleQuote;
            } elseif (!$inSingleQuote) {
                if ($ch === '(') {
                    $depth++;
                } elseif ($ch === ')') {
                    $depth = max(0, $depth - 1);
                } elseif ($ch === ',' && $depth === 0) {
                    $parts[] = $current;
                    $current = '';
                    continue;
                }
            }
            $current .= $ch;
        }

        if (trim($current) !== '') {
            $parts[] = $current;
        }

        return $parts;
    }
}
