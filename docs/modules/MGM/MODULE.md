# Module: MGM

**Status:** `REFACTORED`  
**Readiness Score:** `100/100`  
**Last Synced:** `2026-11-22 10:44:27` @ `e374c49`  

## Purpose
Member Get Member: program referral konsumen, pembuatan link/QR publik, serta approval dan pencairan komisi.

## Out of scope
- Hal-hal di luar tanggung jawab domain MGM.

## Invariants
- Modul lain tidak boleh memanipulasi tabel pemilik secara langsung.
- Perubahan finansial/status harus diverifikasi di level Service.

## Database Tables
### Owner Table(s)
- `referrals` *(akses tulis eksklusif hanya untuk modul MGM)*
- `referral_bonuses` *(akses tulis eksklusif hanya untuk modul MGM)*
- `referral_bonus_stages` *(akses tulis eksklusif hanya untuk modul MGM)*
- `referral_bonus_histories` *(akses tulis eksklusif hanya untuk modul MGM)*

### Shared Tables (Dibaca dari modul lain)
- `konsumen`
- `mkdt`
- `keuangan`

## Contracts
### A. Public HTTP Endpoints & Routes Inventory
| Method | URI / Pattern | Tipe | Handler | Status Migrasi | Target API / Catatan |
|---|---|---|---|---|---|
| `GET` | `/member-get-member` | `Explicit` | `\App\Controllers\Web\MGM\MemberGetMemberController::index` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/mgm/list` | `Explicit` | `ReferralController::list` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/mgm/subrows` | `Explicit` | `ReferralController::subRows` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/mgm/confirm-bonus` | `Explicit` | `ReferralController::confirmBonus` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/mgm/update-nominal` | `Explicit` | `ReferralController::updateNominal` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/mgm/pay-promosi` | `Explicit` | `ReferralController::payByPromosi` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/mgm/submit-keuangan` | `Explicit` | `ReferralController::submitKeuangan` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/mgm/mark-cair-keuangan` | `Explicit` | `ReferralController::markCairKeuangan` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/mgm/cancel-bonus` | `Explicit` | `ReferralController::cancelBonus` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/mgm/update-keterangan` | `Explicit` | `ReferralController::updateKeterangan` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/mgm/search-options` | `Explicit` | `ReferralController::searchOptions` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/mgm/stages/list` | `Explicit` | `ReferralSettingController::listStages` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/mgm/stages/save` | `Explicit` | `ReferralSettingController::saveStage` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/mgm/stages/delete` | `Explicit` | `ReferralSettingController::deleteStage` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/referral-qr/preview` | `Explicit` | `ReferralQrController::preview` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/api/referral-qr/download` | `Explicit` | `ReferralQrController::download` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/public/check-referral` | `Explicit` | `PublicReferralController::checkReferral` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |

### B. Public PHP Services (Backend Contract Antar-Modul)
#### `ReferralService.php`
- `getListMGM(int $idProyek, array $filters = [])`
- `searchReferrerOptions(string $search, int $idProyek)`
- `validateKodePublic(string $kode)`
- `getSubRowsByReferrer(int $idKonsumenReferrer, int $idProyek, array $filters = [])`
- `generateKodeReferal(int $idKonsumen)`
- `createReferral(int $idMkdtReferred, string $kodeReferal, int $idProyek)`
- `syncReferralForMkdt(int $idMkdtReferred, string $kodeReferal, int $idProyek, string $statusMkdt)`
- `getStages(int $idProyek)`
- `saveStage(array $payload, ?int $id = null)`
- `deleteStage(int $id)`
- `checkAndActivateBonuses(int $idMkdt, string $newStatusMkdt)`
- `confirmBonus(int $idReferralBonus, ?float $nominalOverride)`
- `updateKeterangan(int $idReferralBonus, string $keterangan)`
- `updateNominalBonus(int $idReferralBonus, float $nominalBonus, ?string $keterangan = null)`
- `payByPromosi(int $idReferralBonus, string $buktiPath, string $tanggalPembayaran, string $namaPenerima, string $noRekening, string $bankPenerima, ?string $keterangan = null)`
- `submitToKeuangan(int $idReferralBonus, ?float $nominalPengajuan, string $tanggalSpp, ?string $buktiPath = null, ?string $keterangan = null)`
- `markCairKeuangan(int $idReferralBonus, string $tanggalCair, ?float $nominalCair, string $buktiPath, string $namaPenerima, string $noRekening, string $bankPencairan, ?string $keterangan = null)`
- `syncFromKeuangan(int $idPengajuan, string $statusKeuangan, float $nominalCair)`
- `markSelesai(int $idReferralBonus)`
- `cancelBonus(int $idReferralBonus, string $keterangan)`
- `getCashoutBonusesByKavling(int $idKavling)`
- `notifyMgmBonusEligible(int $idReferralBonus)`
#### `ReferralQrService.php`
- `generate(?string $kodeReferal, ?int $idMkdt, ?int $idProyek)`

## File Inventory
### Controllers (5)
- `app/Controllers/Web/MGM/MemberGetMemberController.php` (22 baris, 1 public methods: `index()`)
- `app/Controllers/Api/MGM/ReferralController.php` (287 baris, 10 public methods: `list()`, `subRows()`, `confirmBonus()`, `payByPromosi()`, `updateNominal()`, `submitKeuangan()`, `markCairKeuangan()`, `cancelBonus()`, `updateKeterangan()`, `searchOptions()`)
- `app/Controllers/Api/MGM/ReferralSettingController.php` (59 baris, 3 public methods: `listStages()`, `saveStage()`, `deleteStage()`)
- `app/Controllers/Api/MGM/ReferralQrController.php` (81 baris, 2 public methods: `preview()`, `download()`)
- `app/Controllers/Api/MGM/PublicReferralController.php` (54 baris, 1 public methods: `checkReferral()`)

### Services (2)
- `app/Services/MGM/ReferralService.php` (911 baris, 22 public methods)
- `app/Services/MGM/ReferralQrService.php` (220 baris, 1 public methods)

### Repositories (1)
- `app/Repositories/MGM/ReferralRepository.php` (547 baris, 17 public methods: `getListMGM(int $idProyek, array $filters = [])`, `getSubRowsByReferrer(int $idKonsumenReferrer, int $idProyek, array $filters = [])`, `findByKodeReferal(string $kode, int $idProyek)`, `getReferralByMkdt(int $idMkdt)`, `getMkdtReferralContext(int $idMkdt)`, `getBonusStagesByProyek(int $idProyek)`, `getAllBonusStagesByProyek(int $idProyek)`, `saveBonusStage(array $data, ?int $id = null)`, `deleteBonusStage(int $id)`, `getMgmNotificationContextByBonus(int $idBonus)`, `findPublicByKode(string $kode)`, `searchReferrerOptions(string $search, int $idProyek)`, `getCashoutBonusesByKavling(int $idKavling)`, `findQrReferralData(string $kodeReferal, int $idMkdt, int $idProyek)`, `findKonsumenKodeReferal(int $idKonsumen)`, `isKodeReferalExists(string $kode)`, `hasHistoryTable()`)

### Models (4)
- `app/Models/ReferralModel.php`
- `app/Models/ReferralBonusModel.php`
- `app/Models/ReferralBonusStageModel.php`
- `app/Models/ReferralBonusHistoryModel.php`

### Views (1 folder)
- `app/Views/mgm` (4 file: `index.php`, `modal_bonus_action.php`, `modal_detail_pencairan.php`, `modal_setting_stages.php`)

### JavaScript (1 file)
- `public/assets/js/mgm.js` (1407 baris)

### Zero-Downtime Shims (8 file)
- `app/Controllers/Web/MemberGetMemberController.php`
- `app/Controllers/Api/ReferralController.php`
- `app/Controllers/Api/ReferralQrController.php`
- `app/Controllers/Api/ReferralSettingController.php`
- `app/Controllers/Api/PublicController.php`
- `app/Services/ReferralService.php`
- `app/Services/ReferralQrService.php`
- `app/Repositories/ReferralRepository.php`

## Key Functions
- **Web Controller (`App\Controllers\Web\MGM\MemberGetMemberController`):**
  - `index()`: Merender halaman utama Member Get Member (`mgm/index`) melalui `template`.
- **API Controllers (`App\Controllers\Api\MGM\*`):**
  - `ReferralController` (extends `BaseApiController`): Menangani endpoint terautentikasi `/api/mgm/*` melalui `ReferralService`, meliputi `list()` (mendelegasikan ke `getListMGM()`), `subRows()`, `confirmBonus()`, `updateNominal()`, `payByPromosi()`, `submitKeuangan()`, `markCairKeuangan()`, `cancelBonus()`, `updateKeterangan()`, dan `searchOptions()` (mendelegasikan ke `searchReferrerOptions()` sambil mempertahankan top-level key `results` untuk Select2).
  - `ReferralSettingController` (extends `BaseApiController`): Mengelola master tahapan bonus proyek (`/api/mgm/stages/list`, `save`, `delete`) melalui `ReferralService`.
  - `ReferralQrController` (extends `BaseApiController`): Melayani `/api/referral-qr/preview` dan `/api/referral-qr/download` melalui `ReferralQrService::generate()`.
  - `PublicReferralController` (extends `CodeIgniter\RESTful\ResourceController` — **Pengecualian Resmi Non-CSRF**): Melayani endpoint publik tanpa autentikasi `POST /api/public/check-referral` (`checkReferral()`) dengan filter `throttle:20,60` dan `cors`, mengembalikan payload JSON dengan field `message` tanpa `csrf_hash()`.
- **Service (`app/Services/MGM/ReferralService.php`):**
  - `getListMGM(int $idProyek, array $filters = [])`, `searchReferrerOptions(string $search, int $idProyek)`, `getSubRowsByReferrer(int $idKonsumenReferrer, int $idProyek, array $filters = [])`: Pengambilan daftar referrer, opsi Select2, dan sub-rows bonus beserta signed file URL aman via `FileAccessService`.
  - `validateKodePublic(string $kode)`: Validasi kode referal untuk endpoint publik tanpa sesi login.
  - `generateKodeReferal(int $idKonsumen)`: Menghasilkan/mengambil kode referal unik konsumen; pembacaan melalui `ReferralRepository` (`findKonsumenKodeReferal()`, `isKodeReferalExists()`) dan penulisan `konsumen.kode_referal` didelegasikan ke kontrak lintas modul `App\Services\Mkdt\KonsumenService::upsert(?int $idKonsumen, array $data): ?int` tanpa membuka transaksi DB baru.
  - `syncReferralForMkdt(int $idMkdtReferred, string $kodeReferal, int $idProyek, string $statusMkdt)`, `checkAndActivateBonuses(int $idMkdt, string $newStatusMkdt)`, `notifyMgmBonusEligible(int $idReferralBonus)`: Sinkronisasi referral transaksi MKDT, aktivasi tahapan bonus eligible dalam transaksi DB, serta pengiriman notifikasi lintas divisi via `NotifikasiService`.
  - `getStages(int $idProyek)`, `saveStage(array $payload, ?int $id = null)`, `deleteStage(int $id)`: Pengaturan master tahapan bonus per proyek.
  - `confirmBonus(int $idReferralBonus, ?float $nominalOverride)`, `updateNominalBonus(int $idReferralBonus, float $nominalBonus, ?string $keterangan = null)`, `payByPromosi(...)`, `submitToKeuangan(...)`, `markCairKeuangan(...)`, `cancelBonus(int $idReferralBonus, string $keterangan)`, `updateKeterangan(int $idReferralBonus, string $keterangan)`: Alur approval, pengajuan SPP ke Keuangan, pencairan Keuangan/Promosi dalam transaksi DB (`transStart`/`transComplete`/`transStatus`), pencatatan riwayat ke `referral_bonus_histories`, dan notifikasi lintas divisi.
  - `getCashoutBonusesByKavling(int $idKavling)`: Kontrak layanan untuk perhitungan komponen cashout MGM per kavling.
- **Service (`app/Services/MGM/ReferralQrService.php`):**
  - `generate(?string $kodeReferal, ?int $idMkdt, ?int $idProyek)`: Pengambilan konteks referal melalui `ReferralRepository::findQrReferralData()`, penyusunan target URL landing page, dan rendering PNG QR Code bermerek proyek via `SimpleQrCode` & GD.
- **Repository (`app/Repositories/MGM/ReferralRepository.php`):**
  - `getListMGM(int $idProyek, array $filters = [])`, `getSubRowsByReferrer(int $idKonsumenReferrer, int $idProyek, array $filters = [])`, `searchReferrerOptions(string $search, int $idProyek)`: Query agregasi daftar referrer, sub-rows bonus dengan filter status/tanggal, serta pencarian opsi Select2.
  - `findByKodeReferal(string $kode, int $idProyek)`, `getReferralByMkdt(int $idMkdt)`, `getMkdtReferralContext(int $idMkdt)`, `findPublicByKode(string $kode)`: Lookup relasi referral/MKDT dan validasi kode referal publik.
  - `getBonusStagesByProyek(int $idProyek)`, `getAllBonusStagesByProyek(int $idProyek)`, `saveBonusStage(array $data, ?int $id = null)`, `deleteBonusStage(int $id)`: Akses data dan pemeliharaan master tahapan bonus.
  - `getMgmNotificationContextByBonus(int $idBonus)`, `getCashoutBonusesByKavling(int $idKavling)`: Konteks notifikasi bonus dan agregasi pencairan bonus Keuangan per kavling.
  - `findQrReferralData(string $kodeReferal, int $idMkdt, int $idProyek)`, `findKonsumenKodeReferal(int $idKonsumen)`, `isKodeReferalExists(string $kode)`, `hasHistoryTable()`: Lookup read-only konteks QR proyek/konsumen, kode referal konsumen, keunikan kode referal, dan pengecekan eksistensi tabel `referral_bonus_histories`.
- **Models (`ReferralModel`, `ReferralBonusModel`, `ReferralBonusStageModel`, `ReferralBonusHistoryModel`):**
  - Definisi entitas dan akses tulis eksklusif ke tabel pemilik (`referrals`, `referral_bonuses`, `referral_bonus_stages`, `referral_bonus_histories`).

## Deprecated & Legacy Shims
- **Deprecated Routes / Endpoints:**
  - Tidak ada rute legacy/deprecated; seluruh 17 rute di `app/Config/Routes.php` (baris 879–882, 1023, 1026–1041, dan 1056–1072) eksplisit mengarah ke controller modular (`\App\Controllers\Web\MGM\MemberGetMemberController` & `\App\Controllers\Api\MGM\*`).
- **Deprecated Classes / Shims (Zero-Downtime Shims — 8 file):**
  - `app/Controllers/Web/MemberGetMemberController.php` — **DEPRECATED (Zero-Downtime Shim)**: Dipertahankan untuk kompatibilitas mundur; gunakan `App\Controllers\Web\MGM\MemberGetMemberController`.
  - `app/Controllers/Api/ReferralController.php` — **DEPRECATED (Zero-Downtime Shim)**: Dipertahankan untuk kompatibilitas mundur; gunakan `App\Controllers\Api\MGM\ReferralController`.
  - `app/Controllers/Api/ReferralQrController.php` — **DEPRECATED (Zero-Downtime Shim)**: Dipertahankan untuk kompatibilitas mundur; gunakan `App\Controllers\Api\MGM\ReferralQrController`.
  - `app/Controllers/Api/ReferralSettingController.php` — **DEPRECATED (Zero-Downtime Shim)**: Dipertahankan untuk kompatibilitas mundur; gunakan `App\Controllers\Api\MGM\ReferralSettingController`.
  - `app/Controllers/Api/PublicController.php` — **DEPRECATED (Zero-Downtime Shim)**: Dipertahankan untuk kompatibilitas mundur; gunakan `App\Controllers\Api\MGM\PublicReferralController`.
  - `app/Services/ReferralService.php` — **DEPRECATED (Zero-Downtime Shim)**: Dipertahankan untuk kompatibilitas mundur; gunakan `App\Services\MGM\ReferralService`.
  - `app/Services/ReferralQrService.php` — **DEPRECATED (Zero-Downtime Shim)**: Dipertahankan untuk kompatibilitas mundur; gunakan `App\Services\MGM\ReferralQrService`.
  - `app/Repositories/ReferralRepository.php` — **DEPRECATED (Zero-Downtime Shim)**: Dipertahankan untuk kompatibilitas mundur; gunakan `App\Repositories\MGM\ReferralRepository`.
- **Deprecated Methods:**
  - `App\Services\MGM\ReferralService::createReferral(int $idMkdtReferred, string $kodeReferal, int $idProyek)` — **DEPRECATED**, gunakan `syncReferralForMkdt()` secara langsung.
  - `App\Services\MGM\ReferralService::syncFromKeuangan(int $idPengajuan, string $statusKeuangan, float $nominalCair)` — **DEPRECATED**, dipertahankan untuk kompatibilitas mundur.
  - `App\Services\MGM\ReferralService::markSelesai(int $idReferralBonus)` — **DEPRECATED**, dipertahankan untuk kompatibilitas mundur.
  - *(Catatan)*: Private method `App\Controllers\Api\MGM\ReferralController::canMgmSearch()` merupakan *pre-existing unused helper* (0 callers) dan tetap tidak diubah.

## Readiness Checklist
- [x] Has Service Layer
- [x] Has Repository Layer
- [x] Web & API Controllers Separated
- [x] Explicit Routes Defined
- [x] Zero Controller Layer Violations
- [x] MODULE.md Initialized

## Dead Code Candidates
_Jalankan `php spark module:deadcode MGM` untuk mengaudit dead code kandidat._

## Notes
- Modul diinisialisasi melalui tooling otomatis dan telah direfaktor ke struktur modular resmi (`Web/MGM`, `Api/MGM`, `Services/MGM`, `Repositories/MGM`).
- Seluruh class lama dipertahankan sebagai 8 *Zero-Downtime Shims* (`thin_shim`).
- **Cross-Module Callers & Contract Registry (Pemanggil & Kontrak Lintas Modul):**
  - **A. Kontrak yang Disediakan MGM (Inbound Callers / Consumers):**
    - **1. Modul Transaksi (`App\Services\Transaksi\TransaksiService`):** Memanggil `ReferralService::generateKodeReferal(int $idKonsumen)`, `ReferralService::syncReferralForMkdt(int $idMkdtReferred, string $kodeReferal, int $idProyek, string $statusMkdt)`, `ReferralService::checkAndActivateBonuses(int $idMkdt, string $newStatusMkdt)`, dan `ReferralService::notifyMgmBonusEligible(int $idReferralBonus)` pada `saveTransaksi()`, `replaceConsumer()`, dan `saveStatus()` (melalui shim `App\Services\ReferralService`).
    - **2. Modul MKDT / NUP (`App\Services\Mkdt\NupService`):** Memanggil `App\Services\MGM\ReferralService::generateKodeReferal(int $idKonsumen)` pada `NupService::save()`.
    - **3. Modul Keuangan (`App\Repositories\Keuangan\Cashout\CashoutKavlingRepo`):** Menyediakan kontrak `App\Services\MGM\ReferralService::getCashoutBonusesByKavling(int $idKavling)` untuk perhitungan cashout per kavling.
    - **4. Modul MKDT / Posisi Konsumen & Siteplan UI (`App\Services\Mkdt\PosisiKonsumenService` & `app/Views/siteplan/partials/modal_detail.php`):** Mengonsumsi endpoint `POST /api/referral-qr/preview` dan `GET /api/referral-qr/download` yang ditangani oleh `App\Controllers\Api\MGM\ReferralQrController`.
    - **5. CLI Maintenance Command (`App\Commands\MgmRepairBonuses`):** Memanggil `ReferralService::checkAndActivateBonuses(int $idMkdtReferred, string $statusMkdt)` pada `php spark mgm:repair-bonuses` (melalui shim `App\Services\ReferralService`).
    - **6. Landing Page Eksternal (Public API):** Mengonsumsi endpoint `POST /api/public/check-referral` yang ditangani oleh `App\Controllers\Api\MGM\PublicReferralController::checkReferral`.
  - **B. Kontrak Lintas-Modul yang Dikonsumsi MGM (Outbound Dependencies):**
    - **1. MGM -> MKDT (`App\Services\Mkdt\KonsumenService::upsert(?int $idKonsumen, array $data): ?int`):** `App\Services\MGM\ReferralService::generateKodeReferal(int $idKonsumen)` mendelegasikan mutasi kolom `konsumen.kode_referal` ke layanan pemilik domain `MKDT` tanpa membuka transaksi DB baru dan tanpa mengakses `KonsumenModel` secara langsung.

### Audit Verifikasi Migrasi (2026-11-22)
- **Status Ledger:** `COMPLETED` (Status modul `REFACTORED`, Readiness Score `100/100`, `0` temuan aktif; 17 rute eksplisit modular, `0` legacy/autoroute, dan 8 `thin_shim` bersih).
- **Terverifikasi Bersih (Pasca Remediasi 2026-11-22):**
  - `app/Controllers/Api/MGM/ReferralController.php` tidak lagi menginstansiasi `ReferralRepository` secara langsung; `list()` dan `searchOptions()` mendelegasikan ke `ReferralService` (`getListMGM()`, `searchReferrerOptions()`) dengan tetap mempertahankan top-level key `results` untuk Select2.
  - `app/Services/MGM/ReferralQrService.php` bersih dari `$this->db` dan query builder langsung; seluruh lookup QR didelegasikan ke `ReferralRepository::findQrReferralData()`.
  - `app/Services/MGM/ReferralService.php` bersih dari `KonsumenModel`, query tabel `konsumen` langsung, serta `Database::connect()->tableExists()`; pembacaan didelegasikan ke `ReferralRepository` (`findKonsumenKodeReferal()`, `isKodeReferalExists()`, `hasHistoryTable()`) dan penulisan `konsumen.kode_referal` didelegasikan ke `App\Services\Mkdt\KonsumenService::upsert()`. Penggunaan `\Config\Database::connect()` yang tersisa di `ReferralService` murni untuk manajemen transaksi DB (`transStart`/`transComplete`/`transStatus`).
- **Pengecualian Resmi Terverifikasi — `app/Controllers/Api/MGM/PublicReferralController.php`:** Tetap mewarisi `CodeIgniter\RESTful\ResourceController` (bukan `BaseApiController`) secara disengaja karena endpoint publik `POST /api/public/check-referral` berjalan tanpa sesi login (dilindungi filter `throttle:20,60` dan `cors`) dan mengembalikan respons JSON (`success`, `message`/`data`) tanpa menyertakan `csrf_hash()`.

## Change Log
- **`2026-11-22` [`REFACTOR`] — Remediasi Temuan Minor Audit Migrasi Modular (Isolasi Query Repository, Delegasi Kontrak MKDT, & Thin Controller):**
  - **Routes & Public Contracts:** Rute `/member-get-member`, `/api/mgm/*`, `/api/referral-qr/*`, dan `/api/public/check-referral` tetap Zero-Downtime 100%. Menambahkan 2 public service method pada `App\Services\MGM\ReferralService` (`getListMGM(int $idProyek, array $filters = []): array` dan `searchReferrerOptions(string $search, int $idProyek): array`) serta mengonsumsi kontrak lintas modul `App\Services\Mkdt\KonsumenService::upsert(?int $idKonsumen, array $data): ?int` dari `ReferralService::generateKodeReferal()`. Pengecualian resmi `PublicReferralController extends ResourceController` (non-CSRF/CORS) tetap dipertahankan.
  - **Controller / Service / Repo / Model:**
    - `app/Repositories/MGM/ReferralRepository.php`: Menambahkan 4 method read-only (`findQrReferralData`, `findKonsumenKodeReferal`, `isKodeReferalExists`, `hasHistoryTable`) tanpa meng-override `__construct()` `CodeIgniter\Model`.
    - `app/Services/MGM/ReferralQrService.php`: Menghapus `$this->db`, `BaseConnection`, dan `findReferralData()`; mendelegasikan lookup data QR ke `ReferralRepository::findQrReferralData()`.
    - `app/Services/MGM/ReferralService.php`: Menghapus `KonsumenModel`, query tabel `konsumen` langsung, dan `Database::connect()->tableExists()`; mendelegasikan pembacaan ke `ReferralRepository` dan penulisan `konsumen.kode_referal` ke `App\Services\Mkdt\KonsumenService::upsert()` tanpa membuka transaksi DB baru; menambahkan thin wrapper `getListMGM()` dan `searchReferrerOptions()`.
    - `app/Controllers/Api/MGM/ReferralController.php`: Menghapus instansiasi `ReferralRepository` langsung pada `list()` dan `searchOptions()` dan mendelegasikan ke `$this->service` sambil mempertahankan top-level key `results` untuk Select2.
  - **View / CSS / JS:** Tidak ada perubahan.
  - **Deprecations:** Tidak ada deprecations baru; 8 thin shims dan 3 method `@deprecated` di `ReferralService` (`createReferral`, `syncFromKeuangan`, `markSelesai`) tetap dipertahankan untuk kompatibilitas mundur, sedangkan private method `ReferralController::canMgmSearch()` merupakan pre-existing helper dengan 0 callers.
- `2026-11-22` [`AUDIT`] — Audit verifikasi migrasi modular: `COMPLETED (Catatan Minor)` (Score 100%, temuan query DB langsung di `ReferralQrService` L17/L69-95 dan `ReferralService` L101/L109; `PublicReferralController` ekstensi `ResourceController` terverifikasi sebagai pengecualian publik non-CSRF).
- `2026-11-12` [`SYNC`] — Sinkronisasi status & inventori modul melalui `module:sync`.
