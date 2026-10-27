# Module: SalesExternal

**Status:** `MAPPED`  
**Readiness Score:** `60/100`  
**Last Synced:** `2026-10-27 12:46:08` @ `ad0c900`  

## Purpose
Pengelolaan Master Data Sales External (agen/broker properti independen), perjanjian kerjasama (PKS), tarif komisi (subsidi & komersil), riwayat audit tarif, integrasi transaksi konsumen MKDT, pengaturan matriks hak akses (`fee_sales_setting_akses`), tracking dan pencairan Fee Sales bertahap (skema 100% atau termin 30%-60%-10%), pengelolaan multi-sales fee per kavling, serta **Pengajuan Pencairan** lintas sales dengan verifikasi tanda tangan digital 3 pihak (Pembuat, MKDT, Keuangan), protokol penolakan (*rejection protocol*) dengan saran revisi harga net AJB, kalkulasi ulang nominal tiap termin in-place, pembatalan (*void*), tautan berbagi (*shareable mobile link*), cetak laporan PDF format Landscape (A4-L), pencatatan nomor cek pencairan, dan integrasi 6 event notifikasi real-time/outbox untuk departemen Keuangan & MKDT.

## Out of scope
- Komisi/gaji sales internal/in-house (dikelola oleh payroll/SDM).
- Pembayaran selain fee sales (BPB, subkon, dsb).

## Invariants
- **Server-Side Financial Recalculation (`cleanNumeric()`):** Perhitungan total fee (`total_fee`) dan nominal per termin (`fee_sales_termin.nominal`) wajib dihitung ulang di level Service (`SalesExternalService`) dari basis `harga_net_ajb` dan persentase komisi (`persentase_fee`) atau nominal manual (`total_fee_manual` ketika `is_manual_fee = 1`); tidak boleh hanya mengandalkan kalkulasi client-side.
- **Relaksasi Locking Harga Jual Net AJB (`harga_net_ajb`):** Kolom Harga Jual Net AJB (`mkdt.harga_net_ajb` & `fee_sales.harga_net_ajb`) hanya dikunci (`disabled` pada `#modal-shared-harga-ajb` di UI dan ditolak oleh backend guard di `SalesExternalService::saveFeeHeader`) apabila salah satu termin fee pada kavling (`id_mkdt`) tersebut telah berstatus cair (`SalesExternalRepository::hasCairTerminByMkdt($idMkdt) === true`). Selama belum ada termin yang cair (`sudah_cair`), revisi harga jual net AJB tetap diperbolehkan meskipun jadwal termin sudah di-generate atau sedang diajukan.
- **Protokol Penolakan Pengajuan (*Rejection Protocol*):** Penolakan batch pengajuan (`SalesExternalService::rejectPengajuanGroup`) hanya dapat dilakukan oleh role MKDT (`can_sign_mkdt`) atau Keuangan (`can_sign_keuangan`) selama status pengajuan bukan `selesai` atau `void`. Alasan penolakan (`reject_reason`) wajib diisi, dengan opsi menyertakan saran revisi harga net AJB (`suggested_harga_net_ajb`) baik di tingkat header maupun per item kavling. Status pengajuan diubah menjadi `'ditolak'`, audit penolakan dicatat (`rejected_by`, `rejected_role`, `rejected_at`, `reject_reason`), item menyimpan `suggested_harga_net_ajb`, log dicatat ke `history_log`, dan memicu notifikasi `fee_sales_pengajuan_rejected`.
- **Perhitungan Ulang Nominal Tiap Termin In-Place (`recalculateTerminNominals`):** Pengajuan yang berstatus `'ditolak'` (atau pengajuan aktif yang belum dicairkan) dapat diperbarui nominal terminnya secara in-place berdasarkan `harga_net_ajb` terbaru pada kavling terkait. Aksi ini mereset status pengajuan dari `'ditolak'` kembali menjadi `'diajukan'`, membersihkan jejak penolakan (`rejected_by = NULL`, `rejected_role = NULL`, `rejected_at = NULL`, `reject_reason = NULL`), memperbarui nominal tiap termin di `fee_sales_termin` dan `fee_sales_pengajuan_item`, serta mencatat riwayat perubahan ke `history_log`.
- **Auto-Skema Termin Berdasarkan Akad Indent (`mkdt.akad_indent`):** Jika unit berstatus **Akad Indent** (`akad_indent == 1`), default skema termin otomatis dipilih **100%** (`skema_termin = '100'`, 1 termin tunggal); jika reguler (`akad_indent == 0`), default skema termin adalah **30%-60%-10%** (`skema_termin = '30_60_10'`, 3 termin).
- **Auto-Create Sales Non-PKS:** Input nama sales baru via Select2 tags pada `#modal-fee-sales` otomatis membuat record di tabel `sales` dengan `has_pks = 0`, `fee_subsidi = 1.00`, `fee_komersil = 1.00`, dan `status = 'aktif'`.
- **Audit Trail Perubahan Tarif & PKS:** Perubahan `fee_subsidi`, `fee_komersil`, `tanggal_pks`, `tanggal_exp_pks`, atau `lampiran_pks` pada Master Sales selalu dicatat dalam `sales_history`.
- **Proteksi Termin Aktif/Lunas:** Termin yang sudah berstatus `sudah_cair` dikunci dan tidak dapat diubah nominalnya, di-generate ulang, atau dihapus (`deleteFeeEntry` menolak penghapusan jika ada termin `!= 'belum_diajukan'`).
- **Aturan Pembatalan (*Void*) Pengajuan:** `SalesExternalService::voidPengajuanGroup` hanya diizinkan apabila belum ada satupun item termin dalam grup yang berstatus `sudah_cair` dan wajib menyertakan alasan pembatalan (`void_reason`) untuk audit trail. Saat di-void, seluruh termin terkait dikembalikan ke `belum_diajukan`.
- **Verifikasi Password Tanda Tangan Digital:** Penandatanganan pengajuan oleh MKDT (`can_sign_mkdt`) dan Keuangan (`can_sign_keuangan`) pada `SalesExternalService::signPengajuanGroup` wajib melalui verifikasi password akun pengguna aktif (`Myth\Auth\Password::verify`).
- **Atomicity:** Seluruh operasi tulis multi-tabel wajib dibungkus dalam Database Transaction (`$this->db->transBegin()` / `transCommit()` / `transRollback()`).

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

## Contracts
### A. Public HTTP Endpoints & Routes Inventory
| Method | URI / Pattern | Tipe | Handler | Status Migrasi | Target API / Catatan |
|---|---|---|---|---|---|
| `GET` | `/master-sales` | `Explicit` | `\App\Controllers\Web\SalesExternalController::index` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/fee-sales` | `Explicit` | `\App\Controllers\Web\SalesExternalController::feeSales` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/fee-sales/pengajuan-pdf/(:num)` | `Explicit` | `\App\Controllers\Web\SalesExternalController::pengajuanPdf/$1` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/fee-sales/export-pdf-pengajuan/(:num)` | `Explicit` | `\App\Controllers\Web\SalesExternalController::pengajuanPdf/$1` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/fee-sales/pengajuan-signature/(:num)/(:segment)` | `Explicit` | `\App\Controllers\Web\SalesExternalController::pengajuanSignature/$1/$2` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/fee-sales/pengajuan/(:segment)` | `Explicit` | `\App\Controllers\Web\SalesExternalController::pengajuanShared/$1` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
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
| `GET` | `/setting-fee-sales` | `Explicit` | `FeeSalesSettingController::index` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
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
#### `FeeSalesPdfService.php`
- `render(int $idPengajuan)`
#### `FeeSalesSettingService.php`
- `listPermissions()`
- `savePermissions(array $permissions, ?int $userId = null)`
- `getUserPermissions(?int $userId = null)`

## File Inventory
### Controllers (4)
- `app/Controllers/Web/SalesExternalController.php` (167 baris, 5 public methods: `index()`, `feeSales()`, `pengajuanPdf(int $id)`, `pengajuanSignature(int $id, string $role)`, `pengajuanShared(string $token)`)
- `app/Controllers/Api/SalesExternalController.php` (527 baris, 22 public methods: `listMasterSales()`, `getSales(int $id)`, `saveMasterSales()`, `getSalesHistory(int $id)`, `getActiveSalesList()`, `listFeeSales()`, `getFeeDetail(int $idMkdt)`, `saveFeeHeader()`, `deleteFeeEntry(int $idFeeSales)`, `submitPengajuanSpp()`, `konfirmasiCair()`, `editPengajuan()`, `editCair()`, `preparePengajuanGroup()`, `createPengajuanGroup()`, `listPengajuanGroup()`, `getPengajuanGroupDetail(int $id)`, `konfirmasiCairGroup()`, `voidPengajuanGroup()`, `signPengajuanGroup()`, `rejectPengajuanGroup()`, `recalculateTermin()`)
- `app/Controllers/Web/Settings/FeeSalesSettingController.php` (22 baris, 1 public methods: `index()`)
- `app/Controllers/Api/Settings/FeeSalesSettingController.php` (93 baris, 2 public methods: `listPermissions()`, `savePermissions()`)

### Services (3)
- `app/Services/SalesExternalService.php` (1824 baris, 13 public methods)
- `app/Services/FeeSalesPdfService.php` (97 baris, 1 public methods)
- `app/Services/Settings/FeeSalesSettingService.php` (212 baris, 3 public methods)

### Repositories (1)
- `app/Repositories/SalesExternalRepository.php` (847 baris, 13 public methods: `hasGeneratedTerminByMkdt(int $idMkdt)`, `hasCairTerminByMkdt(int $idMkdt)`, `userSnapshot(int $userId)`, `getMasterSalesList(array $params = [])`, `getSalesById(int $id)`, `getSalesHistory(int $idSales)`, `getActiveSales()`, `getFeeSalesList(array $params = [])`, `getFeeDetailByMkdt(int $idMkdt)`, `getFeeDetailsByMkdtIds(array $mkdtIds)`, `getPengajuanGroupList(array $params = [])`, `getPengajuanGroupDetail(int $idPengajuan)`, `getPengajuanGroupByToken(string $tokenOrId)`)

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
- `public/assets/js/sales/fee_sales.js` (3199 baris)

## Key Functions
- **Controller (`app/Controllers/Api/SalesExternalController.php`):**
  - `rejectPengajuanGroup()`: Memvalidasi izin role (`can_sign_mkdt` atau `can_sign_keuangan`), memvalidasi input `id_pengajuan`, `role`, `reject_reason`, dan array `item_prices` (saran harga per unit), mendelegasikan eksekusi penolakan ke `SalesExternalService::rejectPengajuanGroup()`.
  - `recalculateTermin()`: Menerima request kalkulasi ulang nominal termin untuk kavling terkait (`id_pengajuan`, `id_mkdt`, `harga_net_ajb`), mendelegasikan ke `SalesExternalService::recalculateTerminNominals()`.
  - `saveFeeHeader()`: Menangani penyimpanan kartu komisi dan sinkronisasi harga net AJB dengan validasi `hasCairTerminByMkdt()`.
  - `createPengajuanGroup()`: Membuat batch pengajuan multi-unit dan menghasilkan nomor otomatis.
  - `signPengajuanGroup()`: Verifikasi kata sandi pengguna dan penyimpanan tanda tangan digital pemeriksa/penyetuju.
  - `konfirmasiCairGroup()`: Eksekusi pencairan termin massal beserta pencatatan nomor cek dan bukti cair.
  - `voidPengajuanGroup()`: Pembatalan pengajuan dengan pengembalian status termin ke `belum_diajukan`.
- **Service (`app/Services/SalesExternalService.php`):**
  - `rejectPengajuanGroup(int $idPengajuan, string $role, array $data, int $userId): array`: Mengubah status `fee_sales_pengajuan` menjadi `'ditolak'`, menyimpan audit penolakan (`rejected_by`, `rejected_role`, `rejected_at`, `reject_reason`, `suggested_harga_net_ajb`), menyimpan saran harga pada `fee_sales_pengajuan_item.suggested_harga_net_ajb`, mencatat riwayat ke `history_log`, dan menembakkan notifikasi `fee_sales_pengajuan_rejected` ke Keuangan & MKDT.
  - `recalculateTerminNominals(array $data, ?int $userId = null): array`: Mengambil seluruh termin aktif yang belum cair pada kavling terkait, menghitung ulang nominal masing-masing termin berdasarkan `harga_net_ajb` terbaru, mengupdate `fee_sales_termin.nominal` dan `fee_sales_pengajuan_item.nominal`, mengupdate `fee_sales.total_fee`, mereset status pengajuan dari `'ditolak'` kembali ke `'diajukan'`, mengosongkan metadata penolakan, dan mencatat log ke `history_log`.
  - `saveFeeHeader(array $data, int|bool|null $userId = null, bool $sendNotification = true): array`: Menyimpan header komisi multi-sales, memperbarui `mkdt.harga_net_ajb` (hanya jika `!hasCairTerminByMkdt`), dan men-generate jadwal termin sesuai skema (`100%` untuk Akad Indent atau `30%-60%-10%` untuk reguler).
  - `saveMasterSales(array $data, ?int $userId, ?UploadedFile $filePks = null)`: Simpan/update profil agen dan mencatat audit trail tarif ke `sales_history`.
- **Service (`app/Services/FeeSalesPdfService.php`):**
  - `render(int $idPengajuan)`: Menghasilkan binary PDF Landscape (A4-L) dengan agregasi data per sales dan 3 tanda tangan digital.
- **Service (`app/Services/Settings/FeeSalesSettingService.php`):**
  - `listPermissions()`, `savePermissions(array $permissions, ?int $userId = null)`, `getUserPermissions(?int $userId = null)`: Pengelolaan matriks hak akses RBAC dan sinkronisasi menu sidebar.
- **Repository (`app/Repositories/SalesExternalRepository.php`):**
  - `hasCairTerminByMkdt(int $idMkdt): bool`: Query cepat untuk mengecek keberadaan termin berstatus `sudah_cair` pada unit transaksi tertentu (dipakai sebagai guard revisi harga net AJB).
  - `hasGeneratedTerminByMkdt(int $idMkdt): bool`: Mengecek apakah termin sudah pernah di-generate untuk unit tersebut.
  - `getFeeSalesList(array $params)`: Query JOIN kompleks daftar fee sales kavling Tab 1.
  - `getPengajuanGroupList(array $params)`: Query DataTables daftar batch pengajuan Tab 2 dengan status dan filter proyek.
  - `getPengajuanGroupDetail(int $idPengajuan)`: Mengambil data header pengajuan lengkap beserta tanda tangan 3 pihak, status rejection (`rejected_by`, `rejected_role`, `rejected_at`, `reject_reason`, `suggested_harga_net_ajb`), dan daftar item termin beserta saran harga (`suggested_harga_net_ajb`). Query `$tList` diperkaya dengan `LEFT JOIN fee_sales_pengajuan_item pi` dan `fee_sales_pengajuan fsp` (filter `fsp.status != 'void'`), sehingga setiap objek termin di `all_termin` menyertakan atribut riwayat pengajuan cross-pengajuan: `id_pengajuan`, `pengajuan_nomor`, `pengajuan_status`, `pengajuan_tanggal`.
- **Frontend JavaScript & Stylesheet (`public/assets/js/sales/fee_sales.js`, `public/assets/css/fee-sales.css`, `app/Views/sales/pengajuan_fee_shared.php`):**
  - `renderGroupItemsTable(items)`: Merender tabel detail item pengajuan (`#table-group-items`) di dalam `#modal-group-detail` dengan konsolidasi 1 baris per unit kavling (meskipun diajukan multi-termin untuk unit yang sama). Baris 1: Header Grup Unit Kavling lengkap (Sales, Konsumen, Kavling, Tanggal Booking, Tanggal Akad). Baris 2: Data nominal (Harga Net AJB, % Fee, Total Fee), matriks status tenor pencairan (30%, 60%, 10%, 100%) dengan penandaan warna kuning `.tenor-highlight-yellow` pada seluruh kolom termin yang diajukan dalam pengajuan aktif, rendering status riwayat termin historis (`.tenor-cair-badge` jika sudah cair + tanggal cair + No Cek, `.tenor-diajukan-other-badge` jika diajukan di pengajuan lain + tanggal pengajuan + nomor pengajuan, dan `Belum Diajukan` jika belum diajukan), serta kolom Status & No Cek dan Aksi yang mendukung multi-termin individual secara rapi.
  - **Penyelarasan Lembar Shared Pengajuan (`app/Views/sales/pengajuan_fee_shared.php`):**
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
1. **Pengajuan SPP Satuan (`#modal-spp` & `POST /api/sales-external/submit-spp`):**
   - **Lokasi:** Modal `#modal-spp` di `app/Views/sales/fee_sales.php` (L517–563), handler `#form-spp` di `public/assets/js/sales/fee_sales.js`, dan `SalesExternalController::submitPengajuanSpp()` $\rightarrow$ `SalesExternalService::submitPengajuanSpp()`.
   - **Status:** **LEGACY / DORMANT** (tombol pemicu `.btn-ajukan-spp` pada baris termin sudah tidak dirender oleh `buildTerminRowsHtml()`).
   - **Pengganti:** Gunakan alur **Batch Pengajuan Pencairan** melalui checkbox Tab 1 $\rightarrow$ `#modal-create-group` $\rightarrow$ `POST /api/sales-external/create-group`.
2. **Edit Pengajuan SPP Satuan (`POST /api/sales-external/edit-pengajuan`):**
   - **Lokasi:** `SalesExternalController::editPengajuan()` $\rightarrow$ `SalesExternalService::updatePengajuan()`.
   - **Status:** **DEPRECATED** (tidak lagi dipanggil dari UI `fee_sales.js`).
   - **Pengganti:** Gunakan fitur **Void Pengajuan** (`#modal-void-pengajuan` $\rightarrow$ `POST /api/sales-external/void-group`) untuk membatalkan pengajuan yang salah (selama belum ada termin yang cair), lalu buat pengajuan baru.
3. **Route Aliases (Backward Compatibility):**
   - `GET /fee-sales/export-pdf-pengajuan/(:num)` $\rightarrow$ Alias untuk `GET /fee-sales/pengajuan-pdf/(:num)`.
   - `DELETE /api/sales-external/fee-entry/(:num)` $\rightarrow$ Alias untuk `POST /api/sales-external/delete-fee-entry/(:num)`.
4. **Properti Flat Backward-Compatibility di `SalesExternalRepository::getFeeDetailByMkdt()`:**
   - Menyalin elemen pertama `$feeList[0]` ke properti flat level root (`$row->id_fee_sales`, `$row->persentase_fee`, `$row->total_fee`, `$row->termin`, dll.) demi menjaga kompatibilitas kode lama, sementara UI multi-sales modern membaca array `$row->fee_list`.

---

## Readiness Checklist
- [ ] Has Service Layer
- [ ] Has Repository Layer
- [ ] Web & API Controllers Separated
- [x] Explicit Routes Defined
- [ ] Zero Controller Layer Violations
- [x] MODULE.md Initialized

> **Layer Violations Detected:**
> - `app/Controllers/Web/SalesExternalController.php`: new ProyekModel()
> - `app/Controllers/Api/SalesExternalController.php`: Database::connect()

## Dead Code Candidates
_Jalankan `php spark module:deadcode SalesExternal` untuk mengaudit dead code kandidat._

## Notes
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
  - Ekspor PDF (`/fee-sales/pengajuan-pdf/{id}`) mengelompokkan tabel per sales external, mengonsolidasikan multi-termin pada kavling yang sama menjadi 1 baris per unit (highlight kuning pada termin diajukan), mengurutkan kavling berdasarkan `akad_tgl` ASC, menampilkan No Pengajuan di pojok kiri atas, dan merender 3 kolom tanda tangan (Pembuat, MKDT, Keuangan).
- **Pengaturan Hak Akses Modul (`/setting-fee-sales`):**
  - Dikelola oleh `FeeSalesSettingController` & `FeeSalesSettingService` berbasis tabel `fee_sales_setting_akses` (`can_view`, `can_manage`, `can_submit_spp`, `can_cairkan`, `can_sign_mkdt`, `can_sign_keuangan`), dengan proteksi penuh untuk Role Admin (ID 1).
- **Integrasi 6 Event Notifikasi Fee Sales (`NotificationEvent` & `SalesExternalService`):**
  - Terdaftar dengan kategori `Fee Sales` dan `relevant_groups = '3;4'` (Keuangan & MKDT): `fee_sales_pengajuan_created`, `fee_sales_pengajuan_rejected`, `fee_sales_termin_generated`, `fee_sales_harga_net_updated`, `fee_sales_ttd_signed`, dan `fee_sales_pencairan`.
  - Saat `saveFeeHeader()` dipanggil secara internal dari `createPengajuanGroup()`, argumen `$sendNotification = false` menekan pengiriman notifikasi duplikat.
  - Klik notifikasi `fee_sales*` di navbar diarahkan ke `/notif/open/{id_notif}` menuju `fee-sales/pengajuan/{share_token}` atau `fee-sales?open_mkdt={id_mkdt}`.
- **Status Struktur Modular Saat Ini:**
  - Controller dan Service utama (`Web/SalesExternalController.php`, `Api/SalesExternalController.php`, `Services/SalesExternalService.php`, `Repositories/SalesExternalRepository.php`) masih berada di root layer dan menunggu sesi refaktor khusus untuk dipindahkan ke subfolder `Web/SalesExternal/`, `Api/SalesExternal/`, `Services/SalesExternal/`, dan `Repositories/SalesExternal/`.

## Change Log
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
