# Module: MGM

**Status:** `REFACTORED`  
**Readiness Score:** `100/100`  
**Last Synced:** `2026-11-12 13:40:16` @ `a68829a`  

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
- `app/Controllers/Api/MGM/ReferralController.php` (283 baris, 10 public methods: `list()`, `subRows()`, `confirmBonus()`, `payByPromosi()`, `updateNominal()`, `submitKeuangan()`, `markCairKeuangan()`, `cancelBonus()`, `updateKeterangan()`, `searchOptions()`)
- `app/Controllers/Api/MGM/ReferralSettingController.php` (59 baris, 3 public methods: `listStages()`, `saveStage()`, `deleteStage()`)
- `app/Controllers/Api/MGM/ReferralQrController.php` (81 baris, 2 public methods: `preview()`, `download()`)
- `app/Controllers/Api/MGM/PublicReferralController.php` (54 baris, 1 public methods: `checkReferral()`)

### Services (2)
- `app/Services/MGM/ReferralService.php` (890 baris, 20 public methods)
- `app/Services/MGM/ReferralQrService.php` (251 baris, 1 public methods)

### Repositories (1)
- `app/Repositories/MGM/ReferralRepository.php` (488 baris, 13 public methods: `getListMGM(int $idProyek, array $filters = [])`, `getSubRowsByReferrer(int $idKonsumenReferrer, int $idProyek, array $filters = [])`, `findByKodeReferal(string $kode, int $idProyek)`, `getReferralByMkdt(int $idMkdt)`, `getMkdtReferralContext(int $idMkdt)`, `getBonusStagesByProyek(int $idProyek)`, `getAllBonusStagesByProyek(int $idProyek)`, `saveBonusStage(array $data, ?int $id = null)`, `deleteBonusStage(int $id)`, `getMgmNotificationContextByBonus(int $idBonus)`, `findPublicByKode(string $kode)`, `searchReferrerOptions(string $search, int $idProyek)`, `getCashoutBonusesByKavling(int $idKavling)`)

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
- **Service (`app/Services/MGM/ReferralService.php`):** `validateKodePublic(string $kode)`, `getSubRowsByReferrer(int $idKonsumenReferrer, int $idProyek, array $filters = [])`, `generateKodeReferal(int $idKonsumen)`, `createReferral(int $idMkdtReferred, string $kodeReferal, int $idProyek)`, `syncReferralForMkdt(int $idMkdtReferred, string $kodeReferal, int $idProyek, string $statusMkdt)`
- **Service (`app/Services/MGM/ReferralQrService.php`):** `generate(?string $kodeReferal, ?int $idMkdt, ?int $idProyek)`
- **Repository (`app/Repositories/MGM/ReferralRepository.php`):** `getListMGM(int $idProyek, array $filters = [])`, `getSubRowsByReferrer(int $idKonsumenReferrer, int $idProyek, array $filters = [])`, `findByKodeReferal(string $kode, int $idProyek)`, `getReferralByMkdt(int $idMkdt)`, `getMkdtReferralContext(int $idMkdt)`

## Deprecated & Legacy Shims
- `app/Controllers/Web/MemberGetMemberController.php` — **DEPRECATED (Zero-Downtime Shim)**: Dipertahankan untuk kompatibilitas mundur; gunakan class modular di namespace `MGM`.
- `app/Controllers/Api/ReferralController.php` — **DEPRECATED (Zero-Downtime Shim)**: Dipertahankan untuk kompatibilitas mundur; gunakan class modular di namespace `MGM`.
- `app/Controllers/Api/ReferralQrController.php` — **DEPRECATED (Zero-Downtime Shim)**: Dipertahankan untuk kompatibilitas mundur; gunakan class modular di namespace `MGM`.
- `app/Controllers/Api/ReferralSettingController.php` — **DEPRECATED (Zero-Downtime Shim)**: Dipertahankan untuk kompatibilitas mundur; gunakan class modular di namespace `MGM`.
- `app/Controllers/Api/PublicController.php` — **DEPRECATED (Zero-Downtime Shim)**: Dipertahankan untuk kompatibilitas mundur; gunakan class modular di namespace `MGM`.
- `app/Services/ReferralService.php` — **DEPRECATED (Zero-Downtime Shim)**: Dipertahankan untuk kompatibilitas mundur; gunakan class modular di namespace `MGM`.
- `app/Services/ReferralQrService.php` — **DEPRECATED (Zero-Downtime Shim)**: Dipertahankan untuk kompatibilitas mundur; gunakan class modular di namespace `MGM`.
- `app/Repositories/ReferralRepository.php` — **DEPRECATED (Zero-Downtime Shim)**: Dipertahankan untuk kompatibilitas mundur; gunakan class modular di namespace `MGM`.

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
- Seluruh rute lama dan class lama dipertahankan sebagai *Zero-Downtime Shims*.
- **Cross-Module Callers & Contract Registry (Pemanggil Lintas Modul):**
  - **1. Modul Transaksi & MKDT (`App\Services\TransaksiService`):** Memanggil `ReferralService::syncReferralForMkdt(int $idMkdtReferred, string $kodeReferal, int $idProyek, string $statusMkdt)` pada `saveTransaksi()` dan `saveStatus()` melalui shim `App\Services\ReferralService`.
  - **2. Modul MKDT / NUP (`App\Services\NupService`):** Memanggil `ReferralService::generateKodeReferal(int $idKonsumen)` pada `NupService::save()` melalui shim `App\Services\ReferralService`.
  - **3. Modul Keuangan (`App\Repositories\Keuangan\Cashout\CashoutKavlingRepo`):** Menyediakan kontrak `App\Services\MGM\ReferralService::getCashoutBonusesByKavling(int $idKavling)` untuk perhitungan cashout per kavling.
  - **4. Modul MKDT / Posisi Konsumen & Siteplan UI (`PosisiKonsumenService` & `modal_detail.php`):** Mengonsumsi endpoint `POST /api/referral-qr/preview` dan `GET /api/referral-qr/download` yang ditangani oleh `App\Controllers\Api\MGM\ReferralQrController`.
  - **5. CLI Maintenance Command (`App\Commands\MgmRepairBonuses`):** Memanggil `ReferralService::checkAndActivateBonuses(int $idMkdtReferred, string $statusMkdt)` pada `php spark mgm:repair-bonuses`.

## Change Log
- `2026-11-12` [`SYNC`] — Sinkronisasi status & inventori modul melalui `module:sync`.
