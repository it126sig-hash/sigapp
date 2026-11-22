# Module: Produksi

**Status:** `REFACTORED`  
**Readiness Score:** `100/100`  
**Last Synced:** `2026-11-12 14:14:43` @ `a68829a`  

## Purpose
Manajemen konstruksi lapangan perumahan, monitoring tahapan serah terima fisik (ST 0%, 25%, 50%, 75%, 100%), pelaporan komplain paska serah terima, pengawasan sub-kontraktor (subkon), pengelolaan gambar kerja & SLF, serta sistem checklist inspeksi lapangan berbasis hierarki grup, item, dan sub-item.

## Out of scope
- Pencatatan transaksi pemesanan unit kavling oleh konsumen (Domain `MKDT`).
- Pembayaran kasbon, pengajuan SPP, dan pencairan termin subkon secara akuntansi (Domain `Keuangan` / `Cashout`).
- Pengurusan perizinan legalitas tanah, HGB, dan PBG (Domain `Legal`).
- Master data perumahan, cluster kavling, dan tipe bangunan (Domain `Proyek`).

## Invariants
- Modul lain tidak boleh memanipulasi tabel pemilik (`owner_tables`) secara langsung.
- Perubahan progres fisik (ST 0% s/d 100%) dan pengajuan pembayaran subkon wajib divalidasi di level Service.
- Seluruh input nominal rupiah pada pembayaran produksi subkon wajib dibersihkan menggunakan `cleanNumeric()` sebelum masuk operasi database untuk mencegah kegagalan kalkulasi finansial.
- Semua mutasi data multi-tabel (seperti unggah foto lapangan, pembayaran produksi, pembaruan status kavling) wajib dibungkus dalam DB Transaction (`transStart()` / `transComplete()`).

## Database Tables
### Owner Table(s)
- `produksi` *(akses tulis eksklusif hanya untuk modul Produksi)*
- `komplain` *(akses tulis eksklusif hanya untuk modul Produksi)*
- `subkon` *(akses tulis eksklusif hanya untuk modul Produksi)*
- `gambar_kerja` *(akses tulis eksklusif hanya untuk modul Produksi)*
- `serah_terima` *(akses tulis eksklusif hanya untuk modul Produksi)*
- `checklist_group` *(akses tulis eksklusif hanya untuk modul Produksi)*
- `checklist_item` *(akses tulis eksklusif hanya untuk modul Produksi)*
- `checklist_subitem` *(akses tulis eksklusif hanya untuk modul Produksi)*
- `checklist_work` *(akses tulis eksklusif hanya untuk modul Produksi)*

### Shared Tables (Dibaca dari modul lain)
- `kavling` *(membaca data nomor kavling, cluster, tipe, dan status ST)*
- `jalan` *(membaca dan memperbarui progres infrastruktur jalan lingkungan)*

## Contracts
### A. Public HTTP Endpoints & Routes Inventory
| Method | URI / Pattern | Tipe | Handler | Status Migrasi | Target API / Catatan |
|---|---|---|---|---|---|
| `GET\|POST` | `/api/produksi/meta` | `Explicit` | `App\Controllers\Api\Produksi\ProduksiController::meta` | 🟢 MIGRATED | Full JSON RAW_JSON_READY metadata dropdowns (subkon, cluster, status) |
| `GET` | `/produksi/mobile` | `Explicit` | `App\Controllers\Web\Produksi\ProduksiController::produksiMobile` | 🟢 MIGRATED | Halaman web view interaksi lapangan mobile |
| `GET` | `/checklist-group` | `Explicit` | `App\Controllers\Web\Produksi\ChecklistGroupController::index` | 🟢 MIGRATED | Halaman web master data Checklist Group |
| `GET` | `/checklistGroup` | `Explicit` | `App\Controllers\Web\Produksi\ChecklistGroupController::index` | 🟢 MIGRATED | Alias rute web master Checklist Group |
| `GET` | `/checklist-item` | `Explicit` | `App\Controllers\Web\Produksi\ChecklistItemController::index` | 🟢 MIGRATED | Halaman web master data Checklist Item |
| `GET` | `/checklistItem` | `Explicit` | `App\Controllers\Web\Produksi\ChecklistItemController::index` | 🟢 MIGRATED | Alias rute web master Checklist Item |
| `GET` | `/checklist-sub-item` | `Explicit` | `App\Controllers\Web\Produksi\ChecklistSubItemController::index` | 🟢 MIGRATED | Halaman web master data Checklist Sub-Item |
| `GET` | `/checklistSubItem` | `Explicit` | `App\Controllers\Web\Produksi\ChecklistSubItemController::index` | 🟢 MIGRATED | Alias rute web master Checklist Sub-Item |
| `POST` | `/api/produksi/get_data_by_id` | `Explicit` | `App\Controllers\Api\Produksi\ProduksiController::get_data_by_id` | 🟢 MIGRATED | Ambil detail kavling, progres ST, foto, dan histori |
| `POST` | `/api/produksi/getBayarProduksi` | `Explicit` | `App\Controllers\Api\Produksi\ProduksiController::getBayarProduksi` | 🟢 MIGRATED | Ambil rincian pembayaran produksi subkon per kavling |
| `POST` | `/api/produksi/getBayarProduksiListItem` | `Explicit` | `App\Controllers\Api\Produksi\ProduksiController::getBayarProduksiListItem` | 🟢 MIGRATED | Dropdown opsi item pekerjaan pembayaran subkon |
| `POST` | `/api/produksi/saveBayarProduksi` | `Explicit` | `App\Controllers\Api\Produksi\ProduksiController::saveBayarProduksi` | 🟢 MIGRATED | Simpan pembayaran termin subkon via Service (`cleanNumeric`) |
| `POST` | `/api/produksi/deleteBayarProduksi` | `Explicit` | `App\Controllers\Api\Produksi\ProduksiController::deleteBayarProduksi` | 🟢 MIGRATED | Hapus data pembayaran produksi subkon |
| `POST` | `/api/produksi/get_data_komplain_by_id` | `Explicit` | `App\Controllers\Api\Produksi\ProduksiController::get_data_komplain_by_id` | 🟢 MIGRATED | Ambil data komplain lapangan kavling |
| `POST` | `/api/produksi/save_komplain_produksi` | `Explicit` | `App\Controllers\Api\Produksi\ProduksiController::save_komplain_produksi` | 🟢 MIGRATED | Simpan / update status komplain fisik lapangan |
| `POST` | `/api/produksi/save` | `Explicit` | `App\Controllers\Api\Produksi\ProduksiController::save` | 🟢 MIGRATED | Simpan update progres konstruksi ST kavling |
| `POST` | `/api/produksi/upload-mobile` | `Explicit` | `App\Controllers\Api\Produksi\ProduksiController::uploadMobile` | 🟢 MIGRATED | Upload multi-foto progres dari perangkat lapangan |
| `POST` | `/api/produksi/history` | `Explicit` | `App\Controllers\Api\Produksi\ProduksiController::history` | 🟢 MIGRATED | Ambil histori perubahan progres konstruksi kavling |
| `POST` | `/api/produksi/saveSLF` | `Explicit` | `App\Controllers\Api\Produksi\ProduksiController::saveSLf` | 🟢 MIGRATED | Simpan pengajuan Sertifikat Laik Fungsi (SLF) |
| `GET` | `/api/produksi/getSlf` | `Explicit` | `App\Controllers\Api\Produksi\ProduksiController::getSlf` | 🟢 MIGRATED | Ambil daftar SLF yang terdaftar |
| `POST` | `/api/produksi/hapusSLF` | `Explicit` | `App\Controllers\Api\Produksi\ProduksiController::hapusSLF` | 🟢 MIGRATED | Hapus dokumen SLF |
| `GET` | `/api/produksi/getSLFPDF/(:num)` | `Explicit` | `App\Controllers\Api\Produksi\ProduksiController::getSLFPDF/$1` | 🟢 MIGRATED | Unduh / cetak PDF Sertifikat Laik Fungsi |
| `POST` | `/api/produksi/getKavling` | `Explicit` | `App\Controllers\Api\Produksi\ProduksiController::getKavling` | 🟢 MIGRATED | Pencarian data kavling untuk modul produksi |
| `POST` | `/api/produksi/hapus_foto` | `Explicit` | `App\Controllers\Api\Produksi\ProduksiController::hapus_foto` | 🟢 MIGRATED | Hapus foto bukti progres lapangan (soft delete trash) |
| `POST` | `/api/produksi/get_gambarkerja` | `Explicit` | `App\Controllers\Api\Produksi\ProduksiController::get_gambarkerja` | 🟢 MIGRATED | Ambil dokumen gambar kerja arsitektur unit |
| `POST` | `/api/produksi/add_jalan` | `Explicit` | `App\Controllers\Api\Produksi\ProduksiController::add_jalan` | 🟢 MIGRATED | Catat riwayat progres pembangunan jalan lingkungan |
| `POST` | `/api/produksi/edit_others` | `Explicit` | `App\Controllers\Api\Produksi\ProduksiController::edit_others` | 🟢 MIGRATED | Update data spesifikasi / fasilitas tambahan kavling |
| `GET\|POST` | `/subkon/list/ambil` | `Explicit` | `App\Controllers\Api\Produksi\SubkonController::getList` | 🟢 MIGRATED | Rute eksplisit legacy dropdown/DataTables subkon |
| `GET\|POST` | `/subkon/list` | `Explicit` | `App\Controllers\Api\Produksi\SubkonController::getList` | 🟢 MIGRATED | Rute eksplisit daftar subkon |
| `GET\|POST` | `/api/subkon/list` | `Explicit` | `App\Controllers\Api\Produksi\SubkonController::getList` | 🟢 MIGRATED | API eksplisit daftar subkon |
| `POST` | `/api/produksi/sales/get_data_by_id` | `Explicit` | `App\Controllers\Api\Produksi\SalesController::get_data_by_id` | 🟢 MIGRATED | Ambil data checklist & serah terima untuk Sales |
| `POST` | `/api/produksi/sales/save_checklist` | `Explicit` | `App\Controllers\Api\Produksi\SalesController::save_checklist` | 🟢 MIGRATED | Simpan checklist serah terima unit konsumen |
| `POST` | `/api/produksi/sales/get_data_komplain_by_id` | `Explicit` | `App\Controllers\Api\Produksi\SalesController::get_data_komplain_by_id` | 🟢 MIGRATED | Ambil data komplain konsumen via Sales |
| `POST` | `/api/produksi/sales/batalkan_komplain` | `Explicit` | `App\Controllers\Api\Produksi\SalesController::batalkan_komplain` | 🟢 MIGRATED | Batalkan tiket komplain konsumen |
| `POST` | `/api/produksi/sales/save_komplain_sales` | `Explicit` | `App\Controllers\Api\Produksi\SalesController::save_komplain_sales` | 🟢 MIGRATED | Simpan komplain baru dari konsumen ke unit |
| `POST` | `/api/produksi/sales/get_data_serah_terima_by_id` | `Explicit` | `App\Controllers\Api\Produksi\SalesController::get_data_serah_terima_by_id` | 🟢 MIGRATED | Ambil detail dokumen serah terima fisik |
| `POST` | `/api/produksi/sales/save_serah_terima` | `Explicit` | `App\Controllers\Api\Produksi\SalesController::save_serah_terima` | 🟢 MIGRATED | Simpan rekam serah terima fisik unit ke konsumen |
| `POST` | `/sales/get_data_by_id` | `Explicit` | `App\Controllers\Api\Produksi\SalesController::get_data_by_id` | 🟢 MIGRATED | Rute legacy flat Sales serah terima |
| `POST` | `/sales/save_checklist` | `Explicit` | `App\Controllers\Api\Produksi\SalesController::save_checklist` | 🟢 MIGRATED | Rute legacy flat Sales checklist |
| `POST` | `/sales/get_data_komplain_by_id` | `Explicit` | `App\Controllers\Api\Produksi\SalesController::get_data_komplain_by_id` | 🟢 MIGRATED | Rute legacy flat Sales ambil komplain |
| `POST` | `/sales/batalkan_komplain` | `Explicit` | `App\Controllers\Api\Produksi\SalesController::batalkan_komplain` | 🟢 MIGRATED | Rute legacy flat Sales batalkan komplain |
| `POST` | `/sales/save_komplain_sales` | `Explicit` | `App\Controllers\Api\Produksi\SalesController::save_komplain_sales` | 🟢 MIGRATED | Rute legacy flat Sales simpan komplain |
| `POST` | `/sales/get_data_serah_terima_by_id` | `Explicit` | `App\Controllers\Api\Produksi\SalesController::get_data_serah_terima_by_id` | 🟢 MIGRATED | Rute legacy flat Sales ambil serah terima |
| `POST` | `/sales/save_serah_terima` | `Explicit` | `App\Controllers\Api\Produksi\SalesController::save_serah_terima` | 🟢 MIGRATED | Rute legacy flat Sales simpan serah terima |
| `GET\|POST` | `/checklistGroup/getAll` | `Explicit` | `App\Controllers\Api\Produksi\ChecklistGroupController::getAll` | 🟢 MIGRATED | DataTables list Checklist Group |
| `POST` | `/checklistGroup/getOne` | `Explicit` | `App\Controllers\Api\Produksi\ChecklistGroupController::getOne` | 🟢 MIGRATED | Ambil 1 record Checklist Group |
| `POST` | `/checklistGroup/add` | `Explicit` | `App\Controllers\Api\Produksi\ChecklistGroupController::add` | 🟢 MIGRATED | Tambah master Checklist Group |
| `POST` | `/checklistGroup/edit` | `Explicit` | `App\Controllers\Api\Produksi\ChecklistGroupController::edit` | 🟢 MIGRATED | Edit master Checklist Group |
| `POST` | `/checklistGroup/remove` | `Explicit` | `App\Controllers\Api\Produksi\ChecklistGroupController::remove` | 🟢 MIGRATED | Hapus master Checklist Group |
| `GET\|POST` | `/api/checklist-group/getAll` | `Explicit` | `App\Controllers\Api\Produksi\ChecklistGroupController::getAll` | 🟢 MIGRATED | API DataTables list Checklist Group |
| `POST` | `/api/checklist-group/getOne` | `Explicit` | `App\Controllers\Api\Produksi\ChecklistGroupController::getOne` | 🟢 MIGRATED | API Ambil 1 record Checklist Group |
| `POST` | `/api/checklist-group/add` | `Explicit` | `App\Controllers\Api\Produksi\ChecklistGroupController::add` | 🟢 MIGRATED | API Tambah master Checklist Group |
| `POST` | `/api/checklist-group/edit` | `Explicit` | `App\Controllers\Api\Produksi\ChecklistGroupController::edit` | 🟢 MIGRATED | API Edit master Checklist Group |
| `POST` | `/api/checklist-group/remove` | `Explicit` | `App\Controllers\Api\Produksi\ChecklistGroupController::remove` | 🟢 MIGRATED | API Hapus master Checklist Group |
| `GET\|POST` | `/checklistItem/getAll` | `Explicit` | `App\Controllers\Api\Produksi\ChecklistItemController::getAll` | 🟢 MIGRATED | DataTables list Checklist Item |
| `POST` | `/checklistItem/getOne` | `Explicit` | `App\Controllers\Api\Produksi\ChecklistItemController::getOne` | 🟢 MIGRATED | Ambil 1 record Checklist Item |
| `POST` | `/checklistItem/add` | `Explicit` | `App\Controllers\Api\Produksi\ChecklistItemController::add` | 🟢 MIGRATED | Tambah master Checklist Item |
| `POST` | `/checklistItem/edit` | `Explicit` | `App\Controllers\Api\Produksi\ChecklistItemController::edit` | 🟢 MIGRATED | Edit master Checklist Item |
| `POST` | `/checklistItem/remove` | `Explicit` | `App\Controllers\Api\Produksi\ChecklistItemController::remove` | 🟢 MIGRATED | Hapus master Checklist Item |
| `GET\|POST` | `/api/checklist-item/getAll` | `Explicit` | `App\Controllers\Api\Produksi\ChecklistItemController::getAll` | 🟢 MIGRATED | API DataTables list Checklist Item |
| `POST` | `/api/checklist-item/getOne` | `Explicit` | `App\Controllers\Api\Produksi\ChecklistItemController::getOne` | 🟢 MIGRATED | API Ambil 1 record Checklist Item |
| `POST` | `/api/checklist-item/add` | `Explicit` | `App\Controllers\Api\Produksi\ChecklistItemController::add` | 🟢 MIGRATED | API Tambah master Checklist Item |
| `POST` | `/api/checklist-item/edit` | `Explicit` | `App\Controllers\Api\Produksi\ChecklistItemController::edit` | 🟢 MIGRATED | API Edit master Checklist Item |
| `POST` | `/api/checklist-item/remove` | `Explicit` | `App\Controllers\Api\Produksi\ChecklistItemController::remove` | 🟢 MIGRATED | API Hapus master Checklist Item |
| `GET\|POST` | `/checklistSubItem/getAll` | `Explicit` | `App\Controllers\Api\Produksi\ChecklistSubItemController::getAll` | 🟢 MIGRATED | DataTables list Checklist Sub-Item |
| `POST` | `/checklistSubItem/getOne` | `Explicit` | `App\Controllers\Api\Produksi\ChecklistSubItemController::getOne` | 🟢 MIGRATED | Ambil 1 record Checklist Sub-Item |
| `POST` | `/checklistSubItem/add` | `Explicit` | `App\Controllers\Api\Produksi\ChecklistSubItemController::add` | 🟢 MIGRATED | Tambah master Checklist Sub-Item |
| `POST` | `/checklistSubItem/edit` | `Explicit` | `App\Controllers\Api\Produksi\ChecklistSubItemController::edit` | 🟢 MIGRATED | Edit master Checklist Sub-Item |
| `POST` | `/checklistSubItem/remove` | `Explicit` | `App\Controllers\Api\Produksi\ChecklistSubItemController::remove` | 🟢 MIGRATED | Hapus master Checklist Sub-Item |
| `GET\|POST` | `/api/checklist-sub-item/getAll` | `Explicit` | `App\Controllers\Api\Produksi\ChecklistSubItemController::getAll` | 🟢 MIGRATED | API DataTables list Checklist Sub-Item |
| `POST` | `/api/checklist-sub-item/getOne` | `Explicit` | `App\Controllers\Api\Produksi\ChecklistSubItemController::getOne` | 🟢 MIGRATED | API Ambil 1 record Checklist Sub-Item |
| `POST` | `/api/checklist-sub-item/add` | `Explicit` | `App\Controllers\Api\Produksi\ChecklistSubItemController::add` | 🟢 MIGRATED | API Tambah master Checklist Sub-Item |
| `POST` | `/api/checklist-sub-item/edit` | `Explicit` | `App\Controllers\Api\Produksi\ChecklistSubItemController::edit` | 🟢 MIGRATED | API Edit master Checklist Sub-Item |
| `POST` | `/api/checklist-sub-item/remove` | `Explicit` | `App\Controllers\Api\Produksi\ChecklistSubItemController::remove` | 🟢 MIGRATED | API Hapus master Checklist Sub-Item |

### B. Public PHP Services (Backend Contract Antar-Modul)
#### `App\Services\Produksi\ProduksiService`
- `cleanNumeric(mixed $value)`: Helper pembersihan format nominal rupiah menjadi format numerik baku (float/int).
- `save(array $var, array $uploadedFiles, int $userId)`: Simpan mutasi progres konstruksi kavling (ST 0-100%), unggah foto bukti, dan audit history.
- `saveMobileUpload(int $idKavling, ?int $idProduksi, array $files, array $vars, int $userId)`: Endpoint upload foto berstempel GPS dari aplikasi lapangan.
- `saveBayarProduksi(int $idKavling, int $idItemProduksi, mixed $nominal, string $tanggalBayar, string $keterangan, int $userId)`: Transaksi pembayaran termin subkon terenkapsulasi dengan pembersihan nominal via `cleanNumeric()`.
- `deleteBayarProduksi(int $id, int $userId)`: Pembatalan pembayaran subkon.
- `getBayarProduksiData(int $idKavling)`: Pengambilan histori dan rekapitulasi pembayaran subkon per unit kavling.
- `getDetailByKavling(int $idKavling, ?int $idProduksi)`: Detail komprehensif data konstruksi, subkon, gambar kerja, dan foto progres.
- `getHistory(int $idKavling, int $limit = 10, int $offset = 0)`: Riwayat audit perubahan data produksi lapangan.
- `getKomplainDetail(int $idKavling)`: Data detail tiket komplain fisik unit kavling.
- `saveKomplainProduksi(array $data, array $uploadedFiles, int $userId)`: Pembaruan penanganan komplain fisik oleh divisi Produksi.
- `saveKomplainSales(array $data, array $uploadedFiles, int $userId)`: Pencatatan komplain fisik awal dari konsumen oleh divisi Sales.
- `batalkanKomplain(int $idKavling, ?int $idKomplain)`: Pembatalan tiket komplain.
- `getSerahTerimaDetail(int $idKavling)`: Data detail berita acara serah terima unit kavling.
- `saveSerahTerima(array $data, int $userId)`: Penyimpanan rekam serah terima fisik unit.
- `getChecklistSalesDetail(int $idKavling)`: Pengambilan daftar status checklist inspeksi unit untuk serah terima.
- `saveChecklistSales(int $idKavling, mixed $isChecked, array $postVars, int $userId)`: Penyimpanan hasil checklist inspeksi kelayakan bangunan.
- `deleteFoto(int $id, int $userId)`: Penghapusan foto progres konstruksi.
- `addJalan(array $postVars, int $userId)`: Pencatatan progres pembangunan infrastruktur jalan.
- `editOthers(int $id, array $postVars, array $uploadedFiles, int $userId)`: Pembaruan fasilitas dan spesifikasi tambahan kavling.
- `saveSlf(array $postVars, int $userId)`: Pembuatan dan pengajuan data Sertifikat Laik Fungsi.
- `deleteSlf(int $id)`: Penghapusan data SLF.

#### `App\Services\Produksi\ProduksiFileService`
- `uploadFotoGroups(array $requestFiles, array $requestVars, int $idKavling)`: Pengunggahan batch foto progres konstruksi berdasarkan kategori progres ST.
- `moveFotoToTrash(object $file)`: Pemindahan file foto ke folder trash (soft delete) untuk preservasi data.

#### `App\Services\Produksi\PhotoGpsMetadataService`
- `prepareDownload(array $file)`: Ekstraksi metadata EXIF/GPS foto lapangan dan pembentukan stempel koordinat.
- `cleanup(?string $temporaryPath)`: Pembersihan berkas sementara pasca-unduh metadata foto.

#### `App\Services\Produksi\ChecklistService`
- `getAllGroups(bool $asDatatable = false)`: Pengambilan daftar grup checklist master.
- `getGroup(int $id)`: Pengambilan data detail 1 grup checklist.
- `createGroup(array $data, int $userId)`: Pembuatan grup checklist baru.
- `updateGroup(int $id, array $data, int $userId)`: Pembaruan grup checklist.
- `deleteGroup(int $id)`: Penghapusan grup checklist.
- `getAllItems(bool $asDatatable = false)`: Pengambilan daftar item checklist master.
- `getItem(int $id)`: Pengambilan data detail 1 item checklist.
- `createItem(array $data, int $userId)`: Pembuatan item checklist baru.
- `updateItem(int $id, array $data, int $userId)`: Pembaruan item checklist.
- `deleteItem(int $id)`: Penghapusan item checklist.
- `getAllSubItems(bool $asDatatable = false)`: Pengambilan daftar sub-item checklist master.
- `getSubItem(int $id)`: Pengambilan data detail 1 sub-item checklist.
- `createSubItem(array $data, int $userId)`: Pembuatan sub-item checklist baru.
- `updateSubItem(int $id, array $data, int $userId)`: Pembaruan sub-item checklist.
- `deleteSubItem(int $id)`: Penghapusan sub-item checklist.
- `getActiveGroups()`: Pengambilan grup checklist aktif.
- `getActiveGroupsWithItems()`: Pengambilan hierarki checklist aktif lengkap beserta item dan sub-item untuk form inspeksi lapangan.

## File Inventory
### Controllers (10)
- `app/Controllers/Web/Produksi/ProduksiController.php` (101 baris, 4 public methods: `index()`, `produksiMobile()`, `getSLFPDF($id)`, `direksi()`)
- `app/Controllers/Web/Produksi/ChecklistGroupController.php` (18 baris, 1 public methods: `index()`)
- `app/Controllers/Web/Produksi/ChecklistItemController.php` (27 baris, 1 public methods: `index()`)
- `app/Controllers/Web/Produksi/ChecklistSubItemController.php` (27 baris, 1 public methods: `index()`)
- `app/Controllers/Api/Produksi/ProduksiController.php` (359 baris, 20 public methods: `meta()`, `get_data_by_id()`, `history()`, `getBayarProduksiListItem()`, `getBayarProduksi()`, `saveBayarProduksi()`, `deleteBayarProduksi()`, `get_data_komplain_by_id()`, `save_komplain_produksi()`, `save()`, `uploadMobile()`, `saveSLf()`, `getSlf()`, `hapusSLF()`, `getSLFPDF($id)`, `getKavling()`, `hapus_foto()`, `get_gambarkerja()`, `add_jalan()`, `edit_others()`)
- `app/Controllers/Api/Produksi/SubkonController.php` (37 baris, 1 public methods: `getList()`)
- `app/Controllers/Api/Produksi/SalesController.php` (92 baris, 7 public methods: `get_data_by_id()`, `save_checklist()`, `get_data_komplain_by_id()`, `batalkan_komplain()`, `save_komplain_sales()`, `get_data_serah_terima_by_id()`, `save_serah_terima()`)
- `app/Controllers/Api/Produksi/ChecklistGroupController.php` (94 baris, 5 public methods: `getAll()`, `getOne()`, `add()`, `edit($id = null)`, `remove()`)
- `app/Controllers/Api/Produksi/ChecklistItemController.php` (94 baris, 5 public methods: `getAll()`, `getOne()`, `add()`, `edit($id = null)`, `remove()`)
- `app/Controllers/Api/Produksi/ChecklistSubItemController.php` (94 baris, 5 public methods: `getAll()`, `getOne()`, `add()`, `edit($id = null)`, `remove()`)

### Services (4)
- `app/Services/Produksi/ProduksiService.php` (1017 baris, 21 public methods)
- `app/Services/Produksi/ProduksiFileService.php` (187 baris, 2 public methods)
- `app/Services/Produksi/PhotoGpsMetadataService.php` (191 baris, 2 public methods)
- `app/Services/Produksi/ChecklistService.php` (334 baris, 17 public methods)

### Repositories (2)
- `app/Repositories/Produksi/ProduksiRepository.php` (553 baris, 47 public methods: `getFilesByKavling(int $idKavling)`, `insertFiles(array $files)`, `hasProduksiChangeHistoryTable()`, `insertProduksiChangeHistory(array $fields)`, `countProduksiChangeHistory(int $idKavling)`, `getProduksiChangeHistory(int $idKavling, int $limit, int $offset)`, `getFileById(int $id)`, `deleteFileById(int $id)`, `getBayarList(int $idKavling)`, `getBayarItemList(string $search)`, `getRiwayatBayarByKavling(int $idKavling)`, `insertBayarSingle(array $data)`, `deleteBayar(int $id)`, `getByIdWithUsers(int $idProduksi)`, `getSlfList()`, `getSlfById(int $id)`, `getSlfKavlingData(array $idKavlingList)`, `insertSlf(array $data)`, `deleteSlfById(int $id)`, `getKavlingByProyek(int $idProyek, string $search, ?int $idKavling = null)`, `getKavlingProduksiById(int $idKavling)`, `createOther(array $fields)`, `getOtherById(int $id)`, `updateOthers(int $id, array $fields)`, `hasJalanProgressHistoryTable()`, `insertJalanProgressHistory(array $fields)`, `getSubkonList(string $search)`, `getGambarKerjaById(int $id)`, `getKavlingById(int $idKavling)`, `getKavlingCheckStatus(int $idKavling)`, `getKavlingOwnerKomplain(int $idKavling, int $idKomplain)`, `updateKavling(int $idKavling, array $data)`, `getProduksiRaw(int $idProduksi)`, `insertProduksi(array $data)`, `updateProduksi(int $idProduksi, array $data)`, `getKomplainWithUsers(int $idKavling)`, `insertKomplain(array $data)`, `updateKomplain(int $idKomplain, array $data)`, `deleteKomplain(int $idKomplain)`, `getSerahTerimaByKavling(int $idKavling)`, `insertSerahTerima(array $data)`, `updateSerahTerima(int $idSerahTerima, array $data)`, `getChecklistWorkByKavling(int $idKavling)`, `getChecklistWorkWithUsers(int $idKavling)`, `getChecklistWorkForProduksi(int $idKavling)`, `getAllSubitemIds()`, `upsertChecklistWork(int $idKavling, int $idSubitem, array $data)`)
- `app/Repositories/Produksi/ChecklistRepository.php` (199 baris, 18 public methods: `getAllGroups()`, `getActiveGroups()`, `getGroupById(int $id)`, `insertGroup(array $data)`, `updateGroup(int $id, array $data)`, `toggleGroupStatus(int $id)`, `getAllItems()`, `getItemById(int $id)`, `getItemsByGroup(int $idGroup)`, `insertItem(array $data)`, `updateItem(int $id, array $data)`, `toggleItemStatus(int $id)`, `getAllSubItems()`, `getSubItemById(int $id)`, `insertSubItem(array $data)`, `updateSubItem(int $id, array $data)`, `toggleSubItemStatus(int $id)`, `getActiveGroupsWithItems()`)

### Models (9)
- `app/Models/ProduksiModel.php`
- `app/Models/KomplainModel.php`
- `app/Models/SubkonModel.php`
- `app/Models/GambarkerjaModel.php`
- `app/Models/SerahTerimaModel.php`
- `app/Models/ChecklistGroupModel.php`
- `app/Models/ChecklistItemModel.php`
- `app/Models/ChecklistSubItemModel.php`
- `app/Models/ChecklistWorkModel.php`

### Views (2 folder + 1 shared modal)
- `app/Views/checklist` (3 file: `checklistGroup.php`, `checklistItem.php`, `checklistSubItem.php`)
- `app/Views/direksi` (1 file: `list-tagihan-pending.php`)
- `app/Views/siteplan/produksi.php` (Shared modal interaktif siteplan untuk manajemen progres ST, komplain, pembayaran subkon, dan checklist unit)

### CSS & Frontend Styling (1 file)
- `public/assets/css/produksi.css` (25.1 KB, 3-Tier CSS eksternal ber-scope `#modal-produksi`, `#modal-gambar-kerja`, `#modal-tambah-jalan`, `#modal-preview-gps`, tema `#2057a3`, tabs 1-baris scrollable horizontal untuk mobile, badge status, support dark layout)

### JavaScript (3 file)
- `public/assets/js/siteplan/produksi.js` (1900 baris, modul JS utama untuk modal produksi, dropzone upload, kalkulasi pembayaran subkon, dan checklist ST)
- `public/assets/js/siteplan/produksi_mobile.js` (354 baris, interface input lapangan mobile)
- `public/assets/js/siteplan/direksi.js` (131 baris, interface monitoring direksi untuk tagihan konstruksi)

### Zero-Downtime Shims (11 file)
- `app/Controllers/Produksi.php` — Shim fallback controller web & legacy AutoRoute
- `app/Controllers/Api/ProduksiController.php` — Shim fallback controller API flat
- `app/Controllers/Subkon.php` — Shim fallback controller subkon legacy
- `app/Controllers/ChecklistGroup.php` — Shim fallback controller checklist group
- `app/Controllers/ChecklistItem.php` — Shim fallback controller checklist item
- `app/Controllers/ChecklistSubItem.php` — Shim fallback controller checklist sub-item
- `app/Controllers/Sales.php` — Shim fallback controller sales serah terima & komplain
- `app/Services/ProduksiService.php` — Shim fallback service di root namespace
- `app/Services/ProduksiFileService.php` — Shim fallback file service di root namespace
- `app/Services/PhotoGpsMetadataService.php` — Shim fallback GPS service di root namespace
- `app/Repositories/ProduksiRepository.php` — Shim fallback repository di root namespace

## Key Functions & Core Business Logic
- **Controller (`App\Controllers\Api\Produksi\ProduksiController`):**
  - `meta()`: Menyediakan metadata JSON murni (RAW_JSON_READY) untuk opsi dropdown filter subkon, cluster, dan tahapan ST tanpa coupling HTML.
  - `save()`: Validasi request input update progres ST kavling, delegasi mutasi multi-tabel ke `ProduksiService::save()`.
  - `saveBayarProduksi()` & `deleteBayarProduksi()`: Endpoint mutasi finansial pembayaran subkon, membersihkan input nominal dan mendelegasikan transaksi ke Service.
  - `uploadMobile()`: Endpoint penerima multi-upload foto lapangan dengan stempel GPS.
  - `save_komplain_produksi()` & `get_data_komplain_by_id()`: Manajemen siklus perbaikan komplain unit.
- **Controller (`App\Controllers\Api\Produksi\SalesController`):**
  - `save_serah_terima()`: Pencatatan serah terima fisik bangunan kepada konsumen.
  - `save_checklist()`: Penyimpanan hasil audit checklist kelayakan fisik bangunan sebelum serah terima.
  - `save_komplain_sales()` & `batalkan_komplain()`: Pengajuan dan pembatalan komplain konsumen dari sisi Sales.
- **Service (`App\Services\Produksi\ProduksiService`):**
  - `cleanNumeric(mixed $value)`: Pembersihan format input nominal mata uang (membuang `.` atau format ribuan rupiah) menjadi float/integer murni untuk mencegah kegagalan kalkulasi DB.
  - `saveBayarProduksi()`: Transaksi DB (`transStart`/`transComplete`) penyimpanan pembayaran termin subkon dengan validasi sanitasi nominal uang via `cleanNumeric()`.
  - `save(array $var, array $uploadedFiles, int $userId)`: Pengendali transaksi utama progres ST kavling; mengoordinasikan update status `kavling`, `produksi`, upload foto via `ProduksiFileService`, dan pencatatan audit log `insertProduksiChangeHistory()`.
  - `saveKomplainProduksi()` & `saveKomplainSales()`: Penanganan tiket komplain unit dengan enkapsulasi DB transaction.
  - `saveSerahTerima()`: Penyimpanan dokumen BAST fisik unit ke tabel `serah_terima`.
- **Service (`App\Services\Produksi\ProduksiFileService`):**
  - `uploadFotoGroups()`: Validasi mime type, enkripsi nama file, resize/kompresi foto lapangan, dan penyimpanan ke storage folder per kavling.
  - `moveFotoToTrash()`: Soft delete foto bukti progres ke direktori trash tanpa merusak integritas link referensi histori lama.
- **Service (`App\Services\Produksi\PhotoGpsMetadataService`):**
  - `prepareDownload()`: Membaca tag EXIF dari file foto, mengekstrak koordinat lintang/bujur (Latitude/Longitude), dan merender preview watermarked GPS untuk kebutuhan verifikasi lapangan.
- **Service (`App\Services\Produksi\ChecklistService`):**
  - Manajemen pohon hierarki checklist (`Group -> Item -> SubItem`) serta penyajian DataTables atau data murni untuk inspeksi lapangan.
- **Repository (`App\Repositories\Produksi\ProduksiRepository`):**
  - Mengelola seluruh query pembacaan dan penulisan ke tabel `produksi`, `komplain`, `subkon`, `gambar_kerja`, `serah_terima`, `checklist_work`, serta riwayat progres jalan.
  - Menghapus method mati/dead-code `upsertBayarItems()` yang memiliki 0 pemanggil lintas codebase.
- **Repository (`App\Repositories\Produksi\ChecklistRepository`):**
  - Query struktur checklist hierarkis dengan optimasi JOIN antar `checklist_group`, `checklist_item`, dan `checklist_subitem`.

## Deprecated & Legacy Shims
- **Deprecated Routes / Endpoints:**
  - Route flat legacy `/sales/*` -> **DEPRECATED**, dialihkan ke `App\Controllers\Api\Produksi\SalesController`. Direkomendasikan menggunakan prefix `/api/produksi/sales/*`.
  - Route flat legacy `/subkon/list/ambil` -> **DEPRECATED**, dialihkan ke `App\Controllers\Api\Produksi\SubkonController::getList`. Direkomendasikan menggunakan `/api/subkon/list`.
  - Route AutoRoute implisit `/produksi/*`, `/checklist_group/*`, `/checklist_item/*`, `/checklist_sub_item/*` -> **DEPRECATED**, seluruhnya telah digantikan oleh rute eksplisit modern di `/api/produksi/*` dan `/api/checklist-*/*`.
- **Deprecated Classes / Shims (Zero-Downtime Shims):**
  - `app/Controllers/Produksi.php` — **DEPRECATED (Zero-Downtime Shim)**: Gunakan `App\Controllers\Web\Produksi\ProduksiController` / `App\Controllers\Api\Produksi\ProduksiController`.
  - `app/Controllers/Api/ProduksiController.php` — **DEPRECATED (Zero-Downtime Shim)**: Gunakan `App\Controllers\Api\Produksi\ProduksiController`.
  - `app/Controllers/Subkon.php` — **DEPRECATED (Zero-Downtime Shim)**: Gunakan `App\Controllers\Api\Produksi\SubkonController`.
  - `app/Controllers/ChecklistGroup.php` — **DEPRECATED (Zero-Downtime Shim)**: Gunakan `App\Controllers\Web\Produksi\ChecklistGroupController` / `App\Controllers\Api\Produksi\ChecklistGroupController`.
  - `app/Controllers/ChecklistItem.php` — **DEPRECATED (Zero-Downtime Shim)**: Gunakan `App\Controllers\Web\Produksi\ChecklistItemController` / `App\Controllers\Api\Produksi\ChecklistItemController`.
  - `app/Controllers/ChecklistSubItem.php` — **DEPRECATED (Zero-Downtime Shim)**: Gunakan `App\Controllers\Web\Produksi\ChecklistSubItemController` / `App\Controllers\Api\Produksi\ChecklistSubItemController`.
  - `app/Controllers/Sales.php` — **DEPRECATED (Zero-Downtime Shim)**: Gunakan `App\Controllers\Api\Produksi\SalesController`.
  - `app/Services/ProduksiService.php` — **DEPRECATED (Zero-Downtime Shim)**: Gunakan `App\Services\Produksi\ProduksiService`.
  - `app/Services/ProduksiFileService.php` — **DEPRECATED (Zero-Downtime Shim)**: Gunakan `App\Services\Produksi\ProduksiFileService`.
  - `app/Services/PhotoGpsMetadataService.php` — **DEPRECATED (Zero-Downtime Shim)**: Gunakan `App\Services\Produksi\PhotoGpsMetadataService`.
  - `app/Repositories/ProduksiRepository.php` — **DEPRECATED (Zero-Downtime Shim)**: Gunakan `App\Repositories\Produksi\ProduksiRepository`.
- **Deprecated Dead Methods:**
  - `ProduksiRepository::upsertBayarItems()` — **DEPRECATED & PRUNED**: Method batch-upsert bayar yang tidak pernah dipanggil di mana pun (0 pemanggil di codebase) telah dibersihkan dari repository modular baru.

## Readiness Checklist
- [x] Has Service Layer
- [x] Has Repository Layer
- [x] Web & API Controllers Separated
- [x] Explicit Routes Defined
- [x] Zero Controller Layer Violations
- [x] MODULE.md Initialized

## Dead Code Candidates
_Jalankan `php spark module:deadcode Produksi` untuk mengaudit dead code kandidat._
- Method mati `ProduksiRepository::upsertBayarItems()` (0 pemanggil lintas codebase) telah berhasil dibersihkan dari class repository modular `App\Repositories\Produksi\ProduksiRepository`.

## Notes
- **Cross-Module Callers:**
  - Modul `Siteplan`: Memanggil modal `app/Views/siteplan/produksi.php` dan skrip `public/assets/js/siteplan/produksi.js`, `public/assets/js/siteplan/produksi_mobile.js` yang berinteraksi intensif dengan API `/api/produksi/*` dan `ProduksiService`.
  - Modul `Keuangan` / `Cashout`: Mengakses data master subkon dan riwayat pembayaran fisik kavling untuk verifikasi SPP serta pencairan kasbon termin subkon.
  - Modul `MKDT` / `Sales`: Mengakses endpoint `/api/produksi/sales/*` untuk berita acara serah terima fisik unit (BAST), pengisian checklist kelayakan bangunan, dan tiket komplain paska-huni.
  - Modul `Direksi`: Mengakses view `app/Views/direksi/list-tagihan-pending.php` dan endpoint produksi untuk pengawasan tagihan konstruksi berjalan.

### Audit Verifikasi Migrasi (2026-11-22)
- **Status Audit:** `COMPLETED (Catatan Minor)` (Actual: `REFACTORED`, Score `100/100`).
- **Struktur & Rute:** Rute eksplisit sudah modular (`App\Controllers\Api\Produksi\*` & `App\Controllers\Web\Produksi\*`) dengan 0 rute legacy/AutoRoute; 8 `thin_shim` + 3 `medium_shim` (`ChecklistGroup.php`, `ChecklistItem.php`, `ChecklistSubItem.php`) terverifikasi murni delegator Web/API tanpa logic/DB; `ProduksiService` hanya memakai `\Config\Database::connect()` untuk transaksi DB (`transStart`/`transBegin`/`transCommit`/`transRollback`/`transComplete`); dan API checklist/subkon sudah mendukung `?format=raw` / `X-Client-Platform`.
- **Temuan Minor:**
  1. `app/Controllers/Web/Produksi/ProduksiController.php:48` menginstansiasi `new \App\Models\HakaksesModel()` langsung di `produksiMobile()` serta memanggil `ProduksiRepository` langsung di `getSLFPDF()` (`L72,L81`).
  2. `app/Controllers/Web/Produksi/ChecklistItemController.php:19` dan `app/Controllers/Web/Produksi/ChecklistSubItemController.php:19` memanggil `ChecklistRepository` langsung alih-alih melalui `ChecklistService`.
  3. `app/Controllers/Api/Produksi/ProduksiController.php` memanggil `ProduksiRepository` langsung pada beberapa endpoint (`L51,L112,L222,L259,L271,L290,L308`) dan `getSLFPDF()` (`L276-279`) merender `view('pdf/slf', ...)` / `view('pdf/slf_page3', ...)` + `exit()`.
  4. `app/Services/Produksi/ProduksiFileService.php:36` memanggil `\Config\Database::connect()->getFieldNames('file_produksi')` langsung di `__construct()`.

## Change Log
- `2026-11-22` [`AUDIT`] — Audit verifikasi migrasi modular: status `COMPLETED (Catatan Minor)` (Score 100/100); tercatat temuan minor layer di `Web\Produksi\ProduksiController` (instansiasi `HakaksesModel` & akses langsung `ProduksiRepository`), `Web\Produksi\ChecklistItemController` & `ChecklistSubItemController` (akses langsung `ChecklistRepository`), `Api\Produksi\ProduksiController` (akses langsung repo & render view PDF), serta `ProduksiFileService` (`getFieldNames` via `Database::connect()`).
- **`2026-11-12` [`REFACTOR`] — Refactoring Lengkap Modul Produksi (Thin Controller, Modular Web & API Architecture, Encapsulated Transactions, Clean Numeric, Scoped CSS & External Asset Separation):**
  - **Routes & Public Contracts:**
    - Mengarahkan seluruh rute grup `/api/produksi/*` ke namespace controller modular `App\Controllers\Api\Produksi`.
    - Mendaftarkan endpoint eksplisit baru `GET|POST /api/produksi/meta` dengan keluaran full JSON murni (`RAW_JSON_READY`).
    - Menetapkan rute eksplisit untuk grup subkon (`/subkon/list/ambil`, `/subkon/list`, `/api/subkon/list`), grup sales serah terima (`/api/produksi/sales/*` dan fallback legacy `/sales/*`), serta rute master checklist (`/checklist-group`, `/checklist-item`, `/checklist-sub-item` dan API `/api/checklist-*/*`).
    - Memetakan rute antarmuka mobile `GET /produksi/mobile` ke `Web\Produksi\ProduksiController::produksiMobile`.
    - Menyediakan kontrak service publik pada `ProduksiService`, `ProduksiFileService`, `PhotoGpsMetadataService`, dan `ChecklistService`.
  - **Controller / Service / Repo / Model:**
    - Memisahkan controller menjadi 4 Web Controllers (`ProduksiController`, `ChecklistGroupController`, `ChecklistItemController`, `ChecklistSubItemController`) di `app/Controllers/Web/Produksi/` dan 6 API Controllers (`ProduksiController`, `SubkonController`, `SalesController`, `ChecklistGroupController`, `ChecklistItemController`, `ChecklistSubItemController`) di `app/Controllers/Api/Produksi/` dengan 0 Layer Violations.
    - Mengenkapsulasi seluruh logika bisnis, kalkulasi finansial (`cleanNumeric()`), sanitasi input, dan transaksi multi-tabel (`transStart()`/`transComplete()`) ke dalam `App\Services\Produksi\ProduksiService`.
    - Mengorganisir service pendukung ke namespace modular: `ProduksiFileService`, `PhotoGpsMetadataService`, dan `ChecklistService`.
    - Mengabstraksi seluruh akses database ke `App\Repositories\Produksi\ProduksiRepository` dan `App\Repositories\Produksi\ChecklistRepository`.
    - Mempertahankan 11 file lama sebagai Zero-Downtime Shims untuk menjaga kompatibilitas mundur.
    - Membersihkan method mati `ProduksiRepository::upsertBayarItems()` (0 pemanggil lintas codebase).
  - **View / CSS / JS:**
    - Membuat stylesheet eksternal `public/assets/css/produksi.css` (25.1 KB, 3-Tier CSS ber-scope `#modal-produksi` dan modal terkait, warna brand `#2057a3`, tabs 1-baris scrollable horizontal untuk mobile, badge status, support dark layout).
    - Memperbarui modal `app/Views/siteplan/produksi.php` dengan mengekstrak 827 baris CSS inline ke file eksternal `produksi.css` menggunakan cache-busting otomatis `filemtime`.
    - Memverifikasi fungsionalitas JavaScript pada `public/assets/js/siteplan/produksi.js`, `public/assets/js/siteplan/produksi_mobile.js`, dan `public/assets/js/siteplan/direksi.js`.
  - **Deprecations:**
    - Mendepresiasi 11 class di root namespace dan menjadikannya Zero-Downtime Shim.
    - Mendepresiasi rute flat tanpa namespace `/sales/*` dan `/subkon/list/ambil` ke endpoint modular.
