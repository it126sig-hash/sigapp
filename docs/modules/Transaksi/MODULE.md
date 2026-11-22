# Module: Transaksi

**Status:** `REFACTORED`  
**Readiness Score:** `100/100`  
**Last Synced:** `2026-11-21 18:36:02` @ `004ca42`  

## Purpose
Modul inti (*Core Domain*) yang bertanggung jawab atas eksekusi transaksi jual-beli unit kavling, pengelolaan master daftar harga (*pricelist* `hargajual` & lampiran dokumen SK `file_hargajual`), penetapan harga jual akhir unit kavling (`set_harga`), pergantian konsumen (*consumer replacement*), pembaruan status transaksi & perintah bangun, serta alur pengajuan dan persetujuan pembukaan/request unit kavling (`kavling_request`). Dibangun dengan arsitektur modular **Thin Controller → Service → Repository**, pemisahan tegas antara Web Controller dan API Controller, serta dukungan penuh kontrak **`RAW_JSON_READY`** (`?format=raw` dan endpoint `/meta`) untuk kesiapan integrasi SPA/Mobile.

## Out of scope
- Pencatatan pembayaran angsuran, verifikasi tagihan Cash-In, pencairan dana akad/jaminan KPR, serta pengeluaran kas subkon/operasional Cash-Out (Domain `Keuangan`).
- Administrasi perizinan legalitas lahan, pemecahan sertifikat HGB, PBB, PBG, dan proses akta AJB (Domain `Legal`).
- Pelaksanaan konstruksi fisik lapangan, tahapan progres serah terima (ST 0% s/d 100%), dan checklist inspeksi bangunan (Domain `Produksi`).
- Perencanaan master proyek perumahan, master cluster, master tipe bangunan, dan master jalan lingkungan (Domain `Proyek`).
- Rendering kanvas peta spasial SVG interaktif lintas departemen (Domain `Siteplan`).

## Invariants
- **Kepemilikan Tabel Eksklusif (`Owner Tables`):** Modul lain dilarang memanipulasi tabel pemilik (`kavling`, `kavling_request`, `kavling_request_jalan`, `kavling_request_kavling`, `file_hargajual`, `hargajual`) secara langsung; seluruh operasi tulis wajib melalui Service/Repository modul `Transaksi`.
- **Zero Controller Layer Violations:** Seluruh Controller (`Web\Transaksi\*` dan `Api\Transaksi\*`) hanya bertugas menangani HTTP request/response, validasi awal, dan delegasi ke layer `App\Services\Transaksi\*` tanpa pemanggilan `db_connect()`, query builder, atau Model secara langsung.
- **Sanitasi & Rekalkulasi Finansial Server-Side (`cleanNumeric`):** Seluruh input nominal mata uang wajib dibersihkan menggunakan `cleanNumeric()` di level Service (`HargaJualService` dan `TransaksiService`). Pada `HargaJualService`, komponen harga wajib dihitung ulang di sisi server menggunakan rumus baku:
  - `harga_net = harga_standar - diskon_standar + biaya_hook + kelebihan_tanah + biaya_lain - diskon_khusus`
  - `total_harga = harga_net + ajb_bn + bphtb + ppn + biaya_kpr + biaya_lain_2`
- **Integritas Transaksi Database (`DB Transaction`) & Proteksi Request Kavling:** Mutasi multi-tabel (penyimpanan transaksi konsumen & MKDT, pergantian konsumen `replaceConsumer` dengan row-level lock `lockConsumerReplacementContext`, perubahan status & perintah bangun, pembuatan/pembaruan pricelist beserta lampiran `file_hargajual`, penetapan harga jual kavling, serta pengajuan & penerapan request ubah tipe kavling pada `KavlingRequestService`) wajib dibungkus dalam `db->transStart()` dan `db->transComplete()`. Khusus `applyUbahTipeKavling()`, perubahan dibatasi hanya pada `kavling.id_tipe` (tanpa menyentuh `harga_akhir` ataupun `mkdt`) serta wajib memblokir kavling dengan transaksi MKDT aktif berstatus `Akad` (`akad = 1` / `status_mkdt = 'Akad'`) atau `akad_indent = 1`.
- **Kontrak API `RAW_JSON_READY`:** Endpoint DataTables dan daftar (`POST /api/hargajual/get/all`, `POST /api/hargajual/datatable`, `GET|POST /api/kavling/list`, `POST /api/kavling/list/ambil`, serta seluruh grup `POST /api/kavling-request/*`) wajib mendukung respons JSON terstruktur bebas HTML bersama objek `meta`, serta menyediakan endpoint metadata `/api/hargajual/meta` dan `/api/transaksi/meta`.
- **Audit Trail & Notifikasi:** Setiap perubahan harga jual kavling, penyimpanan transaksi konsumen, pergantian konsumen, pembaruan status unit, serta pengajuan/tindak lanjut request kavling wajib dicatat ke riwayat audit (`HistoryService` / `MkdtHistoryService`) dan memicu pengiriman notifikasi lintas divisi (`NotifikasiService` / `tambah_notif`) sesuai aturan bisnis.

## Database Tables
### Owner Table(s)
- `kavling` *(akses tulis eksklusif hanya untuk modul Transaksi)*
- `kavling_request` *(akses tulis eksklusif hanya untuk modul Transaksi)*
- `kavling_request_jalan` *(akses tulis eksklusif hanya untuk modul Transaksi)*
- `kavling_request_kavling` *(akses tulis eksklusif hanya untuk modul Transaksi)*
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
| `POST` | `/api/kavling-request/apply-ubah-kavling` | `Explicit` | `KavlingRequestController::applyUbahKavling` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/kavling-request/cancel` | `Explicit` | `KavlingRequestController::cancel` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
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
- `isKavlingBlockedByAkad(object $row)`
- `submitRequest(array $data, int $userId)`
- `getList(int $idProyek, ?string $status = null, ?int $page = null, ?int $perPage = null, ?string $jenisRequest = null)`
- `getStatusCounts(int $idProyek)`
- `countFiltered(int $idProyek, ?string $jenisRequest = null, ?string $status = null)`
- `updateStatus(int $idRequest, string $newStatus, int $userId, ?string $catatanPlanning = null)`
- `applyUbahTipeKavling(int $idRequest, int $idTipe, int $userId, ?string $catatanPlanning = null)`
- `cancelRequests(int $idProyek, array $idRequests, int $userId, bool $isAdmin, ?string $alasanBatal = null)`
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
- `app/Controllers/Api/Transaksi/KavlingRequestController.php` (424 baris, 6 public methods: `submit()`, `kavlingList()`, `list()`, `updateStatus()`, `applyUbahKavling()`, `cancel()`)
- `app/Controllers/Api/Transaksi/KavlingController.php` (45 baris, 1 public methods: `getList()`)

### Services (3)
- `app/Services/Transaksi/TransaksiService.php` (1027 baris, 9 public methods)
- `app/Services/Transaksi/KavlingRequestService.php` (1385 baris, 8 public methods)
- `app/Services/Transaksi/HargaJualService.php` (576 baris, 11 public methods)

### Repositories (4)
- `app/Repositories/Transaksi/TransaksiRepository.php` (240 baris, 6 public methods: `getKonsumenTransaksi(int $idMkdt)`, `findNikUsage(string $nik, ?int $excludeMkdt = null, ?int $excludeKonsumen = null)`, `getKonsumenByIdKavling($idKavling)`, `getSpptbData(int $idKavling, int $idMkdt)`, `lockConsumerReplacementContext(int $idMkdt, int $idKavling)`, `getLegacyReplacementSpptbData(int $idKavling, int $currentIdMkdt)`)
- `app/Repositories/Transaksi/HargaJualRepository.php` (270 baris, 14 public methods: `getHargaJualById(int $id)`, `hasModuleAccess(int $userId, string $module = 'Hargajual')`, `getProyekList()`, `getDetailById(int $id)`, `getActiveByProyek(?int $idProyek, string $search = '')`, `getDataTableRows(array $var, ?int $idProyek)`, `getDataTableFilteredCount(array $var, ?int $idProyek)`, `getDataTableTotalCount(?int $idProyek)`, `insertFileHargajual(array $data)`, `insertHargaJual(array $data)`, `updateHargaJual(int $id, array $data)`, `findKavlingById(int $idKavling)`, `updateKavlingHarga(int $idKavling, array $data)`, `getPricelistRowById(int $id)`)
- `app/Repositories/Transaksi/KavlingRepository.php` (851 baris, 13 public methods: `getKavlingByIdMkdt(int $id_mkdt)`, `getIdProyekByIdMkdt(int $id_mkdt)`, `getIdProyekByKavling(int $id_kavling)`, `getKavlingById(int $idKavling)`, `getKavlingByIds(array $idKavlings)`, `getKavlingList($id_proyek = null, $search = "", $limit = null, $is_cashout_subkon = 0, $id_cluster = null, $id_jalan = null, $only_available = 0)`, `getKavlingListForRequest(int $idProyek, string $search = '', int $limit = 50)`, `getDiskresiByKavlingId(int $idKavling)`, `getAll($id_proyek, $id_cluster = null, $id_jalan = null, $id_divisi = null, $kategoriFilters = [])`, `getPerintahBangun($id_kavling)`, `setPerintahBangun($id, $data)`, `getKategoriOptions(int $idProyek)`, `applyKategoriFilter(BaseBuilder $builder, array $filters)`)
- `app/Repositories/Transaksi/KavlingRequestRepository.php` (910 baris, 18 public methods: `insertJalans(int $idRequest, array $idJalans)`, `insertKavlings(int $idRequest, array $kavlingRows)`, `findPendingUbahTipeMap(int $idProyek, array $idKavlings)`, `getKavlingEligibilityRows(int $idProyek, array $idKavlings)`, `getJalanRowsByProyek(int $idProyek, array $idJalans)`, `findTipeByIdAndProyek(int $idTipe, int $idProyek)`, `getList(int $idProyek, ?string $jenisRequest = null, ?string $status = null, ?int $page = null, ?int $perPage = null)`, `getStatusCounts(int $idProyek)`, `countFiltered(int $idProyek, ?string $jenisRequest = null, ?string $status = null)`, `findRequestsByIdsAndProyek(int $idProyek, array $idRequests)`, `markRequestsCancelled(array $idRequests, int $userId, ?string $alasanBatal, string $now)`, `getKavlingsByRequestId(int $idRequest)`, `updateKavlingsTipe(array $idKavlings, int $idTipe, int $userId, string $now)`, `getProyekName(int $idProyek)`, `getUserDisplayName(int $userId)`, `getClusterName(int $idCluster)`, `getJalanName(int $idJalan)`, `getKavlingLabels(array $idKavlings)`)

### Models (3)
- `app/Models/KavlingModel.php`
- `app/Models/KavlingRequestModel.php`
- `app/Models/FileHargajualModel.php`

### Views (1 folder)
- `app/Views/hargajual` (1 file: `index.php`)

### JavaScript (3 file)
- `public/assets/js/hargajual.js` (843 baris)
- `public/assets/js/siteplan/sales.js` (525 baris)
- `public/assets/js/siteplan/request-kavling.js` (2598 baris)

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
  - `submit()` (`POST /api/kavling-request/submit`): RBAC `['1', '4']` untuk form modal terpadu (`tambah_baru` multi-jalan `id_jalans[]` & `ubah_tipe` multi-kavling `id_kavlings[]`, `keterangan` wajib, `id_tipe`/`tipe_manual` opsional) dan `['1', '4', '6']` untuk payload batch `items[]` dari Target Siteplan (`target.js` — kini dikelompokkan per tipe tujuan dan mengembalikan `request_count`, `kavling_count`, `groups`, `submitted_count`, `created_ids`, `skipped_count`, `skipped_pending`), lalu delegasi ke `KavlingRequestService::submitRequest()`.
  - `kavlingList()` (`POST /api/kavling-request/kavling-list`): RBAC `['1', '4', '6']`, memanggil `KavlingRepository::getKavlingListForRequest()` yang mengecualikan kavling berstatus aktif `Akad`/`Akad Indent` dan mengembalikan info tipe saat ini (`current_id_tipe`, `current_tipe_label`) serta info konsumen aktif (`has_active_konsumen`, `nama_konsumen`, `status_mkdt`).
  - `list()` (`POST /api/kavling-request/list`): RBAC `['1', '4', '6']`, mendukung filter `jenis_request` (`tambah_baru`/`ubah_tipe`) dan `status` (`pending`/`approved`/`rejected`/`history`), mengembalikan `requested_tipe` & `applied_tipe` lengkap dengan Signed URL `FileAccessService`, array `jalans[]` & `kavlings[]`, `catatan_planning`, `counts`, serta `meta.permissions` (`can_submit`, `can_process`, `can_apply_ubah_kavling`).
  - `updateStatus()` (`POST /api/kavling-request/update-status`): RBAC `['1', '6']`, menerima `catatan_planning` (fallback `alasan_tolak`/`notes`), mewajibkan catatan saat `status === 'rejected'` (HTTP `422`), opsional saat `approved`, serta memicu notifikasi balik ke pengaju/MKDT.
  - `applyUbahKavling()` (`POST /api/kavling-request/apply-ubah-kavling` — **BARU**): RBAC `['1', '6']`, memvalidasi `id_request`, `id_tipe`, dan `catatan_planning` opsional lalu memanggil `KavlingRequestService::applyUbahTipeKavling()` untuk mengubah `kavling.id_tipe` secara atomik.
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
  - `isKavlingBlockedByAkad(object $row)`: Guard pengecekan blokir kavling bertransaksi MKDT aktif (`id_mkdt` ada, `is_batal = 0`, `status_mkdt != 'Batal'`) dengan status `Akad` (`status_mkdt = 'Akad'` atau `akad = 1`) atau `akad_indent = 1`.
  - `submitRequest(array $data, int $userId)`: Orkestrasi pengajuan `tambah_baru` multi-jalan (`submitTambahBaru()` -> simpan `kavling_request_jalan`), `ubah_tipe` multi-kavling (`submitMultiUbahTipe()` -> validasi blokir Akad/Akad Indent via `isKavlingBlockedByAkad()` & pending duplicate via `findPendingUbahTipeMap()` -> simpan `kavling_request_kavling` beserta `old_id_tipe`), single legacy (`submitLegacySingleUbahTipe()`), serta batch `items[]` Target Siteplan (`submitBatchItemsUbahTipe()`, `normalizeBatchItems()`): mengelompokkan item eligible berdasarkan tipe tujuan (`master:{id_tipe}` tervalidasi via `findTipeByIdAndProyek()`, atau `manual:{lowercase}`), membuat tepat 1 header `kavling_request` (`id_kavling = null` bila grup > 1 unit; penggabungan `keterangan` unik dengan `; `) + N anak `kavling_request_kavling` per tipe tujuan, mengirim tepat 1 notifikasi ringkasan grup `NotificationEvent::REQUEST_KAVLING` ke Planning (`[6]`) dengan breakdown per tipe (atau pesan tunggal bila 1 kavling), dibungkus transaksi DB, dan mengembalikan `request_count`, `kavling_count`, `groups`, `submitted_count`, `created_ids`, `skipped_count`, `skipped_pending`.
  - `getList(...)`, `getStatusCounts(int $idProyek)`, `countFiltered(...)`: Membaca data lewat `KavlingRequestRepository`, membentuk DTO `formatRequestListRow()` dan `formatTipeDto()` (menyertakan `gambar_tipe_access_url`, `gambar_denah_access_url`, `gambar_kerja_access_url` via `FileAccessService::accessUrl('gambar_kerja', ...)`), serta counter tab (`all`, `pending`, `tambah_baru_pending`, `ubah_tipe_pending`, `approved`, `rejected`, `history`).
  - `updateStatus(int $idRequest, string $newStatus, int $userId, ?string $catatanPlanning = null)`: Pembaruan status (`pending`, `approved`, `rejected`) dalam transaksi DB dengan penyimpanan `catatan_planning`, `alasan_tolak`, `processed_by`, `processed_at`, serta pengiriman notifikasi balik `sendStatusFeedbackNotification()` saat `approved`/`rejected`.
  - `applyUbahTipeKavling(int $idRequest, int $idTipe, int $userId, ?string $catatanPlanning = null)`: Validasi request `ubah_tipe` berstatus `pending`, pengecekan ulang blokir Akad/Akad Indent, pengubahan atomik hanya pada `kavling.id_tipe` (`updateKavlingsTipe()`, tanpa menyentuh `harga_akhir` atau `mkdt`), pencatatan audit `HistoryService::log('siteplan', ['action' => 'request_ubah_tipe_kavling', ...])`, pembaruan status menjadi `approved` beserta `applied_id_tipe`, `catatan_planning`, `processed_by`, `processed_at`, pengembalian `active_konsumen_warnings` (jika ada konsumen non-Akad), dan pengiriman notifikasi balik `sendStatusFeedbackNotification()` ke pengaju/MKDT.
- **Repositories (`App\Repositories\Transaksi\*`):**
  - `HargaJualRepository`: Enkapsulasi penuh query tabel `hargajual`, `file_hargajual`, `kavling`, dan `proyek` (14 public methods untuk DataTables, detail, CRUD, dan akses modul).
  - `TransaksiRepository`: Query relasi transaksi konsumen, pengecekan duplikasi NIK (`findNikUsage`), data SPPTB (`getSpptbData(int $idKavling, int $idMkdt): ?object` — termasuk kolom `konsumen.status_pernikahan` dan data pasangan untuk `PrintService::printSpptb()`), dan row lock pergantian konsumen (`lockConsumerReplacementContext`).
  - `KavlingRepository`: Query master & status unit kavling lintas modul (`getKavlingList`, `getKavlingListForRequest` [mengecualikan kavling aktif Akad / Akad Indent untuk request ubah tipe dan menyertakan info tipe & konsumen aktif], `getAll`, `getPerintahBangun`, `setPerintahBangun`, `getKategoriOptions`, `applyKategoriFilter`).
  - `KavlingRequestRepository` (**BARU**, `830` baris): Enkapsulasi query request kavling & tabel relasi (`insertJalans`, `insertKavlings`, `findPendingUbahTipeMap`, `getKavlingEligibilityRows`, `getJalanRowsByProyek`, `findTipeByIdAndProyek`, `getList`, `getStatusCounts`, `countFiltered`, `getKavlingsByRequestId`, `updateKavlingsTipe`, `getProyekName`, `getUserDisplayName`, `getClusterName`, `getJalanName`, `getKavlingLabels`).
- **Model (`App\Models\KavlingRequestModel`):**
  - Definisi entitas `kavling_request` dengan `$allowedFields` yang diperluas mencakup `applied_id_tipe`, `catatan_planning`, `processed_by`, dan `processed_at`.
- **Frontend Assets (`app/Views/hargajual/*`, `public/assets/js/*` & `public/assets/css/*`):**
  - `app/Views/hargajual/index.php`: Template view Master Harga Jual (3-tier separation) dengan hint editor `Rich Text Editor (mendukung Ctrl + V gambar)` dan prompt dropzone `.pricelist-dropzone` `#dropzone-prompt` (`Format .PDF (Maks 10MB)`).
  - `public/assets/js/hargajual.js`: Logika antarmuka Master Harga Jual (server-side DataTables dengan `drawCallback` yang memanggil `api.columns.adjust()` setelah `<img>` di `#data_table tbody img` selesai dimuat, Flatpickr tanggal berlaku, Select2 proyek/cluster/tipe, Quill editor keterangan dengan dukungan clipboard image paste melalui `setupQuillImagePaste(quillInstance)` [capture-phase listener dengan anti-regression guard salinan teks+HTML Excel/Word], `preparePastedImageDataUrl()`, `renderCompressedImageCandidate()` [kompresi canvas WebP/JPEG bertahap terhadap batas `MAX_RAW_IMAGE_DATA_URL_CHARS = 38000`], `getUtf8ByteLength()`, serta guard kapasitas kolom `TEXT` `MAX_KETERANGAN_BYTES = 62000` dan `HARD_KETERANGAN_BYTES = 64000` pada `initFormValidation()` `submitHandler` dengan pengecualian selector Quill `ignore: ':hidden, #keterangan-editor [contenteditable], .ql-editor, .ql-clipboard'`, kalkulasi otomatis `syncHargaNet()` & `load_total()`, dropzone upload file SK `setupDropzone()` [pengganti `setupDropzoneAndPaste()`, hanya drag-and-drop dan pemilihan berkas PDF tanpa listener paste modal], serta aksi CRUD `window.add`, `window.edit`, `window.detail`, `window.remove`, `window.save`).
  - `public/assets/css/hargajual.css`: Stylesheet terisolasi dengan scoping `.hargajual-page`, `#add-modal`, `#detail-modal`, tema Primary Blue `#2057a3`, komponen `.pricelist-dropzone.bpb-dropzone`, serta styling gambar responsif pada `#add-modal .ql-editor img` (`max-width: 100%`, `height: auto`) dan `.hargajual-page #data_table td img` (`max-width: 100%`, `max-height: 120px`, `object-fit: contain`).
  - `public/assets/js/siteplan/sales.js`: Handler modal transaksi penjualan kavling, form konsumen & jadwal uang muka, pergantian konsumen, serta perubahan status kavling di peta Siteplan.
  - `public/assets/js/siteplan/request-kavling.js` (`2051` baris): Pengelola modal terpadu 3-tab Request Kavling (`#modal-request-kavling` pada `app/Views/siteplan/request_kavling_modal.php` & CSS `public/assets/css/siteplan-request-kavling.css` di modul `Siteplan`), Select2 multi-jalan (`/api/jalan/all?format=raw`), Select2 multi-kavling (`/api/kavling-request/kavling-list`), Select2 tipe aktif (`/api/tipe/all?only_active=1&format=raw`), preview 3D/Denah/Spek & modal `#modal-rk-tipe-preview` + lightbox `#modal-rk-lightbox`, Konva canvas picker (`enterCanvasPickMode()`, `exitCanvasPickMode()`, `window.isRequestKavlingCanvasPickActive()`, `window.handleRequestKavlingCanvasClick()`, `window.renderRequestKavlingCanvasHighlights()`, `window.cancelRequestKavlingCanvasPick()`), badge jumlah unit `.rk-kavling-count-badge` (`N kavling`) di `renderKavlingsCell()` & `openApplyUbahKavlingModal()` bila `kavlings.length > 1`, tabel detail & aksi tindak lanjut Planning (`update-status`, sub-modal `#modal-apply-ubah-kavling` + `apply-ubah-kavling` + link `/tipe` `target="_blank"` & Refresh Tipe + reload `load_kavling()`), tab Riwayat, `window.openModalRequestKavling()`, `window.handleContextRequestKavling()`, `window.refreshRequestKavlingCounts` (`loadCountsSummary`), serta shim alias `window.openModalListRequestKavling()`.
  - *Cross-Module Frontend Caller (`public/assets/js/siteplan/mkdt.js` — aset modul `MKDT`)*: Mengonsumsi `POST /hargajual/getAll` untuk dropdown Select2 `#sh-id` (Modal Set Harga Siteplan) melalui `formatKeteranganSummary(rawHtml)` dan `formatDesign(item)` yang meringkas tag `<img>` menjadi `[Gambar]` serta aman dari `split(';')` dan injeksi HTML/XSS.

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
- **Deprecated Methods, Views & Frontend Shims:**
  - `KavlingRepository::getKavlingList()` — **LEGACY RETAINED**: Tetap dipertahankan untuk pemanggil lama (`Api\Transaksi\KavlingController::getList()` dan caller lintas modul); endpoint `/api/kavling-request/kavling-list` beralih menggunakan `KavlingRepository::getKavlingListForRequest()`.
  - `app/Views/siteplan/modal_list_request_kavling.php` — **DEPRECATED / UNMOUNTED**: Tidak lagi dipanggil dari `app/Views/siteplan/master.php`; antarmuka daftar & tindak lanjut Planning telah dikonsolidasikan ke `app/Views/siteplan/request_kavling_modal.php`.
  - `window.openModalListRequestKavling()` (`public/assets/js/siteplan/request-kavling.js`) — **DEPRECATED (Shim Alias)**: Dipertahankan sebagai alias ke `window.openModalRequestKavling({ tab: 'tambah' })`.
  - *(Catatan `2026-11-21` Grouping Batch `ubah_tipe`: Tidak ada fungsi, route, atau shim baru yang di-deprecate).*

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
- **60 Rute Eksplisit & 0 AutoRoute:** Seluruh endpoint Web (`/hargajual`, `/hargajual/index`) dan API (`/api/hargajual/*`, `/api/transaksi/*`, `/api/mkdt/*`, `/api/kavling-request/*`, `/api/kavling/*` beserta alias legacy `/hargajual/*`, `/transaksi/*`, `/kavling/*`) terdaftar secara eksplisit di `app/Config/Routes.php`.
- **Skema Request Kavling Relasional (`app/Database/Migrations/2026-11-21-000001_RevampKavlingRequestTables.php`):** Tabel header `kavling_request` memiliki kolom `applied_id_tipe`, `catatan_planning`, `processed_by`, `processed_at` serta dua tabel relasi anak `kavling_request_jalan` (multi-jalan untuk `tambah_baru`) dan `kavling_request_kavling` (multi-kavling + `old_id_tipe` untuk `ubah_tipe`).
- **Dukungan `RAW_JSON_READY` (`?format=raw` & `/meta`):**
  - `GET|POST /api/hargajual/meta` dan `GET|POST /api/transaksi/meta`: Mengembalikan metadata proyek aktif, daftar proyek, opsi filter, dan hak akses dalam format JSON standar.
  - `POST /api/hargajual/get/all?format=raw` (atau `POST /api/hargajual/datatable` dengan `format=raw`): Mengembalikan baris pricelist mentah (`id_hargajual`, `id_proyek`, `id_cluster`, `id_tipe`, nominal numerik, `is_active`, serta signed URL `berkas_url`) beserta objek `meta` (`total`, `filtered`, `start`, `length`) tanpa string HTML DataTables.
  - `GET|POST /api/kavling/list?format=raw` dan `POST /api/kavling/list/ambil?format=raw`: Mengembalikan daftar objek kavling murni beserta `meta` untuk kebutuhan konsumsi SPA/Mobile.
  - `POST /api/kavling-request/{submit,kavling-list,list,update-status,apply-ubah-kavling}`: Mengembalikan JSON terstruktur lengkap dengan `counts`, `meta.permissions`, `requested_tipe`/`applied_tipe` (`*_access_url` via `FileAccessService`), `jalans[]`, dan `kavlings[]`.
- **3-Tier Frontend Asset Separation (`app/Views/hargajual/index.php`):**
  - Inline CSS dan inline script telah diekstrak sepenuhnya ke `public/assets/css/hargajual.css` dan `public/assets/js/hargajual.js` dengan cache-busting `filemtime()`, menggunakan container `.hargajual-page` dan konfigurasi `window.HARGAJUAL_CONFIG`.

### Cross-Module Callers (Pemanggil Lintas Modul)
Berdasarkan audit Codegraph lintas codebase, komponen modul `Transaksi` dikonsumsi secara aktif oleh modul-modul berikut (seluruhnya tetap kompatibel melalui class modular maupun 11 Zero-Downtime Shims):
- **`KavlingRepository` (22+ callers lintas modul):** Dipanggil oleh modul `Siteplan` (`Siteplan.php`, `Modal.php`, `SiteplanDataService`, `TargetSiteplanService`), `MKDT` (`Mkdt.php`, `PosisiKonsumenService`, `BookingAkadReportService`), `Keuangan` (`Keuangan.php`, `Tagihan.php`, `Pembayaran.php`, `Pajak.php`, `CashOut.php`, `DanaAkad.php`, `PencairanAkadService`), `Legal` (`LegalService`), `Produksi` (`ProduksiService`), dan `Transaksi` (`TransaksiService`, `KavlingRequestController`, `KavlingController`).
- **`TransaksiRepository` (13 callers lintas modul):** Dipanggil oleh modul `MKDT` (`Mkdt.php`, `MkdtSettlementService`, `MkdtFinancialReconciliationService`), `Keuangan` (`Keuangan.php`, `Tagihan.php`, `PembayaranService`, `SpptbRepository`, `PrintService`), `Siteplan` (`Modal.php`), serta internal `Transaksi` (`TransaksiService`).
- **`TransaksiService` (10 callers lintas modul):** Dipanggil oleh modul `MKDT` (`Mkdt.php`), `Siteplan` (`Siteplan.php`, `Modal.php`), `Keuangan` (`Pembayaran.php`, `BookingPaymentService`), serta controller `Api\Transaksi\TransaksiController`.
- **`HargaJualRepository` (3 callers lintas modul):** Dipanggil oleh `HargaJualService`, `TransaksiService`, dan modul `MKDT` / `Siteplan` untuk pembacaan detail pricelist kavling.
- **`KavlingRequestController::submit` / `KavlingRequestService::submitRequest` (Integrasi Target Siteplan & Home Dashboard):** Dikonsumsi oleh modul `Siteplan` (`public/assets/js/siteplan/target.js` -> `submitPlanningKavlingChangeRequest()`) untuk pengajuan tunggal maupun batch `ubah_tipe` dari modal Target Siteplan ke grup Planning (`[6]`) yang kini mengelompokkan item per tipe tujuan (`1` header `kavling_request` + `N` anak `kavling_request_kavling` per grup) dan menyegarkan badge sidebar lewat `window.refreshRequestKavlingCounts()`. `TargetSiteplanRepository::getLatestPlanningChangeRequests()` membaca `kavling_request` dan `kavling_request_kavling` (dengan fallback legacy) untuk status `Req Planning: Pending`, sementara `DashboardService` (`Home`) membaca jumlah `kavling_request` berstatus `pending` untuk Action Queue Admin/Planning/Direksi.
- **Select2 Pricelist `#sh-id` pada Modal Set Harga Siteplan (`public/assets/js/siteplan/mkdt.js` — Modul `MKDT`):** Mengonsumsi endpoint `POST /hargajual/getAll` untuk pilihan pricelist kavling; menggunakan `formatKeteranganSummary(rawHtml)` dan `formatDesign(item)` yang telah diperkuat agar tag `<img src="data:image/...;base64,...">` pada kolom `hargajual.keterangan` diringkas menjadi `[Gambar]`, aman dari pemecahan `split(';')`, dan terproteksi dari XSS.

### Key Functions & Core Business Logic
- **Controllers (`app/Controllers/Web/Transaksi/` & `app/Controllers/Api/Transaksi/`):**
  - `Web\Transaksi\HargajualController::index()`: Render halaman master Harga Jual dengan injeksi daftar proyek & proyek aktif.
  - `Api\Transaksi\HargajualController`: `meta()`, `getDataTable()`, `getAll()`, `getOne()`, `add()`, `edit()`, `remove()`, `set_harga()`.
  - `Api\Transaksi\TransaksiController`: `meta()`, `getByID()`, `getStatusByID()`, `saveTransaksi()`, `replaceConsumer()`, `saveStatus()`.
  - `Api\Transaksi\MkdtController`: `history()`, `consumerReplacementHistory()`.
  - `Api\Transaksi\KavlingRequestController`: `submit()`, `kavlingList()`, `list()`, `updateStatus()`, `applyUbahKavling()`.
  - `Api\Transaksi\KavlingController`: `getList()`.
- **Services (`app/Services/Transaksi/`):**
  - `HargaJualService`: `hasAccess()`, `getProyekList()`, `getMeta()`, `getDataTable()`, `getAll()`, `getOne()`, `create()`, `update()`, `toggleActive()`, `setHargaKavling()`, `cleanNumeric()`.
  - `TransaksiService`: `getMeta()`, `getBundle()`, `getStatusById()`, `saveTransaksi()`, `replaceConsumer()`, `saveStatus()`, `createOrUpdate()`, `update()`, `cleanNumeric()`.
  - `KavlingRequestService`: `isKavlingBlockedByAkad()`, `submitRequest()`, `getList()`, `getStatusCounts()`, `countFiltered()`, `updateStatus()`, `applyUbahTipeKavling()`.
- **Repositories (`app/Repositories/Transaksi/`):**
  - `HargaJualRepository`: `getHargaJualById()`, `hasModuleAccess()`, `getProyekList()`, `getDetailById()`, `getActiveByProyek()`, `getDataTableRows()`, `getDataTableFilteredCount()`, `getDataTableTotalCount()`, `insertFileHargajual()`, `insertHargaJual()`, `updateHargaJual()`, `findKavlingById()`, `updateKavlingHarga()`, `getPricelistRowById()`.
  - `TransaksiRepository`: `getKonsumenTransaksi()`, `findNikUsage()`, `getKonsumenByIdKavling()`, `getSpptbData()`, `lockConsumerReplacementContext()`, `getLegacyReplacementSpptbData()`.
  - `KavlingRepository`: `getKavlingByIdMkdt()`, `getIdProyekByIdMkdt()`, `getIdProyekByKavling()`, `getKavlingById()`, `getKavlingByIds()`, `getKavlingList()`, `getKavlingListForRequest()`, `getDiskresiByKavlingId()`, `getAll()`, `getPerintahBangun()`, `setPerintahBangun()`, `getKategoriOptions()`, `applyKategoriFilter()`.
  - `KavlingRequestRepository`: `insertJalans()`, `insertKavlings()`, `findPendingUbahTipeMap()`, `getKavlingEligibilityRows()`, `getJalanRowsByProyek()`, `findTipeByIdAndProyek()`, `getList()`, `getStatusCounts()`, `countFiltered()`, `getKavlingsByRequestId()`, `updateKavlingsTipe()`, `getProyekName()`, `getUserDisplayName()`, `getClusterName()`, `getJalanName()`, `getKavlingLabels()`.
- **Frontend JS & CSS (`public/assets/js/` & `public/assets/css/`):**
  - `hargajual.js`: Inisialisasi DataTables pricelist (dengan `drawCallback` `api.columns.adjust()` untuk gambar keterangan), filter proyek, modal tambah/edit/detail pricelist, kalkulasi live `syncHargaNet()` & `load_total()`, integrasi Quill & Flatpickr, dukungan paste gambar di editor keterangan (`setupQuillImagePaste()`, `preparePastedImageDataUrl()`, `renderCompressedImageCandidate()`, `getUtf8ByteLength()`, serta validasi batas 64 KB di `initFormValidation()`), dropzone upload `.pricelist-dropzone.bpb-dropzone` (`setupDropzone()`), serta fungsi global `window.add`, `window.edit`, `window.detail`, `window.remove`, `window.save`.
  - `hargajual.css`: Styling scoped `.hargajual-page`, `#add-modal`, `#detail-modal`, tema `#2057a3`, `.pricelist-dropzone.bpb-dropzone`, serta aturan gambar responsif `#add-modal .ql-editor img` dan `.hargajual-page #data_table td img`.
  - `siteplan/sales.js`: Handler modal transaksi penjualan kavling, form konsumen & jadwal uang muka, pergantian konsumen, serta perubahan status kavling di halaman Siteplan.
  - `siteplan/request-kavling.js`: Handler modal terpadu 3-tab Request Kavling (`openModalRequestKavling`, `handleContextRequestKavling`, `refreshRequestKavlingCounts` = `loadCountsSummary`, `.rk-kavling-count-badge` pada tabel & `#modal-apply-ubah-kavling` bila `kavlings.length > 1`, Konva canvas picker, preview 3D/Denah/Spek, tindak lanjut Planning + sub-modal `#modal-apply-ubah-kavling`, tab Riwayat, dan shim `openModalListRequestKavling`).

### Audit Verifikasi Migrasi (2026-11-22)
- **Status & Struktur Modular:** Status `REFACTORED` (`100/100`), 0 `non_modular_files`, 0 `missing_files`, 0 AutoRoute, dan `61` rute eksplisit di `app/Config/Routes.php`. Shim `app/Controllers/Hargajual.php` (71 baris) terverifikasi sebagai dual-shim delegator murni ke `Web\Transaksi\HargajualController` & `Api\Transaksi\HargajualController` tanpa logika atau akses DB.
- **Temuan Layering API Controller:**
  - `app/Controllers/Api/Transaksi/KavlingController.php` (L11-15, L28-36): menginjeksi dan memanggil `KavlingRepository::getKavlingList()` langsung dari Controller tanpa melalui Service layer (sudah extends `BaseApiController` & mendukung `?format=raw` di L38).
  - `app/Controllers/Api/Transaksi/KavlingRequestController.php::kavlingList()` (L14-23, L122): menginjeksi dan memanggil `KavlingRepository::getKavlingListForRequest()` langsung serta memformat payload response di Controller (L125-160) tanpa melalui `KavlingRequestService`.
- **Temuan Layering & Cross-Module Boundary pada Service:**
  - `app/Services/Transaksi/TransaksiService.php`: masih menjalankan query `$this->db->table()` / `$db->table()` langsung pada tabel `proyek` (L224), `mkdt` (L349, L924, L974), dan `kavling` (L428), serta melakukan mutasi langsung ke tabel modul lain/lainnya: `finance_ledger` (L444, owner `Keuangan`), `file_spptb` (L535), dan `konsumen` (L668, L939, owner `MKDT`).
  - `HargaJualService.php` & `KavlingRequestService.php` bersih dari query/mutasi tabel langsung (`$this->db` hanya dipakai untuk manajemen DB transaction).
- **Catatan Minor Sinkronisasi `MODULE.md`:** Jumlah rute eksplisit aktual di `app/Config/Routes.php` berjumlah `61` (catatan di atas L282 masih tertulis `60 Rute Eksplisit`) dan method `KavlingRequestController::cancel()` belum tercantum pada daftar `Key Functions & Core Business Logic` (L307).

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
- **Legacy Route Aliases & Frontend Shims:** Rute flat `/hargajual/*`, `/transaksi/*`, dan `/kavling/*` dipertahankan secara eksplisit di `app/Config/Routes.php` dan diarahkan langsung ke controller modular di `App\Controllers\Api\Transaksi\*`; gunakan rute kanonik `/api/hargajual/*`, `/api/transaksi/*`, dan `/api/kavling/*` untuk pengembangan baru. Pada frontend Request Kavling, `app/Views/siteplan/modal_list_request_kavling.php` telah di-deprecate/unmount dari `master.php` dan `window.openModalListRequestKavling()` menjadi shim alias ke `window.openModalRequestKavling({ tab: 'tambah' })`.

## Change Log
- `2026-11-22` [`AUDIT`] — Audit verifikasi migrasi modul `Transaksi` (`COMPLETED (Catatan Minor)`, Score 100/100): struktur modular & 11 shims (termasuk dual-shim `Hargajual.php`) serta 61 rute eksplisit terverifikasi; ditemukan pemanggilan langsung `KavlingRepository` di `Api\Transaksi\KavlingController` (L28) & `KavlingRequestController::kavlingList()` (L122), query/mutasi `$db->table()` langsung di `TransaksiService` (`proyek` L224, `mkdt` L349/L924/L974, `kavling` L428, `finance_ledger` L444, `file_spptb` L535, `konsumen` L668/L939), serta catatan minor drift `MODULE.md` (60 vs 61 rute, `cancel()` di Key Functions).
- **`2026-11-21` [`FEATURE`] — Grouping Pengajuan Batch `ubah_tipe` per Tipe Tujuan (`POST /api/kavling-request/submit`) & Refresh Counter / Badge Jumlah Kavling:**
  - **Routes & Public Contracts (`RAW_JSON_READY`):**
    - Memperbarui kontrak respons `POST /api/kavling-request/submit` (`KavlingRequestController::submit()` -> `KavlingRequestService::submitRequest()`) pada cabang batch `ubah_tipe` (`items[]`): mengelompokkan item eligible berdasarkan tipe tujuan (`master:{id_tipe}` atau `manual:{lowercase}`), membuat 1 header `kavling_request` (`id_kavling = null` bila grup > 1 unit) + `N` baris anak `kavling_request_kavling` per grup tipe, menggabungkan `keterangan` unik dengan `; `, mengirim tepat 1 notifikasi ringkasan grup `NotificationEvent::REQUEST_KAVLING` ke grup Planning (`[6]`), serta mengembalikan `request_count`, `kavling_count`, `groups`, `submitted_count`, `created_ids`, `skipped_count`, `skipped_pending`, dan `message`.
  - **Controller / Service / Repo / Model / Test:**
    - `app/Services/Transaksi/KavlingRequestService.php` (`1222` baris): Memperbarui `submitBatchItemsUbahTipe(int $idProyek, string $jenisRequest, array $data, int $userId): array` untuk validasi master tipe via `findTipeByIdAndProyek()`, pengelompokan per tipe tujuan, penyimpanan header + anak `kavling_request_kavling`, notifikasi ringkasan grup `[6]`, dan payload metrik grup.
    - `tests/unit/TargetSiteplanServiceTest.php`: Menambahkan tabel `db_kavling_request_kavling` pada schema test serta menambah test `testKavlingRequestBatchGroupedByTargetTipe()` (total `15 tests, 267 assertions` `PASS`).
  - **View / CSS / JS:**
    - `public/assets/js/siteplan/request-kavling.js` (`2051` baris): Mengekspos `window.refreshRequestKavlingCounts = loadCountsSummary` (dipanggil oleh `target.js` setelah pengajuan batch sukses) serta menampilkan `.rk-kavling-count-badge` (`N kavling`) pada `renderKavlingsCell()` dan `openApplyUbahKavlingModal()` bila `kavlings.length > 1`.
    - *(Aset CSS terkait `public/assets/css/siteplan-request-kavling.css` [`1295` baris] dan perubahan UI Target Siteplan `target.php`/`target.js`/`siteplan-target.css` dicatat pada modul `Siteplan`)*.
  - **Deprecations & Verification:** Tidak ada fungsi, route, atau shim baru yang di-deprecate. Terverifikasi QA `PASS` (`php spark module:sync Transaksi`, `php spark routes`, `TargetSiteplanServiceTest` `15 tests, 267 assertions`, `php spark module:deadcode Transaksi` `0` dead code pada `KavlingRequestService`, & Playwright E2E role MKDT & Planning).
- **`2026-11-21` [`FEATURE` + `BUGFIX`] — Revamp Alur Request Kavling Terpadu (Multi-Jalan, Multi-Kavling, Canvas Picker, & Terapkan Ubah Tipe):**
  - **Database & Migration:** `app/Database/Migrations/2026-11-21-000001_RevampKavlingRequestTables.php` melakukan truncate `kavling_request`, menambah kolom `applied_id_tipe`, `catatan_planning`, `processed_by`, dan `processed_at`, serta membuat tabel anak `kavling_request_jalan` dan `kavling_request_kavling`.
  - **Routes & Public Contracts (`RAW_JSON_READY`):**
    - Menambahkan rute baru `POST /api/kavling-request/apply-ubah-kavling` (`KavlingRequestController::applyUbahKavling`, RBAC `['1', '6']`) untuk menerapkan perubahan `kavling.id_tipe` secara atomik (tanpa mengubah `harga_akhir` atau `mkdt`), mencatat audit `HistoryService::log('siteplan', ['action' => 'request_ubah_tipe_kavling'])`, mengubah status menjadi `approved` + `applied_id_tipe`, serta mengirim notifikasi balik ke pengaju/MKDT.
    - Memperbarui kontrak `POST /api/kavling-request/submit` (RBAC `['1', '4']`, atau `['1', '4', '6']` untuk payload `items[]` dari Target Siteplan): mendukung multi-jalan `id_jalans[]` pada `tambah_baru`, multi-kavling `id_kavlings[]` pada `ubah_tipe`, wajib `keterangan`, opsional `id_tipe`/`tipe_manual`, dan validasi blokir kavling berstatus aktif `Akad`/`Akad Indent`.
    - Memperbarui `POST /api/kavling-request/kavling-list` (RBAC `['1', '4', '6']`): beralih ke `KavlingRepository::getKavlingListForRequest()`, mengecualikan kavling aktif `Akad`/`Akad Indent`, serta mengembalikan `current_id_tipe`, `current_tipe_label`, `has_active_konsumen`, `nama_konsumen`, dan `status_mkdt`.
    - Memperbarui `POST /api/kavling-request/list` (RBAC `['1', '4', '6']`): mendukung filter `jenis_request` & `status=history`, menyertakan `requested_tipe` & `applied_tipe` lengkap dengan Signed URL `FileAccessService`, `jalans[]`, `kavlings[]`, `catatan_planning`, `counts` (termasuk `tambah_baru_pending`, `ubah_tipe_pending`, `history`), dan `meta.permissions` (`can_submit`, `can_process`, `can_apply_ubah_kavling`).
    - Memperbarui `POST /api/kavling-request/update-status` (RBAC `['1', '6']`): mewajibkan `catatan_planning` (fallback `alasan_tolak`/`notes`) saat `rejected`, opsional saat `approved`, mengisi `processed_by`/`processed_at`, dan mengirim notifikasi balik `NotificationEvent::REQUEST_KAVLING` ke pengaju/MKDT.
  - **Controller / Service / Repo / Model:**
    - `app/Repositories/Transaksi/KavlingRequestRepository.php` [BARU] (`830` baris, 16 public methods — didaftarkan di `ModuleCatalog.php`).
    - `app/Models/KavlingRequestModel.php`: menambah `applied_id_tipe`, `catatan_planning`, `processed_by`, `processed_at` ke `$allowedFields`.
    - `app/Repositories/Transaksi/KavlingRepository.php`: menambah `getKavlingListForRequest(int $idProyek, string $search = '', int $limit = 50)`.
    - `app/Services/Transaksi/KavlingRequestService.php`: menambah `isKavlingBlockedByAkad()`, `countFiltered()`, `applyUbahTipeKavling()`, serta dukungan multi-jalan/multi-kavling, DTO media/spek tipe, dan notifikasi balik ke pengaju/MKDT.
    - `app/Controllers/Api/Transaksi/KavlingRequestController.php`: memperketat RBAC `[1, 4, 6]` sesuai peran dan menambah `applyUbahKavling()`.
  - **View / CSS / JS:**
    - `public/assets/js/siteplan/request-kavling.js` (`2038` baris): revamp modal terpadu 3-tab (`#modal-request-kavling`), Select2 multi-jalan & multi-kavling, preview 3D/Denah/Spek + lightbox, Konva canvas picker (`enterCanvasPickMode`, `handleRequestKavlingCanvasClick`, `renderRequestKavlingCanvasHighlights`, `cancelRequestKavlingCanvasPick`), tindak lanjut Planning (`#modal-apply-ubah-kavling` + link `/tipe` `target="_blank"` + `Refresh Tipe`), tab Riwayat, dan update badge pending. (Aset view/CSS terkait dicatat pada modul `Siteplan`).
  - **Deprecations & Verification:** `KavlingRepository::getKavlingList()` tetap dipertahankan untuk pemanggil lama; `modal_list_request_kavling.php` di-unmount dari `master.php`; `window.openModalListRequestKavling()` menjadi shim alias ke `window.openModalRequestKavling({ tab: 'tambah' })`. Terverifikasi QA `PASS` (`php spark routes`, `TargetSiteplanServiceTest` `13 tests, 186 assertions`, `php spark module:sync Transaksi`, & Playwright E2E role MKDT, Planning, & Keuangan).
- **`2026-11-20` [`UI_REPORT`] — Dukungan Clipboard Paste Gambar pada Editor Keterangan Harga Jual & Proteksi Cross-Module Select2 `#sh-id`:**
  - **Routes & Public Contracts:** Tidak berubah (Opsi 1 *Frontend-Only*; tetap menggunakan kontrak endpoint existing `POST /hargajual/add`, `POST /hargajual/edit`, `POST /hargajual/get/all`, dan `POST /hargajual/getAll`).
  - **Controller / Service / Repo / Model:** Tidak ada perubahan.
  - **View / CSS / JS:**
    - `app/Views/hargajual/index.php`: Memperbarui hint label editor keterangan menjadi `Rich Text Editor (mendukung Ctrl + V gambar)` dan menghapus teks petunjuk `Ctrl + V` pada `#dropzone-prompt` form upload PDF agar sinkron dengan penghapusan tangkapan clipboard paste upload.
    - `public/assets/js/hargajual.js` (`843` baris): Mengganti `setupDropzoneAndPaste()` menjadi `setupDropzone()` (menghapus handler `$modal.off('paste').on('paste', ...)` dan variabel `$modal`); menambahkan konstanta batas kolom `TEXT` (`MAX_RAW_IMAGE_DATA_URL_CHARS = 38000`, `MAX_KETERANGAN_BYTES = 62000`, `HARD_KETERANGAN_BYTES = 64000`), helper `getUtf8ByteLength()`, `renderCompressedImageCandidate()`, `preparePastedImageDataUrl()`, serta `setupQuillImagePaste(quillInstance)` (capture-phase paste listener pada `quill.root` dengan anti-regression guard salinan teks+HTML Excel/Word, kompresi canvas WebP/JPEG bertahap, dan guard kapasitas ~62 KB); menambahkan guard batas `64000` byte pada `submitHandler` di `initFormValidation()` dan mengecualikan elemen `contenteditable` Quill (`ignore: ':hidden, #keterangan-editor [contenteditable], .ql-editor, .ql-clipboard'`) pada `$('#add-form').validate()`; serta menambahkan `drawCallback` pada DataTables `#data_table` untuk memanggil `api.columns.adjust()` setelah `<img>` selesai dimuat.
    - `public/assets/css/hargajual.css` (`423` baris): Menambahkan CSS scoped `#add-modal .ql-editor img` (`max-width: 100%`, `height: auto`) dan `.hargajual-page #data_table td img` (`max-width: 100%`, `max-height: 120px`, `object-fit: contain`).
    - `public/assets/js/siteplan/mkdt.js` *(Cross-Module Caller Protection — aset modul `MKDT`)*: Menambahkan `formatKeteranganSummary(rawHtml)` dan memperkuat `formatDesign(item)` pada Select2 `#sh-id` (Modal Set Harga di Siteplan) agar tag `<img src="data:image/...;base64,...">` diringkas menjadi `[Gambar]` serta aman dari pemecahan `split(';')` dan XSS.
  - **Deprecations:** Tidak ada.
- **`2026-11-18` [`API-BRIDGE`] — Penambahan Agregasi `tagihan_total_nominal` & `tagihan_sudah_bayar` pada `KavlingRepository` untuk Inspector Siteplan SPA:**
  - **Routes & Public Contracts (`RAW_JSON_READY`):** Tidak ada perubahan rute baru; payload item kavling dari `KavlingRepository::getAll()` (dikonsumsi oleh `GET|POST /api/siteplan/kavling` dan `/siteplan/get/all`) kini menyertakan field agregat `tagihan_total_nominal` dan `tagihan_sudah_bayar`.
  - **Controller / Service / Repo / Model:**
    - `app/Repositories/Transaksi/KavlingRepository.php` (`791` baris): Memperluas `addCompositeFinanceSelect()`, menambahkan `addKeuanganFinanceSelect()`, `addTagihanPaymentDerivedJoins()`, dan `selectTagihanAggregates()`, serta memanggil `addKeuanganFinanceSelect()` pada cabang `$idDivisi === 3` di `getAll()` untuk mengembalikan agregasi `tagihan_total_nominal` dan `tagihan_sudah_bayar` secara *Zero-Downtime*.
    - `tests/unit/KavlingCompositeQueryTest.php`: Menambahkan cakupan unit test untuk agregasi `tagihan_total_nominal` & `tagihan_sudah_bayar`.
  - **View / CSS / JS:** Tidak ada perubahan aset view/JS pada modul `Transaksi` (dikonsumsi oleh `SiteplanInspectorCard.vue` di `sigapp-vue`).
  - **Deprecations:** Tidak ada.
- **`2026-11-16` [`BUGFIX`] — Perbaikan Null-Safe Data Pasangan & Kolom `status_pernikahan` pada Cetak SPPTB:**
  - **Routes & Public Contracts:** Tidak berubah; `TransaksiRepository::getSpptbData(int $idKavling, int $idMkdt): ?object` kini menyertakan kolom `` `konsumen`.`status_pernikahan` `` pada objek data SPPTB yang dikembalikan ke `PrintService::printSpptb()` dan caller lainnya.
  - **Controller / Service / Repo / Model:**
    - `app/Repositories/Transaksi/TransaksiRepository.php`: Menambahkan `` `konsumen`.`status_pernikahan`, `` pada query `SELECT` di `getSpptbData(int $idKavling, int $idMkdt)` (baris 166).
  - **View / CSS / JS:** Tidak ada perubahan aset di modul `Transaksi` (tampilan PDF dikelola pada `app/Views/pdf/spptb-new.php` di modul `Keuangan`).
  - **Deprecations:** Tidak ada.
- **`2026-11-16` [`FEATURE`] — Enhancement Fitur Target Siteplan (Fase 5–8: Batch `ubah_tipe` & Deduplikasi Pending pada `KavlingRequest`):**
  - **Routes & Public Contracts (`RAW_JSON_READY`):**
    - Memperluas kontrak `POST /api/kavling-request/submit` (`KavlingRequestController::submit()` / `KavlingRequestService::submitRequest()`) agar mendukung pengajuan batch `ubah_tipe` melalui parameter `items` atau `id_kavlings` dalam satu transaksi DB (`created_ids`, `submitted_count`, `skipped_count`, `skipped_pending`).
  - **Controller / Service / Repo / Model:**
    - `app/Controllers/Api/Transaksi/KavlingRequestController.php`: Memperbarui `submit()` untuk memvalidasi payload batch `items` dan `id_kavlings`.
    - `app/Services/Transaksi/KavlingRequestService.php`: Menambahkan `submitBatchUbahTipe()`, `normalizeBatchItems()`, dan `findPendingUbahTipeMap()` untuk pemrosesan batch `ubah_tipe`, deduplikasi request yang masih berstatus `pending` (`skipped_pending`), serta pengiriman notifikasi `NotificationEvent::REQUEST_KAVLING` ke grup Planning (`[6]`).
    - `tests/unit/TargetSiteplanServiceTest.php`: Menambahkan unit test batch & deduplikasi `pending` pada `KavlingRequestService::submitRequest()`.
  - **View / CSS / JS:** Tidak ada perubahan aset di modul `Transaksi` (dikonsumsi oleh `public/assets/js/siteplan/target.js`).
  - **Deprecations:** Tidak ada.
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
