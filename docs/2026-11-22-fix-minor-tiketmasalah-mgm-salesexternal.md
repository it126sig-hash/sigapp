# 2026-11-22 — Log Progres & Debugging: Fix Minor Modular (TiketMasalah, MGM, SalesExternal)

- **Tanggal Mulai:** 2026-11-22
- **Modul Terkait:** `TiketMasalah`, `MGM`, `SalesExternal` *(+ kontrak publik tipis `MKDT`)*
- **Mode Tugas:** `REFACTOR`
- **Status Keseluruhan:** `COMPLETED`

---

## 1. Tujuan & Keputusan yang Disetujui User
1. **Keputusan #1 (Opsi A):** Menambah kontrak publik tipis pada modul `MKDT` (`App\Services\Mkdt\MkdtPricingService::updateHargaNetAjb(int $idMkdt, float $hargaNetAjb): bool` + `PosisiKonsumenRepository::updateHargaNetAjb()`, terdaftar di `ModuleCatalog.php` blok `MKDT`, dan disinkronkan ke `docs/modules/MKDT/MODULE.md`) untuk mutasi `mkdt.harga_net_ajb` dari `SalesExternalService`.
2. **Keputusan #2 (Opsi A):** Membungkus `getListMGM()` dan `searchReferrerOptions()` di `Api\MGM\ReferralController` (L29, L204) melalui `ReferralService` sambil mempertahankan top-level key `results` untuk Select2.
3. **Keputusan #3 (Opsi A):** Tetap *surgical* hanya memperbaiki `Web\SalesExternal\SalesExternalController` (L95, L145) sesuai temuan audit `2026-11-22` (tanpa menyentuh `Api\SalesExternal\SalesExternalController`).
4. **Pengecualian Resmi & Kontrak Tetap:**
   - `PublicReferralController` (`MGM`) tetap `extends ResourceController` sebagai endpoint Public CORS non-CSRF.
   - `FeeSalesSettingService` memakai kontrak publik `App\Services\Settings\MenuAccessService`.
   - `ReferralService` memakai kontrak publik `App\Services\Mkdt\KonsumenService::upsert()`.
   - Nol perubahan Frontend/View/JS (Zero-Downtime JSON contract terjaga).
5. **Isolasi Context Memory:** Setiap sub-fase (`B1`–`D4`) menggunakan instance sub-agent baru (*fresh conversationId*) per modul.

---

## 2. Pelacak Progres per Fase

| Fase | Lingkup | Agen | Status | Catatan Ringkas |
|---|---|---|---|---|
| **Fase A** | Inisialisasi dokumen kerja | `Master Orchestrator` | `DONE` | Dokumen kerja dibuat pasca-approval User |
| **Fase B1** | `TiketMasalah` — Implementasi Backend | `sigapp-engineer` | `DONE` | 3 file diubah (`BaseApiController`, `update($id = null)`, helper `auth`, 5 method baru di repo, `php -l` 0 error, grep `->table(` = 0) |
| **Fase B2** | `TiketMasalah` — QA Independen | `sigapp-qa` | `DONE` | Sertifikat QA `PASS` (via `Planning`/`purnama`, 10/10 endpoint JSON `200 OK`, 0 broken callers, `100% REFACTORED`; + Known Pre-Existing Warnings dicatat) |
| **Fase B3** | `TiketMasalah` — Sinkronisasi Docs | `sigapp-docs` | `DONE` | `module:sync TiketMasalah` (`100% REFACTORED`), `MODULE.md` & baris #2 `MIGRATION_PROGRESS.md` → `COMPLETED` |
| **Fase C1** | `MGM` — Implementasi Backend | `sigapp-engineer` | `DONE` | 4 file diubah (`ReferralRepository` +4 method, `ReferralQrService` & `ReferralService` 0 query langsung, `MKDT\KonsumenService::upsert()`, `ReferralController` -> `ReferralService`, PHPUnit 8/8 PASS) |
| **Fase C2** | `MGM` — QA Independen | `sigapp-qa` | `DONE` | Sertifikat QA `PASS` (Akun Promosi `benny`, 0 console error `/member-get-member`, QR preview/download `200 OK`, `/api/public/check-referral` 400/404/200 OK, PHPUnit 8/8 PASS, 0 broken callers) |
| **Fase C3** | `MGM` — Sinkronisasi Docs | `sigapp-docs` | `DONE` | `module:sync MGM` (`100% REFACTORED`), `MODULE.md` & baris #3 `MIGRATION_PROGRESS.md` → `COMPLETED` |
| **Fase D1** | `SalesExternal` — Setting, MenuAccess & Web Controller | `sigapp-engineer` | `DONE` | 1 file dibuat (`FeeSalesSettingRepository`), 5 diubah (`FeeSalesSettingController` `BaseApiController`, `FeeSalesSettingService` -> `MenuAccessService`, `Web\SalesExternalController` -> Service, `ModuleCatalog`), `SalesExternal` `100% REFACTORED` |
| **Fase D2** | `SalesExternal` — Repo Extraction & Kontrak `MKDT` | `sigapp-engineer` | `DONE` | Kontrak tipis `MKDT\MkdtPricingService` + `PosisiKonsumenRepository::updateHargaNetAjb()`, 44 `->table()`/`->query()` dipindah ke `SalesExternalRepository` (27 method baru), partial mock `FeeSalesWorkflowTest` 31/31 PASS |
| **Fase D3** | `SalesExternal` — QA Independen | `sigapp-qa` | `DONE` | Sertifikat QA `PASS` (MKDT `citra` & Keuangan `nisalis` E2E 0 error 500/JS, `/setting-fee-sales` JSON `message`+`messages` OK, PHPUnit 39/39 PASS, 4 modul `100% REFACTORED`) |
| **Fase D4** | `SalesExternal` + `MKDT` — Sinkronisasi Docs & Finalisasi | `sigapp-docs` | `DONE` | `module:sync SalesExternal` & `MKDT` (`100% REFACTORED`), update `MODULE.md`, baris #4 `MIGRATION_PROGRESS.md` -> `COMPLETED` (`5 COMPLETED` \| `10 Minor` \| `2 INCOMPLETE`), & finalisasi work doc (25 file `git status --short` sinkron) |

---

## 3. Log Progres & Debugging

### [2026-11-22 09:50] Fase 1 — Eksplorasi Terisolasi & Plan Disetujui (`3x sigapp-explorer`)
- **Temuan & Blast Radius:**
  - **`TiketMasalah`:** `Api\TiketMasalah\TiketMasalahController` masih `extends BaseController` + `use ResponseTrait` (risiko fatal PHP 8.1+ pada `update($id = null)`, hilangnya helper `auth`, dan frontend `tiket-masalah.js` membaca `res.message` / `res.is_pinned` / `Object.values(xhr.responseJSON.messages)`). `TiketMasalahService` memiliki 7 query `$this->db->table()` read-only (L214, L415, L594, L618, L640, L651, L664). Blast radius rendah (0 caller lintas modul).
  - **`MGM`:** `ReferralQrService` membuka `$this->db` hanya untuk 1 query read-only join 6 tabel (`mkdt` dkk., L67–96). `ReferralService` memiliki query langsung ke `konsumen` (L101, L109), mutasi tabel asing `konsumen` via `KonsumenModel` (L112), dan `tableExists` (L831–832). `PublicReferralController` terverifikasi sebagai pengecualian resmi Public CORS non-CSRF. `Api\MGM\ReferralController` L29 & L204 memanggil `$this->repo` langsung. Blast radius sedang (`generateKodeReferal()` dipanggil dari dalam transaksi `NupService` & `TransaksiService`).
  - **`SalesExternal`:** `Api\SalesExternal\FeeSalesSettingController` masih `extends BaseController` (frontend `fee_sales_access.php` membaca `res.message` singular). `Web\SalesExternal\SalesExternalController` L95 & L145 memanggil `SalesExternalRepository` langsung. `FeeSalesSettingService` memiliki 10 query DB termasuk mutasi `menu_roles`. `SalesExternalService` memiliki 43 query DB termasuk 2 mutasi `mkdt.harga_net_ajb` (L273, L1391). `FeeSalesWorkflowTest.php` L1322 memakai full mock `SalesExternalRepository` yang perlu diubah ke partial mock.
  - **Keterkaitan:** Tidak ada keterkaitan silang antara `TiketMasalah`, `MGM`, dan `SalesExternal`.

### [2026-11-22 09:52] Fase A — Inisialisasi Dokumen Kerja (`Master Orchestrator`)
- **Perubahan:** Membuat dokumen kerja `docs/2026-11-22-fix-minor-tiketmasalah-mgm-salesexternal.md`.
- **Debugging / Temuan:** Tidak ada.
- **Verifikasi:** File dokumen kerja terbentuk sesuai template standar dan siap menjadi pelacak progres per fase.

### [2026-11-22 09:57] Fase B1 — Implementasi Backend `TiketMasalah` (`sigapp-engineer`)
- **Perubahan:**
  - `app/Repositories/TiketMasalah/TiketMasalahRepository.php`: Menambahkan 5 method read-only (`getUserPrimaryGroupId`, `getRefInfo`, `getActiveUsers`, `getCreators`, `getDivisions`).
  - `app/Services/TiketMasalah/TiketMasalahService.php`: Mengganti ke-7 pemanggilan `$this->db->table()` dengan delegasi ke 5 method `TiketMasalahRepository` (mempertahankan `$this->db` khusus transaksi).
  - `app/Controllers/Api/TiketMasalah/TiketMasalahController.php`: Migrasi `extends BaseController` + `ResponseTrait` → `extends BaseApiController`, menambahkan `protected $helpers = ['auth', 'active_proyek'];` + `helper(...)` di `__construct()`, mengubah `update()` → `update($id = null)`, serta mempertahankan kontrak JSON existing (`tiket-masalah.js`).
- **Debugging / Temuan:** Tidak ada kendala pada modul `TiketMasalah`. `git status -s` hanya menyentuh 3 file backend target.
- **Verifikasi:** `php -l` 0 syntax error pada 3 file; grep `->table(` di `TiketMasalahService.php` = 0 match; bootstrap check `PARENT=BaseApiController`, `USER_ID_FN=OK`, `UPDATE_PARAMS=1`; `php spark module:status` (`TiketMasalah`: `REFACTORED 100%`).

### [2026-11-22 10:12] Fase B2 — Inspeksi QA Independen `TiketMasalah` (`sigapp-qa` → `FAIL`)
- **Status Sertifikat:** `FAIL` (bukan regresi kode Backend Fase B1).
- **Temuan Lulus:**
  - `git grep -n "db->table(" -- app/Services/TiketMasalah/` = 0 match; 0 broken callers; `php -l` & `git diff --check` bersih; `php spark module:deadcode TiketMasalah` = 0 kandidat dead code dari 25 metode; `php spark module:status` = `TiketMasalah REFACTORED 100%`; `php spark routes` utuh (1 Web + 13 API routes).
  - Pengujian Playwright E2E via akun terotorisasi `Planning` (`purnama`, menu `tiket-masalah` id=38): `/tiket-masalah` memuat DataTables (3 aktif / 14 total), filter `creators` & `divisions`, modal detail tiket `#13`, modal Siteplan (`#btn_show_buat_tiket` + 25 user aktif), serta 10/10 endpoint JSON (`users`, `users?creators_only=1`, `creators`, `divisions`, `datatable`, `list`, `detail`, `ref-info` `kavling` & `others`, `progress`) mengembalikan HTTP `200 OK` & JSON valid (0 HTTP 500, 0 JS error).
- **Debugging / Temuan Kegagalan QA:**
  1. **Login Akun Produksi (`alika`) Gagal:** Form login menolak password dari `.agents/skills/sigapp-local-testing/SKILL.md` (`"Unable to log you in. Please check your password."`, `auth_logins.id=15419`, `success=0`). `sigapp-qa` dilarang mereset/memutasi data auth.
  2. **Console Error `404` Pre-Existing (`Indonesian.json`):** `public/assets/js/tiket_masalah/index.js:255` meminta `assets/vendor/datatables/i18n/Indonesian.json` yang tidak ada di disk (di luar scope backend plan awal).
  3. **Console Error `404` File Upload Lokal Hilang:** Foto tiket sampel `20260916` & foto profil lokal tidak ada di disk (`writable/uploads/...`), `FileAccessService::pathUrl()` bekerja sesuai desain.
- **Tindak Lanjut:** Menunggu keputusan Planning Model / User mengenai (1) validitas akun uji Produksi (`alika`) vs penerimaan verifikasi via `purnama` / kredensial lain, dan (2) penanganan 404 pre-existing `Indonesian.json` (apakah diperbaiki via `sigapp-frontend` atau dikecualikan agar sertifikat QA Fase B2 bisa diloloskan).

### [2026-11-22 10:15] Fase B2 (Revisi Rubrik QA) — Sertifikat QA `PASS` `TiketMasalah` (`sigapp-qa`)
- **Keputusan Planning Model:** Menerima verifikasi E2E melalui akun terotorisasi resmi `Planning` (`purnama`) tanpa memutasi password `alika`, serta menetapkan error `404` asset/upload pre-existing di luar File Inventory Backend sebagai *Known Pre-Existing Warnings* (tidak memblokir `PASS` untuk scope Backend-Only `REFACTOR`).
- **Hasil Sertifikat:** **`PASS`** (0 broken callers, 0 sisa `$this->db->table()`, 10/10 endpoint JSON `200 OK`, 0 HTTP 500, 0 JS uncaught error, `TiketMasalah` `100% REFACTORED`, `0` kandidat dead code).

### [2026-11-22 10:17] Fase B3 — Sinkronisasi Dokumentasi `TiketMasalah` (`sigapp-docs`)
- **Perubahan:**
  - `docs/modules/TiketMasalah/MODULE.md`: Disinkronkan via `php spark module:sync TiketMasalah` (`100/100 REFACTORED`), dilengkapi `Key Functions`, `Deprecated & Legacy Shims`, penghapusan catatan minor lama, serta entri `Change Log` `[2026-11-22] [REFACTOR]`.
  - `docs/MIGRATION_PROGRESS.md`: Baris #2 **TiketMasalah** diperbarui menjadi `` `COMPLETED` `` (`2026-11-22`, bersih 0 temuan).
- **Debugging / Temuan:** Tidak ada.
- **Verifikasi:** `php spark module:sync TiketMasalah --check-only` (`[OK]`); `git status --short` terisolasi hanya pada 3 file kode `TiketMasalah` + `MODULE.md` + `MIGRATION_PROGRESS.md` + work doc ini.

### [2026-11-22 10:25] Fase C1 — Implementasi Backend `MGM` (`sigapp-engineer`)
- **Perubahan:**
  - `app/Repositories/MGM/ReferralRepository.php`: Menambahkan 4 method read-only (`findQrReferralData`, `findKonsumenKodeReferal`, `isKodeReferalExists`, `hasHistoryTable`) tanpa meng-override `__construct()` `CodeIgniter\Model`.
  - `app/Services/MGM/ReferralQrService.php`: Menghapus `$this->db`, `BaseConnection`, dan `findReferralData()`, lalu mendelegasikan ke `ReferralRepository::findQrReferralData()`.
  - `app/Services/MGM/ReferralService.php`: Menghapus `KonsumenModel`, query langsung `konsumen`, dan `tableExists()`; mendelegasikan bacaan ke `ReferralRepository` dan penulisan `konsumen.kode_referal` ke `App\Services\Mkdt\KonsumenService::upsert()` tanpa membuka transaksi DB baru; menambahkan thin wrapper `getListMGM()` dan `searchReferrerOptions()`.
  - `app/Controllers/Api/MGM/ReferralController.php`: Menghapus instansiasi `ReferralRepository` langsung (L29, L204) dan mendelegasikan ke `$this->service` sambil mempertahankan top-level key `results` untuk Select2. (`PublicReferralController` dipertahankan tidak berubah sebagai pengecualian resmi Public CORS non-CSRF).
- **Debugging / Temuan:** `ReferralController::canMgmSearch()` (private method L230–233) terverifikasi pre-existing dead code (0 callers), dibiarkan utuh sesuai prinsip surgical changes.
- **Verifikasi:** `php -l` 0 error pada 4 file; grep `->table(` & `KonsumenModel` di kedua Service = 0 match; `ReferralRepositoryDateFilterTest.php` (`OK: 2 tests, 9 assertions`); `ConsumerReplacementServiceTest.php` (`OK: 6 tests, 29 assertions`); bootstrap CI4 instansiasi 4 kelas + shims OK; `php spark module:status` (`MGM REFACTORED 100%`).

### [2026-11-22 10:39] Fase C2 — Inspeksi QA Independen `MGM` (`sigapp-qa` → `PASS`)
- **Status Sertifikat:** **`PASS`**
- **Hasil Uji:**
  - Audit `codegraph_explore` & `git grep`: 0 broken callers pada `ReferralQrService`, `ReferralService::generateKodeReferal`, `ReferralRepository`, `ReferralController`; 0 `->table(` & `KonsumenModel` di Services; 0 `ReferralRepository` di `ReferralController`.
  - Playwright E2E (`Promosi` / `benny`): `/member-get-member` render 0 console error; `POST /api/mgm/list`, `POST /api/mgm/subrows`, `POST /api/mgm/search-options` (termasuk top-level key `results`), `POST /api/mgm/stages/list`, `POST /api/referral-qr/preview` (`900x1100` PNG), & `GET /api/referral-qr/download` seluruhnya `200 OK`. Endpoint publik `POST /api/public/check-referral` (400/404/200 tanpa CSRF) terverifikasi utuh.
  - CLI & Unit Test: `ReferralRepositoryDateFilterTest` (`OK: 2 tests, 9 assertions`), `ConsumerReplacementServiceTest` (`OK: 6 tests, 29 assertions`), `php spark module:status` (`MGM REFACTORED 100%`), `php spark module:deadcode MGM` (0 orphaned methods baru).
- **Catatan Pre-Existing Non-Blocking:** `app/Views/mgm/index.php` masih menggunakan `?v=<?= time() ?>` (di luar scope Backend C1) dan `public/assets/js/siteplan/sales.js:140` error di `/siteplan/view` saat dibuka role `sales` (di luar modul MGM).

### [2026-11-22 10:46] Fase C3 — Sinkronisasi Dokumentasi `MGM` (`sigapp-docs`)
- **Perubahan:**
  - `docs/modules/MGM/MODULE.md`: Disinkronkan via `php spark module:sync MGM` (`100/100 REFACTORED`), mencatat 4 method baru `ReferralRepository`, 2 wrapper baru `ReferralService`, kontrak lintas-modul `MKDT\KonsumenService::upsert()`, pengecualian resmi `PublicReferralController extends ResourceController` (Public CORS non-CSRF), serta entri `Change Log` `[2026-11-22] [REFACTOR]`.
  - `docs/MIGRATION_PROGRESS.md`: Baris #3 **MGM** diperbarui menjadi `` `COMPLETED` `` (`2026-11-22`, bersih 0 temuan).
- **Debugging / Temuan:** `php spark module:deadcode MGM` pada fase sebelumnya memperbarui file cache `writable/modules/deadcode_MGM.json` di working tree (akan diaudit di Fase D4).
- **Verifikasi:** `php spark module:sync MGM` (`[SYNCED]`) dan `git status --short` terisolasi sesuai target.

### [2026-11-22 10:53] Fase D1 — Setting, MenuAccess & Web Controller `SalesExternal` (`sigapp-engineer`)
- **Perubahan:**
  - `app/Controllers/Api/SalesExternal/FeeSalesSettingController.php`: Migrasi `extends BaseController` → `extends BaseApiController`, menambahkan `protected $helpers = ['auth'];` + guard `helper('auth')`, menggunakan `readRequestData()`, dan mempertahankan payload JSON kompatibel `fee_sales_access.php` (`success`, `message` singular, `messages` plural, `data`, `token`).
  - `app/Repositories/SalesExternal/FeeSalesSettingRepository.php` *(baru)*: Query `auth_groups` & `fee_sales_setting_akses` (`listPermissions`, `existsByGroupId`, `upsertGroupPermission`, `getAggregatedPermissionsByGroupIds`).
  - `app/Services/SalesExternal/FeeSalesSettingService.php`: Menghapus 10 akses `$this->db->table()`; mendelegasikan akses `fee_sales_setting_akses`/`auth_groups` ke `FeeSalesSettingRepository` dan akses `auth_groups_users`, `menus`, `menu_roles` ke `Settings\MenuAccessService` (`syncGroupMenus` hanya saat berubah & menjaga proteksi role Admin `id_group = 1`).
  - `app/Services/SalesExternal/SalesExternalService.php` & `app/Controllers/Web/SalesExternal/SalesExternalController.php`: Menambahkan wrapper `getPengajuanGroupDetail()` & `getPengajuanGroupByToken()`, menghapus instansiasi `SalesExternalRepository` langsung dari Web Controller.
  - `app/Libraries/ModuleManager/ModuleCatalog.php`: Mendaftarkan `SalesExternal/FeeSalesSettingRepository.php`.
- **Debugging / Temuan:** `php spark module:map` turut menyegarkan `writable/modules/module_map.json`.
- **Verifikasi:** `php -l` 0 error pada 6 file; grep `->table(` di `FeeSalesSettingService.php` = 0 match; grep `SalesExternalRepository` di `Web\SalesExternalController.php` = 0 match; `php spark module:map` 100%; `php spark module:status` (`SalesExternal REFACTORED 100%`); `FeeSalesWorkflowTest.php` (`Tests: 31, Assertions: 275, 0 failures/errors`).

### [2026-11-22 11:16] Fase D2 — Ekstraksi Query `SalesExternalService` & Kontrak `MKDT` (`sigapp-engineer`)
- **Perubahan:**
  - `app/Repositories/Mkdt/PosisiKonsumenRepository.php`: Constructor diubah menjadi `__construct(?BaseConnection $db = null)` dan menambahkan `updateHargaNetAjb(int $idMkdt, float $hargaNetAjb): bool` (Query Builder langsung tanpa `MkdtModel` agar kompatibel dengan SQLite fixture tanpa kolom `updated_at`).
  - `app/Services/Mkdt/MkdtPricingService.php` *(baru)*: Kontrak publik tipis `updateHargaNetAjb(int $idMkdt, float $hargaNetAjb): bool` yang ikut transaksi pemanggil tanpa `transBegin()` baru.
  - `app/Libraries/ModuleManager/ModuleCatalog.php`: Mendaftarkan `Mkdt/MkdtPricingService.php` pada blok `MKDT`.
  - `app/Repositories/SalesExternal/SalesExternalRepository.php`: Constructor `__construct(?BaseConnection $db = null)` dan menambahkan 27 method kohesif (termasuk helper konteks audit `getFeeSalesContextForAudit()`, `getProyekKodeKeuangan()`, serta `syncActivePengajuanItemNominalByTerminId()` yang portabel di MySQL & SQLite).
  - `app/Services/SalesExternal/SalesExternalService.php`: Memindahkan seluruh 44 pemanggilan `->table()`/`->query()` ke `SalesExternalRepository` dan `MkdtPricingService::updateHargaNetAjb()`; menambahkan helper privat `syncFeeSalesStatusFromTermins()`; mempertahankan `$this->db` hanya untuk manajemen transaksi DB dan meneruskan `$this->db` ke repository & `MkdtPricingService`.
  - `tests/unit/FeeSalesWorkflowTest.php`: Memperbarui L1322 menjadi partial mock `getMockBuilder(SalesExternalRepository::class)->setConstructorArgs([$db])->onlyMethods([...])` agar method repo baru berjalan nyata terhadap SQLite in-memory fixture.
- **Debugging / Temuan:** Tidak ada kegagalan pada run baru setelah reset kuota. Query `UPDATE JOIN` (L1450–1455 lama) diubah menjadi 2-step builder (`SELECT` ID lalu `UPDATE WHERE IN`) agar portabel baik di MySQL produksi maupun SQLite test fixture.
- **Verifikasi:** `php -l` 0 error pada 6 file; grep `->table(`/`->query(` di `SalesExternalService.php` = 0 match; `FeeSalesWorkflowTest.php` (`OK: 31 tests, 275 assertions`); regresi gabungan MKDT + FeeSales (`OK: 47 tests, 347 assertions`); `php spark module:map` 100%; `php spark module:status` (`SalesExternal` & `MKDT` `REFACTORED 100%`).

### [2026-11-22 11:24] Fase D3 — Inspeksi QA Independen `SalesExternal` + Kontrak `MKDT` (`sigapp-qa` → `PASS`)
- **Status Sertifikat:** **`PASS`**
- **Hasil Uji:**
  - Audit `codegraph_explore` & `git grep`: 0 broken callers pada seluruh file target D1 & D2 beserta 7 shim legacy-nya; 0 `->table(`/`->query(` pada `SalesExternalService` & `FeeSalesSettingService`; 0 `SalesExternalRepository` pada `Web\SalesExternalController`; `php -l` 0 error pada 9 file.
  - Playwright E2E (`http://localhost/sigapp.dev`):
    - **MKDT (`citra`):** `/master-sales`, `/fee-sales` (`list-active`, `list-fee`, `fee-detail/371`, `list-group`, `group-detail/4`), `/fee-sales/pengajuan/c6e7eeeeb5ad509cc33a2ff7d9b21f44`, 3 endpoint `/fee-sales/pengajuan-signature/4/{pembuat,mkdt,keuangan}`, `/fee-sales/pengajuan-pdf/4`, `/setting-fee-sales`, `POST /api/setting-fee-sales/list` (`200 OK`, key `['success','message','messages','data','token']`), serta uji negatif non-mutasi `POST /api/setting-fee-sales/save` payload invalid (`400`, `['success','message','messages','token']`) seluruhnya lulus tanpa HTTP 500/JS error.
    - **Keuangan (`nisalis`):** `/fee-sales` (`window.FEE_SALES_PERMISSIONS` akurat, `list-fee`, `list-group`, `group-detail/4`, 3 signature routes) lulus dengan 0 HTTP 500 & 0 console errors/warnings.
  - CLI & Unit Test: `FeeSalesWorkflowTest` (`OK: 31 tests, 275 assertions`), `MkdtOperationServicesTest` (`OK: 6 tests, 55 assertions`), `ReferralRepositoryDateFilterTest` (`OK: 2 tests, 9 assertions`), `php spark routes` utuh, `php spark module:deadcode SalesExternal` & `MKDT` (0 dead code baru), `php spark module:status` (`TiketMasalah`, `MGM`, `SalesExternal`, `MKDT` semua `REFACTORED 100%` & 0 unmapped files).
- **Catatan Pre-Existing Non-Blocking:** 1 PWA manifest icon warning di sesi MKDT; observasi lama `fee_sales_access.php`, `master_sales.php` (`?v=<?= time() ?>`), dan `fee_sales.php` L601 (`#7367f0`) di luar cakupan refactor backend.

### [2026-11-22 11:28] Fase D4 — Sinkronisasi Dokumentasi & Finalisasi (sigapp-docs)
- **Perubahan:**
  - `docs/modules/SalesExternal/MODULE.md`: Disinkronkan via `php spark module:sync SalesExternal` (`100/100 REFACTORED`), diperbarui pada `Invariants`, `Contracts` (penambahan `getPengajuanGroupDetail` & `getPengajuanGroupByToken` pada `SalesExternalService`), `File Inventory` (`FeeSalesSettingRepository` baru + `SalesExternalRepository` 42 public methods [14 awal + 28 baru di L905–1226] + `FeeSalesSettingController` 103 baris), `Key Functions`, `Notes` (Cross-Module Dependencies ke `MKDT\MkdtPricingService`, `Settings\MenuAccessService`, dkk., serta status bersih pasca-remediasi dengan catatan surgical Keputusan #3 Opsi A untuk `Api\SalesExternal\SalesExternalController`), serta entri `Change Log` `[2026-11-22] [REFACTOR]`.
  - `docs/modules/MKDT/MODULE.md`: Disinkronkan via `php spark module:sync MKDT` (`100/100 REFACTORED`, 13 Services), diperbarui pada `Key Functions` (`MkdtPricingService` & `PosisiKonsumenRepository::updateHargaNetAjb`), `Notes` (Cross-Module Callers: `SalesExternalService` serta catatan bahwa status internal `#15 MKDT` tetap `COMPLETED (Catatan Minor)`), dan entri `Change Log` `[2026-11-22] [REFACTOR]`.
  - `docs/MIGRATION_PROGRESS.md`: Baris #4 **SalesExternal** diperbarui menjadi `` `COMPLETED` `` (`2026-11-22`, bersih 0 temuan) dan `1. Rangkuman Progres Global` diselaraskan menjadi `5 COMPLETED` (Bersih) | `10 COMPLETED (Catatan Minor)` | `2 INCOMPLETE` (`Rata-rata Skor Kesiapan: 93.5%`, `15 REFACTORED` dari `17` modul).
  - `docs/modul-notifikasi.md`: Tidak diperlukan pembaruan (0 penambahan/perubahan event `tambah_notif()`).
  - Finalisasi dokumen kerja ini (`Status Keseluruhan: COMPLETED`, seluruh baris Fase A–D4 `DONE`, dan `## 4. Ringkasan File Final` sinkron 100% dengan `git status --short`).
- **Debugging / Temuan:** Tidak ada. Tercatat pada `SalesExternalRepository.php` terdapat 28 method kohesif baru (total 42 public methods dari sebelumnya 14).
- **Verifikasi:** `php spark module:sync SalesExternal --check-only` (`[OK]`), `php spark module:sync MKDT --check-only` (`[OK]`), `php spark module:status` (`15/17 REFACTORED`, `93.5%`; `TiketMasalah`, `MGM`, `SalesExternal`, `MKDT` semua `100%`), dan `git status --short` (25 item terisolasi: 22 `M` + 3 `??`).

---

## 4. Ringkasan File Final
Seluruh perubahan terisolasi dan sinkron 100% dengan output `git status --short` (total 25 entri: 22 `M` + 3 `??`):

1. **Backend Modul `TiketMasalah` (3 `M`):**
   - `M app/Controllers/Api/TiketMasalah/TiketMasalahController.php`
   - `M app/Services/TiketMasalah/TiketMasalahService.php`
   - `M app/Repositories/TiketMasalah/TiketMasalahRepository.php`
2. **Backend Modul `MGM` (4 `M`):**
   - `M app/Controllers/Api/MGM/ReferralController.php`
   - `M app/Services/MGM/ReferralService.php`
   - `M app/Services/MGM/ReferralQrService.php`
   - `M app/Repositories/MGM/ReferralRepository.php`
3. **Backend Modul `SalesExternal` (5 `M`, 1 `??`):**
   - `M app/Controllers/Web/SalesExternal/SalesExternalController.php`
   - `M app/Controllers/Api/SalesExternal/FeeSalesSettingController.php`
   - `M app/Services/SalesExternal/SalesExternalService.php`
   - `M app/Services/SalesExternal/FeeSalesSettingService.php`
   - `M app/Repositories/SalesExternal/SalesExternalRepository.php`
   - `?? app/Repositories/SalesExternal/FeeSalesSettingRepository.php` *(baru)*
4. **Kontrak Tipis `MKDT`, Katalog Modul, & Unit Test (3 `M`, 1 `??`):**
   - `?? app/Services/Mkdt/MkdtPricingService.php` *(baru — kontrak publik tipis `MKDT`)*
   - `M app/Repositories/Mkdt/PosisiKonsumenRepository.php`
   - `M app/Libraries/ModuleManager/ModuleCatalog.php`
   - `M tests/unit/FeeSalesWorkflowTest.php`
5. **Dokumentasi Modul, Ledger Global, & Dokumen Kerja (5 `M`, 1 `??`):**
   - `M docs/modules/TiketMasalah/MODULE.md`
   - `M docs/modules/MGM/MODULE.md`
   - `M docs/modules/SalesExternal/MODULE.md`
   - `M docs/modules/MKDT/MODULE.md`
   - `M docs/MIGRATION_PROGRESS.md`
   - `?? docs/2026-11-22-fix-minor-tiketmasalah-mgm-salesexternal.md`
6. **Cache / Output `php spark module:*` (2 `M`):**
   - `M writable/modules/deadcode_MGM.json` *(diperbarui saat `php spark module:deadcode MGM` pada Fase C2)*
   - `M writable/modules/module_map.json` *(diperbarui saat `php spark module:map` pada Fase D1/D2)*
