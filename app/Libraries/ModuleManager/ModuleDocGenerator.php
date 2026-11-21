<?php

namespace App\Libraries\ModuleManager;

/**
 * ModuleDocGenerator
 * 
 * Menghasilkan dan memperbarui file MODULE.md di docs/modules/<NamaModul>/MODULE.md.
 * Menjamin bagian manual (Purpose, Invariants, Notes) TIDAK tertimpa,
 * sementara bagian teknis (File inventory, Routes, Contracts, Checklist) selalu disinkronkan otomatis.
 */
class ModuleDocGenerator
{
    protected string $docsBaseDir;

    public function __construct()
    {
        $this->docsBaseDir = dirname(APPPATH) . DIRECTORY_SEPARATOR . 'docs' . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR;
    }

    public function syncModuleDoc(array $moduleData): string
    {
        $name    = $moduleData['name'];
        $modDir  = $this->docsBaseDir . $name;
        $docFile = $modDir . DIRECTORY_SEPARATOR . 'MODULE.md';

        if (!is_dir($modDir)) {
            mkdir($modDir, 0755, true);
        }

        $existingContent = file_exists($docFile) ? file_get_contents($docFile) : '';

        // Ekstrak bagian manual/kurasi jika file sudah ada
        $purpose      = $this->extractSection($existingContent, 'Purpose', $moduleData['description'] ?? 'Tanggung jawab bisnis utama modul.');
        $outOfScope   = $this->extractSection($existingContent, 'Out of scope', "- Hal-hal di luar tanggung jawab domain {$name}.");
        $invariants   = $this->extractSection($existingContent, 'Invariants', "- Modul lain tidak boleh memanipulasi tabel pemilik secara langsung.\n- Perubahan finansial/status harus diverifikasi di level Service.");
        $keyFunctions = $this->extractSection($existingContent, 'Key Functions', $this->buildDefaultKeyFunctions($moduleData));
        $deprecated   = $this->extractSection($existingContent, 'Deprecated & Legacy Shims', $this->buildDefaultDeprecated($moduleData));
        $notes        = $this->extractSection($existingContent, 'Notes', "- Modul diinisialisasi melalui tooling otomatis.");
        $changeLog    = $this->extractSection($existingContent, 'Change Log', "- `" . date('Y-m-d') . "` [`SYNC`] — Sinkronisasi status & inventori modul melalui `module:sync`.");

        // Ambil git commit hash saat ini
        $gitCommit = $this->getCurrentGitCommit();
        $dateNow   = date('Y-m-d H:i:s');

        // Render file markdown
        $md = "# Module: {$name}\n\n";
        $md .= "**Status:** `{$moduleData['status']}`  \n";
        $md .= "**Readiness Score:** `{$moduleData['readiness_score']}/100`  \n";
        $md .= "**Last Synced:** `{$dateNow}` @ `{$gitCommit}`  \n\n";

        $md .= "## Purpose\n" . trim($purpose) . "\n\n";
        $md .= "## Out of scope\n" . trim($outOfScope) . "\n\n";
        $md .= "## Invariants\n" . trim($invariants) . "\n\n";

        // Database Tables
        $md .= "## Database Tables\n";
        $md .= "### Owner Table(s)\n";
        if (!empty($moduleData['owner_tables'])) {
            foreach ($moduleData['owner_tables'] as $t) {
                $md .= "- `{$t}` *(akses tulis eksklusif hanya untuk modul {$name})*\n";
            }
        } else {
            $md .= "- *(Tidak memiliki tabel database independen)*\n";
        }

        $md .= "\n### Shared Tables (Dibaca dari modul lain)\n";
        if (!empty($moduleData['shared_tables'])) {
            foreach ($moduleData['shared_tables'] as $st) {
                $md .= "- `{$st}`\n";
            }
        } else {
            $md .= "- *(Tidak membaca tabel modul lain)*\n";
        }
        $md .= "\n";

        // Contracts
        $md .= "## Contracts\n";
        $md .= "### A. Public HTTP Endpoints & Routes Inventory\n";
        if (!empty($moduleData['routes'])) {
            $md .= "| Method | URI / Pattern | Tipe | Handler | Status Migrasi | Target API / Catatan |\n";
            $md .= "|---|---|---|---|---|---|\n";
            foreach ($moduleData['routes'] as $r) {
                $md .= "| `{$r['http_method']}` | `{$r['uri']}` | `{$r['type']}` | `{$r['handler']}` | {$r['status']} | {$r['notes']} |\n";
            }
        } else {
            $md .= "_Belum ada rute terdeteksi._\n";
        }
        $md .= "\n";

        $md .= "### B. Public PHP Services (Backend Contract Antar-Modul)\n";
        if (!empty($moduleData['service_contracts'])) {
            foreach ($moduleData['service_contracts'] as $sName => $methods) {
                $md .= "#### `{$sName}`\n";
                if (!empty($methods)) {
                    foreach ($methods as $m) {
                        $md .= "- `{$m}`\n";
                    }
                } else {
                    $md .= "- _Tidak ada public method terdeteksi._\n";
                }
            }
        } else {
            $md .= "_Belum memiliki Service class sebagai contract backend._\n";
        }
        $md .= "\n";

        // File Inventory
        $md .= "## File Inventory\n";
        $md .= "### Controllers (" . count($moduleData['controllers']) . ")\n";
        foreach ($moduleData['controllers'] as $c) {
            $methodsList = !empty($c['public_methods']) ? ': `' . implode('`, `', $c['public_methods']) . '`' : '';
            $md .= "- `{$c['relative_path']}` ({$c['line_count']} baris, " . count($c['public_methods']) . " public methods{$methodsList})\n";
        }

        $md .= "\n### Services (" . count($moduleData['services']) . ")\n";
        foreach ($moduleData['services'] as $s) {
            $md .= "- `{$s['relative_path']}` ({$s['line_count']} baris, " . count($s['public_methods'] ?? []) . " public methods)\n";
        }

        $md .= "\n### Repositories (" . count($moduleData['repositories']) . ")\n";
        foreach ($moduleData['repositories'] as $r) {
            $repoMethods = !empty($r['public_methods']) ? ': `' . implode('`, `', $r['public_methods']) . '`' : '';
            $md .= "- `{$r['relative_path']}` ({$r['line_count']} baris, " . count($r['public_methods'] ?? []) . " public methods{$repoMethods})\n";
        }

        $md .= "\n### Models (" . count($moduleData['models']) . ")\n";
        foreach ($moduleData['models'] as $m) {
            $md .= "- `{$m['relative_path']}`\n";
        }

        $md .= "\n### Views (" . count($moduleData['views']) . " folder)\n";
        foreach ($moduleData['views'] as $v) {
            $viewFiles = !empty($v['files']) ? ': `' . implode('`, `', $v['files']) . '`' : '';
            $md .= "- `{$v['folder']}` ({$v['file_count']} file{$viewFiles})\n";
        }

        $md .= "\n### JavaScript (" . count($moduleData['assets_js']) . " file)\n";
        foreach ($moduleData['assets_js'] as $j) {
            $md .= "- `{$j['path']}` ({$j['line_count']} baris)\n";
        }

        if (!empty($moduleData['shims'])) {
            $md .= "\n### Zero-Downtime Shims (" . count($moduleData['shims']) . " file)\n";
            foreach ($moduleData['shims'] as $shimPath) {
                $md .= "- `{$shimPath}`\n";
            }
        }
        $md .= "\n";

        // Key Functions Section
        $md .= "## Key Functions\n" . trim($keyFunctions) . "\n\n";

        // Deprecated & Legacy Shims Section
        $md .= "## Deprecated & Legacy Shims\n" . trim($deprecated) . "\n\n";

        // Readiness Checklist
        $check = $moduleData['readiness_checklist'];
        $zeroViolations = $check['zero_controller_violations'] ?? empty($moduleData['controller_violations']);
        $md .= "## Readiness Checklist\n";
        $md .= "- [" . ($check['has_service'] ? 'x' : ' ') . "] Has Service Layer\n";
        $md .= "- [" . ($check['has_repository'] ? 'x' : ' ') . "] Has Repository Layer\n";
        $md .= "- [" . ($check['has_web_api_split'] ? 'x' : ' ') . "] Web & API Controllers Separated\n";
        $md .= "- [" . ($check['has_explicit_routes'] ? 'x' : ' ') . "] Explicit Routes Defined\n";
        $md .= "- [" . ($zeroViolations ? 'x' : ' ') . "] Zero Controller Layer Violations\n";
        $md .= "- [" . ($check['has_documentation'] ? 'x' : ' ') . "] MODULE.md Initialized\n";

        if (!empty($moduleData['controller_violations'])) {
            $md .= "\n> **Layer Violations Detected:**\n";
            foreach ($moduleData['controller_violations'] as $ctrlPath => $violations) {
                $md .= "> - `{$ctrlPath}`: " . implode(', ', $violations) . "\n";
            }
        }
        $md .= "\n";

        // Dead Code Candidates Section
        $md .= "## Dead Code Candidates\n";
        $md .= "_Jalankan `php spark module:deadcode {$name}` untuk mengaudit dead code kandidat._\n\n";

        // Notes
        $md .= "## Notes\n" . trim($notes) . "\n\n";

        // Change Log
        $md .= "## Change Log\n" . trim($changeLog) . "\n";

        file_put_contents($docFile, $md);
        return $docFile;
    }

    protected function buildDefaultKeyFunctions(array $moduleData): string
    {
        $lines = [];
        if (!empty($moduleData['services'])) {
            foreach ($moduleData['services'] as $s) {
                $topMethods = array_slice($s['public_methods'] ?? [], 0, 5);
                if (!empty($topMethods)) {
                    $lines[] = "- **Service (`{$s['relative_path']}`):** `" . implode('`, `', $topMethods) . "`";
                }
            }
        }
        if (!empty($moduleData['repositories'])) {
            foreach ($moduleData['repositories'] as $r) {
                $topMethods = array_slice($r['public_methods'] ?? [], 0, 5);
                if (!empty($topMethods)) {
                    $lines[] = "- **Repository (`{$r['relative_path']}`):** `" . implode('`, `', $topMethods) . "`";
                }
            }
        }

        return !empty($lines)
            ? implode("\n", $lines)
            : "- *(Belum ada dokumentasi kurasi fungsi penting. Wajib dilengkapi oleh `sigapp-docs` saat ada perubahan modul).*";
    }

    protected function buildDefaultDeprecated(array $moduleData): string
    {
        $lines = [];
        if (!empty($moduleData['shims'])) {
            foreach ($moduleData['shims'] as $shimPath) {
                $lines[] = "- `{$shimPath}` — **DEPRECATED (Zero-Downtime Shim)**: Dipertahankan untuk kompatibilitas mundur; gunakan class modular di namespace `{$moduleData['name']}`.";
            }
        }
        foreach ($moduleData['routes'] ?? [] as $r) {
            if (str_contains($r['status'] ?? '', 'LEGACY')) {
                $lines[] = "- Route `{$r['http_method']} {$r['uri']}` (`{$r['handler']}`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.";
            }
        }

        return !empty($lines)
            ? implode("\n", $lines)
            : "- *(Tidak ada fungsi, route, atau class shim yang berstatus deprecated saat ini).*";
    }

    protected function extractSection(string $content, string $heading, string $default): string
    {
        if (empty($content)) return $default;

        $pattern = '/##\s+' . preg_quote($heading, '/') . '\s*\n(.*?)(?=\n##\s+|\z)/s';
        if (preg_match($pattern, $content, $m)) {
            $trimmed = trim($m[1]);
            return !empty($trimmed) ? $trimmed : $default;
        }

        return $default;
    }

    protected function getCurrentGitCommit(): string
    {
        $headFile = dirname(APPPATH) . DIRECTORY_SEPARATOR . '.git' . DIRECTORY_SEPARATOR . 'HEAD';
        if (!file_exists($headFile)) return 'no-git';

        $head = trim(file_get_contents($headFile));
        if (str_starts_with($head, 'ref: ')) {
            $refPath = dirname(APPPATH) . DIRECTORY_SEPARATOR . '.git' . DIRECTORY_SEPARATOR . substr($head, 5);
            if (file_exists($refPath)) {
                return substr(trim(file_get_contents($refPath)), 0, 7);
            }
        }

        return substr($head, 0, 7);
    }
}
