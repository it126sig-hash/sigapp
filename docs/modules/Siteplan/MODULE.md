# Module: Siteplan

**Status:** `MAPPED`  
**Readiness Score:** `60/100`  
**Last Synced:** `2026-11-05 12:26:58` @ `a684cd1`  

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
- `tiket_masalah`

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
- `getDashboardAlertCounts(?int $idProyek = null)`
- `countMkdtDateAlerts(string $field, ?int $idProyek = null, ?string $fromDate = null, ?string $toDate = null)`
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
- `app/Controllers/Siteplan.php` (1861 baris, 19 public methods: `index()`, `view_siteplan()`, `produksi_mobile()`, `urgentSummary()`, `add_kavling()`, `add_kavling2()`, `add_others()`, `edit_kavling()`, `edit_others()`, `get_config($id)`, `getAllKavling()`, `getKategoriOptions()`, `get_others()`, `get_kavling_by_multiple_id()`, `get_harga_kavling()`, `get_turun_pembangunan()`, `set_turun_pembangunan()`, `get_kavling_by_id()`, `get_detail()`)
- `app/Controllers/Modal.php` (18 baris, 1 public methods: `index()`)
- `app/Controllers/Api/TargetController.php` (81 baris, 4 public methods: `list()`, `detail()`, `save()`, `history()`)
- `app/Controllers/Kalendar.php` (58 baris, 2 public methods: `index()`, `getEvents()`)

### Services (6)
- `app/Services/SiteplanDataService.php` (124 baris, 3 public methods)
- `app/Services/SiteplanMenuService.php` (12 baris, 0 public methods)
- `app/Services/SiteplanUrgentService.php` (791 baris, 6 public methods)
- `app/Services/SiteplanVisualStatusService.php` (415 baris, 3 public methods)
- `app/Services/TargetSiteplanService.php` (202 baris, 5 public methods)
- `app/Services/KalendarService.php` (189 baris, 4 public methods)

### Repositories (3)
- `app/Repositories/TargetSiteplanRepository.php` (142 baris, 5 public methods: `getByProject(int $idProyek)`, `getById(int $idTarget)`, `getKavlings(int $idTarget)`, `getHistory(int $idTarget)`, `getKavlingTargetMap(int $idProyek)`)
- `app/Repositories/OthersRepository.php` (145 baris, 4 public methods: `getAll(?int $id, int $idProyek, array $clusterIds, array $filters = [])`, `hasProgressHistory()`, `countProgressHistory(int $idOthers)`, `getProgressHistory(int $idOthers, int $limit, int $offset)`)
- `app/Repositories/KalendarRepository.php` (87 baris, 3 public methods: `getPembayaranEvents(int $idProyek, string $start, string $end)`, `getJatuhTempoEvents(int $idProyek, string $start, string $end)`, `getMkdtEvents(int $idProyek, string $start, string $end)`)

### Models (4)
- `app/Models/SiteplanuploadModel.php`
- `app/Models/TargetSiteplanModel.php`
- `app/Models/TargetSiteplanKavlingModel.php`
- `app/Models/TargetSiteplanHistoryModel.php`

### Views (2 folder)
- `app/Views/siteplan` (16 file: `cashout_subkon.php`, `direksi.php`, `keuangan.php`, `legal.php`, `master.php`, `mkdt.php`, `modal_list_request_kavling.php`, `pajak.php`, `pilih_proyek.php`, `planning.php`, `produksi.php`, `produksi_mobile.php`, `request_kavling_modal.php`, `sales.php`, `target.php`, `test.php`)
- `app/Views/misc` (1 file: `kalender.php`)

### JavaScript (8 file)
- `public/assets/js/siteplan/master.js` (3868 baris)
- `public/assets/js/siteplan/composite-shape.js` (420 baris)
- `public/assets/js/siteplan/interaction-highlight.js` (143 baris)
- `public/assets/js/siteplan/shape-filter.js` (65 baris)
- `public/assets/js/siteplan/polygon-clip.js` (312 baris)
- `public/assets/js/siteplan-detail-modal.js` (2431 baris)
- `public/assets/js/siteplan/target.js` (244 baris)
- `public/assets/js/siteplan/tiket-overlay.js` (462 baris)

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
  - `getDashboardAlertCounts(?int $idProyek)`: Agregasi tanpa batas kuota untuk kartu peringatan dashboard utama (tagihan overdue, tagihan due 7 hari, cashout subkon, bangunan telat, SP3K expire, rencana akad).
  - `countMkdtDateAlerts(string $field, ?int $idProyek, ?string $fromDate, ?string $toDate)`: Menghitung kavling aktif dengan batas tanggal MKDT (`sp3k_tgl_exp`, `rencana_akad_tgl`) pada rentang tertentu, memvalidasi `akad_tgl` belum terlaksana dan unit tidak batal.
- **Service (`app/Services/TargetSiteplanService.php`):**
  - `save(array $data)`: Logika transaksi penyimpanan target kavling bulanan dan pencatatan riwayat target.
- **Service (`app/Services/KalendarService.php`):**
  - `getEventsForCalendar(int $idProyek, string $start, string $end, array $types = [])`: Pengambilan event kalender untuk jatuh tempo, pembayaran, dan MKDT.
- **Repository (`app/Repositories/TargetSiteplanRepository.php`):**
  - `getByProject(int $idProyek)` / `getKavlingTargetMap(int $idProyek)`: Data access target siteplan dan pemetaan kavling target per proyek.
- **Repository (`app/Repositories/OthersRepository.php`):**
  - `getAll(?int $id, int $idProyek, array $clusterIds, array $filters = [])`: Mengelola data aset penunjang non-kavling pada siteplan. Menerapkan pengelompokan filter scope (`siteplan`, `produksi`, serta `masalah` jika kategori 'Masalah' dicentang) dan secara eksplisit menyertakan `orWhere('act_tm.active_tiket_id IS NOT NULL', null, false)`. Hal ini memastikan seluruh entitas others (jalan, fasos, rth, ruko) dengan `scope = 'masalah'` otomatis termuat pada tampilan default kanvas siteplan selama memiliki tiket masalah aktif.
  - `addActiveTiketSelect(BaseBuilder $builder)`: Query JOIN ke derived table `tiket_masalah` (`ref_type = 'others'`, `status NOT IN ('selesai', 'batal', 'hold')`) untuk menyertakan `active_tiket_id`, `active_tiket_prioritas`, dan `active_tiket_status` pada aset fasum/penunjang.
- **Repository (`app/Repositories/KalendarRepository.php`):**
  - `getPembayaranEvents()`, `getJatuhTempoEvents()`, `getMkdtEvents()`: Query jadwal pembayaran, jatuh tempo, dan agenda MKDT.
- **Frontend JavaScript (`public/assets/js/siteplan/tiket-overlay.js`):**
  - `SiteplanTiketOverlay`: Modul UMD layer animasi overlay tiket masalah di KonvaJS (`getPriorityColor`, `calculateOpacity`, `calculateCentroid`, `createOverlayShape`, `createBadgeShape`, `createAnimation`, `buildOverlayShapes`, `stopAnimation`, `clearOverlay`).
    - *Multi-Type Entities Support (`kavling` & `others`)*: Mendukung pembuatan shape overlay dan centroid warning badge untuk kavling (`#tiket-overlay-{id}`, `#tiket-badge-{id}`) maupun fasilitas umum/others (`#tiket-overlay-others{id}`, `#tiket-badge-others{id}`).
    - *Safe Centroid Calculation*: Menangani poligon titik jamak secara aman melalui `calculateCentroid(points)` untuk kavling dan others; fallback ke bounding box center jika terjadi anomali koordinat.
    - *Visibility Evaluation*: Memeriksa status visibilitas node induk `#kavling_{id}` atau `#others{id}` sebelum merender shape overlay pada canvas.
    - *Frame Rate Capping 30 FPS*: Pembatasan siklus render animasi menggunakan `TARGET_FPS = 30` dan `FRAME_INTERVAL_MS = 33.33ms` untuk efisiensi CPU/GPU canvas.
    - *Opsi 1 (Pulsing High-Contrast Stroke)*: Menggambar kontur poligon berdenyut berbasis prioritas (`urgent` = merah, `medium` = oranye, `normal` = biru, `low` = abu-abu) dengan garis tepi putih kontras tinggi (`stroke: '#ffffff'`, `strokeWidth` dinamis 1.0px - 3.5px via `1.0 + 2.5 * opacity`).
    - *Opsi 2 (Centroid Warning Badge `!`)*: Menghitung titik tengah poligon kavling/others melalui `calculateCentroid(points)` dan menampilkan badge lingkaran peringatan tanda seru (`!`) yang berdenyut membesar-mengecil halus (`scale` 0.85x - 1.15x via `0.85 + 0.3 * opacity`).
- **Frontend JavaScript (`public/assets/js/siteplan/master.js`):**
  - `siteplanProblemItems`: State penyimpanan masalah terpusat berbasis dictionary `{ kavling: [], others: [] }`.
  - `syncSiteplanTiketOverlay()`: Menggabungkan item masalah kavling dan others secara atomik sebelum meneruskan data gabungan ke `SiteplanTiketOverlay.buildOverlayShapes()`, mencegah race condition saat pemanggilan data asinkron.
- **Frontend JavaScript (`public/assets/js/siteplan-detail-modal.js`):**
  - `resolveMkdtStatusInfo(m)`: Resolusi hierarki prioritas status transaksi kavling MKDT (Batal -> Akad AJB -> Akad Indent -> SP3K -> Wawancara -> Booking) dan tanggal status yang valid (`isValidDate`). Menjamin unit kavling yang telah mencapai `akad == 1` atau tanggal akad terisi selalu berstatus `'Akad AJB'` (`badge-success`) sekalipun flag `akad_indent == 1` bernilai aktif (mengatasi edge case data riwayat seperti kavling 265 / MKDT 349).
  - `renderKavlingTimeline(mkdt, currentStatus)`: Merender milestone horizontal stepper progres transaksi kavling (Booking, Wawancara, SP3K, Akad Indent, Akad AJB) dengan connecting lines. Menerapkan skema Opsi B di mana jika status transaksi mencapai 'Akad AJB' selesai (`mkdt.akad == 1 || isValidDate(mkdt.akad_tgl)`), seluruh 5 tahapan stepper otomatis berstatus `is-completed` dengan icon centang `fas fa-check` lingkaran solid `#2056a4`.

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
- **Cross-Module Callers & Integrations:**
  - Terintegrasi dengan modul `TiketMasalah` dan `Transaksi`:
    - `KavlingRepository::addActiveTiketSelect`: data kavling dari `POST /siteplan/get/all` menyertakan atribut `active_tiket_id`, `active_tiket_prioritas`, dan `active_tiket_status` untuk kavling bermasalah aktif.
    - `OthersRepository::addActiveTiketSelect`: data fasum/others dari `POST /siteplan/get_others` menyertakan atribut `active_tiket_id`, `active_tiket_prioritas`, dan `active_tiket_status` untuk fasilitas non-kavling bermasalah aktif.
    - `OthersRepository::getAll()`: menyertakan kondisi `orWhere('act_tm.active_tiket_id IS NOT NULL', null, false)` sehingga item others berscope `masalah` otomatis termuat dan teranimasi pulse stroke serta badge peringatan `!` pada default canvas siteplan tanpa mengharuskan pengguna mencentang filter kategori 'Masalah'.
    - `SiteplanTiketOverlay` (`public/assets/js/siteplan/tiket-overlay.js`) dan `master.js` (`syncSiteplanTiketOverlay`): mengagregasikan masalah kavling dan others secara atomik dan merender `tiketOverlayLayer` pada peta KonvaJS.

## Change Log
- **2026-11-05 [`BUGFIX`] / [`UI_REPORT`] — Penyempurnaan Hierarki Status MKDT (Prioritas Akad AJB atas Indent) dan Opsi B Stepper Timeline Selesai:**
  - **Routes & Public Contracts:** Tidak ada perubahan endpoint atau kontrak backend (`siteplan/get/detail`).
  - **Controller / Service / Repo / Model:** Tidak berubah.
  - **View / CSS / JS:**
    - `public/assets/js/siteplan-detail-modal.js`:
      - Memperbaiki `resolveMkdtStatusInfo(m)` dengan hierarki prioritas status transaksi yang presisi: Batal (`is_batal == 1 || status_mkdt === 'Batal'`) -> Akad AJB (`akad == 1 || isValidDate(akad_tgl) || (status_mkdt === 'Akad' && akad_indent != 1)`) -> Akad Indent (`(akad_indent == 1 || isValidDate(akad_indent_tgl) || is_ajb === 'PPJB') && akad != 1`) -> SP3K -> Wawancara -> Booking. Menjamin jika `m.akad == 1` selalu diselesaikan sebagai status `'Akad AJB'` (`badge-success`) meskipun `m.akad_indent == 1` (solusi tuntas edge case kavling 265 / MKDT 349).
      - Mengimplementasikan Opsi B pada `renderKavlingTimeline(mkdt, currentStatus)`: saat status transaksi mencapai 'Akad AJB' selesai (`mkdt.akad == 1 || isValidDate(mkdt.akad_tgl)`), seluruh 5 tahapan stepper (Booking, Wawancara, SP3K, Akad Indent, Akad AJB) otomatis berstatus `is-completed` dengan icon centang `fas fa-check` lingkaran solid biru `#2056a4`.
    - `tests/js/siteplan-status-timeline.test.js`:
      - Menambahkan suite pengujian unit Jest dengan 16 skenario komprehensif (Kasus A s/d G dan edge cases) untuk memvalidasi resolusi status MKDT dan stepper timeline (100% passing).
  - **Deprecations:** Tidak ada.
- **2026-11-04 [`FEATURE`] — Penambahan Agregasi Peringatan SP3K Expire & Rencana Akad pada SiteplanUrgentService:**
  - **Routes & Public Contracts:** Tidak berubah.
  - **Controller / Service / Repo / Model:** `app/Services/SiteplanUrgentService.php` menambahkan method publik `countMkdtDateAlerts(string $field, ?int $idProyek, ?string $fromDate, ?string $toDate)` dan memperkaya `getDashboardAlertCounts(?int $idProyek)` dengan metrik `sp3k_expire` dan `rencana_akad` untuk integrasi peringatan dini dashboard lintas departemen.
  - **View / CSS / JS:** Tidak berubah.
  - **Deprecations:** Tidak ada.
- **2026-11-04 [`FEATURE`] — Inklusi Otomatis Item Others Bermasalah Aktif pada Default Canvas Siteplan:**
  - **Routes & Public Contracts:** Tidak berubah (`POST /siteplan/get_others` / `SiteplanDataService::getOthers`).
  - **Controller / Service / Repo / Model:** `app/Repositories/OthersRepository.php` memperbarui method `getAll()` dengan menambahkan klausul `orWhere('act_tm.active_tiket_id IS NOT NULL', null, false)` di dalam grup filter `others.scope`. Entitas non-kavling/others (jalan, fasos, rth, ruko) yang dibuat dengan `scope = 'masalah'` kini otomatis terpilih dan tampil di kanvas default siteplan selama tiket masalahnya aktif (`act_tm.active_tiket_id IS NOT NULL`), memicu animasi pulsing stroke dan warning badge tanda seru (`!`) tanpa memerlukan filter manual kategori 'Masalah'. Unit test ditambahkan pada `tests/unit/KavlingCompositeQueryTest.php` (`testOthersScopeFilteringIncludesActiveTiket`).
  - **View / CSS / JS:** Tidak berubah; canvas KonvaJS dan `SiteplanTiketOverlay` langsung merender animasi pulse dan badge `!` untuk aset others tersebut.
  - **Deprecations:** Tidak ada.
- **2026-11-04 [`FEATURE`] — Dukungan Overlay Tiket Masalah pada Entitas Fasum/Others Siteplan:**
  - **Routes & Public Contracts:** Payload respons aset non-kavling / others (`POST /siteplan/get_others` / `SiteplanDataService::getOthers`) diperkaya dengan atribut tiket masalah aktif (`active_tiket_id`, `active_tiket_prioritas`, `active_tiket_status`).
  - **Controller / Service / Repo / Model:** `app/Repositories/OthersRepository.php` menambahkan method `addActiveTiketSelect(BaseBuilder $builder)` dengan LEFT JOIN derived table agregasi `tiket_masalah` (`ref_type = 'others'`, `status NOT IN ('selesai', 'batal', 'hold')`). Unit test ditambahkan pada `tests/unit/KavlingCompositeQueryTest.php` (`testOthersActiveTiketSelectAddsDerivedTableAndColumns`).
  - **View / CSS / JS:**
    - `public/assets/js/siteplan/tiket-overlay.js`: Penambahan dukungan multi-tipe `refType = 'others'` dan `refType = 'kavling'`, ID prefixing terisolasi (`#tiket-overlay-others{id}`, `#tiket-badge-others{id}`), safe centroid calculation untuk poligon fasum, dan evaluasi visibilitas layer terhadap node target `#others{id}`.
    - `public/assets/js/siteplan/master.js`: Penambahan state tersentralisasi `siteplanProblemItems = { kavling: [], others: [] }` dan fungsi agregasi atomik `syncSiteplanTiketOverlay()` guna mengeliminasi race condition antara pemuatan data kavling dan aset fasum.
    - `app/Views/siteplan/master.php`: Penerapan cache busting dinamis `filemtime` pada pemuatan script `master.js`.
    - Suite unit test Jest di `tests/js/siteplan-tiket-overlay.test.js` diperluas dari 21 menjadi 25 unit tests (100% passing).
  - **Deprecations:** Tidak ada.
- **2026-11-04 [`FEATURE`] — Optimalisasi Tiket Masalah Overlay Siteplan (Cap 30 FPS, High-Contrast Stroke, dan Centroid Warning Badge):**
  - **Routes & Public Contracts:** Tidak berubah.
  - **Controller / Service / Repo / Model:** Tidak berubah.
  - **View / CSS / JS:** Pembaruan pada `public/assets/js/siteplan/tiket-overlay.js`:
    - Penambahan Frame Rate Capping ke 30 FPS (`TARGET_FPS = 30`, `FRAME_INTERVAL_MS = 33.33ms`) pada loop animasi KonvaJS guna menekan utilisasi GPU/CPU canvas.
    - Opsi 1 (Pulsing High-Contrast Stroke): Garis tepi putih `#ffffff` berdenyut dinamis (`strokeWidth` 1.0px s.d. 3.5px via `1.0 + 2.5 * opacity`) pada shape poligon kavling overlay.
    - Opsi 2 (Centroid Warning Badge `!`): Penambahan fungsi `calculateCentroid(points)` dan `createBadgeShape(Konva, options)` yang menggambar floating warning badge tanda seru (`!`) di tengah kavling dengan animasi skala halus (`scale` 0.85x s.d. 1.15x via `0.85 + 0.3 * opacity`).
    - Suite unit test Jest di `tests/js/siteplan-tiket-overlay.test.js` diperluas dari 14 menjadi 21 unit tests (100% passing).
  - **Deprecations:** Tidak ada.
- **2026-11-04 [`FEATURE`] — Penambahan Layer Animasi Tiket Masalah Overlay pada Siteplan KonvaJS:**
  - **Routes & Public Contracts:** Tidak ada penambahan endpoint baru. Payload data kavling pada `POST /siteplan/get/all` (`Siteplan::getAllKavling`) diperkaya dengan kolom tiket masalah aktif (`active_tiket_id`, `active_tiket_prioritas`, `active_tiket_status`).
  - **Controller / Service / Repo / Model:** `app/Repositories/KavlingRepository.php` menambahkan method `addActiveTiketSelect(BaseBuilder $builder)` dengan LEFT JOIN derived table agregasi `tiket_masalah` (`ref_type = 'kavling'`, `status NOT IN ('selesai', 'batal', 'hold')`). Unit test ditambahkan pada `tests/unit/KavlingCompositeQueryTest.php` (`testActiveTiketSelectAddsDerivedTableAndColumns()`).
  - **View / CSS / JS:** Asset baru `public/assets/js/siteplan/tiket-overlay.js` (modul UMD `SiteplanTiketOverlay`). View `app/Views/siteplan/master.php` mendaftarkan tag script `tiket-overlay.js`. Script `public/assets/js/siteplan/master.js` menginisialisasi `tiketOverlayLayer` pada Konva Stage di antara `siteplanLayer` dan `maskedLayer`, memanggil `SiteplanTiketOverlay.buildOverlayShapes()` pada saat kavling dimuat, dan membersihkan overlay via `clearSiteplanDataShapes()`. Unit test Jest `tests/js/siteplan-tiket-overlay.test.js` (14 test cases pass).
  - **Deprecations:** Tidak ada.
- **2026-11-03 [`UI_REPORT`] — Sinkronisasi Milestone Visual Siteplan untuk Status Akad Indent & Akad AJB:**
  - **Routes & Public Contracts:** Penyesuaian output kontrak `visual_rows[0]` (segmen MKDT) pada method `SiteplanVisualStatusService::buildRows` dan `appendVisualRows`. Segmen MKDT kini mengembalikan `config_name` `'Akad Subsidi'` atau `'Akad Komersil'` saat `akad_indent = 1` maupun `akad = 1`. Jika `akad_indent = 1` dan belum akad AJB (`akad = 0`), menyertakan marker `[['config_name' => 'Akad Indent', 'position' => 0.5]]` (garis aksen biru). Setelah `akad = 1` (Akad AJB), marker dihapus sehingga warna solid Akad tampil penuh.
  - **Controller / Service / Repo / Model:** Diperbarui `app/Services/SiteplanVisualStatusService.php` (`mkdtRow()`) untuk evaluasi boolean `$hasAkadAjb` dan `$hasAkadIndent`. Test suite diperbarui pada `tests/unit/SiteplanVisualStatusServiceTest.php` (72 tests passing).
  - **View / CSS / JS:** Tidak ada perubahan file (visual rendering poligon siteplan digerakkan oleh output backend).
  - **Deprecations:** Tidak ada.
- **2026-11-04 [`BUGFIX`] — Resolusi Nama Bank dari id_bank Tabel MKDT pada Modal Detail Kavling:**
  - **Routes & Public Contracts:** Payload endpoint `POST /siteplan/get/detail` (`Siteplan::get_detail`) diperkaya dengan kolom `nama_bank` yang bersumber dari tabel master `list_bank` via `LEFT JOIN list_bank ON list_bank.id = mkdt.id_bank`.
  - **Controller / Service / Repo / Model:** `app/Controllers/Siteplan.php` (`get_detail()`) menambahkan `list_bank.bank as nama_bank` pada select query `$d['mkdt']` dan menambahkan join `list_bank` berdasarkan `mkdt.id_bank`.
  - **View / CSS / JS:** `public/assets/js/siteplan-detail-modal.js`:
    - Mengubah populasi `#s-st_bank` pada bar status ringkasan modal detail agar memprioritaskan `mkdt.nama_bank || mkdt.bank || (mkdt.id_bank && mkdt.id_bank != '0' ? mkdt.id_bank : '-')`.
    - Mengubah input `#dt-st_bank` pada form Tab Status & Akad agar menampilkan nama bank yang sama.
  - **Deprecations:** Tidak ada.
- **2026-11-04 [`BUGFIX`] — Perbaikan Render Ikon Checkmark FontAwesome pada Card Progres Pembangunan:**
  - **Routes & Public Contracts:** Tidak berubah.
  - **Controller / Service / Repo / Model:** Tidak berubah.
  - **View / CSS / JS:**
    - `public/assets/js/siteplan-detail-modal.js`: Menghapus kelas `font-weight-bold` dari output `<i class="fas fa-check text-success"></i>` pada `setAmenityIcon()`. Penggunaan `font-weight-bold` di Vuexy sebelumnya memicu `font-weight: 500 !important`, yang tidak cocok dengan `@font-face` Font Awesome Solid (`font-weight: 900`), sehingga menyebabkan glyph checkmark hilang/tidak muncul.
    - `app/Views/siteplan/partials/modal_detail_styles.php`: Menambahkan rule eksplisit `#modal_detail .amenity-item i.fa-check` dengan `font-family: 'Font Awesome 5 Free' !important`, `font-weight: 900 !important`, dan `color: #28c76f !important` agar render checkmark selalu konsisten dan tampak jelas.
    - `app/Views/siteplan/partials/modal_detail.php`: Menyesuaikan placeholder default amenity menjadi `-` konsisten dengan desain.
  - **Deprecations:** Tidak ada.
- **2026-11-04 [`BUGFIX`] / [`UI_REPORT`] — Penyempurnaan Validitas Status MKDT, Elongated Badge, dan Tanggal Muted Vertikal:**
  - **Routes & Public Contracts:** Tidak ada perubahan endpoint atau kontrak backend (`siteplan/get/detail`).
  - **Controller / Service / Repo / Model:** Tidak berubah.
  - **View / CSS / JS:**
    - `app/Views/siteplan/partials/modal_detail.php`: Mengubah layout kolom Status Konsumen agar badge `#s-status_konsumen_badge` berada di atas dan tanggal `#s-status_konsumen_tgl` berada vertikal di bawahnya dengan style muted (`text-muted small mt-25 font-weight-normal`).
    - `app/Views/siteplan/partials/modal_detail_styles.php`: Menambahkan styling pill memanjang (`min-width: 110px`, `padding: 0.35rem 1.35rem`, `border-radius: 20px`) untuk `#s-status_konsumen_badge` beserta warna background/teks kontras per status (`badge-primary`, `badge-success`, `badge-warning`, `badge-danger`, `badge-info`, `badge-secondary`).
    - `public/assets/js/siteplan-detail-modal.js`:
      - Penambahan helper `isValidDate(val)` untuk menangani field MySQL `'0000-00-00'` agar tidak dievaluasi truthy di JS.
      - Penambahan fungsi `resolveMkdtStatusInfo(m)` untuk menentukan status aktif MKDT (Batal, Akad AJB, Akad Indent, SP3K, Wawancara, Booking) dan tanggal status yang valid.
      - Perbaikan fungsi `renderKavlingTimeline(mkdt, currentStatus)` agar status step aktif (`is-active`), completed (`is-completed`), dan connecting line horizontal sinkron dengan status aktif MKDT.
      - Perbaikan kondisi warning SP3K kedaluwarsa dengan `isValidDate(sp3k_tgl_exp)`.
  - **Deprecations:** Tidak ada.
- **2026-11-04 [`UI_REPORT`] — Redesign Layout Tab Ringkasan Modal Detail Kavling:**
  - **Routes & Public Contracts:** Tidak ada perubahan endpoint atau kontrak backend (`siteplan/get/detail`).
  - **Controller / Service / Repo / Model:** Tidak berubah.
  - **View / CSS / JS:**
    - `app/Views/siteplan/partials/modal_detail.php`: Redesign total tab `#dtt-summary` menjadi 3 baris utama:
      1. Banner warning "PERLU PERHATIAN" (SP3K H-21 & Tagihan jatuh tempo) yang otomatis tampil/sembunyi sesuai kondisi data.
      2. Card horizontal "Status Konsumen" dengan badge status aktif saat ini (Booking, Wawancara, SP3K, Batal, Akad Indent, Akad AJB) + tanggal status, Bank, Notaris, SP3K Terbit & Kadaluarsa.
      3. Row 2 kolom: Ringkasan Keuangan (Total Tagihan vs Sudah Bayar, Total Hasil Akad, Cair vs Belum Cair dual bar) dan Progres Pembangunan (Progres fisik bar %, Turun/Mulai/Selesai, dan checklist Site Amenities).
      4. Card Timeline Aktivitas Kavling dengan stepper horizontal bergaris penghubung (*connecting line*).
      5. Menjaga elemen legacy shims tersembunyi demi backward compatibility.
    - `app/Views/siteplan/partials/modal_detail_styles.php`: Penambahan styling responsif untuk `.detail-warning-banner`, `.detail-warning-card`, `.detail-summary-status-bar`, `.kavling-timeline`, `.kavling-timeline-item`, connecting lines `::after`, dan adaptasi Dark Mode `.dark-layout`.
    - `public/assets/js/siteplan-detail-modal.js`: Penambahan helper `format_date_clean()`, `setAmenityIcon()`, `updateRingkasanHasilAkad()`, dan `renderKavlingTimeline()`. Memperbarui `loadSummary(r)` untuk mengevaluasi warning H-21 SP3K & tagihan jatuh tempo, menentukan status aktif dan tanggalnya, mengupdate progress bar keuangan dan pembangunan, serta merender stepper timeline bergaris.
  - **Deprecations:** Tidak ada deprecations; legacy DOM selector dipertahankan melalui hidden shims.
- `2026-11-03` [`SYNC`] — Sinkronisasi status & inventori modul melalui `module:sync`.
