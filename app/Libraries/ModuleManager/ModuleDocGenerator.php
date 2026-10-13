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

        // Ekstrak bagian manual jika file sudah ada
        $purpose    = $this->extractSection($existingContent, 'Purpose', $moduleData['description'] ?? 'Tanggung jawab bisnis utama modul.');
        $outOfScope = $this->extractSection($existingContent, 'Out of scope', "- Hal-hal di luar tanggung jawab domain {$name}.");
        $invariants = $this->extractSection($existingContent, 'Invariants', "- Modul lain tidak boleh memanipulasi tabel pemilik secara langsung.\n- Perubahan finansial/status harus diverifikasi di level Service.");
        $notes      = $this->extractSection($existingContent, 'Notes', "- Modul diinisialisasi melalui tooling otomatis.");

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
                    foreach (array_slice($methods, 0, 15) as $m) {
                        $md .= "- `{$m}`\n";
                    }
                    if (count($methods) > 15) {
                        $md .= "- _dan " . (count($methods) - 15) . " method lainnya..._\n";
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
            $md .= "- `{$c['relative_path']}` ({$c['line_count']} baris, " . count($c['public_methods']) . " public methods)\n";
        }

        $md .= "\n### Services (" . count($moduleData['services']) . ")\n";
        foreach ($moduleData['services'] as $s) {
            $md .= "- `{$s['relative_path']}` ({$s['line_count']} baris)\n";
        }

        $md .= "\n### Repositories (" . count($moduleData['repositories']) . ")\n";
        foreach ($moduleData['repositories'] as $r) {
            $md .= "- `{$r['relative_path']}` ({$r['line_count']} baris)\n";
        }

        $md .= "\n### Models (" . count($moduleData['models']) . ")\n";
        foreach ($moduleData['models'] as $m) {
            $md .= "- `{$m['relative_path']}`\n";
        }

        $md .= "\n### Views (" . count($moduleData['views']) . " folder)\n";
        foreach ($moduleData['views'] as $v) {
            $md .= "- `{$v['folder']}` ({$v['file_count']} file)\n";
        }

        $md .= "\n### JavaScript (" . count($moduleData['assets_js']) . " file)\n";
        foreach ($moduleData['assets_js'] as $j) {
            $md .= "- `{$j['path']}` ({$j['line_count']} baris)\n";
        }
        $md .= "\n";

        // Readiness Checklist
        $check = $moduleData['readiness_checklist'];
        $md .= "## Readiness Checklist\n";
        $md .= "- [" . ($check['has_service'] ? 'x' : ' ') . "] Has Service Layer\n";
        $md .= "- [" . ($check['has_repository'] ? 'x' : ' ') . "] Has Repository Layer\n";
        $md .= "- [" . ($check['has_web_api_split'] ? 'x' : ' ') . "] Web & API Controllers Separated\n";
        $md .= "- [" . ($check['has_explicit_routes'] ? 'x' : ' ') . "] Explicit Routes Defined\n";
        $md .= "- [" . ($check['has_documentation'] ? 'x' : ' ') . "] MODULE.md Initialized\n\n";

        // Dead Code Candidates Section
        $md .= "## Dead Code Candidates\n";
        $md .= "_Jalankan `php spark module:deadcode {$name}` untuk mengaudit dead code kandidat._\n\n";

        // Notes
        $md .= "## Notes\n" . trim($notes) . "\n";

        file_put_contents($docFile, $md);
        return $docFile;
    }

    protected function extractSection(string $content, string $heading, string $default): string
    {
        if (empty($content)) return $default;

        $pattern = '/##\s+' . preg_quote($heading, '/') . '\s*\n(.*?)(?=\n##|\z)/s';
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
