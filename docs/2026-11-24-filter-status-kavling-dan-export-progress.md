# 2026-11-24 — Log Progres & Debugging: Pembaruan Filter `#filter_status_kavling`, Export Sesuai Filter, & Progress Bar Export

- **Tanggal Mulai:** 2026-11-24
- **Modul Terkait:** `MKDT` (`PosisiKonsumen` / `list-kavling`) & `Keuangan` (`PrintService`)
- **Mode Tugas:** `FEATURE`
- **Status Keseluruhan:** `COMPLETED` (DONE — Lulus QA Independen & Sinkronisasi Docs)

---

## 1. Tujuan & Keputusan yang Disetujui User
1. Filter `#filter_status_kavling` pada `app/Views/kavling/list-kavling.php` & `PosisiKonsumenRepository` mendukung 5 status:
   - `semua` (`Semua Status`): semua konsumen `Booking` + `Akad` (`status_mkdt IN ('Booking', 'Akad')`, tanpa kavling kosong).
   - `aktif` (`Kavling Aktif`, default selection): `COALESCE(mkdt.akad, 0) = 0` AND `COALESCE(mkdt.akad_indent, 0) = 0`; kolom `TGL AKAD` (index 9) disembunyikan saat `aktif` dipilih (`filterVal !== 'aktif'`).
   - `indent` (`Akad Indent`): `COALESCE(mkdt.akad, 0) = 0` AND `COALESCE(mkdt.akad_indent, 0) = 1`.
   - `akad` (`Akad AJB`): `COALESCE(mkdt.akad, 0) = 1`.
   - `ajb_indent` (`Akad AJB & Indent`): `(akad = 1 AND akad_indent = 1) OR (akad = 0 AND akad_indent = 1)` (ekuivalen `COALESCE(mkdt.akad_indent, 0) = 1`).
   - Backward compatibility API: `'booking'` pada `normalizeStatusKavling()` dipetakan ke `'aktif'`.
2. Export Excel/PDF (`exportPoskon`) mengikuti seluruh filter aktif (`status_kavling`, `id_proyek`, `id_cluster`, `id_jalan`, `sp3k`, `wawancara`, `bulan_field`, `bulan`, `tahun`, `search`) dengan guard `0` baris pada `PrintService::exportPoskonXlsx` dan `app/Views/pdf/poskon.php`.
3. Progress bar saat export menggunakan real-time server polling lewat file cache (`WRITEPATH . 'cache/export_progress_<token>.json'`) + endpoint `GET api/mkdt/posisi-konsumen/export/progress/{token}` dan modal Bootstrap 4 (`#2057a3`).

---

## 2. Pelacak Progres per Fase

| Fase | Lingkup | Agen | Status | Catatan Ringkas |
|---|---|---|---|---|
| **Fase A** | Inisialisasi Work Doc + Backend Filter Logic (`normalizeStatusKavling`, `applyStatusKavlingFilter`, `meta`) & Unit Test | `Orchestrator` + `sigapp-engineer` | `DONE` | 5 aturan filter, alias `'booking' => 'aktif'`, `meta.status_kavling_options`, & PHPUnit 4 tests / 31 assertions PASS |
| **Fase B** | Backend Export Sync (`PrintService` + `Api\PosisiKonsumenController`) & Endpoint Progress Tracking | `sigapp-engineer` | `DONE` | Export sinkron dengan filter aktif, guard `0` baris, endpoint progress JSON & PHPUnit 8 tests / 48 assertions PASS |
| **Fase C** | Frontend View `#filter_status_kavling`, Visibilitas `TGL AKAD`, Export Filter Payload & Modal Progress Bar | `sigapp-frontend` | `DONE` | 5 opsi dropdown, visibilitas kolom `TGL AKAD`, modal progress bar `#2057a3`, `poskon-list-kavling.css` (`filemtime`), `php -l` & `git diff --check` bersih |
| **Fase D1** | Inspeksi QA Independen (`codegraph`, Playwright E2E, `$sigapp-local-testing`, Export & Progress Bar) | `sigapp-qa` | `DONE` | Sertifikat QA `PASS` (8/8 PHPUnit, 0 broken callers, Playwright 5 filter + export PDF/XLSX + progress modal + guard 0 baris lulus) |
| **Fase D2** | Sinkronisasi Dokumentasi Modul (`MODULE.md`) & Finalisasi Work Doc | `sigapp-docs` | `DONE` | `ModuleCatalog.php` & `module:sync` (`MKDT`, `Keuangan`), pembaruan `MODULE.md`, finalisasi Work Doc, `MIGRATION_PROGRESS.md` & `modul-notifikasi.md` tidak diubah |

---

## 3. Log Progres & Debugging

### [2026-11-24 10:41] Fase 1 — Eksplorasi & Plan Disetujui (`sigapp-explorer`)
- **Temuan & Blast Radius:**
  - Fitur `/list-kavling` berada di bawah modul `MKDT` (`docs/modules/MKDT/MODULE.md`, status `REFACTORED` 100/100).
  - `PosisiKonsumenRepository::getBaseQuery($status)` memiliki callers di `PosisiKonsumenRepository::getRawDataTables()`, `PosisiKonsumenService::getDataTable()`, serta `PrintService::exportPoskonPdf()` dan `PrintService::exportPoskonXlsx()`.
  - Caller `/export/poskon/{tipe}/{status}` selain `list-kavling.php` adalah `list-kavling-akad.php` (`akad`) dan `list-kavling-batal.php` (`batal`), sehingga parameter filter baru pada `PrintService` dibuat opsional untuk menjaga kompatibilitas.
  - Ditemukan celah `Undefined array key 0` pada `PrintService::exportPoskonXlsx` (L680) dan `app/Views/pdf/poskon.php` (L59) jika hasil filter export `0` baris, serta raw `header(...)` attachment pada `exportPoskonXlsx` yang bentrok dengan JSON response.
- **Keputusan User:** Memilih opsi `semua` untuk gabungan konsumen `Booking + Akad` tanpa kavling kosong, menyembunyikan `TGL AKAD` saat `kavling aktif` dipilih, menyinkronkan export dengan filter aktif, dan menambahkan progress bar saat export.

### [2026-11-24 10:45] Fase A — Backend Filter Logic & Unit Test (`sigapp-engineer`)
- **Perubahan:**
  - `app/Repositories/Mkdt/PosisiKonsumenRepository.php`: update `normalizeStatusKavling()` (whitelist 5 nilai + alias `'booking' => 'aktif'`), `applyStatusKavlingFilter()` (5 kondisi SQL pada `mkdt`), ubah `applyBaseFilters()` & `applyBaseSearch()` menjadi `public`, serta jaga kompatibilitas `getRawDataTables()`.
  - `app/Services/Mkdt/PosisiKonsumenService.php`: update `getIndexData()` (`meta.status_kavling_options`) & `getDataTable()`.
  - `tests/unit/PosisiKonsumenFilterTest.php`: tambah 4 skenario uji unit filter & SQL WHERE.
- **Debugging / Temuan:** Tidak ada kendala; jalur `/list-kavling/akad/ambil` dan parameter legacy tetap kompatibel.
- **Verifikasi:** `php -l` bersih pada 3 file; `vendor\bin\phpunit tests/unit/PosisiKonsumenFilterTest.php` lulus (`Tests: 4, Assertions: 31`).

### [2026-11-24 10:53] Fase B — Backend Export Sync & Progress Endpoint (`sigapp-engineer`)
- **Perubahan:**
  - `app/Config/Routes.php`: tambah rute `GET api/mkdt/posisi-konsumen/export/progress/(:segment)` ke `Api\Mkdt\PosisiKonsumenController::exportProgress/$1`.
  - `app/Controllers/Api/Mkdt/PosisiKonsumenController.php`: update `exportPoskon()` membaca filter penuh + `export_token`, tambah `exportProgress()`.
  - `app/Services/Mkdt/PosisiKonsumenService.php`: update `exportPoskon()` (meneruskan `$filters` & `$progressToken`), tambah `getExportProgress()` & atomic cache progress di `WRITEPATH . 'cache/export_progress_<token>.json'`.
  - `app/Services/Keuangan/PrintService.php`: update `exportPoskonPdf()` & `exportPoskonXlsx()` memakai `getBaseQuery(null)` + `applyBaseFilters()` + `applyBaseSearch()`, catat progress per batch baris, hapus raw `header(...)` attachment bentrok JSON, & guard `0` baris.
  - `app/Views/pdf/poskon.php`: guard `$poskon` kosong agar tidak crash `Undefined array key 0`.
  - `tests/unit/PosisiKonsumenFilterTest.php`: tambah 4 skenario uji progress & empty guard.
- **Debugging / Temuan:** Dihindari PHP Session untuk progress tracking agar polling request tidak terblokir *session lock*.
- **Verifikasi:** `php -l` bersih pada 6 file; `php spark routes` menampilkan rute progress; `vendor\bin\phpunit tests/unit/PosisiKonsumenFilterTest.php` lulus (`Tests: 8, Assertions: 48`); `git diff --check -- app/ tests/` bersih.

### [2026-11-24 10:56] Fase C — Frontend View, Export Payload & Modal Progress Bar (`sigapp-frontend`)
- **Perubahan:**
  - `app/Views/kavling/list-kavling.php`: perbarui `#filter_status_kavling` ke 5 opsi (`semua`, `aktif` [default], `indent`, `akad`, `ajb_indent`), visibilitas kolom 9 (`TGL AKAD`) disinkronkan pada inisialisasi DataTable dan `applyPoskonFilters()` (`filterVal !== 'aktif'`), HTML escaping pada chip filter aktif, tambah modal `#modal_export_progress` (BS4 + `#2057a3`), dan perbarui `export_file()` untuk mengirim semua filter aktif + `export_token`, polling endpoint progress per 350ms, disable tombol selama export, nama file download dinamis, & `load_riwayat()`.
  - `public/assets/css/poskon-list-kavling.css`: stylesheet eksternal baru (scoped ke `#modal_export_progress`, dimuat dengan `filemtime`).
- **Debugging / Temuan:** Penutupan `#modal_export_progress` menangani transisi modal Bootstrap 4 (`closeExportProgressModal`) agar tidak bertabrakan dengan SweetAlert2 saat export selesai.
- **Verifikasi:** `php -l app/Views/kavling/list-kavling.php` bersih; `git diff --check -- app/Views/kavling/list-kavling.php public/assets/` bersih.

### [2026-11-24 11:07] Fase D1 — Inspeksi QA Independen (`sigapp-qa`)
- **Hasil QA:** Sertifikat **`PASS`** (departemen MKDT `citra` pada `http://localhost/sigapp.dev/public/list-kavling`).
- **Verifikasi Codegraph & CLI:** 0 broken callers; `php -l` bersih di seluruh file; `vendor\bin\phpunit tests/unit/PosisiKonsumenFilterTest.php` lulus (`8/8 tests, 48 assertions`); `php spark routes` memverifikasi rute `GET api/mkdt/posisi-konsumen/export/progress/([^/]+)`.
- **Verifikasi Playwright E2E & UI (`sigapp-ui-acuan`):**
  - Default `aktif` (28 baris, kolom `TGL AKAD` tersembunyi), `indent` (25 baris), `akad` (173 baris), `ajb_indent` (30 baris), `semua` (226 baris), reset `#btn_filter_clear_all`, dan viewport mobile (`375x812`) semuanya berfungsi dengan 0 console/network errors.
  - Export XLSX & PDF memunculkan `#modal_export_progress`, polling `GET api/mkdt/posisi-konsumen/export/progress/{token}` `200 OK`, guard 0 baris (`search="__QA_ZERO_MATCH__"`) `200 OK`, dan regresi `/list-kavling/akad` (210 baris) serta `/list-kavling/batal` (10 baris) `200 OK`.
  - `public/assets/css/poskon-list-kavling.css` termuat `200 OK` (`filemtime`), scoped ke `#modal_export_progress`, dan sesuai `sigapp-ui-acuan`.

### [2026-11-24 11:11] Fase D2 — Sinkronisasi Dokumentasi & Finalisasi (`sigapp-docs`) — DONE
- **Katalog & Sinkronisasi Otomatis:**
  - `app/Libraries/ModuleManager/ModuleCatalog.php`: Menambahkan `'assets_css' => ['laporan-booking-akad.css', 'poskon-list-kavling.css']` pada blok modul `MKDT`.
  - Menjalankan `php spark module:sync MKDT` (`[SYNCED]`) dan `php spark module:sync Keuangan` (`[SYNCED]`), serta memverifikasi `--check-only` (`[OK]`) pada keduanya.
- **Pembaruan Dokumentasi Modul (`MODULE.md`):**
  - `docs/modules/MKDT/MODULE.md`: Memperbarui Contracts Public (rute baru `GET /api/mkdt/posisi-konsumen/export/progress/(:segment)` & 14 public methods `PosisiKonsumenService`), File Inventory (`Api\Mkdt\PosisiKonsumenController` 7 methods, `PosisiKonsumenService` 14 methods, `PosisiKonsumenRepository` 18 methods), `## Key Functions`, `## Deprecated & Legacy Shims` (penghapusan opsi UI `'booking'` + alias internal `'booking' => 'aktif'`), `## Notes`, serta menambahkan entri teratas `## Change Log` (`2026-11-24 [FEATURE]`).
  - `docs/modules/Keuangan/MODULE.md`: Memperbarui signature `PrintService` di Contracts/Inventory, `## Key Functions` (`PrintService::exportPoskonPdf()`, `exportPoskonXlsx()`, `buildPoskonExportRows()`, `resolvePoskonProjectName()`, `reportRowIterationProgress()`), dan menambahkan entri teratas `## Change Log` (`2026-11-24 [FEATURE]`).
- **Dokumen Terkecuali (Sesuai Aturan Mode `FEATURE`):**
  - `docs/MIGRATION_PROGRESS.md` tidak diubah (bukan mode `REFACTOR`).
  - `docs/modul-notifikasi.md` tidak diubah (tidak menambah/mengubah notifikasi).

---

## 4. Ringkasan File Final
Selaras dengan `git status --short` untuk lingkup pekerjaan ini:
- **Modified (`M`):**
  - `app/Config/Routes.php` — Menambahkan rute `GET api/mkdt/posisi-konsumen/export/progress/(:segment)` -> `PosisiKonsumenController::exportProgress/$1`.
  - `app/Controllers/Api/Mkdt/PosisiKonsumenController.php` — Memperbarui `exportPoskon()` (filter penuh + `export_token`) dan menambahkan `exportProgress()`.
  - `app/Repositories/Mkdt/PosisiKonsumenRepository.php` — Memperbarui `normalizeStatusKavling()`, `applyStatusKavlingFilter()` (5 status + alias `'booking' => 'aktif'`), serta menjadikan `applyBaseFilters()` & `applyBaseSearch()` `public`.
  - `app/Services/Mkdt/PosisiKonsumenService.php` — Memperbarui `getIndexData()` (5 `status_kavling_options`), `exportPoskon()`, serta menambahkan `sanitizeExportToken()`, `getExportProgress()`, dan `writeExportProgress()`.
  - `app/Services/Keuangan/PrintService.php` — Sinkronisasi export PDF/XLSX dengan filter penuh (`buildPoskonExportRows()`), progress batch per baris (`reportRowIterationProgress()`), resolusi judul proyek (`resolvePoskonProjectName()`), penghapusan raw `header(...)` bentrok JSON, dan guard `0` baris.
  - `app/Views/kavling/list-kavling.php` — Pembaruan 5 opsi `#filter_status_kavling`, visibilitas kolom `TGL AKAD`, HTML escaping chip filter, `#modal_export_progress`, dan `export_file()` dengan polling progress 350ms.
  - `app/Views/pdf/poskon.php` — Guard array `$poskon` kosong untuk hasil export `0` baris.
  - `app/Libraries/ModuleManager/ModuleCatalog.php` — Registrasi `assets_css` (`laporan-booking-akad.css`, `poskon-list-kavling.css`) pada modul `MKDT`.
  - `docs/modules/MKDT/MODULE.md` — Sinkronisasi `module:sync MKDT`, `Key Functions`, `Deprecated`, `Notes`, dan `Change Log`.
  - `docs/modules/Keuangan/MODULE.md` — Sinkronisasi `module:sync Keuangan`, `Key Functions` (`PrintService`), dan `Change Log`.
- **Untracked / New (`??`):**
  - `public/assets/css/poskon-list-kavling.css` — Stylesheet scoped `#modal_export_progress`.
  - `tests/unit/PosisiKonsumenFilterTest.php` — Unit test PHPUnit (8 tests, 48 assertions).
  - `docs/2026-11-24-filter-status-kavling-dan-export-progress.md` — Dokumen log progres & finalisasi pekerjaan ini.
- *(Catatan `git status --short`: terdapat file dirty pra-eksesi di luar lingkup tugas ini yang tidak disentuh: `M app/Services/Settings/SiteplanMenuService.php`, `M docs/modules/Settings/MODULE.md`, `M docs/modules/Siteplan/MODULE.md`, `M public/assets/js/siteplan/master.js`, `?? docs/2026-11-24-siteplan-target-akad-indent.md`).*
