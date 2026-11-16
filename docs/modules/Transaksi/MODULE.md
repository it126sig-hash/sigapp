# Module: Transaksi

**Status:** `REFACTORED`  
**Readiness Score:** `100/100`  
**Last Synced:** `2026-11-15 08:53:24` @ `889516a`  

## Purpose
Modul inti (*Core Domain*) yang bertanggung jawab atas eksekusi transaksi jual-beli unit kavling, pengelolaan master daftar harga (*pricelist* `hargajual` & lampiran dokumen SK `file_hargajual`), penetapan harga jual akhir unit kavling (`set_harga`), pergantian konsumen (*consumer replacement*), pembaruan status transaksi & perintah bangun, serta alur pengajuan dan persetujuan pembukaan/request unit kavling (`kavling_request`). Dibangun dengan arsitektur modular **Thin Controller → Service → Repository**, pemisahan tegas antara Web Controller dan API Controller, serta dukungan penuh kontrak **`RAW_JSON_READY`** (`?format=raw` dan endpoint `/meta`) untuk kesiapan integrasi SPA/Mobile.

## Out of scope
- Pencatatan pembayaran angsuran, verifikasi tagihan Cash-In, pencairan dana akad/jaminan KPR, serta pengeluaran kas subkon/operasional Cash-Out (Domain `Keuangan`).
- Administrasi perizinan legalitas lahan, pemecahan sertifikat HGB, PBB, PBG, dan proses akta AJB (Domain `Legal`).
- Pelaksanaan konstruksi fisik lapangan, tahapan progres serah terima (ST 0% s/d 100%), dan checklist inspeksi bangunan (Domain `Produksi`).
- Perencanaan master proyek perumahan, master cluster, master tipe bangunan, dan master jalan lingkungan (Domain `Proyek`).
- Rendering kanvas peta spasial SVG interaktif lintas departemen (Domain `Siteplan`).

## Invariants
- **Kepemilikan Tabel Eksklusif (`Owner Tables`):** Modul lain dilarang memanipulasi tabel pemilik (`kavling`, `kavling_request`, `file_hargajual`, `hargajual`) secara langsung; seluruh operasi tulis wajib melalui Service/Repository modul `Transaksi`.
- **Zero Controller Layer Violations:** Seluruh Controller (`Web\Transaksi\*` dan `Api\Transaksi\*`) hanya bertugas menangani HTTP request/response, validasi awal, dan delegasi ke layer `App\Services\Transaksi\*` tanpa pemanggilan `db_connect()`, query builder, atau Model secara langsung.
- **Sanitasi & Rekalkulasi Finansial Server-Side (`cleanNumeric`):** Seluruh input nominal mata uang wajib dibersihkan menggunakan `cleanNumeric()` di level Service (`HargaJualService` dan `TransaksiService`). Pada `HargaJualService`, komponen harga wajib dihitung ulang di sisi server menggunakan rumus baku:
  - `harga_net = harga_standar - diskon_standar + biaya_hook + kelebihan_tanah + biaya_lain - diskon_khusus`
  - `total_harga = harga_net + ajb_bn + bphtb + ppn + biaya_kpr + biaya_lain_2`
- **Integritas Transaksi Database (`DB Transaction`):** Mutasi multi-tabel (penyimpanan transaksi konsumen & MKDT, pergantian konsumen `replaceConsumer` dengan row-level lock `lockConsumerReplacementContext`, perubahan status & perintah bangun, pembuatan/pembaruan pricelist beserta lampiran `file_hargajual`, serta penetapan harga jual kavling) wajib dibungkus dalam `db->transStart()` dan `db->transComplete()`.
- **Kontrak API `RAW_JSON_READY`:** Endpoint DataTables dan daftar (`POST /api/hargajual/get/all`, `POST /api/hargajual/datatable`, `GET|POST /api/kavling/list`, `POST /api/kavling/list/ambil`) wajib mendukung parameter `?format=raw` (atau body `format=raw`) yang mengembalikan struktur data murni bebas HTML bersama objek `meta`, serta menyediakan endpoint metadata `/api/hargajual/meta` dan `/api/transaksi/meta`.
- **Audit Trail & Notifikasi:** Setiap perubahan harga jual kavling, penyimpanan transaksi konsumen, pergantian konsumen, dan pembaruan status unit wajib dicatat ke riwayat audit (`HistoryService` / `MkdtHistoryService`) serta memicu pengiriman notifikasi lintas divisi (`tambah_notif`) sesuai aturan bisnis.

## Database Tables
### Owner Table(s)
- `kavling` *(akses tulis eksklusif hanya untuk modul Transaksi)*
- `kavling_request` *(akses tulis eksklusif hanya untuk modul Transaksi)*
- `file_hargajual` *(akses tulis eksklusif hanya untuk modul Transaksi)*
- `hargajual` *(akses tulis eksklusif hanya untuk modul Transaksi)*

### Shared Tables (Dibaca dari modul lain)
- `mkdt`
- `konsumen`
- `keuangan`

## Contracts
### A. Public HTTP Endpoints & Routes Inventory
| Method | URI / Pattern | Tipe | Handler | Status Migrasi | Target API / Catatan |
|---|---|---|---|---|---|
| `GET` | `/hargajual` | `Explicit` | `HargajualController::index` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/hargajual/index` | `Explicit` | `HargajualController::index` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/hargajual/get/all` | `Explicit` | `HargajualController::getDataTable` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/hargajual/getDataTable` | `Explicit` | `HargajualController::getDataTable` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/hargajual/getdatatable` | `Explicit` | `HargajualController::getDataTable` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/hargajual/get` | `Explicit` | `HargajualController::getAll` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/hargajual/getAll` | `Explicit` | `HargajualController::getAll` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/hargajual/getall` | `Explicit` | `HargajualController::getAll` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/hargajual/getOne` | `Explicit` | `HargajualController::getOne` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/hargajual/getone` | `Explicit` | `HargajualController::getOne` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/hargajual/add` | `Explicit` | `HargajualController::add` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/hargajual/edit` | `Explicit` | `HargajualController::edit` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/hargajual/remove` | `Explicit` | `HargajualController::remove` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/hargajual/set_harga` | `Explicit` | `HargajualController::set_harga` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/api/hargajual/meta` | `Explicit` | `HargajualController::meta` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/hargajual/meta` | `Explicit` | `HargajualController::meta` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/hargajual/get/all` | `Explicit` | `HargajualController::getDataTable` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/hargajual/datatable` | `Explicit` | `HargajualController::getDataTable` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/hargajual/getDataTable` | `Explicit` | `HargajualController::getDataTable` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/hargajual/get` | `Explicit` | `HargajualController::getAll` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/hargajual/all` | `Explicit` | `HargajualController::getAll` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/hargajual/getAll` | `Explicit` | `HargajualController::getAll` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/hargajual/getOne` | `Explicit` | `HargajualController::getOne` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/hargajual/getone` | `Explicit` | `HargajualController::getOne` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/hargajual/add` | `Explicit` | `HargajualController::add` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/hargajual/edit` | `Explicit` | `HargajualController::edit` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/hargajual/remove` | `Explicit` | `HargajualController::remove` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/hargajual/set_harga` | `Explicit` | `HargajualController::set_harga` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/hargajual/set-harga` | `Explicit` | `HargajualController::set_harga` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/transaksi/simpan` | `Explicit` | `TransaksiController::saveTransaksi` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/transaksi/saveTransaksi` | `Explicit` | `TransaksiController::saveTransaksi` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/transaksi/savetransaksi` | `Explicit` | `TransaksiController::saveTransaksi` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/transaksi/ambilsatu` | `Explicit` | `TransaksiController::getByID` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/transaksi/getByID` | `Explicit` | `TransaksiController::getByID` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/transaksi/getbyid` | `Explicit` | `TransaksiController::getByID` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/transaksi/status/ambilsatu` | `Explicit` | `TransaksiController::getStatusByID` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/transaksi/getStatusByID` | `Explicit` | `TransaksiController::getStatusByID` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/transaksi/getstatusbyid` | `Explicit` | `TransaksiController::getStatusByID` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/transaksi/status/simpan` | `Explicit` | `TransaksiController::saveStatus` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/transaksi/saveStatus` | `Explicit` | `TransaksiController::saveStatus` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/transaksi/savestatus` | `Explicit` | `TransaksiController::saveStatus` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/api/transaksi/meta` | `Explicit` | `TransaksiController::meta` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/transaksi/meta` | `Explicit` | `TransaksiController::meta` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/transaksi/simpan` | `Explicit` | `TransaksiController::saveTransaksi` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/transaksi/ambilsatu` | `Explicit` | `TransaksiController::getByID` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/transaksi/status/ambilsatu` | `Explicit` | `TransaksiController::getStatusByID` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/transaksi/status/simpan` | `Explicit` | `TransaksiController::saveStatus` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/transaksi/konsumen/ganti` | `Explicit` | `TransaksiController::replaceConsumer` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/mkdt/history` | `Explicit` | `MkdtController::history` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/mkdt/consumer-replacement-history` | `Explicit` | `MkdtController::consumerReplacementHistory` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/kavling-request/submit` | `Explicit` | `KavlingRequestController::submit` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/kavling-request/kavling-list` | `Explicit` | `KavlingRequestController::kavlingList` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/kavling-request/list` | `Explicit` | `KavlingRequestController::list` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/kavling-request/update-status` | `Explicit` | `KavlingRequestController::updateStatus` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/kavling/list/ambil` | `Explicit` | `KavlingController::getList` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/kavling/getList` | `Explicit` | `KavlingController::getList` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/api/kavling/list` | `Explicit` | `KavlingController::getList` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/kavling/list` | `Explicit` | `KavlingController::getList` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/kavling/list/ambil` | `Explicit` | `KavlingController::getList` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |

### B. Public PHP Services (Backend Contract Antar-Modul)
#### `TransaksiService.php`
- `getMeta(?int $idProyek = null, ?int $userId = null)`
- `getBundle(?int $idMkdt, ?int $idHargajual, ?int $idKavling)`
- `getStatusById($id_hargajual, $id_kavling, $id_mkdt)`
- `saveTransaksi(array $kons, array $mk, array $um, array $files = [], array $opt = [])`
- `replaceConsumer(int $idMkdt, int $idKavling, int $oldConsumerId, array $consumer, array $files, string $referralCode, int $actorId, bool $allowDuplicateNik = false)`
- `saveStatus(array $data, array $perintahBangun, array $files = [])`
- `createOrUpdate(?int $idMkdtExisting, array $data, array $opt = [])`
- `update($id, $data)`
- `cleanNumeric(mixed $value)`
#### `KavlingRequestService.php`
- `submitRequest(array $data, int $userId)`
- `getList(int $idProyek, ?string $status = null, ?int $page = null, ?int $perPage = null)`
- `getStatusCounts(int $idProyek)`
- `updateStatus(int $idRequest, string $newStatus, int $userId, ?string $alasanTolak = null)`
#### `HargaJualService.php`
- `hasAccess(int $userId)`
- `getProyekList()`
- `getMeta(?int $idProyek, int $userId)`
- `getDataTable(array $var, ?int $idProyek, bool $rawFormat = false)`
- `getAll(?int $idProyek, string $search = '')`
- `getOne(int $id)`
- `create(array $fields, ?UploadedFile $file, int $userId)`
- `update(int $id, array $fields, ?UploadedFile $file, int $userId)`
- `toggleActive(int $id, int $currentIsActive, int $userId)`
- `setHargaKavling(string $rawIdKavling, $idHargaAkhir, int $userId)`
- `cleanNumeric(mixed $value)`

## File Inventory
### Controllers (6)
- `app/Controllers/Web/Transaksi/HargajualController.php` (37 baris, 1 public methods: `index()`)
- `app/Controllers/Api/Transaksi/HargajualController.php` (155 baris, 8 public methods: `meta()`, `getDataTable()`, `getAll()`, `getOne()`, `add()`, `edit($id = null)`, `remove()`, `set_harga()`)
- `app/Controllers/Api/Transaksi/TransaksiController.php` (344 baris, 6 public methods: `meta()`, `getByID()`, `getStatusByID()`, `saveTransaksi()`, `replaceConsumer()`, `saveStatus()`)
- `app/Controllers/Api/Transaksi/MkdtController.php` (67 baris, 2 public methods: `history()`, `consumerReplacementHistory()`)
- `app/Controllers/Api/Transaksi/KavlingRequestController.php` (216 baris, 4 public methods: `submit()`, `kavlingList()`, `list()`, `updateStatus()`)
- `app/Controllers/Api/Transaksi/KavlingController.php` (45 baris, 1 public methods: `getList()`)

### Services (3)
- `app/Services/Transaksi/TransaksiService.php` (1027 baris, 9 public methods)
- `app/Services/Transaksi/KavlingRequestService.php` (290 baris, 4 public methods)
- `app/Services/Transaksi/HargaJualService.php` (576 baris, 11 public methods)

### Repositories (3)
- `app/Repositories/Transaksi/TransaksiRepository.php` (230 baris, 6 public methods: `getKonsumenTransaksi(int $idMkdt)`, `findNikUsage(string $nik, ?int $excludeMkdt = null, ?int $excludeKonsumen = null)`, `getKonsumenByIdKavling($idKavling)`, `getSpptbData(int $idKavling, int $idMkdt)`, `lockConsumerReplacementContext(int $idMkdt, int $idKavling)`, `getLegacyReplacementSpptbData(int $idKavling, int $currentIdMkdt)`)
- `app/Repositories/Transaksi/HargaJualRepository.php` (270 baris, 14 public methods: `getHargaJualById(int $id)`, `hasModuleAccess(int $userId, string $module = 'Hargajual')`, `getProyekList()`, `getDetailById(int $id)`, `getActiveByProyek(?int $idProyek, string $search = '')`, `getDataTableRows(array $var, ?int $idProyek)`, `getDataTableFilteredCount(array $var, ?int $idProyek)`, `getDataTableTotalCount(?int $idProyek)`, `insertFileHargajual(array $data)`, `insertHargaJual(array $data)`, `updateHargaJual(int $id, array $data)`, `findKavlingById(int $idKavling)`, `updateKavlingHarga(int $idKavling, array $data)`, `getPricelistRowById(int $id)`)
- `app/Repositories/Transaksi/KavlingRepository.php` (726 baris, 12 public methods: `getKavlingByIdMkdt(int $id_mkdt)`, `getIdProyekByIdMkdt(int $id_mkdt)`, `getIdProyekByKavling(int $id_kavling)`, `getKavlingById(int $idKavling)`, `getKavlingByIds(array $idKavlings)`, `getKavlingList($id_proyek = null, $search = "", $limit = null, $is_cashout_subkon = 0, $id_cluster = null, $id_jalan = null, $only_available = 0)`, `getDiskresiByKavlingId(int $idKavling)`, `getAll($id_proyek, $id_cluster = null, $id_jalan = null, $id_divisi = null, $kategoriFilters = [])`, `getPerintahBangun($id_kavling)`, `setPerintahBangun($id, $data)`, `getKategoriOptions(int $idProyek)`, `applyKategoriFilter(BaseBuilder $builder, array $filters)`)

### Models (3)
- `app/Models/KavlingModel.php`
- `app/Models/KavlingRequestModel.php`
- `app/Models/FileHargajualModel.php`

### Views (1 folder)
- `app/Views/hargajual` (1 file: `index.php`)

### JavaScript (3 file)
- `public/assets/js/hargajual.js` (681 baris)
- `public/assets/js/siteplan/sales.js` (525 baris)
- `public/assets/js/siteplan/request-kavling.js` (571 baris)

### Zero-Downtime Shims (11 file)
- `app/Controllers/Transaksi.php`
- `app/Controllers/Api/TransaksiController.php`
- `app/Controllers/Api/MkdtController.php`
- `app/Controllers/Api/KavlingRequestController.php`
- `app/Controllers/Hargajual.php`
- `app/Controllers/Kavling.php`
- `app/Services/TransaksiService.php`
- `app/Services/KavlingRequestService.php`
- `app/Repositories/TransaksiRepository.php`
- `app/Repositories/HargaJualRepository.php`
- `app/Repositories/KavlingRepository.php`

## Key Functions
- **Web Controller (`App\Controllers\Web\Transaksi\HargajualController`):**
  - `index()`: Memverifikasi hak akses modul via `HargaJualService::hasAccess()`, mengambil daftar proyek (`getProyekList()`) serta proyek aktif via `ActiveProyekService`, dan merender `app/Views/hargajual/index.php` tanpa akses DB langsung.
- **API Controller — Harga Jual (`App\Controllers\Api\Transaksi\HargajualController`):**
  - `meta()`: Menyediakan metadata JSON murni (`RAW_JSON_READY`) berisi proyek aktif, daftar proyek, dan izin akses user untuk antarmuka Harga Jual.
  - `getDataTable()`: Menyajikan daftar pricelist untuk server-side DataTables maupun format JSON murni (`?format=raw` / `format=raw`) beserta `meta` paginasi.
  - `getAll()` & `getOne()`: Mengambil daftar pricelist aktif per proyek (untuk dropdown Select2) atau detail lengkap 1 pricelist beserta signed URL dokumen SK.
  - `add()`, `edit($id = null)`, `remove()`: Endpoint CRUD pricelist dengan validasi form, upload file SK (`file_hargajual`), dan toggle status aktif/nonaktif.
  - `set_harga()`: Menetapkan `id_harga_akhir` pada satu atau beberapa unit kavling sekaligus dan mencatat audit history.
- **API Controller — Transaksi (`App\Controllers\Api\Transaksi\TransaksiController`):**
  - `meta()`: Menyediakan metadata JSON murni (`RAW_JSON_READY`) untuk transaksi kavling (daftar opsi cara bayar, status transaksi, bank KPR, dan konteks proyek aktif).
  - `getByID()` & `getStatusByID()`: Mengambil bundle lengkap data transaksi konsumen, MKDT, kavling, harga jual, atau rincian status & perintah bangun.
  - `saveTransaksi()`: Validasi input identitas konsumen, data MKDT, jadwal uang muka, serta unggahan berkas pendukung, lalu mendelegasikan penyimpanan transaksional ke `TransaksiService::saveTransaksi()`.
  - `replaceConsumer()`: Memproses pergantian konsumen pada unit kavling berjalan dengan validasi NIK, upload dokumen pendukung, kode referral, dan pencatatan histori pergantian konsumen.
  - `saveStatus()`: Menyimpan perubahan status tahapan transaksi kavling (Booking, SP3K, Akad, Batal, dll.) beserta dokumen perintah bangun.
- **API Controller — MKDT History (`App\Controllers\Api\Transaksi\MkdtController`):**
  - `history()` & `consumerReplacementHistory()`: Mengambil riwayat perubahan data MKDT kavling dan riwayat pergantian konsumen via `MkdtHistoryService`.
- **API Controller — Request Kavling (`App\Controllers\Api\Transaksi\KavlingRequestController`):**
  - `submit()`, `kavlingList()`, `list()`, `updateStatus()`: Mengelola pengajuan pembukaan/request unit kavling oleh sales, penyajian daftar kavling tersedia, dan persetujuan/penolakan request.
- **API Controller — Kavling (`App\Controllers\Api\Transaksi\KavlingController`):**
  - `getList()`: Menyajikan daftar unit kavling terfilter per proyek/cluster/jalan/ketersediaan melalui `KavlingRepository::getKavlingList()`, mendukung format legacy Select2 maupun `?format=raw` (`RAW_JSON_READY`).
- **Service (`App\Services\Transaksi\HargaJualService`):**
  - `cleanNumeric(mixed $value)`: Sanitasi string nominal berformat ribuan (`.`) menjadi integer murni sebelum kalkulasi dan penyimpanan DB.
  - `create()` & `update()`: Menghitung ulang `harga_net` dan `total_harga` di server-side, mengelola upload file SK ke storage (`insertFileHargajual`), menyimpan record `hargajual` dalam transaksi DB, serta mencatat log perubahan.
  - `setHargaKavling()`: Memperbarui `id_harga_akhir` pada tabel `kavling` dalam transaksi DB, mencatat log audit `MkdtHistoryService`, dan memicu notifikasi `tambah_notif`.
  - `getDataTable()`, `getAll()`, `getOne()`, `getMeta()`, `toggleActive()`: Orkestrasi pembacaan data pricelist, generasi signed URL file SK via `FileAccessService`, dan pemformatan output DataTables vs Raw JSON.
- **Service (`App\Services\Transaksi\TransaksiService`):**
  - `cleanNumeric(mixed $value)` & `getMeta(?int $idProyek, ?int $userId)`: Sanitasi nominal finansial transaksi dan penyediaan kontrak metadata transaksi.
  - `getBundle()`, `getStatusById()`, `saveTransaksi()`, `replaceConsumer()`, `saveStatus()`, `createOrUpdate()`, `update()`: Mengelola siklus hidup utama transaksi kavling, sinkronisasi tabel `konsumen`, `mkdt`, `keuangan`, `kavling`, penguncian baris saat pergantian konsumen, pencatatan histori, dan pengiriman notifikasi.
- **Service (`App\Services\Transaksi\KavlingRequestService`):**
  - `submitRequest()`, `getList()`, `updateStatus()`: Orkestrasi bisnis pengajuan dan approval request kavling beserta notifikasi status.
- **Repositories (`App\Repositories\Transaksi\*`):**
  - `HargaJualRepository`: Enkapsulasi penuh query tabel `hargajual`, `file_hargajual`, `kavling`, dan `proyek` (14 public methods untuk DataTables, detail, CRUD, dan akses modul).
  - `TransaksiRepository`: Query relasi transaksi konsumen, pengecekan duplikasi NIK (`findNikUsage`), data SPPTB (`getSpptbData`), dan row lock pergantian konsumen (`lockConsumerReplacementContext`).
  - `KavlingRepository`: Query master & status unit kavling lintas modul (`getKavlingList`, `getAll`, `getPerintahBangun`, `setPerintahBangun`, `getKategoriOptions`, `applyKategoriFilter`).
- **Frontend Assets (`public/assets/js/*` & `public/assets/css/*`):**
  - `public/assets/js/hargajual.js`: Logika antarmuka Master Harga Jual (server-side DataTables, Flatpickr tanggal berlaku, Select2 proyek/cluster/tipe, Quill editor keterangan, kalkulasi otomatis `syncHargaNet()` & `load_total()`, dropzone upload file SK, serta aksi CRUD `window.add`, `window.edit`, `window.detail`, `window.remove`, `window.save`).
  - `public/assets/css/hargajual.css`: Stylesheet terisolasi dengan scoping `.hargajual-page`, `#add-modal`, `#detail-modal`, tema Primary Blue `#2057a3`, dan komponen `.pricelist-dropzone.bpb-dropzone`.
  - `public/assets/js/siteplan/sales.js` & `public/assets/js/siteplan/request-kavling.js`: Interaksi modal transaksi penjualan, ganti konsumen, ubah status kavling, dan request kavling pada peta Siteplan.

## Deprecated & Legacy Shims
- **Deprecated Classes / Shims (11 Zero-Downtime Shims):**
  - `app/Controllers/Hargajual.php` — **DEPRECATED (Dual-Shim Delegator)**: Mendelegasikan `index()` ke `App\Controllers\Web\Transaksi\HargajualController` dan method API (`getDataTable`, `getAll`, `getOne`, `add`, `edit`, `remove`, `set_harga`, `meta`) ke `App\Controllers\Api\Transaksi\HargajualController` via `initController()`.
  - `app/Controllers/Transaksi.php` — **DEPRECATED (Zero-Downtime Shim)**: Gunakan `App\Controllers\Api\Transaksi\TransaksiController`.
  - `app/Controllers/Api/TransaksiController.php` — **DEPRECATED (Zero-Downtime Shim)**: Gunakan `App\Controllers\Api\Transaksi\TransaksiController`.
  - `app/Controllers/Api/MkdtController.php` — **DEPRECATED (Zero-Downtime Shim)**: Gunakan `App\Controllers\Api\Transaksi\MkdtController`.
  - `app/Controllers/Api/KavlingRequestController.php` — **DEPRECATED (Zero-Downtime Shim)**: Gunakan `App\Controllers\Api\Transaksi\KavlingRequestController`.
  - `app/Controllers/Kavling.php` — **DEPRECATED (Zero-Downtime Shim)**: Gunakan `App\Controllers\Api\Transaksi\KavlingController`.
  - `app/Services/TransaksiService.php` — **DEPRECATED (Zero-Downtime Shim)**: Gunakan `App\Services\Transaksi\TransaksiService`.
  - `app/Services/KavlingRequestService.php` — **DEPRECATED (Zero-Downtime Shim)**: Gunakan `App\Services\Transaksi\KavlingRequestService`.
  - `app/Repositories/TransaksiRepository.php` — **DEPRECATED (Zero-Downtime Shim)**: Gunakan `App\Repositories\Transaksi\TransaksiRepository`.
  - `app/Repositories/HargaJualRepository.php` — **DEPRECATED (Zero-Downtime Shim)**: Gunakan `App\Repositories\Transaksi\HargaJualRepository`.
  - `app/Repositories/KavlingRepository.php` — **DEPRECATED (Zero-Downtime Shim)**: Gunakan `App\Repositories\Transaksi\KavlingRepository`.
- **Deprecated Legacy Routes (Dipertahankan sebagai Alias Eksplisit ke Controller Modular):**
  - `POST /hargajual/get/all`, `/hargajual/getDataTable`, `/hargajual/getdatatable` -> Gunakan `POST /api/hargajual/get/all` atau `POST /api/hargajual/datatable`.
  - `POST /hargajual/get`, `/hargajual/getAll`, `/hargajual/getall` -> Gunakan `POST /api/hargajual/get` atau `POST /api/hargajual/all`.
  - `POST /hargajual/getOne`, `/hargajual/getone` -> Gunakan `POST /api/hargajual/getOne`.
  - `POST /hargajual/add`, `/hargajual/edit`, `/hargajual/remove`, `/hargajual/set_harga` -> Gunakan `POST /api/hargajual/add`, `/api/hargajual/edit`, `/api/hargajual/remove`, `/api/hargajual/set_harga`.
  - `POST /transaksi/simpan`, `/transaksi/saveTransaksi`, `/transaksi/savetransaksi` -> Gunakan `POST /api/transaksi/simpan`.
  - `POST /transaksi/ambilsatu`, `/transaksi/getByID`, `/transaksi/getbyid` -> Gunakan `POST /api/transaksi/ambilsatu`.
  - `POST /transaksi/status/ambilsatu`, `/transaksi/getStatusByID`, `/transaksi/getstatusbyid` -> Gunakan `POST /api/transaksi/status/ambilsatu`.
  - `POST /transaksi/status/simpan`, `/transaksi/saveStatus`, `/transaksi/savestatus` -> Gunakan `POST /api/transaksi/status/simpan`.
  - `POST /kavling/list/ambil`, `/kavling/getList` -> Gunakan `GET|POST /api/kavling/list` atau `POST /api/kavling/list/ambil`.

## Readiness Checklist
- [x] Has Service Layer
- [x] Has Repository Layer
- [x] Web & API Controllers Separated
- [x] Explicit Routes Defined
- [x] Zero Controller Layer Violations
- [x] MODULE.md Initialized

## Dead Code Candidates
_Jalankan `php spark module:deadcode Transaksi` untuk mengaudit dead code kandidat._

## Notes
### Arsitektur & Kontrak `RAW_JSON_READY`
- **59 Rute Eksplisit & 0 AutoRoute:** Seluruh endpoint Web (`/hargajual`, `/hargajual/index`) dan API (`/api/hargajual/*`, `/api/transaksi/*`, `/api/mkdt/*`, `/api/kavling-request/*`, `/api/kavling/*` beserta alias legacy `/hargajual/*`, `/transaksi/*`, `/kavling/*`) terdaftar secara eksplisit di `app/Config/Routes.php`.
- **Dukungan `RAW_JSON_READY` (`?format=raw` & `/meta`):**
  - `GET|POST /api/hargajual/meta` dan `GET|POST /api/transaksi/meta`: Mengembalikan metadata proyek aktif, daftar proyek, opsi filter, dan hak akses dalam format JSON standar.
  - `POST /api/hargajual/get/all?format=raw` (atau `POST /api/hargajual/datatable` dengan `format=raw`): Mengembalikan baris pricelist mentah (`id_hargajual`, `id_proyek`, `id_cluster`, `id_tipe`, nominal numerik, `is_active`, serta signed URL `berkas_url`) beserta objek `meta` (`total`, `filtered`, `start`, `length`) tanpa string HTML DataTables.
  - `GET|POST /api/kavling/list?format=raw` dan `POST /api/kavling/list/ambil?format=raw`: Mengembalikan daftar objek kavling murni beserta `meta` untuk kebutuhan konsumsi SPA/Mobile.
- **3-Tier Frontend Asset Separation (`app/Views/hargajual/index.php`):**
  - Inline CSS dan inline script telah diekstrak sepenuhnya ke `public/assets/css/hargajual.css` dan `public/assets/js/hargajual.js` dengan cache-busting `filemtime()`, menggunakan container `.hargajual-page` dan konfigurasi `window.HARGAJUAL_CONFIG`.

### Cross-Module Callers (Pemanggil Lintas Modul)
Berdasarkan audit Codegraph lintas codebase, komponen modul `Transaksi` dikonsumsi secara aktif oleh modul-modul berikut (seluruhnya tetap kompatibel melalui class modular maupun 11 Zero-Downtime Shims):
- **`KavlingRepository` (22 callers lintas modul):** Dipanggil oleh modul `Siteplan` (`Siteplan.php`, `Modal.php`, `SiteplanDataService`, `TargetSiteplanService`), `MKDT` (`Mkdt.php`, `PosisiKonsumenService`, `BookingAkadReportService`), `Keuangan` (`Keuangan.php`, `Tagihan.php`, `Pembayaran.php`, `Pajak.php`, `CashOut.php`, `DanaAkad.php`, `PencairanAkadService`), `Legal` (`LegalService`), `Produksi` (`ProduksiService`), dan `Transaksi` (`TransaksiService`, `KavlingRequestController`, `KavlingController`).
- **`TransaksiRepository` (13 callers lintas modul):** Dipanggil oleh modul `MKDT` (`Mkdt.php`, `MkdtSettlementService`, `MkdtFinancialReconciliationService`), `Keuangan` (`Keuangan.php`, `Tagihan.php`, `PembayaranService`, `SpptbRepository`, `PrintService`), `Siteplan` (`Modal.php`), serta internal `Transaksi` (`TransaksiService`).
- **`TransaksiService` (10 callers lintas modul):** Dipanggil oleh modul `MKDT` (`Mkdt.php`), `Siteplan` (`Siteplan.php`, `Modal.php`), `Keuangan` (`Pembayaran.php`, `BookingPaymentService`), serta controller `Api\Transaksi\TransaksiController`.
- **`HargaJualRepository` (3 callers lintas modul):** Dipanggil oleh `HargaJualService`, `TransaksiService`, dan modul `MKDT` / `Siteplan` untuk pembacaan detail pricelist kavling.

### Key Functions & Core Business Logic
- **Controllers (`app/Controllers/Web/Transaksi/` & `app/Controllers/Api/Transaksi/`):**
  - `Web\Transaksi\HargajualController::index()`: Render halaman master Harga Jual dengan injeksi daftar proyek & proyek aktif.
  - `Api\Transaksi\HargajualController`: `meta()`, `getDataTable()`, `getAll()`, `getOne()`, `add()`, `edit()`, `remove()`, `set_harga()`.
  - `Api\Transaksi\TransaksiController`: `meta()`, `getByID()`, `getStatusByID()`, `saveTransaksi()`, `replaceConsumer()`, `saveStatus()`.
  - `Api\Transaksi\MkdtController`: `history()`, `consumerReplacementHistory()`.
  - `Api\Transaksi\KavlingRequestController`: `submit()`, `kavlingList()`, `list()`, `updateStatus()`.
  - `Api\Transaksi\KavlingController`: `getList()`.
- **Services (`app/Services/Transaksi/`):**
  - `HargaJualService`: `hasAccess()`, `getProyekList()`, `getMeta()`, `getDataTable()`, `getAll()`, `getOne()`, `create()`, `update()`, `toggleActive()`, `setHargaKavling()`, `cleanNumeric()`.
  - `TransaksiService`: `getMeta()`, `getBundle()`, `getStatusById()`, `saveTransaksi()`, `replaceConsumer()`, `saveStatus()`, `createOrUpdate()`, `update()`, `cleanNumeric()`.
  - `KavlingRequestService`: `submitRequest()`, `getList()`, `updateStatus()`.
- **Repositories (`app/Repositories/Transaksi/`):**
  - `HargaJualRepository`: `getHargaJualById()`, `hasModuleAccess()`, `getProyekList()`, `getDetailById()`, `getActiveByProyek()`, `getDataTableRows()`, `getDataTableFilteredCount()`, `getDataTableTotalCount()`, `insertFileHargajual()`, `insertHargaJual()`, `updateHargaJual()`, `findKavlingById()`, `updateKavlingHarga()`, `getPricelistRowById()`.
  - `TransaksiRepository`: `getKonsumenTransaksi()`, `findNikUsage()`, `getKonsumenByIdKavling()`, `getSpptbData()`, `lockConsumerReplacementContext()`, `getLegacyReplacementSpptbData()`.
  - `KavlingRepository`: `getKavlingByIdMkdt()`, `getIdProyekByIdMkdt()`, `getIdProyekByKavling()`, `getKavlingById()`, `getKavlingByIds()`, `getKavlingList()`, `getDiskresiByKavlingId()`, `getAll()`, `getPerintahBangun()`, `setPerintahBangun()`, `getKategoriOptions()`, `applyKategoriFilter()`.
- **Frontend JS & CSS (`public/assets/js/` & `public/assets/css/`):**
  - `hargajual.js`: Inisialisasi DataTables pricelist, filter proyek, modal tambah/edit/detail pricelist, kalkulasi live `syncHargaNet()` & `load_total()`, integrasi Quill & Flatpickr, dropzone upload `.pricelist-dropzone.bpb-dropzone`, serta fungsi global `window.add`, `window.edit`, `window.detail`, `window.remove`, `window.save`.
  - `siteplan/sales.js`: Handler modal transaksi penjualan kavling, form konsumen & jadwal uang muka, pergantian konsumen, serta perubahan status kavling di halaman Siteplan.
  - `siteplan/request-kavling.js`: Handler pengajuan, daftar, dan approval request kavling di halaman Siteplan.

### Deprecated & Legacy Shims
- **11 Zero-Downtime Shim Classes:**
  1. `app/Controllers/Transaksi.php` -> `App\Controllers\Api\Transaksi\TransaksiController`
  2. `app/Controllers/Api/TransaksiController.php` -> `App\Controllers\Api\Transaksi\TransaksiController`
  3. `app/Controllers/Api/MkdtController.php` -> `App\Controllers\Api\Transaksi\MkdtController`
  4. `app/Controllers/Api/KavlingRequestController.php` -> `App\Controllers\Api\Transaksi\KavlingRequestController`
  5. `app/Controllers/Hargajual.php` (Dual-Shim Delegator) -> `App\Controllers\Web\Transaksi\HargajualController` (untuk `index`) & `App\Controllers\Api\Transaksi\HargajualController` (untuk operasi API)
  6. `app/Controllers/Kavling.php` -> `App\Controllers\Api\Transaksi\KavlingController`
  7. `app/Services/TransaksiService.php` -> `App\Services\Transaksi\TransaksiService`
  8. `app/Services/KavlingRequestService.php` -> `App\Services\Transaksi\KavlingRequestService`
  9. `app/Repositories/TransaksiRepository.php` -> `App\Repositories\Transaksi\TransaksiRepository`
  10. `app/Repositories/HargaJualRepository.php` -> `App\Repositories\Transaksi\HargaJualRepository`
  11. `app/Repositories/KavlingRepository.php` -> `App\Repositories\Transaksi\KavlingRepository`
- **Legacy Route Aliases:** Rute flat `/hargajual/*`, `/transaksi/*`, dan `/kavling/*` dipertahankan secara eksplisit di `app/Config/Routes.php` dan diarahkan langsung ke controller modular di `App\Controllers\Api\Transaksi\*`; gunakan rute kanonik `/api/hargajual/*`, `/api/transaksi/*`, dan `/api/kavling/*` untuk pengembangan baru.

## Change Log
- **`2026-11-15` [`API-BRIDGE & SCHEMA`] — Penyederhanaan `kavling_request` (`tipe_manual` & `alasan_tolak`), Akses Baca MKDT, & JSON Bridge `Hargajual`/`Transaksi` (Siteplan Fase 3.3 Sub-Fase 3.3.1):**
  - **Database & Model:**
    - `app/Database/Migrations/2026-11-15-000001_AddTipeManualAndAlasanTolakToKavlingRequest.php` (`Batch 48`): Menjadikan `kavling_request.id_tipe` nullable serta menambahkan kolom `tipe_manual VARCHAR(100) NULL` dan `alasan_tolak TEXT NULL`.
    - `app/Models/KavlingRequestModel.php`: Menambahkan `'tipe_manual'` dan `'alasan_tolak'` ke `$allowedFields`.
  - **Routes & Public Contracts (`RAW_JSON_READY`):**
    - `KavlingRequestController::submit()` / `KavlingRequestService::submitRequest()`: Mendukung `tipe_manual` (maks 100 char) serta `id_cluster`/`id_jalan`/`id_tipe` opsional (`keterangan` wajib saat `tambah_baru` tanpa cluster/jalan).
    - `KavlingRequestController::list()` / `KavlingRequestService::getList()`: Membuka akses baca `in_groups(['1', '3', '4', '6', '8'])` untuk MKDT (`4`), dan mengembalikan `tipe_manual`, `tipe_display`, serta `alasan_tolak`.
    - `KavlingRequestController::updateStatus()` / `KavlingRequestService::updateStatus(int $idRequest, string $newStatus, int $userId, ?string $alasanTolak = null)`: Mewajibkan `alasan_tolak` (HTTP `422`) saat `status === 'rejected'` dari SPA/API serta menyimpan/membersihkan `kavling_request.alasan_tolak`.
    - `HargajualController::set_harga()`: Membaca input via `readRequestData()` dan mendukung payload `id_kavling` berupa array maupun CSV.
    - `TransaksiController::{getByID,getStatusByID}` & `TransaksiService::getStatusById()`: Membaca input via `readRequestData()` + `validateData()`, serta memperkaya respons dengan `nama_proyek`, `hp_konsumen`, `sp3k_access_url`, `bast_access_url`, dan `perintah_bangun_access_url`.
  - **Verification:** Terverifikasi QA `PASS` (`php spark migrate:status`, `php spark module:sync Transaksi`, & Playwright E2E) tanpa merusak endpoint legacy.
- **`[2026-11-13] [REFACTOR]` — Refactoring Penuh Modul Transaksi (Fase 2A-1, 2A-2, 2B, & 3B — Readiness Score 100/100):**
  - **Fase 2A-1 (Service & Repository Layer Refactoring):** Memindahkan `TransaksiService`, `KavlingRequestService`, `TransaksiRepository`, `HargaJualRepository`, dan `KavlingRepository` ke namespace modular `App\Services\Transaksi\*` dan `App\Repositories\Transaksi\*`; membangun `HargaJualService` baru (`cleanNumeric()`, rekalkulasi server-side `harga_net` & `total_harga`, transaksi DB, integrasi `FileAccessService`, audit log, & notifikasi) serta memperluas `HargaJualRepository` dengan 14 method data-access bebas layer violation.
  - **Fase 2A-2 (Web/API Controller Split, Explicit Routes & Zero-Downtime Shims):** Memisahkan controller menjadi `Web\Transaksi\HargajualController` dan 5 API Controllers (`Api\Transaksi\HargajualController`, `TransaksiController`, `MkdtController`, `KavlingRequestController`, `KavlingController`); mendaftarkan 59 rute eksplisit (0 AutoRoute) di `app/Config/Routes.php` dengan dukungan `RAW_JSON_READY` (`?format=raw` & `/meta`); serta memasang 11 Zero-Downtime Shims (termasuk Dual-Shim Delegator pada `app/Controllers/Hargajual.php`).
  - **Fase 2B (Frontend 3-Tier Asset Extraction & UI Standarisasi):** Mengekstrak seluruh inline CSS dan JS dari `app/Views/hargajual/index.php` ke `public/assets/css/hargajual.css` (scoping `.hargajual-page`, `#add-modal`, `#detail-modal`, tema Primary Blue `#2057a3`, `.pricelist-dropzone.bpb-dropzone`) dan `public/assets/js/hargajual.js` (DataTables, Flatpickr, Select2, Quill, `syncHargaNet()`, `load_total()`, dropzone upload, dan CRUD `window.add`/`edit`/`detail`/`remove`/`save`) dengan cache-busting `filemtime()`.
  - **Fase 3B (Remediasi & Verifikasi QA PASS 100%):** Menyelesaikan validasi E2E Playwright, audit kepatuhan UI `sigapp-ui-acuan`, verifikasi kompatibilitas 48 pemanggil lintas modul via Codegraph (`KavlingRepository` 22 callers, `TransaksiRepository` 13 callers, `TransaksiService` 10 callers, `HargaJualRepository` 3 callers), serta sinkronisasi katalog modul (`ModuleCatalog.php` -> `100/100 REFACTORED`).
