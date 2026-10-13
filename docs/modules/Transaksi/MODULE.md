# Module: Transaksi

**Status:** `REFACTORED`  
**Readiness Score:** `100/100`  
**Last Synced:** `2026-10-12 14:38:40` @ `be98758`  

## Purpose
Eksekusi transaksi jual-beli kavling, penetapan harga jual, ganti konsumen, dan request kavling.

## Out of scope
- Hal-hal di luar tanggung jawab domain Transaksi.

## Invariants
- Modul lain tidak boleh memanipulasi tabel pemilik secara langsung.
- Perubahan finansial/status harus diverifikasi di level Service.

## Database Tables
### Owner Table(s)
- `kavling` *(akses tulis eksklusif hanya untuk modul Transaksi)*
- `kavling_request` *(akses tulis eksklusif hanya untuk modul Transaksi)*
- `file_hargajual` *(akses tulis eksklusif hanya untuk modul Transaksi)*

### Shared Tables (Dibaca dari modul lain)
- `mkdt`
- `konsumen`
- `keuangan`

## Contracts
### A. Public HTTP Endpoints & Routes Inventory
| Method | URI / Pattern | Tipe | Handler | Status Migrasi | Target API / Catatan |
|---|---|---|---|---|---|
| `POST` | `/transaksi/simpan` | `Explicit` | `Transaksi::saveTransaksi` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `/transaksi/ambilsatu` | `Explicit` | `Transaksi::getByID` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `/transaksi/status/ambilsatu` | `Explicit` | `Transaksi::getStatusById` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `/transaksi/status/simpan` | `Explicit` | `Transaksi::saveStatus` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `simpan` | `Explicit` | `TransaksiController::saveTransaksi` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `ambilsatu` | `Explicit` | `TransaksiController::getByID` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `status/ambilsatu` | `Explicit` | `TransaksiController::getStatusById` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `status/simpan` | `Explicit` | `TransaksiController::saveStatus` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `konsumen/ganti` | `Explicit` | `TransaksiController::replaceConsumer` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `simpan` | `Explicit` | `TransaksiController::saveTransaksi` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `ambilsatu` | `Explicit` | `TransaksiController::getByID` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `status/ambilsatu` | `Explicit` | `TransaksiController::getStatusById` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `status/simpan` | `Explicit` | `TransaksiController::saveStatus` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `konsumen/ganti` | `Explicit` | `TransaksiController::replaceConsumer` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `history` | `Explicit` | `MkdtController::history` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `consumer-replacement-history` | `Explicit` | `MkdtController::consumerReplacementHistory` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `submit` | `Explicit` | `KavlingRequestController::submit` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `kavling-list` | `Explicit` | `KavlingRequestController::kavlingList` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `list` | `Explicit` | `KavlingRequestController::list` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `update-status` | `Explicit` | `KavlingRequestController::updateStatus` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `hargajual/get/all` | `Explicit` | `Hargajual::getDataTable` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `hargajual/get` | `Explicit` | `Hargajual::getAll` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `ANY` | `/transaksi/num` | `AutoRoute` | `Transaksi::num` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/transaksi_controller/cleannum` | `AutoRoute` | `TransaksiController::cleanNum` | 🟢 MIGRATED | Rute method aktif |
| `ANY` | `/hargajual/index` | `AutoRoute` | `Hargajual::index` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/hargajual/set_harga` | `AutoRoute` | `Hargajual::set_harga` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/hargajual/applysetharga` | `AutoRoute` | `Hargajual::applySetHarga` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/hargajual/getone` | `AutoRoute` | `Hargajual::getOne` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/hargajual/add` | `AutoRoute` | `Hargajual::add` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/hargajual/edit` | `AutoRoute` | `Hargajual::edit` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/hargajual/remove` | `AutoRoute` | `Hargajual::remove` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/hargajual/if_where` | `AutoRoute` | `Hargajual::if_where` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/hargajual/is_active` | `AutoRoute` | `Hargajual::is_active` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/hargajual/format_tgl` | `AutoRoute` | `Hargajual::format_tgl` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/hargajual/num` | `AutoRoute` | `Hargajual::num` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |

### B. Public PHP Services (Backend Contract Antar-Modul)
#### `TransaksiService.php`
- `getBundle(?int $idMkdt, ?int $idHargajual, ?int $idKavling)`
- `getStatusById($id_hargajual, $id_kavling, $id_mkdt)`
- `saveTransaksi(array $kons, array $mk, array $um, array $files = [], array $opt = [])`
- `replaceConsumer(int $idMkdt, int $idKavling, int $oldConsumerId, array $consumer, array $files, string $referralCode, int $actorId, bool $allowDuplicateNik = false)`
- `saveStatus(array $data, array $perintahBangun, array $files = [])`
- `createOrUpdate(?int $idMkdtExisting, array $data, array $opt = [])`
- `update($id, $data)`
- `num($d)`
#### `KavlingRequestService.php`
- `submitRequest(array $data, int $userId)`
- `getList(int $idProyek, ?string $status = null)`
- `updateStatus(int $idRequest, string $newStatus, int $userId)`

## File Inventory
### Controllers (5)
- `app/Controllers/Transaksi.php` (253 baris, 5 public methods)
- `app/Controllers/Api/TransaksiController.php` (304 baris, 6 public methods)
- `app/Controllers/Api/MkdtController.php` (58 baris, 2 public methods)
- `app/Controllers/Api/KavlingRequestController.php` (128 baris, 4 public methods)
- `app/Controllers/Hargajual.php` (557 baris, 13 public methods)

### Services (2)
- `app/Services/TransaksiService.php` (808 baris)
- `app/Services/KavlingRequestService.php` (217 baris)

### Repositories (3)
- `app/Repositories/TransaksiRepository.php` (230 baris)
- `app/Repositories/HargaJualRepository.php` (37 baris)
- `app/Repositories/KavlingRepository.php` (686 baris)

### Models (3)
- `app/Models/KavlingModel.php`
- `app/Models/KavlingRequestModel.php`
- `app/Models/FileHargajualModel.php`

### Views (1 folder)
- `app/Views/hargajual` (1 file)

### JavaScript (2 file)
- `public/assets/js/siteplan/sales.js` (525 baris)
- `public/assets/js/siteplan/request-kavling.js` (571 baris)

## Readiness Checklist
- [x] Has Service Layer
- [x] Has Repository Layer
- [x] Web & API Controllers Separated
- [x] Explicit Routes Defined
- [x] MODULE.md Initialized

## Dead Code Candidates
_Jalankan `php spark module:deadcode Transaksi` untuk mengaudit dead code kandidat._

## Notes
- Modul diinisialisasi melalui tooling otomatis.
