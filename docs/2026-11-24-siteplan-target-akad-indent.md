# 2026-11-24 — Log Progres & Debugging: Perbaikan Klasifikasi `akad_indent = 1` pada Filter Target (`#filter-target-other-mode`)

- **Tanggal Mulai:** 2026-11-24
- **Modul Terkait:** `Siteplan`
- **Mode Tugas:** `BUGFIX`
- **Status Keseluruhan:** `COMPLETED` (`DONE — Lulus QA Independen & Sinkronisasi Docs`)

---

## 1. Tujuan & Keputusan yang Disetujui User
1. Memindahkan **kedua** kondisi `row.akad_indent == 1` dan `hasValidTargetDate(row.akad_indent_tgl)` dari `isBooking` ke `isAkad` di `resolveTargetCanvasHit()` (`public/assets/js/siteplan/master.js`), selaras dengan `SiteplanVisualStatusService::mkdtRow()` dan `BookingAkadReportRepository`.
2. Mempertahankan `row.status_mkdt === 'Akad'` di `isBooking` sebagai fallback anomali apabila flag/tanggal akad maupun akad indent kosong (menjaga guard bugfix `2026-11-16`).
3. Menjaga guard `!isBatal` (`row.is_batal == 1 || row.status_mkdt === 'Batal'`) sebagai prioritas tertinggi, dan memberlakukan perbaikan seragam pada mode `split_4` (`Akad Subsidi` / `Akad Komersil`) dan `merge_2` (`Sudah Akad`) untuk kavling target maupun selain target saat `#toggle-tampilkan-target` aktif.

---

## 2. Pelacak Progres per Fase

| Fase | Lingkup | Agen | Status | Catatan Ringkas |
|---|---|---|---|---|
| **Fase A** | Inisialisasi Work Doc & Surgical Fix `resolveTargetCanvasHit()` | `Master Orchestrator` + `sigapp-frontend` | `DONE` | `master.js` diperbarui (`+2/-2`); `node --check` & `git diff --check` lulus |
| **Fase B** | QA Independen (`codegraph`, Playwright E2E `$sigapp-local-testing`, PHPUnit/Jest) | `sigapp-qa` | `DONE` | Sertifikat QA `PASS` (Playwright E2E Proyek 13: 25 `akad_indent` terhitung Akad; Jest Siteplan 8/8 & PHPUnit 72/72 lulus; 0 broken callers) |
| **Fase C** | Sinkronisasi `docs/modules/Siteplan/MODULE.md` & Finalisasi Work Doc | `sigapp-docs` | `DONE` | `php spark module:sync Siteplan`, update `MODULE.md`, & finalisasi work doc selesai |

---

## 3. Log Progres & Debugging

### [2026-11-24 09:53] Fase 1 — Eksplorasi & Plan Disetujui (`sigapp-explorer`)
- **Temuan & Blast Radius:**
  - `resolveTargetCanvasHit()` (`public/assets/js/siteplan/master.js:727-919`) memiliki 1 caller aktif di `load_kavling()` (`master.js:2054`).
  - Pada baris `741-755`, `row.akad_indent == 1` dan `hasValidTargetDate(row.akad_indent_tgl)` berada di `isBooking`, sehingga mode `split_4` melabeli `'Booking Subsidi'`/`'Booking Komersil'` dan mode `merge_2` melabeli `'Booking'`, serta `hitung_kavling()` menghitungnya sebagai Booking.
  - Backend (`KavlingRepository::baseQuery()`, `SiteplanVisualStatusService::mkdtRow()`) serta `master.php`, `target.php`, dan `target.js` sudah sesuai dan tidak perlu diubah.
- **Keputusan:** Plan `bugfix_plan.md` disetujui oleh User pada `2026-11-24 10:26`.

### [2026-11-24 10:28] Fase A — Surgical Fix Frontend (`sigapp-frontend`)
- **Perubahan:** Memindahkan `row.akad_indent == 1` dan `hasValidTargetDate(row.akad_indent_tgl)` dari `isBooking` ke `isAkad` pada fungsi `resolveTargetCanvasHit()` di [`public/assets/js/siteplan/master.js`](file:///c:/Users/Salivs/Data/laragon/www/sigapp.dev/public/assets/js/siteplan/master.js#L741-L756) (`+2/-2` baris).
- **Debugging / Temuan:** Tidak ada kendala pada file target; catatan bahwa `git diff --check` global repo memiliki trailing whitespace bawaan di `docs/modules/Settings/MODULE.md:5` di luar scope tugas.
- **Verifikasi:** `node --check public/assets/js/siteplan/master.js` (exit code `0`), `git diff --check -- public/assets/js/siteplan/master.js` (exit code `0`).

### [2026-11-24 10:38] Fase B — Inspeksi QA Independen (`sigapp-qa`)
- **Status Sertifikat QA:** `PASS`
- **Audit Codegraph & Callers:** 1 caller aktif `resolveTargetCanvasHit` di `public/assets/js/siteplan/master.js:2054` (`load_kavling`), kontrak argumen & return tetap identik (`0 broken callers`).
- **Playwright E2E (`Planning` / `purnama`, Proyek `SANGGAR INDAH PALASTRI` / `id_proyek=13`):**
  - Teruji pada 286 kavling (`173` Akad reguler, `25` `akad_indent == 1`, `28` Booking non-akad, `1` Batal, `59` belum berkonsumen).
  - `#toggle-tampilkan-target` ON + `#filter-target-other-mode = split_4`: seluruh 25 kavling `akad_indent = 1` masuk ke `Akad Komersil (37)` & `Akad Subsidi (161)` (total `198`), bukan `Booking Komersil (8)` / `Booking Subsidi (20)`.
  - `#filter-target-other-mode = merge_2`: seluruh 25 kavling `akad_indent = 1` masuk ke `Sudah Akad (198)`, bukan `Booking (28)`.
  - `#filter-target-other-mode = single_1` (`Sudah Memiliki Konsumen (226)`) dan `default` tetap konsisten; kavling Batal (`kav833`) tetap masuk `Bukan Target (60)`.
  - Console/Network Siteplan: 0 JS runtime error, 0 error 500, 0 error 404 pada aset/endpoint Siteplan (1 pre-existing 404 foto profil global `purnama` & 1 warning manifest di luar modul Siteplan).
- **Verifikasi Automated & CLI:** `node --check` PASS, Jest Siteplan `8/8` suites (`104/104` tests) PASS, `php vendor/bin/phpunit tests/unit/SiteplanVisualStatusServiceTest.php` (`72 tests, 168 assertions`) PASS, `php spark module:status Siteplan` (`REFACTORED | 100%`).

### [2026-11-24 10:38] Fase C — Sinkronisasi Dokumentasi & Finalisasi (`sigapp-docs`) — DONE
- **`module:sync Siteplan`:** Dijalankan (`php spark module:sync Siteplan`), tersinkronisasi (`REFACTORED`, `100/100`, `Last Synced: 2026-11-24 10:38:28 @ 7609063`, `master.js` tercatat `5180` baris). `app/Libraries/ModuleManager/ModuleCatalog.php` tidak perlu diubah karena `public/assets/js/siteplan/master.js` sudah terdaftar.
- **Pembaruan `docs/modules/Siteplan/MODULE.md`:**
  - Memperbarui deskripsi `resolveTargetCanvasHit(row, rawHargaAkhir, targetInfo, defaultHit)` di seksi `## Key Functions` (pencatatan logika `isAkad` yang mencakup `akad_indent == 1` & `hasValidTargetDate(row.akad_indent_tgl)` serta fallback anomali `row.status_mkdt === 'Akad'` di `isBooking`).
  - Menambahkan entri terbaru `[2026-11-24] [BUGFIX]` di urutan paling atas pada seksi `## Change Log`.
- **Dokumen Global & Notifikasi:** `docs/MIGRATION_PROGRESS.md` dan `docs/modul-notifikasi.md` tidak diubah karena mode tugas adalah `BUGFIX` (bukan `REFACTOR` penuh) dan tidak ada perubahan notifikasi.

---

## 4. Ringkasan File Final
Sinkron 100% dengan `git status --short`:

### File Tugas Ini
- ` M` [`public/assets/js/siteplan/master.js`](file:///c:/Users/Salivs/Data/laragon/www/sigapp.dev/public/assets/js/siteplan/master.js#L741-L756) — Perbaikan `resolveTargetCanvasHit()` (`+2/-2` baris).
- ` M` [`docs/modules/Siteplan/MODULE.md`](file:///c:/Users/Salivs/Data/laragon/www/sigapp.dev/docs/modules/Siteplan/MODULE.md) — Sinkronisasi `module:sync Siteplan`, pembaruan `## Key Functions`, & entri `## Change Log` `[2026-11-24] [BUGFIX]`.
- `??` [`docs/2026-11-24-siteplan-target-akad-indent.md`](file:///c:/Users/Salivs/Data/laragon/www/sigapp.dev/docs/2026-11-24-siteplan-target-akad-indent.md) — Dokumen kerja & log progres final tugas ini (`COMPLETED`).

### File Pre-Existing di Working Tree (Tidak Disentuh Tugas Ini)
- ` M app/Services/Settings/SiteplanMenuService.php`
- ` M docs/modules/Settings/MODULE.md`
