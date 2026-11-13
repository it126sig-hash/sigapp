# Module: MKDT

**Status:** `MAPPED`  
**Readiness Score:** `80/100`  
**Last Synced:** `2026-11-13 12:46:15` @ `4975daa`  

## Purpose
Manajemen Konsumen dan Transaksi (MKDT): tata kelola siklus transaksi konsumen mulai dari NUP, booking unit, verifikasi data konsumen, posisi kavling, wawancara perbankan, persetujuan SP3K, akad indent (PPJB), akad AJB, mutasi/pergantian konsumen, hingga pembatalan unit (`mkdt`, `konsumen`, `mkdt_kavling_history`).

Termasuk di dalamnya subfitur pelaporan performa penjualan dan konversi transaksi **Laporan Booking & Akad** (`/laporan/booking-akad`). Subfitur ini mengelola pemantauan 12 bulan matriks transaksi 6 status (Booking, Wawancara, SP3K, Batal, Akad Indent, Akad AJB), perhitungan rasio konversi riil berbasis cohort transaksi unik (`mkdt.uniq_id`), perbandingan performa tahunan side-by-side, serta rincian data unit kavling terintegrasi modal siteplan.

## Out of scope
- Hal-hal di luar tanggung jawab domain MKDT (seperti penjurnalan akuntansi keuangan kas/bank, konstruksi fisik produksi, dan penerbitan sertifikat legal induk).

## Invariants
- **Exclusive Write Ownership**: Modul lain dilarang memanipulasi tabel pemilik (`mkdt`, `konsumen`, `mkdt_kavling_history`, `promo`, `riwayat_export_poskon`, `si`, `list_si`) secara langsung; seluruh mutasi wajib melalui Service MKDT.
- **Service-Level Financial & Data Sanitization**: Perubahan data finansial dan status transaksi wajib diverifikasi di level Service dengan sanitasi numerik aman (`cleanNumeric()`) dan dibungkus transaksi database atomic (`$db->transStart()` / `$db->transComplete()`).
- **6 Statuses Pipeline Integrity**: Transaksi unit diklasifikasikan secara tegas ke dalam 6 kategori status resmi: `booking`, `wawancara`, `sp3k`, `batal`, `akad_indent`, dan `akad_ajb`.
- **Status Precedence Hierarchy**: Dalam penentuan status terakhir transaksi (terutama pada drilldown modal detail dan klasifikasi data historis), diterapkan hierarki ketat:
  $$\text{Batal} \succ \text{Akad AJB} \succ \text{Akad Indent} \succ \text{SP3K} \succ \text{Wawancara} \succ \text{Booking}$$
  - Transaksi yang berstatus Batal (`is_batal = 1` atau `status_mkdt = 'Batal'`) selalu diprioritaskan sebagai Batal.
  - Transaksi Akad AJB (`akad = 1` atau `akad_tgl > '1970-01-01'`) diprioritaskan di atas Akad Indent.
  - Transaksi Akad Indent (`akad_indent = 1` atau `akad_indent_tgl > '1970-01-01'`) diprioritaskan di atas SP3K.
  - Transaksi SP3K (`sp3k = 1`, `sp3k_tgl > '1970-01-01'`, atau `sp3k_no` terisi valid bukan `''`/`'-'`) diprioritaskan di atas Wawancara.
  - Transaksi Wawancara (`wawancara = 1` atau `wawancara_tgl > '1970-01-01'`) diprioritaskan di atas Booking murni.
- **Real Cohort Conversion based on `mkdt.uniq_id`**: Rasio konversi dihitung bukan dari perbandingan kuantitas per bulan kalender, melainkan dari cohort transaksi unik berbasis kode transaksi (`mkdt.uniq_id`). Mengukur berapa persen transaksi unik yang melakukan booking pada bulan bersangkutan yang akhirnya berhasil mencapai akad (baik Akad Indent maupun Akad AJB) sepanjang siklus hidupnya. Persentase dibatasi pada interval $[0.0\%, 100.0\%]$.
- **Safe Zero-Date Filtering**: Seluruh query agregasi dan tanggal status wajib memfilter tanggal tidak valid atau dirty zero-dates (`'0000-00-00'`, `'0000-00-00 00:00:00'`, `NULL`, atau `<= '1970-01-01'`) menggunakan kriteria `> '1970-01-01'` untuk mencegah kesalahan parsing dan kegagalan fungsi tanggal. Tanggal yang tidak valid dirender sebagai `-`.
- **Modal Detail 7-Column Contract**: Tabel modal detail transaksi (`#booking_akad_detail_table`) wajib menyajikan tepat 7 kolom:
  1. `No` (`text-center`, width 40px): Nomor urut baris DataTables (`meta.row + meta.settings._iDisplayStart + 1`).
  2. `Kavling` (`text-left`): Nama Jalan + No. Kavling + sales muted (`<small class="text-muted d-block">${sales}</small>`).
  3. `Konsumen` (`text-left`): Nama Konsumen + tipe rumah pricelist (`<small class="text-muted d-block">${tipe_rumah}</small>`) + badge Subsidi/Komersil (`badge-light-success` / `badge-light-primary`).
  4. `Tanggal Booking` (`text-center`): Tanggal transaksi booking unit berformat `d-m-Y`.
  5. `Status Terakhir` (`text-left`): Tanggal milestone status terakhir (`booking-akad-status-date`) berformat `d-m-Y` (atau `-`) disertai badge status semantik (`secondary`, `warning`, `primary`, `danger`, `info`, `success`).
  6. `Catatan / Keterangan` (`text-left`): Teks catatan transaksi MKDT bertingkat dari `mkdt` (`keterangan_batal` -> `keterangan` -> `keterangan_status` -> `-`).
  7. `Aksi` (`text-center`, width 70px): Tombol aksi `.booking-akad-view-kavling` untuk memicu modal detail kavling siteplan (`#modal_detail`) dengan integrasi stacked modal `window.initSigappModalStacking()` (z-index 1070 di atas modal laporan 1050).
- **6-Column Matrix Layout with Embedded Conversion**: Tabel matriks tahunan menyajikan tepat 6 kolom kategori status per tahun (`Booking`, `Wawancara`, `SP3K`, `Batal`, `Akad Indent`, `Akad AJB`), di mana persentase konversi kohort riil disematkan langsung di dalam sel `Booking` (pada baris bulanan dan Total Tahun via `.booking-cell-wrapper` dan `.booking-konversi-sub`).
- **Event-Based Monthly Totals & Detail Alignment**: Angka bulanan untuk setiap kategori status merefleksikan kondisi kualifikasi tahap dan tanggal efektif terjadinya aktivitas/milestone masing-masing pada bulan tersebut (`booking_tgl`, `stageDateSql('m.wawancara_tgl')`, `stageDateSql('m.sp3k_tgl')`, `batalDateExpr`, `stageDateSql('m.akad_indent_tgl')`, `stageDateSql('m.akad_tgl')`), dan query modal detail diselaraskan 100% sehingga `recordsTotal` modal detail identik dengan angka pada sel matriks.
- **Text Alignment Standard (R4)**: Seluruh teks pada tabel laporan dan tabel modal detail wajib rata kiri (`text-left`), kecuali kolom nominal/angka metrik rata kanan (`text-right`), serta kolom tanggal, nomor urut, dan rasio persentase rata tengah (`text-center`).

## Database Tables
### Owner Table(s)
- `mkdt` *(akses tulis eksklusif hanya untuk modul MKDT)*
- `konsumen` *(akses tulis eksklusif hanya untuk modul MKDT)*
- `mkdt_kavling_history` *(akses tulis eksklusif hanya untuk modul MKDT)*
- `promo` *(akses tulis eksklusif hanya untuk modul MKDT)*
- `riwayat_export_poskon` *(akses tulis eksklusif hanya untuk modul MKDT)*
- `si` *(akses tulis eksklusif hanya untuk modul MKDT)*
- `list_si` *(akses tulis eksklusif hanya untuk modul MKDT)*

### Shared Tables (Dibaca dari modul lain)
- `kavling`
- `keuangan`
- `legal`

## Contracts
### A. Public HTTP Endpoints & Routes Inventory
| Method | URI / Pattern | Tipe | Handler | Status Migrasi | Target API / Catatan |
|---|---|---|---|---|---|
| `GET` | `/list-batal` | `Explicit` | `Mkdt::list_batal` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `GET` | `/list-kavling` | `Explicit` | `\App\Controllers\Web\Mkdt\PosisiKonsumenController::index` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/list-kavling/(:segment)` | `Explicit` | `\App\Controllers\Web\Mkdt\PosisiKonsumenController::index/$1` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/list-kavling/ambil` | `Explicit` | `\App\Controllers\Api\Mkdt\PosisiKonsumenController::getDataTables` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/list-kavling/akad/ambil` | `Explicit` | `\App\Controllers\Api\Mkdt\PosisiKonsumenController::getDataTablesAkad` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/list-kavling/batal/ambil` | `Explicit` | `\App\Controllers\Api\Mkdt\PosisiKonsumenController::getDataTablesBatal` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/export/poskon/(:segment)/(:segment)` | `Explicit` | `\App\Controllers\Api\Mkdt\PosisiKonsumenController::exportPoskon/$1/$2` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/riwayat/poskon/(:segment)` | `Explicit` | `\App\Controllers\Api\Mkdt\PosisiKonsumenController::getRiwayatExport/$1` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/mkdt/posisi-konsumen/export/(:segment)/(:segment)` | `Explicit` | `PosisiKonsumenController::exportPoskon/$1/$2` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/nup/list` | `Explicit` | `NupController::list` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/nup/detail` | `Explicit` | `NupController::detail` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/nup/save` | `Explicit` | `NupController::save` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/nup/pull` | `Explicit` | `NupController::pull` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/nup/kavling-options` | `Explicit` | `NupController::kavlingOptions` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/nup/kavling-history` | `Explicit` | `NupController::kavlingHistory` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/promo` | `Explicit` | `\App\Controllers\Web\Mkdt\PromoController::index` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/mkdt/promo/add` | `Explicit` | `PromoController::add` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/mkdt/promo/edit` | `Explicit` | `PromoController::edit` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/mkdt/promo/edit/(:num)` | `Explicit` | `PromoController::edit/$1` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/mkdt/promo/remove` | `Explicit` | `PromoController::remove` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/mkdt/promo/remove/(:num)` | `Explicit` | `PromoController::remove/$1` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/laporan/booking-akad` | `Explicit` | `\App\Controllers\Web\Mkdt\BookingAkadReportController::index` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/laporan/booking-akad/summary` | `Explicit` | `BookingAkadReportController::summary` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/laporan/booking-akad/detail` | `Explicit` | `BookingAkadReportController::detail` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/list-stock` | `Explicit` | `\App\Controllers\Web\Mkdt\StockController::index` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/mkdt/si/save` | `Explicit` | `MkdtOperationController::saveSi` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/mkdt/batal/simpan` | `Explicit` | `MkdtOperationController::simpanBatal` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/mkdt/saveSI` | `Explicit` | `MkdtOperationController::saveSi` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/mkdt/savesi` | `Explicit` | `MkdtOperationController::saveSi` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/mkdt/simpan_batal` | `Explicit` | `MkdtOperationController::simpanBatal` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `ANY` | `/mkdt/initcontroller` | `AutoRoute` | `Mkdt::initController` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/mkdt/get_data_by_id` | `AutoRoute` | `Mkdt::get_data_by_id` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/mkdt/simpan_batal` | `AutoRoute` | `Mkdt::simpan_batal` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/mkdt/batal_mkdt` | `AutoRoute` | `Mkdt::batal_mkdt` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/mkdt/save` | `AutoRoute` | `Mkdt::save` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/mkdt/set_harga` | `AutoRoute` | `Mkdt::set_harga` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/mkdt/list_stock` | `AutoRoute` | `Mkdt::list_stock` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/mkdt/getliststock` | `AutoRoute` | `Mkdt::getListStock` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/mkdt/if_where` | `AutoRoute` | `Mkdt::if_where` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/mkdt/format_tgl` | `AutoRoute` | `Mkdt::format_tgl` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/mkdt/is_active` | `AutoRoute` | `Mkdt::is_active` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/mkdt/getsi` | `AutoRoute` | `Mkdt::getsi` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/mkdt/savesi` | `AutoRoute` | `Mkdt::saveSI` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/mkdt/export_xlsx` | `AutoRoute` | `Mkdt::export_xlsx` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |

### B. Public PHP Services (Backend Contract Antar-Modul)
#### `MkdtHistoryService.php`
- `actionLabel(string $action)`
- `log(int $idKavling, ?int $idMkdt, string $action, string $summary, ?array $oldData = null, ?array $newData = null, ?int $actorId = null, array $metadata = [])`
- `getConsumerReplacementSnapshots(int $idMkdt)`
- `getConsumerReplacementHistory(int $idMkdt, int $idKavling)`
- `getHistory(int $idKavling, int $limit = 10, int $offset = 0)`
- `buildSetHargaSummary(?array $old, array $new, ?string $pricelistLabel = null)`
- `buildKonsumenSummary(?object $oldMkdt, array $kons, array $mk, bool $isNew)`
- `buildStatusSummary(?object $old, array $new, array $perintahBangun = [])`
- `buildTurunPembangunanSummary(?array $old, array $new)`
- `buildStandingInstructionSummary(array $items)`
- `buildBatalSummary(array $data)`
#### `MkdtSettlementService.php`
- `synchronize(int $idMkdt)`
#### `MkdtFinancialBreakdownService.php`
- `targets(array|object $mkdt)`
- `validTurunKpr(array|object $mkdt)`
- `progress(float $target, float $paid)`
- `hasFinancialChanges(?object $old, array $new)`
- `validateContractAndSchedule(array $mkdt, array $schedules)`
- `validatePaymentAllocation(int $idMkdt, array $allocations, float $headerNominal)`
#### `MkdtFinancialReconciliationService.php`
- `audit(?int $idMkdt = null)`
- `apply(array $manifest, ?int $actorId = null)`
#### `PosisiKonsumenService.php`
- `getIndexData(?string $status, int $userId, array $userGroups)`
- `userHasProjectAccess(?object $proyek, int $userId)`
- `getRawDataTables(array $params, ?string $status = null)`
- `getRawDataTablesBatal(array $params)`
- `exportPoskon(string $tipe, string $status, array $filters, int $userId)`
- `getDataTable($request, $status = null)`
- `getDataTablesBatal($request)`
- `getRiwayatExport($id_proyek, $status)`
- `insertRiwayatExport($data)`
- `format_tgl($tgl)`
- `is_active($id, $texts, $textf)`
#### `KonsumenService.php`
- `upsert(?int $idKonsumen, array $data)`
- `getKonsumenTransaksi($id_mkdt, $select = [])`
- `getByIDKavling($id_kavling)`
#### `NupService.php`
- `getListByProyek(int $idProyek, ?string $search = null)`
- `getDetail(int $idMkdt)`
- `saveNup(array $post, array $files, int $actorId)`
- `pullToNup(array $post, ?UploadedFile $fileLampiran, int $actorId)`
- `getKavlingOptions(int $idProyek, ?string $search = null, ?int $currentIdKavling = null, ?int $lastIdKavling = null, ?string $filterHargaJual = null)`
- `getFormattedKavlingHistory(int $idMkdt = 0, int $idKavling = 0)`
#### `PromoService.php`
- `getIndexData()`
- `getDataTables(array $params, bool $raw = false)`
- `getAll(?int $idProyek = null, string $search = '', bool $raw = false)`
- `getOne(int $id)`
- `create(array $input, int $actorId)`
- `update(int $id, array $input, int $actorId)`
- `deactivate(int $id, int $actorId)`
- `formatDate(?string $tgl)`
- `statusBadge($id, string $activeText, string $inactiveText)`
- `cleanNumeric(mixed $value)`
#### `BookingAkadReportService.php`
- `getAvailableYears(int $idProyek)`
- `getSummary(int $idProyek, int $yearA, ?int $yearB = null)`
- `getDetail(int $idProyek, array|int $requestOrYear, ?int $month = null, ?string $category = null, array $dataTable = [])`
- `monthLabel(int $month)`
- `resolveStatusBadge(string $status)`
- `resolveStatusTerakhir(array $row)`
- `resolveTanggalStatusTerakhir(array $row, string $status)`
- `resolveCatatanMkdt(array $row)`
- `formatDate(?string $date)`
#### `StockService.php`
- `getIndexData()`
- `getListStock(array $params, bool $raw = false)`
- `formatTanggal(?string $tgl)`
- `formatDateTime(?string $datetime)`
#### `StandingInstructionService.php`
- `getByKavling(int $idKavling)`
- `save(int $idKavling, array $items, array $uploadedFiles, int $actorId)`
#### `BatalBookingService.php`
- `cleanNumeric(mixed $value)`
- `getDetail(int $idMkdt)`
- `cancelBooking(array $input, ?UploadedFile $suratBatalFile, int $actorId, array $userGroupIds)`

## File Inventory
### Controllers (10)
- `app/Controllers/Mkdt.php` (799 baris, 14 public methods: `initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)`, `get_data_by_id($st = null)`, `simpan_batal()`, `batal_mkdt()`, `save()`, `set_harga()`, `list_stock()`, `getListStock()`, `if_where($var, $column, $condition, $query)`, `format_tgl($tgl)`, `is_active($id, $texts, $textf)`, `getsi()`, `saveSI()`, `export_xlsx()`)
- `app/Controllers/Web/Mkdt/PosisiKonsumenController.php` (38 baris, 1 public methods: `index($status = null)`)
- `app/Controllers/Api/Mkdt/PosisiKonsumenController.php` (145 baris, 6 public methods: `meta()`, `getDataTables($status = null)`, `getDataTablesAkad()`, `getDataTablesBatal()`, `exportPoskon($tipe = 'xlsx', $status = 'aktif')`, `getRiwayatExport($status = 'Aktif')`)
- `app/Controllers/Api/Mkdt/NupController.php` (195 baris, 6 public methods: `list()`, `detail()`, `save()`, `pull()`, `kavlingOptions()`, `kavlingHistory()`)
- `app/Controllers/Web/Mkdt/PromoController.php` (33 baris, 1 public methods: `index()`)
- `app/Controllers/Api/Mkdt/PromoController.php` (206 baris, 7 public methods: `meta()`, `getDataTable()`, `getAll()`, `getOne($id = null)`, `add()`, `edit($id = null)`, `remove($id = null)`)
- `app/Controllers/Web/Mkdt/BookingAkadReportController.php` (49 baris, 1 public methods: `index()`)
- `app/Controllers/Api/Mkdt/BookingAkadReportController.php` (81 baris, 2 public methods: `summary()`, `detail()`)
- `app/Controllers/Web/Mkdt/StockController.php` (27 baris, 1 public methods: `index()`)
- `app/Controllers/Api/Mkdt/MkdtOperationController.php` (219 baris, 6 public methods: `meta()`, `getListStock()`, `getSi()`, `saveSi()`, `getBatal()`, `simpanBatal()`)

### Services (12)
- `app/Services/Mkdt/MkdtHistoryService.php` (316 baris, 11 public methods)
- `app/Services/Mkdt/MkdtSettlementService.php` (48 baris, 1 public methods)
- `app/Services/Mkdt/MkdtFinancialBreakdownService.php` (254 baris, 6 public methods)
- `app/Services/Mkdt/MkdtFinancialReconciliationService.php` (275 baris, 2 public methods)
- `app/Services/Mkdt/PosisiKonsumenService.php` (822 baris, 11 public methods)
- `app/Services/Mkdt/KonsumenService.php` (49 baris, 3 public methods)
- `app/Services/Mkdt/NupService.php` (536 baris, 6 public methods)
- `app/Services/Mkdt/PromoService.php` (492 baris, 10 public methods)
- `app/Services/Mkdt/BookingAkadReportService.php` (755 baris, 9 public methods)
- `app/Services/Mkdt/StockService.php` (174 baris, 4 public methods)
- `app/Services/Mkdt/StandingInstructionService.php` (190 baris, 2 public methods)
- `app/Services/Mkdt/BatalBookingService.php` (270 baris, 3 public methods)

### Repositories (9)
- `app/Repositories/Mkdt/MkdtHistoryRepository.php` (55 baris, 4 public methods: `hasTable()`, `insert(array $fields)`, `countByKavling(int $idKavling)`, `getByKavling(int $idKavling, int $limit, int $offset)`)
- `app/Repositories/Mkdt/PosisiKonsumenRepository.php` (576 baris, 13 public methods: `getBaseQuery($status = "Booking")`, `getQueryBatal()`, `getRiwayatExport($id_proyek, $status)`, `insertRiwayatExport($data)`, `getFirstProyek()`, `getPphList()`, `getPpnList()`, `getLiKeu()`, `getConfigShapeMap()`, `getChecklistSubItemsWithGroup()`, `getHakAksesByUser(int $userId, ?string $namaHak = null)`, `getRawDataTables(array $params, ?string $status = null)`, `getRawDataTablesBatal(array $params)`)
- `app/Repositories/Mkdt/KonsumenRepository.php` (60 baris, 1 public methods: `getKonsumenTransaksi(int $idMkdt, $select = [])`)
- `app/Repositories/Mkdt/NupRepository.php` (412 baris, 10 public methods: `getNupListByProyek(int $idProyek, ?string $search = null)`, `getNupDetail(int $idMkdt, bool $lock = false)`, `getKavlingRow(int $idKavling, bool $lock = false)`, `getKavlingLabel(?int $idKavling)`, `getProyekIdByKavling(?int $idKavling)`, `getKavlingOptionsForTransfer(int $idProyek, ?string $search = null, ?int $currentIdKavling = null, ?int $lastIdKavling = null, ?string $filterHargaJual = null)`, `insertKavlingHistory(array $data)`, `getKavlingHistory(int $idMkdt = 0, int $idKavling = 0)`, `isNoNupExists(string $noNup, int $idProyek, int $excludeIdMkdt = 0)`, `generateNextNupNumber(int $idProyek)`)
- `app/Repositories/Mkdt/PromoRepository.php` (201 baris, 7 public methods: `getProyekList()`, `getDataTables(array $params)`, `getAll(?int $idProyek = null, string $search = '')`, `getById(int $id)`, `insert(array $fields)`, `updateById(int $id, array $fields)`, `deactivate(int $id, int $actorId, string $timestamp)`)
- `app/Repositories/Mkdt/BookingAkadReportRepository.php` (659 baris, 8 public methods: `getAvailableYears(int $idProyek)`, `getAvailableYearBounds(int $idProyek)`, `getMonthlyBookingTotals(int $idProyek, array $years)`, `getMonthlyAkadTotals(int $idProyek, array $years)`, `getMonthlyStatusTotals(int $idProyek, array $years)`, `getMonthlySummaryTotals(int $idProyek, array $years)`, `getSummary(int $idProyek, int $year)`, `getDetailRows(int $idProyek, int $year, int $month, string $category, array $dataTable = [])`)
- `app/Repositories/Mkdt/StockRepository.php` (162 baris, 1 public methods: `getStockListDataTables(array $params)`)
- `app/Repositories/Mkdt/StandingInstructionRepository.php` (100 baris, 6 public methods: `getByKavling(int $idKavling)`, `getSiListIdBySiId(int $idSi)`, `getListSiNameById(int $idListSi)`, `getKavlingMkdtId(int $idKavling)`, `insertSi(array $data)`, `updateSi(int $idSi, array $data)`)
- `app/Repositories/Mkdt/BatalBookingRepository.php` (88 baris, 5 public methods: `getMkdtDetailForBatal(int $idMkdt)`, `getMkdtRawById(int $idMkdt)`, `getPembayaranLogsByMkdt(int $idMkdt)`, `getProyekIdByKavling(int $idKavling)`, `updateMkdtBatal(int $idMkdt, array $data)`)

### Models (3)
- `app/Models/MkdtModel.php`
- `app/Models/KonsumenModel.php`
- `app/Models/MkdtKavlingHistoryModel.php`

### Views (3 folder)
- `app/Views/kavling` (4 file: `list-kavling-akad.php`, `list-kavling-batal.php`, `list-kavling.php`, `list-stock.php`)
- `app/Views/promo` (1 file: `index.php`)
- `app/Views/laporan` (3 file: `booking-akad.php`, `cash-in.php`, `hasil-akad.php`)

### JavaScript (2 file)
- `public/assets/js/siteplan/mkdt.js` (3299 baris)
- `public/assets/js/laporan-booking-akad.js` (691 baris)

### Zero-Downtime Shims (18 file)
- `app/Controllers/PosisiKonsumen.php`
- `app/Controllers/Promo.php`
- `app/Controllers/Api/NupController.php`
- `app/Controllers/Web/BookingAkadReportController.php`
- `app/Controllers/Api/BookingAkadReportController.php`
- `app/Services/MkdtHistoryService.php`
- `app/Services/MkdtSettlementService.php`
- `app/Services/MkdtFinancialBreakdownService.php`
- `app/Services/MkdtFinancialReconciliationService.php`
- `app/Services/PosisiKonsumenService.php`
- `app/Services/KonsumenService.php`
- `app/Services/NupService.php`
- `app/Services/BookingAkadReportService.php`
- `app/Repositories/MkdtHistoryRepository.php`
- `app/Repositories/PosisiKonsumenRepository.php`
- `app/Repositories/KonsumenRepository.php`
- `app/Repositories/NupRepository.php`
- `app/Repositories/BookingAkadReportRepository.php`

## Key Functions
- **Controllers:**
  - `App\Controllers\Web\Mkdt\BookingAkadReportController`:
    - `index()`: Menginisialisasi dan merender tampilan utama laporan Booking & Akad tahunan (`app/Views/laporan/booking-akad.php`) dengan proteksi otorisasi `MenuAccessService::canAccessUrl()` berdasarkan role pengguna, mendeteksi proyek aktif via `ActiveProyekService`, dan menyediakan daftar tahun transaksi yang tersedia.
  - `App\Controllers\Api\Mkdt\BookingAkadReportController`:
    - `summary()`: Endpoint JSON POST `/api/laporan/booking-akad/summary` untuk menyajikan ringkasan matriks agregasi bulanan (12 bulan) untuk single year maupun komparasi 2 tahun, mencakup 6 status transaksi resmi dan konversi cohort riil berbasis `uniq_id`.
    - `detail()`: Endpoint JSON POST `/api/laporan/booking-akad/detail` untuk melayani server-side DataTables rincian transaksi kavling per bulan dan kategori status dengan payload 7 kolom terstruktur dan token CSRF terbaru.
  - `App\Controllers\Api\Mkdt\NupController`: Endpoint terstruktur untuk listing, detail, simpan, pull transaksi ke NUP, opsi kavling, dan histori pemindahan kavling (`/api/nup/*`).
  - `App\Controllers\Mkdt`: Controller legacy untuk alur simpan transaksi, set harga, detail unit, dan ekspor HTML-to-Excel (`save()`, `set_harga()`, `get_data_by_id()`, `export_xlsx()`), sekaligus **Zero-Downtime Legacy Delegator Shim** (`initController()` meneruskan request/response/logger ke `StockController` & `MkdtOperationController`):
    - `@deprecated` `list_stock()` -> mendelegasikan ke `App\Controllers\Web\Mkdt\StockController::index()`.
    - `@deprecated` `getListStock()`, `getsi()`, `saveSI()`, `batal_mkdt()`, `simpan_batal()` -> mendelegasikan ke `App\Controllers\Api\Mkdt\MkdtOperationController` (`getListStock()`, `getSi()`, `saveSi()`, `getBatal()`, `simpanBatal()`).
  - `App\Controllers\Web\Mkdt\StockController`:
    - `index()`: Thin Web Controller (`GET /list-stock`) yang memanggil `StockService::getIndexData()` dan merender `template` dengan view `kavling/list-stock` serta konteks proyek aktif untuk modal `siteplan/mkdt`.
  - `App\Controllers\Api\Mkdt\MkdtOperationController` (`extends BaseApiController`):
    - `meta()`: Mengembalikan metadata operasi MKDT (`proyek`, `active_proyek_id`, `title`, `user_group_ids`, `is_keuangan`) via `GET|POST /api/mkdt/operations/meta`.
    - `getListStock()`: Melayani server-side DataTables daftar kavling stock (`GET|POST /api/mkdt/stock/list` & legacy `/mkdt/getListStock`, `/mkdt/getliststock`) dalam dual-mode (`?format=raw` -> JSON bersih via `isRawFormat()` dengan penanda `// TODO(full-json)`; default -> payload DataTables legacy + CSRF token).
    - `getSi()` & `saveSi()`: Mengambil (`GET|POST /api/mkdt/si` & `/mkdt/getsi`) dan menyimpan (`POST /api/mkdt/si/save` & `/mkdt/saveSI`, `/mkdt/savesi`) multi-row Standing Instruction per kavling dalam dual-mode (`?format=raw` -> JSON terstruktur 422 saat validasi gagal).
    - `getBatal()` & `simpanBatal()`: Mengambil detail pembatalan (`GET|POST /api/mkdt/batal/detail` & `/mkdt/batal_mkdt`) beserta `surat_batal_access_url` dan menyimpan pembatalan booking (`POST /api/mkdt/batal/simpan` & `/mkdt/simpan_batal`) dalam dual-mode (`?format=raw` -> JSON 404/422 terstruktur).
  - `App\Controllers\Web\Mkdt\PosisiKonsumenController`:
    - `index($status = null)`: Thin Web Controller yang memanggil `PosisiKonsumenService::getIndexData()` (membawa `user_id` & daftar role Myth/Auth) dan merender `template` dengan view `kavling/list-kavling`, `kavling/list-kavling-akad`, atau `kavling/list-kavling-batal`.
  - `App\Controllers\Api\Mkdt\PosisiKonsumenController` (`extends BaseApiController`):
    - `meta()`: Mengembalikan metadata awal Posisi Konsumen (`proyek`, `active_proyek_id`, `pph`, `ppn`, `li_keu`, `conf`, `has_akses`, `list`, `status_options`, `bulan_field_options`) dalam format JSON terstruktur (`GET|POST /api/mkdt/posisi-konsumen/meta`).
    - `getDataTables($status = null)` & `getDataTablesAkad()`: Melayani server-side DataTables Posisi Konsumen Aktif/Booking dan Akad dalam dual-mode (`?format=raw` -> `RAW_JSON_READY` dengan penanda `// TODO(full-json)` pada `isRawRequest()`; default -> payload DataTables legacy).
    - `getDataTablesBatal()`: Melayani server-side DataTables Posisi Konsumen Batal dalam dual-mode (`?format=raw` -> `getRawDataTablesBatal()`; default -> `getDataTablesBatal()` legacy).
    - `exportPoskon($tipe = 'xlsx', $status = 'aktif')`: Mengekspor PDF/XLSX Posisi Konsumen via `PosisiKonsumenService::exportPoskon()` dengan respon JSON terstruktur (HTTP 422 pada mode raw atau `{status: 'error'}` pada mode legacy bila `id_proyek` tidak tersedia).
    - `getRiwayatExport($status = 'Aktif')`: Mengambil maksimal 20 riwayat ekspor terbaru per proyek dan status (`GET|POST /api/mkdt/posisi-konsumen/riwayat/(:segment)` dan `POST /riwayat/poskon/(:segment)`).
  - `App\Controllers\PosisiKonsumen`: **Zero-Downtime Legacy Delegator Shim** (`@deprecated`):
    - `initController()`: Meneruskan `RequestInterface`, `ResponseInterface`, dan `LoggerInterface` ke `Web\Mkdt\PosisiKonsumenController` serta `Api\Mkdt\PosisiKonsumenController` (0 layer violations).
    - Mendelegasikan `index()`, `getDataTables()`, `getDataTablesAkad()`, `getDataTablesBatal()`, `exportPoskon()`, `getRiwayatExport()`, serta `exportPoskonPdf()` / `exportPoskonXlsx()` (kini mengarah ke `Api\Mkdt\PosisiKonsumenController::exportPoskon()`, memperbaiki bug pemanggilan method `PrintService` legacy yang tidak ada), serta mempertahankan helper `@deprecated` (`format_tgl()`, `is_active()`).
  - `App\Controllers\Web\Mkdt\PromoController`:
    - `index()`: Merender halaman web daftar Promo (`app/Views/promo/index.php`) dengan data proyek dari `PromoService::getIndexData()`.
  - `App\Controllers\Api\Mkdt\PromoController` (`extends BaseApiController`):
    - `meta()`: Mengembalikan metadata awal Promo (daftar proyek, `active_proyek_id`, `status_options`) dalam format JSON terstruktur (`GET|POST /api/mkdt/promo/meta`).
    - `getDataTable()`: Melayani server-side DataTables Promo dalam dual-mode (`?format=raw` -> `RAW_JSON_READY` dengan penanda `// TODO(full-json)`; default -> payload DataTables 13 kolom legacy + CSRF token).
    - `getAll()`: Mengambil seluruh daftar Promo terfilter proyek/pencarian (dual-mode `?format=raw` vs legacy).
    - `getOne($id = null)`: Mengambil detail satu Promo berdasarkan ID (mendukung param `id`, `id_promo`, dan legacy `id_cluster`), mengembalikan JSON 422/404 tanpa HTML exception.
    - `add()`, `edit($id = null)`, `remove($id = null)`: Endpoint mutasi CRUD & soft-deactivate Promo dengan delegasi ke `PromoService` dan respon JSON terstruktur (422/404 saat error di mode raw).
  - `App\Controllers\Promo`: **Zero-Downtime Legacy Delegator Shim** (`@deprecated`):
    - `initController()`: Meneruskan `RequestInterface`, `ResponseInterface`, dan `LoggerInterface` ke `Web\Mkdt\PromoController` serta `Api\Mkdt\PromoController` (0 layer violations).
    - Mendelegasikan `index()`, `getDataTable()`, `getAll()`, `getOne()`, `add()`, `edit()`, `remove()` ke controller kanonik, serta mempertahankan helper `@deprecated` (`if_where()`, `is_active()`, `format_tgl()`).
- **Services:**
  - `App\Services\Mkdt\BookingAkadReportService`:
    - `CATEGORIES` & `STATUS_BADGE_MAP`: Konstanta 6 status transaksi resmi (`booking`, `wawancara`, `sp3k`, `batal`, `akad_indent`, `akad_ajb`) dan pemetaan badge visual semantik (`secondary`, `warning`, `primary`, `danger`, `info`, `success`).
    - `getAvailableYears(int $idProyek)`: Mengambil rentang daftar tahun yang tersedia dari batas tahun seluruh tanggal aktivitas transaksi (`booking_tgl`, `wawancara_tgl`, `sp3k_tgl`, `akad_indent_tgl`, `akad_tgl`, dan tanggal batal) pada proyek aktif dengan guard aman `> '1970-01-01'`.
    - `getSummary(int $idProyek, int $yearA, ?int $yearB = null)`: Agregasi matriks bulanan 12 bulan untuk 6 kategori status transaksi, menghitung rasio konversi cohort riil per bulan dan total tahunan berbasis transaksi unik (`mkdt.uniq_id`) dengan pembatasan [0.0%, 100.0%], serta mendukung komparasi 2 tahun side-by-side.
    - `getDetail(int $idProyek, array|int $requestOrYear, ?int $month = null, ?string $category = null, array $dataTable = [])`: Server-side DataTables provider dengan normalisasi parameter, pagination, searching multi-kolom, sorting (termasuk kolom ke-6 / index 5 `catatan_mkdt`), dan pemformatan data baris 7 kolom terstruktur.
    - `normalizeDataTableRequest(array $request)`: Normalisasi request DataTables dengan pemetaan sorting kolom ke-6 (index 5) ke `booking_akad.catatan_mkdt` dan alias nama mapping.
    - `resolveStatusTerakhir(array $row)`: Menentukan status transaksi terakhir mengikuti hierarki Batal > Akad AJB (`akad = 1` atau `akad_tgl`/`tgl_akad` valid) > Akad Indent (`akad_indent = 1` atau `akad_indent_tgl` valid) > SP3K (`sp3k = 1`, `sp3k_tgl` valid, atau `sp3k_no` valid) > Wawancara (`wawancara = 1` atau `wawancara_tgl` valid) > Booking.
    - `resolveTanggalStatusTerakhir(array $row, string $status)`: Menentukan tanggal milestone status terakhir sesuai status aktif dengan fallback kandidat tanggal valid (`firstValidDate()`) serta dukungan alias `akad_tgl`/`tgl_akad`.
    - `isValidDate(?string $date)`, `hasValidSp3kNo(?string $sp3kNo)`, & `firstValidDate(?string ...$candidates)`: Helper internal untuk memvalidasi tanggal (`> '1970-01-01'` dan bukan zero-date), memvalidasi nomor SP3K (`!= ''` dan `!= '-'`), serta memilih tanggal valid pertama dari kandidat fallback (`stage_tgl` -> `booking_tgl` -> `updated_at`).
    - `resolveCatatanMkdt(array $row)`: Mengambil keterangan/catatan transaksi bertingkat dari `mkdt` (`keterangan_batal` -> `keterangan` -> `keterangan_status` -> `-`).
    - `formatDate(?string $date)`: Sanitasi dan format tanggal aman (`d-m-Y` atau `-` untuk zero-date/NULL).
    - `monthLabel(int $month)`: Label nama bulan bahasa Indonesia standar (Januari - Desember).
  - `App\Services\Mkdt\MkdtHistoryService`: Pencatatan log histori kavling, consumer replacement snapshots, dan build summary status/perubahan (termasuk audit trail perubahan `akad_indent_tgl` serta pembedaan label `Akad AJB` vs `Akad Indent`).
  - `App\Services\Mkdt\MkdtSettlementService`: Sinkronisasi status pelunasan transaksi MKDT.
  - `App\Services\Mkdt\MkdtFinancialBreakdownService`: Validasi breakdown finansial kavling, target, progress pembayaran, dan jadwal angsuran.
  - `App\Services\Mkdt\MkdtFinancialReconciliationService`: Audit dan rekonsiliasi data transaksi finansial MKDT.
  - `App\Services\Mkdt\PosisiKonsumenService`:
    - `getIndexData(?string $status, int $userId, array $userGroups)`: Menyusun data view web & metadata `/api/mkdt/posisi-konsumen/meta` dengan resolusi proyek aktif via `ActiveProyekService::resolveAndGet()` (fallback `getFirstProyek()`), daftar PPh/PPN/item keuangan/config shape, checklist sub-item, dan hak akses proyek/legal/update tanggal pembangunan.
    - `userHasProjectAccess(?object $proyek, int $userId)`: Memeriksa keanggotaan `userId` pada daftar `proyek.id_users`.
    - `getRawDataTables(array $params, ?string $status = null)` & `getRawDataTablesBatal(array $params)`: Payload DataTables JSON terstruktur bebas HTML untuk mode `?format=raw` (`items`/`data`, `recordsTotal`, `recordsFiltered`, `row_payload`).
    - `exportPoskon(string $tipe, string $status, array $filters, int $userId)`: Orkestrasi ekspor PDF/XLSX via `PrintService` dan pencatatan riwayat ekspor ke tabel `riwayat_export_poskon`.
    - `getDataTable($request, $status = null)`, `getDataTablesBatal($request)`, `getRiwayatExport($id_proyek, $status)`, `insertRiwayatExport($data)`, `format_tgl($tgl)`, `is_active($id, $texts, $textf)`: Business logic DataTables legacy posisi konsumen (Booking, Akad, Batal), dukungan filter acuan waktu `bulan_field == 'indent'` (terhadap `mkdt.akad_indent_tgl`), rendering kolom `TGL AKAD` eksklusif (`AJB: <tgl>` / `Indent: <tgl>` / `-`), dan riwayat ekspor.
  - `App\Services\Mkdt\KonsumenService`: Upsert master data konsumen dan query relasi konsumen transaksi.
  - `App\Services\Mkdt\NupService`: Manajemen siklus NUP, transfer kavling, validasi nomor NUP unik per proyek, dan lampiran berkas.
  - `App\Services\Mkdt\PromoService`:
    - `getIndexData()`: Menyediakan daftar proyek, `active_proyek_id`, dan opsi status (`Aktif`/`Tidak Aktif`) untuk halaman web Promo maupun endpoint `/api/mkdt/promo/meta`.
    - `getDataTables(array $params, bool $raw = false)`: Logika bisnis server-side DataTables Promo dalam dual-mode (`$raw = true` -> array asosiatif bersih `items`/`data` tanpa HTML; `$raw = false` -> 13 kolom legacy dengan badge & tombol aksi).
    - `getAll(?int $idProyek = null, string $search = '', bool $raw = false)`: Mengambil seluruh daftar Promo terfilter proyek & pencarian dalam dual-mode.
    - `getOne(int $id)`, `create(array $input, int $actorId)`, `update(int $id, array $input, int $actorId)`, `deactivate(int $id, int $actorId)`: Validasi CI4 (`nama_promo` required max 255, `id_proyek`), normalisasi payload & tanggal, serta orkestrasi CRUD + soft-deactivate (`status = 0`).
    - `formatDate(?string $tgl)` (safe zero-date guard `0000-00-00` -> `-`), `statusBadge($id, string $activeText, string $inactiveText)`, & `cleanNumeric(mixed $value)` (sanitasi format ribuan koma/titik).
  - `App\Services\Mkdt\StockService`:
    - `getIndexData()`: Menyediakan konteks halaman `kavling/list-stock` dan proyek aktif via `ActiveProyekService::resolveAndGet()`.
    - `getListStock(array $params, bool $raw = false)`: Menyusun payload DataTables daftar kavling stock dalam dual-mode (`$raw = true` -> JSON terstruktur bebas HTML; `$raw = false` -> baris legacy lengkap dengan `terakhir_diperbarui` dan format tanggal aman `formatTanggal()` / `formatDateTime()`).
  - `App\Services\Mkdt\StandingInstructionService`:
    - `getByKavling(int $idKavling)`: Mengambil daftar Standing Instruction per kavling dan menyisipkan signed/access URL berkas via `FileAccessService::addAccessUrlsToRows($rows, 'si')`.
    - `save(int $idKavling, array $items, array $uploadedFiles, int $actorId)`: Validasi input & tanggal SI, upload berkas ke `uploads/si/{Ymd}/` via `FileAccessService::storeAs()` sebelum transaksi DB (`transStart`/`transComplete`), insert/update tabel `si`, serta pencatatan audit trail via `MkdtHistoryService::log()` (`ACTION_STANDING_INSTRUCTION`).
  - `App\Services\Mkdt\BatalBookingService`:
    - `cleanNumeric(mixed $value)`: Sanitasi angka aman dari pemisah ribuan.
    - `getDetail(int $idMkdt)`: Mengambil detail pembatalan booking, menghitung ulang total biaya (`uang_muka`, `biaya_biaya`) dan total sudah bayar dari `log_pembayaran`, serta menyertakan `surat_batal_access_url` via `FileAccessService::accessUrl('mkdt_surat_batal', $idMkdt)` (perbaikan bug `batal_mkdt`).
    - `cancelBooking(array $input, ?UploadedFile $suratBatalFile, int $actorId, array $userGroupIds)`: Validasi `id_mkdt` & `id_kavling`, menyimpan file `surat_batal` ke `uploads/batal/{Ymd}/` via `FileAccessService::storeAs()` **sebelum** update DB (`updateMkdtBatal()` dalam transaksi DB — memperbaiki bug `simpan_batal` legacy yang dulu meng-update `mkdt` sebelum file di-upload), mencatat audit trail `MkdtHistoryService::log()` (`ACTION_BATAL_BOOKING`), mengirim notifikasi `NotificationEvent::BATAL_BOOKING`, serta mengembalikan `surat_batal_access_url`.
- **Repositories:**
  - `App\Repositories\Mkdt\BookingAkadReportRepository`:
    - `getAvailableYears(int $idProyek)`: Menghitung rentang tahun yang tersedia dari minimum dan maksimum bounds seluruh tanggal aktivitas transaksi, diapit minimal tahun berjalan - 1 hingga tahun berjalan.
    - `getAvailableYearBounds(int $idProyek)`: Query batas tahun minimum dan maksimum dari seluruh tanggal milestone menggunakan kondisi kualifikasi tahap (`wawancaraConditionSql()`, `sp3kConditionSql()`, `akadIndentConditionSql()`, `akadAjbConditionSql()`) dan ekspresi tanggal efektif (`stageDateSql()` serta `batalDateSql()`), dipagari kriteria `> '1970-01-01'`.
    - `getMonthlyStatusTotals(int $idProyek, array $years)`: Multi-metric SQL query untuk menghitung agregasi bulanan berdasarkan kondisi kualifikasi dan tanggal efektif masing-masing status (`booking` via `m.booking_tgl`, `wawancara` via `wawancaraConditionSql()` + `stageDateSql('m.wawancara_tgl')`, `sp3k` via `sp3kConditionSql()` + `stageDateSql('m.sp3k_tgl')`, `batal` via `batalDateSql()`, `akad_indent` via `akadIndentConditionSql()` + `stageDateSql('m.akad_indent_tgl')`, `akad_ajb` via `akadAjbConditionSql()` + `stageDateSql('m.akad_tgl')`), serta konversi kohort transaksi booking (`total_booking_cohort` dan `booking_converted_akad` via `akadAjbConditionSql() OR akadIndentConditionSql()`), dilengkapi safe zero-date filtering `> '1970-01-01'`.
    - `getMonthlySummaryTotals(int $idProyek, array $years)`: Alias untuk `getMonthlyStatusTotals()`.
    - `getSummary(int $idProyek, int $year)`: Adapter yang mengembalikan array asosiatif 12 bulan (1..12) dari data status totals untuk kemudahan konsumsi Service.
    - `getMonthlyBookingTotals(int $idProyek, array $years)` & `getMonthlyAkadTotals(int $idProyek, array $years)`: Query agregasi bulanan untuk backward compatibility.
    - `getDetailRows(int $idProyek, int $year, int $month, string $category, array $dataTable = [])`: Query detail transaksi unit kavling dengan JOIN lengkap (`mkdt`, `kavling`, `jalan`, `cluster`, `proyek`, `konsumen`, `hargajual`, `tipe`), penentuan status terakhir & tanggal status terakhir di SQL, filter safe zero-date, pencarian global DataTables, dan pagination.
    - `compileDetailQuery(...)`: Filter kategori modal detail diselaraskan 100% dengan kondisi kualifikasi (`wawancaraConditionSql()`, `sp3kConditionSql()`, `akadIndentConditionSql()`, `akadAjbConditionSql()`) dan rentang bulan tanggal efektif (`stageDateSql()`) kategori terkait, sehingga `recordsTotal` modal detail identik dengan angka pada sel matriks.
    - `wawancaraConditionSql()`, `sp3kConditionSql()`, `akadIndentConditionSql()`, `akadAjbConditionSql()`, & `stageDateSql(string $column)`: Helper ekspresi SQL terpusat untuk kondisi kualifikasi 5 kolom status non-batal dan fallback tanggal efektif tahap (`COALESCE(DATE($column), DATE(m.booking_tgl), DATE(m.updated_at), DATE(m.created_at))` dengan guard `> '1970-01-01'`).
  - `App\Repositories\Mkdt\MkdtHistoryRepository`: Query dan persistensi tabel histori aktivitas transaksi kavling.
  - `App\Repositories\Mkdt\PosisiKonsumenRepository`:
    - `getBaseQuery($status = "Booking")`, `getQueryBatal()`, `getRiwayatExport($id_proyek, $status)`, `insertRiwayatExport($data)`: Query builder DataTables posisi konsumen aktif, akad, batal (termasuk seleksi kolom `mkdt.akad_indent_tgl`), dan log ekspor `riwayat_export_poskon`.
    - `getFirstProyek()`, `getPphList()`, `getPpnList()`, `getLiKeu()`, `getConfigShapeMap()`, `getChecklistSubItemsWithGroup()`, `getHakAksesByUser(int $userId, ?string $namaHak = null)`: Query referensi metadata halaman & API Posisi Konsumen (menggantikan query langsung di controller legacy).
    - `getRawDataTables(array $params, ?string $status = null)` & `getRawDataTablesBatal(array $params)`: Query paginated + search/filter server-side untuk endpoint DataTables mode `?format=raw`.
  - `App\Repositories\Mkdt\KonsumenRepository`: Query master konsumen terkait transaksi MKDT.
  - `App\Repositories\Mkdt\NupRepository`: Query detail NUP, validasi nomor NUP, histori perpindahan kavling, dan opsi kavling transfer.
  - `App\Repositories\Mkdt\PromoRepository`:
    - `getProyekList()`: Mengambil daftar proyek (`id_proyek, nama_proyek`) untuk dropdown/filter Promo.
    - `getDataTables(array $params)` & `getAll(?int $idProyek = null, string $search = '')`: Query builder tabel `promo` join `proyek` + `users` (`add_by`/`edit_by`) dengan filter proyek & pencarian; menghapus anomali legacy `ClusterModel` pada `getAll()`.
    - `getById(int $id)`, `insert(array $fields)`, `updateById(int $id, array $fields)`, `deactivate(int $id, int $actorId, string $timestamp)`: Query detail dan persistensi tulis eksklusif ke tabel `promo`.
  - `App\Repositories\Mkdt\StockRepository`:
    - `getStockListDataTables(array $params)`: Query DataTables server-side untuk unit kavling dengan `progres_bangunan = 100` dan status belum terjual/batal (`status_mkdt = 'Batal'` atau `NULL`), mendukung scope lokasi (`id_jalan`, `id_cluster`, `id_proyek`), filter rentang tanggal (`tanggal_pembangunan`, `tanggal_selesai_pembangunan`), dan pencarian global.
  - `App\Repositories\Mkdt\StandingInstructionRepository`:
    - `getByKavling(int $idKavling)`, `getSiListIdBySiId(int $idSi)`, `getListSiNameById(int $idListSi)`, `getKavlingMkdtId(int $idKavling)`, `insertSi(array $data)`, `updateSi(int $idSi, array $data)`: Query master `list_si` LEFT JOIN `si` dan persistensi tulis ke tabel `si`.
  - `App\Repositories\Mkdt\BatalBookingRepository`:
    - `getMkdtDetailForBatal(int $idMkdt)`, `getMkdtRawById(int $idMkdt)`, `getPembayaranLogsByMkdt(int $idMkdt)`, `getProyekIdByKavling(int $idKavling)`, `updateMkdtBatal(int $idMkdt, array $data)`: Query detail transaksi MKDT + konsumen + `log_pembayaran` dan persistensi status batal pada tabel `mkdt`.
- **Models:**
  - `App\Models\MkdtModel`: Definisi entitas dan akses data tabel `mkdt`, dengan whitelist `$allowedFields` mencakup `akad_indent_tgl` (DATE, Nullable).
  - `App\Models\KonsumenModel`: Definisi entitas master data konsumen.
  - `App\Models\MkdtKavlingHistoryModel`: Definisi entitas riwayat mutasi transaksi kavling.
- **Frontend & Assets (Views, CSS, & JS):**
  - `app/Views/laporan/booking-akad.php`: View layout laporan Booking & Akad dengan matriks 6 kolom, card KPI, canvas Chart.js, thead modal detail 7 kolom (`#booking_akad_detail_table`: No, Kavling, Konsumen, Tanggal Booking, Status Terakhir, Catatan / Keterangan, Aksi), pemuatan jQuery kondisional, eliminasi deklarasi duplikat `var loaded = []` (mencegah fatal SyntaxError dengan `scripts.js:346`), dan fallback aman `dt_proyek.id_perumahan_sikumbang`.
  - `public/assets/js/laporan-booking-akad.js`: Interaktivitas laporan analitik Booking & Akad:
    - Rendering tabel matriks 6 kolom per tahun (`Booking`, `Wawancara`, `SP3K`, `Batal`, `Akad Indent`, `Akad AJB`) dengan rasio konversi kohort riil tersemat di dalam sel `Booking` (`.booking-cell-wrapper`, `.booking-konversi-sub`) baik pada baris bulanan maupun baris Total Tahun.
    - Server-side DataTables modal detail 7 kolom terstruktur: kolom ke-5 menampilkan tanggal status terakhir + badge status semantik, kolom ke-6 menampilkan teks catatan MKDT, kolom ke-7 aksi tombol lihat detail kavling.
    - Integrasi stacked modal `openKavlingDetailModal()` dengan helper global `window.initSigappModalStacking()` di `scripts.js` (z-index 1070 di atas modal laporan 1050).
  - `public/assets/css/laporan-booking-akad.css`: Scoped styling sel booking (`.booking-cell-wrapper`, `.booking-konversi-sub`), sel status (`.booking-akad-status-cell`, `.booking-akad-status-date`), teks catatan (`.booking-akad-catatan-text`), badge status 6 kategori, dan penegakan perataan teks tegas (R4).
  - `public/assets/js/siteplan/mkdt.js`:
    - Event listener switch `#akad_indent` & `#akad` (Status Akad Indent & AJB): Logika sinkronisasi otomatis status akta PPJB / AJB pada modal form MKDT (`#modal_divisi4` / `#fm-mkdt`):
      - Jika switch `#akad` diaktifkan (`akad == 1`), dropdown `#is_ajb` otomatis diset ke `'AJB'`.
      - Jika switch `#akad` dinonaktifkan (`akad == 0`): tanggal `#akad_tgl` di-reset, dan jika `#akad_indent` masih aktif (`akad_indent == 1`) maka `#is_ajb` di-fallback ke `'PPJB'`, jika tidak maka `#is_ajb` di-reset ke `''`.
      - Jika switch `#akad_indent` diaktifkan (`akad_indent == 1`) dan `#akad` belum aktif (`akad == 0`), dropdown `#is_ajb` otomatis diset ke `'PPJB'`.
      - Jika switch `#akad_indent` dinonaktifkan (`akad_indent == 0`): tanggal `#akad_indent_tgl` di-reset, dan jika `#akad` juga nonaktif (`akad == 0`) maka `#is_ajb` di-reset ke `''`.
  - `app/Views/promo/index.php`:
    - Penanda `// TODO(follow-up)` di baris 9 untuk migrasi AJAX `/promo/*` ke `/api/mkdt/promo/*?format=raw` dan ekstraksi inline JS/CSS ke aset eksternal per standar `sigapp-ui-acuan`.
    - Perbaikan bug JS Fase 3B: (1) penghapusan re-deklarasi `const base_url` agar tidak bentrok dengan `template/menu.php`, (2) inisialisasi tunggal `$('#add-form').validate(...)` + helper `resetPromoForm()` agar alur `Add -> Edit` mengirimkan `POST /promo/edit` (bukan duplikat ke `/promo/add`), dan (3) pengisian `$("#idProyek").val(r.id_proyek).trigger('change')` pada `edit()` serta guard null-safe `promoDateFields`.
  - `app/Views/kavling/list-kavling.php`, `app/Views/kavling/list-kavling-akad.php`, `app/Views/kavling/list-kavling-batal.php`:
    - Penanda `<!-- TODO(follow-up) -->` pada baris 1 masing-masing view untuk migrasi endpoint AJAX (`/list-kavling*`, `/export/poskon*`, `/riwayat/poskon*`) ke `/api/mkdt/posisi-konsumen/*?format=raw`.
    - Perbaikan bug Fase 3B pada `app/Views/kavling/list-kavling.php` L716–721 & L1634: memperbaiki pembacaan double-nesting `$data['data'][...]` menjadi variabel langsung `$has_akses`, `$pph`, `$ppn`, `$li_keu`, `$conf`, `$proyek` dengan fallback aman serta meneruskan payload `$data` yang tepat ke partial `siteplan/partials/modal_detail`.
  - `app/Views/kavling/list-stock.php`:
    - Penanda `<!-- TODO(follow-up): migrate /mkdt/getListStock AJAX call to /api/mkdt/stock/list?format=raw -->` pada baris 1.
- **Interaksi Lintas Modul (Cross-Module Invariants & Key Handlers):**
  - **Modul Legal (`legal` via `kavling.id_legal`):**
    - Sumber kebenaran data yuridis resmi: membaca `legal.ppjb_tanggal` untuk penanggalan Akad Indent/PPJB dan `legal.ajb_tanggal` untuk penanggalan Akad AJB. Digunakan dalam proses migrasi backfill pemisahan status transaksi serta sebagai acuan integritas data transaksi MKDT.
  - **Modul Transaksi (`App\Services\TransaksiService`, `App\Controllers\Api\TransaksiController`, `App\Controllers\Transaksi`):**
    - Menyimpan pembaruan status transaksi MKDT; memvalidasi mandatory `akad_indent_tgl` saat switch Akad Indent diaktifkan (`akad_indent == 1`), serta mereset menjadi `null` saat switch nonaktif.
  - **Modul Keuangan (`App\Services\PrintService`):**
    - `exportPoskonXlsx()`: Mengekspor laporan Posisi Konsumen ke Excel dengan kolom terpisah `TGL AKAD INDENT` (Kolom I) dan `TGL AKAD` (Kolom J).
  - **Modul Siteplan (`App\Services\SiteplanVisualStatusService` & Asset UI):**
    - Mendeteksi penanda visual kavling `Akad Indent` pada siteplan jika flag `akad_indent == 1` atau tanggal `akad_indent_tgl` terisi valid.
    - Menampilkan data `Tgl Akad Indent` dan `Tgl Akad (AJB)` secara terpisah pada summary card & section status Modal Detail kavling (`siteplan-detail-modal.js`, `modal_detail.php`).

## Deprecated & Legacy Shims
- **Zero-Downtime Namespace Shims (16 File — Fase 1 Migrasi MKDT):**
  Seluruh class root/non-modular di bawah ini dipertahankan sebagai *inheritance shim* demi kompatibilitas pemanggil eksternal (*Zero-Downtime*) dan dinyatakan **DEPRECATED** untuk pemakaian baru; gunakan namespace kanonik `*\Mkdt\*`:
  - **Controllers (3 shims):**
    - `app/Controllers/Api/NupController.php` (`App\Controllers\Api\NupController`) -> **DEPRECATED**, gunakan `App\Controllers\Api\Mkdt\NupController`.
    - `app/Controllers/Web/BookingAkadReportController.php` (`App\Controllers\Web\BookingAkadReportController`) -> **DEPRECATED**, gunakan `App\Controllers\Web\Mkdt\BookingAkadReportController`.
    - `app/Controllers/Api/BookingAkadReportController.php` (`App\Controllers\Api\BookingAkadReportController`) -> **DEPRECATED**, gunakan `App\Controllers\Api\Mkdt\BookingAkadReportController`.
  - **Services (8 shims):**
    - `app/Services/MkdtHistoryService.php` -> **DEPRECATED**, gunakan `App\Services\Mkdt\MkdtHistoryService`.
    - `app/Services/MkdtSettlementService.php` -> **DEPRECATED**, gunakan `App\Services\Mkdt\MkdtSettlementService`.
    - `app/Services/MkdtFinancialBreakdownService.php` -> **DEPRECATED**, gunakan `App\Services\Mkdt\MkdtFinancialBreakdownService`.
    - `app/Services/MkdtFinancialReconciliationService.php` -> **DEPRECATED**, gunakan `App\Services\Mkdt\MkdtFinancialReconciliationService`.
    - `app/Services/PosisiKonsumenService.php` -> **DEPRECATED**, gunakan `App\Services\Mkdt\PosisiKonsumenService`.
    - `app/Services/KonsumenService.php` -> **DEPRECATED**, gunakan `App\Services\Mkdt\KonsumenService`.
    - `app/Services/NupService.php` -> **DEPRECATED**, gunakan `App\Services\Mkdt\NupService`.
    - `app/Services/BookingAkadReportService.php` -> **DEPRECATED**, gunakan `App\Services\Mkdt\BookingAkadReportService`.
  - **Repositories (5 shims):**
    - `app/Repositories/MkdtHistoryRepository.php` -> **DEPRECATED**, gunakan `App\Repositories\Mkdt\MkdtHistoryRepository`.
    - `app/Repositories/PosisiKonsumenRepository.php` -> **DEPRECATED**, gunakan `App\Repositories\Mkdt\PosisiKonsumenRepository`.
    - `app/Repositories/KonsumenRepository.php` -> **DEPRECATED**, gunakan `App\Repositories\Mkdt\KonsumenRepository`.
    - `app/Repositories/NupRepository.php` -> **DEPRECATED**, gunakan `App\Repositories\Mkdt\NupRepository`.
    - `app/Repositories/BookingAkadReportRepository.php` -> **DEPRECATED**, gunakan `App\Repositories\Mkdt\BookingAkadReportRepository`.
- **Zero-Downtime Legacy Controller Shim (Fase 2 Migrasi MKDT — Promo):**
  - `app/Controllers/Promo.php` (`App\Controllers\Promo`) -> **DEPRECATED**, dipertahankan sebagai Zero-Downtime delegator shim ke `App\Controllers\Web\Mkdt\PromoController` dan `App\Controllers\Api\Mkdt\PromoController` (0 layer violations).
  - Method `@deprecated` pada `App\Controllers\Promo`:
    - `if_where($var, $column, $condition, $query)` -> **DEPRECATED**, gunakan query builder filtering di `App\Repositories\Mkdt\PromoRepository`.
    - `is_active($id, $texts, $textf)` -> **DEPRECATED**, gunakan `App\Services\Mkdt\PromoService::statusBadge()`.
    - `format_tgl($tgl)` -> **DEPRECATED**, gunakan `App\Services\Mkdt\PromoService::formatDate()`.
- **Zero-Downtime Legacy Controller Shim (Fase 3 Migrasi MKDT — PosisiKonsumen):**
  - `app/Controllers/PosisiKonsumen.php` (`App\Controllers\PosisiKonsumen`) -> **DEPRECATED**, dipertahankan sebagai Zero-Downtime delegator shim ke `App\Controllers\Web\Mkdt\PosisiKonsumenController` dan `App\Controllers\Api\Mkdt\PosisiKonsumenController` (0 layer violations).
  - Method `@deprecated` pada `App\Controllers\PosisiKonsumen`:
    - `exportPoskonPdf($status = 'aktif')` -> **DEPRECATED**, gunakan `App\Controllers\Api\Mkdt\PosisiKonsumenController::exportPoskon('pdf', $status)`.
    - `exportPoskonXlsx($status = 'aktif')` -> **DEPRECATED**, gunakan `App\Controllers\Api\Mkdt\PosisiKonsumenController::exportPoskon('xlsx', $status)`.
    - `format_tgl($tgl)` -> **DEPRECATED**, gunakan `App\Services\Mkdt\PosisiKonsumenService::format_tgl()`.
    - `is_active($id, $texts, $textf)` -> **DEPRECATED**, gunakan `App\Services\Mkdt\PosisiKonsumenService::is_active()`.
- **Zero-Downtime Method Shims pada `App\Controllers\Mkdt` (Fase 4 Migrasi MKDT — Stock, Standing Instruction, & Batal Booking):**
  - `list_stock()` -> **DEPRECATED**, mendelegasikan ke `App\Controllers\Web\Mkdt\StockController::index()`.
  - `getListStock()` -> **DEPRECATED**, mendelegasikan ke `App\Controllers\Api\Mkdt\MkdtOperationController::getListStock()`.
  - `getsi()` -> **DEPRECATED**, mendelegasikan ke `App\Controllers\Api\Mkdt\MkdtOperationController::getSi()`.
  - `saveSI()` -> **DEPRECATED**, mendelegasikan ke `App\Controllers\Api\Mkdt\MkdtOperationController::saveSi()`.
  - `batal_mkdt()` -> **DEPRECATED**, mendelegasikan ke `App\Controllers\Api\Mkdt\MkdtOperationController::getBatal()`.
  - `simpan_batal()` -> **DEPRECATED**, mendelegasikan ke `App\Controllers\Api\Mkdt\MkdtOperationController::simpanBatal()`.
- **Legacy Routes / Shims:**
  - Route `GET /list-batal` (`Mkdt::list_batal`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
  - Route `GET /list-stock` kini mengarah ke `\App\Controllers\Web\Mkdt\StockController::index` (rute AutoRoute `/mkdt/list_stock` -> **DEPRECATED**).
  - Route Legacy Zero-Downtime Operasi MKDT pada `app/Config/Routes.php` (kini menunjuk langsung ke `App\Controllers\Api\Mkdt\MkdtOperationController` dalam default mode; penanda follow-up migrasi frontend ke `/api/mkdt/*?format=raw`):
    - `GET|POST /mkdt/getListStock`, `/mkdt/getliststock` -> **DEPRECATED (Zero-Downtime)**, gunakan `GET|POST /api/mkdt/stock/list?format=raw`.
    - `GET|POST /mkdt/getsi` -> **DEPRECATED (Zero-Downtime)**, gunakan `GET|POST /api/mkdt/si?format=raw`.
    - `POST /mkdt/saveSI`, `/mkdt/savesi` -> **DEPRECATED (Zero-Downtime)**, gunakan `POST /api/mkdt/si/save?format=raw`.
    - `GET|POST /mkdt/batal_mkdt` -> **DEPRECATED (Zero-Downtime)**, gunakan `GET|POST /api/mkdt/batal/detail?format=raw`.
    - `POST /mkdt/simpan_batal` -> **DEPRECATED (Zero-Downtime)**, gunakan `POST /api/mkdt/batal/simpan?format=raw`.
  - Route Web & Legacy AJAX Zero-Downtime Posisi Konsumen pada `app/Config/Routes.php` (sudah menunjuk controller kanonik `Web\Mkdt\PosisiKonsumenController` & `Api\Mkdt\PosisiKonsumenController` dalam default mode; penanda follow-up migrasi frontend ke `/api/mkdt/posisi-konsumen/*?format=raw`):
    - `GET /list-kavling`, `GET /list-kavling/(:segment)` -> `Web\Mkdt\PosisiKonsumenController::index`
    - `POST /list-kavling/ambil`, `POST /list-kavling/akad/ambil`, `POST /list-kavling/batal/ambil`, `POST /export/poskon/(:segment)/(:segment)`, `POST /riwayat/poskon/(:segment)` -> **DEPRECATED (Zero-Downtime AJAX)**, gunakan `/api/mkdt/posisi-konsumen/*?format=raw`.
  - Route `ANY /mkdt/get_data_by_id` (`Mkdt::get_data_by_id`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
  - Route `ANY /mkdt/simpan_batal` (`Mkdt::simpan_batal`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
  - Route `ANY /mkdt/batal_mkdt` (`Mkdt::batal_mkdt`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
  - Route `ANY /mkdt/save` (`Mkdt::save`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
  - Route `ANY /mkdt/set_harga` (`Mkdt::set_harga`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
  - Route `ANY /mkdt/getliststock` (`Mkdt::getListStock`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
  - Route `ANY /mkdt/if_where` (`Mkdt::if_where`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
  - Route `ANY /mkdt/format_tgl` (`Mkdt::format_tgl`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
  - Route `ANY /mkdt/is_active` (`Mkdt::is_active`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
  - Route `ANY /mkdt/getsi` (`Mkdt::getsi`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
  - Route `ANY /mkdt/savesi` (`Mkdt::saveSI`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
  - Route `ANY /mkdt/export_xlsx` (`Mkdt::export_xlsx`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
  - Route `ANY /posisi_konsumen/format_tgl` (`PosisiKonsumen::format_tgl`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
  - Route `ANY /posisi_konsumen/is_active` (`PosisiKonsumen::is_active`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
  - Route `ANY /posisi_konsumen/exportposkonpdf` (`PosisiKonsumen::exportPoskonPdf`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
  - Route `ANY /posisi_konsumen/exportposkonxlsx` (`PosisiKonsumen::exportPoskonXlsx`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
  - Route `ANY /promo/index` (`Promo::index`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
  - Route `ANY /promo/getdatatable` (`Promo::getDataTable`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
  - Route `ANY /promo/getall` (`Promo::getAll`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
  - Route `ANY /promo/getone` (`Promo::getOne`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
  - Route `ANY /promo/add` (`Promo::add`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
  - Route `ANY /promo/edit` (`Promo::edit`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
  - Route `ANY /promo/remove` (`Promo::remove`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
  - Route `ANY /promo/if_where` (`Promo::if_where`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
  - Route `ANY /promo/is_active` (`Promo::is_active`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
  - Route `ANY /promo/format_tgl` (`Promo::format_tgl`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
  - Seluruh rute eksplisit legacy Zero-Downtime `/promo/*` pada `app/Config/Routes.php` (`GET /promo/index`, `GET|POST /promo/{getDataTable,getdatatable,getAll,getall,getOne,getone,getOne/(:num),getone/(:num)}`, `POST /promo/{add,edit,edit/(:num),remove}`) -> **DEPRECATED (Zero-Downtime)**: Gunakan `GET /promo` (Web) dan `/api/mkdt/promo/*` (API).

## Readiness Checklist
- [x] Has Service Layer
- [x] Has Repository Layer
- [ ] Web & API Controllers Separated
- [ ] Explicit Routes Defined
- [ ] Zero Controller Layer Violations
- [x] MODULE.md Initialized

> **Layer Violations Detected:**
> - `app/Controllers/Mkdt.php`: db_connect(), $this->db->table(), new KeuanganModel(), new MkdtModel(), new KavlingModel(), new KonsumenModel(), new LogPembayaranModel()

## Dead Code Candidates
_Jalankan `php spark module:deadcode MKDT` untuk mengaudit dead code kandidat._

## Notes
- Modul MKDT bertanggung jawab atas siklus transaksi konsumen dari NUP, booking, akad, hingga pembatalan unit.
- Subfitur Laporan Perhitungan Booking & Akad (`/laporan/booking-akad`) menggunakan arsitektur modern Thin Controller -> Service -> Repository. Dokumentasi teknis komprehensif, kamus data modal detail, pemetaan status, dan formulasi matematis kohort riil tersedia di: [`docs/modules/MKDT/laporan-booking-akad.md`](laporan-booking-akad.md).
- **Penyajian Matriks 6 Kolom & Konversi Kohort di Kolom Booking:**
  Kolom mandiri *Konversi (%)* dihilangkan dari tabel matriks tahunan. Sebagai gantinya, persentase konversi kohort transaksi unik riil disematkan langsung di dalam sel `Booking` (`.booking-cell-wrapper` & `.booking-konversi-sub`) pada setiap baris bulan dan baris Total Tahun.
- **Kalkulasi Event-Based Bulanan & Keselarasan 100% Modal Detail:**
  Angka bulanan untuk 6 kolom status dihitung berdasarkan kondisi kualifikasi dan tanggal efektif aktivitas masing-masing milestone (`booking_tgl`, `stageDateSql('m.wawancara_tgl')`, `stageDateSql('m.sp3k_tgl')`, `batalDateExpr`, `stageDateSql('m.akad_indent_tgl')`, `stageDateSql('m.akad_tgl')`). Filter kategori modal detail pada `BookingAkadReportRepository::compileDetailQuery(...)` diselaraskan 100% dengan kondisi kualifikasi dan rentang bulan tanggal efektif terkait sehingga jumlah baris (`recordsTotal`) modal detail selalu identik dengan angka pada sel matriks, serta baris yang memiliki flag/nomor valid namun tanggalnya zero-date tidak terlewat.
- **Eliminasi Bug Rasio Kalender Legacy (Anomali 1400%):**
  Pada implementasi sebelumnya, rasio konversi dihitung dengan membagi kuantitas akad pada bulan $M$ dengan kuantitas booking pada bulan $M$: $\frac{\text{Akad}_M}{\text{Booking}_M} \times 100\%$. Karena proses pemenuhan berkas KPR memerlukan waktu lintas bulan (wawancara, verifikasi berkas, analisa perbankan, hingga penerbitan SP3K), transaksi akad yang selesai pada bulan $M$ hampir selalu berasal dari booking bulan-bulan sebelumnya. Akibatnya, pada bulan dengan booking baru yang sedikit (misal 1 unit booking) namun pencairan akad tinggi (misal 14 unit akad), rasio melonjak drastis hingga 1400%. Bug perbandingan kalender ini dieliminasi total dengan beralih ke metodologi pelacakan cohort riil.
- **Formula Konversi Riil Berbasis Transaksi Unik (`mkdt.uniq_id`):**
  - **Matriks Bulanan:**
    $$\text{Konversi}_M = \frac{\text{Jumlah Transaksi Unik Booking Bulan } M \text{ yang Telah Akad}}{\text{Total Transaksi Unik Booking Bulan } M} \times 100\%$$
    Denominator adalah `COUNT(DISTINCT m.uniq_id)` dengan tanggal booking pada bulan $M$. Numerator adalah `COUNT(DISTINCT m.uniq_id)` dari kohort booking tersebut yang telah mencapai status Akad Indent (`akad_indent = 1` / PPJB) atau Akad AJB (`akad = 1` / AJB), dan tidak berstatus Batal. Jika Denominator $= 0$, nilai konversi adalah $0.0\%$.
  - **Total Tertimbang Tahunan (Annual Weighted Total):**
    $$\text{Konversi}_{\text{Tahunan}} = \frac{\sum_{M=1}^{12} \text{Booking Terkonversi Akad}_M}{\sum_{M=1}^{12} \text{Total Booking Cohort}_M} \times 100\%$$
    Baris total tahunan dihitung dari total akumulasi riil numerator sepanjang tahun dibagi akumulasi riil denominator sepanjang tahun (weighted cohort sum), bukan rata-rata aritmatika dari persentase bulanan.
  - **Batas Nilai (Bounds Enforcement):**
    Nilai persentase dipagari secara matematis pada interval tertutup $[0.0\%, 100.0\%]$ via `max(0.0, min(100.0, (float) $konversi))` sehingga anomali rasio di atas 100% mustahil terjadi.
- **Integrasi Tombol Aksi Modal Kavling Siteplan & Stacked Modal (`window.initSigappModalStacking`):**
  - Tombol aksi pada kolom ke-7 modal detail memicu fungsi `detail_kavling(sh, idKavling)` untuk membuka modal siteplan kavling (`#modal_detail`) secara bertumpuk (*stacked*) via helper global `window.initSigappModalStacking()` di `scripts.js` (z-index 1070 di atas modal laporan 1050).
  - Untuk menjamin stabilitas tanpa runtime error (`ReferenceError` / `TypeError`), template `booking-akad.php` menyertakan stylesheet `<style><?= view('siteplan/partials/modal_detail_styles') ?></style>`, pemuatan vendor jQuery kondisional, eliminasi deklarasi duplikat `var loaded = []` (menghindari fatal SyntaxError dengan `let loaded = []` di `scripts.js:346`), dan deklarasi variabel global proteksi: `window.base_url`, `window.loaded = window.loaded || []`, `window.editdtt`, serta `dt_proyek` (dengan fallback `id_perumahan_sikumbang`).
- **Cross-Module Callers / Consumers & Integration:**
  - Modul Legal: integrasi pembacaan data akta yuridis resmi (`legal.ppjb_tanggal` dan `legal.ajb_tanggal`) via relasi `kavling.id_legal` untuk sinkronisasi tanggal akta PPJB & AJB serta acuan backfill data historis.
  - Modul Keuangan: sinkronisasi status pembayaran dan pelunasan kavling via `MkdtSettlementService`, serta ekspor Excel Posisi Konsumen dengan kolom `TGL AKAD INDENT` dan `TGL AKAD` via `PrintService::exportPoskonXlsx()`.
  - Modul Transaksi: manipulasi status unit kavling, validasi dan persistensi `akad_indent_tgl` & `akad_tgl` via `TransaksiService`, serta log pergantian konsumen via `MkdtHistoryService`.
  - Modul Siteplan: visual status marker `Akad Indent` pada peta interaktif via `SiteplanVisualStatusService`, serta integrasi summary card dan status Akad Indent pada modal detail kavling (`siteplan-detail-modal.js`, `app/Views/siteplan/partials/modal_detail.php`).
  - Frontend Laporan: mengonsumsi REST API `/api/laporan/booking-akad/summary` dan `/api/laporan/booking-akad/detail`.
- **Follow-up Plan (Migrasi Endpoint Frontend `/api/mkdt/*` Full JSON):**
  - Sesuai keputusan arsitektur yang disetujui pada Fase 0 migrasi MKDT (#3 & #4), seluruh endpoint `/api/mkdt/*` mendukung dual-mode (`?format=raw`) beserta flag penanda follow-up Full JSON (`// TODO(full-json)` pada `App\Controllers\Api\Mkdt\PromoController::isRawRequest()`, `App\Controllers\Api\Mkdt\PosisiKonsumenController::isRawRequest()`, dan `App\Controllers\Api\Mkdt\MkdtOperationController`), sementara aset frontend JS/View eksisting (`public/assets/js/siteplan/mkdt.js`, `app/Views/siteplan/mkdt.php`, `app/Views/kavling/*.php`, `app/Views/promo/index.php`) **tidak dialihkan** ke `/api/mkdt/*` selama migrasi ini dan ditandai sebagai pekerjaan follow-up pasca-migrasi MKDT.
  - Pada `app/Views/promo/index.php:9`, telah ditambahkan penanda `// TODO(follow-up): migrate /promo/* AJAX calls and inline JS/CSS to /api/mkdt/promo/*?format=raw + external assets per sigapp-ui-acuan`.
  - Pada `app/Views/kavling/list-kavling.php:1`, `app/Views/kavling/list-kavling-akad.php:1`, dan `app/Views/kavling/list-kavling-batal.php:1`, telah ditambahkan penanda `<!-- TODO(follow-up): migrate /list-kavling*, /export/poskon*, /riwayat/poskon* AJAX calls to /api/mkdt/posisi-konsumen/*?format=raw -->`.
  - Pada `app/Views/kavling/list-stock.php:1`, telah ditambahkan penanda `<!-- TODO(follow-up): migrate /mkdt/getListStock AJAX call to /api/mkdt/stock/list?format=raw -->`.
  - Kontrak lengkap API Promo dual-mode pada `app/Config/Routes.php` (`api/mkdt/promo`): `GET|POST meta`, `GET|POST {datatable,getdatatable,getDataTable}`, `GET|POST {all,getall,getAll}`, `GET|POST {getone,getOne}` & `/{getone,getOne}/(:num)`, `POST add`, `POST edit` & `/edit/(:num)`, `POST remove` & `/remove/(:num)`.
  - Kontrak lengkap API Posisi Konsumen dual-mode pada `app/Config/Routes.php` (`api/mkdt/posisi-konsumen`): `GET|POST meta`, `GET|POST datatable`, `GET|POST datatable/(:segment)`, `GET|POST datatable-akad`, `GET|POST datatable-batal`, `POST export/(:segment)/(:segment)`, `GET|POST riwayat/(:segment)`.
  - Kontrak lengkap API Operasi MKDT dual-mode pada `app/Config/Routes.php` (`api/mkdt`): `GET|POST operations/meta`, `GET|POST stock/list`, `GET|POST si`, `POST si/save`, `GET|POST batal/detail`, `POST batal/simpan` (`MkdtOperationController`).
  - Perbaikan Fase 4 pada alur Batal Booking: (1) `BatalBookingService::cancelBooking()` kini mengunggah/menyimpan `surat_batal` via `FileAccessService::storeAs()` **sebelum** melakukan `updateMkdtBatal()` di database (mengatasi bug urutan legacy `Mkdt::simpan_batal` yang menyebabkan `surat_batal` gagal tersimpan), dan (2) `BatalBookingService::getDetail()` (`batal_mkdt` / `/api/mkdt/batal/detail`) serta hasil `cancelBooking()` kini menyertakan `surat_batal_access_url` via `FileAccessService::accessUrl('mkdt_surat_batal', $idMkdt)`.

## Change Log
- **`2026-11-13` [`REFACTOR & BUGFIX`] — Fase 4 Migrasi MKDT (Dekomposisi Stock, Standing Instruction, & Batal Booking; Dual-Mode `/api/mkdt/*`; Perbaikan `surat_batal` Upload-Before-Update & `surat_batal_access_url`):**
  - **Routes & Public Contracts:**
    - Web: `GET /list-stock` -> `\App\Controllers\Web\Mkdt\StockController::index`.
    - API Dual-Mode (`/api/mkdt/*`, `?format=raw` = Full JSON via `isRawFormat()` dengan penanda `// TODO(full-json)` pada `MkdtOperationController`):
      - `GET|POST /api/mkdt/operations/meta` -> `MkdtOperationController::meta`
      - `GET|POST /api/mkdt/stock/list` -> `MkdtOperationController::getListStock`
      - `GET|POST /api/mkdt/si` -> `MkdtOperationController::getSi`
      - `POST /api/mkdt/si/save` -> `MkdtOperationController::saveSi`
      - `GET|POST /api/mkdt/batal/detail` -> `MkdtOperationController::getBatal`
      - `POST /api/mkdt/batal/simpan` -> `MkdtOperationController::simpanBatal`
    - Legacy Zero-Downtime Routes (`/mkdt/{getListStock,getliststock,getsi,saveSI,savesi,batal_mkdt,simpan_batal}`) -> langsung ke `App\Controllers\Api\Mkdt\MkdtOperationController`.
  - **Controller / Service / Repo / Model:**
    - `app/Repositories/Mkdt/StockRepository.php`, `StandingInstructionRepository.php`, `BatalBookingRepository.php`: Ekstraksi query kavling stock, `list_si`/`si`, dan `mkdt`/`log_pembayaran` pembatalan booking.
    - `app/Services/Mkdt/StockService.php`, `StandingInstructionService.php`, `BatalBookingService.php`: Business logic stock dual-mode, transaksi SI + `FileAccessService` + `MkdtHistoryService::log()`, serta pembatalan booking (perbaikan bug upload `surat_batal` via `FileAccessService::storeAs()` **sebelum** `updateMkdtBatal()`, penambahan `surat_batal_access_url` pada `batal_mkdt`/`getDetail()` dan hasil `cancelBooking()`, serta notifikasi `NotificationEvent::BATAL_BOOKING`).
    - `app/Controllers/Web/Mkdt/StockController.php` & `app/Controllers/Api/Mkdt/MkdtOperationController.php`: Thin Web & API Controllers.
    - `app/Controllers/Mkdt.php`: Menambahkan `initController()` forwarding serta shim `@deprecated` untuk `list_stock()`, `getListStock()`, `getsi()`, `saveSI()`, `batal_mkdt()`, `simpan_batal()`.
    - `app/Config/Routes.php` & `app/Libraries/ModuleManager/ModuleCatalog.php`: Registrasi rute Fase 4 dan penambahan `si`, `list_si` ke `owner_tables`.
  - **View / CSS / JS:**
    - `app/Views/kavling/list-stock.php`: Penambahan penanda `<!-- TODO(follow-up): migrate /mkdt/getListStock AJAX call to /api/mkdt/stock/list?format=raw -->` di baris 1.
  - **Deprecations:**
    - Method `Mkdt::{list_stock,getListStock,getsi,saveSI,batal_mkdt,simpan_batal}` dan rute legacy `/mkdt/*` terkait berstatus `@deprecated` Zero-Downtime.
  - **Testing & Verification:**
    - Diverifikasi `PASS` oleh `sigapp-qa`: Playwright E2E (`/list-stock`, `#modals-si`, `#modal-batal`, `/api/mkdt/*?format=raw`), PHPUnit (`tests/unit/MkdtOperationServicesTest.php` 6 tests / 55 assertions; gabungan MKDT 19 tests / 86 assertions), `tests/bin/run_booking_akad_suite.php` (39/39 PASS), DB transaction rollback check (`FILE_STORED_BEFORE_UPDATE: YES`), dan `php spark module:status` (MKDT `80%` `MAPPED`).
- **`2026-11-13` [`REFACTOR & BUGFIX`] — Fase 3 Migrasi MKDT (Pemisahan Web & API Controller PosisiKonsumen, Explicit Routes `/api/mkdt/posisi-konsumen/*`, Zero-Downtime Shim, & Bugfix Double-Nesting `list-kavling.php`):**
  - **Routes & Public Contracts:**
    - Web: `GET /list-kavling`, `GET /list-kavling/(:segment)` -> `\App\Controllers\Web\Mkdt\PosisiKonsumenController::index`.
    - Legacy Zero-Downtime AJAX Routes (langsung ke API Controller default mode):
      - `POST /list-kavling/ambil` -> `\App\Controllers\Api\Mkdt\PosisiKonsumenController::getDataTables`
      - `POST /list-kavling/akad/ambil` -> `\App\Controllers\Api\Mkdt\PosisiKonsumenController::getDataTablesAkad`
      - `POST /list-kavling/batal/ambil` -> `\App\Controllers\Api\Mkdt\PosisiKonsumenController::getDataTablesBatal`
      - `POST /export/poskon/(:segment)/(:segment)` -> `\App\Controllers\Api\Mkdt\PosisiKonsumenController::exportPoskon/$1/$2`
      - `POST /riwayat/poskon/(:segment)` -> `\App\Controllers\Api\Mkdt\PosisiKonsumenController::getRiwayatExport/$1`
    - API Dual-Mode (`/api/mkdt/posisi-konsumen/*`, `?format=raw` = `RAW_JSON_READY` dengan penanda `// TODO(full-json)` pada `PosisiKonsumenController::isRawRequest()`):
      - `GET|POST /api/mkdt/posisi-konsumen/meta`
      - `GET|POST /api/mkdt/posisi-konsumen/datatable`, `/datatable/(:segment)`, `/datatable-akad`, `/datatable-batal`
      - `POST /api/mkdt/posisi-konsumen/export/(:segment)/(:segment)`
      - `GET|POST /api/mkdt/posisi-konsumen/riwayat/(:segment)`
  - **Controller / Service / Repo / Model:**
    - `app/Repositories/Mkdt/PosisiKonsumenRepository.php`: Tambah `getFirstProyek`, `getPphList`, `getPpnList`, `getLiKeu`, `getConfigShapeMap`, `getChecklistSubItemsWithGroup`, `getHakAksesByUser`, `getRawDataTables`, `getRawDataTablesBatal`.
    - `app/Services/Mkdt/PosisiKonsumenService.php`: Tambah `getIndexData`, `userHasProjectAccess`, `getRawDataTables`, `getRawDataTablesBatal`, `exportPoskon` (pakai `ActiveProyekService::resolveAndGet()` dengan fallback `getFirstProyek()`).
    - `app/Controllers/Web/Mkdt/PosisiKonsumenController.php`: Thin Web Controller (`index`).
    - `app/Controllers/Api/Mkdt/PosisiKonsumenController.php`: Thin API Controller (`extends BaseApiController`, dual-mode `?format=raw`, `meta`, DataTables, `exportPoskon`, `getRiwayatExport`).
    - `app/Controllers/PosisiKonsumen.php`: Zero-Downtime delegator shim (`initController()`, 0 layer violations; memperbaiki `exportPoskonPdf`/`exportPoskonXlsx` legacy yang sebelumnya memanggil method `PrintService` yang tidak ada).
    - `app/Config/Routes.php` & `app/Libraries/ModuleManager/ModuleCatalog.php`: Registrasi rute Web/API/Legacy PosisiKonsumen, penambahan `riwayat_export_poskon` ke `owner_tables`, serta pembaruan `controllers` dan `shims`.
  - **View / CSS / JS:**
    - `app/Views/kavling/list-kavling.php`, `list-kavling-akad.php`, `list-kavling-batal.php`: Penanda `<!-- TODO(follow-up) -->` pada L1 untuk migrasi endpoint AJAX & asset ke `/api/mkdt/posisi-konsumen/*?format=raw`.
    - `app/Views/kavling/list-kavling.php` L716–721 & L1634 (Fase 3B): Memperbaiki bug pembacaan double-nesting `$data['data'][...]` menjadi variabel langsung `$has_akses`, `$pph`, `$ppn`, `$li_keu`, `$conf`, `$proyek` dengan fallback aman dan passing data yang tepat ke partial `siteplan/partials/modal_detail`.
  - **Deprecations:**
    - `App\Controllers\PosisiKonsumen` (beserta `exportPoskonPdf()`, `exportPoskonXlsx()`, `format_tgl()`, `is_active()`) serta AutoRoute `/posisi_konsumen/*` dan endpoint AJAX legacy `/list-kavling/*/ambil`, `/export/poskon/*`, `/riwayat/poskon/*` berstatus `@deprecated` Zero-Downtime.
  - **Testing & Verification:**
    - Diverifikasi `PASS` oleh `sigapp-qa` (`2026-11-13 12:22`): `php -l` bersih, `php spark module:status` (`MKDT: MAPPED 80%`, 0 layer violations di `PosisiKonsumen.php`), `php spark module:deadcode MKDT`, PHPUnit (`10/10` pass), dan Playwright E2E + verifikasi dual-mode `?format=raw` pasca-perbaikan bug double-nesting `list-kavling.php` (`PASS`, 0 console error).
- **`2026-11-13` [`REFACTOR & BUGFIX`] — Fase 2 Migrasi MKDT (Dekomposisi Subdomain Promo: Web & API Controller, Service, Repository, Zero-Downtime Shim, & Bugfix UI Promo):**
  - **Routes & Public Contracts:**
    - Web: `GET /promo` -> `\App\Controllers\Web\Mkdt\PromoController::index`.
    - API Dual-Mode (`?format=raw` = Full JSON `RAW_JSON_READY` dengan penanda `// TODO(full-json)` pada `PromoController::isRawRequest()`):
      - `GET|POST /api/mkdt/promo/meta`
      - `GET|POST /api/mkdt/promo/{datatable,getdatatable,getDataTable}`
      - `GET|POST /api/mkdt/promo/{all,getall,getAll}`
      - `GET|POST /api/mkdt/promo/{getone,getOne}` & `/{getone,getOne}/(:num)`
      - `POST /api/mkdt/promo/add`, `POST /api/mkdt/promo/edit` & `/edit/(:num)`, `POST /api/mkdt/promo/remove` & `/remove/(:num)`
    - Legacy Zero-Downtime Routes: `GET /promo/index`, `GET|POST /promo/{getDataTable,getdatatable,getAll,getall,getOne,getone,getOne/(:num),getone/(:num)}`, `POST /promo/{add,edit,edit/(:num),remove}` -> `App\Controllers\Promo::*`.
  - **Controller / Service / Repo / Model:**
    - `app/Repositories/Mkdt/PromoRepository.php`: Query builder tabel `promo` join `proyek` + `users` (`getProyekList`, `getDataTables`, `getAll`, `getById`, `insert`, `updateById`, `deactivate`); menghapus anomali legacy `ClusterModel` pada `getAll()`.
    - `app/Services/Mkdt/PromoService.php`: Business logic Promo, validasi CI4, `cleanNumeric()`, `formatDate()` safe zero-date, `statusBadge()`, dan dual-mode DataTables/`getAll`.
    - `app/Controllers/Web/Mkdt/PromoController.php`: Thin Web Controller (`index()`).
    - `app/Controllers/Api/Mkdt/PromoController.php`: Thin API Controller (`extends BaseApiController`, `meta`, `getDataTable`, `getAll`, `getOne`, `add`, `edit`, `remove`), mengembalikan JSON 404/422 tanpa HTML exception.
    - `app/Controllers/Promo.php`: Zero-Downtime legacy delegator shim (`initController()` meneruskan request/response/logger, 0 layer violations).
    - `app/Config/Routes.php` & `app/Libraries/ModuleManager/ModuleCatalog.php`: Registrasi rute Promo Web/API/Legacy dan pembaruan inventori file MKDT (termasuk `App\Controllers\Promo` pada `'shims'`).
  - **View / CSS / JS:**
    - `app/Views/promo/index.php`: Penanda `// TODO(follow-up)` di L9 untuk migrasi AJAX `/promo/*` ke `/api/mkdt/promo/*?format=raw` + ekstraksi inline JS/CSS ke aset eksternal, serta perbaikan Fase 3B untuk 3 bug JS eksisting: (1) hapus re-deklarasi `const base_url` (bentrok `template/menu.php`), (2) inisialisasi tunggal `$('#add-form').validate(...)` + `resetPromoForm()` agar `Add -> Edit` mengirim `POST /promo/edit` (bukan duplikat ke `/promo/add`), dan (3) isi `$("#idProyek").val(r.id_proyek).trigger('change')` pada `edit()` + guard null-safe `promoDateFields`.
  - **Deprecations:**
    - `App\Controllers\Promo` (dan method `if_where()`, `is_active()`, `format_tgl()`) serta rute `/promo/*` (selain `GET /promo`) berstatus `@deprecated` Zero-Downtime shim.
  - **Testing & Verification:**
    - Diverifikasi `PASS` oleh `sigapp-qa` (`2026-11-13 12:01`): `php -l` bersih, `php spark module:status` (`MKDT: MAPPED 80%`, 0 layer violations di `Promo.php`), `php spark module:deadcode MKDT`, PHPUnit (`10/10` pass), dan Playwright E2E ulang pasca-perbaikan 3 bug JS di `/promo` (`PASS`, 0 console error).
- **`2026-11-13` [`REFACTOR`] — Fase 0 & 1 Migrasi MKDT (Baseline, Pembersihan Dead Code, Pemindahan Modern Layer ke Subfolder `Mkdt/`, & Bugfix `/list-stock`):**
  - **Routes & Public Contracts:**
    - `app/Config/Routes.php`: Memperbarui namespace eksplisit menjadi `\App\Controllers\Web\Mkdt\BookingAkadReportController::index` (`GET /laporan/booking-akad`), grup `api/laporan/booking-akad` ke namespace `App\Controllers\Api\Mkdt`, dan grup `api/nup` ke namespace `App\Controllers\Api\Mkdt`.
    - Kontrak JSON dan public PHP service antar-modul tetap identik (Zero-Downtime).
  - **Controller / Service / Repo / Model:**
    - `app/Controllers/Mkdt.php`: Menghapus dead commented code pada L801–1515 (file menyusut menjadi 1257 baris) serta bugfix Fase 3B pada `Mkdt::list_stock()` mengisi `$data['data']['proyek']` via `(new \App\Services\Proyek\ActiveProyekService())->resolveAndGet()` dengan fallback objek aman (`id_proyek => null, nama_proyek => ''`).
    - Memindahkan 3 modern controllers ke subfolder `Mkdt/`: `App\Controllers\Api\Mkdt\NupController`, `App\Controllers\Web\Mkdt\BookingAkadReportController`, dan `App\Controllers\Api\Mkdt\BookingAkadReportController`.
    - Memindahkan 8 services ke subfolder `Mkdt/`: `App\Services\Mkdt\{MkdtHistoryService, MkdtSettlementService, MkdtFinancialBreakdownService, MkdtFinancialReconciliationService, PosisiKonsumenService, KonsumenService, NupService, BookingAkadReportService}`.
    - Memindahkan 5 repositories ke subfolder `Mkdt/`: `App\Repositories\Mkdt\{MkdtHistoryRepository, PosisiKonsumenRepository, KonsumenRepository, NupRepository, BookingAkadReportRepository}`.
    - `app/Libraries/ModuleManager/ModuleCatalog.php`: Memperbarui inventori MKDT termasuk mendaftarkan 16 file Zero-Downtime Shims pada kunci `'shims'`.
  - **View / CSS / JS:**
    - `app/Views/siteplan/mkdt.php` (L1693, 1709, 1778, 1797 — Bugfix Fase 3B): Menambahkan guard null-safe + `esc($data['proyek']->nama_proyek ?? ...)` pada modal Turun Pembangunan dan Set Harga.
    - Belum ada pengalihan frontend JS/View ke `/api/mkdt/*` (ditandai sebagai follow-up pasca-migrasi).
  - **Deprecations:**
    - Penambahan 16 Zero-Downtime namespace shims pada `App\Controllers\{Api,Web}\*`, `App\Services\*`, dan `App\Repositories\*` yang mewarisi kelas kanonik `*\Mkdt\*`.
    - Keputusan Fase 0 (Opsi A): `Mkdt::save`, `Mkdt::set_harga`, `Mkdt::get_data_by_id`, `Mkdt::export_xlsx` akan di-deprecate in place + log warning pada Fase 5.
  - **Testing & Verification:**
    - Diverifikasi `PASS` oleh `sigapp-qa` (2026-11-13 11:40): `php spark module:status` (`MKDT: MAPPED 80%`), `php spark module:deadcode MKDT`, `php -l` bersih, `tests/bin/run_booking_akad_suite.php` (39/39 pass), PHPUnit (82 tests / 882 assertions pass), dan Playwright E2E (termasuk verifikasi `/list-stock` pasca-perbaikan HTTP 200 OK tanpa error).
- **`2026-11-05` [`BUGFIX`] — Penyelarasan Perhitungan 5 Kolom Status (Booking, Wawancara, SP3K, Akad Indent, Akad AJB) pada Laporan Booking & Akad:**
  - **Routes & Public Contracts:**
    - Tidak ada rute baru; kontrak endpoint `/laporan/booking-akad`, `/api/laporan/booking-akad/summary`, dan `/api/laporan/booking-akad/detail` tetap.
  - **Controller / Service / Repo / Model:**
    - `App\Repositories\BookingAkadReportRepository`:
      - Penyelarasan perhitungan 5 kolom status pada `getAvailableYearBounds()`, `getMonthlyStatusTotals()`, dan `compileDetailQuery()` menggunakan helper kondisi kualifikasi dan fallback tanggal efektif (`stageDateSql()`):
        1. `booking`: dihitung dari `m.booking_tgl` (`m.booking_tgl IS NOT NULL AND m.booking_tgl > '1970-01-01'`), dan sub-metrik konversi `booking_converted_akad` menggunakan `akadAjbConditionSql() OR akadIndentConditionSql()`.
        2. `wawancara`: dihitung dari `wawancaraConditionSql()` (`COALESCE(m.wawancara, 0) = 1 OR (m.wawancara_tgl IS NOT NULL AND m.wawancara_tgl > '1970-01-01')`) dengan `stageDateSql('m.wawancara_tgl')`.
        3. `sp3k`: dihitung dari `sp3kConditionSql()` (`COALESCE(m.sp3k, 0) = 1 OR (m.sp3k_tgl IS NOT NULL AND m.sp3k_tgl > '1970-01-01') OR (m.sp3k_no IS NOT NULL AND TRIM(m.sp3k_no) != '' AND TRIM(m.sp3k_no) != '-')`) dengan `stageDateSql('m.sp3k_tgl')`.
        4. `akad_indent`: dihitung dari `akadIndentConditionSql()` (`COALESCE(m.akad_indent, 0) = 1 OR (m.akad_indent_tgl IS NOT NULL AND m.akad_indent_tgl > '1970-01-01')`) dengan `stageDateSql('m.akad_indent_tgl')`.
        5. `akad_ajb`: dihitung dari `akadAjbConditionSql()` (`COALESCE(m.akad, 0) = 1 OR (m.akad_tgl IS NOT NULL AND m.akad_tgl > '1970-01-01')`) dengan `stageDateSql('m.akad_tgl')`.
    - `App\Services\BookingAkadReportService`:
      - Penyelarasan `resolveStatusTerakhir()` dan `resolveTanggalStatusTerakhir()` (termasuk penambahan helper `isValidDate()`, `hasValidSp3kNo()`, `firstValidDate()`, serta dukungan alias `tgl_akad`/`akad_tgl`).
  - **View / CSS / JS:** Tidak ada perubahan.
  - **Testing & Verification:**
    - `tests/database/BookingAkadReportLiveDbTest.php` (9 tests, termasuk `TC-R09` verifikasi kesesuaian total 5 kolom dengan kondisi database Proyek 13).
    - `tests/unit/BookingAkadReportServiceOverhaulTest.php` (memperluas `TC-B07` untuk aturan berbasis tanggal dan nomor SP3K).
  - **Deprecations:** Tidak ada fungsi atau rute yang di-deprecate.
- **`2026-11-04` [`UI_REPORT & BUGFIX`] — Pembaruan Laporan Booking & Akad: Matriks 6 Kolom, Konversi di Kolom Booking, Event-Based Monthly Totals, Modal Detail 7 Kolom (Status Terakhir & Catatan), serta Perbaikan Stacked Modal Detail:**
  - **Routes & Public Contracts:**
    - Kontrak payload API `/api/laporan/booking-akad/summary` dan `/api/laporan/booking-akad/detail` tetap konsisten.
    - Sorting server-side DataTables mendukung kolom ke-6 (index 5) yang dipetakan ke `booking_akad.catatan_mkdt`.
  - **Controller / Service / Repo / Model:**
    - `App\Repositories\BookingAkadReportRepository`:
      - `getAvailableYears(int $idProyek)` & `getAvailableYearBounds(int $idProyek)`: Memperluas pemindaian batas tahun mencakup seluruh tanggal milestone aktivitas (`booking_tgl`, `wawancara_tgl`, `sp3k_tgl`, `akad_indent_tgl`, `akad_tgl`, dan tanggal batal efektif via `batalDateSql()`) dengan filter aman `> '1970-01-01'`.
      - `getMonthlyStatusTotals(int $idProyek, array $years)`: Menghitung agregasi event-based bulanan per kolom sesuai tanggal terjadinya masing-masing status pada bulan bersangkutan (`booking`, `wawancara`, `sp3k`, `batal`, `akad_indent`, `akad_ajb`) ditambah metrik konversi kohort booking (`total_booking_cohort`, `booking_converted_akad`).
      - `compileDetailQuery(...)`: Menyelaraskan filter kategori modal detail dengan rentang bulan tanggal aktivitas kategori terkait, sehingga `recordsTotal` modal detail identik 100% dengan angka pada sel matriks.
    - `App\Services\BookingAkadReportService`:
      - `normalizeDataTableRequest(array $request)`: Memperbarui pemetaan index dan nama kolom sorting DataTables (kolom index 5 memetakan ke `booking_akad.catatan_mkdt`).
  - **View / CSS / JS:**
    - `app/Views/laporan/booking-akad.php`:
      - Restrukturisasi thead modal detail `#booking_akad_detail_table`: kolom ke-5 `Status Terakhir` (`text-left`), kolom ke-6 `Catatan / Keterangan` (`text-left`).
      - Menjadikan pemuatan vendor jQuery kondisional (`if (typeof window.jQuery === 'undefined')`).
      - Menghapus deklarasi global `var loaded = [];` yang bentrok dengan `let loaded = [];` di `scripts.js:346` (memperbaiki fatal SyntaxError).
      - Menambahkan fallback objek `dt_proyek.id_perumahan_sikumbang`.
    - `public/assets/js/laporan-booking-akad.js`:
      - Menghapus kolom mandiri `Konversi (%)` pada tabel matriks (menjadi tepat 6 kolom per tahun).
      - Menggabungkan persentase konversi kohort ke dalam kolom `Booking` (pada baris bulanan dan Total Tahun via `.booking-cell-wrapper` & `.booking-konversi-sub`).
      - Memperbarui DataTables modal detail: kolom 5 menampilkan tanggal status terakhir + badge status; kolom 6 menampilkan teks catatan MKDT.
      - Integrasi stacked modal `openKavlingDetailModal()` dengan helper global `window.initSigappModalStacking()` di `scripts.js` (z-index 1070 di atas modal laporan 1050).
    - `public/assets/css/laporan-booking-akad.css`: Styling scoped untuk sel booking (`.booking-cell-wrapper`, `.booking-konversi-sub`), sel status (`.booking-akad-status-cell`, `.booking-akad-status-date`), dan teks catatan (`.booking-akad-catatan-text`).
  - **Testing & Verification:**
    - `tests/database/BookingAkadReportLiveDbTest.php` (8 tests, 556 assertions).
    - `tests/unit/BookingAkadReportServiceTest.php` (17 tests, 89 assertions).
    - `tests/bin/run_booking_akad_suite.php` (38 tests, 100% pass).
  - **Deprecations:** Tidak ada fungsi atau rute yang di-deprecate.
- **`2026-11-04` [`FEATURE / UI_REPORT`] — Overhaul Laporan Perhitungan Booking & Akad (6 Status Transaksi, Konversi Real Uniq ID, Modal Detail 7 Kolom & Siteplan Integration, Perataan Teks):**
  - **Routes & Public Contracts:**
    - Mengonfirmasi endpoint terstruktur: `GET /laporan/booking-akad` (Web View), `POST /api/laporan/booking-akad/summary` (JSON Matriks), dan `POST /api/laporan/booking-akad/detail` (JSON DataTables).
    - Memperbarui payload response JSON `summary` dengan 6 kategori status transaksi (`booking`, `wawancara`, `sp3k`, `batal`, `akad_indent`, `akad_ajb`) dan metrik konversi cohort riil `konversi` pada level bulanan dan baris total tahunan.
    - Menetapkan skema data terstruktur 7 kolom pada payload response JSON `detail`: `id_mkdt`, `id_kavling`, objek `kavling` (`nama_jalan`, `no_kavling`, `sales`), objek `konsumen` (`nama_konsumen`, `tipe_rumah`, `is_subsidi`), `tanggal_booking`, `status_terakhir`, `status_terakhir_badge`, `tanggal_status_terakhir`, `catatan_mkdt`, dengan tetap menyertakan kompatibilitas field flat (`alamat_kavling`, `sales`, `nama_konsumen`, `tipe_rumah`, `nominal`).
  - **Controller / Service / Repo / Model:**
    - `App\Controllers\Web\BookingAkadReportController`: Pemisahan Web Controller dengan verifikasi hak akses URL via `MenuAccessService::canAccessUrl()` dan penyediaan data tahun transaksi aktif.
    - `App\Controllers\Api\BookingAkadReportController`: Pemisahan API Controller dengan sanitasi request komparasi 2 tahun, output respon JSON terstruktur, dan penanganan exception `InvalidArgumentException`.
    - `App\Services\BookingAkadReportService`: Implementasi 6 status transaksi resmi (`CATEGORIES`), perhitungan rasio konversi cohort riil per bulan dan total tertimbang tahunan berbasis `mkdt.uniq_id`, pembatasan nilai $[0.0\%, 100.0\%]$ untuk eliminasi anomali 1400%, penentuan hierarki status terakhir (`Batal` > `Akad AJB` > `Akad Indent` > `SP3K` > `Wawancara` > `Booking`), fallback catatan transaksi bertingkat, dan normalisasi DataTables 7 kolom.
    - `App\Repositories\BookingAkadReportRepository`: Implementasi method `getMonthlyStatusTotals()` dan `getSummary()` dengan single-pass conditional aggregation SQL (`SUM(CASE ...)` dan `COUNT(DISTINCT ...)`), penanganan safe zero-date filtering (`> '1970-01-01'`), join tabel `hargajual` & `tipe` untuk akurasi tipe pricelist, serta pagination dan filter pencarian DataTables pada `getDetailRows()`.
  - **View / CSS / JS:**
    - `app/Views/laporan/booking-akad.php`: Restrukturisasi thead modal detail `#booking_akad_detail_table` menjadi tepat 7 kolom terstruktur, eliminasi kolom legacy `Jenis Laporan` dan `Nominal`, include modal detail styles `siteplan/partials/modal_detail_styles`, include vendor Flatpickr (`flatpickr.min.js`), dan inisialisasi variabel global pelindung `window.base_url` serta `window.loaded`.
    - `public/assets/js/laporan-booking-akad.js`: Pembaruan rendering header matriks bulanan (`colspan="7"`), DataTables modal detail 7 kolom dengan mapping custom renderer (muted sales, subsidi badge, status badge + catatan, tanggal format), tombol aksi pemanggil modal unit kavling `#modal_detail`, dan konfigurasi Chart.js 6 status.
    - `public/assets/css/laporan-booking-akad.css`: Penambahan kelas badge semantik untuk 6 status transaksi (`.booking-akad-badge-*`), penyesuaian min-width matriks (1200px single year, 2200px comparison mode), dan penegakan aturan perataan teks tegas (rata kiri teks, rata kanan nominal/angka, rata tengah tanggal/persentase).
  - **Testing & Verification:**
    - Pembuatan dan pembaruan suite pengujian: `tests/unit/BookingAkadReportServiceOverhaulTest.php` (25 tests, 83 assertions), `tests/unit/BookingAkadReportServiceTest.php` (17 tests, 89 assertions), `tests/database/BookingAkadReportLiveDbTest.php` (7 tests, 330 assertions pada Proyek 13 "Sanggar Indah Palastri"), dan standalone suite runner `tests/bin/run_booking_akad_suite.php` (38 tests, 100% pass rate).
  - **Deprecations:** Tidak ada fungsi atau rute yang di-deprecate.
- **`2026-11-03` [`UI_REPORT`] — Sinkronisasi Otomatis Switch Akad Indent (`#akad_indent`) dan Akad AJB (`#akad`) dengan Tipe Akad (`#is_ajb` PPJB/AJB) pada Modal Form MKDT:**
  - **Routes & Public Contracts:** Tidak ada perubahan rute atau kontrak backend (100% frontend interaktivitas).
  - **Controller / Service / Repo / Model:** Tidak ada perubahan backend.
  - **View / CSS / JS:**
    - `public/assets/js/siteplan/mkdt.js`: Memperbarui event listener `change` pada switch `#akad_indent` dan `#akad` di `#modal_divisi4`. Menambahkan sinkronisasi status dua arah untuk `#is_ajb`: jika `#akad_indent` aktif dan `#akad` nonaktif, `#is_ajb` otomatis diset `'PPJB'`; jika `#akad` aktif, `#is_ajb` otomatis diset `'AJB'`; jika `#akad` dinonaktifkan saat `#akad_indent` masih aktif, `#is_ajb` kembali ke `'PPJB'`; jika kedua switch nonaktif, `#is_ajb` di-reset ke string kosong `''`. Serta mereset tanggal `#akad_indent_tgl` atau `#akad_tgl` saat masing-masing switch dinonaktifkan.
  - **Deprecations:** Tidak ada fungsi atau rute yang di-deprecate.
- **`2026-11-03` [`UI_REPORT`] — Sinkronisasi Otomatis Switch Status Akad AJB (`#akad`) dengan Dropdown Tipe Akad (`#is_ajb`) dan Reset Tanggal Akad (`#akad_tgl`) pada Modal Form MKDT:**
  - **Routes & Public Contracts:** Tidak ada perubahan rute atau kontrak backend (100% frontend interaktivitas).
  - **Controller / Service / Repo / Model:** Tidak ada perubahan backend.
  - **View / CSS / JS:**
    - `public/assets/js/siteplan/mkdt.js`: Menambahkan event listener `change` pada switch `#akad` (Status Akad AJB) di modal form MKDT (`#modal_divisi4` / `#fm-mkdt`). Saat `#akad` dicentang (checked), nilai dropdown `#is_ajb` otomatis diubah menjadi `'AJB'`. Saat uncheck, field tanggal `#akad_tgl` dikosongkan via `setDatePicker(null, ...)` dan nilai `#is_ajb` di-reset kembali menjadi string kosong (`''`).
  - **Deprecations:** Tidak ada fungsi atau rute yang di-deprecate.
- **`2026-11-03` [`BUGFIX/UI_REPORT`] — Penyaringan Eksklusif Status Akad Indent pada Filter Sidebar Posisi Konsumen (`akad_indent == 1` & `akad == 0`):**
  - **Frontend:**
    - `app/Views/kavling/list-kavling.php`: Menambahkan payload AJAX DataTables `data.akad = ($("#filter_status_kavling").val() === 'indent') ? 0 : ''` saat filter status kavling dipilih "Akad Indent".
  - **Backend (Service):**
    - `App\Services\PosisiKonsumenService`: Memperbarui query filter `akad` dan `akad_indent` menggunakan pengecekan eksplisit `!== null && !== ''`. Ketika filter `akad_indent == 1` aktif, builder otomatis mengunci kriteria `mkdt.akad = 0` sehingga unit yang sudah mencapai Akad AJB (`akad = 1`) tidak muncul pada list Akad Indent.
    - `App\Services\TransaksiService`: Memperbarui sinkronisasi status pada `saveStatus` agar penandaan `status_mkdt = 'Akad'` tidak memaksa `akad = 1` bila unit kavling hanya melakukan Akad Indent.
  - **Deprecations:** Tidak ada fungsi atau rute yang di-deprecate.
- **`2026-11-03` [`BUGFIX/UI_REPORT`] — Penyesuaian Kondisi Tampilan Badge "Akad Indent" pada Kolom Nama Blok (BLOK) DataTables Posisi Konsumen:**
  - **Backend (Repository & Service):**
    - `App\Repositories\PosisiKonsumenRepository`: Menambahkan seleksi `mkdt.akad` pada `getBaseQuery()` SELECT fields agar status Akad AJB tersedia di row data transformer.
    - `App\Services\PosisiKonsumenService`: Memperbarui transformer kolom `nama_jalan` sehingga badge biru `"Akad Indent"` hanya dirender jika unit berstatus Akad Indent aktif dan belum Akad AJB (`$isAkadIndent && !$isAkadAjb` / `akad_indent == 1 && akad == 0`). Jika unit sudah mencapai tahap Akad AJB (`akad == 1`), badge `"Akad Indent"` otomatis disembunyikan.
  - **Deprecations:** Tidak ada fungsi atau rute yang di-deprecate.
- **`2026-11-02` [`UI_REPORT`] — Penyesuaian Tampilan Kolom TGL AKAD DataTables Posisi Konsumen (Eksklusif AJB jika sudah AJB, atau Indent jika belum AJB):**
  - **Backend (Service):**
    - `App\Services\PosisiKonsumenService`: Memperbarui transformer kolom `akad_tgl` pada `getDataTable()`. Jika unit kavling sudah mencapai tahap Akad AJB (`!empty(akad_tgl)`), kolom secara eksklusif hanya menampilkan badge hijau (`AJB: <tgl>`). Jika unit belum mencapai tahap AJB namun sudah Akad Indent (`!empty(akad_indent_tgl)`), kolom menampilkan badge biru (`Indent: <tgl>`). Jika belum keduanya, kolom menampilkan `-`.
  - **Deprecations:** Tidak ada fungsi atau rute yang di-deprecate.
- **`2026-11-02` [`FEATURE / MIGRATION`] — Migrasi Backfill Data Pemisahan State Akad Indent & Akad AJB pada Tabel mkdt Terintegrasi Modul Legal:**
  - **Database Migration:**
    - `app/Database/Migrations/2026-11-02-000001_BackfillMkdtAkadIndentAndAjb.php`: Eksekusi backfill pemisahan data transaksi historis pada tabel `mkdt`:
      - 44 transaksi Akad Indent (`akad_indent = 1`): diset `akad = 0`, `akad_tgl = NULL`, `akad_indent_tgl` terisi dari `legal.ppjb_tanggal` (atau fallback `mkdt.akad_tgl` lama), dan `is_ajb = 'PPJB'`.
      - 207 transaksi Akad AJB (`(akad_indent = 0 OR akad_indent IS NULL) AND akad = 1`): diset `akad = 1`, `akad_tgl` terisi resmi (diutamakan dari `legal.ajb_tanggal`), `is_ajb = 'AJB'`, dan `akad_indent_tgl` terisi dari `legal.ppjb_tanggal` jika memiliki riwayat PPJB.
  - **Cross-Module Integration (MKDT <-> Legal):**
    - Modul MKDT kini mereferensikan tabel `legal` (`legal.ppjb_tanggal` dan `legal.ajb_tanggal`) melalui relasi `kavling.id_legal` sebagai *source of truth* tanggal akta hukum resmi.
    - Menyiapkan cetak biru integrasi data berkelanjutan antara Modul Legal dan Modul MKDT.
  - **Deprecations:** Tidak ada fungsi atau rute yang di-deprecate.
- **`2026-10-29` [`FEATURE`] — Pemisahan Tanggal Akad Indent (akad_indent_tgl) dan Akad AJB (akad_tgl), Penataan Urutan Form MKDT, Modal Detail, List Kavling, & Ekspor Posisi Konsumen:**
  - **Routes & Public Contracts:**
    - Perluasan skema data tabel `mkdt` dengan kolom `akad_indent_tgl` (DATE, Nullable).
    - `PosisiKonsumenService::getDataTable()` mendukung parameter filter `bulan_field = 'indent'` untuk filtering berdasarkan tanggal akad indent.
  - **Database & Model:**
    - `app/Database/Migrations/2026-10-28-000002_AddAkadIndentTglToMkdtTable.php`: Migrasi penambahan kolom `akad_indent_tgl` setelah `akad_tgl` pada tabel `mkdt`.
    - `App\Models\MkdtModel`: Mendaftarkan `akad_indent_tgl` ke dalam `$allowedFields`.
  - **Backend (Service & Repository):**
    - `App\Services\MkdtHistoryService`: Penambahan pencatatan audit trail untuk `akad_indent_tgl` ("Tgl Akad Indent") serta pembaruan label `akad` menjadi "Akad AJB".
    - `App\Repositories\PosisiKonsumenRepository`: Penambahan seleksi kolom `mkdt.akad_indent_tgl` pada `getBaseQuery()`.
    - `App\Services\PosisiKonsumenService`: Penanganan filter `bulan_field == 'indent'` dan rendering multi-badge pada kolom `akad_tgl` (badge "Indent: <tgl>" dan "AJB: <tgl>").
    - Modul Transaksi (`App\Services\TransaksiService`, `App\Controllers\Api\TransaksiController`, `App\Controllers\Transaksi`): Validasi mandatory `akad_indent_tgl` saat `akad_indent == 1`, normalisasi payload, dan reset `null` jika nonaktif.
    - Modul Siteplan (`App\Services\SiteplanVisualStatusService`): Penentuan marker visual kavling `Akad Indent` mendukung pengecekan flag `akad_indent` maupun tanggal `akad_indent_tgl`.
    - Modul Keuangan (`App\Services\PrintService`): Penyesuaian layout ekspor Excel `exportPoskonXlsx` dengan menyisipkan kolom terpisah `TGL AKAD INDENT` (Kolom I) dan `TGL AKAD` (Kolom J).
  - **Frontend & UI:**
    - `app/Views/siteplan/mkdt.php`: Penataan ulang form alur transaksi menjadi sekuensial: Persiapan -> Akad Indent (Switch + Tgl) -> Akad AJB (Switch + Tgl) -> Debitur & BAST.
    - `app/Views/siteplan/partials/modal_detail.php`: Summary header menampilkan `Tgl Akad Indent` dan `Tgl Akad (AJB)`; section status menampilkan card Akad Indent kemudian Akad AJB.
    - `app/Views/kavling/list-kavling.php`: Penambahan opsi filter "Tgl. Akad Indent" (`indent`) pada dropdown acuan bulan/tahun posisi konsumen.
    - `public/assets/js/siteplan/mkdt.js`: Inisialisasi Flatpickr `akad_indent_tgl`, event listener auto-clear saat switch dimatikan, dan validasi client-side mandatory.
    - `public/assets/js/siteplan-detail-modal.js`: Binding data `s-akad_indent_tgl` serta switch/input detail `dt-st_akad_indent` dan `dt-st_akad_indent_tgl`.
  - **Deprecations:** Tidak ada fungsi atau rute yang di-deprecate.
- **`2026-10-28` [`FEATURE`] — Penambahan Laporan Perhitungan Jumlah Booking & Akad Per Bulan Selama 1 Tahun:**
  - **Routes & Public Contracts:**
    - `GET /laporan/booking-akad` -> `App\Controllers\Web\BookingAkadReportController::index`
    - `POST /api/laporan/booking-akad/summary` -> `App\Controllers\Api\BookingAkadReportController::summary`
    - `POST /api/laporan/booking-akad/detail` -> `App\Controllers\Api\BookingAkadReportController::detail`
  - **Backend:**
    - `App\Controllers\Web\BookingAkadReportController`: Controller Web untuk antarmuka laporan booking & akad dengan proteksi auth dan filter proyek aktif.
    - `App\Controllers\Api\BookingAkadReportController`: Controller API JSON untuk ringkasan matriks agregasi bulanan dan server-side DataTables detail transaksi.
    - `App\Services\BookingAkadReportService`: Service kalkulasi tahunan, agregasi 12 bulan matriks (Booking Total, Booking Aktif, Booking Batal, Akad, Rasio Akad/Booking %), dan normalisasi server-side DataTables.
    - `App\Repositories\BookingAkadReportRepository`: Repository query agregasi bulanan DB (`mkdt`, `konsumen`, `kavling`, `cluster`, `tipe`) dan rincian transaksi unit per kategori.
    - `app/Database/Migrations/2026-10-28-000001_AddBookingAkadReport.php`: Migrasi registrasi menu navigasi MKDT dan indeks performa DB (`idx_mkdt_booking_tgl_status`, `idx_mkdt_akad_tgl_status`).
  - **Frontend:**
    - `app/Views/laporan/booking-akad.php`: View antarmuka laporan lengkap dengan filter proyek, selector tahun & mode komparasi 2 tahun, KPI cards ringkasan, tabel matriks 12 bulan, dan modal detail transaksi DataTables.
    - `public/assets/css/laporan-booking-akad.css`: Styling kustom kartu ringkasan KPI, pill badge kategori, dan responsivitas tabel matriks.
    - `public/assets/js/laporan-booking-akad.js`: Interaktivitas AJAX summary, render komparasi 2 tahun, dan inisialisasi DataTables detail transaksi dengan filter kategori.
  - **Testing:**
    - `tests/unit/BookingAkadReportServiceTest.php`: Unit test service validasi range tahun, matriks agregasi bulanan, rasio persentase, dan normalisasi detail DataTables.
  - **Deprecations:** Tidak ada fungsi atau rute yang di-deprecate.
- **`2026-10-28` [`SYNC`] — Sinkronisasi status & inventori modul melalui `module:sync`.**
