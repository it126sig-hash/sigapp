# Tracker Eksekusi & Log Debugging — Fitur Target Siteplan (`sigapp.dev`)

> **Dokumen pelacak khusus untuk pengembangan Fitur Target Siteplan (lintas modul `Proyek` / `Siteplan` / `MKDT`) di `sigapp.dev` (Mode: `FEATURE`).**
>
> - **Status Keseluruhan:** `DONE (ALL 8 PHASES QA PASS)`
> - **Tanggal Inisialisasi:** `2026-11-16`
> - **Dokumen Rujukan Modul:**
>   - [`docs/modules/Proyek/MODULE.md`](file:///c:/Users/Salivs/Data/laragon/www/sigapp.dev/docs/modules/Proyek/MODULE.md)
>   - [`docs/modules/Siteplan/MODULE.md`](file:///c:/Users/Salivs/Data/laragon/www/sigapp.dev/docs/modules/Siteplan/MODULE.md)
>   - [`docs/modules/Transaksi/MODULE.md`](file:///c:/Users/Salivs/Data/laragon/www/sigapp.dev/docs/modules/Transaksi/MODULE.md)
>   - [`docs/modules/MKDT/MODULE.md`](file:///c:/Users/Salivs/Data/laragon/www/sigapp.dev/docs/modules/MKDT/MODULE.md)

---

## 1. Ringkasan Tujuan & Keputusan Desain

Fitur **Target Siteplan** menghadirkan pengelolaan target pemasaran kavling per tahun pada kanvas Siteplan, terintegrasi dengan Master Data Tipe (`is_active`), menu **Set Target** untuk Departemen MKDT (lengkap dengan riwayat versi target), serta alur **Request Ubah Tipe Target** dari Planning yang memerlukan approval MKDT.

### 1.1 Keputusan Desain (Disepakati Sebelum Implementasi)
1. **Mode Interaktif Kanvas + Modal Kelola Target MKDT:**
   - Pengaturan target oleh MKDT dilakukan melalui mode interaktif langsung pada kanvas Siteplan (memilih kavling di kanvas) yang terhubung dengan **Modal Kelola Target MKDT** untuk penetapan tahun/versi target, tipe target, dan riwayat perubahan (*versioning history*).
2. **Visualisasi Non-Target di Kanvas (`#e2e8f0`):**
   - Saat mode tampilan Target aktif, kavling yang **bukan** merupakan bagian dari target aktif dan **belum Akad & belum Booking** (atau **Batal**) diwarnai **abu-abu pudar (`#e2e8f0`)** agar kavling target menonjol secara visual di kanvas, sedangkan kavling non-target yang sudah berstatus **Akad** atau **Booking** tetap mempertahankan warna status Akad/Booking-nya.
3. **Kontrol Header (`Toggle` + `Dropdown Tahun Target`):**
   - Tombol/Toggle **"Tampilkan Target"** beserta **Dropdown Tahun Target** ditempatkan di kontrol siteplan untuk **menggantikan** opsi `Target` yang sebelumnya berada di dropdown `#pilih-divisi`.
4. **Batasan Dampak Approval Planning:**
   - Persetujuan (*Approval*) MKDT atas **Request Ubah Tipe Target** dari Planning **hanya mengubah data pada tabel `target_siteplan_kavling`** (tidak memodifikasi master tipe pada tabel `kavling` utama).

---

## 2. Tabel Status 4 Fase Eksekusi

| Fase | Ruang Lingkup & Fokus Kerja | Status Backend | Status Frontend | Status QA | Status Fase |
|---|---|---|---|---|---|
| **Fase 1** | **Master Data Tipe (`is_active`), Skema DB Target (`target_siteplan_kavling` & `target_siteplan_request`), & Tracker** | `DONE` | `DONE` | `PASS` | **`DONE (QA PASS)`** |
| **Fase 2** | **Toggle "Tampilkan Target", Dropdown Tahun Target, & Pewarnaan Kanvas (`master.js`)** | `DONE` | `DONE` | `PASS` | **`DONE (QA PASS)`** |
| **Fase 3** | **Menu MKDT "Set Target" (Mode Interaktif Kanvas + Modal Kelola Target + Versioning History)** | `DONE` | `DONE` | `PASS` | **`DONE (QA PASS)`** |
| **Fase 4** | **Request Ubah Tipe Target (Planning) & Approval (MKDT)** | `DONE` | `DONE` | `PASS` | **`DONE (QA PASS)`** |

### 2.1 Batch Enhancement & UX Refinement (Fase 5 – Fase 8)

| Fase | Ruang Lingkup & Fokus Kerja | Status Backend | Status Frontend | Status QA | Status Fase |
|---|---|---|---|---|---|
| **Fase 5** | **Backend API Enhancement: Spek Teknis Tipe, Batch Target CRUD, & Request Planning** | `DONE` | `N/A` | `PASS` | **`DONE (QA PASS)`** |
| **Fase 6** | **Redesign `#target-interactive-card` (Portrait, Select2 AJAX, Preview 3D/Denah/Spek, Tahun -1..+3, Staging Kanvas)** | `N/A` | `DONE` | `PASS` | **`DONE (QA PASS)`** |
| **Fase 7** | **Enhancement `#modal-target-siteplan` (Tipe Rumah Saat Ini, Modal Spek Teknis, Warning Mismatch, Batch Edit Tipe)** | `N/A` | `DONE` | `PASS` | **`DONE (QA PASS)`** |
| **Fase 8** | **Integrasi Request Ubah Kavling ke Planning, QA E2E Lintas Akun, & Sinkronisasi Docs** | `DONE` | `DONE` | `PASS` | **`DONE (QA PASS)`** |

---

## 3. Rincian & Inventori File per Fase

### 3.1 Fase 1 — Master Data Tipe (`is_active`), Skema DB Target, & Tracker (`DONE — QA PASS`)

#### Target & Realisasi Teknis Fase 1
- [x] **Migrasi Skema DB (`2026-11-16-000001_UpgradeTargetSiteplanAndTipeActive`):**
  - Menambahkan kolom `tipe.is_active` (`TINYINT(1) NOT NULL DEFAULT 1` setelah `is_subsidi`).
  - Memperbarui tabel `target_siteplan_kavling` dengan kolom `id_tipe` (`INT(11) NULL` + index), `tipe_manual` (`VARCHAR(255) NULL`), `updated_by` (`INT(11) NULL`), dan `updated_at` (`DATETIME NULL`).
  - Membuat tabel baru `target_siteplan_request` (`id_request`, `id_target`, `id_kavling`, `old_id_tipe`, `old_tipe_manual`, `new_id_tipe`, `new_tipe_manual`, `catatan_request`, `status`, `request_by`, `request_at`, `approve_by`, `approve_at`, `catatan_approval`).
- [x] **Models:** Memperbarui `TipeModel` (`is_active` di `$allowedFields`), `TargetSiteplanKavlingModel` (`id_tipe`, `tipe_manual`, `updated_by`, `updated_at`), serta menambah `TargetSiteplanRequestModel`.
- [x] **Repository, Service, & Controller `Tipe`:** Mendukung filter `is_active` / `only_active`, validasi aturan `in_list[0,1]`, serta output `is_active` pada `getAll()`, `getDataTables()`, `getOne()`, `add()`, dan `edit()`.
- [x] **Frontend View `master/tipe.php`:** Menambahkan filter status aktif/nonaktif (`#filter-status-tipe`), field status pada modal tambah/edit tipe (`#is_active` / `#edit_is_active`), serta penyesuaian kolom DataTables.
- [x] **Verifikasi QA (`sigapp-qa`) & Sinkronisasi Dokumentasi (`sigapp-docs`):** Lulus verifikasi QA (`PASS`: Playwright `/tipe` & API `only_active=1` PASS, `0` broken callers, `100% REFACTORED`); `ModuleCatalog.php`, `docs/modules/Proyek/MODULE.md`, dan `docs/modules/Siteplan/MODULE.md` telah disinkronkan via `php spark module:sync`.

#### Inventori File Fase 1
| Layer | File | Keterangan Perubahan |
|---|---|---|
| **Migration** | [`app/Database/Migrations/2026-11-16-000001_UpgradeTargetSiteplanAndTipeActive.php`](file:///c:/Users/Salivs/Data/laragon/www/sigapp.dev/app/Database/Migrations/2026-11-16-000001_UpgradeTargetSiteplanAndTipeActive.php) | Tambah `tipe.is_active`, upgrade kolom `target_siteplan_kavling`, & buat tabel `target_siteplan_request`. |
| **Model** | [`app/Models/TipeModel.php`](file:///c:/Users/Salivs/Data/laragon/www/sigapp.dev/app/Models/TipeModel.php) | Menambahkan `is_active` ke `$allowedFields`. |
| **Model** | [`app/Models/TargetSiteplanKavlingModel.php`](file:///c:/Users/Salivs/Data/laragon/www/sigapp.dev/app/Models/TargetSiteplanKavlingModel.php) | Menambahkan `id_tipe`, `tipe_manual`, `updated_by`, `updated_at` ke `$allowedFields`. |
| **Model** | [`app/Models/TargetSiteplanRequestModel.php`](file:///c:/Users/Salivs/Data/laragon/www/sigapp.dev/app/Models/TargetSiteplanRequestModel.php) | Model baru untuk tabel `target_siteplan_request`. |
| **Repository** | [`app/Repositories/Proyek/TipeRepository.php`](file:///c:/Users/Salivs/Data/laragon/www/sigapp.dev/app/Repositories/Proyek/TipeRepository.php) | Select & filter `is_active` / `only_active` pada `getAll()` & `getDataTables()`. |
| **Service** | [`app/Services/Proyek/TipeService.php`](file:///c:/Users/Salivs/Data/laragon/www/sigapp.dev/app/Services/Proyek/TipeService.php) | Validasi, default, penyimpanan, & serialisasi `is_active`. |
| **Controller** | [`app/Controllers/Api/Proyek/TipeController.php`](file:///c:/Users/Salivs/Data/laragon/www/sigapp.dev/app/Controllers/Api/Proyek/TipeController.php) | Penyesuaian endpoint API Tipe (`getAll`, `getDataTables`, `getOne`, `add`, `edit`). |
| **View** | [`app/Views/master/tipe.php`](file:///c:/Users/Salivs/Data/laragon/www/sigapp.dev/app/Views/master/tipe.php) | Filter `#filter-status-tipe`, input `#is_active` & `#edit_is_active`, dan tampilan status di tabel. |
| **Docs** | [`docs/TARGET_FEATURE_PROGRESS.md`](file:///c:/Users/Salivs/Data/laragon/www/sigapp.dev/docs/TARGET_FEATURE_PROGRESS.md) | Dokumen pelacak progres & log debugging 4 fase. |

---

### 3.2 Fase 2 — Toggle "Tampilkan Target", Dropdown Tahun Target, & Pewarnaan Kanvas (`DONE — QA PASS`)

#### Target & Realisasi Teknis Fase 2
- [x] Menambahkan kontrol Switch **"Tampilkan Target"** (`#toggle-tampilkan-target`) & **Dropdown Tahun Target** (`#filter-tahun-target` di `#filter-target-tahun-container`), serta menghapus `<option value="11">Target</option>` dari `#pilih-divisi`.
- [x] Implementasi pewarnaan kanvas pada `public/assets/js/siteplan/master.js` via `resolveTargetCanvasHit()` (4 prioritas warna target: Akad/Akad Indent, Booking/Wawancara/SP3K, Sudah Set Harga tanpa konsumen aktif, Default `Def`, serta kavling non-target berwarna abu-abu pudar `#e2e8f0` melalui `get_kategori_color('Bukan Target')`), plus sinkronisasi filter server `tahun_target`, badge tag aktif (`removeFilterTag('target')`), dan tooltip target (`setSiteplanTooltip()`).
- [x] Menambahkan stylesheet khusus ter-scope `public/assets/css/siteplan-target.css` (`#filter-side`, `.siteplan-target-*`, aksen `#2057a3`, `.dark-layout`) yang dimuat dengan `filemtime` di `app/Views/siteplan/master.php`.
- [x] Backend endpoint/query pendukung data target per tahun proyek:
  - `TargetSiteplanRepository`: menambah `getAvailableYears(int $idProyek)` & memperbarui `getKavlingTargetMap(int $idProyek, ?int $tahunTarget = null)` (`LEFT JOIN tipe target_tipe`, `id_tipe`, `tipe_manual`, `tipe_label`, `target_is_subsidi`, dan `resolveTipeLabel()`).
  - `TargetSiteplanService`: menambah `getAvailableYears(int $idProyek)` & meneruskan `$tahunTarget` pada `getKavlingTargetMap()`.
  - `SiteplanDataService`: `getKavlings(array $params)` selalu menyertakan `target_kavling` (filter opsional `tahun_target`) dan `target_years` di semua `id_role`.
  - `KavlingRepository`: konsolidasi field MKDT ke `baseQuery()` agar tersedia di semua mode divisi tanpa duplikasi alias SQL.
- [x] Verifikasi QA (`sigapp-qa`) & Sinkronisasi Dokumentasi (`sigapp-docs`): Lulus `90` unit test PHPUnit (`765` assertions `PASS`), Playwright `purnama` & `citra` `PASS` (`0` console error), dan mobile `PASS`; `ModuleCatalog.php` & `docs/modules/Siteplan/MODULE.md` disinkronkan via `php spark module:sync Siteplan`.

#### Inventori File Fase 2
| Layer | File | Keterangan Perubahan |
|---|---|---|
| **Repository** | [`app/Repositories/Siteplan/TargetSiteplanRepository.php`](file:///c:/Users/Salivs/Data/laragon/www/sigapp.dev/app/Repositories/Siteplan/TargetSiteplanRepository.php) | Tambah `getAvailableYears()`, filter `$tahunTarget` & `LEFT JOIN tipe target_tipe` pada `getKavlingTargetMap()`, serta helper `resolveTipeLabel()`. |
| **Repository** | [`app/Repositories/Transaksi/KavlingRepository.php`](file:///c:/Users/Salivs/Data/laragon/www/sigapp.dev/app/Repositories/Transaksi/KavlingRepository.php) | Konsolidasi field MKDT (`status_mkdt`, `is_batal`, tanggal proses, `is_kpr`, `mkdt_is_subsidi`) ke `baseQuery()`. |
| **Service** | [`app/Services/Siteplan/TargetSiteplanService.php`](file:///c:/Users/Salivs/Data/laragon/www/sigapp.dev/app/Services/Siteplan/TargetSiteplanService.php) | Tambah `getAvailableYears()` & teruskan `$tahunTarget` pada `getKavlingTargetMap()`. |
| **Service** | [`app/Services/Siteplan/SiteplanDataService.php`](file:///c:/Users/Salivs/Data/laragon/www/sigapp.dev/app/Services/Siteplan/SiteplanDataService.php) | `getKavlings()` selalu mengembalikan `target_kavling` (opsional `tahun_target`) & `target_years` di semua `id_role`. |
| **View** | [`app/Views/siteplan/master.php`](file:///c:/Users/Salivs/Data/laragon/www/sigapp.dev/app/Views/siteplan/master.php) | Muat `siteplan-target.css` (`filemtime`), hapus `<option value="11">Target</option>`, tambah `#toggle-tampilkan-target` & `#filter-target-tahun-container`. |
| **CSS** | [`public/assets/css/siteplan-target.css`](file:///c:/Users/Salivs/Data/laragon/www/sigapp.dev/public/assets/css/siteplan-target.css) | Stylesheet baru ter-scope `#filter-side` / `.siteplan-target-*` (`#2057a3`, `.dark-layout`). |
| **JS** | [`public/assets/js/siteplan/master.js`](file:///c:/Users/Salivs/Data/laragon/www/sigapp.dev/public/assets/js/siteplan/master.js) | Hapus cabang `if (va == 11)`; tambah kontrol target, `resolveTargetCanvasHit()`, `#e2e8f0` non-target, filter tag, & tooltip target. |
| **Tests** | [`tests/unit/SiteplanDataServiceTest.php`](file:///c:/Users/Salivs/Data/laragon/www/sigapp.dev/tests/unit/SiteplanDataServiceTest.php) | Tambah/update unit test kontrak `SiteplanDataService::getKavlings()` untuk `target_kavling` & `target_years`. |
| **Tests** | [`tests/unit/KavlingCompositeQueryTest.php`](file:///c:/Users/Salivs/Data/laragon/www/sigapp.dev/tests/unit/KavlingCompositeQueryTest.php) | Update unit test query komposit `KavlingRepository::baseQuery()`. |
| **Catalog & Docs** | [`app/Libraries/ModuleManager/ModuleCatalog.php`](file:///c:/Users/Salivs/Data/laragon/www/sigapp.dev/app/Libraries/ModuleManager/ModuleCatalog.php) | Daftarkan `siteplan-target.css` di `assets_css` modul `Siteplan`. |
| **Docs** | [`docs/modules/Siteplan/MODULE.md`](file:///c:/Users/Salivs/Data/laragon/www/sigapp.dev/docs/modules/Siteplan/MODULE.md) | Sinkronisasi kontrak, inventori, key functions, deprecations, & change log Fase 2. |

### 3.3 Fase 3 — Menu MKDT "Set Target" (`DONE — QA PASS`)

#### Target & Realisasi Teknis Fase 3
- [x] **Migrasi Menu MKDT (`2026-11-16-000002_AddMkdtTargetMenuItem.php`):** Menambahkan item menu floating `mkdt_set_target` ("Set Target", `onclick="open_target_siteplan()"`, `extra_id="mkdt-set-target-btn"`, `sort_order=65`) untuk group `4` (MKDT) & `1` (Admin) pada `siteplan_menu_items` dan `siteplan_menu_roles`.
- [x] **RBAC & Endpoint Mutasi Target (`Routes.php`, `TargetController.php`, `SiteplanService.php`):**
  - Menambahkan 3 rute baru di grup `api/target`: `POST api/target/add-kavling`, `POST api/target/update-kavling-tipe`, dan `POST api/target/remove-kavling`.
  - Memperbarui `TargetController::detail()` agar mendukung lookup `(id_proyek, tahun_target)` selain `id_target`, serta menegakkan `authorizeMutation()` (eksklusif group `1` Admin & `4` MKDT, HTTP `403` untuk role lain) dan `authorizeProjectAccess()`.
  - Memperbarui `SiteplanService::getViewSiteplanData()` (`has_akses['target']`) dan `SiteplanService::getMeta()` (`can_manage_target`) untuk group `1`, `4`, dan `11`.
- [x] **Service, Repository, Audit Label, & Unit Test (`TargetSiteplanService`, `TargetSiteplanRepository`, `HistoryService`, `TargetSiteplanServiceTest`):**
  - Implementasi `getByProjectAndYear()`, `resolveProjectIdForTargetKavling()`, `addKavlingToYear()`, `updateKavlingTipe()`, dan `removeKavling()` di `TargetSiteplanService` dengan auto-create header tahun baru, validasi kepemilikan kavling proyek, penolakan konsumen aktif (kecuali kosong/`Batal`/`NUP`), validasi tipe master aktif (`is_active = 1`) vs `tipe_manual`, serta preservasi `id_tipe`/`tipe_manual` saat `save()`.
  - Pencatatan **Versioning History** (`v1`, `v2`, ...) beserta `before_snapshot`, `after_snapshot`, `diff_summary`, dan username aktor via `HistoryService::log('target_siteplan', ...)` dengan dukungan label aksi `target_add_kavling`, `target_remove_kavling`, dan `target_update_tipe` di `HistoryService::formatActionLabel()`.
  - Unit test baru `tests/unit/TargetSiteplanServiceTest.php` mencakup CRUD per tahun, penolakan konsumen aktif & tipe nonaktif, preservasi tipe saat `save()`, dan versioning history.
- [x] **Mode Interaktif Kanvas, Modal Kelola Target, & History Versioning (`master.php`, `target.php`, `target.js`, `master.js`, `planning.js`, `siteplan-target.css`):**
  - Floating Interactive Bar `#target-interactive-bar` (pemilih tahun target, toggle `Master Tipe Aktif` vs `Ketik Manual`, tombol `Kelola & History` dan `Selesai`), flash highlight Konva (`flashTargetShapeHighlight()`), dan penjagaan klik kanvas di `master.js` & `planning.js` (`window.isTargetSetModeActive()`, `window.handleTargetCanvasClick()`, `window.scheduleTargetCanvasRefresh()`).
  - Modal `#modal-target-siteplan` dengan tab **Daftar Kavling Target** (filter tahun, pencarian kavling, inline edit tipe target, hapus kavling, simpan catatan target) dan tab **History Versioning** (kartu versi `v1..vN`, pill diff, komparasi Before vs After), lengkap dengan styling responsif `< 768px` dan `.dark-layout` di `siteplan-target.css`.

#### Inventori File Fase 3
| Layer | File | Keterangan Perubahan |
|---|---|---|
| **Migration** | [`app/Database/Migrations/2026-11-16-000002_AddMkdtTargetMenuItem.php`](file:///c:/Users/Salivs/Data/laragon/www/sigapp.dev/app/Database/Migrations/2026-11-16-000002_AddMkdtTargetMenuItem.php) | Seed menu `mkdt_set_target` ("Set Target") untuk group `4` (MKDT) & `1` (Admin). |
| **Routes** | [`app/Config/Routes.php`](file:///c:/Users/Salivs/Data/laragon/www/sigapp.dev/app/Config/Routes.php) | Daftarkan `POST api/target/add-kavling`, `update-kavling-tipe`, & `remove-kavling`. |
| **Controller** | [`app/Controllers/Api/Siteplan/TargetController.php`](file:///c:/Users/Salivs/Data/laragon/www/sigapp.dev/app/Controllers/Api/Siteplan/TargetController.php) | Tambah `addKavling()`, `updateKavlingTipe()`, `removeKavling()`, lookup `detail()` per tahun, & RBAC `authorizeMutation()` (`403`). |
| **Service** | [`app/Services/Siteplan/TargetSiteplanService.php`](file:///c:/Users/Salivs/Data/laragon/www/sigapp.dev/app/Services/Siteplan/TargetSiteplanService.php) | Logika bisnis Set Target, validasi konsumen aktif/tipe aktif, & Versioning History (`v1..vN`, diff & snapshot). |
| **Service** | [`app/Services/Siteplan/SiteplanService.php`](file:///c:/Users/Salivs/Data/laragon/www/sigapp.dev/app/Services/Siteplan/SiteplanService.php) | Perluas `has_akses['target']` & `can_manage_target` untuk group `4` (MKDT). |
| **Service** | [`app/Services/AuditTrail/HistoryService.php`](file:///c:/Users/Salivs/Data/laragon/www/sigapp.dev/app/Services/AuditTrail/HistoryService.php) | Tambah label aksi `target_add_kavling`, `target_remove_kavling`, `target_update_tipe`. |
| **Repository** | [`app/Repositories/Siteplan/TargetSiteplanRepository.php`](file:///c:/Users/Salivs/Data/laragon/www/sigapp.dev/app/Repositories/Siteplan/TargetSiteplanRepository.php) | Tambah operasi CRUD `target_siteplan`/`target_siteplan_kavling`, validasi konsumen/tipe, & `getHistory()` terstruktur. |
| **View** | [`app/Views/siteplan/master.php`](file:///c:/Users/Salivs/Data/laragon/www/sigapp.dev/app/Views/siteplan/master.php) | Muat `siteplan/target` untuk role `[1, 4, 11]`. |
| **View** | [`app/Views/siteplan/target.php`](file:///c:/Users/Salivs/Data/laragon/www/sigapp.dev/app/Views/siteplan/target.php) | Floating Interactive Bar `#target-interactive-bar` & Modal Kelola Target + History Versioning `#modal-target-siteplan`. |
| **JS** | [`public/assets/js/siteplan/target.js`](file:///c:/Users/Salivs/Data/laragon/www/sigapp.dev/public/assets/js/siteplan/target.js) | Mode interaktif kanvas, inline edit tipe, hapus kavling, simpan catatan, & timeline versioning Before vs After. |
| **JS** | [`public/assets/js/siteplan/master.js`](file:///c:/Users/Salivs/Data/laragon/www/sigapp.dev/public/assets/js/siteplan/master.js) | Integrasi `isTargetSetModeActive()`, `handleTargetCanvasClick()`, & `scheduleTargetCanvasRefresh()`. |
| **JS** | [`public/assets/js/siteplan/planning.js`](file:///c:/Users/Salivs/Data/laragon/www/sigapp.dev/public/assets/js/siteplan/planning.js) | Guard `stage.on('click.planningDeselect tap.planningDeselect')` saat mode Set Target aktif. |
| **CSS** | [`public/assets/css/siteplan-target.css`](file:///c:/Users/Salivs/Data/laragon/www/sigapp.dev/public/assets/css/siteplan-target.css) | Styling `#target-interactive-bar`, `#modal-target-siteplan`, diff grid, mobile `< 768px`, & `.dark-layout`. |
| **Tests** | [`tests/unit/TargetSiteplanServiceTest.php`](file:///c:/Users/Salivs/Data/laragon/www/sigapp.dev/tests/unit/TargetSiteplanServiceTest.php) | Unit test CRUD target, validasi konsumen/tipe aktif, preservasi `save()`, & versioning history. |

### 3.4 Fase 4 — Request Ubah Tipe Target (Planning) & Approval (MKDT) (`DONE — QA PASS`)

#### Target & Realisasi Teknis Fase 4
- [x] **Migrasi Menu Planning (`2026-11-16-000003_AddPlanningTargetMenuItem.php`):** Menambahkan item menu `planning_target_request` ("Target Siteplan", `onclick="open_target_request()"`, `extra_id="planning-target-request-btn"`, `sort_order=85`) untuk group `6` (Planning) & `1` (Admin) pada `siteplan_menu_items` dan `siteplan_menu_roles`.
- [x] **Alur Pengajuan Request Ubah Tipe Target (Planning) ke `target_siteplan_request` (`POST /api/target/request-change`):**
  - Endpoint `POST /api/target/request-change` (`TargetController::requestChange()`) dilindungi `authorizePlanningRequest()` (khusus group `1` Admin & `6` Planning; MKDT/role lain ditolak HTTP `403`) serta `authorizeProjectAccess()`.
  - `TargetSiteplanService::requestTipeChange()` memvalidasi target aktif, menolak kavling berkonsumen aktif, memvalidasi tipe baru (`is_active = 1` atau `tipe_manual`) wajib berbeda dari tipe target saat ini, mencegah duplikat request `pending` pada kavling/target yang sama, dan menyimpan record ke `target_siteplan_request`.
  - `SiteplanService::getMeta()` menambahkan flag `permissions.can_request_target_change` (`$this->userInGroups(['1', '6'])`).
- [x] **Daftar & Aksi Approval / Reject (MKDT) (`POST /api/target/requests`, `POST /api/target/review-request`):**
  - Endpoint `POST /api/target/requests` (`TargetController::requests()` -> `TargetSiteplanService::listRequests()`) dapat diakses group `1`, `4`, `6`, dan `11` (`authorizeRequestList()`) lengkap dengan filter `status`/`tahun_target` dan ringkasan counter (`all`, `pending`, `approved`, `rejected`).
  - Endpoint `POST /api/target/review-request` (`TargetController::reviewRequest()` -> `TargetSiteplanService::reviewRequest()`) dilindungi `authorizeMutation()` (khusus group `1` Admin & `4` MKDT; Planning ditolak HTTP `403`).
  - Saat **disetujui (`approve`/`approved`)**, sistem **hanya memperbarui `target_siteplan_kavling`** (`id_tipe`, `tipe_manual`, `updated_by`, `updated_at`) serta `target_siteplan_request` (`status = 'approved'`) **dan tidak menyentuh `kavling.id_tipe`**, lalu mencatat riwayat versi target baru dengan action `target_approve_tipe` (`ACC Ubah Tipe Target` di `HistoryService::formatActionLabel()`), username pengaju/penyetuju (`resolveUsername()`), serta `id_request`, `catatan_request`, dan `catatan_approval` yang diekspos oleh `TargetSiteplanRepository::getHistory()`.
- [x] **Frontend UI Planning & MKDT (`master.php`, `target.php`, `target.js`, `siteplan-target.css`):**
  - `app/Views/siteplan/master.php` memuat partial `siteplan/target` untuk role `[1, 4, 6, 11]`.
  - `app/Views/siteplan/target.php` & `public/assets/js/siteplan/target.js` menambahkan fungsi global `open_target_request()`, Tab 3 **Request Perubahan** (`#tab-target-requests` + badge counter `#target-request-pending-badge`), inline request box Planning (`.target-inline-req-box`, `submitInlineRequestChange()`), daftar & filter pengajuan (`loadTargetRequests()`, `renderTargetRequests()`), tombol **ACC** / **Tolak** MKDT (`reviewTargetRequest()`), serta tampilan kotak catatan `.target-history-notes` pada Tab 2 History Versioning.
- [x] **Catatan Notifikasi:** Tidak ditambahkan integrasi `NotifikasiService` maupun perubahan pada `docs/modul-notifikasi.md` karena tidak diminta dalam ruang lingkup fitur; pemantauan pengajuan perubahan tipe target dilakukan langsung melalui Tab **Request Perubahan** dan badge counter `#target-request-pending-badge` di Modal Target Siteplan.

#### Inventori File Fase 4
| Layer | File | Keterangan Perubahan |
|---|---|---|
| **Migration** | [`app/Database/Migrations/2026-11-16-000003_AddPlanningTargetMenuItem.php`](file:///c:/Users/Salivs/Data/laragon/www/sigapp.dev/app/Database/Migrations/2026-11-16-000003_AddPlanningTargetMenuItem.php) | Seed item menu `planning_target_request` ("Target Siteplan", `open_target_request()`) untuk group `6` (Planning) & `1` (Admin). |
| **Routes** | [`app/Config/Routes.php`](file:///c:/Users/Salivs/Data/laragon/www/sigapp.dev/app/Config/Routes.php) | Daftarkan `POST api/target/request-change`, `POST api/target/requests`, dan `POST api/target/review-request`. |
| **Controller** | [`app/Controllers/Api/Siteplan/TargetController.php`](file:///c:/Users/Salivs/Data/laragon/www/sigapp.dev/app/Controllers/Api/Siteplan/TargetController.php) | Tambah `requestChange()`, `requests()`, `reviewRequest()`, serta RBAC `authorizePlanningRequest()` & `authorizeRequestList()`. |
| **Service** | [`app/Services/Siteplan/TargetSiteplanService.php`](file:///c:/Users/Salivs/Data/laragon/www/sigapp.dev/app/Services/Siteplan/TargetSiteplanService.php) | Tambah `resolveProjectIdForTargetRequest()`, `requestTipeChange()`, `listRequests()`, `reviewRequest()`, & perbaikan `resolveUsername()`. |
| **Service** | [`app/Services/Siteplan/SiteplanService.php`](file:///c:/Users/Salivs/Data/laragon/www/sigapp.dev/app/Services/Siteplan/SiteplanService.php) | Tambah `permissions.can_request_target_change` untuk group `['1', '6']` di `getMeta()`. |
| **Service** | [`app/Services/AuditTrail/HistoryService.php`](file:///c:/Users/Salivs/Data/laragon/www/sigapp.dev/app/Services/AuditTrail/HistoryService.php) | Tambah label aksi `target_approve_tipe` (`ACC Ubah Tipe Target`) pada `formatActionLabel()`. |
| **Repository** | [`app/Repositories/Siteplan/TargetSiteplanRepository.php`](file:///c:/Users/Salivs/Data/laragon/www/sigapp.dev/app/Repositories/Siteplan/TargetSiteplanRepository.php) | Tambah `findPendingRequest()`, `findRequestById()`, `insertRequest()`, `updateRequest()`, `getRequests()`, `getRequestCounts()`, & proyeksi `id_request`/`catatan_request`/`catatan_approval` di `getHistory()`. |
| **View** | [`app/Views/siteplan/master.php`](file:///c:/Users/Salivs/Data/laragon/www/sigapp.dev/app/Views/siteplan/master.php) | Perluas pemuatan `siteplan/target` untuk role `[1, 4, 6, 11]`. |
| **View** | [`app/Views/siteplan/target.php`](file:///c:/Users/Salivs/Data/laragon/www/sigapp.dev/app/Views/siteplan/target.php) | Tambah Tab 3 **Request Perubahan** (`#tab-target-requests`, `#target-request-pending-badge`, `#panel-target-requests`, `#target-requests-table`). |
| **JS** | [`public/assets/js/siteplan/target.js`](file:///c:/Users/Salivs/Data/laragon/www/sigapp.dev/public/assets/js/siteplan/target.js) | Tambah `open_target_request()`, inline request Planning (`submitInlineRequestChange()`), daftar & review MKDT (`loadTargetRequests()`, `reviewTargetRequest()`), dan `.target-history-notes`. |
| **CSS** | [`public/assets/css/siteplan-target.css`](file:///c:/Users/Salivs/Data/laragon/www/sigapp.dev/public/assets/css/siteplan-target.css) | Styling Tab Request Perubahan, `#target-request-pending-badge`, `.target-inline-req-box`, `#target-requests-table`, `.target-history-notes`, mobile `< 768px`, & `.dark-layout`. |
| **Tests** | [`tests/unit/TargetSiteplanServiceTest.php`](file:///c:/Users/Salivs/Data/laragon/www/sigapp.dev/tests/unit/TargetSiteplanServiceTest.php) | Tambah unit test alur request ubah tipe, penolakan duplikat pending/tipe sama/konsumen aktif, reject/approve, DB invariant `kavling.id_tipe`, & metadata `getHistory()`. |

---

### Batch Enhancement & UX Refinement (Fase 5 – Fase 8)

### 3.5 Fase 5 — Backend API Enhancement: Spek Teknis Tipe, Batch Target CRUD, & Request Planning (`DONE — QA PASS`)

#### Target Checklist Teknis Fase 5
- [x] **Endpoint / Payload Spek Teknis Tipe (`Proyek` / `Siteplan`):**
  - Menyediakan endpoint & data detail spek teknis tipe (gambar 3D, denah, luas tanah/bangunan, spesifikasi material/ruangan, dan status `is_active`) untuk kebutuhan preview pada `#target-interactive-card` dan modal spek teknis di `#modal-target-siteplan`.
- [x] **Pencarian Tipe Aktif (Select2 AJAX):**
  - Menyediakan dukungan parameter pencarian (`q`/`search`, `only_active=1`, pagination/limit) yang kompatibel dengan Select2 AJAX di kanvas Set Target.
- [x] **Batch Target CRUD (`api/target/*`):**
  - Mendukung operasi batch tambah kavling dari staging kanvas serta batch edit/update tipe target beberapa kavling sekaligus dalam satu transaksi DB (`transStart`/`transComplete`), lengkap dengan validasi kepemilikan proyek, konsumen aktif, tipe aktif/manual, serta pencatatan Versioning History (`v1..vN`).
- [x] **Enhancement Payload Target & Request Planning:**
  - Memastikan daftar kavling target & request perubahan mengekspos informasi tipe rumah saat ini pada kavling (`kavling.id_tipe` / label tipe kavling aktual) serta status pengajuan `kavling_request` Planning terbaru untuk deteksi *mismatch* tipe target vs tipe kavling aktual dan integrasi alur request ubah kavling ke Planning.
- [x] **Unit Test PHPUnit:**
  - Menambahkan unit test untuk batch target CRUD, deteksi status `kavling_request` Planning di `getKavlings()`, serta batch/deduplikasi pending pada `KavlingRequestService`.

#### Inventori File Fase 5
| Layer | File | Keterangan Perubahan |
|---|---|---|
| **Repository** | [`app/Repositories/Proyek/TipeRepository.php`](file:///c:/Users/Salivs/Data/laragon/www/sigapp.dev/app/Repositories/Proyek/TipeRepository.php) | Tambah filter `only_active`, `is_active`, & `search`, serta select field spek teknis (`spesifikasi_teknis_*`) dan ID gambar (`id_gambar_tipe`, `id_gambar_denah`, `id_gambar_kerja`) di `getAll()` & `getDataTables()`. |
| **Service** | [`app/Services/Proyek/TipeService.php`](file:///c:/Users/Salivs/Data/laragon/www/sigapp.dev/app/Services/Proyek/TipeService.php) | Ekspos payload spek teknis, harga, status aktif, dan URL file aman (`gambar_tipe_access_url`, `gambar_denah_access_url`, `gambar_kerja_access_url`) pada output `raw` `getAll()` & `getDataTables()`. |
| **Repository** | [`app/Repositories/Siteplan/TargetSiteplanRepository.php`](file:///c:/Users/Salivs/Data/laragon/www/sigapp.dev/app/Repositories/Siteplan/TargetSiteplanRepository.php) | Tambah `findTargetKavlingsByIds()`, `findTargetKavlingsByTargetAndKavlingIds()`, serta `getLatestPlanningChangeRequests()` untuk memproyeksikan status `kavling_request` (`ubah_tipe`) ke daftar target kavling. |
| **Service** | [`app/Services/Siteplan/TargetSiteplanService.php`](file:///c:/Users/Salivs/Data/laragon/www/sigapp.dev/app/Services/Siteplan/TargetSiteplanService.php) | Dukung batch `addKavlingToYear()`, batch `updateKavlingTipe()`, & batch `removeKavling()` dalam satu transaksi dengan `batch_summary` dan satu entri Versioning History (`v1..vN`), plus `resolveProjectIdsForTargetKavlings()`. |
| **Controller** | [`app/Controllers/Api/Siteplan/TargetController.php`](file:///c:/Users/Salivs/Data/laragon/www/sigapp.dev/app/Controllers/Api/Siteplan/TargetController.php) | Perluas `addKavling()`, `updateKavlingTipe()`, & `removeKavling()` untuk menerima array/CSV ID via `normalizeIdList()` dan validasi akses proyek seluruh item. |
| **Service** | [`app/Services/Transaksi/KavlingRequestService.php`](file:///c:/Users/Salivs/Data/laragon/www/sigapp.dev/app/Services/Transaksi/KavlingRequestService.php) | Tambah dukungan batch `submitBatchUbahTipe()`, normalisasi `items`/`id_kavlings`, deduplikasi request `ubah_tipe` yang masih `pending` (`findPendingUbahTipeMap()`), & notifikasi Planning. |
| **Controller** | [`app/Controllers/Api/Transaksi/KavlingRequestController.php`](file:///c:/Users/Salivs/Data/laragon/www/sigapp.dev/app/Controllers/Api/Transaksi/KavlingRequestController.php) | Validasi payload batch `items` & `id_kavlings` pada endpoint `submit()` untuk pengajuan tunggal maupun batch `ubah_tipe`. |
| **Tests** | [`tests/unit/TargetSiteplanServiceTest.php`](file:///c:/Users/Salivs/Data/laragon/www/sigapp.dev/tests/unit/TargetSiteplanServiceTest.php) | Tambah 4 unit test baru: batch add kavling & penolakan konsumen aktif, batch update tipe kavling, proyeksi status request Planning di `getKavlings()`, dan batch/deduplikasi `KavlingRequestService`. |

### 3.6 Fase 6 — Redesign `#target-interactive-card` (Portrait, Select2 AJAX, Preview 3D/Denah/Spek, Tahun -1..+3, Staging Kanvas) (`DONE — QA PASS`)

#### Target Checklist Teknis Fase 6
- [x] **Redesign Layout Portrait `#target-interactive-card`:**
  - Mengubah floating card interaktif kanvas menjadi layout portrait yang ringkas, responsif, dan mendukung `.dark-layout` sesuai acuan `sigapp-ui-acuan`.
- [x] **Select2 AJAX Master Tipe Aktif:**
  - Mengintegrasikan Select2 AJAX untuk memilih tipe master aktif (`is_active = 1`) maupun opsi input manual (`tipe_manual`) dengan pencarian cepat.
- [x] **Preview 3D / Denah / Spek Teknis:**
  - Menampilkan preview gambar 3D, denah, dan ringkasan spek teknis dari tipe yang dipilih langsung di `#target-interactive-card` (via `FileAccessService` bila file terproteksi).
- [x] **Rentang Tahun Target `-1..+3`:**
  - Menyediakan pilihan tahun target dinamis dengan rentang `-1` hingga `+3` tahun dari tahun berjalan.
- [x] **Staging Kanvas (Multi-Select & Batch Commit):**
  - Mengizinkan MKDT memilih beberapa kavling di kanvas ke dalam daftar *staging* sementara sebelum disimpan secara batch ke server.

#### Inventori File Fase 6
| Layer | File | Keterangan Perubahan |
|---|---|---|
| **View** | [`app/Views/siteplan/target.php`](file:///c:/Users/Salivs/Data/laragon/www/sigapp.dev/app/Views/siteplan/target.php) | ID `#target-interactive-card`, `<select id="target-bar-year">`, `#target-bar-tipe-preview`, `#target-bar-staged-list`, dan modal `#modal-target-tipe-spec`. |
| **CSS** | [`public/assets/css/siteplan-target.css`](file:///c:/Users/Salivs/Data/laragon/www/sigapp.dev/public/assets/css/siteplan-target.css) | Layout portrait `#target-interactive-card`, preview 3D/denah/spek kompak, staging list, styling `#modal-target-tipe-spec`, mobile, & `.dark-layout`. |
| **JS** | [`public/assets/js/siteplan/target.js`](file:///c:/Users/Salivs/Data/laragon/www/sigapp.dev/public/assets/js/siteplan/target.js) | Auto-toggle `#toggle-tampilkan-target`, Select2 AJAX `<strong>Tipe</strong> (No Tipe)`, tahun `-1..+3` sinkron `#filter-tahun-target`, staging kavling kanvas + highlight Konva + batch save. |
| **JS** | [`public/assets/js/siteplan/master.js`](file:///c:/Users/Salivs/Data/laragon/www/sigapp.dev/public/assets/js/siteplan/master.js) | `syncTargetYearDropdown` menjaga tahun terpilih dalam rentang `-1..+3` & pemanggilan `renderTargetStagedCanvasHighlights()`. |

### 3.7 Fase 7 — Enhancement `#modal-target-siteplan` (Tipe Rumah Saat Ini, Modal Spek Teknis, Warning Mismatch, Batch Edit Tipe) (`DONE — QA PASS`)

#### Target Checklist Teknis Fase 7
- [x] **Kolom "Tipe Rumah Saat Ini" (Aktual Kavling):**
  - Menampilkan tipe kavling aktual (`kavling.id_tipe`) berdampingan dengan tipe target pada tabel Daftar Kavling Target.
- [x] **Warning Mismatch Tipe:**
  - Menampilkan indikator/peringatan visual apabila tipe target berbeda dengan tipe rumah aktual pada kavling.
- [x] **Modal Spek Teknis:**
  - Menambahkan tombol/pemicu untuk membuka modal detail spek teknis (3D, denah, dan rincian spesifikasi tipe) langsung dari daftar target atau riwayat.
- [x] **Batch Edit Tipe Target:**
  - Menyediakan pemilihan multi-baris (*checkbox*) + kontrol aksi batch ubah tipe target dalam `#modal-target-siteplan` menggunakan endpoint batch dari Fase 5.

#### Inventori File Fase 7
| Layer | File | Keterangan Perubahan |
|---|---|---|
| **View** | [`app/Views/siteplan/target.php`](file:///c:/Users/Salivs/Data/laragon/www/sigapp.dev/app/Views/siteplan/target.php) | Kolom checkbox `#target-check-all`, ubah label `"Tipe Rumah Saat Ini"`, `#target-batch-action-bar`, dan modal `#modal-target-edit-tipe` dengan Select2 AJAX. |
| **JS** | [`public/assets/js/siteplan/target.js`](file:///c:/Users/Salivs/Data/laragon/www/sigapp.dev/public/assets/js/siteplan/target.js) | Deteksi `isTargetRowTipeMismatch`, highlight baris & teks belum sama, `renderCurrentKavlingTipeCell`, `renderTargetRowActions`, badge `Manual` di ujung kanan tanpa `Master`, modal `#modal-target-edit-tipe` single & batch, serta `openTargetTipeSpecModal` fetch `api/tipe/getone`. |
| **CSS** | [`public/assets/css/siteplan-target.css`](file:///c:/Users/Salivs/Data/laragon/www/sigapp.dev/public/assets/css/siteplan-target.css) | Styling `.target-row-tipe-mismatch`, `.target-mismatch-note`, `.target-tipe-trigger`, `#target-batch-action-bar`, `#modal-target-edit-tipe`, responsif mobile, & `.dark-layout`. |

### 3.8 Fase 8 — Integrasi Request Ubah Kavling ke Planning, QA E2E Lintas Akun, & Sinkronisasi Docs (`DONE — QA PASS`)

#### Target Checklist Teknis Fase 8
- [x] **Integrasi Request Ubah Kavling ke Planning:**
  - Menghubungkan alur tindak lanjut *mismatch* / request ubah kavling melalui tombol aksi & batch action bar (`#target-btn-batch-req-planning` -> `submitPlanningKavlingChangeRequest()` ke `POST /api/kavling-request/submit`) serta menampilkan badge `Req Planning: Pending` dari status `kavling_request` tanpa memodifikasi master `kavling.id_tipe`.
- [x] **QA E2E Lintas Akun (`sigapp-qa`):**
  - Menjalankan verifikasi menyeluruh Codegraph, PHPUnit, `module:deadcode`, RBAC API check, dan Playwright E2E lintas akun (`citra` MKDT + `purnama` Planning) pada desktop & mobile (`375x812`), plus pembersihan seluruh data uji DB.
- [x] **Sinkronisasi Dokumentasi Final (`sigapp-docs`):**
  - Menjalankan `php spark module:sync` untuk seluruh modul terdampak (`Siteplan`, `Proyek`, `Transaksi`), memperbarui `MODULE.md` masing-masing modul, dan menutup status seluruh 8 fase pada `docs/TARGET_FEATURE_PROGRESS.md`.

#### Inventori File Fase 8
| Layer | File | Keterangan Perubahan |
|---|---|---|
| **View** | [`app/Views/siteplan/target.php`](file:///c:/Users/Salivs/Data/laragon/www/sigapp.dev/app/Views/siteplan/target.php) | Tombol batch `#target-btn-batch-req-planning` di `#target-batch-action-bar` dan kelengkapan UI integrasi request Planning. |
| **JS** | [`public/assets/js/siteplan/target.js`](file:///c:/Users/Salivs/Data/laragon/www/sigapp.dev/public/assets/js/siteplan/target.js) | `submitPlanningKavlingChangeRequest()` ke `POST /api/kavling-request/submit` (tunggal & batch) dan badge status `Req Planning: Pending`. |
| **CSS** | [`public/assets/css/siteplan-target.css`](file:///c:/Users/Salivs/Data/laragon/www/sigapp.dev/public/assets/css/siteplan-target.css) | Styling `.target-planning-req-badge`, tombol batch request Planning, responsif mobile `< 768px`, & `.dark-layout`. |
| **Docs** | [`docs/TARGET_FEATURE_PROGRESS.md`](file:///c:/Users/Salivs/Data/laragon/www/sigapp.dev/docs/TARGET_FEATURE_PROGRESS.md) | Penutupan status akhir `DONE (ALL 8 PHASES QA PASS)` dan pencatatan Sertifikat QA `PASS` Fase 5–8. |
| **Docs** | [`docs/modules/Siteplan/MODULE.md`](file:///c:/Users/Salivs/Data/laragon/www/sigapp.dev/docs/modules/Siteplan/MODULE.md) | Sinkronisasi kontrak, `Key Functions`, `Deprecated & Legacy Shims`, `Notes`, & `Change Log` Fase 5–8 via `php spark module:sync Siteplan`. |
| **Docs** | [`docs/modules/Proyek/MODULE.md`](file:///c:/Users/Salivs/Data/laragon/www/sigapp.dev/docs/modules/Proyek/MODULE.md) | Sinkronisasi kontrak spek teknis `TipeService::getAll()` (`format=raw`) & `Change Log` via `php spark module:sync Proyek`. |
| **Docs** | [`docs/modules/Transaksi/MODULE.md`](file:///c:/Users/Salivs/Data/laragon/www/sigapp.dev/docs/modules/Transaksi/MODULE.md) | Sinkronisasi kontrak batch `ubah_tipe` pada `KavlingRequestController` / `KavlingRequestService` & `Change Log` via `php spark module:sync Transaksi`. |

---

## 4. Log Debugging & QA

> Bagian ini mencatat setiap temuan QA, error runtime/UI, akar masalah (*root cause*), serta solusi per fase.

### Fase 1
- **`[2026-11-16]` Status QA:** `PASS` — Verifikasi `sigapp-qa` lulus (`Playwright /tipe` & API `only_active=1` PASS, `0` broken callers, status modul `100% REFACTORED`).
- **`[2026-11-16]` Penutupan Dokumentasi (`sigapp-docs`):** `TargetSiteplanRequestModel.php` & `target_siteplan_request` didaftarkan ke `app/Libraries/ModuleManager/ModuleCatalog.php`; `php spark module:sync Proyek` dan `php spark module:sync Siteplan` berhasil dijalankan serta `MODULE.md` kedua modul diperbarui.

### Fase 2
- **`[2026-11-16]` Status QA:** `PASS` — Verifikasi `sigapp-qa` lulus (`90` unit test PHPUnit / `765` assertions `PASS`, Playwright `purnama` & `citra` `PASS`, `0` console error, tampilan mobile `PASS`).
- **`[2026-11-16]` Penutupan Dokumentasi (`sigapp-docs`):** `public/assets/css/siteplan-target.css` didaftarkan ke `assets_css` modul `Siteplan` di `app/Libraries/ModuleManager/ModuleCatalog.php`; `php spark module:sync Siteplan` dijalankan; `docs/modules/Siteplan/MODULE.md` dan `docs/TARGET_FEATURE_PROGRESS.md` diperbarui.

### Fase 3
- **`[2026-11-16]` Status QA:** `PASS` — Verifikasi `sigapp-qa` lulus:
  - PHPUnit (`tests/unit/`): `84 tests, 290 assertions` `PASS` (termasuk `TargetSiteplanServiceTest.php`).
  - `php spark module:deadcode Siteplan`: `0` dead code candidates.
  - RBAC API check: user non-MKDT (`purnama`) menerima HTTP `403` pada endpoint mutasi `api/target/*`.
  - Playwright E2E (`citra` MKDT) desktop & mobile (`375px`): alur Mode Set Target, interaksi kanvas, Modal Kelola Target, & tab History Versioning berjalan dengan `0` JS errors dan `0` HTTP 5xx errors; seluruh DB test rows dibersihkan kembali ke `0`.
- **`[2026-11-16]` Penutupan Dokumentasi (`sigapp-docs`):** `php spark module:sync Siteplan` dan `php spark module:sync AuditTrail` dijalankan; `docs/modules/Siteplan/MODULE.md`, `docs/modules/AuditTrail/MODULE.md`, dan `docs/TARGET_FEATURE_PROGRESS.md` diperbarui.

### Fase 4
- **`[2026-11-16]` Temuan Awal QA (`cb507e52` `FAIL`):** `TargetSiteplanRepository::getHistory()` belum mengekspos `catatan_request` dan `catatan_approval` dari `history_log.metadata`, sehingga kotak `.target-history-notes` tidak tampil pada Tab 2 History Versioning (`hasNotesBox: false`).
- **`[2026-11-16]` Remediasi (`sigapp-engineer` `0bec31fa`):** Mengekspos `id_request`, `catatan_request`, dan `catatan_approval` dari `history_log.metadata` di `TargetSiteplanRepository::getHistory()` serta menambahkan assertion pada `tests/unit/TargetSiteplanServiceTest.php` (`15 tests, 181 assertions` `PASS`).
- **`[2026-11-16]` Re-Verifikasi Final QA (`cb507e52` `PASS`):**
  - PHPUnit (`TargetSiteplanServiceTest`, `SiteplanDataServiceTest`, `SiteplanVisualStatusServiceTest`): `87 tests, 349 assertions` `PASS`.
  - `php spark module:deadcode Siteplan` (`30` method, `0` kandidat) & `php spark module:deadcode AuditTrail` (`18` method, `0` kandidat).
  - RBAC API check: Planning (`purnama`) -> `POST /api/target/review-request` ditolak HTTP `403`; MKDT (`citra`) -> `POST /api/target/request-change` ditolak HTTP `403`.
  - Playwright E2E lintas akun (`citra` setup -> `purnama` request -> `citra` ACC/Tolak) + uji XSS escaping pada catatan (`hasInjectedScriptOrBold: false`) + verifikasi DB invariant (`kavling.id_tipe` master `796` tetap `35`, tidak berubah saat ACC) + mobile 3-tab `375px` `PASS` + `0` JS console errors & `0` HTTP 5xx; seluruh DB test rows dibersihkan kembali ke `0`.
- **`[2026-11-16]` Penutupan Dokumentasi (`sigapp-docs`):** `php spark module:sync` dijalankan untuk `Proyek`, `Siteplan`, `MKDT`, dan `AuditTrail` (`--check-only` OK); `docs/modules/Siteplan/MODULE.md`, `docs/modules/AuditTrail/MODULE.md`, dan `docs/TARGET_FEATURE_PROGRESS.md` diperbarui (`DONE (ALL 4 PHASES QA PASS)`).

### Fase 5
- **`[2026-11-16]` Status Implementasi Backend & QA (`PASS`):**
  - `php -l` pada seluruh 8 file PHP Fase 5 (`TipeRepository.php`, `TipeService.php`, `TargetSiteplanRepository.php`, `TargetSiteplanService.php`, `TargetController.php`, `KavlingRequestService.php`, `KavlingRequestController.php`, `TargetSiteplanServiceTest.php`) bersih tanpa syntax error.
  - PHPUnit (`TargetSiteplanServiceTest.php` & `SiteplanDataServiceTest.php`) lulus **`19 tests, 234 assertions`**, mencakup 4 unit test baru: batch add kavling & penolakan konsumen aktif, batch update tipe kavling, proyeksi status `kavling_request` Planning di `getKavlings()`, serta batch & deduplikasi pending `KavlingRequest`.

### Fase 6
- **`[2026-11-16]` Status Implementasi Frontend & QA (`PASS`):**
  - `php -l app/Views/siteplan/target.php`: `No syntax errors detected`; `node --check public/assets/js/siteplan/target.js` & `master.js`: exit code `0`; `git diff --check`: bersih.
  - Diverifikasi E2E pada Fase 8 (`PASS` untuk layout portrait `#target-interactive-card`, dropdown tahun `-1..+3`, Select2 AJAX, preview spek, & staging kanvas).

### Fase 7
- **`[2026-11-16]` Status Implementasi Frontend & QA (`PASS`):**
  - `php -l app/Views/siteplan/target.php`: `No syntax errors detected`; `node --check public/assets/js/siteplan/target.js`: exit code `0`; `git diff --check`: bersih.
  - Diverifikasi E2E pada Fase 8 (`PASS` untuk kolom `Tipe Rumah Saat Ini`, deteksi *mismatch*, modal `#modal-target-tipe-spec`, dan modal `#modal-target-edit-tipe` single & batch).

### Fase 8
- **`[2026-11-16]` Sertifikat Verifikasi QA (`sigapp-qa` — `PASS`):**
  - **Codegraph:** `0` broken callers lintas modul (`Proyek`, `Siteplan`, `Transaksi`).
  - **PHPUnit (`TargetSiteplanServiceTest.php` & `SiteplanDataServiceTest.php`):** `19 tests, 234 assertions` `PASS`.
  - **Dead Code Audit (`php spark module:deadcode Siteplan`):** `0` kandidat dead code.
  - **Playwright E2E Desktop & Mobile (`375x812`):** Lintas akun `citra` (MKDT) & `purnama` (Planning) seluruhnya `PASS` (auto-toggle Tampilkan Target, dropdown `-1..+3`, Select2 AJAX, preview 3D/denah/spek & `#modal-target-tipe-spec`, staging kanvas + batch add, warning *mismatch*, batch edit `#modal-target-edit-tipe`, request ubah kavling ke Planning `POST /api/kavling-request/submit` & badge `Req Planning: Pending`) dengan **`0` JS console errors** dan **`0` HTTP 5xx**; seluruh data uji DB telah dibersihkan.
- **`[2026-11-16]` Penutupan Dokumentasi (`sigapp-docs`):**
  - Menjalankan `php spark module:sync Siteplan`, `php spark module:sync Proyek`, dan `php spark module:sync Transaksi`.
  - Memperbarui `docs/modules/Siteplan/MODULE.md`, `docs/modules/Proyek/MODULE.md`, `docs/modules/Transaksi/MODULE.md`, dan menutup `docs/TARGET_FEATURE_PROGRESS.md` dengan status **`DONE (ALL 8 PHASES QA PASS)`**.

### Pembaruan Pasca-Fase 8 (Bugfix)
- **`[2026-11-16]` Bugfix Pewarnaan Target (`akad = 1` vs Booking) & Layout `#target-interactive-card` (`sigapp-frontend`):**
  - [`public/assets/js/siteplan/master.js`](file:///c:/Users/Salivs/Data/laragon/www/sigapp.dev/public/assets/js/siteplan/master.js) (`resolveTargetCanvasHit`):
    - Memperluas fallback `targetSubsidi` dari `row.is_subsidi` -> `row.mkdt_is_subsidi` -> `targetInfo.target_is_subsidi`.
    - Memperketat Prioritas 1 (Akad) hanya untuk `!isBatal && (row.akad == 1 || hasValidTargetDate(row.akad_tgl))` (`Akad Komersil` / `Akad Subsidi`), dan memindahkan `row.status_mkdt === 'Akad'` & `akad_indent == 1` saat `akad != 1` ke Prioritas 2 (`Booking Komersil` / `Booking Subsidi`) bersama `status_mkdt === 'Booking'`, `sp3k == 1`, `wawancara == 1`, dan `booking_tgl`.
  - [`public/assets/css/siteplan-target.css`](file:///c:/Users/Salivs/Data/laragon/www/sigapp.dev/public/assets/css/siteplan-target.css):
    - Menambahkan batas tinggi `#target-interactive-bar` (`max-height: min(calc(100% - 96px), calc(100vh - 216px))` serta `is-fixed-fallback` `calc(100vh - 160px)`) agar tidak tertutup floating dock `.float` (`bottom: 24px`, `z-index: 1040`).
    - Menambahkan `flex-shrink: 0; box-sizing: border-box;` pada child sections `#target-interactive-card` (`.target-interactive-top`, `.target-interactive-controls`, `.target-staged-section`, `.target-bar-tipe-preview`) dan memadatkan preview/staged list agar header `Mode Set Target` + border atas `#2057a3` tidak terpotong di atas serta tombol `Simpan ke Target` selalu dapat diakses di atas `.float` pada desktop & mobile (`< 768px`).
  - [`public/assets/js/siteplan/target.js`](file:///c:/Users/Salivs/Data/laragon/www/sigapp.dev/public/assets/js/siteplan/target.js):
    - Menambahkan `resetTargetInteractiveCardScroll()` dan memanggilnya pada `enterTargetSetMode()` dan `renderTargetBarTipePreview()` untuk menjaga `scrollTop = 0` saat card dibuka atau mode/tipe berubah.
- **`[2026-11-16]` Sertifikat Verifikasi QA (`sigapp-qa` — `PASS`):**
  - Verifikasi `resolveTargetCanvasHit` (`akad = 1` vs non-akad Booking & fallback `targetSubsidi`) `PASS`.
  - Verifikasi layout & bounding box `#target-interactive-bar` / `#target-interactive-card` di atas `.float` pada viewport `1366x768`, `1280x800`, dan `375x812` `PASS`, dengan **`0` JS errors** dan **`0` HTTP 5xx**.
- **`[2026-11-16]` Sinkronisasi Dokumentasi (`sigapp-docs`):**
  - Menjalankan `php spark module:sync Siteplan` (`--check-only` OK), memperbarui `Key Functions` & `Change Log` di [`docs/modules/Siteplan/MODULE.md`](file:///c:/Users/Salivs/Data/laragon/www/sigapp.dev/docs/modules/Siteplan/MODULE.md), serta merekam pembaruan bugfix pada [`docs/TARGET_FEATURE_PROGRESS.md`](file:///c:/Users/Salivs/Data/laragon/www/sigapp.dev/docs/TARGET_FEATURE_PROGRESS.md).
- **`[2026-11-17]` Bugfix Prioritas Pewarnaan Non-Target Akad & Booking Saat Toggle Filter Target Aktif (`sigapp-frontend`):**
  - **Akar Masalah (RCA):** Pada `resolveTargetCanvasHit(row, rawHargaAkhir, targetInfo)` di [`public/assets/js/siteplan/master.js`](file:///c:/Users/Salivs/Data/laragon/www/sigapp.dev/public/assets/js/siteplan/master.js), pengecekan `if (!matchesSelectedYear)` sebelumnya dievaluasi lebih awal sehingga memblokir Prioritas 1 (`Akad`) dan Prioritas 2 (`Booking`) pada kavling non-target dan memaksa seluruh kavling non-target berwarna `#e2e8f0` (`Bukan Target`).
  - **Solusi:** Memindahkan evaluasi Prioritas 1 (`Akad`: `!isBatal && (row.akad == 1 || hasValidTargetDate(row.akad_tgl))` -> `Akad Komersil`/`Akad Subsidi`) dan Prioritas 2 (`Booking`: `!isBatal &&` non-akad aktif seperti `status_mkdt === 'Booking'|'Akad'`, `sp3k == 1`, `wawancara == 1`, `booking_tgl`, `akad_indent == 1` -> `Booking Komersil`/`Booking Subsidi`) **sebelum** pengecekan `if (!matchesSelectedYear)`. Pengecekan `!matchesSelectedYear` (`Bukan Target` `#e2e8f0`) kini menjadi Prioritas 3 sehingga hanya mewarnai abu-abu kavling non-target yang belum Akad & belum Booking (atau Batal), sedangkan kavling target belum Akad & belum Booking pada Prioritas 4 & 5 tetap memakai warna tipe rumah atau `Def` (`#fcffd9`).
- **`[2026-11-17]` Sertifikat Verifikasi QA (`sigapp-qa` — `PASS`):**
  - Verifikasi Playwright pada tahun `2026` & `2027` di proyek `SANGGAR INDAH PALASTRI`: `16` `Akad Komersil`, `154` `Akad Subsidi`, `31` `Booking Komersil`, `28` `Booking Subsidi`, serta `61`/`30` `Bukan Target`, dengan **`0` JS errors** dan **`0` HTTP 5xx**.
- **`[2026-11-17]` Sinkronisasi Dokumentasi (`sigapp-docs`):**
  - Menjalankan `php spark module:sync Siteplan` (`--check-only` OK), memperbarui `Key Functions` (`resolveTargetCanvasHit`) & menambahkan entri teratas `Change Log` di [`docs/modules/Siteplan/MODULE.md`](file:///c:/Users/Salivs/Data/laragon/www/sigapp.dev/docs/modules/Siteplan/MODULE.md), serta memperbarui Bagian 1.1 poin 2 dan Bagian 4 di [`docs/TARGET_FEATURE_PROGRESS.md`](file:///c:/Users/Salivs/Data/laragon/www/sigapp.dev/docs/TARGET_FEATURE_PROGRESS.md).
