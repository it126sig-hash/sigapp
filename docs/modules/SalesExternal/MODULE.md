# Module: SalesExternal (Master Sales External & Pencairan Fee Sales)

**Status:** `ACTIVE / COMPLETED`  
**Readiness Score:** `100/100`  
**Last Synced:** `2026-10-14`

## Purpose
Pengelolaan Master Data Sales External (agen/broker properti independen), perjanjian kerjasama (PKS), tarif komisi (subsidi & komersil), riwayat audit tarif, integrasi transaksi konsumen MKDT, serta tracking dan pencairan Fee Sales bertahap (skema 100% atau termin 30%-60%-10%) dengan pengajuan SPP Umum dan konfirmasi pencairan.

## Out of scope
- Komisi/gaji sales internal/in-house (dikelola oleh payroll/SDM).
- Pembayaran selain fee sales (BPB, subkon, dsb).

## Invariants
- Perhitungan total fee dan termin wajib dihitung ulang di level Service (`SalesExternalService`) dari basis harga AJB dan tarif PKS; tidak boleh hanya mengandalkan perhitungan client-side.
- Perubahan tarif fee atau tanggal PKS pada Master Sales selalu dicatat dalam audit trail `sales_history`.
- Termin yang sudah berstatus `sudah_cair` dikunci dan tidak dapat di-generate ulang.
- Semua operasi manipulasi multi-tabel dibungkus dalam Database Transaction.

## Database Tables
### Owner Table(s)
- `sales` *(master data sales external & tarif komisi PKS)*
- `sales_history` *(audit trail histori perubahan tarif & dokumen PKS)*
- `fee_sales` *(header fee per transaksi konsumen akad)*
- `fee_sales_termin` *(rincian termin pencairan fee sales, nomor SPP, dan status cair)*

### Shared Tables (Dibaca & diperbarui secara terkontrol)
- `mkdt` *(kolom `harga_net_ajb`)*
- `konsumen` *(kolom `keterangan_sales`, `id_sales`)*
- `kavling`, `proyek`, `keuangan` *(dibaca untuk monitoring realisasi akad)*

## Contracts
### A. Public HTTP Endpoints & Routes Inventory
| Method | URI / Pattern | Handler | Akses Role | Keterangan |
|---|---|---|---|---|
| `GET` | `/master-sales` | `\App\Controllers\Web\SalesExternalController::index` | Admin (1), MKDT (4) | Halaman Master Data Sales External |
| `GET` | `/fee-sales` | `\App\Controllers\Web\SalesExternalController::feeSales` | Admin (1), Umum (2), Keuangan (3), MKDT (4) | Halaman Tracking & Pencairan Fee Sales |
| `POST` | `api/sales-external/list-master` | `\App\Controllers\Api\SalesExternalController::listMasterSales` | Login | DataTables server-side master sales |
| `GET` | `api/sales-external/get/(:num)` | `\App\Controllers\Api\SalesExternalController::getSales/$1` | Login | Ambil detail satu master sales |
| `POST` | `api/sales-external/save` | `\App\Controllers\Api\SalesExternalController::saveMasterSales` | Login | Simpan / perbarui master sales & log histori |
| `GET` | `api/sales-external/history/(:num)` | `\App\Controllers\Api\SalesExternalController::getSalesHistory/$1` | Login | Riwayat perubahan PKS & fee sales |
| `GET` | `api/sales-external/list-active` | `\App\Controllers\Api\SalesExternalController::getActiveSalesList` | Login | List sales aktif untuk Select2 transaksi MKDT |
| `POST` | `api/sales-external/list-fee` | `\App\Controllers\Api\SalesExternalController::listFeeSales` | Login | DataTables server-side fee sales tracking |
| `GET` | `api/sales-external/fee-detail/(:num)` | `\App\Controllers\Api\SalesExternalController::getFeeDetail/$1` | Login | Detail kalkulasi & jadwal termin per MKDT |
| `POST` | `api/sales-external/save-fee-header` | `\App\Controllers\Api\SalesExternalController::saveFeeHeader` | Login | Simpan konfigurasi fee & generate termin |
| `POST` | `api/sales-external/submit-spp` | `\App\Controllers\Api\SalesExternalController::submitPengajuanSpp` | Login | Pengajuan SPP untuk termin tertentu |
| `POST` | `api/sales-external/konfirmasi-cair` | `\App\Controllers\Api\SalesExternalController::konfirmasiCair` | Login | Konfirmasi dana cair termin tertentu |

### B. Public PHP Services
#### `SalesExternalService.php`
- `saveMasterSales(array $data, ?object $filePks, ?int $userId): array`
- `saveFeeHeader(array $data, ?int $userId): array`
- `submitPengajuanSpp(array $data, ?object $fileSpp, ?int $userId): array`
- `confirmPencairan(array $data, ?object $fileBukti, ?int $userId): array`

### C. Public Repositories
#### `SalesExternalRepository.php`
- `getMasterSalesList(array $params): array`
- `getSalesById(int $id): ?object`
- `getSalesHistory(int $idSales): array`
- `getActiveSales(): array`
- `getFeeSalesList(array $params): array`
- `getFeeDetailByMkdt(int $idMkdt): ?object`
