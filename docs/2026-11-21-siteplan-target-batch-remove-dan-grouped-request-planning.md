# Pelacakan Progres: Hapus Kavling Target Batch & Grouping Request Perubahan Tipe ke Planning

## Metadata Tugas
- **Tanggal:** 2026-11-21
- **Modul Target:** `Siteplan` & `Transaksi`
- **Mode Tugas:** `FEATURE`
- **Status Keseluruhan:** `COMPLETED` (`DONE` — Lulus QA Independen & Sinkronisasi Docs)
- **Keputusan User (Approved):** Gunakan **Opsi A** untuk cakupan checkbox pada Hapus Batch (pertahankan guard `canEditTargetRow()` agar hanya kavling tanpa konsumen aktif yang dapat dicentang untuk aksi batch; kavling berkonsumen aktif tetap bisa dihapus satuan lewat tombol hapus per baris).
- **Artefak Plan:** `implementation_plan.md`

---

## Ringkasan Fase Eksekusi

| Fase | Agen Penanggung Jawab | Ruang Lingkup | Status | Catatan Ringkas |
|---|---|---|---|---|
| **Fase 1** | `sigapp-explorer` & Master Orchestrator | Eksplorasi codebase, codegraph blast radius, & penyusunan `implementation_plan.md` | `COMPLETED` | Disetujui User (Opsi A) |
| **Fase A (2A)** | `sigapp-engineer` | Implementasi Backend (`TargetSiteplanRepository`, `TargetSiteplanService`, `TargetController`, `KavlingRequestService`, `TargetSiteplanServiceTest`) | `COMPLETED` | 5 file diubah, `php -l` bersih, `phpunit --no-coverage` PASS (15 tests, 267 assertions) |
| **Fase B (2B)** | `sigapp-frontend` | Implementasi Frontend (`target.php`, `target.js`, `siteplan-target.css`, `request-kavling.js`, `siteplan-request-kavling.css`) | `COMPLETED` | 5 file diubah, `php -l` & `node --check` & `git diff --check` bersih |
| **Fase C (3)** | `sigapp-qa` | Audit callers (`codegraph`), `php spark routes`, PHPUnit, & Playwright E2E (`MKDT` + `Planning`) | `COMPLETED (PASS)` | Sertifikat QA PASS: 0 broken callers, 15 unit test lulus, E2E MKDT & Planning lulus |
| **Fase D (4)** | `sigapp-docs` | Sinkronisasi `php spark module:sync Siteplan & Transaksi`, update `MODULE.md`, & finalisasi tracking doc | `DONE` | `ModuleCatalog.php` terverifikasi lengkap; `module:sync` + update `Siteplan/MODULE.md` & `Transaksi/MODULE.md` + finalisasi work doc selesai |

---

## Log Progres & Verifikasi

### Fase 1: Eksplorasi & Perencanaan
- `sigapp-explorer` memetakan alur hapus kavling target (`POST /api/target/remove-kavling`) dan batch request perubahan tipe ke Planning (`POST /api/kavling-request/submit`).
- Terkonfirmasi skema DB `kavling_request_kavling`, repository, service (`applyUbahTipeKavling()`), dan UI Planning (`request-kavling.js`) sudah mendukung relasi `1 request : N kavling`, sehingga hanya `KavlingRequestService::submitBatchItemsUbahTipe()` yang perlu diubah untuk mengelompokkan item berdasarkan tipe tujuan.
- User menyetujui `implementation_plan.md` dengan pilihan **Opsi A**.

### Fase A: Implementasi Backend (`sigapp-engineer`)
- **Status:** `COMPLETED`
- **File Diubah (5 file):**
  1. `app/Repositories/Siteplan/TargetSiteplanRepository.php` — Menambahkan `deleteTargetKavlings(array $idTargetKavlings): bool` (L408-422); mempertahankan `deleteTargetKavling()` (L397-406).
  2. `app/Services/Siteplan/TargetSiteplanService.php` — Memperluas `removeKavling(array $data, int $actorId): array` (L451-558) untuk single & batch atomik (1 transaksi DB, 1 entri Versioning History `target_remove_kavling` per target, `batch_summary`, tanpa `assertNoActiveConsumers()`).
  3. `app/Controllers/Api/Siteplan/TargetController.php` — Memperluas `removeKavling(): ResponseInterface` (L291-350) menerima `id_target_kavlings` / `id_target_kavling` / `id_target` + `id_kavlings`/`id_kavling`, validasi single project, dan pesan JSON dinamis.
  4. `app/Services/Transaksi/KavlingRequestService.php` — Mengubah `submitBatchItemsUbahTipe()` (L436-664) menjadi grouped by target tipe (`master:{id_tipe}` / `manual:{lowercase}`), menyimpan 1 header `kavling_request` (`id_kavling = null` jika > 1 unit) + N anak `kavling_request_kavling`, 1 notifikasi ringkasan grup `[6]`, dan payload JSON `request_count`, `kavling_count`, `groups`.
  5. `tests/unit/TargetSiteplanServiceTest.php` — Menambahkan tabel `db_kavling_request_kavling` pada schema test serta menambah test `testKavlingRequestBatchGroupedByTargetTipe()` dan `testRemoveKavlingBatchRecordsSingleHistoryAndBatchSummary()`.
- **Verifikasi:**
  - `php -l` pada 5 file PHP: `No syntax errors detected` (exit 0).
  - `vendor/bin/phpunit --no-coverage tests/unit/TargetSiteplanServiceTest.php`: `OK (15 tests, 267 assertions)` (exit 0; tanpa `--no-coverage` hanya muncul warning default `No code coverage driver available` di `phpunit.xml.dist`).

### Fase B: Implementasi Frontend & UI (`sigapp-frontend`)
- **Status:** `COMPLETED`
- **File Diubah (5 file):**
  1. `app/Views/siteplan/target.php` — Menambahkan tombol `#target-btn-batch-remove` (`Hapus dari Target`, `hidden`) pada `#target-batch-action-bar .target-batch-action-buttons`.
  2. `public/assets/js/siteplan/target.js` — Mempertahankan guard checkbox Opsi A (`canEditTargetRow()`), menambahkan state `isSubmittingBatchRemove`, fungsi `confirmBatchRemoveTargetKavlings()` + handler `#target-btn-batch-remove`, serta memperbarui `submitPlanningKavlingChangeRequest()` untuk preview Swal terkelompok per tipe dan menyegarkan badge via `window.refreshRequestKavlingCounts()`.
  3. `public/assets/css/siteplan-target.css` — Menambahkan CSS scoped untuk `#target-btn-batch-remove`, `.target-planning-swal-group*`, dan `.target-remove-swal-*` (light, `.dark-layout`, & mobile `< 768px`).
  4. `public/assets/js/siteplan/request-kavling.js` — Mengekspos `window.refreshRequestKavlingCounts = loadCountsSummary`, serta menambahkan `.rk-kavling-count-badge` pada `renderKavlingsCell()` dan `openApplyUbahKavlingModal()` bila `kavlings.length > 1`.
  5. `public/assets/css/siteplan-request-kavling.css` — Menambahkan batas scroll (`max-height` + `overflow-y: auto`) pada `.rk-chip-stack` (tabel & modal apply) serta styling `.rk-kavling-count-badge`.
- **Verifikasi:**
  - `php -l app/Views/siteplan/target.php`: `No syntax errors detected` (exit 0).
  - `node --check public/assets/js/siteplan/target.js` & `request-kavling.js`: exit 0.
  - `git diff --check`: exit 0.

### Fase C: Inspeksi QA Independen (`sigapp-qa`)
- **Status:** `PASS`
- **Audit Callers (`codegraph_explore` + `git grep`):** `0 broken callers`; seluruh backward-compatibility shims utuh.
- **CLI & Unit Test:**
  - `php spark routes`: Rute `POST api/target/remove-kavling` & `POST api/kavling-request/submit` valid.
  - `vendor/bin/phpunit --no-coverage tests/unit/TargetSiteplanServiceTest.php`: `OK (15 tests, 267 assertions)`.
  - `php spark module:deadcode Siteplan`: 0 kandidat dead code; `Transaksi`: 0 dead code pada `KavlingRequestService`.
  - `php -l` & `git diff --check`: bersih (0 syntax/diff errors).
- **Full JSON Contract Audit:** `RAW_JSON_READY` terverifikasi pada kedua endpoint (`POST /api/kavling-request/submit` dan `POST /api/target/remove-kavling`).
- **Playwright E2E (`http://localhost/sigapp.dev`):**
  - **MKDT (`citra`):** Opsi A checkbox terverifikasi (konsumen aktif disabled); 4 kavling menuju 2 tipe target berbeda (`22/60 LEBAR DEPAN 5m` & `31/72 DESAIN BARU`) menampilkan preview grup Swal dan menghasilkan **2 request** (`Req #51`, `Req #52`), badge sidebar ter-refresh `45 -> 47`; hapus batch 4 kavling uji berhasil (`batch_summary.removed_count: 4`) dan tercatat tepat **1 entri versi baru** (`v3`, `target_remove_kavling`).
  - **Planning (`purnama`):** Modal Request Kavling menampilkan tepat **2 baris request pending** (`Req #51` & `Req #52`), `.rk-kavling-count-badge` (`2 kavling`) & `.rk-chip-stack` scrollable rapi di tabel maupun modal Apply (`#modal-apply-ubah-kavling`). Kedua request uji dibersihkan via UI (`Tolak`) sehingga counter kembali `45`.
  - **Console & UI (`sigapp-ui-acuan`):** `0 JS runtime errors`, `0 CSS/JS asset 404`, `0 API 404/500`, responsive mobile (`375x812`) `docOverflow: false`.

### [2026-11-21 14:17] Fase D (4) — Sinkronisasi Dokumentasi & Katalog Modul (`sigapp-docs`) — `DONE`
- **Module Catalog & Auto-Sync:**
  - Memverifikasi `app/Libraries/ModuleManager/ModuleCatalog.php`: seluruh file modul `Siteplan` (`TargetController`, `TargetSiteplanService`, `TargetSiteplanRepository`, `target.js`, `siteplan-target.css`, `siteplan-request-kavling.css`) dan `Transaksi` (`KavlingRequestService`, `request-kavling.js`) sudah terdaftar lengkap (tidak ada file baru yang ditambahkan).
  - Menjalankan `php spark module:sync Siteplan` dan `php spark module:sync Transaksi` (`REFACTORED 100/100`), lalu merestore daftar `### CSS` pada `File Inventory` di masing-masing `MODULE.md` (karena `ModuleDocGenerator` hanya mencetak seksi `JavaScript`).
- **Pembaruan `docs/modules/Siteplan/MODULE.md` & `docs/modules/Transaksi/MODULE.md`:**
  - Memperbarui Status Modul, Contracts Public (perluasan `POST /api/target/remove-kavling` & `POST /api/kavling-request/submit` cabang batch `ubah_tipe`), File Inventory (termasuk line count CSS/JS/PHP terbaru), `## Key Functions`, `## Deprecated & Legacy Shims` (mencatat eksplisit tidak ada deprecations baru serta mempertahankan `deleteTargetKavling()` & `locateTargetKavlingRow()`), `## Notes` (Cross-Module Callers), serta menambahkan entri terbaru `2026-11-21 [FEATURE]` di paling atas `## Change Log`.
- **Dokumen Kondisional:**
  - `docs/MIGRATION_PROGRESS.md`: **Tidak diubah** (mode tugas `FEATURE`, bukan `REFACTOR` modul penuh).
  - `docs/modul-notifikasi.md`: **Tidak diubah** (tetap memakai `NotificationEvent::REQUEST_KAVLING` ke grup Planning `[6]`).
- **Verifikasi Git:** `git status --short` terverifikasi; hanya file target tugas, dokumen modul/log kerja yang relevan, serta modifikasi pra-ada `.agents/*` & `AGENTS.md` yang tercatat di working tree.

---

## Ringkasan File Final

- **Backend & Unit Test (`5` file):**
  - `app/Controllers/Api/Siteplan/TargetController.php`
  - `app/Repositories/Siteplan/TargetSiteplanRepository.php`
  - `app/Services/Siteplan/TargetSiteplanService.php`
  - `app/Services/Transaksi/KavlingRequestService.php`
  - `tests/unit/TargetSiteplanServiceTest.php`
- **Frontend View, CSS, & JS (`5` file):**
  - `app/Views/siteplan/target.php`
  - `public/assets/css/siteplan-target.css`
  - `public/assets/css/siteplan-request-kavling.css`
  - `public/assets/js/siteplan/target.js`
  - `public/assets/js/siteplan/request-kavling.js`
- **Dokumentasi (`3` file):**
  - `docs/modules/Siteplan/MODULE.md`
  - `docs/modules/Transaksi/MODULE.md`
  - `docs/2026-11-21-siteplan-target-batch-remove-dan-grouped-request-planning.md`
- **Catatan Working Tree (`git status --short`):** Terdapat pula modifikasi pra-ada di luar lingkup fitur pada `AGENTS.md` dan 7 file `.agents/` (`.agents/rules/{sigapp-module-qa,sigapp-orchestrator}.md`, `.agents/skills/sigapp-module-qa/{SKILL.md,references/docs-sync-guide.md,references/plan-validation-checklist.md}`, `.agents/skills/sigapp-orchestrator/{SKILL.md,references/subagent-definitions.md}`).
