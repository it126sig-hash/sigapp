# 2026-11-21 — Log Progres & Debugging: Perbaikan Badge Daftar NUP & Revamp Request Kavling

- **Tanggal Mulai:** 2026-11-21
- **Modul Terkait:** `Siteplan`, `Transaksi` (Request Kavling & Kavling), `Proyek` (Tipe & Jalan — read-only integration)
- **Mode Tugas:** `FEATURE` + `BUGFIX`
- **Status Keseluruhan:** `COMPLETED` (`DONE` — Lulus QA Independen & Sinkronisasi Docs)

---

## 1. Keputusan Bisnis yang Disetujui User

1. **Akses Role:** Diizinkan untuk **Admin (`group_id = 1`)**, **MKDT (`group_id = 4`)**, dan **Planning (`group_id = 6`)**.
   - Form input (Request Tambah Kavling & Request Ubah Kavling) & context menu kanvas: **MKDT (`4`)** & **Admin (`1`)**.
   - Tabel detail & aksi proses (`Selesai`, `Tolak`, `Ubah Kavling`): **Planning (`6`)** & **Admin (`1`)**.
   - Tab `Riwayat`: **MKDT (`4`)**, **Planning (`6`)**, & **Admin (`1`)**.
   - Payload batch legacy `items[]` dari Target Siteplan (`/api/kavling-request/submit`): diizinkan untuk `['1', '4', '6']`.
2. **Filter Kavling pada Request Ubah Kavling:**
   - Menggunakan opsi default: semua kavling di proyek dapat dipilih **kecuali** yang diblokir.
   - Select2 wajib menampilkan **informasi konsumen aktif** jika kavling sudah memiliki konsumen (misal nama konsumen & status transaksi).
   - **Blokir / Kecualikan** kavling dengan transaksi MKDT aktif (`COALESCE(mkdt.is_batal, 0) = 0 AND COALESCE(mkdt.status_mkdt, '') != 'Batal'`) yang berstatus **Akad** (`mkdt.status_mkdt = 'Akad'` atau `COALESCE(mkdt.akad, 0) = 1`) **atau** `akad_indent = 1` — diterapkan di query list, validasi backend (submit & apply), serta frontend (kanvas & context menu).
3. **Aksi "Ubah Kavling" oleh Planning:**
   - Mengikuti `SiteplanService::editKavling()`: hanya meng-update `kavling.id_tipe` (tanpa mengubah `harga_akhir`/transaksi `mkdt`), mencatat audit ke `HistoryService`, menyimpan `applied_id_tipe` + `catatan_planning` opsional, dan otomatis mengubah status request menjadi `approved` (`Selesai`), lalu frontend menjalankan `load_kavling()`.
4. **Target Siteplan & Notifikasi:**
   - Kompatibilitas payload `items[]` dari `target.js` dipertahankan, `TargetSiteplanRepository::getLatestPlanningChangeRequests()` diperbarui agar membaca tabel relasi baru, dan `NotifikasiService` mengirim notifikasi balik `NotificationEvent::REQUEST_KAVLING` ke pengaju / Group MKDT (`4`) saat Planning menandai Selesai / Tolak / Ubah Kavling.

---

## 2. Pelacak Progres per Fase

| Fase | Lingkup | Agen | Status | Catatan Ringkas |
|---|---|---|---|---|
| **Fase A** | Dokumen log, Fix CSS Badge Daftar NUP, Migrasi DB & Truncate `kavling_request` | Orchestrator + `sigapp-engineer` + `sigapp-frontend` | `DONE` | Migrasi DB selesai (`php spark migrate`); CSS kontras `.badge.badge-light` & `.badge-nup-count` di `public/assets/css/style.css` selesai. |
| **Fase B** | Backend Repo, Model, Service, Controller, Routes, TargetSiteplanRepo, KavlingRepo | `sigapp-engineer` | `DONE` | 8 file backend selesai & terverifikasi (`php -l`, `php spark routes`, `TargetSiteplanServiceTest` 13/13 OK, query MySQL proyek 11 filter Akad/Akad Indent terverifikasi). |
| **Fase C** | Frontend Modal 3-Tab, Preview 3D/Denah/Spek, Canvas Picker Konva, Sub-Modal Ubah Kavling | `sigapp-frontend` | `DONE` | CSS ter-scope `siteplan-request-kavling.css`, modal 3-tab terpadu, Konva canvas picker, preview 3D/Denah/Spek, sub-modal Ubah Kavling, & guard RBAC `[1, 4, 6]` selesai (`php -l`, `node --check`, `git diff --check` lulus). |
| **Fase D** | QA Independen (`sigapp-qa`: Playwright E2E + `$sigapp-local-testing` + `codegraph`) & Docs (`sigapp-docs`) | `sigapp-qa` + `sigapp-docs` | `DONE` | Fase D1 QA Independen `PASS`; Fase D2 sinkronisasi `ModuleCatalog.php`, `module:sync Transaksi` & `Siteplan`, `modul-notifikasi.md`, dan dokumen log selesai. |

---

## 3. Log Progres & Debugging

### [2026-11-21 07:53] Fase 1 — Eksplorasi & RCA Awal (`sigapp-explorer`)
- **RCA Badge Daftar NUP:** Pada `app/Views/siteplan/master.php:1301`, `<span class="badge badge-light badge-nup-count">` mewarisi `color: #fff` dari `public/app-assets/css/bootstrap-extended.css:1637-1638` (dimuat setelah `bootstrap.min.css`), menimpa warna gelap `.badge-light` karena specificity sama (`0,1,0`). `public/assets/css/style.css` belum menimpa `.badge.badge-light`.
- **Blast Radius Request Kavling:** Teridentifikasi pemanggil lintas modul pada `TargetSiteplanRepository::getLatestPlanningChangeRequests()` dan `public/assets/js/siteplan/target.js` (`submitPlanningKavlingChangeRequest`).
- **Endpoint Reuse:** `/api/tipe/all?only_active=1&format=raw` sudah menyediakan spek teknis & Signed URL `FileAccessService` (`gambar_tipe_access_url`, `gambar_denah_access_url`, `gambar_kerja_access_url`); `/api/jalan/all` sudah mendukung `id_proyek` + `format=raw`.

### [2026-11-21 08:06] Fase A (DB) & Fase B — Backend CI4 (`sigapp-engineer`)
- **Migrasi DB:** `app/Database/Migrations/2026-11-21-000001_RevampKavlingRequestTables.php` dijalankan via `php spark migrate`:
  - `kavling_request` di-truncate; kolom baru `applied_id_tipe`, `catatan_planning`, `processed_by`, `processed_at` ditambahkan.
  - Tabel anak `kavling_request_jalan` dan `kavling_request_kavling` berhasil dibuat beserta indeksnya.
- **Implementasi Backend:**
  - Dibuat `app/Repositories/Transaksi/KavlingRequestRepository.php` (catatan Fase 4: perlu didaftarkan di `ModuleCatalog.php`).
  - Diperbarui `KavlingRequestModel.php`, `KavlingRepository::getKavlingListForRequest()` (filter blokir aktif `Akad` / `akad = 1` / `akad_indent = 1` + info konsumen), `TargetSiteplanRepository::getLatestPlanningChangeRequests()` (support `kavling_request_kavling` + fallback legacy), `KavlingRequestService.php` (multi-jalan `tambah_baru`, multi-kavling `ubah_tipe`, wajib `keterangan`, `id_tipe` opsional, validasi blokir Akad/Akad Indent, `FileAccessService` URLs untuk 3D/denah/gambar kerja, notifikasi balik ke pengaju/MKDT saat `approved`/`rejected`/`applyUbahTipeKavling`, transaksi DB + `HistoryService::log` khusus `kavling.id_tipe`), `KavlingRequestController.php` (RBAC `[1, 4, 6]` sesuai peran), dan `app/Config/Routes.php` (`POST api/kavling-request/apply-ubah-kavling`).
- **Verifikasi Awal Backend:**
  - `php -l` bersih di seluruh 8 file backend.
  - `php spark routes` memetakan 5 rute `api/kavling-request/*`.
  - `vendor/bin/phpunit --no-coverage tests/unit/TargetSiteplanServiceTest.php`: `OK (13 tests, 186 assertions)`.
  - Uji query MySQL proyek 11: dari 424 kavling (58 Akad/Akad Indent), `getKavlingListForRequest` mengembalikan 366 baris (0 kavling terblokir lolos).

### [2026-11-21 08:24] Fase A (CSS Badge NUP) & Fase C — Frontend & UI (`sigapp-frontend`)
- **Fix Badge Daftar NUP (`public/assets/css/style.css`):** Menambahkan override specificity tinggi untuk `.badge.badge-light`, `.btn-primary .badge.badge-light.badge-nup-count`, dan `.dark-layout .badge.badge-light` (`#2057a3` / gelap pada latar putih).
- **Modal 3-Tab & Role Guard (`master.php`, `request_kavling_modal.php`, `siteplan-request-kavling.css`, `request-kavling.js`):**
  - Membatasi visibilitas fitur hanya untuk role `[1, 4, 6]` (`Admin`, `MKDT`, `Planning`); context menu & form input hanya `[1, 4]`; tabel & aksi tindak lanjut untuk `[1, 6]`; tab Riwayat untuk `[1, 4, 6]`.
  - Menghapus duplikasi tombol sidebar & pemanggilan partial `modal_list_request_kavling.php` (alias `openModalListRequestKavling()` tetap dipertahankan ke `#modal-request-kavling`).
  - Mengimplementasikan Select2 multi-jalan (`/api/jalan/all?format=raw`), Select2 multi-kavling (`/api/kavling-request/kavling-list`), Konva canvas picker (`#rk-canvas-picker-bar` + layer `masked`), preview 3D/Denah/Spek (`*_access_url` dari `FileAccessService`) + lightbox zoom, validasi wajib keterangan, sub-modal `#modal-apply-ubah-kavling` (+ link `Tambah Tipe` ke `/tipe` tab baru & tombol `Refresh Tipe`), serta hook minimal di `master.js` & `planning.js`.
- **Verifikasi Awal Frontend:**
  - `php -l` bersih pada `master.php` & `request_kavling_modal.php`.
  - `node --check` lulus pada `request-kavling.js`, `master.js`, `planning.js`.
  - `git diff --check` exit code `0`.

### [2026-11-21 08:57] Fase D1 — QA Independen & E2E (`sigapp-qa`) — `PASS`
- **Codegraph & Grep Callers:** `0` broken callers across `KavlingRequestService`, `KavlingRequestController`, `TargetSiteplanRepository::getLatestPlanningChangeRequests`, `openModalRequestKavling`, `openModalListRequestKavling` (shim), `handleContextRequestKavling`, `target.js`, and `DashboardService`. `modal_list_request_kavling.php` terkonfirmasi tidak lagi dipanggil di view manapun.
- **Playwright E2E (`http://localhost/sigapp.dev`, proyek `BKI (Dummy Data)` `id_proyek=12`):**
  - **MKDT (`citra`):** Badge Daftar NUP terbaca jelas (`rgb(32, 87, 163)` pada `rgb(255, 255, 255)`). Modal 3-tab + mobile `390x844` tab 1 baris tanpa overlap bottom nav. Right-click & canvas picker memblokir Akad (`kav132`), memilih 2 kavling eligible (`kav130` & `kav131`) beserta warning konsumen aktif, preview 3D/Denah (`/public/files/gambar_kerja/*`) + lightbox + spek teknis termuat, validasi keterangan wajib berjalan, submit multi-jalan dengan & tanpa tipe berhasil (`0` console errors).
  - **Planning (`purnama`):** Form input & context menu tidak ada di DOM (hanya tabel detail). Tombol preview 3D/Denah/Spek & lightbox bekerja (`z-index` bertumpuk rapi). Aksi **Tolak** wajib catatan (`200 OK`), **Selesai** + catatan (`200 OK`), serta **Ubah Kavling** (`#modal-apply-ubah-kavling` + link `/tipe` `target="_blank"` + Refresh Tipe -> `apply-ubah-kavling` `200 OK`) memperbarui `kavling.id_tipe` (`65`), menjaga `harga_akhir` & `id_mkdt` utuh, meng-update kanvas Konva, memindahkan request ke **Riwayat** (`Selesai` + notes Planning), serta menulis `history_log` dan 3 notifikasi balik `request_kavling` ke MKDT.
  - **Keuangan (`nisalis`):** Negatif RBAC lulus — tombol/menu/modal/asset absen dari DOM & ke-5 API `/api/kavling-request/*` mengembalikan `403 Forbidden`.
- **CLI Checks:** `php spark routes` (5 rute `api/kavling-request/*` OK), `TargetSiteplanServiceTest` (`OK 13 tests, 186 assertions`), `php spark module:deadcode Siteplan` (`0` kandidat), `php spark module:deadcode Transaksi` (`0` kandidat baru).

### [2026-11-21 09:04] Fase D2 — Sinkronisasi Dokumentasi & Katalog Modul (`sigapp-docs`) — `DONE`
- **Module Catalog & Auto-Sync:**
  - Memperbarui `app/Libraries/ModuleManager/ModuleCatalog.php` untuk mendaftarkan `Transaksi/KavlingRequestRepository.php` dan tabel pemilik baru (`kavling_request_jalan`, `kavling_request_kavling`) pada modul `Transaksi`, serta `siteplan-request-kavling.css` (`assets_css`) dan `shared_tables` (`kavling_request`, `kavling_request_kavling`) pada modul `Siteplan`.
  - Menjalankan `php spark module:sync Transaksi` dan `php spark module:sync Siteplan` (`REFACTORED 100/100`).
- **Dokumentasi Modul & Notifikasi:**
  - Memperbarui `docs/modules/Transaksi/MODULE.md` dan `docs/modules/Siteplan/MODULE.md` (Database Tables, Public HTTP & PHP Service Contracts, File Inventory termasuk CSS, Key Functions, Deprecated & Legacy Shims, Cross-Module Callers, & Change Log `[2026-11-21] [FEATURE + BUGFIX]`).
  - Memperbarui `docs/modul-notifikasi.md` dengan seksi `## Event Request Kavling (NotificationEvent::REQUEST_KAVLING)` mencakup trigger, audience (`[6]` saat submit; `forUsers([$createdBy])` / fallback `[4]` saat feedback Planning), template pesan, dan `action_url`.
  - Memfinalisasi `docs/2026-11-21-revamp-request-kavling-dan-badge-nup.md` menjadi `COMPLETED` / `DONE` (tanpa mengubah `docs/MIGRATION_PROGRESS.md`).

---

## 4. Ringkasan File Final

- **Database Migration:** `app/Database/Migrations/2026-11-21-000001_RevampKavlingRequestTables.php`
- **Routes & Config:** `app/Config/Routes.php`, `app/Libraries/ModuleManager/ModuleCatalog.php`
- **Backend (`Transaksi` & `Siteplan`):**
  - `app/Controllers/Api/Transaksi/KavlingRequestController.php`
  - `app/Services/Transaksi/KavlingRequestService.php`
  - `app/Repositories/Transaksi/KavlingRequestRepository.php`
  - `app/Repositories/Transaksi/KavlingRepository.php`
  - `app/Repositories/Siteplan/TargetSiteplanRepository.php`
  - `app/Models/KavlingRequestModel.php`
- **Frontend (`Siteplan` & Global CSS):**
  - `public/assets/css/style.css`
  - `public/assets/css/siteplan-request-kavling.css`
  - `app/Views/siteplan/master.php`
  - `app/Views/siteplan/request_kavling_modal.php`
  - `public/assets/js/siteplan/request-kavling.js`
  - `public/assets/js/siteplan/master.js`
  - `public/assets/js/siteplan/planning.js`
- **Dokumentasi:**
  - `docs/modules/Transaksi/MODULE.md`
  - `docs/modules/Siteplan/MODULE.md`
  - `docs/modul-notifikasi.md`
  - `docs/2026-11-21-revamp-request-kavling-dan-badge-nup.md`



