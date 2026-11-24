# Module: SalesExternal

**Status:** `REFACTORED`  
**Readiness Score:** `100/100`  
**Last Synced:** `2026-11-22 11:25:18` @ `e374c49`  

## Purpose
Pengelolaan Master Data Sales External (agen/broker properti independen), perjanjian kerjasama (PKS), tarif komisi (subsidi & komersil), riwayat audit tarif, integrasi transaksi konsumen MKDT, pengaturan matriks hak akses (`fee_sales_setting_akses`), tracking dan pencairan Fee Sales bertahap (skema 100% atau termin 30%-60%-10%), pengelolaan multi-sales fee per kavling, serta **Pengajuan Pencairan** lintas sales dengan verifikasi tanda tangan digital 3 pihak (Pembuat, MKDT, Keuangan), protokol penolakan (*rejection protocol*) dengan saran revisi harga net AJB, kalkulasi ulang nominal tiap termin in-place, pembatalan (*void*), tautan berbagi (*shareable mobile link*), cetak laporan PDF format Landscape (A4-L), pencatatan nomor cek pencairan, dan integrasi 6 event notifikasi real-time/outbox untuk departemen Keuangan & MKDT.

## Out of scope
- Komisi/gaji sales internal/in-house (dikelola oleh payroll/SDM).
- Pembayaran selain fee sales (BPB, subkon, dsb).

## Invariants
- **Server-Side Financial Recalculation (`cleanNumeric()`):** Perhitungan total fee (`total_fee`) dan nominal per termin (`fee_sales_termin.nominal`) wajib dihitung ulang di level Service (`SalesExternalService`) dari basis `harga_net_ajb` dan persentase komisi (`persentase_fee`) atau nominal manual (`total_fee_manual` ketika `is_manual_fee = 1`); tidak boleh hanya mengandalkan kalkulasi client-side.
- **Relaksasi Locking Harga Jual Net AJB (`harga_net_ajb`):** Kolom Harga Jual Net AJB (`mkdt.harga_net_ajb` & `fee_sales.harga_net_ajb`) hanya dikunci (`disabled` pada `#modal-shared-harga-ajb` di UI dan ditolak oleh backend guard di `SalesExternalService::saveFeeHeader`) apabila salah satu termin fee pada kavling (`id_mkdt`) tersebut telah berstatus cair (`SalesExternalRepository::hasCairTerminByMkdt($idMkdt) === true`). Selama belum ada termin yang cair (`sudah_cair`), revisi harga jual net AJB tetap diperbolehkan meskipun jadwal termin sudah di-generate atau sedang diajukan.
- **Protokol Penolakan Pengajuan (*Rejection Protocol*):** Penolakan batch pengajuan (`SalesExternalService::rejectPengajuanGroup`) hanya dapat dilakukan oleh role MKDT (`can_sign_mkdt`) atau Keuangan (`can_sign_keuangan`) selama status pengajuan bukan `selesai` atau `void`. **Pengajuan tidak dapat ditolak apabila salah satu pihak (MKDT atau Keuangan) sudah menandatangani dokumen** (`!empty($group->ttd_mkdt_by) || !empty($group->ttd_keuangan_by)`). Alasan penolakan (`reject_reason`) wajib diisi, dengan opsi menyertakan saran revisi harga net AJB (`suggested_harga_net_ajb`) baik di tingkat header maupun per item kavling. Status pengajuan diubah menjadi `'ditolak'`, audit penolakan dicatat (`rejected_by`, `rejected_role`, `rejected_at`, `reject_reason`), item menyimpan `suggested_harga_net_ajb`, log dicatat ke `history_log`, dan memicu notifikasi `fee_sales_pengajuan_rejected`.
- **Penanganan Data Legacy Akad 2025 (Flag Selesai):** Transaksi akad lampau (<= 31-12-2025) ditandai dengan flag `mkdt.is_fee_sales_selesai = 1` dengan backfill record `fee_sales` (status `selesai`) dan `fee_sales_termin` (skema 100%, status `sudah_cair`, tanggal cair = tanggal akad), sehingga otomatis dikelompokkan ke dalam status fee `selesai` dan dieksklusikan dari filter default `belum_proses`.
- **Perhitungan Ulang Nominal Tiap Termin In-Place (`recalculateTerminNominals`):** Pengajuan yang berstatus `'ditolak'` (atau pengajuan aktif yang belum dicairkan) dapat diperbarui nominal terminnya secara in-place berdasarkan `harga_net_ajb` terbaru pada kavling terkait. Aksi ini mereset status pengajuan dari `'ditolak'` kembali menjadi `'diajukan'`, membersihkan jejak penolakan (`rejected_by = NULL`, `rejected_role = NULL`, `rejected_at = NULL`, `reject_reason = NULL`), memperbarui nominal tiap termin di `fee_sales_termin` dan `fee_sales_pengajuan_item`, serta mencatat riwayat perubahan ke `history_log`.
- **Auto-Skema Termin Berdasarkan Akad Indent (`mkdt.akad_indent`):** Jika unit berstatus **Akad Indent** (`akad_indent == 1`), default skema termin otomatis dipilih **100%** (`skema_termin = '100'`, 1 termin tunggal); jika reguler (`akad_indent == 0`), default skema termin adalah **30%-60%-10%** (`skema_termin = '30_60_10'`, 3 termin).
- **Auto-Create Sales Non-PKS:** Input nama sales baru via Select2 tags pada `#modal-fee-sales` otomatis membuat record di tabel `sales` dengan `has_pks = 0`, `fee_subsidi = 1.00`, `fee_komersil = 1.00`, dan `status = 'aktif'`.
- **Audit Trail Perubahan Tarif & PKS:** Perubahan `fee_subsidi`, `fee_komersil`, `tanggal_pks`, `tanggal_exp_pks`, atau `lampiran_pks` pada Master Sales selalu dicatat dalam `sales_history`.
- **Proteksi Termin Aktif/Lunas:** Termin yang sudah berstatus `sudah_cair` dikunci dan tidak dapat diubah nominalnya, di-generate ulang, atau dihapus (`deleteFeeEntry` menolak penghapusan jika ada termin `!= 'belum_diajukan'`).
- **Aturan Pembatalan (*Void*) Pengajuan:** `SalesExternalService::voidPengajuanGroup` hanya diizinkan apabila belum ada satupun item termin dalam grup yang berstatus `sudah_cair` dan wajib menyertakan alasan pembatalan (`void_reason`) untuk audit trail. Saat di-void, seluruh termin terkait dikembalikan ke `belum_diajukan`.
- **Verifikasi Password Tanda Tangan Digital:** Penandatanganan pengajuan oleh MKDT (`can_sign_mkdt`) dan Keuangan (`can_sign_keuangan`) pada `SalesExternalService::signPengajuanGroup` wajib melalui verifikasi password akun pengguna aktif (`Myth\Auth\Password::verify`).
- **Atomicity:** Seluruh operasi tulis multi-tabel wajib dibungkus dalam Database Transaction (`$this->db->transBegin()` / `transCommit()` / `transRollback()`).
- **Isolasi Batas Modul & Kontrak Lintas-Modul:** Mutasi kolom `mkdt.harga_net_ajb` oleh `SalesExternalService` wajib didelegasikan ke modul pemiliknya melalui kontrak publik tipis `App\Services\Mkdt\MkdtPricingService::updateHargaNetAjb(int $idMkdt, float $hargaNetAjb): bool` (tanpa membuka transaksi DB baru agar tetap atomik dalam transaksi pemanggil), sedangkan pembacaan `auth_groups_users`/`menus` serta sinkronisasi `menu_roles` pada `FeeSalesSettingService` didelegasikan ke `App\Services\Settings\MenuAccessService`.
- **Resolusi Tanggal Efektif Akad (Akad Indent vs AJB):** Pada seluruh lapisan modul (tabel DataTables Tab 1, header grup kavling modal `#modal-group-detail`, pengurutan item PDF via `FeeSalesPdfService`, dan kolom `TGL AKAD` di cetakan PDF), tanggal akad dievaluasi secara dinamis dan konsisten: jika unit berstatus **Akad Indent** (`akad_indent == 1`), sistem menggunakan `akad_indent_tgl` dengan label penanda `(Indent)`. Jika transaksi reguler/AJB (`akad_indent == 0` atau kosong), sistem menggunakan `akad_tgl`.
- **Fallback Toleran Aset Tanda Tangan Digital:** Endpoint `GET /fee-sales/pengajuan-signature/(:num)/(:segment)` menyajikan respon gambar PNG 1x1 transparan dengan status HTTP 200 jika file fisik tanda tangan tidak ditemukan di disk server, mencegah kegagalan visual dan 404 console error pada browser.
- **Pengurutan Default Berdasarkan Tanggal Pencairan Hasil Akad Terbaru:** Pada tabel daftar fee sales Tab 1 (`#table-fee-sales`), urutan data secara default (baik pada inisialisasi DataTables client-side `order: [[8, "desc"]]` maupun fallback query backend `SalesExternalRepository::getFeeSalesList`) memprioritaskan unit dengan tanggal pencairan hasil akad terbaru (`COALESCE(keu.tgl_cair_keuangan, "1970-01-01") DESC`), diikuti tanggal akad (`m.akad_tgl DESC`), dan ID MKDT (`m.id_mkdt DESC`). Hal ini memastikan transaksi yang pencairan dananya baru saja diselesaikan oleh departemen Keuangan langsung berada di baris paling atas untuk diproses pengajuan fee sales-nya.

## Database Tables
### Owner Table(s)
- `sales` *(akses tulis eksklusif hanya untuk modul SalesExternal)*
- `sales_history` *(akses tulis eksklusif hanya untuk modul SalesExternal)*
- `fee_sales` *(akses tulis eksklusif hanya untuk modul SalesExternal)*
- `fee_sales_termin` *(akses tulis eksklusif hanya untuk modul SalesExternal)*
- `fee_sales_pengajuan` *(akses tulis eksklusif hanya untuk modul SalesExternal)*
- `fee_sales_pengajuan_item` *(akses tulis eksklusif hanya untuk modul SalesExternal)*
- `fee_sales_setting_akses` *(akses tulis eksklusif hanya untuk modul SalesExternal)*

### Shared Tables (Dibaca dari modul lain)
- `mkdt`
- `konsumen`
- `kavling`
- `proyek`
- `keuangan`
- `pencairan_akad_plan`
- `pencairan_akad_pengajuan`
- `pencairan_akad_payment`

## Contracts
### A. Public HTTP Endpoints & Routes Inventory
| Method | URI / Pattern | Tipe | Handler | Status Migrasi | Target API / Catatan |
|---|---|---|---|---|---|
| `GET` | `/master-sales` | `Explicit` | `\App\Controllers\Web\SalesExternal\SalesExternalController::index` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/fee-sales` | `Explicit` | `\App\Controllers\Web\SalesExternal\SalesExternalController::feeSales` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/fee-sales/pengajuan-pdf/(:num)` | `Explicit` | `\App\Controllers\Web\SalesExternal\SalesExternalController::pengajuanPdf/$1` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/fee-sales/export-pdf-pengajuan/(:num)` | `Explicit` | `\App\Controllers\Web\SalesExternal\SalesExternalController::pengajuanPdf/$1` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/fee-sales/pengajuan-signature/(:num)/(:segment)` | `Explicit` | `\App\Controllers\Web\SalesExternal\SalesExternalController::pengajuanSignature/$1/$2` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/fee-sales/pengajuan/(:segment)` | `Explicit` | `\App\Controllers\Web\SalesExternal\SalesExternalController::pengajuanShared/$1` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/sales-external/list-master` | `Explicit` | `SalesExternalController::listMasterSales` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/api/sales-external/get/(:num)` | `Explicit` | `SalesExternalController::getSales/$1` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/sales-external/save` | `Explicit` | `SalesExternalController::saveMasterSales` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/api/sales-external/history/(:num)` | `Explicit` | `SalesExternalController::getSalesHistory/$1` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/api/sales-external/list-active` | `Explicit` | `SalesExternalController::getActiveSalesList` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/sales-external/list-fee` | `Explicit` | `SalesExternalController::listFeeSales` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/api/sales-external/fee-detail/(:num)` | `Explicit` | `SalesExternalController::getFeeDetail/$1` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/sales-external/save-fee-header` | `Explicit` | `SalesExternalController::saveFeeHeader` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/sales-external/submit-spp` | `Explicit` | `SalesExternalController::submitPengajuanSpp` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/sales-external/konfirmasi-cair` | `Explicit` | `SalesExternalController::konfirmasiCair` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/sales-external/edit-pengajuan` | `Explicit` | `SalesExternalController::editPengajuan` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/sales-external/edit-cair` | `Explicit` | `SalesExternalController::editCair` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `DELETE` | `/api/sales-external/fee-entry/(:num)` | `Explicit` | `SalesExternalController::deleteFeeEntry/$1` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/sales-external/delete-fee-entry/(:num)` | `Explicit` | `SalesExternalController::deleteFeeEntry/$1` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/sales-external/prepare-group` | `Explicit` | `SalesExternalController::preparePengajuanGroup` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/sales-external/create-group` | `Explicit` | `SalesExternalController::createPengajuanGroup` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/sales-external/list-group` | `Explicit` | `SalesExternalController::listPengajuanGroup` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/api/sales-external/group-detail/(:num)` | `Explicit` | `SalesExternalController::getPengajuanGroupDetail/$1` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/sales-external/konfirmasi-cair-group` | `Explicit` | `SalesExternalController::konfirmasiCairGroup` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/sales-external/void-group` | `Explicit` | `SalesExternalController::voidPengajuanGroup` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/sales-external/sign-group` | `Explicit` | `SalesExternalController::signPengajuanGroup` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/sales-external/reject-group` | `Explicit` | `SalesExternalController::rejectPengajuanGroup` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/sales-external/recalculate-termin` | `Explicit` | `SalesExternalController::recalculateTermin` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/setting-fee-sales` | `Explicit` | `\App\Controllers\Web\SalesExternal\FeeSalesSettingController::index` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/setting-fee-sales/list` | `Explicit` | `FeeSalesSettingController::listPermissions` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/setting-fee-sales/save` | `Explicit` | `FeeSalesSettingController::savePermissions` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |

### B. Public PHP Services (Backend Contract Antar-Modul)
#### `SalesExternalService.php`
- `saveMasterSales(array $data, ?int $userId, ?UploadedFile $filePks = null)`
- `saveFeeHeader(array $data, int|bool|null $userId = null, bool $sendNotification = true)`
- `deleteFeeEntry(int $idFeeSales, ?int $userId)`
- `submitPengajuanSpp(int $idTermin, array $data, ?int $userId, ?UploadedFile $fileSpp = null)`
- `confirmPencairan(int $idTermin, array $data, ?int $userId, ?UploadedFile $fileBukti = null)`
- `updatePengajuan(int $idTermin, array $data, ?int $userId, ?UploadedFile $fileSpp = null)`
- `updatePencairan(int $idTermin, array $data, ?int $userId, ?UploadedFile $fileBukti = null)`
- `createPengajuanGroup(array $data, ?int $userId, ?UploadedFile $fileSpp = null)`
- `voidPengajuanGroup(int $idPengajuan, string $alasan, int $userId)`
- `signPengajuanGroup(int $idPengajuan, string $role, array $data, int $userId)`
- `rejectPengajuanGroup(int $idPengajuan, string $role, array $data, int $userId)`
- `recalculateTerminNominals(array $data, int $userId)`
- `confirmPencairanGroup(int $idPengajuan, array $data, ?int $userId, ?UploadedFile $fileBukti = null)`
- `getPengajuanGroupDetail(int $idPengajuan)`
- `getPengajuanGroupByToken(string $tokenOrId)`
#### `FeeSalesPdfService.php`
- `render(int $idPengajuan)`
#### `FeeSalesSettingService.php`
- `listPermissions()`
- `savePermissions(array $permissions, ?int $userId = null)`
- `getUserPermissions(?int $userId = null)`

## File Inventory
### Controllers (4)
- `app/Controllers/Web/SalesExternal/SalesExternalController.php` (176 baris, 5 public methods: `index()`, `feeSales()`, `pengajuanPdf(int $id)`, `pengajuanSignature(int $id, string $role)`, `pengajuanShared(string $token)`)
- `app/Controllers/Api/SalesExternal/SalesExternalController.php` (522 baris, 22 public methods: `listMasterSales()`, `getSales(int $id)`, `saveMasterSales()`, `getSalesHistory(int $id)`, `getActiveSalesList()`, `listFeeSales()`, `getFeeDetail(int $idMkdt)`, `saveFeeHeader()`, `deleteFeeEntry(int $idFeeSales)`, `submitPengajuanSpp()`, `konfirmasiCair()`, `editPengajuan()`, `editCair()`, `preparePengajuanGroup()`, `createPengajuanGroup()`, `listPengajuanGroup()`, `getPengajuanGroupDetail(int $id)`, `konfirmasiCairGroup()`, `voidPengajuanGroup()`, `signPengajuanGroup()`, `rejectPengajuanGroup()`, `recalculateTermin()`)
- `app/Controllers/Web/SalesExternal/FeeSalesSettingController.php` (22 baris, 1 public methods: `index()`)
- `app/Controllers/Api/SalesExternal/FeeSalesSettingController.php` (103 baris, 2 public methods: `listPermissions()`, `savePermissions()`)

### Services (3)
- `app/Services/SalesExternal/SalesExternalService.php` (1723 baris, 15 public methods)
- `app/Services/SalesExternal/FeeSalesPdfService.php` (116 baris, 1 public methods)
- `app/Services/SalesExternal/FeeSalesSettingService.php` (212 baris, 3 public methods)

### Repositories (2)
- `app/Repositories/SalesExternal/SalesExternalRepository.php` (1228 baris, 42 public methods: `getMkdtIdByFeeSalesId(int $idFeeSales)`, `hasGeneratedTerminByMkdt(int $idMkdt)`, `hasCairTerminByMkdt(int $idMkdt)`, `userSnapshot(int $userId)`, `getMasterSalesList(array $params = [])`, `getSalesById(int $id)`, `getSalesHistory(int $idSales)`, `getActiveSales()`, `getFeeSalesList(array $params = [])`, `getFeeDetailByMkdt(int $idMkdt)`, `getFeeDetailsByMkdtIds(array $mkdtIds)`, `getPengajuanGroupList(array $params = [])`, `getPengajuanGroupDetail(int $idPengajuan)`, `getPengajuanGroupByToken(string $tokenOrId)`, `findActiveSalesByNama(string $nama)`, `getFeeSalesById(int $idFeeSales)`, `getFeeSalesByIdAndMkdt(int $idFeeSales, int $idMkdt)`, `getFeeSalesByMkdtAndSales(int $idMkdt, int $idSales)`, `getFeeSalesListByMkdt(int $idMkdt)`, `markFeeSalesProsesTerminIfNotSelesai(int $idFeeSales, ?int $userId)`, `updateFeeSalesStatus(int $idFeeSales, string $status, ?int $userId)`, `updateFeeSalesPricing(int $idFeeSales, float $hargaNetAjb, float $totalFee, ?int $userId)`, `deleteFeeSalesById(int $idFeeSales)`, `getTerminById(int $idTermin)`, `getTerminListByFeeSalesId(int $idFeeSales)`, `countActiveTerminByFeeSalesId(int $idFeeSales)`, `countTerminByFeeSalesId(int $idFeeSales)`, `countCairTerminByFeeSalesId(int $idFeeSales)`, `deleteBelumDiajukanTerminByFeeSalesId(int $idFeeSales)`, `deleteTerminByFeeSalesId(int $idFeeSales)`, `updateTerminNominal(int $idTermin, float $nominal, ?int $userId)`, `getPengajuanStatusById(int $idPengajuan)`, `getLatestNonVoidPengajuanByTerminId(int $idTermin)`, `getPengajuanItemStatuses(int $idPengajuan)`, `getPengajuanLinksByTerminId(int $idTermin)`, `getRejectedPengajuanByMkdt(int $idMkdt)`, `countRemainingSuggestedItemsByPengajuanId(int $idPengajuan)`, `syncActivePengajuanItemNominalByTerminId(int $idTermin, float $nominal)`, `getMkdtHargaNetAjb(int $idMkdt)`, `getProyekContextByMkdtId(int $idMkdt)`, `getFeeSalesContextForAudit(int $idFeeSales)`, `getProyekKodeKeuangan(int $idProyek)`)
- `app/Repositories/SalesExternal/FeeSalesSettingRepository.php` (82 baris, 4 public methods: `listPermissions()`, `existsByGroupId(int $idGroup)`, `upsertGroupPermission(int $idGroup, array $payload)`, `getAggregatedPermissionsByGroupIds(array $groupIds)`)

### Models (6)
- `app/Models/SalesModel.php`
- `app/Models/SalesHistoryModel.php`
- `app/Models/FeeSalesModel.php`
- `app/Models/FeeSalesTerminModel.php`
- `app/Models/FeeSalesPengajuanModel.php`
- `app/Models/FeeSalesPengajuanItemModel.php`

### Views (2 folder)
- `app/Views/sales` (4 file: `fee_sales.php`, `master_sales.php`, `pdf_pengajuan_fee.php`, `pengajuan_fee_shared.php`)
- `app/Views/setting` (2 file: `data.php`, `fee_sales_access.php`)

### JavaScript (2 file)
- `public/assets/js/sales/master_sales.js` (272 baris)
- `public/assets/js/sales/fee_sales.js` (3246 baris)

### Zero-Downtime Shims (8 file)
- `app/Controllers/Web/SalesExternalController.php`
- `app/Controllers/Api/SalesExternalController.php`
- `app/Controllers/Web/Settings/FeeSalesSettingController.php`
- `app/Controllers/Api/Settings/FeeSalesSettingController.php`
- `app/Services/SalesExternalService.php`
- `app/Services/FeeSalesPdfService.php`
- `app/Services/Settings/FeeSalesSettingService.php`
- `app/Repositories/SalesExternalRepository.php`

## Key Functions
- **Controller (`App\Controllers\Web\SalesExternal\SalesExternalController`):**
  - `index()`: Merender halaman master data sales external.
  - `feeSales()`: Merender antarmuka Fee Sales 2 tab (Daftar Kavling & Pengajuan Pencairan) dengan integrasi filter proyek via `ProyekService::getAll()` (Zero Controller Layer Violation).
  - `pengajuanPdf(int $id)`: Memanggil `FeeSalesPdfService::render()` untuk stream dokumen PDF format Landscape A4-L.
  - `pengajuanSignature(int $id, string $role)`: Mendelegasikan pengambilan detail pengajuan ke `SalesExternalService::getPengajuanGroupDetail($id)` dan menyajikan file fisik tanda tangan digital, dengan fallback respon gambar PNG transparan 1x1 (HTTP 200) jika file fisik tanda tangan tidak ditemukan di disk server guna mengeliminasi 404 console error pada browser.
  - `pengajuanShared(string $token)`: Mendelegasikan pengambilan detail pengajuan ke `SalesExternalService::getPengajuanGroupByToken($token)` dan merender lembar persetujuan/penolakan shared mobile & desktop berbasis token acak.
- **Controller (`App\Controllers\Api\SalesExternal\SalesExternalController`):**
  - `listFeeSales()`: Menerima parameter request filtering (dengan fallback default `status_fee = 'belum_lunas'`) dan DataTables server-side `order` (array indeks kolom & arah sorting) untuk sorting dinamis kolom 2..10 di Tab 1.
  - `deleteFeeEntry(int $idFeeSales)`: Menghapus entri kartu komisi, mendelegasikan lookup MKDT ke `$this->salesRepo->getMkdtIdByFeeSalesId($idFeeSales)` (Zero Controller Layer Violation) dan eksekusi penghapusan ke Service.
  - `rejectPengajuanGroup()`: Memvalidasi izin role (`can_sign_mkdt` atau `can_sign_keuangan`), memvalidasi input `id_pengajuan`, `role`, `reject_reason`, dan array `item_prices` (saran harga per unit), mendelegasikan eksekusi penolakan ke `SalesExternalService::rejectPengajuanGroup()` (yang menjamin pengajuan belum ditandatangani oleh salah satu pihak).
  - `recalculateTermin()`: Menerima request kalkulasi ulang nominal termin untuk kavling terkait (`id_pengajuan`, `id_mkdt`, `harga_net_ajb`), mendelegasikan ke `SalesExternalService::recalculateTerminNominals()`.
  - `saveFeeHeader()`: Menangani penyimpanan kartu komisi dan sinkronisasi harga net AJB dengan validasi `hasCairTerminByMkdt()`.
  - `createPengajuanGroup()`: Membuat batch pengajuan multi-unit dan menghasilkan nomor otomatis.
  - `signPengajuanGroup()`: Verifikasi kata sandi pengguna dan penyimpanan tanda tangan digital pemeriksa/penyetuju.
  - `konfirmasiCairGroup()`: Eksekusi pencairan termin massal beserta pencatatan nomor cek dan bukti cair.
  - `voidPengajuanGroup()`: Pembatalan pengajuan dengan pengembalian status termin ke `belum_diajukan`.
- **Controller (`App\Controllers\Web\SalesExternal\FeeSalesSettingController`):**
  - `index()`: Merender halaman matriks konfigurasi hak akses modul Fee Sales.
- **Controller (`App\Controllers\Api\SalesExternal\FeeSalesSettingController` — `extends BaseApiController`):**
  - Deklarasi `protected $helpers = ['auth'];` + guard `helper('auth')` dan helper `jsonResponse()` yang menjaga JSON envelope `['success', 'message', 'messages', 'data'?, 'token']` agar kompatibel penuh dengan view `fee_sales_access.php` dan standar `BaseApiController`.
  - `listPermissions()`: Mengambil daftar matriks perizinan role untuk modul Fee Sales melalui `FeeSalesSettingService::listPermissions()`.
  - `savePermissions()`: Membaca payload `permissions` via `readRequestData()` (dengan fallback `getJSON(true)`), memvalidasi array (`400` jika invalid), dan mendelegasikan penyimpanan ke `FeeSalesSettingService::savePermissions()`.
- **Service (`App\Services\SalesExternal\SalesExternalService`):**
  - Bebas dari query `$this->db->table()`/`->query()` langsung; `$this->db` hanya digunakan untuk manajemen transaksi DB (`transStart`/`transComplete`/`transRollback`) dan diteruskan ke `SalesExternalRepository($this->db)` serta `MkdtPricingService($this->db)`.
  - `getPengajuanGroupDetail(int $idPengajuan): ?object` & `getPengajuanGroupByToken(string $tokenOrId): ?object`: Wrapper layanan untuk konsumsi `Web\SalesExternal\SalesExternalController`.
  - `rejectPengajuanGroup(int $idPengajuan, string $role, array $data, int $userId): array`: Mengubah status `fee_sales_pengajuan` menjadi `'ditolak'`, memvalidasi mutlak bahwa pengajuan tidak dapat ditolak jika salah satu pihak (MKDT atau Keuangan) telah menandatangani dokumen (`!empty($group->ttd_mkdt_by) || !empty($group->ttd_keuangan_by)`) atau jika sudah ada termin cair, menyimpan audit penolakan (`rejected_by`, `rejected_role`, `rejected_at`, `reject_reason`, `suggested_harga_net_ajb`), menyimpan saran harga pada `fee_sales_pengajuan_item.suggested_harga_net_ajb`, mencatat riwayat ke `history_log`, dan menembakkan notifikasi `fee_sales_pengajuan_rejected` ke Keuangan & MKDT.
  - `recalculateTerminNominals(array $data, int $userId): array`: Mengambil seluruh termin aktif yang belum cair pada kavling terkait, mendelegasikan pembaruan harga `mkdt.harga_net_ajb` via `MkdtPricingService::updateHargaNetAjb()` (melalui helper privat `applyPriceRecalculationForMkdt()`), menghitung ulang nominal masing-masing termin, mengupdate `fee_sales_termin.nominal`, `fee_sales_pengajuan_item.nominal`, dan `fee_sales.total_fee` melalui `SalesExternalRepository`, mereset status pengajuan dari `'ditolak'` kembali ke `'diajukan'`, mengosongkan metadata penolakan, dan mencatat log ke `history_log`.
  - `saveFeeHeader(array $data, int|bool|null $userId = null, bool $sendNotification = true): array`: Menyimpan header komisi multi-sales, memperbarui `mkdt.harga_net_ajb` melalui kontrak `MkdtPricingService::updateHargaNetAjb()` (hanya jika `!hasCairTerminByMkdt`), dan men-generate jadwal termin sesuai skema (`100%` untuk Akad Indent atau `30%-60%-10%` untuk reguler).
  - `saveMasterSales(array $data, ?int $userId, ?UploadedFile $filePks = null)`: Simpan/update profil agen dan mencatat audit trail tarif ke `sales_history`.
  - `createPengajuanGroup(array $data, ?int $userId, ?UploadedFile $fileSpp = null)`: Membuat batch pengajuan pencairan komisi multi-kavling secara atomik.
  - `signPengajuanGroup(int $idPengajuan, string $role, array $data, int $userId)`: Verifikasi password pengguna dan penyimpanan tanda tangan digital pemeriksa MKDT / penyetuju Keuangan.
  - `voidPengajuanGroup(int $idPengajuan, string $alasan, int $userId)`: Pembatalan pengajuan dengan pengembalian status termin ke `belum_diajukan`.
  - `confirmPencairanGroup(int $idPengajuan, array $data, ?int $userId, ?UploadedFile $fileBukti = null)`: Eksekusi pencairan termin massal beserta pencatatan nomor cek dan bukti transfer/pencairan (dengan helper privat `syncFeeSalesStatusFromTermins()`).
- **Service (`App\Services\SalesExternal\FeeSalesPdfService`):**
  - `render(int $idPengajuan)`: Menghasilkan binary PDF Landscape (A4-L) dengan agregasi data per sales dan 3 tanda tangan digital. Pengurutan data unit kavling (`uasort`) memperhitungkan tanggal efektif akad: memprioritaskan `akad_indent_tgl` apabila berstatus Akad Indent (`akad_indent == 1`), dengan fallback ke `akad_tgl`.
- **Service (`App\Services\SalesExternal\FeeSalesSettingService`):**
  - Constructor `__construct(?BaseConnection $db = null, ?FeeSalesSettingRepository $repo = null, ?MenuAccessService $menuAccessService = null)`.
  - `listPermissions()`, `savePermissions(array $permissions, ?int $userId = null)`, `getUserPermissions(?int $userId = null)`: Pengelolaan matriks hak akses RBAC melalui `FeeSalesSettingRepository` dan delegasi akses `auth_groups_users`/`menus`/`menu_roles` ke `App\Services\Settings\MenuAccessService` (`getUserGroupIds()`, `getActiveMenus()`, `getGroupMenuIds()`, `syncGroupMenus()` hanya bila berubah serta menjaga proteksi Role Admin `id_group = 1`).
- **Repository (`App\Repositories\SalesExternal\SalesExternalRepository`):**
  - `__construct(?BaseConnection $db = null)`: Mendukung injeksi koneksi transaksi/fixture.
  - `getMkdtIdByFeeSalesId(int $idFeeSales): ?int`: Query langsung untuk mendapatkan ID MKDT berdasarkan ID entri Fee Sales (mengeliminasi layer violation di Api Controller).
  - `hasCairTerminByMkdt(int $idMkdt): bool`: Query cepat untuk mengecek keberadaan termin berstatus `sudah_cair` pada unit transaksi tertentu (dipakai sebagai guard revisi harga net AJB).
  - `hasGeneratedTerminByMkdt(int $idMkdt): bool`: Mengecek apakah termin sudah pernah di-generate untuk unit tersebut.
  - `getFeeSalesList(array $params)`: Query JOIN kompleks daftar fee sales kavling Tab 1. Menyertakan subquery `keu` yang di-join ke `pencairan_akad_plan`, `pencairan_akad_pengajuan` (`status = 'paid'`), dan `pencairan_akad_payment` untuk mengekstrak `total_hasil_akad`, `total_cair_keuangan`, `tgl_cair_keuangan`, serta `last_update_keuangan`. Menyertakan evaluasi `m.is_fee_sales_selesai` pada kalkulasi `fee_status` di subquery agregasi `fs_agg`, filter `status_fee` (`belum_lunas` [default: mengisolasi baris aktif dengan mengecualikan fee selesai/legacy selesai dan memastikan termin belum lunas], `belum_proses`, `proses_spp`, `cair_sebagian`, `selesai`, dan `all`), dynamic column ordering per kolom DataTables 2..10 (dengan Kolom 8 dipetakan ke `COALESCE(keu.tgl_cair_keuangan, "1970-01-01")`), serta fallback default ordering yang memprioritaskan tanggal pencairan keuangan terbaru (`COALESCE(keu.tgl_cair_keuangan, "1970-01-01") DESC, m.akad_tgl DESC, m.id_mkdt DESC`).
  - `getPengajuanGroupList(array $params)`: Query DataTables daftar batch pengajuan Tab 2 dengan status dan filter proyek.
  - `getPengajuanGroupDetail(int $idPengajuan)`: Mengambil data header pengajuan lengkap beserta tanda tangan 3 pihak, status rejection (`rejected_by`, `rejected_role`, `rejected_at`, `reject_reason`, `suggested_harga_net_ajb`), serta seleksi `m.akad` dan `m.akad_indent_tgl` pada rincian unit kavling. Query `$tList` diperkaya dengan `LEFT JOIN fee_sales_pengajuan_item pi` dan `fee_sales_pengajuan fsp` (filter `fsp.status != 'void'`), sehingga setiap objek termin di `all_termin` menyertakan atribut riwayat pengajuan cross-pengajuan: `id_pengajuan`, `pengajuan_nomor`, `pengajuan_status`, `pengajuan_tanggal`.
  - **28 Method Kohesif Ekstraksi Service (`SalesExternalRepository` L905–1226):**
    - Master & Fee Sales: `findActiveSalesByNama()`, `getFeeSalesById()`, `getFeeSalesByIdAndMkdt()`, `getFeeSalesByMkdtAndSales()`, `getFeeSalesListByMkdt()`, `markFeeSalesProsesTerminIfNotSelesai()`, `updateFeeSalesStatus()`, `updateFeeSalesPricing()`, `deleteFeeSalesById()`.
    - Termin: `getTerminById()`, `getTerminListByFeeSalesId()`, `countActiveTerminByFeeSalesId()`, `countTerminByFeeSalesId()`, `countCairTerminByFeeSalesId()`, `deleteBelumDiajukanTerminByFeeSalesId()`, `deleteTerminByFeeSalesId()`, `updateTerminNominal()`.
    - Pengajuan & Item Sync: `getPengajuanStatusById()`, `getLatestNonVoidPengajuanByTerminId()`, `getPengajuanItemStatuses()`, `getPengajuanLinksByTerminId()`, `getRejectedPengajuanByMkdt()`, `countRemainingSuggestedItemsByPengajuanId()`, `syncActivePengajuanItemNominalByTerminId()` (2-step select-then-update yang portabel untuk MySQL & SQLite).
    - Konteks MKDT & Proyek: `getMkdtHargaNetAjb()`, `getProyekContextByMkdtId()`, `getFeeSalesContextForAudit()`, `getProyekKodeKeuangan()`.
- **Repository (`App\Repositories\SalesExternal\FeeSalesSettingRepository`):**
  - `__construct(?BaseConnection $db = null)`: Koneksi database opsional.
  - `listPermissions(): array`: Mengambil seluruh `auth_groups` dengan `LEFT JOIN fee_sales_setting_akses`.
  - `existsByGroupId(int $idGroup): bool`: Memeriksa eksistensi konfigurasi hak akses berdasarkan `id_group`.
  - `upsertGroupPermission(int $idGroup, array $payload): bool`: Insert atau update konfigurasi hak akses per grup pada tabel `fee_sales_setting_akses`.
  - `getAggregatedPermissionsByGroupIds(array $groupIds): ?object`: Mengambil agregasi `MAX(...)` seluruh flag perizinan untuk daftar grup milik pengguna.
- **Frontend JavaScript & Stylesheet (`public/assets/js/sales/fee_sales.js`, `public/assets/css/fee-sales.css`, `app/Views/sales/fee_sales.php`, `app/Views/sales/pdf_pengajuan_fee.php`, `app/Views/sales/pengajuan_fee_shared.php`):**
  - Struktur Kolom DataTables Tab 1 & Optimalisasi Resolusi Layar HD (`fee_sales.php`, `fee_sales.js`): Menggabungkan elemen Checkbox dan Nomor Urut ke dalam Kolom 0 (width: 70px) dengan container flexbox (fallback aman jika `!canSubmitSpp` hanya menampilkan nomor urut), serta merelokasi tombol aksi "Kelola" (`.btn-manage-fee`) menjadi Kolom 1 (width: 85px) tepat di sebelah kolom nomor urut. Hal ini mengeliminasi kebutuhan scroll horizontal ke sisi kanan tabel pada resolusi desktop HD (1366x768). Kolom 2 s.d. 10 tetap mempertahankan indeks mapping data transaksi finansial (Konsumen, Harga Jual, Net, AJB, Sales, Akad, Pencairan Keuangan, Total Fee, Status Termin) dan sorting server-side dinamis.
  - Server-Side Column Ordering & Default Sort Pencairan Keuangan (`fee_sales.js`, `fee_sales.php`): Kolom 2..10 DataTables Tab 1 kini berstatus `orderable: true` dengan dynamic server-side ordering ke backend. Konfigurasi inisialisasi DataTables diatur default sort Kolom 8 secara menurun (`order: [[8, "desc"]]`) agar transaksi dengan pencairan hasil akad terbaru berada di baris teratas. Renderer Kolom 8 ("Pencairan Keuangan") menampilkan persentase progress bar pencairan beserta informasi tanggal cair hasil akad berformat `dd/mm/yyyy` dengan icon `<i class="fas fa-calendar-check mr-25 text-success"></i>` (atau `-` jika belum ada pencairan) di bawah persentase progress bar. Dropdown status fee `#filter-fee-status` diset default ke `belum_lunas` ("Semua Kecuali Lunas") dengan lebar 175px untuk menyaring langsung transaksi aktif yang belum lunas (belum diproses, proses spp, dan cair sebagian) dengan mengecualikan fee selesai dan legacy selesai.
  - Proteksi Mutlak Penolakan Pasca-TTD (`fee_sales.js`, `pengajuan_fee_shared.php`): Menyembunyikan tombol tolak pengajuan baik di modal detail desktop maupun halaman shared mobile/desktop jika salah satu pihak (MKDT atau Keuangan) telah menandatangani dokumen (`hasAnySignature`).
  - PHP 8.2 Safe Null Coalescing & Dynamic Termin Labels (`pengajuan_fee_shared.php`): Menerapkan coalesce aman pada pemetaan unit key grouping dan perenderan label termin dinamis (contoh: `Termin 1 (50%)`).
  - `resolveAkadDate(row)` di `fee_sales.js`: Helper evaluasi tanggal akad unit kavling — jika berstatus Akad Indent (`akad_indent == 1`), menggunakan `akad_indent_tgl` (diberi label penanda `(Indent)`), sedangkan jika transaksi reguler/AJB menggunakan `akad_tgl` (format Indonesia via `formatTanggalIndo()`). Digunakan pada kolom Akad DataTables `#table-fee-sales` dan header baris grup kavling modal `#modal-group-detail`.
  - Fallback `onerror` Tanda Tangan Digital (`fee_sales.js`): Handler fallback pada elemen `<img>` tanda tangan digital dengan transparent PNG data URI jika file tanda tangan gagal dimuat.
  - Helper Akad PDF (`app/Views/sales/pdf_pengajuan_fee.php`): Fungsi helper `resolveAkadDatePdf($it)` dan `getEffectiveAkadTimePdf($it)` untuk evaluasi dan format tanggal akad pada kolom `TGL AKAD` cetakan dokumen pengajuan fee (menampilkan label `(Indent)` jika akad indent).
  - Modal `#modal-group-detail` Full Height (`public/assets/css/fee-sales.css`): Styling scoped modal detail pengajuan dengan tinggi viewport penuh (`calc(100vh - 2rem)` di desktop, `100vh` di mobile), sticky locked header-footer, dan modal-body berkemampuan scroll vertikal mandiri.
  - `renderGroupItemsTable(items)`: Merender tabel detail item pengajuan (`#table-group-items`) di dalam `#modal-group-detail` dengan konsolidasi 1 baris per unit kavling (meskipun diajukan multi-termin untuk unit yang sama). Baris 1: Header Grup Unit Kavling lengkap (Sales, Konsumen, Kavling, Tanggal Booking, Tanggal Akad). Baris 2: Data nominal (Harga Net AJB, % Fee, Total Fee), matriks status tenor pencairan (30%, 60%, 10%, 100%) dengan penandaan warna kuning `.tenor-highlight-yellow` pada seluruh kolom termin yang diajukan dalam pengajuan aktif, rendering status riwayat termin historis (`.tenor-cair-badge` jika sudah cair + tanggal cair + No Cek, `.tenor-diajukan-other-badge` jika diajukan di pengajuan lain + tanggal pengajuan + nomor pengajuan, dan `Belum Diajukan` jika belum diajukan), serta kolom Status & No Cek dan Aksi yang mendukung multi-termin individual secara rapi.
    - Grouping `$groupedUnitItems`: Mengonsolidasikan item pengajuan per unit kavling berbasis key `$it->id_fee_sales ?: ($it->id_mkdt . '_' . $it->id_sales)` sehingga satu unit kavling yang diajukan multi-termin tampil dalam satu kesatuan baris/kartu.
    - Tampilan Desktop (`d-none d-md-block`): Mengadopsi struktur Hierarchical Grouped Rows 12 kolom identik dengan `#modal-group-detail` (header unit kavling dan baris rincian nominal, tenor highlight emas `.tenor-highlight-yellow`, badge riwayat pencairan/pengajuan lain, serta status & nomor cek terpadu).
    - Tampilan Mobile Grid (`d-block d-md-none`): Responsive Card System tanpa horizontal overflow yang memadukan 2-kolom Financial Micro-Grid (`.fee-shared-finance-grid`) dan 2x2 Tenor Grid Box (`.fee-shared-tenor-grid`) dengan highlight emas `.active-target` (`#fff3cd` + border `#ffc107`) pada termin yang sedang diajukan.
    - Pembersihan CSS Inline: Memindahkan seluruh styling shared page ke `public/assets/css/fee-sales.css` demi kepatuhan arsitektur aset 3-tier.
    - Pemicu Modal Tolak & Saran Harga (`.btn-trigger-reject` & `#modal-shared-reject`): Menempatkan tombol aksi tolak bagi reviewer MKDT (`can_sign_mkdt`) dan Keuangan (`can_sign_keuangan`) di 3 lokasi (Header Action Bar samping Unduh PDF, Kartu Tanda Tangan MKDT/Keuangan, dan Mobile Sticky Bottom Bar), tombol "Ubah Penolakan / Saran Harga" pada alert banner status `ditolak`, guard relaksasi `$canReject = (($group->status ?? '') !== 'void' && ($group->status ?? '') !== 'selesai' && !$hasCair)` (bisa ditolak meski sudah bertanda tangan selama belum ada termin cair), serta event delegation `$(document).on("click", ".btn-trigger-reject", ...)`.
  - Interaktivitas Row Click Pengajuan: Event delegation pada baris `#table-pengajuan-group tbody tr` untuk membuka modal `#modal-group-detail` secara otomatis saat baris diklik (didukung cursor pointer & efek hover), serta penyederhanaan tombol aksi detail menjadi icon-only (`<i class="fas fa-eye"></i>`).
  - Handling event penolakan pengajuan via `#modal-reject-pengajuan` untuk MKDT dan Keuangan.
  - Handling aksi "Perbaharui Nominal Pencairan Tiap Termin" (`.btn-recalculate-termin`) di modal detail fee `#modal-fee-sales`, modal detail pengajuan `#modal-group-detail`, dan halaman shared.
  - Rendering banner peringatan pengajuan ditolak (`#creator-rejected-notif-banner`) di atas tabel Tab 2 ketika user login adalah pembuat pengajuan yang ditolak.
  - Adaptasi UI penguncian tombol edit/revisi harga net AJB yang kini hanya disabled jika sudah ada termin yang cair (`has_cair_termin === true`).

## Deprecated & Legacy Shims
1. **Zero-Downtime Shim Classes (8 File):**
   - `app/Controllers/Web/SalesExternalController.php` (Legacy Web Controller Shim) -> **DEPRECATED**, gunakan `\App\Controllers\Web\SalesExternal\SalesExternalController`.
   - `app/Controllers/Api/SalesExternalController.php` (Legacy API Controller Shim) -> **DEPRECATED**, gunakan `\App\Controllers\Api\SalesExternal\SalesExternalController`.
   - `app/Controllers/Web/Settings/FeeSalesSettingController.php` (Legacy Setting Web Controller Shim) -> **DEPRECATED**, gunakan `\App\Controllers\Web\SalesExternal\FeeSalesSettingController`.
   - `app/Controllers/Api/Settings/FeeSalesSettingController.php` (Legacy Setting API Controller Shim) -> **DEPRECATED**, gunakan `\App\Controllers\Api\SalesExternal\FeeSalesSettingController`.
   - `app/Services/SalesExternalService.php` (Root Namespace Service Shim) -> **DEPRECATED**, gunakan `\App\Services\SalesExternal\SalesExternalService`.
   - `app/Services/FeeSalesPdfService.php` (Root Namespace PDF Service Shim) -> **DEPRECATED**, gunakan `\App\Services\SalesExternal\FeeSalesPdfService`.
   - `app/Services/Settings/FeeSalesSettingService.php` (Legacy Setting Service Shim) -> **DEPRECATED**, gunakan `\App\Services\SalesExternal\FeeSalesSettingService`.
   - `app/Repositories/SalesExternalRepository.php` (Root Namespace Repository Shim) -> **DEPRECATED**, gunakan `\App\Repositories\SalesExternal\SalesExternalRepository`.
2. **Pengajuan SPP Satuan (`#modal-spp` & `POST /api/sales-external/submit-spp`):**
   - **Lokasi:** Modal `#modal-spp` di `app/Views/sales/fee_sales.php` (L517–563), handler `#form-spp` di `public/assets/js/sales/fee_sales.js`, dan `SalesExternalController::submitPengajuanSpp()` $\rightarrow$ `SalesExternalService::submitPengajuanSpp()`.
   - **Status:** **LEGACY / DORMANT** (tombol pemicu `.btn-ajukan-spp` pada baris termin sudah tidak dirender oleh `buildTerminRowsHtml()`).
   - **Pengganti:** Gunakan alur **Batch Pengajuan Pencairan** melalui checkbox Tab 1 $\rightarrow$ `#modal-create-group` $\rightarrow$ `POST /api/sales-external/create-group`.
3. **Edit Pengajuan SPP Satuan (`POST /api/sales-external/edit-pengajuan`):**
   - **Lokasi:** `SalesExternalController::editPengajuan()` $\rightarrow$ `SalesExternalService::updatePengajuan()`.
   - **Status:** **DEPRECATED** (tidak lagi dipanggil dari UI `fee_sales.js`).
   - **Pengganti:** Gunakan fitur **Void Pengajuan** (`#modal-void-pengajuan` $\rightarrow$ `POST /api/sales-external/void-group`) untuk membatalkan pengajuan yang salah (selama belum ada termin yang cair), lalu buat pengajuan baru.
4. **Route Aliases (Backward Compatibility):**
   - `GET /fee-sales/export-pdf-pengajuan/(:num)` $\rightarrow$ Alias untuk `GET /fee-sales/pengajuan-pdf/(:num)`.
   - `DELETE /api/sales-external/fee-entry/(:num)` $\rightarrow$ Alias untuk `POST /api/sales-external/delete-fee-entry/(:num)`.
5. **Properti Flat Backward-Compatibility di `SalesExternalRepository::getFeeDetailByMkdt()`:**
   - Menyalin elemen pertama `$feeList[0]` ke properti flat level root (`$row->id_fee_sales`, `$row->persentase_fee`, `$row->total_fee`, `$row->termin`, dll.) demi menjaga kompatibilitas kode lama, sementara UI multi-sales modern membaca array `$row->fee_list`.

---

## Readiness Checklist
- [x] Has Service Layer
- [x] Has Repository Layer
- [x] Web & API Controllers Separated
- [x] Explicit Routes Defined
- [x] Zero Controller Layer Violations
- [x] MODULE.md Initialized

## Dead Code Candidates
_Jalankan `php spark module:deadcode SalesExternal` untuk mengaudit dead code kandidat._

## Notes
- **Optimalisasi Layout Kolom DataTables Tab 1 untuk Resolusi Layar Desktop HD (2026-11-09):**
  - Menggabungkan elemen Checkbox dan Nomor Urut ke dalam Kolom 1 (width: 70px) dan merelokasi kolom tombol Aksi ke Kolom 2 (width: 85px) di `app/Views/sales/fee_sales.php` dan `public/assets/js/sales/fee_sales.js`.
  - Menghilangkan kebutuhan scroll horizontal ke sisi kanan pada resolusi standar desktop HD (1366x768) saat mengelola fee kavling, sekaligus mempertahankan indeks mapping kolom DataTables 2 s.d. 10 dan default sort Kolom 8 DESC (Pencairan Keuangan).
- **Default Filter "Semua Kecuali Lunas" (`belum_lunas`) & Preservasi Order Pencairan Hasil Akad (2026-11-09):**
  - Dropdown filter status fee `#filter-fee-status` di `app/Views/sales/fee_sales.php` diset default ke `<option value="belum_lunas" selected>Semua Kecuali Lunas</option>` (lebar 175px) sebagai opsi terpilih saat halaman dibuka.
  - Backend controller `SalesExternalController::listFeeSales()` mengarahkan fallback default parameter `status_fee` ke `'belum_lunas'`.
  - Klausul filter `status_fee === 'belum_lunas'` di `SalesExternalRepository::getFeeSalesList()` mengisolasi baris-baris transaksi aktif (`is_fee_sales_selesai = 0`, `fee_status != 'selesai'`, dan `termin_cair < total_termin`), mengecualikan kavling lunas dan legacy akad 2025 selesai, sambil tetap memelihara prioritas urutan tanggal pencairan keuangan terbaru (`COALESCE(keu.tgl_cair_keuangan, "1970-01-01") DESC`).
- **Penanganan Data Legacy Akad 2025, Default Filter "Belum Diproses", & Ordering Server-Side DataTables (2026-11-09):**
  - Penambahan kolom `mkdt.is_fee_sales_selesai` (migration `2026-11-09-000001_FlagLegacyFeeSalesAkad2025.php`) menandai 165 transaksi akad lampau (<= 31-12-2025) sebagai komisi yang sudah selesai.
  - Untuk transaksi legacy yang belum memiliki record komisi, migration mem-backfill entri `fee_sales` (status `selesai`) dan `fee_sales_termin` (skema 100%, status `sudah_cair`, tanggal cair = tanggal akad) sehingga historisitas data tetap utuh dan konsisten.
  - Filter default `#filter-fee-status` diubah menjadi `belum_proses` ("Belum Diproses") untuk mengecualikan transaksi bertanda `is_fee_sales_selesai = 1` agar daftar fokus pada kavling yang belum memiliki pengajuan fee.
  - Kolom 2..10 DataTables Tab 1 kini mendukung `orderable: true` dengan server-side sorting dinamis di `SalesExternalRepository::getFeeSalesList()`.
  - Proteksi penolakan mutlak: pengajuan yang telah ditandatangani oleh salah satu pihak (`ttd_mkdt_by` atau `ttd_keuangan_by`) dilarang ditolak, baik dari modal detail pengajuan desktop maupun lembar shared mobile/desktop.
- **Protokol Penolakan Pengajuan & Perhitungan Ulang Nominal Tiap Termin In-Place (2026-10-26):**
  - Alur lengkap penolakan pengajuan oleh MKDT (`can_sign_mkdt`) atau Keuangan (`can_sign_keuangan`) melalui modal `#modal-reject-pengajuan`.
  - Mendukung input alasan penolakan wajib (`reject_reason`) dan saran revisi harga net AJB (`suggested_harga_net_ajb`) baik secara agregat maupun per item kavling.
  - Pengiriman notifikasi otomatis `fee_sales_pengajuan_rejected` ke Keuangan & MKDT (grup `3;4`) dengan deep link ke detail pengajuan.
  - Tampilan banner alert peringatan `#creator-rejected-notif-banner` di atas tabel Tab 2 ketika user login memiliki pengajuan berstatus `'ditolak'`.
  - Tombol aksi "Perbaharui Nominal Pencairan Tiap Termin" (`.btn-recalculate-termin`) di `#modal-fee-sales`, `#modal-group-detail`, dan halaman shared untuk merevisi nominal termin in-place dan mereset status pengajuan dari `'ditolak'` kembali ke `'diajukan'`.
  - Relaksasi penguncian harga jual net AJB: beralih dari guard `hasGeneratedTerminByMkdt` ke `hasCairTerminByMkdt` sehingga revisi harga tidak terblokir selama belum ada termin yang cair.
- **Dukungan Akad Indent & Otomatisasi Skema Termin (2026-10-22):**
  - Query data fee sales pada `SalesExternalRepository` (`getFeeSalesList`, `getFeeDetailByMkdt`, `getFeeDetailsByMkdtIds`, `getPengajuanGroupDetail`) menyertakan `m.akad_indent` dari tabel `mkdt`.
  - Jika unit berstatus **Akad Indent** (`akad_indent = 1`), default skema termin otomatis dipilih **100%** (pencairan tunggal); jika bukan (`akad_indent = 0`), default skema termin diatur ke **30%-60%-10%**.
  - Proteksi seleksi batch pada `#table-fee-sales`: baris berstatus lunas (`fee_status = 'selesai'`) atau belum memiliki jadwal termin (`!id_fee_sales` / total termin = 0) dikunci (`disabled` + `row-restricted`) beserta tooltip informatif.
- **Form Pengajuan Pencairan, Penomoran, & Tanda Tangan Digital 3 Pihak (2026-10-22 s.d. 2026-10-23):**
  - Memisahkan **Tanggal Pengajuan dari Sales** (`tanggal_pengajuan_sales`) dan **Tanggal Pengajuan Pencairan** (`tanggal_pengajuan`), keduanya wajib diisi via Flatpickr.
  - Format penomoran pengajuan otomatis berbasis `kode_keuangan` proyek: `PGJ/FEE/{KODE_KEUANGAN}/{Ymd}/{RAND4}`.
  - Mendukung tanda tangan Pembuat, Pemeriksa MKDT, dan Penyetuju Keuangan (via metode `canvas` atau `profile`) yang disimpan secara privat di `protected_uploads/fee_sales/pengajuan/{id}/signatures/` via `BpbFileService`.
  - Dilengkapi fitur **Void Pengajuan** (`POST /api/sales-external/void-group`) dengan alasan wajib yang mengembalikan status item termin ke `belum_diajukan` (menggantikan fitur edit pengajuan tunggal yang telah dideprekasi).
- **Halaman Berbagi Responsif & Format Cetak PDF Landscape A4-L:**
  - Halaman shareable (`/fee-sales/pengajuan/{share_token}`) mengadopsi struktur Hierarchical Grouped Rows 12 kolom konsisten dengan modal detail pengajuan di desktop (konsolidasi 1 baris per unit, highlight kuning `.tenor-highlight-yellow` pada termin yang diajukan, badge status termin cair/pengajuan lain, status & no cek terpadu), dan Responsive Card Grid System berbasis 2-kolom Financial Micro-Grid (`.fee-shared-finance-grid`) serta 2x2 Tenor Grid Box (`.fee-shared-tenor-grid`, `.fee-tenor-box.active-target` highlight emas) di mobile (bebas horizontal overflow) beserta sticky bottom action bar. Seluruh aturan CSS dipusatkan di `public/assets/css/fee-sales.css` tanpa inline style.
  - Ekspor PDF (`/fee-sales/pengajuan-pdf/{id}` / `app/Views/sales/pdf_pengajuan_fee.php`) mengelompokkan tabel per sales external (dengan pemisah halaman `page-break-before` per sales), mengonsolidasikan multi-termin pada kavling yang sama menjadi 1 baris per unit (highlight kuning pada termin diajukan), mengurutkan kavling berdasarkan `akad_tgl` ASC, menampilkan `<title>Pengajuan Fee Sales</title>`, header tanggal pengajuan berbasis `tanggal_pengajuan_sales` dalam format bahasa Indonesia, nomor pengajuan bersufiks inisial nama sales per-kelompok sheet (`{nomor_pengajuan}.{INITIALS}` via `getSalesInitialsPdf()`), kolom Sales pada tabel tetap dipertahankan, dan merender 3 kolom tanda tangan digital (Pembuat, MKDT, Keuangan).
- **Pengaturan Hak Akses Modul (`/setting-fee-sales`):**
  - Dikelola oleh `FeeSalesSettingController` & `FeeSalesSettingService` berbasis tabel `fee_sales_setting_akses` (`can_view`, `can_manage`, `can_submit_spp`, `can_cairkan`, `can_sign_mkdt`, `can_sign_keuangan`), dengan proteksi penuh untuk Role Admin (ID 1).
- **Integrasi 6 Event Notifikasi Fee Sales (`NotificationEvent` & `SalesExternalService`):**
  - Terdaftar dengan kategori `Fee Sales` dan `relevant_groups = '3;4'` (Keuangan & MKDT): `fee_sales_pengajuan_created`, `fee_sales_pengajuan_rejected`, `fee_sales_termin_generated`, `fee_sales_harga_net_updated`, `fee_sales_ttd_signed`, dan `fee_sales_pencairan`.
  - Saat `saveFeeHeader()` dipanggil secara internal dari `createPengajuanGroup()`, argumen `$sendNotification = false` menekan pengiriman notifikasi duplikat.
  - Klik notifikasi `fee_sales*` di navbar diarahkan ke `/notif/open/{id_notif}` menuju `fee-sales/pengajuan/{share_token}` atau `fee-sales?open_mkdt={id_mkdt}`.
- **Status Struktur Modular Pasca-Refaktor (2026-11-09):**
  - Modul telah 100% bermigrasi ke arsitektur modular monolit SIGAPP dengan Readiness Score 100/100 (`REFACTORED`).
  - Seluruh Controller, Service, dan Repository kini berada dalam subfolder spesifik modul (`App\Controllers\Web\SalesExternal\`, `App\Controllers\Api\SalesExternal\`, `App\Services\SalesExternal\`, dan `App\Repositories\SalesExternal\`).
  - 8 Zero-Downtime Shim classes dipelihara di root layer legacy untuk menjamin backward compatibility penuh tanpa downtime.
  - Zero Controller Layer Violations: Web Controller tidak lagi memanggil `new ProyekModel()` (digantikan oleh `ProyekService::getAll()`), dan Api Controller tidak lagi query langsung ke database via `Database::connect()->table('fee_sales')` (digantikan oleh method baru `SalesExternalRepository::getMkdtIdByFeeSalesId()`).
  - 32 routes di `app/Config/Routes.php` diperbarui mengarah langsung ke namespace modular baru. Seluruh unit tests (31/31) terverifikasi PASS.

- **Cross-Module Dependencies & Callers:**
  - **Outbound Contracts (Dikonsumsi oleh `SalesExternal`):**
    - `SalesExternal` -> `MKDT` (`App\Services\Mkdt\MkdtPricingService::updateHargaNetAjb(int $idMkdt, float $hargaNetAjb): bool`): Dipanggil dari `SalesExternalService::saveFeeHeader()` dan `SalesExternalService::applyPriceRecalculationForMkdt()` (via `recalculateTerminNominals()`) dalam transaksi aktif untuk memperbarui `mkdt.harga_net_ajb`.
    - `SalesExternal` -> `Settings` (`App\Services\Settings\MenuAccessService`): `FeeSalesSettingService` memanggil `getUserGroupIds()`, `getActiveMenus()`, `getGroupMenuIds()`, dan `syncGroupMenus()` untuk sinkronisasi menu `fee-sales` serta resolusi hak akses pengguna.
    - `SalesExternal` -> `Proyek`, `BPB`, `AuditTrail`, `Notifikasi`, & Storage: `ProyekService::getAll()`, `BpbFileService`, `HistoryService`, `NotifikasiService::tambah_notif()` (6 event `fee_sales*`), dan `FileAccessService`/`StorageService`.
  - **Inbound Callers & Test Consumers:**
    - `Notifikasi` (`NotificationNavigationService` / `/notif/open/{id}`) mengarahkan deep link notifikasi `fee_sales*` ke `/fee-sales/pengajuan/{share_token}` atau `/fee-sales?open_mkdt={id_mkdt}`.
    - `tests/unit/FeeSalesWorkflowTest.php` menguji alur Fee Sales dengan partial mock `SalesExternalRepository` berbasis SQLite in-memory fixture.

### Audit Verifikasi Migrasi (2026-11-22)
- **Status Ledger:** `COMPLETED` — Status modul `REFACTORED`, Readiness Score `100/100`, `0` temuan audit aktif (32 rute eksplisit modular, `0` legacy/autoroute, `8` `thin_shim` bersih, dan `2` Repositories modular terdaftar di `ModuleCatalog.php`).
- **Terverifikasi Bersih (Pasca Remediasi 2026-11-22):**
  1. `app/Controllers/Api/SalesExternal/FeeSalesSettingController.php`: kini mewarisi `App\Controllers\Api\BaseApiController`, mendeklarasikan `protected $helpers = ['auth'];` + guard `helper('auth')`, membaca payload via `readRequestData()`, dan mempertahankan JSON envelope `['success', 'message', 'messages', 'data'?, 'token']` kompatibel dengan `fee_sales_access.php`.
  2. `app/Controllers/Web/SalesExternal/SalesExternalController.php`: bebas dari instansiasi `SalesExternalRepository` langsung; `pengajuanSignature()` dan `pengajuanShared()` mendelegasikan ke `SalesExternalService::getPengajuanGroupDetail()` & `getPengajuanGroupByToken()`. *(Catatan arsitektur terpisah: `Api\SalesExternal\SalesExternalController` tetap dipertahankan surgical menggunakan `$this->repo` / `$this->salesRepo` untuk lookup read-only sesuai Keputusan #3 Opsi A yang disetujui user — tidak termasuk temuan audit 2026-11-22).*
  3. `app/Services/SalesExternal/SalesExternalService.php`: bebas dari seluruh 44 akses `$this->db->table()`/`->query()` langsung; seluruh query/mutasi tabel modul dipindah ke `SalesExternalRepository` (penambahan 28 method kohesif baru) dan mutasi `mkdt.harga_net_ajb` didelegasikan melalui kontrak tipis `App\Services\Mkdt\MkdtPricingService::updateHargaNetAjb()`. `$this->db` hanya digunakan untuk manajemen transaksi DB (`transStart`/`transComplete`/`transRollback`) dan diteruskan ke `SalesExternalRepository($this->db)` & `MkdtPricingService($this->db)`.
  4. `app/Services/SalesExternal/FeeSalesSettingService.php`: bebas dari akses `$this->db->table()` langsung; seluruh query `auth_groups` & `fee_sales_setting_akses` dipindah ke `App\Repositories\SalesExternal\FeeSalesSettingRepository`, sementara query `auth_groups_users`/`menus` dan sinkronisasi `menu_roles` didelegasikan ke `App\Services\Settings\MenuAccessService` (sinkronisasi selektif saat menu berubah dan menjaga proteksi Role Admin `id_group = 1`).

## Change Log
- **`2026-11-22` [`REFACTOR`] — Remediasi Temuan Minor Audit Migrasi Modular (`BaseApiController`, Isolasi Repository, & Kontrak Lintas Modul `MKDT`/`Settings`):**
  - **Routes & Public Contracts:** Seluruh 32 rute eksplisit Web/API (`/master-sales`, `/fee-sales*`, `/setting-fee-sales`, `/api/sales-external/*`, `/api/setting-fee-sales/*`) tetap Zero-Downtime 100%. Menambahkan 2 public wrapper pada `SalesExternalService` (`getPengajuanGroupDetail(int $idPengajuan)` & `getPengajuanGroupByToken(string $tokenOrId)`). `SalesExternalService` kini mengonsumsi kontrak lintas modul `App\Services\Mkdt\MkdtPricingService::updateHargaNetAjb(int $idMkdt, float $hargaNetAjb): bool`, dan `FeeSalesSettingService` mengonsumsi `App\Services\Settings\MenuAccessService`.
  - **Controller / Service / Repo / Model:**
    - `app/Controllers/Api/SalesExternal/FeeSalesSettingController.php`: Migrasi `extends BaseController` $\rightarrow$ `extends BaseApiController`, tambah `protected $helpers = ['auth'];` + guard `helper('auth')`, baca input via `readRequestData()`, dan pertahankan JSON envelope `['success', 'message', 'messages', 'data'?, 'token']`.
    - `app/Repositories/SalesExternal/FeeSalesSettingRepository.php` *(baru)*: Repository khusus `auth_groups` & `fee_sales_setting_akses` (`__construct(?BaseConnection $db = null)`, `listPermissions`, `existsByGroupId`, `upsertGroupPermission`, `getAggregatedPermissionsByGroupIds`). Terdaftar di `app/Libraries/ModuleManager/ModuleCatalog.php`.
    - `app/Services/SalesExternal/FeeSalesSettingService.php`: Constructor `__construct(?BaseConnection $db = null, ?FeeSalesSettingRepository $repo = null, ?MenuAccessService $menuAccessService = null)`; memindahkan query setting ke `FeeSalesSettingRepository` dan mendelegasikan query user group/menu serta sinkronisasi `menu_roles` ke `Settings\MenuAccessService` (sinkronisasi selektif & proteksi Role Admin `id_group = 1`).
    - `app/Controllers/Web/SalesExternal/SalesExternalController.php`: Menghapus instansiasi langsung `SalesExternalRepository` pada `pengajuanSignature()` dan `pengajuanShared()`, mendelegasikan ke `SalesExternalService::getPengajuanGroupDetail()` & `getPengajuanGroupByToken()`. *(`Api\SalesExternal\SalesExternalController` tetap surgical sesuai Keputusan #3 Opsi A).*
    - `app/Repositories/SalesExternal/SalesExternalRepository.php`: Menambahkan `__construct(?BaseConnection $db = null)` dan 28 method kohesif baru (`findActiveSalesByNama`, `getFeeSalesById`, `getFeeSalesByIdAndMkdt`, `getFeeSalesByMkdtAndSales`, `getFeeSalesListByMkdt`, `markFeeSalesProsesTerminIfNotSelesai`, `updateFeeSalesStatus`, `updateFeeSalesPricing`, `deleteFeeSalesById`, `getTerminById`, `getTerminListByFeeSalesId`, `countActiveTerminByFeeSalesId`, `countTerminByFeeSalesId`, `countCairTerminByFeeSalesId`, `deleteBelumDiajukanTerminByFeeSalesId`, `deleteTerminByFeeSalesId`, `updateTerminNominal`, `getPengajuanStatusById`, `getLatestNonVoidPengajuanByTerminId`, `getPengajuanItemStatuses`, `getPengajuanLinksByTerminId`, `getRejectedPengajuanByMkdt`, `countRemainingSuggestedItemsByPengajuanId`, `syncActivePengajuanItemNominalByTerminId` [2-step portabel MySQL & SQLite], `getMkdtHargaNetAjb`, `getProyekContextByMkdtId`, `getFeeSalesContextForAudit`, `getProyekKodeKeuangan`).
    - `app/Services/SalesExternal/SalesExternalService.php`: Memindahkan seluruh 44 `->table()`/`->query()` ke `SalesExternalRepository` dan `MKDT\MkdtPricingService::updateHargaNetAjb()`, menambahkan `getPengajuanGroupDetail()`, `getPengajuanGroupByToken()`, dan helper privat `syncFeeSalesStatusFromTermins()`, meneruskan `$this->db` ke repository & `MkdtPricingService`, serta mempertahankan `$this->db` khusus transaksi DB & sanitasi finansial `cleanNumeric()`.
    - `tests/unit/FeeSalesWorkflowTest.php`: Memperbarui L1322 ke partial mock `SalesExternalRepository` (`setConstructorArgs([$db])`) agar method baru berjalan nyata terhadap SQLite in-memory fixture (31/31 PASS).
  - **View / CSS / JS:** Tidak ada perubahan.
  - **Deprecations:** Tidak ada deprecations baru; 8 Zero-Downtime Shims dan endpoint legacy tetap dipertahankan.
- `2026-11-22` [`AUDIT`] — Audit verifikasi migrasi modular: status `COMPLETED (Catatan Minor)` (Score 100%); ditemukan `Api\SalesExternal\FeeSalesSettingController` extends `BaseController`, `Web\SalesExternal\SalesExternalController` panggil `SalesExternalRepository` langsung (L95-96, L145-146), serta `SalesExternalService` & `FeeSalesSettingService` masih `$this->db->table()`/`query()` langsung dan mutasi tabel asing (`mkdt`, `menu_roles`).
- **`2026-11-09` [`REFACTOR`] — Migrasi Penuh ke Arsitektur Modular Monolit CI4 SIGAPP & Eliminasi Controller Layer Violations:**
  - **Routes & Public Contracts:**
    - 32 rute modul SalesExternal di `app/Config/Routes.php` diperbarui mengarah ke controller modular `App\Controllers\Web\SalesExternal\` dan `App\Controllers\Api\SalesExternal\`.
    - Seluruh Public Contracts HTTP REST API dan Public PHP Services tetap 100% kompatibel dan backward-compatible.
  - **Controller / Service / Repo / Model:**
    - Repositories Modular: Dibuat `app/Repositories/SalesExternal/SalesExternalRepository.php` dengan penambahan method baru `getMkdtIdByFeeSalesId(int $idFeeSales): ?int` untuk abstraksi data MKDT dari kartu komisi.
    - Services Modular: Dibuat `app/Services/SalesExternal/SalesExternalService.php`, `app/Services/SalesExternal/FeeSalesPdfService.php`, dan `app/Services/SalesExternal/FeeSalesSettingService.php`.
    - Controllers Modular: Dibuat `app/Controllers/Web/SalesExternal/SalesExternalController.php`, `app/Controllers/Api/SalesExternal/SalesExternalController.php`, `app/Controllers/Web/SalesExternal/FeeSalesSettingController.php`, dan `app/Controllers/Api/SalesExternal/FeeSalesSettingController.php`.
    - Eliminasi Layer Violations:
      - `SalesExternalController` (Web): Menghapus instansiasi `new ProyekModel()` dan beralih menggunakan `ProyekService::getAll()`.
      - `SalesExternalController` (Api): Menghapus direct database query `Database::connect()->table('fee_sales')` pada `deleteFeeEntry()` dan beralih mendelegasikan ke `$this->salesRepo->getMkdtIdByFeeSalesId($idFeeSales)`.
    - Zero-Downtime Shims: Dibuat 8 class shim di path legacy (`app/Controllers/Web/SalesExternalController.php`, `app/Controllers/Api/SalesExternalController.php`, `app/Controllers/Web/Settings/FeeSalesSettingController.php`, `app/Controllers/Api/Settings/FeeSalesSettingController.php`, `app/Services/SalesExternalService.php`, `app/Services/FeeSalesPdfService.php`, `app/Services/Settings/FeeSalesSettingService.php`, `app/Repositories/SalesExternalRepository.php`) yang meng-extends class modular baru.
    - Pendaftaran Katalog: Didaftarkan seluruh file modular baru dan 8 file shim ke `app/Libraries/ModuleManager/ModuleCatalog.php`.
    - Unit Tests: Disesuaikan `tests/unit/FeeSalesWorkflowTest.php` untuk membaca repository modular (31/31 unit test PASS).
  - **View / CSS / JS:** Tidak berubah (antarmuka view, CSS, dan JS frontend kompatibel 100% tanpa regresi).
  - **Deprecations:** 8 class controller/service/repo legacy di root namespace dideprekasi dan kini bertindak sebagai Zero-Downtime Shims.
- **`2026-11-09` [`UI_REPORT & ENHANCEMENT`] — Relokasi Kolom Aksi ke Kolom 2 & Penggabungan Checkbox + No Urut di Kolom 1 untuk Kompatibilitas Resolusi HD:**
  - **Routes & Public Contracts:** Tidak berubah.
  - **Controller / Service / Repo / Model:** Tidak berubah (indeks data kolom 2 s.d. 10 di backend controller & repository tetap selaras dan tidak terpengaruh pergeseran kolom aksi).
  - **View / CSS / JS:**
    - `app/Views/sales/fee_sales.php`:
      - Menggabungkan elemen header Checkbox dan Nomor Urut menjadi satu kolom pada Kolom 1 (`<th>`, width: `70px`) menggunakan container flexbox dengan `id="check-all-fee"`.
      - Merelokasi kolom header Aksi (`<th>`, width: `85px`) menjadi Kolom 2 tepat di sebelah kolom No/Checkbox.
      - Menghapus kolom header Aksi di ujung kanan tabel sehingga total kolom tabel menjadi 11 kolom (indeks 0 s.d. 10).
    - `public/assets/js/sales/fee_sales.js`:
      - Kolom 0 DataTables: Merender gabungan checkbox (`.check-mkdt-item`) dan nomor urut (`rowNum`), dengan fallback aman jika pengguna tidak memiliki hak akses `canSubmitSpp` (hanya menampilkan nomor urut).
      - Kolom 1 DataTables: Merender tombol aksi Kelola (`<button class="btn btn-xs btn-primary btn-manage-fee py-25 px-50"><i class="fas fa-sliders-h mr-25"></i> Kelola</button>`).
      - Kolom 2 s.d. 10 DataTables: Mempertahankan mapping indeks data dan kapabilitas sorting server-side (Konsumen & Unit, Harga Jual, Harga Jual Net, Net AJB, Sales External, Akad, Pencairan Keuangan, Total Fee, Status Termin).
      - Konfigurasi DataTables: Mempertahankan sorting default Kolom 8 (Pencairan Keuangan) secara menurun (`order: [[8, "desc"]]`).
  - **Deprecations:** Tidak ada.
- **`2026-11-09` [`FEATURE & ENHANCEMENT`] — Default Filter Status Fee 'belum_lunas' (Semua Kecuali Lunas) & Preservasi Order Pencairan Hasil Akad:**
  - **Routes & Public Contracts:** Tidak berubah (`POST /api/sales-external/list-fee` tetap melayani request DataTables dengan default parameter `status_fee = 'belum_lunas'`).
  - **Controller / Service / Repo / Model:**
    - `app/Controllers/Api/SalesExternalController.php`:
      - Pada `listFeeSales()`, mengarahkan default fallback request parameter `status_fee` ke `'belum_lunas'` (sebelumnya `'all'`).
    - `app/Repositories/SalesExternalRepository.php`:
      - Pada `getFeeSalesList()`, menambahkan klausul filter `status_fee === 'belum_lunas'` untuk mengisolasi baris-baris aktif (`(m.is_fee_sales_selesai = 0 OR m.is_fee_sales_selesai IS NULL)`, `(fs_agg.fee_status IS NULL OR fs_agg.fee_status != "selesai")`, dan `(t_stat.total_termin IS NULL OR t_stat.total_termin = 0 OR t_stat.termin_cair < t_stat.total_termin)`) dengan mengecualikan transaksi lunas 100% / berstatus selesai.
  - **View / CSS / JS:**
    - `app/Views/sales/fee_sales.php`:
      - Menambahkan opsi `<option value="belum_lunas" selected>Semua Kecuali Lunas</option>` di posisi pertama dropdown `#filter-fee-status` dengan lebar `175px` sebagai opsi default terpilih pada Tab 1.
  - **Deprecations:** Tidak ada.
- **`2026-11-09` [`FEATURE & ENHANCEMENT`] — Default Order Tanggal Pencairan Hasil Akad & Rendering Tanggal Cair pada Kolom Persentase:**
  - **Routes & Public Contracts:** Tidak berubah (`POST /api/sales-external/list-fee` tetap melayani request DataTables).
  - **Controller / Service / Repo / Model:**
    - `app/Repositories/SalesExternalRepository.php`:
      - Pada `getFeeSalesList()`, memperkaya subquery `keu` dengan `LEFT JOIN pencairan_akad_payment pay ON pay.id_pengajuan = paq.id` untuk mengekstrak field `keu.tgl_cair_keuangan` (`MAX(COALESCE(pay.tanggal_cair, paq.tanggal_rencana_cair, paq.tanggal_pengajuan, DATE(paq.updated_at)))`) dan `keu.last_update_keuangan` (`MAX(paq.updated_at)`).
      - Mengarahkan mapping sorting Kolom 8 DataTables (Pencairan Keuangan) ke `COALESCE(keu.tgl_cair_keuangan, "1970-01-01")`.
      - Mengatur fallback default order agar memprioritaskan tanggal pencairan keuangan terbaru (`COALESCE(keu.tgl_cair_keuangan, "1970-01-01") DESC, m.akad_tgl DESC, m.id_mkdt DESC`).
  - **View / CSS / JS:**
    - `public/assets/js/sales/fee_sales.js`:
      - Mengatur konfigurasi inisialisasi default sort DataTables ke Kolom 8 secara menurun (`order: [[8, "desc"]]`).
      - Memperbarui renderer Kolom 8 ("Pencairan Keuangan") untuk menampilkan informasi tanggal pencairan hasil akad berformat `dd/mm/yyyy` dengan icon `<i class="fas fa-calendar-check mr-25 text-success"></i>` (atau `-` jika belum ada pencairan) di bawah persentase progress bar.
  - **Deprecations:** Tidak ada.
- **`2026-11-09` [`BUGFIX & FEATURE`] — Flagging Data Legacy Akad 2025 (Status Selesai), Server-Side Ordering DataTables, & Proteksi Mutlak Penolakan Pasca-TTD:**
  - **Routes & Public Contracts:**
    - `POST /api/sales-external/list-fee`: Mendukung parameter request `order` (array DataTables server-side column ordering).
  - **Database & Migrations:**
    - Migration baru `app/Database/Migrations/2026-11-09-000001_FlagLegacyFeeSalesAkad2025.php`:
      - Menambahkan kolom `is_fee_sales_selesai TINYINT(1) DEFAULT 0` dengan index `idx_mkdt_fee_sales_selesai` pada tabel `mkdt`.
      - Menandai 165 transaksi akad lampau (<= 31-12-2025) menjadi `is_fee_sales_selesai = 1`.
      - Mem-backfill entri `fee_sales` (status `selesai`) dan `fee_sales_termin` (skema 100%, status `sudah_cair`, tanggal cair = tanggal akad) untuk transaksi legacy yang belum memiliki record fee sales.
  - **Controller / Service / Repo / Model:**
    - `app/Controllers/Api/SalesExternalController.php`: Method `listFeeSales()` meneruskan parameter `'order'` dari request ke repository.
    - `app/Services/SalesExternalService.php`: Method `rejectPengajuanGroup()` menambahkan validasi mutlak bahwa pengajuan tidak dapat ditolak jika salah satu pihak (MKDT atau Keuangan) telah menandatangani dokumen (`!empty($group->ttd_mkdt_by) || !empty($group->ttd_keuangan_by)`).
    - `app/Repositories/SalesExternalRepository.php`: Method `getFeeSalesList(array $params)`:
      - Menambahkan evaluasi `m.is_fee_sales_selesai` pada kalkulasi `fee_status` di subquery `fs_agg`.
      - Menyesuaikan filter `belum_proses` agar mengecualikan `is_fee_sales_selesai = 1`.
      - Menyesuaikan filter `selesai` agar mencakup `m.is_fee_sales_selesai = 1`.
      - Mengimplementasikan server-side sorting dinamis untuk kolom DataTables 2 s.d. 10 (Konsumen, Harga Jual, Harga Net, Net AJB, Sales, Tanggal Akad, % Cair Keuangan, Total Fee, Status Fee) dengan fallback default `m.akad_tgl DESC, m.id_mkdt DESC`.
  - **View / CSS / JS:**
    - `app/Views/sales/fee_sales.php`: Menjadikan default opsi dropdown `#filter-fee-status` ke `belum_proses` ("Belum Diproses").
    - `public/assets/js/sales/fee_sales.js`: Mengaktifkan `orderable: true` pada kolom 2..10 DataTables Tab 1, dan mengevaluasi `hasAnySignature` untuk menyembunyikan tombol penolakan jika sudah ada tanda tangan dari MKDT atau Keuangan.
    - `app/Views/sales/pengajuan_fee_shared.php`: Menyembunyikan tombol penolakan jika `$hasAnySignature`, menerapkan PHP 8.2 safe property coalesce pada pemetaan unit key dan termin targets, serta memformat label termin `Termin 1 (50%)` secara dinamis.
  - **Deprecations:** Tidak ada.
- **`2026-11-03` [`UI_REPORT`] — Sinkronisasi Tanggal Akad Indent, Full-Height Modal Detail Pengajuan, & Fallback Toleran Tanda Tangan Digital:**
  - **Routes & Public Contracts:**
    - `GET /fee-sales/pengajuan-signature/(:num)/(:segment)`: Fallback 1x1 transparent PNG response (status HTTP 200) jika file fisik tanda tangan tidak ada di disk (mengeliminasi 404 console error).
  - **Controller / Service / Repo / Model:**
    - `app/Controllers/Web/SalesExternalController.php`: Handling fallback missing physical signature file pada method `pengajuanSignature()` dengan pengiriman binary PNG 1x1 transparan.
    - `app/Services/FeeSalesPdfService.php`: Pengurutan dokumen PDF pengajuan fee sales (`uasort`) kini memperhitungkan tanggal efektif akad (`akad_indent_tgl` saat `akad_indent == 1`, fallback `akad_tgl`).
    - `app/Repositories/SalesExternalRepository.php`: Seleksi kolom `m.akad` dan `m.akad_indent_tgl` pada `getFeeSalesList()`, `getFeeDetailByMkdt()`, `getKandidatTerminList()`, dan `getPengajuanGroupDetail()`.
  - **View / CSS / JS:**
    - `public/assets/js/sales/fee_sales.js`: Helper `resolveAkadDate()`, DataTables `#table-fee-sales` kolom Akad (prioritas `akad_indent_tgl` saat `akad_indent == 1` dan `akad_tgl` saat akad AJB), header unit kavling pada `#modal-group-detail`, dan `onerror` fallback pada gambar tanda tangan digital.
    - `app/Views/sales/pdf_pengajuan_fee.php`: Helper `resolveAkadDatePdf()` dan `getEffectiveAkadTimePdf()`, perenderan kolom `TGL AKAD` di PDF.
    - `public/assets/css/fee-sales.css`: Styling scoped `#modal-group-detail` full screen height (`calc(100vh - 2rem)` desktop / `100vh` mobile) dengan header-footer terkunci dan body scrollable.
  - **Deprecations:** Tidak ada.
- **`2026-11-03` [`UI_REPORT`] — Penyempurnaan Format Cetak PDF Pengajuan Fee Sales (Title, Tanggal Sales, & Sufiks Inisial Nomor Pengajuan):**
  - **Routes & Public Contracts:** Tidak berubah (rute `GET /fee-sales/pengajuan-pdf/(:num)` dan aliasnya tetap aktif).
  - **Controller / Service / Repo / Model:** Tidak berubah.
  - **View / CSS / JS / Tests:**
    - `app/Views/sales/pdf_pengajuan_fee.php`:
      - Mengubah title tag HTML menjadi `<title>Pengajuan Fee Sales</title>` (tanpa nomor pengajuan di tab dokumen).
      - Menyesuaikan tanggal pada judul header laporan (`header-title`) agar memprioritaskan `tanggal_pengajuan_sales` (fallback ke `tanggal_pengajuan` jika kosong) dan dirender dalam format bahasa Indonesia (contoh: `PENGAJUAN FEE SALES — TANGGAL 3 NOVEMBER 2026`).
      - Menambahkan helper `getSalesInitialsPdf($name)` untuk mengekstrak inisial nama sales dan menambahkan sufiks inisial tersebut pada nomor pengajuan di header pojok kiri atas per kelompok sales (contoh: `PGJ/FEE/.../0001.Y` atau `...AP`).
      - Mempertahankan kolom Sales pada tabel rincian unit kavling.
    - `tests/unit/FeeSalesWorkflowTest.php`:
      - Memperbarui assertions pada `testPdfPengajuanViewRendersHeaderAndSignatures` untuk memverifikasi title baru, tanggal sales di header laporan, serta penomoran bersufiks inisial.
  - **Deprecations:** Tidak ada.
- **`2026-10-27` [`UI_REPORT`] — Penambahan & Penguatan Tombol Modal Tolak & Saran Harga pada Lembar Shared Pengajuan:**
  - **Routes & Public Contracts:** Tidak berubah (rute `POST /api/sales-external/reject-group` dan `GET /fee-sales/pengajuan/(:segment)` tetap aktif).
  - **Controller / Service / Repo / Model:** Tidak berubah.
  - **View / CSS / JS:**
    - `app/Views/sales/pengajuan_fee_shared.php`:
      - Menambahkan tombol pemicu modal "Tolak & Saran Harga" (`.btn-trigger-reject`) untuk reviewer MKDT (`can_sign_mkdt`) dan Keuangan (`can_sign_keuangan`) di 3 lokasi strategis: (1) Header Action Bar (desktop), (2) Kartu Tanda Tangan MKDT & Keuangan, dan (3) Mobile Sticky Bottom Bar (`.fee-mobile-sticky-bar`).
      - Menambahkan tombol "Ubah Penolakan / Saran Harga" pada banner alert status `ditolak` agar reviewer dapat meninjau atau memperbarui catatan penolakan dan saran harga net AJB.
      - Mengonfigurasi guard `$canReject = (($group->status ?? '') !== 'void' && ($group->status ?? '') !== 'selesai' && !$hasCair)` sehingga reviewer tetap dapat menolak pengajuan meskipun tanda tangan telah terisi, selama belum ada termin yang dicairkan.
      - Menerapkan event delegation `$(document).on("click", ".btn-trigger-reject", ...)` untuk pembukaan modal `#modal-shared-reject` secara konsisten di seluruh breakpoint viewport.
  - **Deprecations:** Tidak ada.
- **`2026-10-27` [`UI_REPORT`] — Penyelarasan Rincian Unit & Responsivitas Mobile Grid Lembar Shared Pengajuan:**
  - **Routes & Public Contracts:** Tidak berubah (rute `GET /fee-sales/pengajuan/(:segment)` tetap aktif).
  - **Controller / Service / Repo / Model:** Tidak berubah.
  - **View / CSS / JS:**
    - `app/Views/sales/pengajuan_fee_shared.php`: Menghapus blok inline `<style>` demi kepatuhan arsitektur aset 3-tier; menambahkan grouping `$groupedUnitItems` berbasis `$it->id_fee_sales ?: ($it->id_mkdt . '_' . $it->id_sales)` untuk mengonsolidasikan baris multi-termin per unit kavling; merombak tampilan desktop (`d-none d-md-block`) menjadi layout Hierarchical Grouped Rows 12 kolom identik dengan modal `#modal-group-detail` (highlight kuning `.tenor-highlight-yellow` pada termin yang diajukan, badge riwayat `.tenor-cair-badge` dan `.tenor-diajukan-other-badge`, serta status & nomor cek terpadu); merombak tampilan mobile (`d-block d-md-none`) menjadi Responsive Card Grid System bebas horizontal overflow dengan 2-kolom Financial Micro-Grid (`.fee-shared-finance-grid`) dan 2x2 Tenor Grid Box (`.fee-shared-tenor-grid`) dengan highlight emas `.active-target` pada termin yang diajukan.
    - `public/assets/css/fee-sales.css`: Menambahkan scoped CSS `.fee-shared-page`: `.fee-shared-finance-grid`, `.fee-finance-item`, `.fee-finance-full`, `.fee-shared-tenor-grid`, `.fee-tenor-box`, dan `.fee-tenor-box.active-target` (highlight emas `#fff3cd` + border `#ffc107` untuk termin target).
  - **Deprecations:** Tidak ada.
- **`2026-10-27` [`UI_REPORT`] — Konsolidasi 1 Baris Unit Multi-Termin & Badge Riwayat Termin Cross-Pengajuan:**
  - **Routes & Public Contracts:** Tidak berubah.
  - **Controller / Service / Repo / Model:**
    - `app/Repositories/SalesExternalRepository.php`: Memperbarui method `getPengajuanGroupDetail(int $idPengajuan)` dengan memperkaya query `$tList` melalui `LEFT JOIN fee_sales_pengajuan_item pi` dan `fee_sales_pengajuan fsp` (filter `fsp.status != 'void'`), sehingga setiap objek termin di `all_termin` menyertakan atribut riwayat pengajuan: `id_pengajuan`, `pengajuan_nomor`, `pengajuan_status`, `pengajuan_tanggal`.
  - **View / CSS / JS:**
    - `public/assets/css/fee-sales.css`: Menambahkan class styling `.tenor-diajukan-other-badge` (badge biru penanda termin diajukan di pengajuan lain) dan `.tenor-cair-badge` (badge hijau penanda termin sudah cair).
    - `public/assets/js/sales/fee_sales.js`: Mengonsolidasikan perenderan `#table-group-items` menjadi 1 baris per unit kavling di `#modal-group-detail` meski diajukan multi-termin sekaligus; menandai seluruh kolom termin yang diajukan dalam pengajuan aktif dengan highlight kuning `.tenor-highlight-yellow`; merender status riwayat historis untuk termin yang sudah cair (`.tenor-cair-badge` + tanggal cair + No Cek), termin yang diajukan di pengajuan lain (`.tenor-diajukan-other-badge` + tanggal pengajuan + nomor pengajuan), dan termin yang belum diajukan; serta menyajikan status & nomor cek dan aksi multi-termin individual secara rapi.
  - **Deprecations:** Tidak ada.
- **`2026-10-27` [`UI_REPORT`] — Restrukturisasi Tabel Detail Pengajuan (Hierarchical Grouped Rows) & Interaktivitas Baris Pengajuan:**
  - **Routes & Public Contracts:** Tidak berubah.
  - **Controller / Service / Repo / Model:** Tidak berubah.
  - **View / CSS / JS:**
    - `app/Views/sales/fee_sales.php`: Merombak tabel `#table-group-items` di dalam `#modal-group-detail` dari struktur 10 kolom menjadi 12 kolom terstruktur (menyelaraskan layout header grup kavling dan matriks tenor pencairan).
    - `public/assets/css/fee-sales.css`: Menambahkan interaktivitas baris pengajuan `#table-pengajuan-group tbody tr` (`cursor: pointer`, hover background styling), scoped styling `.group-header-row` (background header abu gelap halus dan tipografi penanda unit kavling), serta `.tenor-highlight-yellow` (badge kuning cerah untuk tenor aktif yang diajukan).
    - `public/assets/js/sales/fee_sales.js`: Menyederhanakan tombol aksi detail pada tabel pengajuan `#table-pengajuan-group` menjadi icon-only (`<i class="fas fa-eye"></i>`), menambahkan event delegation row click pada `#table-pengajuan-group tbody tr` untuk membuka detail pengajuan `#modal-group-detail` secara otomatis, serta merombak fungsi `renderGroupItemsTable(items)` menjadi layout Hierarchical Grouped Rows (baris 1: Header Grup Unit Kavling Sales/Konsumen/Kavling/Booking/Akad, baris 2: rincian nominal, matriks tenor 30%, 60%, 10%, 100% dengan highlight kuning `.tenor-highlight-yellow` pada tenor aktif diajukan, status & nomor cek, dan aksi).
  - **Deprecations:** Tidak ada.
- **`2026-10-26` [`FEATURE`] — Penolakan Pengajuan (Rejection Protocol), Perhitungan Ulang Termin In-Place, & Relaksasi Locking Harga Net AJB:**
  - **Routes & Public Contracts:**
    - Menambahkan rute `POST /api/sales-external/reject-group` (`SalesExternalController::rejectPengajuanGroup`) untuk penolakan pengajuan oleh MKDT (`can_sign_mkdt`) atau Keuangan (`can_sign_keuangan`).
    - Menambahkan rute `POST /api/sales-external/recalculate-termin` (`SalesExternalController::recalculateTermin`) untuk kalkulasi ulang nominal termin in-place berdasarkan harga net AJB terbaru.
  - **Database & Migrations:**
    - Menjalankan migration `2026-10-26-070000_AddRejectionAndSuggestedPriceToFeeSalesPengajuan.php`.
    - Menambahkan kolom `rejected_by`, `rejected_role`, `rejected_at`, `reject_reason`, `suggested_harga_net_ajb`, serta enum status `'ditolak'` pada tabel `fee_sales_pengajuan`.
    - Menambahkan kolom `suggested_harga_net_ajb` pada tabel `fee_sales_pengajuan_item`.
    - Mendaftarkan event notifikasi baru `fee_sales_pengajuan_rejected` pada tabel `notification_event_types` (kategori 'Fee Sales', target `3;4`).
  - **Controller / Service / Repo:**
    - Menambahkan method `SalesExternalService::rejectPengajuanGroup()`, `SalesExternalService::recalculateTerminNominals()`, dan `SalesExternalRepository::hasCairTerminByMkdt()`.
    - Memperbarui invariant locking `harga_net_ajb`: digantikan dari `hasGeneratedTerminByMkdt` menjadi `hasCairTerminByMkdt` (harga net AJB hanya terkunci jika sudah ada termin yang berstatus `sudah_cair`).
    - Reset status pengajuan otomatis dari `'ditolak'` kembali ke `'diajukan'` saat termin dihitung ulang.
  - **View / CSS / JS:**
    - Menambahkan modal `#modal-reject-pengajuan`, tombol "Perbaharui Nominal Pencairan Tiap Termin" (`.btn-recalculate-termin`), dan banner `#creator-rejected-notif-banner` pada `app/Views/sales/fee_sales.php` dan `app/Views/sales/pengajuan_fee_shared.php`.
    - Menambahkan styling status badge `'ditolak'`, banner notifikasi rejected, dan penataan form rejection di `public/assets/css/fee-sales.css`.
    - Memperbarui `public/assets/js/sales/fee_sales.js`: handling modal reject, handling AJAX recalculate termin, dan banner peringatan penolakan untuk pembuat.
- **`2026-10-26` [`DOCS`] — Dokumentasi Komprehensif Arsitektur, Kontrak Publik, Deep-Dive Frontend/Backend, & Matriks Debugging `SalesExternal` (`Fee Sales`):**
  - **Routes & Public Contracts:** Mendokumentasikan secara lengkap seluruh 29 rute eksplisit (Web & API) beserta guard permission (`can_view`, `can_manage`, `can_submit_spp`, `can_cairkan`, `can_sign_mkdt`, `can_sign_keuangan`), kontrak parameter request/response, serta Cross-Module Callers (inbound dari `MKDT` & `Notifikasi`, outbound ke `BpbFileService`, `FileAccessService`, `Mpdf_lib`, `HistoryService`, dan `NotifikasiService`).
  - **Controller / Service / Repo / Model:** Merinci 7 Owner Tables (`sales`, `sales_history`, `fee_sales`, `fee_sales_termin`, `fee_sales_pengajuan`, `fee_sales_pengajuan_item`, `fee_sales_setting_akses`), akses baca/tulis pada Shared Tables (`mkdt.harga_net_ajb`, `pencairan_akad_plan`, `pencairan_akad_pengajuan`, `user_signature_profiles`), serta tanggung jawab bisnis setiap method utama di `SalesExternalService`, `SalesExternalRepository`, `FeeSalesPdfService`, dan `FeeSalesSettingService`.
  - **View / CSS / JS:** Mendokumentasikan struktur DOM 2 Tab & 9 Modal pada `app/Views/sales/fee_sales.php` (753 baris), stylesheet `public/assets/css/fee-sales.css` (267 baris), serta rentang baris dan alur kerja fungsi-fungsi krusial di `public/assets/js/sales/fee_sales.js` (2618 baris) dan `master_sales.js` (272 baris).
  - **Deprecations & Debugging Matrix:** Mencatat status legacy/deprecated untuk `#modal-spp` (`POST /api/sales-external/submit-spp`), `POST /api/sales-external/edit-pengajuan`, dan rute alias, serta menambahkan tabel **Debugging & End-to-End Tracing Matrix** (10 skenario *Symptom $\rightarrow$ JS/DOM $\rightarrow$ Endpoint $\rightarrow$ Service/Repo $\rightarrow$ DB*).
- **`2026-10-26` [`SYNC`] — Sinkronisasi status & inventori modul melalui `module:sync`.**
