# 2026-11-24 — Log Progres & Debugging: Redesign Compact Table List Kavling

- **Tanggal Mulai:** 2026-11-24
- **Modul Terkait:** `MKDT`
- **Mode Tugas:** `UI_REPORT`
- **Status Keseluruhan:** `COMPLETED` (`DONE — Lulus QA Independen Fase F1 & Sinkronisasi Docs`)

---

## 1. Tujuan & Keputusan yang Disetujui User
1. Redesign tabel utama `#data_tables` pada `app/Views/kavling/list-kavling.php` agar jauh lebih **compact**, terutama pada header 3-baris (`<thead>`) dan padding/tinggi tiap baris (`<tbody>`).
2. **Opsi A (Disetujui):** Sel `NAMA KONSUMEN` + kode QR referal tetap 2 baris super-rapat tanpa pemotongan elipsis dan tanpa mengubah backend (`PosisiKonsumenService.php`).
3. **Cakupan Tabel:** Fokus khusus pada tabel utama `#data_tables` (Tab *List Posisi Konsumen Aktif*) agar perubahan terisolasi (*minimum necessary diff*).
4. **Tone Warna & Struktur Kolom:** Tetap mempertahankan palet warna light SIGAPP/Vuexy existing (`#2057a3`, surface terang, plus fallback `.dark-layout`), seluruh **30 kolom (`0..29`)**, dan struktur header 3-baris.
5. **Pure Frontend / CSS Scoped (Tahap 1):** Seluruh aturan tabel ditempatkan di `public/assets/css/poskon-list-kavling.css` di bawah root scope `.poskon-page`.
6. **Penyempurnaan Lanjutan (Disetujui 2026-11-24 13:48):**
   - **Global Header Opt-In (`.sigapp-table-compact`):** Menambahkan utility class opt-in `.sigapp-table-compact` pada `public/assets/css/style.css` (header compact + border tipis `1px` + phantom-gap guard `scrollX`/`FixedColumns` + `.dark-layout`) tanpa memengaruhi tabel/laporan matriks eksisting.
   - **Border Header Tipis `1px`:** Mereset `border-bottom-width: 2px` (`bootstrap.css`) pada `border-collapse: separate` agar tidak menumpuk `2px–3px`.
   - **Perataan Kiri (`BLOK` [2], `NAMA KONSUMEN` [5], `KETERANGAN STATUS` [29]):** Rata kiri pada sel `tbody` maupun header leaf `thead`, sementara `NO` kavling (`col 3`) tetap rata tengah.
   - **Kolom Tanggal Seragam & Cukup (`130px` `border-box`):** `TGL BOOKING` [7], `TGL WAWANCARA` [8], `TGL AKAD` [9], `TERBIT` [13], dan `EXPIRED` [14] disamakan `130px` (`box-sizing: border-box`).
   - **Tombol Aksi Titik 3 (`⋮`):** Mengubah trigger dropdown `"Aksi"` di `PosisiKonsumenService::renderPoskonActionHtml()` menjadi `<i class="fa fa-ellipsis-v">` + `.poskon-action-menu-btn` (menyeragamkan `list-kavling.php` & `list-kavling-akad.php`), dengan `.poskon-action-menu-btn.dropdown-toggle::after { display: none; }` di `style.css`.

---

## 2. Pelacak Progres per Fase

| Fase | Lingkup | Agen | Status | Catatan Ringkas |
|---|---|---|---|---|
| **Fase A** | Inisialisasi Work Doc, root scope `.poskon-page`, & migrasi rule inline `#data_tables` ke CSS eksternal | `Orchestrator` + `sigapp-frontend` | `DONE` | Root class `.poskon-page` terpasang; rule inline tabel dimigrasi ke `poskon-list-kavling.css`; `php -l` & `git diff --check` bersih |
| **Fase B** | Compact 3-row header (visible only), compact body rows, inner-cell density, DataTables chrome, dark mode, & mobile | `sigapp-frontend` | `DONE` | Compact 3-row header, body rows, inner-cell density, sorting arrows, chrome, dark-layout, & mobile selesai; `php -l` & `git diff --check` bersih |
| **Fase C1** | Inspeksi QA Independen (Codegraph callers, Playwright E2E Desktop & Mobile, & audit `sigapp-ui-acuan`) | `sigapp-qa` | `DONE` | Putaran 2 `PASS` — alignment `<= 0.08px`, header `59px`, row sync `<= 0.19px`, sorting aktif, mobile search bersih, 0 console errors |
| **Fase C2** | Sinkronisasi `docs/modules/MKDT/MODULE.md` & finalisasi Work Doc | `sigapp-docs` | `DONE` | `php spark module:sync MKDT` OK; `MODULE.md` & Work Doc disinkronkan; `MIGRATION_PROGRESS.md` & `modul-notifikasi.md` tidak diubah (`UI_REPORT`) |
| **Fase D** | Pembaruan tombol aksi titik 3 (`fa-ellipsis-v`) di `PosisiKonsumenService` | `Orchestrator` + `sigapp-engineer` | `DONE` | `renderPoskonActionHtml()` di `PosisiKonsumenService.php:256` diubah ke `fa-ellipsis-v` + `.poskon-action-menu-btn`; `php -l` & `git diff --check` bersih |
| **Fase E1** | Pembuatan style header global opt-in `.sigapp-table-compact` & helper `.poskon-action-menu-btn` di `style.css` | `sigapp-frontend` | `DONE` | Utility global opt-in `.sigapp-table-compact` (border `1px`, phantom-gap guard, sorting, `.dark-layout`) & `.poskon-action-menu-btn` ditambahkan di `style.css:1974–2252`; `git diff --check` bersih |
| **Fase E2** | Penerapan `.sigapp-table-compact`, rata kiri (`2, 5, 29`), kolom tanggal `130px`, alignment titik 3, & cleanup dead JS | `sigapp-frontend` | `DONE` | `.sigapp-table-compact` dipasang; `columnDefs` `[2,5]` (`poskon-col-left`) & `[7,8,9,13,14]` (`poskon-col-date` `130px` `border-box`); `.poskon-col-blok` `120px`; dead JS dibersihkan; `php -l` & `git diff --check` bersih |
| **Fase F1** | QA Independen (`/list-kavling`, `/list-kavling/akad`, `/laporan/booking-akad`, & `codegraph_explore`) | `sigapp-qa` | `DONE` | `PASS` — border header `1px` (`54px` total), rata kiri (`2,5,29`), kolom tanggal seragam `130px`, titik 3 tanpa caret ganda, `.dark-layout`, mobile, & regresi `/list-kavling/akad` + `/laporan/booking-akad` lulus 0 console error |
| **Fase F2** | Sinkronisasi `docs/modules/MKDT/MODULE.md` & finalisasi Work Doc | `sigapp-docs` | `DONE` | `php spark module:sync MKDT` OK; `MODULE.md` & Work Doc disinkronkan; `MIGRATION_PROGRESS.md` & `modul-notifikasi.md` tidak diubah (`UI_REPORT`) |

---

## 3. Log Progres & Debugging

### [2026-11-24 11:27] Fase 1 — Eksplorasi & Plan Disetujui (`sigapp-explorer`)
- **Temuan & Blast Radius:**
  - `app/Views/kavling/list-kavling.php` dinaungi modul `MKDT` (`docs/modules/MKDT/MODULE.md`, Status `REFACTORED` 100/100).
  - `public/assets/css/poskon-list-kavling.css` hanya dimuat oleh `list-kavling.php:10` (`?v=<?= filemtime(...) ?>`); `list-kavling-akad.php` dan `list-kavling-batal.php` tidak memuatnya → blast radius lintas halaman = 0.
  - Akar masalah kepadatan: (1) `style.css` hanya mengecilkan padding `tbody` (`4px 6px !important`), sehingga `thead th` tetap mewarisi `0.72rem 2rem` dari `bootstrap-extended.css` ditambah `padding-right: 30px` pada kolom sortable (`7`, `9`); (2) elemen dalam `<td>` (`poskon-action-cell` `.btn.btn-sm`, `span.btn.btn-sm` TUNAI/KPR, `.badge`, `.mt-25`, serta trailing `<br>` di LPA/Listrik) mendorong tinggi baris; (3) kloning DataTables `scrollX` dan FixedColumns membuang `id="data_tables"`, dan hidden sizing `thead` di `.dataTables_scrollBody`/`.DTFC_LeftBodyWrapper` rentan menciptakan phantom gap bila `thead th` diberi padding secara global.

### [2026-11-24 12:27] Fase A — Root Scoping View & Migrasi Rule Tabel Inline (`sigapp-frontend`)
- **Perubahan:**
  - `app/Views/kavling/list-kavling.php`: menambahkan class root `.poskon-page` pada `<div class="app-content content poskon-page">` dan memindahkan seluruh rule inline `#data_tables`, `.poskon-datatable-card`, dan `.poskon-action-cell` (mempertahankan `#riwayat_export` mobile media query).
  - `public/assets/css/poskon-list-kavling.css`: menambahkan section scoped `.poskon-page` yang mencakup master table, `.dataTables_scrollHead`, `.dataTables_scrollBody`, `.DTFC_LeftHeadWrapper`, dan `.DTFC_LeftBodyWrapper` tanpa mengubah style `#modal_export_progress`.
- **Debugging / Temuan:** Tidak ada.
- **Verifikasi:** `php -l app/Views/kavling/list-kavling.php` bersih (0 error); `git diff --check` exit code 0.

### [2026-11-24 12:33] Fase B — Implementasi Compact Table & FixedColumns Sync (`sigapp-frontend`)
- **Perubahan:**
  - `public/assets/css/poskon-list-kavling.css` (L135–609): menerapkan styling compact scoped `.poskon-page` meliputi:
    - Opaque surface & margin reset untuk `.DTFC_ScrollWrapper`, `.DTFC_LeftHeadWrapper`, `.DTFC_LeftBodyWrapper`, `.dtfc-fixed-left`.
    - Visible 3-row compact header (`2px 6px` parent rows, `3px 6px` leaf headers, `10px`, `line-height: 1.15`, tone light `#f3f4f8`), proteksi hidden sizing `thead` (`height: 0`, `padding-top/bottom: 0`, tanpa merusak tabel ukur sementara `columns.adjust()`), dan posisi panah sorting presisi (`#2057a3` aktif, bebas tumpang-tindih SVG chevron).
    - Compact body rows (`padding: 2px 6px`, `font-size: 10.5px`, `line-height: 1.2`) + pemadatan inner-cell (`.poskon-action-cell .btn-sm`, pill `TUNAI`/`KPR`, `.badge`, `.mt-25`, `.poskon-referral` Opsi A, `i.fa-check + br { display: none }`, `.poskon-keterangan-status` 260px).
    - Compact DataTables chrome, fallback `.dark-layout`, dan penyesuaian tap target mobile `@media (max-width: 767.98px)`.
  - `app/Views/kavling/list-kavling.php`: memperbarui `applyPoskonTableHeight()` (tambah `columns.adjust()` + trigger `document.fonts.ready`), menyelaraskan `columnDefs` target 29 (`width: '260px'`), dan menyesuaikan `#tb-BLOK` ke `120px`.
- **Debugging / Temuan:** Ditemukan potensi tumpang-tindih SVG chevron bawaan `components.css` pada panah sorting dan kebutuhan mengecualikan tabel ukur sementara `table:not(#data_tables)` dari `font-size: 0` hidden header; keduanya langsung ditangani pada CSS scoped.
- **Verifikasi:** `php -l app/Views/kavling/list-kavling.php` bersih (exit 0); `git diff --check` bersih (exit 0).

### [2026-11-24 12:42] Fase C1 (Putaran 1) — Inspeksi QA Independen (`sigapp-qa`: `FAIL`)
- **Hasil Uji Lulus:**
  - `codegraph_explore` & grep: 0 broken callers, `list-kavling-akad.php` & `list-kavling-batal.php` bebas regresi.
  - Console errors di `/list-kavling`: `0`; asset `poskon-list-kavling.css?v=1795498350` HTTP `200`; tinggi visible 3-row header `59px` (selisih `0px`); hidden sizing `thead` `0px` (0 phantom gap); sinkronisasi tinggi baris `tbody` `25/25` baris `maxDiff = 0.38px`; 30 kolom utuh; `#modal_detail`, dropdown `Aksi`, pencarian, switch tab, & filter `Semua Status` berjalan normal.
- **Temuan Kegagalan (`FAIL` — Domain Frontend/UI):**
  1. **[CRITICAL] Misalignment Lebar Kolom Header vs Body + Clipping `NAMA KONSUMEN`:**
     - Gejala: `AKSI` header `42.34px` vs body `90.36px` (`selisih 48px`), `TGL BOOKING` header `88.31px` vs body `42.73px`, `TGL WAWANCARA` header `112.91px` vs body `41.05px` (tanggal patah multibaris), `DTFC_LeftHeadWrapper` `413.30px` vs `DTFC_LeftBodyWrapper` `425.89px` (nama konsumen panjang terpotong & bocor ke `SALES`).
     - RCA: `poskon-list-kavling.css:144-147` memaksa `#data_tables` dan `.dataTables_scrollBody #data_tables` menjadi `width: 100% !important` sehingga menimpa lebar terhitung DataTables, serta `L195-205` memberi `font-size: 0 !important` pada `.dataTables_scrollBody #data_tables thead th` sehingga lebar min-content header tersembunyi di `scrollBody` hilang.
  2. **[HIGH] Panah Sorting `TGL BOOKING` (col 7) & `TGL AKAD` (col 9) Nonaktif (`sorting_disabled`):**
     - RCA: Pada `app/Views/kavling/list-kavling.php:1161-1168`, `{ targets: '_all', orderable: false }` ditempatkan sebelum `{ targets: [7, 9], orderable: true }`, sehingga prioritas DataTables menonaktifkan sorting pada kolom 7 & 9.
  3. **[MEDIUM] Mobile Search Input Overflow Kartu (`375x812`):**
     - Gejala: `#data_tables_filter input` melampaui tepi kanan `.poskon-datatable-card` sebesar `17.02px`.
     - RCA: `dataTables.bootstrap4.min.css` menetapkan `white-space: nowrap` pada `.dataTables_filter label`, belum di-reset pada `@media (max-width: 767.98px)` di `poskon-list-kavling.css:590-599`.
- **Tindak Lanjut:** Masuk **Fase 3B** (Bugfix Plan + Surgical Fix oleh `sigapp-frontend`).

### [2026-11-24 12:50] Fase 3B — Surgical Bugfix Alignment, Sorting, & Mobile Search (`sigapp-frontend`)
- **Perubahan:**
  - `public/assets/css/poskon-list-kavling.css`:
    - Menghapus `width: 100% !important` pada `#data_tables` dan menggantinya dengan `width: auto !important; min-width: 100% !important;` pada `.dataTables_scrollBody #data_tables` dan tabel ukur sementara `table:not(#data_tables)` untuk menetralkan override global `components.css:3556`.
    - Menghapus `font-size: 0 !important;` pada hidden sizing `thead` di `scrollBody` & `DTFC_LeftBodyWrapper`, mempertahankan tipografi horizontal 100% identik dengan visible header sambil menjaga tinggi vertikal `0px` (termasuk `.dataTables_sizing`).
    - Membatasi rule mobile `font-size: .82rem` ke `tbody` saja dan memperbaiki `.dataTables_filter label` di `@media (max-width: 767.98px)` (`display: flex; flex-direction: column; white-space: normal; width: 100%;`) agar input search tidak overflow.
  - `app/Views/kavling/list-kavling.php`:
    - Menambahkan `syncPoskonFixedHeaderWidths()` setelah `fixedColumns().relayout()` di `applyPoskonTableHeight()` agar lebar `th` pada `.DTFC_LeftHeadWrapper` selalu sinkron 1:1 dengan `.dataTables_scrollHead` setelah AJAX load.
    - Memindahkan `{ targets: [7, 9], orderable: true }` sebelum `{ targets: '_all', orderable: false }` di `columnDefs` untuk mengaktifkan sorting `TGL BOOKING` & `TGL AKAD`.
- **Verifikasi:** `php -l app/Views/kavling/list-kavling.php` exit 0; `git diff --check` exit 0. Siap Re-QA Putaran 2.

### [2026-11-24 12:55] Fase C1 (Putaran 2) — Re-Inspeksi QA Independen (`sigapp-qa`: `PASS`)
- **Hasil Uji Playwright E2E & Codegraph:**
  - **Codegraph & Isolasi:** 0 broken callers; `list-kavling-akad.php` & `list-kavling-batal.php` terverifikasi bebas regresi; `poskon-list-kavling.css?v=1795499372` HTTP `200`; `0` console error pada `/list-kavling` (hanya temuan pre-existing 404 gambar kerja `113`/`114` di dalam `#modal_detail` & warning `icon-192.png` di manifest, di luar scope tabel).
  - **Alignment Header vs Body (`Desktop 1440×900`):** `maxScrollXDiff = 0.05px`, `maxScrollWDiff = 0.03px`; `DTFC_LeftHeadWrapper` = `463.64px` vs `DTFC_LeftBodyWrapper` = `463.64px` (`diff = 0px`); nama konsumen panjang tampil utuh tanpa bocor ke `SALES`; kolom tanggal (`TGL BOOKING`, `TGL WAWANCARA`, `TGL AKAD`) rapi 1 baris; status `Semua Status` `maxXDiff = 0.06px`, `maxWDiff = 0.06px`.
  - **Compact Density & Row Sync:** visible 3-row header = `59px` (selisih `0px`); hidden sizing `thead` = `0px` (0 phantom gap); sinkronisasi tinggi baris `tbody` `maxDiff = 0.19px`; `.DTFC_LeftBodyWrapper` opaque `rgb(255, 255, 255)`.
  - **Sorting Kolom:** `#tb-TGL_BOOKING` (`col 7`) & `#tb-TGL_AKAD` (`col 9`) aktif (`bSortable === true`), panah `↑↓` terpusat tanpa tumpang tindih SVG chevron, klik sort HTTP `200` dan panah aktif `#2057a3`.
  - **Mobile (`375×812`):** `mobileMaxXDiff = 0.08px`, `mobileMaxWDiff = 0.06px`; `searchOverflowPx = -21.39px` (input search rapi 2 baris dalam kartu); `leftColumns: 0`; `hasPageOverflow: false`; `minBtnHeight = 24px`.
  - **CLI & Kepatuhan UI:** `php spark module:status` (`MKDT` 100% `REFACTORED`), `php -l` bersih, `git diff --check` exit 0, `sigapp-ui-acuan` terpenuhi.
- **Keputusan QA:** Sertifikat **`PASS`** — lanjut ke **Fase C2 (Fase 4: `sigapp-docs`)**.

### [2026-11-24 12:58] Fase C2 (Fase 4) — Sinkronisasi Dokumentasi & Finalisasi (`sigapp-docs`) — `DONE`
- **Verifikasi Katalog & `module:sync`:**
  - `app/Libraries/ModuleManager/ModuleCatalog.php` (blok `MKDT`): folder view `kavling` (`list-kavling.php`) dan `assets_css` (`poskon-list-kavling.css`) sudah terdaftar tanpa perlu penambahan baru.
  - `php spark module:sync MKDT`: berhasil (`[SYNCED] MKDT -> docs\modules\MKDT\MODULE.md`, `Last Synced: 2026-11-24 12:57:24 @ 84f8fcb`, Status `REFACTORED`, Readiness Score `100/100`).
- **Pembaruan `docs/modules/MKDT/MODULE.md`:**
  - `## Key Functions` (`Frontend & Assets`): merekam root scope `poskon-page`, pemindahan rule inline `#data_tables` / `.poskon-action-cell` ke CSS eksternal, `syncPoskonFixedHeaderWidths()` pada `applyPoskonTableHeight()`, perbaikan urutan `columnDefs` DataTables (`[7, 9]` sebelum `_all`), target 29 `260px`, `#tb-BLOK` `120px`, serta rincian section `.poskon-page` di `public/assets/css/poskon-list-kavling.css`.
  - `## Deprecated & Legacy Shims`: mencatat pemindahan/penghapusan rule CSS inline `#data_tables`, `.poskon-datatable-card .card-body`, dan `.poskon-action-cell` dari `<style>` `list-kavling.php` (dengan selector JS pre-existing `#tb_nama_konsumen`, `#tb_tgl_booking`, dll. tetap dipertahankan).
  - `## Notes`: mencatat isolasi scoped CSS `.poskon-page` di `poskon-list-kavling.css` (bebas dampak ke `list-kavling-akad.php` dan `list-kavling-batal.php`).
  - `## Change Log`: menambahkan entri teratas `2026-11-24 [UI_REPORT] — Redesign Compact Table #data_tables pada List Posisi Konsumen (list-kavling)`.
- **Kepatuhan Lingkup Dokumen:**
  - `docs/MIGRATION_PROGRESS.md`: **tidak diubah** (mode `UI_REPORT`, bukan `REFACTOR` modul penuh).
  - `docs/modul-notifikasi.md`: **tidak diubah** (tidak ada penambahan/perubahan notifikasi).

### [2026-11-24 13:45] Fase 1 Lanjutan — Eksplorasi & Persetujuan Plan Penyempurnaan (`sigapp-explorer`)
- **Temuan & Blast Radius:**
  - Border header menumpuk `2px–3px` karena `.table-bordered thead th { border-bottom-width: 2px; }` (`bootstrap.css`) + `border-collapse: separate !important` (`dataTables.bootstrap4.min.css`).
  - Style header global aman dijadikan utility opt-in `.sigapp-table-compact` di `public/assets/css/style.css` dengan guard hidden sizing `thead`, spesifisitas lebih tinggi dari vendor CSS, dan `.dark-layout`.
  - Indeks kolom dinamis DataTables (toggle `visible(false)` pada col `9` & `15`) mengharuskan `columnDefs` `className` (`poskon-col-left` untuk `[2, 5]`, `poskon-col-date` `130px` `border-box` untuk `[7, 8, 9, 13, 14]`), bukan `nth-child`.
  - `PosisiKonsumenService::renderPoskonActionHtml()` (`L227–266`) dipakai oleh `getDataTable()` (`/list-kavling` & `/list-kavling/akad`) dan `getDataTablesBatal()`, dan FontAwesome proyek adalah v5.15.3 (`fa fa-ellipsis-v`). Plan lanjutan disetujui User pada `2026-11-24 13:48`.

### [2026-11-24 13:51] Fase D — Pembaruan Tombol Aksi Titik 3 di Backend (`sigapp-engineer`)
- **Perubahan:**
  - `app/Services/Mkdt/PosisiKonsumenService.php` (`L256`, `renderPoskonActionHtml()`): mengubah tombol dropdown `"Aksi"` menjadi `<button type="button" class="btn btn-outline-primary btn-sm dropdown-toggle poskon-action-menu-btn" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="Menu Aksi" aria-label="Menu Aksi"><i class="fa fa-ellipsis-v" aria-hidden="true"></i></button>`.
- **Verifikasi:** `php -l app/Services/Mkdt/PosisiKonsumenService.php` bersih (exit 0); `git diff --check` exit 0.

### [2026-11-24 13:55] Fase E1 — Global Header Opt-In `.sigapp-table-compact` & Action Helper (`sigapp-frontend`)
- **Perubahan:**
  - `public/assets/css/style.css` (`L1974–2252`): menambahkan `.poskon-action-menu-btn.dropdown-toggle::after { display: none; }` dan utility opt-in `table.dataTable.sigapp-table-compact` mencakup header compact (`2px 6px` parent, `3px 6px` leaf, `10px`, `#f3f4f8`), border tipis `1px` (`#e2e8f0`, bebas penumpukan `2px–3px`), sorting arrows `#2057a3`, guard hidden sizing `thead` (`.dataTables_scrollBody > table`, `.DTFC_LeftBodyWrapper`, `.DTFC_RightBodyWrapper`), konsistensi border body `1px`, serta fallback `.dark-layout`.
- **Verifikasi:** `git diff --check` bersih (exit 0); zero bare `thead th` selectors.

### [2026-11-24 13:59] Fase E2 — View & Module CSS `list-kavling` (`sigapp-frontend`)
- **Perubahan:**
  - `app/Views/kavling/list-kavling.php`: menambahkan class `sigapp-table-compact` pada `#data_tables` (`L457`), menyelaraskan `renderPoskonActionCell()` (`L884`) ke tombol titik 3 `.poskon-action-menu-btn` (`fa-ellipsis-v`), memperbarui `columnDefs` (`L1185–1219`: `[2]` → `poskon-col-left poskon-col-blok`, `[5]` → `poskon-col-left`, `[7, 8, 9, 13, 14]` → `poskon-col-date` `130px`), dan menghapus dead inline jQuery `$('#tb-BLOK')` / `#tb_nama_konsumen` (`L1932`).
  - `public/assets/css/poskon-list-kavling.css` (`L135–529`): mengonsolidasikan header agar dikelola tunggal oleh `.sigapp-table-compact` di `style.css` (menghapus duplikasi header/sorting lama), menambahkan rata kiri `text-align: left !important` untuk `.poskon-col-left` & `.poskon-keterangan-status` (body & leaf header), `.poskon-col-blok` `120px` `border-box`, `.poskon-col-date` `130px` `border-box` + `white-space: nowrap`, serta ukuran sejajar `.poskon-action-cell .btn.btn-sm` (desktop & mobile).
- **Verifikasi:** `php -l app/Views/kavling/list-kavling.php` exit 0; `git diff --check` exit 0. Siap QA Fase F1.

### [2026-11-24 14:09] Fase F1 — Inspeksi QA Independen Lanjutan & Regresi (`sigapp-qa`: `PASS`)
- **Hasil Uji Playwright E2E, Codegraph, & Regresi:**
  - **Codegraph & CLI:** 0 broken callers pada `PosisiKonsumenService::renderPoskonActionHtml()` (`getDataTable()`, `getDataTablesBatal()`); zero bare `thead th` di `style.css`; `php -l` bersih (`PosisiKonsumenService.php` & `list-kavling.php`); `git diff --check` exit 0.
  - **Border Header `1px` (`/list-kavling`):** `tr:first-child th` `borderTop: 1px, borderBottom: 1px`, baris 2–3 `borderTop: 0px, borderBottom: 1px`, `#tb-BLOK` `borderLeft: 0px`, pemisah beku col 5 (`NAMA KONSUMEN`) ke col 6 (`SALES`) tepat `1px`; visible header `54px` (`diff = 0px`), hidden `thead = 0px`, row sync `maxDiff = 0.19px`.
  - **Rata Kiri & Lebar Kolom:** `BLOK` (`120px`), `NAMA KONSUMEN`, dan `KETERANGAN STATUS` `textAlign: "left"` (header leaf & body), `KAVLING` & `NO` tetap `"center"`; semua 5 kolom tanggal (`TGL BOOKING`, `TGL WAWANCARA`, `TGL AKAD`, `TERBIT`, `EXPIRED`) tepat `130px` (`box-sizing: border-box`, selisih `0px`) pada mode `Kavling Aktif` maupun `Semua Status`, sorting `#tb-TGL_BOOKING` & `#tb-TGL_AKAD` aktif `#2057a3` (`HTTP 200`).
  - **Tombol Titik 3, Dark Layout, & Mobile:** `.poskon-action-menu-btn` (`fa-ellipsis-v`, `::after display: "none"`) sejajar tombol mata dan dropdown terbuka (`8` item); `.dark-layout` header `rgb(30,38,56)` / body `rgb(40,48,70)` border `1px`; mobile `375×812` `mobileMaxXDiff = 0.08px`, `searchOverflowPx = -21.39px`, `0` console error.
  - **Regresi Lintas Halaman:** `/list-kavling/akad` menampilkan tombol titik 3 tanpa caret ganda & dropdown berfungsi (`0` console error); `/laporan/booking-akad` (`#booking_akad_matrix`) tidak terpengaruh `.sigapp-table-compact` (`0` console error).
- **Keputusan QA:** Sertifikat **`PASS`** — lanjut ke **Fase F2 (`sigapp-docs`)**.

### [2026-11-24 14:11] Fase F2 (Fase 4) — Sinkronisasi Dokumentasi & Finalisasi (`sigapp-docs`) — `DONE`
- **Verifikasi Katalog & `module:sync`:**
  - `app/Libraries/ModuleManager/ModuleCatalog.php` (`MKDT`): seluruh asset modul (`kavling/list-kavling.php`, `poskon-list-kavling.css`) sudah terdaftar; asset global Tier 1 (`public/assets/css/style.css`) didokumentasikan sebagai utility opt-in yang dikonsumsi oleh `MKDT`.
  - `php spark module:sync MKDT`: berhasil (`[SYNCED] MKDT -> docs\modules\MKDT\MODULE.md`, `Last Synced: 2026-11-24 14:11:01 @ b0c70af`, Status `REFACTORED`, Readiness Score `100/100`).
- **Pembaruan `docs/modules/MKDT/MODULE.md`:**
  - `## Key Functions`: mencatat `PosisiKonsumenService::renderPoskonActionHtml()` (`fa-ellipsis-v` + `.poskon-action-menu-btn` pada `L256`), pembaruan `app/Views/kavling/list-kavling.php` (`.sigapp-table-compact` `L457`, `renderPoskonActionCell()` `L884`, `columnDefs` `L1185–1219`, penghapusan dead selectors `L1932`), konsolidasi `public/assets/css/poskon-list-kavling.css` (`L135–529`), dan utility opt-in `public/assets/css/style.css` (`L1975–2252`).
  - `## Deprecated & Legacy Shims`: mencatat penghapusan dead inline jQuery width selectors (`$('#tb-BLOK')`, `$('#tb_nama_konsumen')`, `$('#tb_tgl_booking, ...')`) dan konsolidasi rule header/sorting lama dari `poskon-list-kavling.css` ke `.sigapp-table-compact` di `style.css`.
  - `## Notes`: mencatat konsumsi utility opt-in `.sigapp-table-compact` & `.poskon-action-menu-btn` dari `public/assets/css/style.css` tanpa dampak regresi ke `list-kavling-akad.php`, `list-kavling-batal.php`, maupun `/laporan/booking-akad`.
  - `## Change Log`: menambahkan entri teratas `2026-11-24 [UI_REPORT] — Penyempurnaan Border Header 1px, Rata Kiri, Kolom Tanggal Seragam 130px, Tombol Titik 3, & Global Header .sigapp-table-compact`.
- **Kepatuhan Lingkup Dokumen:**
  - `docs/MIGRATION_PROGRESS.md`: **tidak diubah** (mode `UI_REPORT`, bukan `REFACTOR` modul penuh).
  - `docs/modul-notifikasi.md`: **tidak diubah** (tidak ada penambahan/perubahan notifikasi).

---

## 4. Ringkasan File Final
Sinkron 100% dengan `git status --short`:
- `M  app/Services/Mkdt/PosisiKonsumenService.php` — Memperbarui `renderPoskonActionHtml()` (`L256`) dari tombol teks `"Aksi"` menjadi ikon titik 3 vertikal (`<i class="fa fa-ellipsis-v"></i>`) dengan class `.poskon-action-menu-btn` untuk DataTables legacy (`getDataTable()`, `getDataTablesBatal()`); kontrak raw JSON `getRawDataTables()` tetap utuh.
- `M  public/assets/css/style.css` — Menambahkan helper `.poskon-action-menu-btn.dropdown-toggle::after { display: none; }` dan utility global opt-in `table.dataTable.sigapp-table-compact` (`L1975–2252`: compact header, border tipis `1px`, sorting arrows `#2057a3`, guard hidden sizing `thead`, border body `1px`, dan varian `.dark-layout`).
- `M  app/Views/kavling/list-kavling.php` — Memasang class `sigapp-table-compact` pada `#data_tables` (`L457`), menyelaraskan `renderPoskonActionCell()` (`L884`) ke `.poskon-action-menu-btn`, memperbarui `columnDefs` (`L1185–1219`: `[2]` → `poskon-col-left poskon-col-blok`, `[5]` → `poskon-col-left`, `[7, 8, 9, 13, 14]` → `poskon-col-date` `130px`), dan menghapus dead inline jQuery selectors `$('#tb-BLOK')`, `$('#tb_nama_konsumen')`, `$('#tb_tgl_booking, ...')`.
- `M  public/assets/css/poskon-list-kavling.css` — Mengonsolidasikan header/sorting/border ke `.sigapp-table-compact` (`style.css`), menambahkan rata kiri `.poskon-col-left` & `.poskon-keterangan-status` (body & leaf header), lebar `.poskon-col-blok` `120px` `border-box`, lebar `.poskon-col-date` `130px` `border-box` + `white-space: nowrap`, dan menyamakan dimensi `.poskon-action-cell .btn.btn-sm` (desktop & mobile).
- `M  docs/modules/MKDT/MODULE.md` — Sinkronisasi via `php spark module:sync MKDT` (`@ b0c70af`) serta pembaruan `Key Functions`, `Deprecated & Legacy Shims`, `Notes`, dan `Change Log` untuk Fase D, E1, E2, dan F1.
- `M  docs/2026-11-24-redesign-compact-table-list-kavling.md` — Work doc log progres, debugging, verifikasi QA Fase C1 & F1, dan ringkasan file final (`COMPLETED`).
