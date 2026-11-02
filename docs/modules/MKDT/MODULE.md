# Module: MKDT

**Status:** `MAPPED`  
**Readiness Score:** `60/100`  
**Last Synced:** `2026-11-02 15:44:31` @ `bc174ee`  

## Purpose
Manajemen Konsumen dan Transaksi: siklus pemesanan, verifikasi data konsumen, dan posisi kavling.

## Out of scope
- Hal-hal di luar tanggung jawab domain MKDT.

## Invariants
- Modul lain tidak boleh memanipulasi tabel pemilik secara langsung.
- Perubahan finansial/status harus diverifikasi di level Service.

## Database Tables
### Owner Table(s)
- `mkdt` *(akses tulis eksklusif hanya untuk modul MKDT)*
- `konsumen` *(akses tulis eksklusif hanya untuk modul MKDT)*
- `mkdt_kavling_history` *(akses tulis eksklusif hanya untuk modul MKDT)*
- `promo` *(akses tulis eksklusif hanya untuk modul MKDT)*

### Shared Tables (Dibaca dari modul lain)
- `kavling`
- `keuangan`
- `legal`

## Contracts
### A. Public HTTP Endpoints & Routes Inventory
| Method | URI / Pattern | Tipe | Handler | Status Migrasi | Target API / Catatan |
|---|---|---|---|---|---|
| `GET` | `/list-batal` | `Explicit` | `Mkdt::list_batal` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `GET` | `/list-stock` | `Explicit` | `Mkdt::list_stock` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `GET` | `/list-kavling` | `Explicit` | `PosisiKonsumen::index` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `/list-kavling/ambil` | `Explicit` | `PosisiKonsumen::getDataTables` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `GET` | `/list-kavling/(:segment)` | `Explicit` | `PosisiKonsumen::index/$1` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `/list-kavling/akad/ambil` | `Explicit` | `PosisiKonsumen::getDataTablesAkad` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `/list-kavling/batal/ambil` | `Explicit` | `PosisiKonsumen::getDataTablesBatal` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `/export/poskon/(:segment)/(:segment)` | `Explicit` | `PosisiKonsumen::exportPoskon/$1/$2` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `/riwayat/poskon/(:segment)` | `Explicit` | `PosisiKonsumen::getRiwayatExport/$1` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `/api/nup/list` | `Explicit` | `NupController::list` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/nup/detail` | `Explicit` | `NupController::detail` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/nup/save` | `Explicit` | `NupController::save` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/nup/pull` | `Explicit` | `NupController::pull` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/nup/kavling-options` | `Explicit` | `NupController::kavlingOptions` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/nup/kavling-history` | `Explicit` | `NupController::kavlingHistory` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/laporan/booking-akad` | `Explicit` | `BookingAkadReportController::index` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/laporan/booking-akad/summary` | `Explicit` | `BookingAkadReportController::summary` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/laporan/booking-akad/detail` | `Explicit` | `BookingAkadReportController::detail` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `ANY` | `/mkdt/get_data_by_id` | `AutoRoute` | `Mkdt::get_data_by_id` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/mkdt/simpan_batal` | `AutoRoute` | `Mkdt::simpan_batal` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/mkdt/batal_mkdt` | `AutoRoute` | `Mkdt::batal_mkdt` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/mkdt/save` | `AutoRoute` | `Mkdt::save` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/mkdt/set_harga` | `AutoRoute` | `Mkdt::set_harga` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/mkdt/getliststock` | `AutoRoute` | `Mkdt::getListStock` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/mkdt/if_where` | `AutoRoute` | `Mkdt::if_where` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/mkdt/format_tgl` | `AutoRoute` | `Mkdt::format_tgl` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/mkdt/is_active` | `AutoRoute` | `Mkdt::is_active` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/mkdt/getsi` | `AutoRoute` | `Mkdt::getsi` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/mkdt/savesi` | `AutoRoute` | `Mkdt::saveSI` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/mkdt/export_xlsx` | `AutoRoute` | `Mkdt::export_xlsx` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/posisi_konsumen/format_tgl` | `AutoRoute` | `PosisiKonsumen::format_tgl` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/posisi_konsumen/is_active` | `AutoRoute` | `PosisiKonsumen::is_active` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/posisi_konsumen/exportposkonpdf` | `AutoRoute` | `PosisiKonsumen::exportPoskonPdf` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/posisi_konsumen/exportposkonxlsx` | `AutoRoute` | `PosisiKonsumen::exportPoskonXlsx` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/promo/index` | `AutoRoute` | `Promo::index` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/promo/getdatatable` | `AutoRoute` | `Promo::getDataTable` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/promo/getall` | `AutoRoute` | `Promo::getAll` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/promo/getone` | `AutoRoute` | `Promo::getOne` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/promo/add` | `AutoRoute` | `Promo::add` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/promo/edit` | `AutoRoute` | `Promo::edit` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/promo/remove` | `AutoRoute` | `Promo::remove` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/promo/if_where` | `AutoRoute` | `Promo::if_where` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/promo/is_active` | `AutoRoute` | `Promo::is_active` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/promo/format_tgl` | `AutoRoute` | `Promo::format_tgl` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |

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
#### `BookingAkadReportService.php`
- `getAvailableYears(int $idProyek)`
- `getSummary(int $idProyek, int $yearA, ?int $yearB = null)`
- `getDetail(int $idProyek, array $request)`
- `monthLabel(int $month)`

## File Inventory
### Controllers (6)
- `app/Controllers/Mkdt.php` (1969 baris, 13 public methods: `get_data_by_id($st = null)`, `simpan_batal()`, `batal_mkdt()`, `save()`, `set_harga()`, `list_stock()`, `getListStock()`, `if_where($var, $column, $condition, $query)`, `format_tgl($tgl)`, `is_active($id, $texts, $textf)`, `getsi()`, `saveSI()`, `export_xlsx()`)
- `app/Controllers/PosisiKonsumen.php` (304 baris, 10 public methods: `index($status = null)`, `getDataTables($status = null)`, `getDataTablesAkad()`, `getDataTablesBatal()`, `format_tgl($tgl)`, `is_active($id, $texts, $textf)`, `exportPoskon($tipe, $status)`, `exportPoskonPdf($status)`, `exportPoskonXlsx($status)`, `getRiwayatExport($status = "Aktif")`)
- `app/Controllers/Api/NupController.php` (194 baris, 6 public methods: `list()`, `detail()`, `save()`, `pull()`, `kavlingOptions()`, `kavlingHistory()`)
- `app/Controllers/Promo.php` (351 baris, 10 public methods: `index()`, `getDataTable()`, `getAll()`, `getOne()`, `add()`, `edit()`, `remove()`, `if_where($var, $column, $condition, $query)`, `is_active($id, $texts, $textf)`, `format_tgl($tgl)`)
- `app/Controllers/Web/BookingAkadReportController.php` (49 baris, 1 public methods: `index()`)
- `app/Controllers/Api/BookingAkadReportController.php` (80 baris, 2 public methods: `summary()`, `detail()`)

### Services (8)
- `app/Services/MkdtHistoryService.php` (314 baris, 11 public methods)
- `app/Services/MkdtSettlementService.php` (48 baris, 1 public methods)
- `app/Services/MkdtFinancialBreakdownService.php` (254 baris, 6 public methods)
- `app/Services/MkdtFinancialReconciliationService.php` (275 baris, 2 public methods)
- `app/Services/PosisiKonsumenService.php` (446 baris, 6 public methods)
- `app/Services/KonsumenService.php` (49 baris, 3 public methods)
- `app/Services/NupService.php` (532 baris, 6 public methods)
- `app/Services/BookingAkadReportService.php` (222 baris, 4 public methods)

### Repositories (5)
- `app/Repositories/MkdtHistoryRepository.php` (55 baris, 4 public methods: `hasTable()`, `insert(array $fields)`, `countByKavling(int $idKavling)`, `getByKavling(int $idKavling, int $limit, int $offset)`)
- `app/Repositories/PosisiKonsumenRepository.php` (198 baris, 4 public methods: `getBaseQuery($status = "Booking")`, `getQueryBatal()`, `getRiwayatExport($id_proyek, $status)`, `insertRiwayatExport($data)`)
- `app/Repositories/KonsumenRepository.php` (60 baris, 1 public methods: `getKonsumenTransaksi(int $idMkdt, $select = [])`)
- `app/Repositories/NupRepository.php` (412 baris, 10 public methods: `getNupListByProyek(int $idProyek, ?string $search = null)`, `getNupDetail(int $idMkdt, bool $lock = false)`, `getKavlingRow(int $idKavling, bool $lock = false)`, `getKavlingLabel(?int $idKavling)`, `getProyekIdByKavling(?int $idKavling)`, `getKavlingOptionsForTransfer(int $idProyek, ?string $search = null, ?int $currentIdKavling = null, ?int $lastIdKavling = null, ?string $filterHargaJual = null)`, `insertKavlingHistory(array $data)`, `getKavlingHistory(int $idMkdt = 0, int $idKavling = 0)`, `isNoNupExists(string $noNup, int $idProyek, int $excludeIdMkdt = 0)`, `generateNextNupNumber(int $idProyek)`)
- `app/Repositories/BookingAkadReportRepository.php` (239 baris, 4 public methods: `getAvailableYearBounds(int $idProyek)`, `getMonthlyBookingTotals(int $idProyek, array $years)`, `getMonthlyAkadTotals(int $idProyek, array $years)`, `getDetailRows(int $idProyek, int $year, int $month, string $category, array $dataTable)`)

### Models (3)
- `app/Models/MkdtModel.php`
- `app/Models/KonsumenModel.php`
- `app/Models/MkdtKavlingHistoryModel.php`

### Views (3 folder)
- `app/Views/kavling` (4 file: `list-kavling-akad.php`, `list-kavling-batal.php`, `list-kavling.php`, `list-stock.php`)
- `app/Views/promo` (1 file: `index.php`)
- `app/Views/laporan` (3 file: `booking-akad.php`, `cash-in.php`, `hasil-akad.php`)

### JavaScript (2 file)
- `public/assets/js/siteplan/mkdt.js` (3279 baris)
- `public/assets/js/laporan-booking-akad.js` (613 baris)

## Key Functions
- **Controllers:**
  - `App\Controllers\Web\BookingAkadReportController`:
    - `index()`: Merender tampilan utama laporan Booking & Akad tahunan (`app/Views/laporan/booking-akad.php`) dengan filter proyek aktif.
  - `App\Controllers\Api\BookingAkadReportController`:
    - `summary()`: Endpoint JSON matriks agregasi bulanan untuk 1 atau 2 tahun pembanding via `BookingAkadReportService::getSummary()`.
    - `detail()`: Endpoint JSON server-side DataTables rincian transaksi kavling per bulan & kategori via `BookingAkadReportService::getDetail()`.
  - `App\Controllers\Api\NupController`: Endpoint terstruktur untuk listing, detail, simpan, pull transaksi ke NUP, opsi kavling, dan histori pemindahan kavling.
  - `App\Controllers\Mkdt`: Controller legacy untuk alur batal, stok kavling, set harga, dan standing instruction.
  - `App\Controllers\PosisiKonsumen`: Controller legacy untuk posisi konsumen aktif, akad, batal, dan ekspor.
  - `App\Controllers\Promo`: Controller manajemen promo dan diskon kavling.
- **Services:**
  - `App\Services\BookingAkadReportService`:
    - `getAvailableYears(int $idProyek)`: Mengambil rentang daftar tahun yang tersedia dari tanggal booking (`booking_tgl`) & akad (`akad_tgl`) kavling pada proyek.
    - `getSummary(int $idProyek, int $yearA, ?int $yearB = null)`: Agregasi matriks bulanan 12 bulan (Booking Total, Booking Aktif, Booking Batal, Akad, dan Rasio Akad/Booking %) serta total akumulasi tahunan untuk single year maupun komparasi 2 tahun.
    - `getDetail(int $idProyek, array $request)`: Server-side DataTables provider dengan normalisasi parameter, sorting, searching, dan whitelist category (`booking_total`, `booking_aktif`, `booking_batal`, `akad`).
    - `monthLabel(int $month)`: Label nama bulan bahasa Indonesia standar (Januari - Desember).
  - `App\Services\MkdtHistoryService`: Pencatatan log histori kavling, consumer replacement snapshots, dan build summary status/perubahan (termasuk audit trail perubahan `akad_indent_tgl` serta pembedaan label `Akad AJB` vs `Akad Indent`).
  - `App\Services\MkdtSettlementService`: Sinkronisasi status pelunasan transaksi MKDT.
  - `App\Services\MkdtFinancialBreakdownService`: Validasi breakdown finansial kavling, target, progress pembayaran, dan jadwal angsuran.
  - `App\Services\MkdtFinancialReconciliationService`: Audit dan rekonsiliasi data transaksi finansial MKDT.
  - `App\Services\PosisiKonsumenService`: Business logic DataTables posisi konsumen (Booking, Akad, Batal), dukungan filter acuan waktu `bulan_field == 'indent'` (terhadap `mkdt.akad_indent_tgl`), rendering kolom `TGL AKAD` yang eksklusif (jika sudah mencapai tahap Akad AJB hanya menampilkan badge `AJB: <tgl>`, jika baru tahap Akad Indent menampilkan badge `Indent: <tgl>`, atau `-` jika belum keduanya), serta riwayat ekspor.
  - `App\Services\KonsumenService`: Upsert master data konsumen dan query relasi konsumen transaksi.
  - `App\Services\NupService`: Manajemen siklus NUP, transfer kavling, validasi nomor NUP unik per proyek, dan lampiran berkas.
- **Repositories:**
  - `App\Repositories\BookingAkadReportRepository`:
    - `getAvailableYearBounds(int $idProyek)`: Query batas tahun minimum dan maksimum dari tanggal booking dan akad.
    - `getMonthlyBookingTotals(int $idProyek, array $years)`: Agregasi bulanan unit booking total, aktif, dan batal berdasarkan `booking_tgl` dan `status_transaksi`.
    - `getMonthlyAkadTotals(int $idProyek, array $years)`: Agregasi bulanan unit akad berdasarkan `akad_tgl` dan `status_transaksi`.
    - `getDetailRows(int $idProyek, int $year, int $month, string $category, array $dataTable)`: Query detail transaksi unit kavling per bulan & kategori untuk server-side DataTables dengan pagination, sorting, dan filtering.
  - `App\Repositories\MkdtHistoryRepository`: Query dan persistensi tabel histori aktivitas transaksi kavling.
  - `App\Repositories\PosisiKonsumenRepository`: Query builder DataTables posisi konsumen aktif, akad, batal (termasuk seleksi kolom `mkdt.akad_indent_tgl`), dan log ekspor.
  - `App\Repositories\KonsumenRepository`: Query master konsumen terkait transaksi MKDT.
  - `App\Repositories\NupRepository`: Query detail NUP, validasi nomor NUP, histori perpindahan kavling, dan opsi kavling transfer.
- **Models:**
  - `App\Models\MkdtModel`: Definisi entitas dan akses data tabel `mkdt`, dengan whitelist `$allowedFields` mencakup `akad_indent_tgl` (DATE, Nullable).
  - `App\Models\KonsumenModel`: Definisi entitas master data konsumen.
  - `App\Models\MkdtKavlingHistoryModel`: Definisi entitas riwayat mutasi transaksi kavling.
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
- **Fitur Baru (Laporan Booking & Akad):** Tidak ada deprecations; seluruh rute baru menggunakan arsitektur modern Web & Api Controller (`App\Controllers\Web\...` dan `App\Controllers\Api\...`).
- **Legacy Routes / Shims:**
  - Route `GET /list-batal` (`Mkdt::list_batal`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
  - Route `GET /list-stock` (`Mkdt::list_stock`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
  - Route `GET /list-kavling` (`PosisiKonsumen::index`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
  - Route `POST /list-kavling/ambil` (`PosisiKonsumen::getDataTables`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
  - Route `GET /list-kavling/(:segment)` (`PosisiKonsumen::index/$1`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
  - Route `POST /list-kavling/akad/ambil` (`PosisiKonsumen::getDataTablesAkad`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
  - Route `POST /list-kavling/batal/ambil` (`PosisiKonsumen::getDataTablesBatal`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
  - Route `POST /export/poskon/(:segment)/(:segment)` (`PosisiKonsumen::exportPoskon/$1/$2`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
  - Route `POST /riwayat/poskon/(:segment)` (`PosisiKonsumen::getRiwayatExport/$1`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
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

## Readiness Checklist
- [ ] Has Service Layer
- [ ] Has Repository Layer
- [ ] Web & API Controllers Separated
- [ ] Explicit Routes Defined
- [ ] Zero Controller Layer Violations
- [x] MODULE.md Initialized

> **Layer Violations Detected:**
> - `app/Controllers/Mkdt.php`: db_connect(), $this->db->table(), new KeuanganModel(), new MkdtModel(), new KavlingModel(), new KonsumenModel(), new LogPembayaranModel()
> - `app/Controllers/PosisiKonsumen.php`: db_connect(), $this->db->table(), new KeuanganModel(), new MkdtModel(), new KavlingModel(), new KonsumenModel(), new LogPembayaranModel(), new ProyekModel(), new ChecklistSubItemModel()
> - `app/Controllers/Promo.php`: db_connect(), $this->db->table(), new ClusterModel(), new ProyekModel()

## Dead Code Candidates
_Jalankan `php spark module:deadcode MKDT` untuk mengaudit dead code kandidat._

## Notes
- Modul MKDT bertanggung jawab atas siklus transaksi konsumen dari NUP, booking, akad, hingga pembatalan unit.
- Subfitur Laporan Perhitungan Booking & Akad (`/laporan/booking-akad`) menggunakan arsitektur modern Thin Controller -> Service -> Repository.
- **Cross-Module Callers / Consumers & Integration:**
  - Modul Legal: integrasi pembacaan data akta yuridis resmi (`legal.ppjb_tanggal` dan `legal.ajb_tanggal`) via relasi `kavling.id_legal` untuk sinkronisasi tanggal akta PPJB & AJB serta acuan backfill data historis.
  - Modul Keuangan: sinkronisasi status pembayaran dan pelunasan kavling via `MkdtSettlementService`, serta ekspor Excel Posisi Konsumen dengan kolom `TGL AKAD INDENT` dan `TGL AKAD` via `PrintService::exportPoskonXlsx()`.
  - Modul Transaksi: manipulasi status unit kavling, validasi dan persistensi `akad_indent_tgl` & `akad_tgl` via `TransaksiService`, serta log pergantian konsumen via `MkdtHistoryService`.
  - Modul Siteplan: visual status marker `Akad Indent` pada peta interaktif via `SiteplanVisualStatusService`, serta integrasi summary card dan status Akad Indent pada modal detail kavling (`siteplan-detail-modal.js`, `app/Views/siteplan/partials/modal_detail.php`).
  - Frontend Laporan: mengonsumsi REST API `/api/laporan/booking-akad/summary` dan `/api/laporan/booking-akad/detail`.

## Change Log
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
