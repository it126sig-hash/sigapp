# Module: MGM

**Status:** `REFACTORED`  
**Readiness Score:** `100/100`  
**Last Synced:** `2026-10-12 14:38:40` @ `be98758`  

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
| `GET` | `member-get-member` | `Explicit` | `\App\Controllers\Web\MemberGetMemberController::index` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `list` | `Explicit` | `ReferralController::list` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `subrows` | `Explicit` | `ReferralController::subRows` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `confirm-bonus` | `Explicit` | `ReferralController::confirmBonus` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `update-nominal` | `Explicit` | `ReferralController::updateNominal` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `pay-promosi` | `Explicit` | `ReferralController::payByPromosi` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `submit-keuangan` | `Explicit` | `ReferralController::submitKeuangan` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `mark-cair-keuangan` | `Explicit` | `ReferralController::markCairKeuangan` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `cancel-bonus` | `Explicit` | `ReferralController::cancelBonus` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `update-keterangan` | `Explicit` | `ReferralController::updateKeterangan` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `search-options` | `Explicit` | `ReferralController::searchOptions` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `stages/list` | `Explicit` | `ReferralSettingController::listStages` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `stages/save` | `Explicit` | `ReferralSettingController::saveStage` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `stages/delete` | `Explicit` | `ReferralSettingController::deleteStage` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `preview` | `Explicit` | `ReferralQrController::preview` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `download` | `Explicit` | `ReferralQrController::download` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `check-referral` | `Explicit` | `PublicController::checkReferral` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `ANY` | `/referral_controller/canpromosi` | `AutoRoute` | `ReferralController::canPromosi` | 🟢 MIGRATED | Rute method aktif |
| `ANY` | `/referral_controller/cankeuangan` | `AutoRoute` | `ReferralController::canKeuangan` | 🟢 MIGRATED | Rute method aktif |
| `ANY` | `/referral_controller/canmgmsearch` | `AutoRoute` | `ReferralController::canMgmSearch` | 🟢 MIGRATED | Rute method aktif |
| `ANY` | `/referral_controller/mgmfilters` | `AutoRoute` | `ReferralController::mgmFilters` | 🟢 MIGRATED | Rute method aktif |
| `ANY` | `/referral_controller/cleandate` | `AutoRoute` | `ReferralController::cleanDate` | 🟢 MIGRATED | Rute method aktif |
| `ANY` | `/referral_controller/cleanmoney` | `AutoRoute` | `ReferralController::cleanMoney` | 🟢 MIGRATED | Rute method aktif |
| `ANY` | `/referral_controller/isvalidmgmfile` | `AutoRoute` | `ReferralController::isValidMgmFile` | 🟢 MIGRATED | Rute method aktif |

### B. Public PHP Services (Backend Contract Antar-Modul)
#### `ReferralService.php`
- `validateKodePublic(string $kode)`
- `getSubRowsByReferrer(int $idKonsumenReferrer, int $idProyek, array $filters = [])`
- `generateKodeReferal(int $idKonsumen)`
- `createReferral(int $idMkdtReferred, string $kodeReferal, int $idProyek)`
- `syncReferralForMkdt(int $idMkdtReferred, string $kodeReferal, int $idProyek, string $statusMkdt)`
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
- _dan 14 method lainnya..._
#### `ReferralQrService.php`
- `generate(?string $kodeReferal, ?int $idMkdt, ?int $idProyek)`
- `findReferralData(string $kodeReferal, int $idMkdt, int $idProyek)`
- `buildTargetUrl(string $landingPageUrl, string $kodeReferal)`
- `renderBrandedPng(string $targetUrl, array $data)`
- `drawLogo($canvas, string $logoPath, int $x, int $y, int $maxWidth, int $maxHeight)`
- `drawCenteredText($image, string $text, float $centerX, int $baselineY, int $fontSize, int $color, ?string $fontPath, int $maxWidth = 760)`
- `resolveLogoPath(string $logicalPath)`
- `fontPath(bool $bold)`
- `safeFilename(string $value)`

## File Inventory
### Controllers (5)
- `app/Controllers/Web/MemberGetMemberController.php` (22 baris, 1 public methods)
- `app/Controllers/Api/ReferralController.php` (282 baris, 17 public methods)
- `app/Controllers/Api/ReferralSettingController.php` (57 baris, 3 public methods)
- `app/Controllers/Api/ReferralQrController.php` (80 baris, 2 public methods)
- `app/Controllers/Api/PublicController.php` (54 baris, 1 public methods)

### Services (2)
- `app/Services/ReferralService.php` (738 baris)
- `app/Services/ReferralQrService.php` (251 baris)

### Repositories (1)
- `app/Repositories/ReferralRepository.php` (441 baris)

### Models (4)
- `app/Models/ReferralModel.php`
- `app/Models/ReferralBonusModel.php`
- `app/Models/ReferralBonusStageModel.php`
- `app/Models/ReferralBonusHistoryModel.php`

### Views (1 folder)
- `app/Views/mgm` (4 file)

### JavaScript (1 file)
- `public/assets/js/mgm.js` (1407 baris)

## Readiness Checklist
- [x] Has Service Layer
- [x] Has Repository Layer
- [x] Web & API Controllers Separated
- [x] Explicit Routes Defined
- [x] MODULE.md Initialized

## Dead Code Candidates
_Jalankan `php spark module:deadcode MGM` untuk mengaudit dead code kandidat._

## Notes
- Modul diinisialisasi melalui tooling otomatis.
