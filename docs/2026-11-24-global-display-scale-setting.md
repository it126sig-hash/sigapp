# 2026-11-24 — Log Progres & Debugging: Pengaturan Scale Layar Global (`UI Scale` & `Font Scale` Terpisah)

- **Tanggal Mulai:** 2026-11-24
- **Modul Terkait:** `Global Layout / Template`
- **Mode Tugas:** `UI_REPORT`
- **Status Keseluruhan:** `COMPLETED` (`DONE — Lulus QA Independen & Sinkronisasi Docs`)

---

## 1. Tujuan & Keputusan yang Disetujui User
1. **Rentang Skala & Preset:**
   - **UI Density / Spacing (`--sigapp-ui-scale`)**: `0.75` (`75%`) s/d `1.15` (`115%`), step `0.05`, preset cepat: `80%`, `90%`, `100%`, `110%`.
   - **Ukuran Font (`--sigapp-font-scale`)**: `0.85` (`85%`) s/d `1.25` (`125%`), step `0.05`, preset cepat: `90%`, `100%`, `110%`, `120%`.
2. **Pemisahan Independen Font vs Spacing:**
   - Tidak menggunakan CSS `zoom`.
   - `--sigapp-font-scale` mengontrol basis `rem` pada `html` (`calc(80% * var(--sigapp-font-scale, 1))`) serta override `font-size` hardcoded `px` global (`table.dataTable`, dll.).
   - `--sigapp-ui-scale` mengontrol `padding`, `margin`, gap, dan tinggi komponen via rasio kompensasi `--sigapp-space-ratio` (`uiScale / fontScale`) untuk satuan `rem` dan perkalian langsung `var(--sigapp-ui-scale)` untuk spacing `px` global.
   - Lebar shell sidebar desktop (`260px` / `80px`) dipertahankan statis agar frame navigasi tidak terpotong di layar `1280px–1366px`.
3. **Lokasi Kontrol & Penyimpanan:**
   - Trigger ditempatkan di `#dropdown-user` ([generate_menu.php](file:///c:/Users/Salivs/Data/laragon/www/sigapp.dev/app/Views/template/generate_menu.php)), modal `#sigapp-display-scale-modal` di [footer.php](file:///c:/Users/Salivs/Data/laragon/www/sigapp.dev/app/Views/template/footer.php) (di luar `<nav class="floating-nav">` yang memakai `backdrop-filter`).
   - Penyimpanan murni `localStorage` (`sigapp.display.uiScale` dan `sigapp.display.fontScale`) dengan anti-FOUC inline script di `<head>` [header.php](file:///c:/Users/Salivs/Data/laragon/www/sigapp.dev/app/Views/template/header.php).
4. **Kompatibilitas Siteplan Layar Kecil:**
   - `@media (max-width: 1366px)` di [master.php](file:///c:/Users/Salivs/Data/laragon/www/sigapp.dev/app/Views/siteplan/master.php) disesuaikan menjadi `calc(70% * var(--sigapp-font-scale, 1))`.

---

## 2. Pelacak Progres per Fase

| Fase | Lingkup | Agen | Status | Catatan Ringkas |
|---|---|---|---|---|
| **Fase A** | Inisialisasi work doc, fondasi CSS independen (`--sigapp-ui-scale` vs `--sigapp-font-scale`), anti-FOUC `<head>`, & Siteplan `<1366px` | `Orchestrator` + `sigapp-frontend` | `DONE` | `display-scale.css`, `header.php`, & `siteplan/master.php` selesai (`php -l` & `git diff --check` bersih) |
| **Fase B** | Modal Pengaturan Tampilan (`#dropdown-user` + `#sigapp-display-scale-modal`), engine `localStorage`, live preview & relayout DataTables | `sigapp-frontend` | `DONE` | `generate_menu.php`, `footer.php`, `display-scale.js`, & polish `display-scale.css` selesai (`php -l`, `node --check`, `git diff --check` bersih) |
| **Fase C1** | Inspeksi QA Independen (`codegraph`, Playwright E2E + `getComputedStyle()`, mobile & dark mode) | `sigapp-qa` | `DONE` | Putaran 2 `PASS` — independensi UI vs Font Scale, FixedColumns, Siteplan `<1366px`, Dark Mode, Mobile, & 0 console error terverifikasi |
| **Fase 3B** | Remediasi Bug CSS Specificity & Integrasi Token Skala Modul (`display-scale.css`, `poskon-list-kavling.css`, `list-kavling.php`, `dashboard.css`) | `sigapp-frontend` | `DONE` | Perbaikan 4 file selesai (`php -l` & `git diff --check` bersih) |
| **Fase C2** | Audit & finalisasi dokumen kerja serta verifikasi `git status --short` | `sigapp-docs` | `DONE` | `module:sync` (`Home`, `MKDT`, `Siteplan`), pembaruan `MODULE.md`, & finalisasi work doc selesai |

---

## 3. Log Progres & Debugging

### [2026-11-24 13:04] Fase 1 — Eksplorasi & Plan Disetujui (`sigapp-explorer`)
- **Temuan & Blast Radius:** Tidak ada `MODULE.md` untuk global layout; token warna `--sigapp-*` dan `localStorage` existing aman tanpa benturan. Ditemukan 3 titik kritis: (1) `app/Views/siteplan/master.php` L39-44 hardcode `html { font-size: 70%; }` pada `<= 1366px`, (2) `nav.floating-nav` memakai `backdrop-filter: blur(12px)` sehingga modal wajib berada di `footer.php`, dan (3) `list-kavling.php` sudah memanggil `applyPoskonTableHeight()` pada `window.resize`.
- **Status Plan:** `implementation_plan.md` disetujui oleh User pada `2026-11-24 13:03`.

### [2026-11-24 13:12] Fase A — Fondasi CSS Independen & Anti-FOUC Head Bootstrap (`sigapp-frontend`)
- **Perubahan:**
  - Baru: `public/assets/css/display-scale.css` (`--sigapp-ui-scale`, `--sigapp-font-scale`, `--sigapp-space-ratio` dengan `:where(html[data-sigapp-custom-scale="1"])`, override spacing `rem` & `px` global, serta styling `#sigapp-display-scale-modal` + `.dark-layout`).
  - Edit: `app/Views/template/header.php` (load `display-scale.css?v=filemtime(...)` dan script anti-FOUC `<head>` dari `localStorage`).
  - Edit: `app/Views/siteplan/master.php` (L41 `calc(70% * var(--sigapp-font-scale, 1))` pada `@media (max-width: 1366px)`).
- **Debugging / Temuan:** Tidak ada kendala; `:where(...)` dipakai pada selector utility spacing agar tidak menaikkan CSS specificity terhadap stylesheet modul.
- **Verifikasi:** `php -l` pada `header.php` & `siteplan/master.php` lulus tanpa error; `git diff --check` exit code `0`.

### [2026-11-24 13:15] Fase B — UI Pengaturan Global, `localStorage` Engine, & Live Relayout (`sigapp-frontend`)
- **Perubahan:**
  - Edit: `app/Views/template/generate_menu.php` (tambah item `#sigapp-display-scale-trigger` "Pengaturan Tampilan" di `#dropdown-user`).
  - Edit: `app/Views/template/footer.php` (markup `#sigapp-display-scale-modal` di luar `<nav>` & load `assets/js/display-scale.js?v=filemtime(...)`).
  - Baru: `public/assets/js/display-scale.js` (`window.SIGAPP_DISPLAY_SCALE`, `localStorage`, sinkronisasi `:root` & `--sigapp-space-ratio`, event `sigapp:scale-changed`, debounced DataTables `columns.adjust()` + `fixedColumns().relayout()`, kontrol slider/preset/reset).
  - Edit: `public/assets/css/display-scale.css` (penyesuaian minor `.detail-title-icon svg`, `.modal-footer .btn svg`, `.sigapp-scale-presets`, & `.sigapp-scale-preview-controls`).
- **Debugging / Temuan:** Tidak ada kendala.
- **Verifikasi:** `php -l` pada `generate_menu.php` & `footer.php` bersih; `node --check public/assets/js/display-scale.js` & `git diff --check` exit code `0`.

### [2026-11-24 13:26] Fase C1 — Inspeksi QA Independen Putaran 1 (`sigapp-qa`) — `FAIL`
- **Hasil Lulus:** `php -l`, `node --check`, `git diff --check`, 0 console errors, modal `#sigapp-display-scale-modal` di luar `floating-nav`, persistensi `localStorage` & anti-FOUC, Reset Default, Dark Mode, Mobile `390x844`, dan Siteplan `1366x768` (`11.2px` -> `12.88px`).
- **Temuan FAIL (`getComputedStyle()`):**
  1. Pada `/public/list-kavling`, `#data_tables tbody td` tetap `10.5px` / `2px 6px` pada semua skala, sementara `.poskon-table-card .card-body` & `.poskon-filter-actions .btn` tidak mengecil di `80%` UI Scale dan justru membesar saat Font Scale `115%`.
  2. Pada Dashboard, `.dashboard-period-pills .btn` tetap `5.76px 11.52px` dan tidak ikut skala.
  3. *(Catatan visual FixedColumns)*: terdapat offset vertikal `6px` pada `.DTFC_LeftHeadWrapper table.dataTable` di `poskon-list-kavling.css:154`.
- **RCA:** `:where(html[data-sigapp-custom-scale="1"])` di `display-scale.css` berspesifisitas 0 dan dimuat sebelum CSS modul, serta `poskon-list-kavling.css`, `list-kavling.php` `<style>`, dan `dashboard.css` memakai `px !important` & `rem` statis tanpa CSS scale variables.
- **Tindak Lanjut:** Masuk **Fase 3B** oleh `sigapp-frontend` mengacu pada `bugfix_plan.md`.

### [2026-11-24 13:32] Fase 3B — Remediasi CSS Specificity & Integrasi Token Skala Modul (`sigapp-frontend`)
- **Perubahan:**
  - Edit: `public/assets/css/display-scale.css` (hapus `:where(...)` pada `html[data-sigapp-custom-scale="1"]`, plus safeguard `textarea.form-control`, `select.form-control[multiple/size]`, & `.form-inline .form-group`).
  - Edit: `public/assets/css/poskon-list-kavling.css` (`font-size` `px` `#data_tables` dikalikan `--sigapp-font-scale`, spacing `px` dikalikan `--sigapp-ui-scale`, spacing `rem` dikalikan `--sigapp-space-ratio`, & tambah `.DTFC_LeftHeadWrapper`/`.DTFC_LeftBodyWrapper table.dataTable` ke `margin-top: 0 !important; margin-bottom: 0 !important;`).
  - Edit: `app/Views/kavling/list-kavling.php` (blok `<style>` toolbar/filter/card memakai `--sigapp-space-ratio` & `--sigapp-ui-scale`).
  - Edit: `public/assets/css/dashboard.css` (seluruh spacing `rem` dikalikan `--sigapp-space-ratio` dan `px` dikalikan `--sigapp-ui-scale`, sementara `font-size` tetap mengikuti `--sigapp-font-scale`).
- **Debugging / Temuan:** Spesifisitas `.poskon-page .poskon-filter-actions .btn` & `.dashboard-page .dashboard-dept-tabs.nav-tabs .nav-link` dijaga agar tetap mengalahkan rule global tanpa merusak layout modul.
- **Verifikasi:** `php -l app/Views/kavling/list-kavling.php` lulus; `git diff --check` exit code `0`. Siap re-verifikasi QA Putaran 2.

### [2026-11-24 13:39] Fase C1 — Inspeksi QA Independen Putaran 2 (`sigapp-qa`) — `PASS`
- **Verifikasi CLI & Callers:** `php -l` (5 file PHP), `node --check public/assets/js/display-scale.js`, `git diff --check` (9 file), dan audit callers (`0` broken callers) lulus 100%; semua asset termuat `200 OK` dengan `filemtime`.
- **Pengukuran `getComputedStyle()` (MKDT `citra`):**
  - Dashboard (`/`): `100%/100%` → `80%/100%` → `80%/115%` membuktikan `.dashboard-period-pills .btn` (`5.76px 11.52px` → `4.608px 9.216px` → `4.608px 9.216px`; font `10.496px` → `10.496px` → `12.0704px`), `.project-stock-target-card`, `.dashboard-kpi-card`, `.card-body`, dan `.app-content` bergerak **independen**.
  - List Kavling (`/public/list-kavling`): `#data_tables tbody td` (`2px 6px` → `1.6px 4.8px` → `1.6px 4.8px`; font `10.5px` → `10.5px` → `12.075px`), `.poskon-table-card .card-body`, `.poskon-datatable-card .card-body`, `.poskon-filter-actions .btn` bergerak **independen**, serta FixedColumns `.DTFC_LeftHeadWrapper` / `.DTFC_LeftBodyWrapper` memiliki `marginTop: 0px` (`headTopDiff: 0px`, `row1TopDiff: 0px`).
- **Fitur, Safeguard, Dark Mode, Mobile, & Siteplan:** Persistensi `localStorage` & anti-FOUC pasca reload lulus; `#sigapp-display-scale-modal` sesuai `sigapp-ui-acuan` + `.dark-layout`; Reset Default lulus; safeguard `textarea.form-control` / `select[multiple]` / `.form-inline` lulus; Mobile `390x844` `0` horizontal overflow; Siteplan `1366x768` (`purnama`) merespons `--sigapp-font-scale` (`11.2px` → `12.88px`); `0` JS error.

### [2026-11-24 13:42] Fase C2 — Sinkronisasi Dokumentasi & Finalisasi (`sigapp-docs`) — `DONE`
- **Sinkronisasi Modul (`php spark module:sync`):**
  - Menjalankan `php spark module:sync Home`, `php spark module:sync MKDT`, dan `php spark module:sync Siteplan` (berhasil tanpa mengubah file di `app/`, `public/`, atau `assets/`).
  - Memperbarui `docs/modules/Home/MODULE.md` (`## Notes` & `## Change Log`): mencatat integrasi CSS variabel skala global (`--sigapp-ui-scale`, `--sigapp-space-ratio`, `--sigapp-font-scale`) pada `public/assets/css/dashboard.css`.
  - Memperbarui `docs/modules/MKDT/MODULE.md` (`## Key Functions` & `## Change Log`): mencatat integrasi variabel skala global pada `app/Views/kavling/list-kavling.php` & `public/assets/css/poskon-list-kavling.css` serta perbaikan `margin-top/bottom: 0 !important` pada `.DTFC_LeftHeadWrapper`/`.DTFC_LeftBodyWrapper table.dataTable` (sambil mempertahankan entri compact table sebelumnya).
  - Memperbarui `docs/modules/Siteplan/MODULE.md` (`## Key Functions` & `## Change Log`): mencatat penyesuaian `@media screen and (max-width: 1366px)` `html { font-size: calc(70% * var(--sigapp-font-scale, 1)); }` pada `app/Views/siteplan/master.php`.
- **Ledger & Notifikasi:** `docs/MIGRATION_PROGRESS.md` tidak diubah (mode `UI_REPORT`, bukan `REFACTOR`); `docs/modul-notifikasi.md` tidak diubah (tidak ada perubahan notifikasi).
- **Audit Git:** Output `git status --short` terverifikasi dan disinkronkan pada Seksi 4 di bawah.

---

## 4. Ringkasan File Final
Sesuai `git status --short` pasca-Fase C2:

### File Tugas Pengaturan Scale Layar Global (`UI_REPORT`)
- **Baru (`??`):**
  - `public/assets/css/display-scale.css` — CSS variables independen (`--sigapp-ui-scale`, `--sigapp-font-scale`, `--sigapp-space-ratio`), pemisahan font vs spacing global/DataTables, safeguard elemen form, & styling `#sigapp-display-scale-modal` (light & `.dark-layout`).
  - `public/assets/js/display-scale.js` — Engine `window.SIGAPP_DISPLAY_SCALE`, persistensi `localStorage` (`sigapp.display.uiScale`, `sigapp.display.fontScale`), event `sigapp:scale-changed`, debounced DataTables `columns.adjust()` + `fixedColumns().relayout()`, & kontrol modal.
  - `docs/2026-11-24-global-display-scale-setting.md` — Log progres, debugging, QA, dan dokumentasi final pekerjaan ini.
- **Dimodifikasi (`M`):**
  - `app/Views/template/header.php` — Memuat `display-scale.css?v=filemtime(...)` & inline anti-FOUC script di `<head>`.
  - `app/Views/template/generate_menu.php` — Menambahkan trigger `#sigapp-display-scale-trigger` ("Pengaturan Tampilan") di `#dropdown-user`.
  - `app/Views/template/footer.php` — Menambahkan markup `#sigapp-display-scale-modal` di luar `<nav class="floating-nav">` & memuat `display-scale.js?v=filemtime(...)`.
  - `app/Views/siteplan/master.php` — Menyesuaikan `@media screen and (max-width: 1366px)` `html { font-size: calc(70% * var(--sigapp-font-scale, 1)); }`.
  - `public/assets/css/dashboard.css` — Integrasi `--sigapp-space-ratio` (spacing `rem`) & `--sigapp-ui-scale` (dimensi `px`).
  - `app/Views/kavling/list-kavling.php` — Integrasi `--sigapp-space-ratio` & `--sigapp-ui-scale` pada `<style>` toolbar/filter/card *(bersama perubahan Redesign Compact Table pre-existing di working tree)*.
  - `public/assets/css/poskon-list-kavling.css` — Integrasi `--sigapp-font-scale`, `--sigapp-ui-scale`, `--sigapp-space-ratio`, serta fix `margin-top/bottom: 0 !important` pada `.DTFC_LeftHeadWrapper`/`.DTFC_LeftBodyWrapper table.dataTable` *(bersama perubahan Redesign Compact Table pre-existing di working tree)*.
  - `docs/modules/Home/MODULE.md` — Sinkronisasi `module:sync Home` & catatan integrasi skala global pada `dashboard.css`.
  - `docs/modules/MKDT/MODULE.md` — Sinkronisasi `module:sync MKDT` & catatan integrasi skala global + fix FixedColumns *(bersama catatan Redesign Compact Table pre-existing)*.
  - `docs/modules/Siteplan/MODULE.md` — Sinkronisasi `module:sync Siteplan` & catatan penyesuaian `master.php` layar `<= 1366px`.

### File Uncommitted Lain di Working Tree (`git status --short`)
- `?? docs/2026-11-24-redesign-compact-table-list-kavling.md` *(work doc pre-existing dari pekerjaan Redesign Compact Table `#data_tables` di working tree yang belum di-commit).*
