# SIGAPP Modular Migration Progress Ledger

> **Dokumen ini adalah sumber kebenaran tunggal (Single Source of Truth) untuk melacak progres migrasi arsitektur modular Backend CI4 lintas percakapan.**
> Setiap kali selesai merefaktor satu modul di sesi chat baru, perbarui baris modul tersebut di dokumen ini dan lakukan git commit.
>
> **Keterkaitan dengan Migrasi Vue 3 (`shadcn-vue`) & Mobile App (Capacitor):**
> - Ruang lingkup produk, arsitektur repo Vue, dan target **80% Parity Cutover** didokumentasikan di [`sigapp-vue/docs/VUE_MOBILE_PRD.md`](file:///c:/Users/Salivs/Data/laragon/www/sigapp-vue/docs/VUE_MOBILE_PRD.md).
> - Progres kesiapan **Wave 0**, status **API Vue-Ready (`RAW_JSON_READY`)**, dan pembangunan UI Vue/Capacitor dilacak secara khusus di [`sigapp-vue/docs/VUE_MIGRATION_PROGRESS.md`](file:///c:/Users/Salivs/Data/laragon/www/sigapp-vue/docs/VUE_MIGRATION_PROGRESS.md).
> - Standar teknis endpoint REST API JSON murni (bebas string HTML DataTables), Bearer Token, dan Signed URL mengacu pada [`sigapp-vue/docs/API_SPA_CONTRACT.md`](file:///c:/Users/Salivs/Data/laragon/www/sigapp-vue/docs/API_SPA_CONTRACT.md).
> - Panduan scaffolding repo terpisah (`sigapp-vue`) mengacu pada [`sigapp-vue/docs/VUE_PROJECT_SCAFFOLDING.md`](file:///c:/Users/Salivs/Data/laragon/www/sigapp-vue/docs/VUE_PROJECT_SCAFFOLDING.md).

---

## 1. Rangkuman Progres Global

* **Total Modul:** 17 *(16 modul awal di ledger + `Home` dari `ModuleCatalog.php`)*
* **Status (Audit `2026-11-22`):** `5 COMPLETED` (Bersih) | `10 COMPLETED (Catatan Minor)` (Struktur Modular `REFACTORED`, ada temuan layering/API/shim/docs) | `2 INCOMPLETE` (`Auth` `MAPPED 60%`, `Home` `MAPPED 40%`)
* **Rata-rata Skor Kesiapan (`php spark module:status`):** `93.5%` (`15 REFACTORED` dari `17` modul)
* **Strategi:** Migrasi modular bertahap (1 modul per sesi *fresh chat*) dengan jaminan **Zero-Downtime**.
* **Laporan Audit Terakhir:** `2026-11-22` — [`docs/2026-11-22-audit-verifikasi-migrasi-modular.md`](2026-11-22-audit-verifikasi-migrasi-modular.md)

---

## 2. Papan Status Modul (Urutan Eksekusi yang Direkomendasikan)

Urutan di bawah ini disusun dari **Modul Daun (Leaf/Paling Terisolasi)** menuju **Modul Inti (Core/Tinggi Keterikatan)** untuk meminimalisasi risiko breaking changes.

> *Catatan Konsolidasi & Prioritas Akselerasi Wave 0 (Vue/Mobile):*
> - **Prioritas Wave 0 Vue/Capacitor:** Modul **#7 `Auth`** dan **#8 `Notifikasi`** diprioritaskan untuk dikerjakan lebih dulu sebagai fondasi infrastruktur **Wave 0** (Bearer Token `DeviceSessionService`, endpoint `/api/auth/me`, dan Push Notification FCM/WebPush) sebelum memulai pembangunan aplikasi di repo Vue. Lihat checklist lengkapnya di [`sigapp-vue/docs/VUE_MIGRATION_PROGRESS.md`](file:///c:/Users/Salivs/Data/laragon/www/sigapp-vue/docs/VUE_MIGRATION_PROGRESS.md).
> - Fitur Pencairan Jaminan (Retensi Perbankan) telah dikonsolidasikan langsung ke dalam sub-domain Pencairan Bank / Akad pada modul **Keuangan** sebagai bagian utuh dari Cash-In.
> - Fitur Master Sales External dan Pengelolaan/Pencairan Fee Sales dikonsolidasikan di bawah modul **SalesExternal**.

| No | Modul | Kategori | Tingkat Kesulitan | Status | Sesi / Commit Hash | Terakhir Diverifikasi | Keterangan / Temuan Audit |
|---|---|---|---|---|---|---|---|
| 1 | **BPB** | Leaf Module | Rendah (Sudah modern Web/Api) | `COMPLETED` | Session Refactor (100% Score) | 2026-11-22 | ✅ Bersih — migrasi modular terverifikasi (Score 100%, 0 temuan) |
| 2 | **TiketMasalah** | Leaf Module | Rendah (Sudah modular Web/Api) | `COMPLETED` | `80a0180` (100% Score) | 2026-11-22 | ✅ Bersih — migrasi modular terverifikasi (Score 100%, 0 temuan; Api\TiketMasalahController extends BaseApiController & 7 query service dipindah ke TiketMasalahRepository) |
| 3 | **MGM** | Leaf Module | Rendah (Sudah modular Web/Api) | `COMPLETED` | Layer Fix (100% Score) | 2026-11-22 | ✅ Bersih — migrasi modular terverifikasi (Score 100%, 0 temuan; query mkdt/konsumen dipindah ke ReferralRepository, write kode_referal delegasi ke MKDT\KonsumenService::upsert(); pengecualian resmi PublicReferralController extends ResourceController non-CSRF terverifikasi) |
| 4 | **SalesExternal** | Intermediate (Master Sales & Fee Sales) | Sedang (Modular Web/Api & Services) | `COMPLETED` | `e238e4a` (100% Score) | 2026-11-22 | ✅ Bersih — migrasi modular terverifikasi (Score 100%, 0 temuan; FeeSalesSettingController extends BaseApiController, Web\SalesExternalController delegasi ke Service, seluruh query & mutasi asing dipindah ke Repository + kontrak Settings\MenuAccessService & MKDT\MkdtPricingService) |
| 5 | **Legal** | Intermediate | Sedang | `COMPLETED (Catatan Minor)` | `e658eba` (90% Score) | 2026-11-22 | ⚠️ [Score 90%] Api\Legal\LegalController::meta() memanggil Database::connect() & query tabel proyek/cluster/jalan langsung (L253-277); MODULE.md sempat drift 100% dan telah disinkronkan |
| 6 | **AuditTrail** | Intermediate | Rendah | `COMPLETED` | Refactor QA-PASS (100% Score) | 2026-11-22 | ✅ Bersih — migrasi modular terverifikasi (Score 100%, 0 temuan) |
| 7 | **Auth** | Infrastructure | Sedang | `INCOMPLETE` | Wave 0 API Bridge (`QA-PASS`) | 2026-11-22 | ❌ [Score 60%] Status masih MAPPED & 9 file masih flat di luar subfolder Auth; AuthController masih db_connect() & query tabel users/auth_groups_users langsung (L38,L101,L162,L178); AuthDeviceController & AdminAuthDeviceController extends BaseController (bukan BaseApiController); DeviceManagementService masih $this->db->table('users') langsung (L23,L111,L131) |
| 8 | **Notifikasi** | Infrastructure | Sedang | `COMPLETED (Catatan Minor)` | Refactor QA-PASS (100% Score) | 2026-11-22 | ⚠️ [Score 100%] Controllers, shims, & routes bersih, namun 7 dari 8 Service masih query/mutasi $this->db->table() langsung (NotifikasiService, NotificationDispatchService, NotificationPreferenceService, NotificationNavigationService, WebPushService, EmailDigestService, GoogleCalendarService) |
| 9 | **Proyek** | Core Master Data | Sedang | `COMPLETED (Catatan Minor)` | `65c937d` (90% Score) | 2026-11-22 | ⚠️ [Score 100%] JalanService query db_connect()->table() langsung (L119-131,L162-163,L204-205,L220-221); TipeService::remove() pakai new KavlingModel() (L344); 5 controller shim fat_legacy (Cluster.php & Tipe.php masih new ProyekModel/KavlingModel) + 3 rute legacy (Routes.php L128,L528,L531); Web controllers dual-mode wantsJson() setJSON() |
| 10 | **Produksi** | Core Construction | Sedang - Tinggi | `COMPLETED (Catatan Minor)` | Refactor QA-PASS (100% Score) | 2026-11-22 | ⚠️ [Score 100%] Web\Produksi\ProduksiController instansiasi new \App\Models\HakaksesModel() langsung (L48) serta panggil ProduksiRepository langsung (L72,L81); Web\ChecklistItemController (L19) & Web\ChecklistSubItemController (L19) panggil ChecklistRepository langsung; Api\Produksi\ProduksiController panggil repo langsung (L51,L112,L222,L259,L271,L290,L308) & render view PDF di getSLFPDF (L276); ProduksiFileService panggil Database::connect()->getFieldNames() di __construct (L36) |
| 11 | **UserManagement** | Core Master Data | Sedang | `COMPLETED (Catatan Minor)` | Session Refactor (100% Score) | 2026-11-22 | ⚠️ [Score 100%] 1 repo non-modular app/Repositories/DivisiRepository.php (unused); 2 shim fat_legacy (Pengguna.php L77-87 & Karyawan.php L72-82 panggil PenggunaRepository langsung); PenggunaService & KaryawanService getDatatable() kembalikan HTML tanpa ?format=raw; MODULE.md sempat kurang seksi Key Functions, Deprecated & Legacy Shims, dan Change Log (telah dilengkapi) |
| 12 | **Settings** | Core System | Sedang | `COMPLETED (Catatan Minor)` | `8442c4e` (100% Score) | 2026-11-22 | ⚠️ [Score 100%] MenuAccessService (L81-220) & SiteplanMenuService (L94-450) masih query/mutasi $this->db->table() langsung; Api\Settings\MenuSettingController panggil MenuRepository langsung (L118,L132,L156,L197,L212,L238); tabel siteplan_menu_items & siteplan_menu_roles belum tercatat di MODULE.md (shim MenuSetting.php terverifikasi thin delegator) |
| 13 | **Siteplan** | Core Spasial UI | Tinggi (Multi-dept JS) | `COMPLETED (Catatan Minor)` | Refactor QA-PASS (100% Score) | 2026-11-22 | ⚠️ [Score 100%] SiteplanService::resolvePrimaryRoleId() query $this->db->table('auth_groups_users') langsung (L932); TargetSiteplanService query/mutasi $this->db->table('target_siteplan_kavling') di L303,L634,L649 & $this->db->table('users') di L1295 serta instansiasi 3 Model tak terpakai di __construct (L31-33) |
| 14 | **Transaksi** | Core Domain | Tinggi | `COMPLETED (Catatan Minor)` | Refactor QA-PASS (100% Score) | 2026-11-22 | ⚠️ [Score 100%] Api\Transaksi\KavlingController (L28) & Api\Transaksi\KavlingRequestController::kavlingList() (L122) panggil KavlingRepository langsung tanpa Service; TransaksiService query $db->table() langsung (proyek L224, mkdt L349,L924,L974, kavling L428) & mutasi tabel lain finance_ledger (L444), file_spptb (L535), konsumen (L668,L939); minor drift MODULE.md (60 vs 61 rute, cancel() belum tercantum di Key Functions) |
| 15 | **MKDT** | Core Domain | Sangat Tinggi (Fat Controller) | `COMPLETED (Catatan Minor)` | `7f0ed7e` (`REFACTORED 100%` — Web/Api/Service/Repo `*\Mkdt\*`, Zero-Downtime Shims & Opsi A; [`MODULE.md`](modules/MKDT/MODULE.md), [`MIGRATION_LOG.md`](modules/MKDT/MIGRATION_LOG.md)) | 2026-11-22 | ⚠️ [Score 100%] MkdtFinancialBreakdownService (L145,L185) & MkdtFinancialReconciliationService (L35,L149-153,L177,L207,L229) query/mutasi $db->table()/SQL langsung; mutasi tabel asing history_log (MkdtFinancialReconciliationService L181) & kavling/finance_ledger (NupService L363,L410); shim fat_legacy Mkdt.php (Opsi A) & 5 rute legacy /promo/* (Routes.php L480,L489-492); $this->db unused di PosisiKonsumenService L29 |
| 16 | **Keuangan** | Core Financial | Sangat Tinggi (Terbesar + Pencairan Akad/Jaminan) | `COMPLETED (Catatan Minor)` | `f21fc2b` (`REFACTORED 100%` — Web/Api/Service/Repo `*\Keuangan\*`, 44 Zero-Downtime Shims & Opsi A; [`MODULE.md`](modules/Keuangan/MODULE.md), [`MIGRATION_LOG.md`](modules/Keuangan/MIGRATION_LOG.md)) | 2026-11-22 | ⚠️ [Score 100%] Shim Keuangan.php masih punya 4 orphan methods dengan db_connect() (L248-368); Web\Keuangan\DireksiController::export_xlsx() return setJSON() (L26-33); 9 Service (KeuanganService, PencairanAkadService, DanaJaminanService, FinanceLedgerService, BankKprDisbursementService, BookingPaymentService, BookingFeeMigrationService, CashoutKavlingService, PrintService) masih ->table()/->query() langsung; mutasi tabel asing mkdt di DanaJaminanService (L74) & KeuanganService (MkdtModel L92,152,1223); pencairan_akad_batch belum terdaftar di owner_tables |
| 17 | **Home** | Core Dashboard (Baru terdeteksi di `ModuleCatalog` & `docs/modules/Home`) | Sedang | `INCOMPLETE` | Belum di-refactor (40% Score) | 2026-11-22 | ❌ [Score 40%] Status masih MAPPED & 2 file masih flat (app/Controllers/Home.php 623 baris, app/Services/DashboardService.php 576 baris) tanpa Repository; Home.php panggil db_connect() (L25), new ProyekModel() (L23), 17x $this->db->table() & campur view()/setJSON(); DashboardService.php query $this->db->table() langsung di 15 titik (L113-539); 5 rute eksplisit + 7 AutoRoute masih ke Home::* |

---

## 3. Protokol Memulai di Fresh Chat (Untuk Agen AI)

Ketika memulai sesi percakapan baru untuk merefaktor satu modul:

1. **Langkah 1 (Baca Konteks Awal):**
   * Baca file ini: `docs/MIGRATION_PROGRESS.md` untuk konfirmasi status antrean.
   * Baca dokumen spesifik modul: `docs/modules/<NamaModul>/MODULE.md`.
   * Baca aturan migrasi: `.agents/rules/sigapp-modular-migration.md`.
2. **Langkah 2 (Fokus Wilayah):**
   * Agen **HANYA** diizinkan memodifikasi file yang terdaftar di `File Inventory` milik modul tersebut.
   * Dilarang mengubah file modul lain tanpa izin eksplisit.
   * **Aturan Shared Views/Modals & Routes:**
     * File rute global `app/Config/Routes.php` **boleh dimodifikasi** untuk mendaftarkan rute eksplisit modul target (dengan tetap mempertahankan rute lama sebagai shim/fallback).
     * Untuk folder view bersama (`app/Views/master/`, `app/Views/siteplan/partials/`, `app/Views/setting/`), agen hanya boleh mengubah partial/view yang spesifik milik modul target tanpa merusak partial departemen lain.
3. **Langkah 3 (Selesaikan & Verifikasi):**
   * Pisahkan controller ke `Web/<Modul>/` & `Api/<Modul>/`, serta pindahkan Service ke `Services/<Modul>/` dan Repository ke `Repositories/<Modul>/`.
   * Pastikan endpoint dan class lama tetap hidup sebagai fallback (*Zero-Downtime Shims*) dan terdaftar di `ModuleCatalog.php`.
   * Uji antarmuka modul menggunakan Playwright.
   * Jalankan `php spark module:sync <NamaModul>` untuk memperbarui `MODULE.md`.
   * Update status modul di tabel di atas menjadi `COMPLETED` beserta commit hash dan skor kesiapan.
