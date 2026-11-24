# 2026-11-24 — Log Progres & Debugging: Redesign Compact Table List Kavling

- **Tanggal Mulai:** 2026-11-24
- **Modul Terkait:** `MKDT`
- **Mode Tugas:** `UI_REPORT`
- **Status Keseluruhan:** `COMPLETED` (`DONE — Lulus QA Independen & Sinkronisasi Docs`)

---

## 1. Tujuan & Keputusan yang Disetujui User
1. Redesign tabel utama `#data_tables` pada `app/Views/kavling/list-kavling.php` agar jauh lebih **compact**, terutama pada header 3-baris (`<thead>`) dan padding/tinggi tiap baris (`<tbody>`).
2. **Opsi A (Disetujui):** Sel `NAMA KONSUMEN` + kode QR referal tetap 2 baris super-rapat tanpa pemotongan elipsis dan tanpa mengubah backend (`PosisiKonsumenService.php`).
3. **Cakupan Tabel:** Fokus khusus pada tabel utama `#data_tables` (Tab *List Posisi Konsumen Aktif*) agar perubahan terisolasi (*minimum necessary diff*).
4. **Tone Warna & Struktur Kolom:** Tetap mempertahankan palet warna light SIGAPP/Vuexy existing (`#2057a3`, surface terang, plus fallback `.dark-layout`), seluruh **30 kolom (`0..29`)**, dan struktur header 3-baris.
5. **Pure Frontend / CSS Scoped:** Tidak ada perubahan backend; seluruh aturan tabel ditempatkan di `public/assets/css/poskon-list-kavling.css` di bawah root scope `.poskon-page`.

---

## 2. Pelacak Progres per Fase

| Fase | Lingkup | Agen | Status | Catatan Ringkas |
|---|---|---|---|---|
| **Fase A** | Inisialisasi Work Doc, root scope `.poskon-page`, & migrasi rule inline `#data_tables` ke CSS eksternal | `Orchestrator` + `sigapp-frontend` | `DONE` | Root class `.poskon-page` terpasang; rule inline tabel dimigrasi ke `poskon-list-kavling.css`; `php -l` & `git diff --check` bersih |
| **Fase B** | Compact 3-row header (visible only), compact body rows, inner-cell density, DataTables chrome, dark mode, & mobile | `sigapp-frontend` | `DONE` | Compact 3-row header, body rows, inner-cell density, sorting arrows, chrome, dark-layout, & mobile selesai; `php -l` & `git diff --check` bersih |
| **Fase C1** | Inspeksi QA Independen (Codegraph callers, Playwright E2E Desktop & Mobile, & audit `sigapp-ui-acuan`) | `sigapp-qa` | `DONE` | Putaran 2 `PASS` — alignment `<= 0.08px`, header `59px`, row sync `<= 0.19px`, sorting aktif, mobile search bersih, 0 console errors |
| **Fase C2** | Sinkronisasi `docs/modules/MKDT/MODULE.md` & finalisasi Work Doc | `sigapp-docs` | `DONE` | `php spark module:sync MKDT` OK; `MODULE.md` & Work Doc disinkronkan; `MIGRATION_PROGRESS.md` & `modul-notifikasi.md` tidak diubah (`UI_REPORT`) |

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

---

## 4. Ringkasan File Final
Sinkron 100% dengan `git status --short`:
- `M  app/Views/kavling/list-kavling.php` — Penambahan root class `.poskon-page`, pemindahan CSS tabel inline ke CSS eksternal, penambahan `syncPoskonFixedHeaderWidths()` (+ trigger `document.fonts.ready` & `columns.adjust()`), perbaikan urutan `columnDefs` sorting `[7, 9]`, target 29 `260px`, dan `#tb-BLOK` `120px`.
- `M  public/assets/css/poskon-list-kavling.css` — Mempertahankan `#modal_export_progress` (L1–133) dan menambahkan section scoped `.poskon-page` (L135–629) untuk compact table `#data_tables`, FixedColumns sync/surface, panah sorting presisi, inner-cell density, chrome, dark mode, dan responsivitas mobile (termasuk fix overflow `.dataTables_filter label`).
- `M  docs/modules/MKDT/MODULE.md` — Sinkronisasi via `php spark module:sync MKDT` serta pembaruan `Key Functions`, `Deprecated & Legacy Shims`, `Notes`, dan `Change Log` (`2026-11-24 [UI_REPORT]`).
- `?? docs/2026-11-24-redesign-compact-table-list-kavling.md` — Work doc log progres, debugging Putaran 1–2, dan ringkasan final (`COMPLETED`).
