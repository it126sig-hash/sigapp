# 2026-11-24 — Log Progres & Debugging: Infrastruktur Notifikasi Realtime (Vue-Ready) & Pusat Permohonan (`/permohonan`)

- **Tanggal Mulai:** 2026-11-24
- **Modul Terkait:** `Notifikasi` & `Permohonan` *(Lintas Modul: `BPB`, `SalesExternal`, `Transaksi/KavlingRequest`, `Keuangan/Direksi/CashoutSubkon/DanaJaminan`, `MGM`, `TiketMasalah`, `Legal`)*
- **Mode Tugas:** `FEATURE`
- **Status Keseluruhan:** `COMPLETED` (`DONE — Lulus QA Independen & Sinkronisasi Docs`)

---

## 1. Tujuan & Keputusan yang Disetujui User
1. **Skema Realtime & Cutoff Sesi Device:** Post-Response Immediate Push Dispatch (`register_shutdown_function` + `fastcgi_finish_request()` pasca-commit transaksi DB di `NotifikasiService::create()`), `BroadcastChannel('sigapp_notif_realtime')` di `public/sw.js`, cron 5 menit sebagai *retry sweeper*, serta batas inaktivitas (idle cutoff) sesi device **> 30 hari**.
2. **Dukungan Multi-Platform (Vue & Capacitor-Ready):** Upgrade tabel `push_subscriptions` dan `/api/notif/push/subscribe` agar mendukung dual-provider (`web_push` VAPID + `fcm` token Capacitor Android/iOS) dan resolusi `device_session_id` melalui cookie SPA (`sigapp_spa_token`) / Bearer token.
3. **Taksonomi 2-Dimensi & 6 Event Gap Lintas Departemen:** Penambahan metadata `event_type`, `category`, `intent` (`action_required` | `urgent_alert` | `operational_info` | `system_master`), `target_module`, `ref_id`, `spa_route`; serta penambahan event `legal_dokumen_updated`, `tagihan_signature_requested`, `tagihan_signature_responded`, `bpb_execution_requested`, `dajam_pengajuan_created`, `dajam_pencairan_completed`, callback pembayaran `cashout_subkon`, dan perapihan `relevant_groups` di seeder.
4. **Halaman Khusus Pusat Permohonan (`/permohonan` + `/api/permohonan/*`):** Mengagregasi 7 modul request utama (`BPB`, `Fee Sales`, `Request Kavling`, `TTD Tagihan Direksi`, `Cashout Subkon`, `MGM Referral`, `Tiket Masalah`) dengan 3 scope (`assigned_to_me`, `my_requests`, `department_monitoring`) dan 7 modal respon langsung.

---

## 2. Pelacak Progres per Fase

| Fase | Lingkup | Agen | Status | Catatan Ringkas |
|---|---|---|---|---|
| **Fase A** | Migration Dual-Provider & Menu `/permohonan`, Enum 6 Event, Seeder, & `NotificationTaxonomy` | `sigapp-engineer` | `DONE` | Migration Batch 50 sukses (`push_subscriptions` dual-provider, 47 event types, menu `/permohonan` id=52). `NotificationTaxonomy` & filter `intent`/`category` lulus `php -l` & smoke test. |
| **Fase B1** | Post-Response Immediate Push, Cutoff Sesi 30 Hari, & Subscribe Dual-Provider | `sigapp-engineer` | `DONE` | Post-response dispatch (`register_shutdown_function` + `fastcgi_finish_request()`), cutoff sesi idle 30 hari, subscribe dual-provider (`web_push` + `fcm`) + resolusi `sigapp_spa_token` selesai. `php -l` & `DeviceManagementServiceTest` (5/5) PASS. |
| **Fase B2** | Event Notifikasi Gap Lintas Modul (`Legal`, `Penagihan`, `Direksi`, `BPB`, `Dajam`, `CashoutSubkon`) | `sigapp-engineer` | `DONE` | 6 event gap & callback bayar `CashoutSubkon` diintegrasikan via `NotifikasiService` (`try/catch` pasca-commit). `php -l` (6/6) & `BpbPdfServiceTest` (2/2) PASS. |
| **Fase B3** | Frontend Realtime `BroadcastChannel` (`sw.js`, `scripts.js`), Filter Intent `#list-notif`, & Pintasan `/permohonan` | `sigapp-frontend` | `DONE` | `BroadcastChannel` + `client.postMessage()` di `sw.js`, listener debounce 400ms + `visibilitychange` di `scripts.js`, filter pill `.notif-intent-pill` & `#notif-permohonan-link` di `#list-notif`. `php -l`, `node --check`, & `git diff --check` lulus. |
| **Fase C1** | Backend Pusat Permohonan (`PermohonanRepository`, `PermohonanService`, `Web` & `Api` Controller, `Routes.php`) | `sigapp-engineer` | `DONE` | `PermohonanRepository` (UNION ALL + batch hydration aman collation), `PermohonanService`, `Api\Permohonan\PermohonanController` (`meta`, `summary`, `list`), `Web\Permohonan\PermohonanController` (`/permohonan`). `php -l`, `php spark routes`, & smoke test 4 akun departemen PASS. |
| **Fase C2** | Frontend Halaman `/permohonan` (`index.php`, `permohonan.css`, `permohonan.js`) & 7 Modal Respon Langsung | `sigapp-frontend` | `DONE` | View, CSS eksternal (`filemtime`, scoped `.permohonan-page`), JS controller + integrasi 7 modal (BPB, Fee Sales, Request Kavling, Tagihan Direksi, Cashout Subkon, MGM, Tiket Masalah) selesai. `php -l`, `node --check`, & `git diff --check` bersih. |
| **Fase D1** | Inspeksi QA Independen & Playwright E2E Multi-Departemen | `sigapp-qa` | `DONE` | Putaran 2: `PASS`. Bug `sanitizeUrl()` terverifikasi fix; Keuangan (`nisalis`), Planning (`purnama`), MKDT (`citra`), Promosi (`benny`), Full JSON API, codegraph, PHPUnit (7/7), & `sigapp-ui-acuan` lulus. (Catatan: akun lokal `alika` gagal login password DB lokal). |
| **Fase D2** | Sinkronisasi `MODULE.md`, `docs/modul-notifikasi.md`, & Finalisasi Work Doc | `sigapp-docs` | `DONE` | `ModuleCatalog.php` diperbarui (`Notifikasi` + modul baru `Permohonan`), `php spark module:sync` (100/100 `REFACTORED`), `Notifikasi/MODULE.md`, `Permohonan/MODULE.md`, `docs/modul-notifikasi.md`, & work doc difinalisasi. |

---

## 3. Log Progres & Debugging

### [2026-11-24 19:12] Fase 1 — Eksplorasi & Plan Disetujui (`sigapp-explorer`)
- **Temuan & Blast Radius:** Pemetaan codegraph `NotifikasiService` (15+ service callers), `PushSubscriptionModel` (4 callers + `DeviceManagementServiceTest`), `DeviceSessionService` (15 callers), dan 6 service lintas modul selesai. Struktur tabel & endpoint existing untuk 7 modul request di `/permohonan` (`bpb_requests`, `fee_sales_pengajuan`, `kavling_request`, `invoice_log`, `cashout_subkon_detail`, `referral_bonuses`, `tiket_masalah`) telah dipetakan.
- **Plan Artefak:** Disetujui user pada `implementation_plan.md`.

### [2026-11-24 19:22] Fase A — Fondasi Database, Enum Event, Seeder, & Taksonomi 2-Dimensi (`sigapp-engineer`)
- **Perubahan:**
  - Migration `2026-11-25-000001_UpgradePushSubscriptionsAndNotificationTaxonomy.php` dijalankan (`push_subscriptions` tambah `provider`, `fcm_token`, `platform`; `p256dh_key`/`auth_token` nullable; sinkronisasi seeder; menu `Permohonan & Approval` `id=52` untuk role `1..10`).
  - `NotificationEvent.php` & `NotificationEventTypeSeeder.php`: tambah 6 event baru (`legal_dokumen_updated`, `tagihan_signature_requested`, `tagihan_signature_responded`, `bpb_execution_requested`, `dajam_pengajuan_created`, `dajam_pencairan_completed`), sinkronisasi `fee_sales_pengajuan_rejected`, dan perapihan `relevant_groups` serta default channel.
  - `NotificationTaxonomy.php`, `NotificationRepository.php`, & `Api\Notifikasi\NotifikasiController.php`: penambahan metadata 2-dimensi (`event_type`, `category`, `intent`, `target_module`, `ref_id`, `spa_route`) serta dukungan filter `intent` & `category`.
- **Debugging / Temuan:** Tidak ada error; seluruh 12 skenario taksonomi & query filter `listForUser()` lolos smoke test.
- **Verifikasi:** `php -l` bersih pada 6 file; `php spark migrate` (Batch 50) & `php spark migrate:status` sukses.

### [2026-11-24 19:29] Fase B1 — Post-Response Immediate Push, Cutoff Sesi 30 Hari, & Dual-Provider (`sigapp-engineer`)
- **Perubahan:**
  - `NotifikasiService::create()` + `scheduleImmediatePushDispatch()`: eksekusi post-response immediate push pasca-commit transaksi (`register_shutdown_function` + `fastcgi_finish_request()`/`litespeed_finish_request()`) dengan guard CLI/testing.
  - `NotificationDispatchService::dispatchForNotification()`: claim atomik per `notification_id` dan filter `provider = 'web_push'` di `dispatchWebPush()`.
  - `PushSubscriptionModel`: dukungan `provider = 'web_push' | 'fcm'`, `fcm_token`, `platform`, `deleteSubscription()` by endpoint atau token, serta cutoff `last_seen_at >= -30 days` pada `activeForUser()`.
  - `NotifPushController`: resolusi `device_session_id` dari cookie `sigapp_spa_token` / Bearer token via `DeviceSessionService`, validasi payload `web_push` & `fcm`, serta filter `web_push` pada `test()`.
- **Debugging / Temuan:** `tests/database/DeviceManagementServiceTest.php` sempat gagal 1 assertion pada `testPushDeliveryOnlyReturnsSubscriptionsForActiveLinkedDevices` karena mock clock test di-hardcode `2026-09-30` (55 hari sebelum `2026-11-24`). Test tersebut diperbarui dengan izin Orchestrator agar menguji sesi aktif terkini sekaligus memverifikasi penolakan device idle `-31 days`.
- **Verifikasi:** `php -l` lulus (5 file); smoke test SQLite `PushSubscriptionModel` & `dispatchForNotification` PASS; `vendor/bin/phpunit tests/database/DeviceManagementServiceTest.php` 5/5 PASS (24 assertions).

### [2026-11-24 19:34] Fase B2 — Event Notifikasi Gap Lintas Modul (`sigapp-engineer`)
- **Perubahan:**
  - `LegalService` (`simpan`, `uploadDocument`) -> kirim `legal_dokumen_updated` ke `'3;4;5;10'`.
  - `PenagihanService` (`simpanPenagihan`, `updateStatusPenagihan`) -> kirim `tagihan_signature_requested` ke `'9'` saat `publish`.
  - `DireksiService` (`signTagihan`, `rejectTagihan`) -> kirim/lengkapi `tagihan_signature_responded` ke `'3'`.
  - `BpbService` (`sign`) -> kirim `bpb_execution_requested` ke `'2;3'` saat status menjadi `approved`.
  - `DanaJaminanService` (`storePengajuan`, `cairkanPengajuan`) -> kirim `dajam_pengajuan_created` (`'3;4'`) dan `dajam_pencairan_completed` (`'3;4;9'`).
  - `CashoutSubkonService` (`pembayaran`, `notifyCashoutSubkon`) -> kirim `cashout_subkon` ke `[7]` (Produksi) saat pembayaran cek disimpan; hapus pemanggilan shim controller `\App\Controllers\Notif` & shim `App\Services\NotifikasiService`.
- **Debugging / Temuan:** Tidak ada error; seluruh notifikasi dibungkus `try/catch` pasca-commit.
- **Verifikasi:** `php -l` lulus (6/6 file); `vendor/bin/phpunit tests/unit/BpbPdfServiceTest.php` 2/2 PASS (26 assertions).

### [2026-11-24 19:41] Fase B3 — Frontend Realtime `BroadcastChannel` & Filter Intent `#list-notif` (`sigapp-frontend`)
- **Perubahan:**
  - `public/sw.js`: menambahkan `BroadcastChannel('sigapp_notif_realtime')` (`PUSH_RECEIVED`) dan `clients.matchAll()` -> `client.postMessage({ type: 'SIGAPP_PUSH_RECEIVED' })` di event `push`.
  - `app/Views/template/generate_menu.php`: menambahkan `#notif-permohonan-link` (`.notification-permohonan-btn` ke `/permohonan`) dan filter bar `#notif-intent-filter` (`.notif-intent-pill`: `Semua`, `Perlu Tindakan`, `Operasional`, `Sistem`).
  - `public/assets/css/style.css`: styling `.notification-permohonan-btn`, `.notif-intent-scroll`, `.notif-intent-pill`, dan `.activity-intent-badge` (dengan varian `.dark-layout`).
  - `public/assets/js/scripts.js`: menambahkan `initNotificationRealtimeListeners()`, `scheduleRealtimeNotifSync()` (debounce 400ms), `visibilitychange` sync (>15s), filter parameter `intent` pada `/notif/center` & `/loadnotif`, badge `Perlu Tindakan`, serta dukungan `handleNotificationClick()` untuk 6 event baru.
- **Debugging / Temuan:** Tidak ada error; seluruh selector CSS di-scope pada `.notification-center-modal` dan `.activity-item`.
- **Verifikasi:** `php -l app/Views/template/generate_menu.php`, `node --check public/sw.js`, `node --check public/assets/js/scripts.js`, dan `git diff --check` semuanya lulus (exit 0).

### [2026-11-24 19:52] Fase C1 — Backend Pusat Permohonan & Approval (`sigapp-engineer`)
- **Perubahan:**
  - `app/Repositories/Permohonan/PermohonanRepository.php`: query teragregasi 7 modul (`bpb_requests`, `fee_sales_pengajuan`, `kavling_request`, `invoice_log`, `cashout_subkon_detail`, `referral_bonuses`, `tiket_masalah`) menggunakan `UNION ALL` ringan + batch hydration per modul.
  - `app/Services/Permohonan/PermohonanService.php`: `resolveUserContext()`, `getMeta()`, `getSummary()`, `getList()` dengan DTO seragam dan `modal_payload` spesifik per modul.
  - `app/Controllers/Api/Permohonan/PermohonanController.php`: `meta()`, `summary()`, `list()` (`RAW_JSON_READY`).
  - `app/Controllers/Web/Permohonan/PermohonanController.php`: `index()` merender `template` dengan `content => 'permohonan/index'` serta data awal (`meta`, `summary`, `projects`, `roleId`, `roleName`, `deepLink`).
  - `app/Config/Routes.php`: mendaftarkan `GET /permohonan` dan `GET /api/permohonan/{meta|summary|list}` dengan filter `login`.
- **Debugging / Temuan:** Terdapat perbedaan collation antar-tabel (`utf8mb4_general_ci`, `utf8mb4_0900_ai_ci`, `latin1_swedish_ci`, `utf8mb3_general_ci`) saat menggabungkan 7 tabel dalam satu `UNION ALL`; diselesaikan dengan index `UNION ALL` ringan (`module`, `item_id`, `sort_at`) lalu batch hydration terpisah per modul. Selain itu `BaseWebController.php` tidak ada di codebase, sehingga Web Controller mengikuti konvensi `BpbController` (`extends BaseController` + `view('template', ...)`).
- **Verifikasi:** `php -l` lulus (5 file); `php spark routes` menampilkan 4 route baru; CLI smoke test terhadap DB `sigapp` untuk Admin (`sig.it`), Keuangan (`nisalis`), Produksi (`alika`), & Planning (`purnama`) sukses tanpa error.

### [2026-11-24 20:03] Fase C2 — Frontend Halaman `/permohonan` & 7 Modal Respon Langsung (`sigapp-frontend`)
- **Perubahan:**
  - `app/Views/permohonan/index.php`: halaman Pola A `.permohonan-page` dengan 3 scope KPI cards, 8 module filter pills, filter proyek/status/pencarian, tabel desktop + `#permohonan-mobile-list`, pagination, serta integrasi 7 modal respon (`#permohonan-bpb-modal`, `#permohonan-fee-sales-modal`, `#permohonan-kavling-modal`, `#permohonan-tagihan-direksi-modal`, `#modal-cashout-subkon`, `#permohonan-mgm-modal`, `#modal_tiket_masalah`).
  - `public/assets/css/permohonan.css` & `public/assets/js/permohonan.js`: stylesheet eksternal scoped (`.permohonan-page`, `#2057a3`, `.dark-layout`, mobile tabs 1 baris, `filemtime`) & JS controller (filter, deep-link auto-open, dan submit aksi modal dengan sinkronisasi `loadNotificationBadge()`).
- **Debugging / Temuan:** `modal_payload` MGM saat ini membawa `{ id_bonus, id_referral, id_proyek }`, sementara `/api/mgm/subrows` membutuhkan `id_konsumen_referrer`; JS melakukan lookup via `/api/mgm/list` untuk mendapatkan `id_konsumen_referrer` sehingga modal MGM tetap berjalan mulus tanpa mengubah backend.
- **Verifikasi:** `php -l app/Views/permohonan/index.php`, `node --check public/assets/js/permohonan.js`, dan `git diff --check` bersih (exit 0).

### [2026-11-24 20:15] Fase D1 (Putaran 1) — Inspeksi QA Independen (`sigapp-qa`)
- **Status Sertifikat:** `FAIL`
- **Hasil Audit:**
  - Codegraph & grep (0 broken callers), `php spark migrate:status` (Batch 50), `php spark routes`, `php spark module:status` (`Notifikasi` 100%), `phpunit` (7 tests, 50 assertions, 0 failures), & `sigapp-ui-acuan` checklist: **LULUS**.
  - Skenario 1 Keuangan (`nisalis`): `#list-notif` (filter intent + `#notif-permohonan-link`), `/permohonan`, Modal Fee Sales, Modal Cashout Subkon, dan Full JSON `/api/permohonan/*` & `/api/notif/*`: **LULUS (0 error)**.
  - Skenario 2 Produksi (`alika`): **BLOCKED** — login gagal dengan `"Unable to log you in. Please check your password."` (tidak ada mutasi DB sesuai aturan `$sigapp-local-testing`).
  - Skenario 3 Planning (`purnama`): `#permohonan-kavling-modal` & `#modal_tiket_masalah` terbuka normal, **ditemukan 1 bug Frontend**: link `"Buka Siteplan"` merender `href="/siteplan/view?..."` tanpa prefix subpath `/sigapp.dev/public` karena regex `sanitizeUrl()` di `public/assets/js/permohonan.js:127-132` meloloskan path berawalan `/` tanpa memanggil `apiUrl(str)`. (Catatan tambahan: 404 pada `files/profile_photo/...` & `files/tiket_masalah/...` berasal dari berkas fisik lama yang memang belum ada di disk lokal).
- **Tindak Lanjut (Fase 3B):** `bugfix_plan.md` dibuat; delegasi perbaikan surgical `sanitizeUrl()` ke `sigapp-frontend`.

### [2026-11-24 20:17] Fase 3B — Perbaikan `sanitizeUrl()` (`sigapp-frontend`)
- **Perubahan:** `public/assets/js/permohonan.js` (`sanitizeUrl`, baris 127–146) diperbarui untuk mengecek `basePath` dari `baseUrl` (`new URL(baseUrl || '/', window.location.origin).pathname`) sehingga path berawalan `/` yang belum memiliki prefix subpath (seperti `spa_route` `/siteplan/view?...`) otomatis digabungkan via `apiUrl(str)`, sekaligus menolak `^//` dan scheme berbahaya (`javascript:`, `data:`, `vbscript:`).
- **Verifikasi:** `node --check public/assets/js/permohonan.js` & `git diff --check` lulus (exit 0). Siap re-audit QA Putaran 2.

### [2026-11-24 20:22] Fase D1 (Putaran 2) — Sertifikat QA `PASS` (`sigapp-qa`)
- **Status Sertifikat:** `PASS`
- **Verifikasi Lanjutan:**
  - Planning (`purnama`): Deep-link `/permohonan?module=kavling_request&id=7` membuka `#permohonan-kavling-modal`, link `"Buka Siteplan"` merender `http://localhost/sigapp.dev/public/siteplan/view?id_proyek=13&id_kavling=808` (`200 OK`, 0 404 subpath).
  - MKDT (`citra`): `/permohonan` (`Menunggu Respon Saya: 1`, `Pengajuan Saya: 7`, `Monitoring: 28`), `#permohonan-fee-sales-modal` (link PDF valid dengan subpath), & `#permohonan-kavling-modal` lulus (`0 console error`).
  - Promosi (`benny`): `#list-notif` & `/permohonan` (`Monitoring: 13`) lulus (`0 console error`).
  - Catatan lingkungan lokal: Akun uji Produksi `alika` di DB lokal tidak cocok dengan password di skill `$sigapp-local-testing` (tidak diubah sesuai aturan); logika backend Produksi telah diuji via CLI dan modal Cashout Subkon/Tiket Masalah diuji via Keuangan/Planning.

### [2026-11-24 20:30] Fase D2 — Sinkronisasi Dokumentasi & Finalisasi (`sigapp-docs`) — DONE
- **Katalog & `module:sync`:**
  - `app/Libraries/ModuleManager/ModuleCatalog.php`: menambahkan `auth_device_sessions` ke `Notifikasi.shared_tables` dan mendaftarkan modul baru `Permohonan` (`Web/Permohonan/PermohonanController.php`, `Api/Permohonan/PermohonanController.php`, `Permohonan/PermohonanService.php`, `Permohonan/PermohonanRepository.php`, `views => ['permohonan']`, `assets_css => ['permohonan.css']`, `assets_js => ['permohonan.js']`, serta `shared_tables` lintas 7 modul).
  - Menjalankan `php spark module:sync Notifikasi`, `php spark module:sync Permohonan`, dan `php spark module:status` (`Notifikasi`: `REFACTORED` `100/100`, `Permohonan`: `REFACTORED` `100/100`; total 18 modul di katalog).
- **Pembaruan `MODULE.md`:**
  - `docs/modules/Notifikasi/MODULE.md`: memperbarui `## Key Functions` (taksonomi `NotificationTaxonomy::resolve()`, filter `intent`/`category`, post-response immediate push `scheduleImmediatePushDispatch()`, `dispatchForNotification()`, dual-provider `web_push`/`fcm` + `sigapp_spa_token`/Bearer + cutoff 30 hari, `BroadcastChannel` + `visibilitychange` + filter intent UI), `## Deprecated & Legacy Shims` (pembersihan caller shim `Notif` di `CashoutSubkonService` & `App\Services\NotifikasiService` di `BpbService`), `## Notes` (aset terkait & *Cross-Module Callers* 6 service baru), dan `## Change Log` (`2026-11-24 [FEATURE]`).
  - `docs/modules/Permohonan/MODULE.md`: membuat & mengkurasi `## Out of scope`, `## Invariants`, `## Key Functions`, `## Deprecated & Legacy Shims`, `## Notes` (`permohonan.css`, integrasi menu `id=52` & `#notif-permohonan-link`, *Cross-Module API Consumers* 7 modal respon langsung), dan `## Change Log` (`2026-11-24 [FEATURE]`).
- **Pembaruan `docs/modul-notifikasi.md`:**
  - Memperbarui arsitektur *Post-Response Immediate Push*, `BroadcastChannel`, dual-provider `web_push`/`fcm` + resolusi `sigapp_spa_token`/Bearer + cutoff 30 hari, taksonomi 2-dimensi, 6 event gap baru + callback `cashout_subkon`, perapihan 47 event seeder, migration `2026-11-25-000001`, dan integrasi `/permohonan`.
- **Kebijakan `docs/MIGRATION_PROGRESS.md`:** Tidak diubah (mode tugas adalah `FEATURE`).

---

## 4. Ringkasan File Final
Sinkron 100% dengan `git status --short`:
- **Modified (`M`):**
  - `app/Config/Routes.php`
  - `app/Controllers/Api/Notifikasi/NotifPushController.php`
  - `app/Controllers/Api/Notifikasi/NotifikasiController.php`
  - `app/Database/Seeds/NotificationEventTypeSeeder.php`
  - `app/Enums/NotificationEvent.php`
  - `app/Libraries/ModuleManager/ModuleCatalog.php`
  - `app/Models/PushSubscriptionModel.php`
  - `app/Repositories/Notifikasi/NotificationRepository.php`
  - `app/Services/Bpb/BpbService.php`
  - `app/Services/Keuangan/Cashout/CashoutSubkonService.php`
  - `app/Services/Keuangan/DanaJaminanService.php`
  - `app/Services/Keuangan/DireksiService.php`
  - `app/Services/Keuangan/PenagihanService.php`
  - `app/Services/Legal/LegalService.php`
  - `app/Services/Notifikasi/NotificationDispatchService.php`
  - `app/Services/Notifikasi/NotifikasiService.php`
  - `app/Views/template/generate_menu.php`
  - `docs/modul-notifikasi.md`
  - `docs/modules/Notifikasi/MODULE.md`
  - `public/assets/css/style.css`
  - `public/assets/js/scripts.js`
  - `public/sw.js`
  - `tests/database/DeviceManagementServiceTest.php`
  - `writable/modules/deadcode_Notifikasi.json`
- **Untracked / New (`??`):**
  - `app/Controllers/Api/Permohonan/PermohonanController.php` (`app/Controllers/Api/Permohonan/`)
  - `app/Controllers/Web/Permohonan/PermohonanController.php` (`app/Controllers/Web/Permohonan/`)
  - `app/Database/Migrations/2026-11-25-000001_UpgradePushSubscriptionsAndNotificationTaxonomy.php`
  - `app/Repositories/Permohonan/PermohonanRepository.php` (`app/Repositories/Permohonan/`)
  - `app/Services/Permohonan/PermohonanService.php` (`app/Services/Permohonan/`)
  - `app/Support/NotificationTaxonomy.php`
  - `app/Views/permohonan/index.php` (`app/Views/permohonan/`)
  - `docs/2026-11-24-realtime-notifikasi-dan-pusat-permohonan.md`
  - `docs/modules/Permohonan/MODULE.md` (`docs/modules/Permohonan/`)
  - `public/assets/css/permohonan.css`
  - `public/assets/js/permohonan.js`
