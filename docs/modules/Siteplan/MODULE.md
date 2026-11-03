# Module: Siteplan

**Status:** `MAPPED`  
**Readiness Score:** `60/100`  
**Last Synced:** `2026-11-03 13:04:46` @ `1b0ebfd`  

## Purpose
Visualisasi peta interaktif kavling, filter multi-departemen, dan status spasial visual.

## Out of scope
- Hal-hal di luar tanggung jawab domain Siteplan.

## Invariants
- Modul lain tidak boleh memanipulasi tabel pemilik secara langsung.
- Perubahan finansial/status harus diverifikasi di level Service.

## Database Tables
### Owner Table(s)
- `siteplan_upload` *(akses tulis eksklusif hanya untuk modul Siteplan)*
- `target_siteplan` *(akses tulis eksklusif hanya untuk modul Siteplan)*
- `target_siteplan_kavling` *(akses tulis eksklusif hanya untuk modul Siteplan)*
- `target_siteplan_history` *(akses tulis eksklusif hanya untuk modul Siteplan)*

### Shared Tables (Dibaca dari modul lain)
- `kavling`
- `mkdt`
- `keuangan`
- `produksi`
- `legal`

## Contracts
### A. Public HTTP Endpoints & Routes Inventory
| Method | URI / Pattern | Tipe | Handler | Status Migrasi | Target API / Catatan |
|---|---|---|---|---|---|
| `POST` | `/siteplan/get/all` | `Explicit` | `Siteplan::getAllKavling` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `/siteplan/get/detail` | `Explicit` | `Siteplan::get_detail` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `/siteplan/getKategoriOptions` | `Explicit` | `Siteplan::getKategoriOptions` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `/siteplan/urgent/summary` | `Explicit` | `Siteplan::urgentSummary` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `GET` | `/siteplan/view` | `Explicit` | `Siteplan::view_siteplan` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `GET` | `/siteplan/produksi-mobile` | `Explicit` | `Siteplan::produksi_mobile` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `GET` | `/getmodal` | `Explicit` | `Modal::index` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `/api/target/list` | `Explicit` | `TargetController::list` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/target/detail` | `Explicit` | `TargetController::detail` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/target/save` | `Explicit` | `TargetController::save` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/target/history` | `Explicit` | `TargetController::history` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `ANY` | `/siteplan/index` | `AutoRoute` | `Siteplan::index` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/siteplan/add_kavling` | `AutoRoute` | `Siteplan::add_kavling` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/siteplan/add_kavling2` | `AutoRoute` | `Siteplan::add_kavling2` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/siteplan/add_others` | `AutoRoute` | `Siteplan::add_others` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/siteplan/edit_kavling` | `AutoRoute` | `Siteplan::edit_kavling` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/siteplan/edit_others` | `AutoRoute` | `Siteplan::edit_others` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/siteplan/get_config` | `AutoRoute` | `Siteplan::get_config` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/siteplan/get_others` | `AutoRoute` | `Siteplan::get_others` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/siteplan/get_kavling_by_multiple_id` | `AutoRoute` | `Siteplan::get_kavling_by_multiple_id` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/siteplan/get_harga_kavling` | `AutoRoute` | `Siteplan::get_harga_kavling` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/siteplan/get_turun_pembangunan` | `AutoRoute` | `Siteplan::get_turun_pembangunan` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/siteplan/set_turun_pembangunan` | `AutoRoute` | `Siteplan::set_turun_pembangunan` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/siteplan/get_kavling_by_id` | `AutoRoute` | `Siteplan::get_kavling_by_id` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/kalendar/index` | `AutoRoute` | `Kalendar::index` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/kalendar/getevents` | `AutoRoute` | `Kalendar::getEvents` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |

### B. Public PHP Services (Backend Contract Antar-Modul)
#### `SiteplanDataService.php`
- `getKavlings(array $params)`
- `getOthers(array $params)`
- `normalizeIdList($value)`
#### `SiteplanMenuService.php`
- _Tidak ada public method terdeteksi._
#### `SiteplanUrgentService.php`
- `getSummary(int $idProyek, int $groupId, ?int $userId = null)`
- `getUrgentSummary(int $idProyek, int $groupId, ?int $userId = null)`
- `getDashboardAlertCounts(int $idProyek)`
- `emptySummary()`
- `snoozeUrgentItem(int $userId, int $idProyek, string $itemKey, int $minutes)`
#### `SiteplanVisualStatusService.php`
- `appendVisualRows(array $rows, ?DateTimeInterface $today = null)`
- `buildRows($row, ?DateTimeInterface $today = null)`
- `buildFill($row, ?array $visualRows = null)`
#### `TargetSiteplanService.php`
- `listByProject(int $idProyek)`
- `get(int $idTarget)`
- `getKavlingTargetMap(int $idProyek)`
- `save(array $data)`
- `history(int $idTarget)`
#### `KalendarService.php`
- `divisiKeyFromName(string $name)`
- `getDivisiForView(array $groups)`
- `getEventKeysForDivisiKeys(array $divisiKeys)`
- `getEventsForCalendar(int $idProyek, string $start, string $end, array $types = [])`

## File Inventory
### Controllers (4)
- `app/Controllers/Siteplan.php` (1859 baris, 19 public methods: `index()`, `view_siteplan()`, `produksi_mobile()`, `urgentSummary()`, `add_kavling()`, `add_kavling2()`, `add_others()`, `edit_kavling()`, `edit_others()`, `get_config($id)`, `getAllKavling()`, `getKategoriOptions()`, `get_others()`, `get_kavling_by_multiple_id()`, `get_harga_kavling()`, `get_turun_pembangunan()`, `set_turun_pembangunan()`, `get_kavling_by_id()`, `get_detail()`)
- `app/Controllers/Modal.php` (18 baris, 1 public methods: `index()`)
- `app/Controllers/Api/TargetController.php` (81 baris, 4 public methods: `list()`, `detail()`, `save()`, `history()`)
- `app/Controllers/Kalendar.php` (58 baris, 2 public methods: `index()`, `getEvents()`)

### Services (6)
- `app/Services/SiteplanDataService.php` (124 baris, 3 public methods)
- `app/Services/SiteplanMenuService.php` (12 baris, 0 public methods)
- `app/Services/SiteplanUrgentService.php` (743 baris, 5 public methods)
- `app/Services/SiteplanVisualStatusService.php` (415 baris, 3 public methods)
- `app/Services/TargetSiteplanService.php` (202 baris, 5 public methods)
- `app/Services/KalendarService.php` (189 baris, 4 public methods)

### Repositories (3)
- `app/Repositories/TargetSiteplanRepository.php` (142 baris, 5 public methods: `getByProject(int $idProyek)`, `getById(int $idTarget)`, `getKavlings(int $idTarget)`, `getHistory(int $idTarget)`, `getKavlingTargetMap(int $idProyek)`)
- `app/Repositories/OthersRepository.php` (117 baris, 4 public methods: `getAll(?int $id, int $idProyek, array $clusterIds, array $filters = [])`, `hasProgressHistory()`, `countProgressHistory(int $idOthers)`, `getProgressHistory(int $idOthers, int $limit, int $offset)`)
- `app/Repositories/KalendarRepository.php` (87 baris, 3 public methods: `getPembayaranEvents(int $idProyek, string $start, string $end)`, `getJatuhTempoEvents(int $idProyek, string $start, string $end)`, `getMkdtEvents(int $idProyek, string $start, string $end)`)

### Models (4)
- `app/Models/SiteplanuploadModel.php`
- `app/Models/TargetSiteplanModel.php`
- `app/Models/TargetSiteplanKavlingModel.php`
- `app/Models/TargetSiteplanHistoryModel.php`

### Views (2 folder)
- `app/Views/siteplan` (16 file: `cashout_subkon.php`, `direksi.php`, `keuangan.php`, `legal.php`, `master.php`, `mkdt.php`, `modal_list_request_kavling.php`, `pajak.php`, `pilih_proyek.php`, `planning.php`, `produksi.php`, `produksi_mobile.php`, `request_kavling_modal.php`, `sales.php`, `target.php`, `test.php`)
- `app/Views/misc` (1 file: `kalender.php`)

### JavaScript (7 file)
- `public/assets/js/siteplan/master.js` (3824 baris)
- `public/assets/js/siteplan/composite-shape.js` (420 baris)
- `public/assets/js/siteplan/interaction-highlight.js` (143 baris)
- `public/assets/js/siteplan/shape-filter.js` (65 baris)
- `public/assets/js/siteplan/polygon-clip.js` (312 baris)
- `public/assets/js/siteplan-detail-modal.js` (2074 baris)
- `public/assets/js/siteplan/target.js` (244 baris)

## Key Functions
- **Controller (`app/Controllers/Siteplan.php`):**
  - `getAllKavling()`: Mengambil seluruh unit kavling proyek beserta kalkulasi status visual multi-departemen untuk rendering visual siteplan.
- **Controller (`app/Controllers/Api/TargetController.php`):**
  - `save()`: Validasi request dan pendelegasian penyimpanan target penjualan kavling bulanan ke Service.
- **Service (`app/Services/SiteplanVisualStatusService.php`):**
  - `appendVisualRows(array $rows, ?DateTimeInterface $today = null)` / `buildRows($row, ?DateTimeInterface $today = null)`: Membangun array status visual multi-departemen (MKDT, Keuangan, Produksi, Legal, Pajak) untuk setiap unit kavling di peta siteplan.
  - `mkdtRow($row)`: Menghitung status dan marker visual segmen MKDT. Mengevaluasi boolean `$hasAkadAjb` (`akad == 1` atau tanggal akad terisi) dan `$hasAkadIndent` (`akad_indent == 1` atau tanggal akad indent terisi). Jika salah satu terpenuhi, status MKDT adalah `'Akad'` (`Akad Subsidi` atau `Akad Komersil`). Jika kavling sudah mencapai `Akad Indent` namun belum `Akad AJB` (`$hasAkadIndent && !$hasAkadAjb`), marker visual `[['config_name' => 'Akad Indent', 'position' => 0.5]]` ditambahkan (garis aksen biru). Saat `Akad AJB` terpenuhi (`$hasAkadAjb`), marker visual dilepas sehingga warna solid Akad tampil penuh.
  - `buildFill($row, ?array $visualRows = null)`: Menentukan warna isian (fill color) poligon berdasarkan konfigurasi status visual kavling.
- **Service (`app/Services/SiteplanDataService.php`):**
  - `getKavlings(array $params)`: Pengambilan data kavling dengan filtering proyek, cluster, dan status.
- **Service (`app/Services/SiteplanUrgentService.php`):**
  - `getSummary()` / `getUrgentSummary()`: Menghitung kavling-kavling yang memerlukan perhatian mendesak berdasarkan filter waktu/status per divisi.
- **Service (`app/Services/TargetSiteplanService.php`):**
  - `save(array $data)`: Logika transaksi penyimpanan target kavling bulanan dan pencatatan riwayat target.
- **Service (`app/Services/KalendarService.php`):**
  - `getEventsForCalendar(int $idProyek, string $start, string $end, array $types = [])`: Pengambilan event kalender untuk jatuh tempo, pembayaran, dan MKDT.
- **Repository (`app/Repositories/TargetSiteplanRepository.php`):**
  - `getByProject(int $idProyek)` / `getKavlingTargetMap(int $idProyek)`: Data access target siteplan dan pemetaan kavling target per proyek.
- **Repository (`app/Repositories/OthersRepository.php`):**
  - `getAll(?int $id, int $idProyek, array $clusterIds, array $filters = [])`: Mengelola data aset penunjang non-kavling pada siteplan.
- **Repository (`app/Repositories/KalendarRepository.php`):**
  - `getPembayaranEvents()`, `getJatuhTempoEvents()`, `getMkdtEvents()`: Query jadwal pembayaran, jatuh tempo, dan agenda MKDT.

## Deprecated & Legacy Shims
- Route `POST /siteplan/get/all` (`Siteplan::getAllKavling`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
- Route `POST /siteplan/get/detail` (`Siteplan::get_detail`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
- Route `POST /siteplan/getKategoriOptions` (`Siteplan::getKategoriOptions`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
- Route `POST /siteplan/urgent/summary` (`Siteplan::urgentSummary`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
- Route `GET /siteplan/view` (`Siteplan::view_siteplan`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
- Route `GET /siteplan/produksi-mobile` (`Siteplan::produksi_mobile`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
- Route `GET /getmodal` (`Modal::index`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
- Route `ANY /siteplan/index` (`Siteplan::index`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
- Route `ANY /siteplan/add_kavling` (`Siteplan::add_kavling`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
- Route `ANY /siteplan/add_kavling2` (`Siteplan::add_kavling2`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
- Route `ANY /siteplan/add_others` (`Siteplan::add_others`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
- Route `ANY /siteplan/edit_kavling` (`Siteplan::edit_kavling`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
- Route `ANY /siteplan/edit_others` (`Siteplan::edit_others`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
- Route `ANY /siteplan/get_config` (`Siteplan::get_config`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
- Route `ANY /siteplan/get_others` (`Siteplan::get_others`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
- Route `ANY /siteplan/get_kavling_by_multiple_id` (`Siteplan::get_kavling_by_multiple_id`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
- Route `ANY /siteplan/get_harga_kavling` (`Siteplan::get_harga_kavling`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
- Route `ANY /siteplan/get_turun_pembangunan` (`Siteplan::get_turun_pembangunan`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
- Route `ANY /siteplan/set_turun_pembangunan` (`Siteplan::set_turun_pembangunan`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
- Route `ANY /siteplan/get_kavling_by_id` (`Siteplan::get_kavling_by_id`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
- Route `ANY /kalendar/index` (`Kalendar::index`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.
- Route `ANY /kalendar/getevents` (`Kalendar::getEvents`) — **LEGACY / DEPRECATED**: Gunakan endpoint eksplisit di `/api/...` atau controller modular.

## Readiness Checklist
- [ ] Has Service Layer
- [ ] Has Repository Layer
- [ ] Web & API Controllers Separated
- [ ] Explicit Routes Defined
- [ ] Zero Controller Layer Violations
- [x] MODULE.md Initialized

> **Layer Violations Detected:**
> - `app/Controllers/Siteplan.php`: Database::connect(), $this->db->table(), new ProyekModel(), new ClusterModel(), new JalanModel(), new TipeModel(), new KavlingModel(), new ConfigModel(), new ProduksiModel(), new LegalModel(), new MkdtModel(), new KeuanganModel(), new LogPembayaranModel(), new ChecklistSubItemModel()
> - `app/Controllers/Kalendar.php`: new GroupModel()

## Dead Code Candidates
_Jalankan `php spark module:deadcode Siteplan` untuk mengaudit dead code kandidat._

## Notes
- Modul diinisialisasi melalui tooling otomatis.

## Change Log
- **2026-11-03 [`UI_REPORT`] — Sinkronisasi Milestone Visual Siteplan untuk Status Akad Indent & Akad AJB:**
  - **Routes & Public Contracts:** Penyesuaian output kontrak `visual_rows[0]` (segmen MKDT) pada method `SiteplanVisualStatusService::buildRows` dan `appendVisualRows`. Segmen MKDT kini mengembalikan `config_name` `'Akad Subsidi'` atau `'Akad Komersil'` saat `akad_indent = 1` maupun `akad = 1`. Jika `akad_indent = 1` dan belum akad AJB (`akad = 0`), menyertakan marker `[['config_name' => 'Akad Indent', 'position' => 0.5]]` (garis aksen biru). Setelah `akad = 1` (Akad AJB), marker dihapus sehingga warna solid Akad tampil penuh.
  - **Controller / Service / Repo / Model:** Diperbarui `app/Services/SiteplanVisualStatusService.php` (`mkdtRow()`) untuk evaluasi boolean `$hasAkadAjb` dan `$hasAkadIndent`. Test suite diperbarui pada `tests/unit/SiteplanVisualStatusServiceTest.php` (72 tests passing).
  - **View / CSS / JS:** Tidak ada perubahan file (visual rendering poligon siteplan digerakkan oleh output backend).
  - **Deprecations:** Tidak ada.
- `2026-11-03` [`SYNC`] — Sinkronisasi status & inventori modul melalui `module:sync`.
