# 2026-11-21 — Log Progres & Debugging: Tombol Batal (Satuan & Batch) Request Kavling

- **Tanggal Mulai:** 2026-11-21
- **Modul Terkait:** `Transaksi` (`kavling_request`), `Siteplan` (Modal & JS Request Kavling), `Notifikasi`
- **Mode Tugas:** `FEATURE`
- **Status Keseluruhan:** `IN_PROGRESS` (Fase A & B dimulai)

---

## 1. Keputusan Bisnis yang Disetujui User

1. **Cakupan Jenis Request:** Tombol Batal (satuan & batch) diaktifkan untuk **kedua jenis** (`ubah_tipe` DAN `tambah_baru`) yang masih berstatus `pending`.
2. **Lokasi UI Batal (Single & Batch):**
   - Tersedia di **2 tempat yang tersinkronisasi**:
     1. Kartu daftar pending di bagian bawah **Tab `Request Tambah Kavling`** dan **Tab `Request Ubah Kavling`** (khusus MKDT `group_id = 4` agar Admin `group_id = 1` tidak mendapat tabel ganda di bawah antrean Planning), lengkap dengan checkbox multi-pilih, toolbar **"Batalkan Terpilih (N)"**, dan tombol **"Batal"** satuan.
     2. **Tab `Riwayat`** (khusus MKDT `4` & Admin `1`; Planning `6` tidak melihat kontrol batal): dilengkapi filter pill **`Dibatalkan`**, kolom checkbox untuk baris `can_cancel === true`, toolbar **"Batalkan Terpilih (N)"**, tombol **"Batal"** satuan di kolom Aksi, serta rincian pembatalan di kolom catatan.
3. **Kepemilikan Pembatalan:**
   - **MKDT (`4`):** hanya boleh membatalkan request `pending` yang dibuat oleh dirinya sendiri (`created_by === user_id`).
   - **Admin (`1`):** boleh membatalkan seluruh request `pending` di proyek.
   - **Planning (`6`):** tidak memiliki akses batal (`403` pada `/api/kavling-request/cancel`).
4. **Alasan Pembatalan & Notifikasi:**
   - Konfirmasi SweetAlert2 dengan input **Alasan Pembatalan (opsional)**.
   - Setelah pembatalan berhasil, kirim 1 notifikasi ringkas `NotificationEvent::REQUEST_KAVLING` ke **Group Planning (`6`)** melalui `NotifikasiService`.

---

## 2. Pelacak Progres per Fase

| Fase | Lingkup | Agen | Status | Catatan Ringkas |
|---|---|---|---|---|
| **Fase A** | Dokumen log & Migrasi DB (`ENUM` + `'cancelled'` & `alasan_batal`) | Orchestrator + `sigapp-engineer` | `DONE` | Migrasi `2026-11-21-000002_AddCancelledStatusToKavlingRequest` sukses (Batch `53`); `status` ENUM & kolom `alasan_batal` terverifikasi di MySQL. |
| **Fase B** | Backend Model, Repo, Service (`cancelRequests`), Controller (`cancel`), & Route | `sigapp-engineer` | `DONE` | Backend & unit test selesai (`php -l`, `php spark routes`, `TargetSiteplanServiceTest` OK 16 tests / 306 assertions). |
| **Fase C** | Frontend UI Batal Satuan & Batch di Tab Tambah, Tab Ubah, & Tab Riwayat | `sigapp-frontend` | `DONE` | View, CSS, & JS batal satuan/batch selesai (`php -l`, `node --check`, `git diff --check` lulus). |
| **Fase D** | QA Independen (`sigapp-qa`: Playwright E2E + `$sigapp-local-testing` + `codegraph`) & Docs (`sigapp-docs`) | `sigapp-qa` + `sigapp-docs` | `IN_PROGRESS` | Memulai Fase D1: Audit & Playwright E2E oleh `sigapp-qa`. |

---

## 3. Log Progres & Debugging

### [2026-11-21 18:04] Inisialisasi Eksekusi
- Plan telah disetujui dengan 4 keputusan final. Memulai Fase A (migrasi DB) & Fase B (Backend API `POST api/kavling-request/cancel`).

### [2026-11-21 18:12] Fase A & Fase B — Migrasi DB & Backend CI4 (`sigapp-engineer`)
- **Migrasi DB:** `app/Database/Migrations/2026-11-21-000002_AddCancelledStatusToKavlingRequest.php` dijalankan via `php spark migrate` (Batch `53`); `kavling_request.status` menjadi `ENUM('pending','approved','rejected','cancelled')` dan kolom `alasan_batal` ditambahkan.
- **Implementasi Backend:**
  - `KavlingRequestModel.php`: menambah `alasan_batal` ke `$allowedFields`.
  - `KavlingRequestRepository.php`: mendukung filter `cancelled` & `history` (`approved`, `rejected`, `cancelled`), `getStatusCounts()` (`cancelled`), `findRequestsByIdsAndProyek()`, dan `markRequestsCancelled()`.
  - `KavlingRequestService.php`: menambah `cancelRequests()` dalam DB transaction + notifikasi Group `6` (Planning) via `NotifikasiService`, serta guard `updateStatus()` agar request `cancelled` tidak dapat diproses lagi.
  - `KavlingRequestController.php` & `app/Config/Routes.php`: rute baru `POST api/kavling-request/cancel` (RBAC `['1', '4']`, dukung `id_request` single & `id_requests[]` batch, wajib `created_by === user_id` untuk MKDT non-Admin), serta injeksi `can_cancel` per item & `meta.permissions.can_cancel` pada `list()`.
- **Verifikasi Backend:** `php -l` bersih di 7 file PHP, `php spark routes` memetakan `api/kavling-request/cancel`, dan `vendor/bin/phpunit --no-coverage tests/unit/TargetSiteplanServiceTest.php` lulus (`OK (16 tests, 306 assertions)`).

### [2026-11-21 18:19] Fase C — Frontend & UI (`sigapp-frontend`)
- **`app/Views/siteplan/request_kavling_modal.php`:** Menambahkan kartu pending MKDT murni (`#rk-mkdt-tambah-card` & `#rk-mkdt-ubah-card`) dengan checkbox batch, batch toolbar (`#rk-mkdt-*-batch-bar`), dan tombol Batal satuan di Tab Tambah & Tab Ubah; serta filter pill `Dibatalkan` (`data-status="cancelled"`), kolom checkbox, kolom Aksi Batal, dan toolbar `#rk-riwayat-batch-bar` di Tab Riwayat (disembunyikan untuk Planning murni).
- **`public/assets/css/siteplan-request-kavling.css`:** Menambahkan style ter-scope `#modal-request-kavling` untuk `.rk-status-badge.is-cancelled`, `.rk-note-box.is-cancelled`, `.rk-batch-toolbar`, `.rk-check-col`, `.rk-row-checkbox`, varian `.dark-layout`, dan responsif mobile.
- **`public/assets/js/siteplan/request-kavling.js`:** Mengimplementasikan `confirmAndCancelRequests()`, sinkronisasi seleksi per konteks (`selectedCancelIds.tambah/ubah/riwayat`), anti-stale pruning, loader MKDT pending (`loadMkdtPendingList`), pembaruan rendering status `Dibatalkan` & info pembatal/`alasan_batal`, serta refresh counters/badges.
- **Verifikasi Frontend:** `php -l`, `node --check`, dan `git diff --check` lulus bersih.

### [2026-11-21 19:50] Fase D1 — QA Independen & E2E (`sigapp-qa`) — `PASS`
- **Catatan Interupsi & Lanjutan:** Run `sigapp-qa` pertama sempat terhenti karena `429 RESOURCE_EXHAUSTED` tepat setelah skenario MKDT desktop selesai; QA dilanjutkan ulang secara menyeluruh (`sigapp-qa` kedua) hingga tuntas dengan Sertifikat **`PASS`**.
- **Codegraph & Grep Callers:** `0` broken callers pada `cancelRequests`, `markRequestsCancelled`, `findRequestsByIdsAndProyek`, `KavlingRequestController::cancel`, `api/kavling-request/cancel`, `confirmAndCancelRequests`, `alasan_batal`, dan `.is-cancelled`.
- **Playwright E2E (`http://localhost/sigapp.dev`, proyek `BKI (Dummy Data)` `id_proyek=12`):**
  - **MKDT (`citra`):** Submit `#60`, `#61` (`ubah_tipe`) → uji Select All & `Batal Pilih` → batal satuan `#60` (+ `"QA Batal Satuan #60"`) → pelepasan kunci pending kavling `1005` terverifikasi (resubmit `#62` berhasil) → submit `tambah_baru` (`#63`) → uji mobile `390x844` (`.nav-pills` 1 baris, toolbar batch stack rapi, `0` horizontal overflow modal/dokumen) → **Batal Batch** campuran (`#61`, `#62`, `#63`) dari Tab Riwayat → filter `Dibatalkan` menampilkan seluruh item beserta `.rk-status-badge.is-cancelled` dan `.rk-note-box.is-cancelled`, serta dukungan `.dark-layout` terverifikasi.
  - **Planning (`purnama`):** Antrean pending Tab Tambah & Tab Ubah bersih (`0` baris), Tab Riwayat menampilkan item `Dibatalkan` beserta `alasan_batal` **tanpa** batch toolbar/checkbox/kolom Aksi/tombol Batal, negatif RBAC `POST /api/kavling-request/cancel` → HTTP `403`, dan verifikasi DB read-only mengonfirmasi status `cancelled` di `kavling_request` serta notifikasi `request_kavling` (`#3828`, `#3831`) terkirim ke Group `6` (`notification_recipients`: `purnama`, `rachma`, `rani`).
  - **Keuangan (`nisalis`):** Tombol & modal absen dari DOM Siteplan dan `POST /api/kavling-request/cancel` mengembalikan HTTP `403`.
- **CLI & UI Checks:** `php spark routes` (`POST api/kavling-request/cancel` OK), `TargetSiteplanServiceTest` (`OK (16 tests, 306 assertions)`), `php spark module:deadcode Siteplan` (`0` kandidat), `php spark module:deadcode Transaksi` (`0` kandidat pada `KavlingRequest*`), `php -l` bersih di 8 file PHP, `git diff --check -- app/ public/ tests/` bersih, dan `sigapp-ui-acuan` terpenuhi (`0` error modul di console).



