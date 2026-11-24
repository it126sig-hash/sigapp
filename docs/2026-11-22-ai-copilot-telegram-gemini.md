# 2026-11-22 — Log Progres & Debugging: AI Copilot (Gemini, Tab Web & Bot Telegram)

- **Tanggal Mulai:** 2026-11-22 (dilanjutkan 2026-11-24)
- **Modul Terkait:** `Copilot` (lintas-baca: `Keuangan`, `MKDT`, `Proyek`, `AuditTrail`, `Settings`)
- **Mode Tugas:** `FEATURE`
- **Status Keseluruhan:** `COMPLETED` (`DONE — Lulus QA Independen & Sinkronisasi Docs`)

---

## 1. Tujuan & Keputusan yang Disetujui User
1. **Lokasi API Key & Pengaturan Provider:** API key LLM (`GEMINI_API_KEY`, `OPENAI_API_KEY`, dll.) disimpan di `.env`; sedangkan `provider`, `model`, dan `base_url` disimpan di tabel DB `copilot_settings` dan dapat diubah dari Tab Copilot (khusus Admin).
2. **Definisi "Jatuh Tempo Cicilan":** Tagihan tabel `keuangan` dengan `sudah_dibayar = 0`, `is_void = 0`, konsumen tidak batal (`mkdt.is_batal = 0` & `status_mkdt != 'Batal'`), dan `jatuh_tempo_tgl <= hari ini + N hari` (default `N = 7`, dapat diminta user). Hasil dipisah menjadi kategori **Terlambat** dan **Akan jatuh tempo**.
3. **Hak Akses RBAC & Menu:**
   - Tool `get_jatuh_tempo_cicilan`: hanya grup **Admin (1), Keuangan (3), MKDT (4), Sales (8), Direksi (9)**.
   - Tool `get_booking_per_bulan` & `get_konversi_booking_akad`: **semua user** (seluruh grup aktif).
   - Menu **Copilot** (`/copilot`): **semua user** (seluruh grup aktif di `auth_groups`).
4. **Perlindungan Data Sensitif (PII):** LLM boleh melihat `nama_konsumen` dan kavling terpilih, tetapi dilarang keras mengakses data konfidensial (`nik`, `npwp`, `nik_pasangan`, `hp_*`, `alamat_*`, `email_*`, `file_*`, `keterangan`, `void_reason`, data rekening/pekerjaan/pasangan). Diterapkan via SQL SELECT whitelist + DTO whitelist + `PiiGuard`.
5. **Telegram di Shared Hosting:** Menggunakan webhook sinkron (`POST /api/public/copilot/telegram/webhook`) dengan verifikasi header `X-Telegram-Bot-Api-Secret-Token`, dedup `telegram_update_id`, timeout LLM ≤ 20 detik, serta command `php spark copilot:telegram-poll` untuk dev lokal.
6. **Penautan Akun & Riwayat:** Penautan Telegram via kode 6 digit (berlaku 10 menit, disimpan sebagai hash) melalui perintah `/link KODE` di chat privat. Riwayat chat disimpan 90 hari dan dibersihkan via `php spark copilot:prune`.

---

## 2. Pelacak Progres per Fase

| Fase | Lingkup | Agen | Status | Catatan Ringkas |
|---|---|---|---|---|
| **Fase A** | Config, migrasi 3 tabel + menu Copilot, LLM Provider (`GeminiProvider`, `OpenAiCompatibleProvider`, `LlmProviderFactory`), CLI `copilot:ask`, unit test adapter | Orchestrator + `sigapp-engineer` | `DONE` | 12 file PHP dibuat, `php -l` bersih, migrasi Batch 51 sukses, 6/6 unit test (45 assertions) PASS |
| **Fase B1** | 3 Tool read-only, `JatuhTempoRepository`, `PiiGuard`, `CopilotAuthorizer`, `ToolRegistry` + unit test | `sigapp-engineer` | `DONE` | 9 file dibuat, `php -l` bersih, 11/11 unit test (251 assertions) PASS |
| **Fase B2** | `CopilotOrchestrator`, `MessageRepository`, rate limit, audit `history_log`, API `/api/copilot/*` (`meta`, `chat`, `settings`) | `sigapp-engineer` | `DONE` | 6 file baru + 3 diubah, `php -l` bersih, `php spark routes` OK, 14/14 unit test (330 assertions) PASS |
| **Fase B3** | `TelegramService`, webhook + dedup, `/link`, CLI `copilot:telegram-webhook`, `copilot:telegram-poll`, `copilot:prune` | `sigapp-engineer` | `DONE` | 8 file baru + `Routes.php`, `php -l` bersih, `routes` OK, 17/17 test (389 assertions) PASS |
| **Fase C** | Frontend Tab Copilot (`app/Views/copilot/index.php`, `copilot.css`, `copilot.js`), kartu Telegram, panel Admin settings | `sigapp-frontend` | `DONE` | 3 file frontend dibuat sesuai `sigapp-ui-acuan`, `php -l`, `node --check`, `git diff --check` bersih |
| **Fase D1** | QA Independen (`codegraph`, Playwright E2E multi-role, `sigapp-ui-acuan`, `php spark routes`, uji negatif PII) | `sigapp-qa` | `DONE` | Putaran 2 `PASS` (0 console error di Keuangan/MKDT/Legal, 17/17 test PASS, UI & keamanan lulus) |
| **Fase D2** | `module:sync Copilot`, `docs/modules/Copilot/MODULE.md`, finalisasi work doc, verifikasi `git status` | `sigapp-docs` | `DONE` | `ModuleCatalog.php` diperbarui, `module:sync Copilot` (`90/100`), `MODULE.md` & work doc selesai |

---

## 3. Log Progres & Debugging

### [2026-11-24 20:42] Fase 1 — Eksplorasi & Plan Disetujui (`sigapp-explorer` / Orchestrator)
- **Temuan & Blast Radius:** `KeuanganRepository::getAllJatuhTempo` membawa `hp_konsumen` & `alamat_konsumen` serta tidak menyaring batal/lunas, sehingga perlu query read-only baru (`JatuhTempoRepository`) dengan SELECT whitelist. `Mkdt\BookingAkadReportService::getSummary` bebas session dan dapat langsung dipakai ulang. `ProyekRepository::getAccessibleForUser` bebas session dan aman untuk webhook/CLI. `auth_groups` memiliki ID 1–10 (+11). Endpoint webhook aman diletakkan di grup `api/public/*` yang sudah bebas filter `login` dan memakai throttle.
- **Keputusan User:** Plan disetujui dengan revisi RBAC: `get_jatuh_tempo_cicilan` untuk Admin/Keuangan/MKDT/Sales/Direksi, sedangkan `get_booking_per_bulan` & `get_konversi_booking_akad` (dan menu Copilot) dapat diakses semua user.

### [2026-11-24 20:49] Fase A — Fondasi LLM, Config, Migrasi DB & Menu (`sigapp-engineer`)
- **Perubahan:** Membuat `app/Config/Copilot.php`, migrasi `2026-11-25-000002_CreateCopilotTables.php` & `2026-11-25-000003_AddCopilotMenu.php` (menu ID 53 `AI Copilot`, icon `message-square`, semua grup `auth_groups`), `SettingRepository`, `LlmProviderInterface`, `LlmResponse`, `LlmProviderException`, `GeminiProvider`, `OpenAiCompatibleProvider`, `LlmProviderFactory`, `CopilotAsk` (`php spark copilot:ask`), dan `tests/unit/Copilot/LlmProviderTest.php`.
- **Debugging / Temuan:** Timestamp migrasi disesuaikan menjadi `2026-11-25-000002` & `000003` mengikuti migrasi terakhir repo (`2026-11-25-000001`). `.gitignore` mengabaikan `/app/Config/*` kecuali `Filters.php` dan `Routes.php` (perlu diperhatikan saat commit/sync).
- **Verifikasi:** `php -l` 0 error pada 12 file; `php spark migrate` & `migrate:status` ter-apply (Batch 51); `vendor/bin/phpunit tests/unit/Copilot/LlmProviderTest.php` PASS 6/6 (45 assertions); `php spark copilot:ask "halo"` merespons graceful error karena `GEMINI_API_KEY` belum diisi di `.env`.

### [2026-11-24 20:55] Fase B1 — Tool Registry, PII Guard, RBAC & 3 Tools (`sigapp-engineer`)
- **Perubahan:** Membuat `JatuhTempoRepository`, `PiiGuard`, `CopilotAuthorizer`, `ToolRegistry`, `CopilotToolInterface`, `JatuhTempoCicilanTool`, `BookingPerBulanTool`, `KonversiBookingAkadTool`, dan `tests/unit/Copilot/ToolsAndSecurityTest.php`.
- **Debugging / Temuan:** `phpunit` tanpa `--no-coverage` memicu warning `No code coverage driver available` karena `failOnWarning="true"` di `phpunit.xml.dist`; menjalankan dengan `--no-coverage` bersih. Query `JatuhTempoRepository` diverifikasi mengecualikan `is_batal=1`, `status_mkdt='Batal'`, `is_void=1`, `sudah_dibayar=1`, `is_lunas=1` dengan SELECT whitelist ketat, dan angka `BookingPerBulanTool`/`KonversiBookingAkadTool` identik dengan `BookingAkadReportService::getSummary()`.
- **Verifikasi:** `php -l` 0 error pada 9 file; `vendor/bin/phpunit --no-coverage tests/unit/Copilot/ToolsAndSecurityTest.php tests/unit/Copilot/LlmProviderTest.php` PASS 11/11 (251 assertions).

### [2026-11-24 21:02] Fase B2 — Orchestrator, Audit, Web & API Controllers (`sigapp-engineer`)
- **Perubahan:** Membuat `MessageRepository`, `CopilotOrchestrator`, `Web\Copilot\CopilotController`, `Api\Copilot\ChatController`, `Api\Copilot\SettingController`, dan `tests/unit/Copilot/OrchestratorAndApiTest.php`; memperbarui `HistoryService::MODULE_LABELS['copilot']`, `CopilotAsk` (`--user`), dan `app/Config/Routes.php` (6 rute eksplisit).
- **Debugging / Temuan:** `GeminiProvider` & `OpenAiCompatibleProvider` membaca instruksi sistem dari item `role => 'system'` di `$messages`, sehingga `CopilotOrchestrator` menyertakan system prompt di `$messages[0]` sekaligus `$options['systemInstruction']`. Signature `SettingController::update($id = null)` disesuaikan dengan `ResourceController::update`.
- **Verifikasi:** `php -l` 0 error pada 9 file; `php spark routes` menampilkan 6 rute aktif; `vendor/bin/phpunit --no-coverage tests/unit/Copilot` PASS 14/14 (330 assertions).

### [2026-11-24 21:08] Fase B3 — Integrasi Telegram Bot (`sigapp-engineer`)
- **Perubahan:** Membuat `TelegramLinkRepository`, `TelegramService`, `Api\Copilot\TelegramLinkController`, `Api\Public\CopilotTelegramController`, `CopilotTelegramWebhook`, `CopilotTelegramPoll`, `CopilotPrune`, dan `tests/unit/Copilot/TelegramIntegrationTest.php`; memperbarui `app/Config/Routes.php` (`POST/DELETE api/copilot/telegram/link`, `POST api/public/copilot/telegram/webhook`).
- **Debugging / Temuan:** `verifyAndBindByCodeHash` menangani kasus 1 akun Telegram sebelumnya tertaut ke user lain dengan melepaskan tautan lama dalam transaksi DB sebelum bind baru agar tidak terbentur unique constraint `telegram_user_id`. Webhook selalu mengembalikan HTTP 200 untuk payload valid setelah verifikasi `hash_equals` pada `X-Telegram-Bot-Api-Secret-Token` agar Telegram tidak melakukan retry berulang.
- **Verifikasi:** `php -l` 0 error pada 9 file; `php spark routes` menampilkan seluruh rute web, `api/copilot/*`, & `api/public/copilot/telegram/webhook`; `vendor/bin/phpunit --no-coverage tests/unit/Copilot` PASS 17/17 (389 assertions); `php spark copilot:prune --days=90` sukses.

### [2026-11-24 21:12] Fase C — Tab Web AI Copilot & UI (`sigapp-frontend`)
- **Perubahan:** Membuat `app/Views/copilot/index.php`, `public/assets/css/copilot.css`, dan `public/assets/js/copilot.js` dengan pola 3-Tier (`filemtime`, CSS scoped `.copilot-page`, `#2057a3`, `divider-left`, render bubble chat anti-XSS via DOM `createTextNode`, kartu pairing Telegram, panel pengaturan provider Admin-only, serta tab mobile 1 baris horizontal scrollable).
- **Debugging / Temuan:** Padding bawah `.copilot-page` pada `< 767.98px` diset `5rem` agar tidak tertutup `#sigapp-mobile-bottom-nav`.
- **Verifikasi:** `php -l app/Views/copilot/index.php` bersih, `node --check public/assets/js/copilot.js` exit 0, `git diff --check` bersih, 0 pola terlarang (`time()`, `<style>`, `uploads/`).

### [2026-11-24 21:22] Fase D1 (Putaran 1) — QA Independen (`sigapp-qa`) → `FAIL`
- **Temuan & RCA:** Codegraph (0 broken callers), `php spark routes` (9 rute OK), `migrate:status` (applied), PHPUnit (17/17 PASS, 389 assertions), Playwright multi-role RBAC (Keuangan/MKDT/Legal), Telegram pairing flow, mobile layout (`375x812`), dan uji keamanan webhook (`503` JSON) seluruhnya lulus. Namun setiap pemuatan `/copilot` memicu 3 console error `404` pada `fa-brands-400.{woff2,woff,ttf}` karena `app/Views/copilot/index.php` L77 & L163 memakai `fab fa-telegram-plane` yang font Brands-nya tidak dibundel di `public/app-assets/vendors/css/webfonts/`.
- **Tindak Lanjut (Fase 3B):** `bugfix_plan.md` dibuat; mendelegasikan perbaikan surgical ke `sigapp-frontend`.

### [2026-11-24 21:24] Fase 3B — Perbaikan Ikon `fa-brands` (`sigapp-frontend`)
- **Perubahan:** Mengganti `fab fa-telegram-plane` menjadi `fas fa-paper-plane` pada `app/Views/copilot/index.php` baris 77 & 163; memastikan `copilot.js` hanya memakai `fas fa-bolt`.
- **Verifikasi:** `php -l app/Views/copilot/index.php` bersih, `git diff --check` exit 0, 0 sisa class `fab`. Mendelegasikan re-audit ke `sigapp-qa`.

### [2026-11-24 21:26] Fase D1 (Putaran 2) — QA Independen (`sigapp-qa`) → `PASS`
- **Verifikasi Ulang:** Playwright pada Keuangan (`nisalis`), MKDT (`citra`), dan Legal (`marlina`) menghasilkan **0 console errors & 0 warnings**. `#copilot-tab-telegram i.fas.fa-paper-plane` dan `#copilot-telegram-card .card-header i.fas.fa-paper-plane` ter-render normal memakai `fa-solid-900.woff2` warna `#2057a3`. Seluruh skenario RBAC, pairing Telegram, error handling `502` JSON, webhook publik `503` JSON, `php spark routes`, `migrate:status`, codegraph, dan 17/17 unit test (389 assertions) berstatus `PASS`.

### [2026-11-24 21:30] Fase 4 / D2 — Sinkronisasi Dokumentasi & Katalog Modul (`sigapp-docs`) — DONE
- **Katalog & `module:sync`:** Mendaftarkan modul `Copilot` ke `app/Libraries/ModuleManager/ModuleCatalog.php` (5 Controller, 15 Service/Tool, 4 Repository, 1 folder View, CSS/JS, 3 owner tables, 13 shared tables) dan menjalankan `php spark module:sync Copilot` (Status `MAPPED`, Readiness Score `90/100`, checklist `MODULE.md Initialized` tercentang `[x]`; `Web & API Controllers Separated` belum penuh karena `Api\Public\CopilotTelegramController` berada di sub-namespace `Api\Public`).
- **Kurasi `docs/modules/Copilot/MODULE.md`:** Melengkapi `Purpose`, `Out of scope`, `Invariants`, `Database Tables`, `Contracts` (9 rute eksplisit & 15 kontrak Service), `File Inventory`, `Key Functions`, `Deprecated & Legacy Shims` (tidak ada), `Notes` (file Config/Migrations/Commands/CSS/Tests & Cross-Module Callers/Dependencies), serta `Change Log` (`2026-11-24 [FEATURE]`).
- **Ledger Lain:** `docs/MIGRATION_PROGRESS.md` dan `docs/modul-notifikasi.md` **tidak diubah** karena mode tugas adalah `FEATURE` (bukan `REFACTOR` modul penuh) dan tidak ada perubahan notifikasi.

---

## 4. Ringkasan File Final
Sinkron dengan `git status --short`:

- **Modified (`M`):**
  - `app/Config/Routes.php` (9 rute eksplisit Copilot: web `/copilot`, API `/api/copilot/*`, & webhook publik `/api/public/copilot/telegram/webhook`)
  - `app/Libraries/ModuleManager/ModuleCatalog.php` (registrasi definisi modul `Copilot`)
  - `app/Services/AuditTrail/HistoryService.php` (`MODULE_LABELS['copilot'] = 'AI Copilot'`)
- **Untracked / New (`??`):**
  - `app/Commands/CopilotAsk.php`, `CopilotPrune.php`, `CopilotTelegramPoll.php`, `CopilotTelegramWebhook.php`
  - `app/Controllers/Api/Copilot/` (`ChatController.php`, `SettingController.php`, `TelegramLinkController.php`)
  - `app/Controllers/Api/Public/CopilotTelegramController.php`
  - `app/Controllers/Web/Copilot/CopilotController.php`
  - `app/Database/Migrations/2026-11-25-000002_CreateCopilotTables.php`, `2026-11-25-000003_AddCopilotMenu.php`
  - `app/Repositories/Copilot/` (`JatuhTempoRepository.php`, `MessageRepository.php`, `SettingRepository.php`, `TelegramLinkRepository.php`)
  - `app/Services/Copilot/` (`CopilotAuthorizer.php`, `CopilotOrchestrator.php`, `GeminiProvider.php`, `LlmProviderException.php`, `LlmProviderFactory.php`, `LlmProviderInterface.php`, `LlmResponse.php`, `OpenAiCompatibleProvider.php`, `PiiGuard.php`, `TelegramService.php`, `ToolRegistry.php`, serta `Tools/` — `BookingPerBulanTool.php`, `CopilotToolInterface.php`, `JatuhTempoCicilanTool.php`, `KonversiBookingAkadTool.php`)
  - `app/Views/copilot/index.php`
  - `docs/2026-11-22-ai-copilot-telegram-gemini.md`
  - `docs/modules/Copilot/MODULE.md`
  - `public/assets/css/copilot.css`
  - `public/assets/js/copilot.js`
  - `tests/unit/Copilot/` (`LlmProviderTest.php`, `OrchestratorAndApiTest.php`, `TelegramIntegrationTest.php`, `ToolsAndSecurityTest.php`)
- **Catatan Khusus `.gitignore`:**
  - `app/Config/Copilot.php` dibuat dan aktif digunakan oleh sistem, tetapi **tidak muncul** di `git status --short` karena aturan `.gitignore` baris 135–137 (`/app/Config/*` mengecualikan selain `Filters.php` dan `Routes.php`). Saat commit/deploy ke repository, pastikan `app/Config/Copilot.php` ikut di-stage (`git add -f app/Config/Copilot.php`) atau aturan `.gitignore` disesuaikan bila diperlukan.
