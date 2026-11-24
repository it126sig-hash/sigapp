# 2026-11-21 — Log Progres & Debugging: Bugfix Luas Tanah Tidak Berubah di Cetak SPPTB Setelah Pindah Kavling

- **Tanggal Mulai:** 2026-11-21
- **Modul Terkait:** `Transaksi`, `MKDT`, `Keuangan`
- **Mode Tugas:** `BUGFIX`
- **Status Keseluruhan:** `IN_PROGRESS`

---

## 1. Tujuan & Keputusan yang Disetujui User
1. **Perbaikan Query-Level (Tanpa Migration DB):** Solusi diterapkan 100% di level query `SELECT` agar langsung aman dan konsisten baik di DB local maupun DB live/production tanpa schema change maupun backfill massal.
2. **Aturan Prioritas Resolusi `luas_tanah` (`TransaksiRepository::getSpptbData()` & `NupRepository::getKavlingOptionsForTransfer()`):**
   - Jika `kavling.status_tanah = 'Kelebihan Tanah'` dan `kavling.luas_tanah > 0` → gunakan `kavling.luas_tanah`.
   - Selain itu (`Standar` atau `kavling.luas_tanah` kosong/`0`) → prioritaskan `hargajual.lt`, lalu fallback `tipe.lt`, lalu fallback `kavling.luas_tanah`.
   - Pindahkan `mkdt.*` ke awal `SELECT` dengan `$escape = false` untuk mencegah *column shadowing*.
3. **Penguatan UX Modal Pindah Kavling & View SPPTB:**
   - Null-safe rendering `luas_tanah` di `app/Views/pdf/spptb-new.php:482`.
   - Guard klik `#btn-print_spptb` pada `public/assets/js/siteplan/mkdt.js` saat `SIGAPP_NUP.pendingTransferContext` aktif (sebelum Simpan Pindah Kavling) agar tidak memicu HTTP 500.
   - Tampilkan badge `LT <luas_tanah> m²` (dan penanda `KT`/`Kelebihan Tanah`) pada dropdown Select2 `#select-target-kavling-nup` di `public/assets/js/siteplan/nup.js`.
4. **Di Luar Scope Bugfix:** Kolom `Luas Bangunan` (`spptb-new.php:460`, `$data->lb`) dan mutasi `kavling.luas_tanah` saat Ubah Tipe (`KavlingRequestService::applyUbahTipeKavling()`) tidak diubah.

---

## 2. Pelacak Progres per Fase

| Fase | Lingkup | Agen | Status | Catatan Ringkas |
|---|---|---|---|---|
| **Fase A** | Inisialisasi dokumen kerja + unit test reproduksi `luas_tanah` | `Orchestrator` + `sigapp-engineer` | `DONE` | Work doc diinisialisasi; unit test reproduksi `TransaksiRepositorySpptbLuasTanahTest` dibuat (`php -l` bersih; 11/11 assertion gagal tepat pada bug pre-fix) |
| **Fase B** | Perbaikan Backend (`getSpptbData()` & `getKavlingOptionsForTransfer()`) | `sigapp-engineer` | `DONE` | `getSpptbData()` & `getKavlingOptionsForTransfer()` diperbarui (`php -l` bersih; PHPUnit 12/12 + 19/19 regresi PASS; verifikasi DB lokal PASS: `60`, `60`, `72`, `107`) |
| **Fase C** | Perbaikan Frontend & UX Guard (`spptb-new.php`, `mkdt.js`, `nup.js`) | `sigapp-frontend` | `DONE` | Null-safe `spptb-new.php:482`, guard `#btn-print_spptb` saat pending transfer, clear `pendingTransferContext` saat modal tutup, & badge LT Select2 (`php -l`, `node --check`, `git diff --check` bersih) |
| **Fase D1** | Inspeksi QA Independen (Codegraph, Playwright E2E, PHPUnit, CLI) | `sigapp-qa` | `IN_PROGRESS` | Terblokir sementara (`429 RESOURCE_EXHAUSTED` pada subagent `sigapp-qa`): Codegraph, grep, `php -l`, `node --check`, `git diff --check`, `php spark routes`, & PHPUnit (`13 tests, 103 assertions`) lulus; Playwright E2E berhenti setelah navigasi awal `http://localhost/sigapp.dev` |
| **Fase D2** | Sinkronisasi `MODULE.md` & Finalisasi Dokumen Kerja | `sigapp-docs` | `TODO` | Terblokir sementara (`429 RESOURCE_EXHAUSTED` pada subagent `sigapp-docs` sebelum melakukan perubahan file); menunggu penyelesaian Fase D1 |

---

## 3. Log Progres & Debugging

### [2026-11-21 15:17] Fase 1 — Eksplorasi & Plan Disetujui (`sigapp-explorer` + Master Orchestrator)
- **Temuan & Blast Radius:**
  - `TransaksiRepository::getSpptbData()` (`app/Repositories/Transaksi/TransaksiRepository.php:122-186`) hanya memilih `kavling.luas_tanah` mentah, padahal kolom tersebut hanya diisi saat input/edit Planning (`#f_luas`) dan tidak ikut diperbarui oleh `KavlingRequestService::applyUbahTipeKavling()` maupun `HargaJualService::setHargaKavling()`.
  - Verifikasi DB lokal menunjukkan 129 dari 865 kavling `Standar` tidak sinkron dengan `tipe.lt` (contoh kasus Pindah Kavling `id_mkdt = 373` ke `DAHAYU 4 No. 2` tipe `22/60`, `hargajual.lt = 60`, `tipe.lt = 60`, namun `kavling.luas_tanah = 72`), sementara seluruh 80 kavling `Kelebihan Tanah` memang menyimpan luas fisik aktual pada `kavling.luas_tanah`.
  - Kolom `mkdt.*` juga diseleksi setelah field kavling/tipe (`L142`) sehingga berisiko shadowing jika skema berubah; pengecekan fisik DB memastikan `mkdt` saat ini belum memiliki kolom `luas_tanah`/`lt`/`lb`.
  - Callers `getSpptbData()` terpetakan via `codegraph_explore` (5 callers) dan `getKavlingOptionsForTransfer()` (1 caller) dengan blast radius rendah.
- **Keputusan User:** Plan disetujui tanpa migration DB (query-level fix).

### [2026-11-21 18:23] Fase A — Unit Test Reproduksi Resolusi `luas_tanah` (`Orchestrator` + `sigapp-engineer`)
- **Perubahan:** Inisialisasi work doc `docs/2026-11-21-bugfix-luas-tanah-spptb-pindah-kavling.md` serta pembuatan file unit test terisolasi `tests/unit/TransaksiRepositorySpptbLuasTanahTest.php` (menggunakan SQLite3 `:memory:` mencakup klausa `SELECT`, urutan `mkdt.*`, 5 skenario resolusi `getSpptbData()`, dan 5 skenario `NupRepository::getKavlingOptionsForTransfer()`).
- **Debugging / Temuan:** Tidak ada perubahan ke file produksi pada fase ini.
- **Verifikasi:**
  - `php -l tests/unit/TransaksiRepositorySpptbLuasTanahTest.php`: `No syntax errors detected`.
  - `php vendor/bin/phpunit tests/unit/TransaksiRepositorySpptbLuasTanahTest.php`: Exit code `1` (`Tests: 11, Assertions: 42, Failures: 11`) — gagal tepat pada assertion reproduksi bug (`72.0` vs `60.0`, `0.0` vs `60.0`, missing `status_tanah`, dan missing `status_tanah`/`luas_tanah` pada `NupRepository`).

### [2026-11-21 18:27] Fase B — Perbaikan Backend (`sigapp-engineer`)
- **Perubahan:**
  - `app/Repositories/Transaksi/TransaksiRepository.php` (`getSpptbData()` L122–195): memindahkan `mkdt.*` ke awal klausa `SELECT` dengan `$escape = false`, menambahkan `` `kavling`.`id_kavling` `` dan `` `kavling`.`status_tanah` `` secara eksplisit setelah `mkdt.*`, mengganti ``kavling.luas_tanah`` dengan ekspresi `CASE WHEN kavling.status_tanah = 'Kelebihan Tanah' AND COALESCE(kavling.luas_tanah, 0) > 0 THEN kavling.luas_tanah ELSE COALESCE(NULLIF(hargajual.lt, 0), NULLIF(tipe.lt, 0), NULLIF(kavling.luas_tanah, 0), 0) END AS luas_tanah`, serta menghapus trailing comma & duplikat `konsumen.tel_instansi`.
  - `app/Repositories/Mkdt/NupRepository.php` (`getKavlingOptionsForTransfer()` L204–351): menyeleksi `k.status_tanah`, `k.luas_tanah`, `hj.lt AS hj_lt`, menghitung `$effectiveLuasTanah` dengan aturan prioritas identik, dan mengembalikan field `status_tanah` & `luas_tanah` (angka bersih `int|float`) secara additive pada Full JSON response `POST /api/nup/kavling-options`.
  - `tests/unit/TransaksiRepositorySpptbLuasTanahTest.php`: penyesuaian minor regex anti-auto-escaping agar backtick identifier kolom tidak false-positive (seluruh 11 skenario tetap utuh).
- **Debugging / Temuan:** Regex awal pada test menganggap spasi sebelum backtick kolom (``WHEN `kavling` ``) sebagai auto-escaping keyword `WHEN`; disesuaikan agar memeriksa backtick menempel pada keyword SQL.
- **Verifikasi:**
  - `php -l` pada 3 file backend/test: `No syntax errors detected`.
  - `php vendor/bin/phpunit --no-coverage tests/unit/TransaksiRepositorySpptbLuasTanahTest.php tests/unit/PrintServiceSpptbHistoryTest.php`: `OK (12 tests, 93 assertions)`.
  - Regresi suite (`SpptbTemplateContentTest.php`, `ConsumerReplacementServiceTest.php`): `OK (19 tests, 132 assertions)`.
  - Verifikasi read-only DB lokal: `getSpptbData(859, 373)` -> `60`, `getSpptbData(861, 460)` -> `60`, `getSpptbData(264, 401)` -> `72`, `getSpptbData(11, 444)` (`Kelebihan Tanah`) -> `107`; hasil `NupRepository::getKavlingOptionsForTransfer()` sinkron 100% (`60`, `60`, `72`, `107`).

### [2026-11-21 18:33] Fase C — Perbaikan Frontend & UX Guard (`sigapp-frontend`)
- **Perubahan:**
  - `app/Views/pdf/spptb-new.php` (baris 482): render `$data->luas_tanah` dibuat null-safe (`isset` + `!== ''` + `esc()`, fallback `'-'`) tanpa mengubah layout F4.
  - `public/assets/js/siteplan/mkdt.js` (`bindKavlingContext()` L577–600): menambahkan pengecekan `hasPendingTransfer` (`to_id_kavling === state.id_kavling` && `id_mkdt === state.id_mkdt`) untuk menahan klik `#btn-print_spptb` dengan peringatan ramah `swal('warning', ...)` sebelum perubahan Pindah Kavling disimpan.
  - `public/assets/js/siteplan/nup.js` (L166–170, L630–754): membersihkan `pendingTransferContext = null` pada event `hidden.bs.modal` `#modal-isi_data_konsumen`, menambahkan helper `NUP.formatKavlingLtInfo(item)`, dan menampilkan badge `LT <luas> m² [· KT]` pada Select2 `#select-target-kavling-nup`.
- **Debugging / Temuan:** `git diff --check` global melaporkan trailing whitespace pre-existing di `docs/modules/Siteplan/MODULE.md:5` (bukan dari perubahan tugas ini); `git diff --check` pada 3 file Fase C bersih 100%.
- **Verifikasi:**
  - `php -l app/Views/pdf/spptb-new.php`: `No syntax errors detected`.
  - `node --check public/assets/js/siteplan/mkdt.js` & `public/assets/js/siteplan/nup.js`: exit code `0`.
  - `git diff --check -- app/Views/pdf/spptb-new.php public/assets/js/siteplan/mkdt.js public/assets/js/siteplan/nup.js`: exit code `0`.
  - Tag `<script>` di `mkdt.php:2406`, `master.php:2180`, dan `list-kavling.php:1746` terverifikasi sudah memakai `filemtime(...)`.

### [2026-11-21 18:36] Fase D1 — Inspeksi QA Independen (`sigapp-qa` — Terhenti Parsial oleh Quota `429`)
- **Status Sertifikat QA:** `PENDING` *(terblokir error eksternal `RESOURCE_EXHAUSTED (code 429)` pada subagent `sigapp-qa` sebelum menyelesaikan alur login & uji UI/PDF di Playwright)*
- **Pemeriksaan yang Sudah Lulus Dijalankan `sigapp-qa`:**
  - **Audit Simbol & Callers (`codegraph_explore` + `git grep`):** 0 *broken callers* pada `TransaksiRepository::getSpptbData()` (5 callers), `NupRepository::getKavlingOptionsForTransfer()` (1 caller), serta asset JS/View terkait.
  - **PHPUnit:** `php vendor/bin/phpunit --no-coverage tests/unit/TransaksiRepositorySpptbLuasTanahTest.php tests/unit/PrintServiceSpptbHistoryTest.php tests/unit/SpptbTemplateContentTest.php` -> `OK (13 tests, 103 assertions)`.
  - **Lint & Diff Check:** `php -l` (4 file PHP), `node --check` (`mkdt.js`, `nup.js`), dan `git diff --check` pada ke-6 file target -> exit code `0`.
  - **Rute CLI (`php spark routes`):** Rute `GET print/spptb` dan `POST api/nup/kavling-options` terkonfirmasi terdaftar dan aktif.
  - **Playwright Navigasi Awal:** `http://localhost/sigapp.dev` terbuka normal (`SIGAPP`, 0 console errors), kemudian subagent terhenti karena kuota model (`RESOURCE_EXHAUSTED 429`).
- **Sisa Pemeriksaan Fase D1 yang Belum Selesai:** Login akun MKDT (`citra`), uji interaktif modal Pindah Kavling + Select2 badge `LT`, guard `#btn-print_spptb`, dan verifikasi PDF `/print/spptb` di browser.

### [2026-11-21 18:36] Fase D2 — Sinkronisasi Dokumentasi (`sigapp-docs` — Terblokir Quota `429`)
- **Temuan:** Subagent `sigapp-docs` baru membaca dokumen referensi & memeriksa `git diff`, lalu terhenti oleh `RESOURCE_EXHAUSTED (code 429)` sebelum mengubah `MODULE.md` maupun work doc ini (karena Orkestrator juga tidak diizinkan menyelesaikan `Fase D2` sebelum `Fase D1` QA berstatus `PASS`).

---

## 4. Ringkasan File Final
- *(Diisi & diverifikasi sinkron dengan `git status --short` oleh `sigapp-docs` pada Fase D2)*
