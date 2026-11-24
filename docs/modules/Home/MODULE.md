# Module: Home

**Status:** `MAPPED`  
**Readiness Score:** `40/100`  
**Last Synced:** `2026-11-24 13:40:19` @ `84f8fcb`  

## Purpose
Dashboard eksekutif dan operasional multi-departemen yang menyajikan ringkasan metrik lintas divisi (MKDT/Sales, Produksi, Legal, Planning, Keuangan), antrean tindakan cepat (*Action Queue*), peringatan dini (*Alerts*) berbasis wewenang role, grafik perbandingan tren transaksi booking & akad, serta pemantauan log aktivitas sistem.

## Out of scope
- Mutasi langsung data master atau transaksi entitas bisnis (seluruh mutasi didelegasikan ke modul spesialis masing-masing: `MKDT`, `Keuangan`, `Produksi`, `Legal`, `BPB`, `MGM`, `SalesExternal`).
- Penjurnalan akuntansi keuangan atau transaksi kas masuk/keluar secara langsung.

## Invariants
- **Role-Aware Financial Guards**: Data nominal finansial (`finance` metrics & financial alerts seperti tagihan overdue, tagihan due, cashout subkon) dilindungi secara ketat di backend; hanya dapat diakses oleh pengguna dengan role Admin (1), Keuangan (3), MKDT (4), Sales (8), dan Direksi (9). Jika wewenang tidak terpenuhi (`can_view_finance = false`), data finansial di-*strip* total dari payload JSON untuk mencegah kebocoran data sensitif (*data leakage*).
- **Role Department Tab Scoping**: Hak alih tab antar-departemen (`can_switch_department`) dibatasi secara eksklusif untuk Admin (1) dan Direksi (9). Pengguna dengan peran departemen tunggal (Keuangan, MKDT, Produksi, Legal, Planning) secara otomatis diarahkan ke tab departemen kerja masing-masing dan hanya melihat tab yang diizinkan. Tab awal default bagi Admin/Direksi adalah "MKDT & Sales" (`#tab-mkdt_sales`) pasca-penghapusan tab ringkasan eksekutif redundan.
- **Actionable & Clickable Routing**: Setiap item pada antrean tindakan (*Action Queue*) dan peringatan dini (*Alerts*) wajib menyertakan URL navigasi internal yang valid (`bpb`, `member-get-member`, `fee-sales`, `tagihan/list`, `siteplan/view`, `keuangan`, `produksi`, `legal`, dll.) sehingga penanggung jawab dapat langsung menindaklanjuti permohonan atau masalah dalam satu klik.
- **Card Metrics Integrity**:
  - *Card 2 (Sales Periode)*: Menyajikan 6 metrik riil siklus penjualan (Booking, Wawancara, SP3K, Batal, Akad Indent, dan Akad AJB) secara faktual sesuai rentang tanggal aktif tanpa kalkulasi persentase konversi estimatif/progress bar.
  - *Card 4 (Produksi Lapangan)*: Menyajikan 3 metrik esensial lapangan (Sedang Dibangun `dash_prod_active`, Terlambat `st_telat`, dan Selesai 100% `st_100persen`) yang mencerminkan status fisik konstruksi unit riil.
- **Actionable Problem Tickets**: Tabel tiket masalah mengintegrasikan waktu & lokasi ramah baca bahasa Indonesia, pratinjau keterangan berformat HTML (clickable modal detail), serta tombol aksi terhubung ke Modal Detail Tiket dan pin lokasi spasial di Siteplan berbekal metadata koordinat (`ref_type`, `ref_id`, `id_proyek`, `target_cluster_id`).
- **Strict Clean View Architecture**: View `app/Views/home/dashboard.php` bebas dari inline styling atau inline scripting; tata letak visual diatur oleh `public/assets/css/dashboard.css` (terisolasi dalam lingkup `.dashboard-page`, warna identitas SIGAPP `#2057a3`, badge semantik, navigasi tab 1 baris mobile responsif) dan interaktivitas oleh `public/assets/js/dashboard.js`. Dependensi SweetAlert2 dan aset vendor dimuat secara terstandarisasi.

## Database Tables
### Owner Table(s)
- *(Tidak memiliki tabel database independen)*

### Shared Tables (Dibaca dari modul lain)
- `mkdt`
- `kavling`
- `jalan`
- `cluster`
- `proyek`
- `keuangan`
- `produksi`
- `legal`
- `spk_bangunan`
- `bpb_requests`
- `referral_bonuses`
- `fee_sales_pengajuan`
- `kavling_request`
- `history_log`
- `tiket_masalah`
- `others`

## Contracts
### A. Public HTTP Endpoints & Routes Inventory
| Method | URI / Pattern | Tipe | Handler | Status Migrasi | Target API / Catatan |
|---|---|---|---|---|---|
| `GET` | `/dashboard` | `Explicit` | `Home::dashboard` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `GET` | `/` | `Explicit` | `Home::dashboard` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `GET` | `/loadaktivitas` | `Explicit` | `Home::loadAktivitas` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `/get-dashboard` | `Explicit` | `Home::getDashboard` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `POST` | `/home/getMenuItemsJson` | `Explicit` | `Home::getMenuItemsJson` | 🟡 LEGACY | Rute eksplisit ke flat controller lama |
| `ANY` | `/home/index` | `AutoRoute` | `Home::index` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/home/getdata` | `AutoRoute` | `Home::getData` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/home/getbookingakad` | `AutoRoute` | `Home::getBookingAkad` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/home/generatemenu` | `AutoRoute` | `Home::generateMenu` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/home/getmenubtn` | `AutoRoute` | `Home::getMenuBtn` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/home/getkop` | `AutoRoute` | `Home::getKop` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |
| `ANY` | `/home/gethak` | `AutoRoute` | `Home::getHak` | 🟡 LEGACY (AutoRoute) | Implisit via AutoRoute convention (belum didaftarkan eksplisit) |

### B. Public PHP Services (Backend Contract Antar-Modul)
#### `DashboardService.php`
- `getRoleContext()`
- `getDepartmentMetrics(int $idProyek, string $sdate, string $edate)`
- `getActionQueue(int $idProyek, array $roleContext)`
- `buildRoleAwareAlerts(array $alertCounts, array $production, array $deptMetrics, array $finance, array $roleContext)`

## File Inventory
### Controllers (1)
- `app/Controllers/Home.php` (624 baris, 11 public methods: `index()`, `dashboard()`, `getData($id_proyek, $sdate, $edate)`, `getDashboard()`, `getBookingAkad($field = 'booking_tgl', $id_proyek = null, $thn = null, $bln = null)`, `loadAktivitas()`, `generateMenu($menu)`, `getMenuBtn()`, `getMenuItemsJson()`, `getKop()`, `getHak($id_user, $nama_hak = null)`)

### Services (1)
- `app/Services/DashboardService.php` (577 baris, 4 public methods)

### Repositories (0)

### Models (0)

### Views (1 folder)
- `app/Views/home` (1 file: `dashboard.php`)

### JavaScript (1 file)
- `public/assets/js/dashboard.js` (1185 baris)

## Key Functions
- **Controller (`app/Controllers/Home.php`):**
  - `dashboard()`: Menginisialisasi konteks wewenang peran pengguna (`roleContext`) dari `DashboardService::getRoleContext()` dan merender view dashboard terintegrasi.
  - `getDashboard()`: Menerima request AJAX parameter proyek dan tanggal (`sdate`, `edate`, `tahun`); memanggil `DashboardService` untuk memproses ringkasan transaksi, metrik 4 departemen, antrean tugas personal, daftar peringatan kritis dengan proteksi data finansial (`can_view_finance`), serta ringkasan tiket masalah terbaru via `getDashboardTiketMasalahSummary()`.
  - `getData($id_proyek, $sdate, $edate)`: Menghitung metrik transaksi agregat periode terpilih: metrik penjualan (`jumlah_booking`, `jumlah_akad`, `jumlah_sp3k`, `jumlah_batal`, `jumlah_wawancara`, `jumlah_akad_indent`, `jumlah_akad_ajb`), metrik konstruksi fisik (`jumlah_pembangunan`, `jumlah_bangunan_selesai`, `jumlah_bangunan_telat`), serta status booking aktif/batal.
  - `getDashboardTiketMasalahSummary(int $id_proyek)`: Menarik 5 tiket masalah aktif terbaru dengan JOIN polimorfik ke kavling atau others beserta jalan/cluster, mengekspos atribut navigasi spasial (`target_cluster_id`, `ref_type`, `ref_id`, `id_proyek`, `lokasi`, `keterangan`, `status`, `prioritas`).
  - `loadAktivitas()`: Menyediakan log feed audit aktivitas terkini dengan paging `offset` melalui `Notif::getActivity()`.
- **Service (`app/Services/DashboardService.php`):**
  - `getRoleContext()`: Melakukan resolusi RBAC pengguna aktif (`user_id`, `group_ids`), menentukan izin akses data finansial (`can_view_finance`), hak beralih departemen (`can_switch_department`), tab awal (`default_tab` default: `mkdt_sales` untuk Admin/Direksi), serta tab yang dapat diakses (`visible_tabs`).
  - `getDepartmentMetrics(int $idProyek, string $sdate, string $edate)`: Mengagregasikan metrik operasional 4 divisi utama:
    - *MKDT / Sales*: Booking, akad, SP3K terbit, berkas masuk, wawancara, batal unit, dan rasio konversi.
    - *Produksi*: Bangunan dalam pengerjaan, unit selesai 100%, bangunan terlambat dari target, komplain aktif, dan unit belum SPK.
    - *Legal*: Status sertifikat induk, splitzing, sertifikat per kavling, izin PBG, dan proses AJB.
    - *Planning*: Total luas lahan rencana, efektivitas siteplan, kavling terencana, dan sisa lahan pengembangan.
  - `getActionQueue(int $idProyek, array $roleContext)`: Mengumpulkan antrean tugas segera yang dipersonalisasi sesuai role:
    - *BPB Menunggu TTD*: Pengajuan nota dinas/belanja yang membutuhkan tandatangan elektronik user yang sedang login.
    - *BPB Bagian Umum*: Pengajuan yang telah disetujui dan menunggu pencairan/belanja bagian umum.
    - *Bonus MGM (Keuangan)*: Klaim referral bonus yang berstatus diajukan ke bagian keuangan.
    - *Bonus MGM (Sales)*: Bonus referral yang memenuhi syarat atau dikonfirmasi dan perlu diajukan.
    - *Fee Sales*: Pengajuan komisi sales yang berstatus diajukan atau perlu perbaikan (ditolak).
    - *Permohonan Kavling*: Request tambah/ubah nomor kavling yang berstatus pending review.
  - `buildRoleAwareAlerts(array $alertCounts, array $production, array $deptMetrics, array $finance, array $roleContext)`: Membangun kartu peringatan dini operasional dan finansial (tagihan lewat tempo mengarah ke `/tagihan/list`, tagihan tempo 7 hari mengarah ke `/tagihan/list`, termin subkon tempo, bangunan telat, SP3K mendekati kedaluwarsa, rencana akad mendekati tanggal) yang dilengkapi tautan langsung (*clickable URL*) ke modul operasional terkait.
- **Integrasi Service Terkait (`app/Services/SiteplanUrgentService.php`):**
  - `countMkdtDateAlerts(string $field, ?int $idProyek, ?string $fromDate, ?string $toDate)`: Menghitung secara atomik jumlah transaksi kavling dengan peringatan tanggal batas (`sp3k_tgl_exp` dan `rencana_akad_tgl`) tanpa terpengaruh batas kuota tampilan urgent panel.

## Deprecated & Legacy Shims
- **Deprecated UI & Components:**
  - Tab `#tab-eksekutif` (Ringkasan Eksekutif) — **REMOVED**: Dihapus karena datanya redundan dengan kartu metrik ringkasan di baris atas; peran Admin dan Direksi dialihkan default ke tab `#tab-mkdt_sales`.
- **Deprecated Methods:**
  - `Home::buildDashboardAlerts`: Private helper pembuatan alert dipertahankan semata untuk kompatibilitas mundur; seluruh logika penyusunan alert telah dipindahkan ke `DashboardService::buildRoleAwareAlerts()`.
- **Legacy Routes:**
  - Route `GET /dashboard` (`Home::dashboard`) — **LEGACY**: Rute flat controller utama; disiapkan untuk migrasi ke namespace `Web\Home\DashboardController`.
  - Route `POST /get-dashboard` (`Home::getDashboard`) — **LEGACY**: Endpoint agregasi data dashboard; disiapkan untuk migrasi ke namespace `Api\Home\DashboardApiController`.
  - Route `GET /loadaktivitas` (`Home::loadAktivitas`) — **LEGACY**: Disiapkan untuk migrasi ke API terstruktur.
  - Route `POST /home/getMenuItemsJson` (`Home::getMenuItemsJson`) — **LEGACY**: Disiapkan untuk migrasi ke namespace `Api\Home\MenuApiController`.
  - Route `ANY /home/*` (AutoRoutes) — **DEPRECATED**: Seluruh pemanggilan otomatis berbasis URL controller flat dinonaktifkan secara bertahap demi rute eksplisit.

## Readiness Checklist
- [ ] Has Service Layer
- [ ] Has Repository Layer
- [ ] Web & API Controllers Separated
- [ ] Explicit Routes Defined
- [ ] Zero Controller Layer Violations
- [x] MODULE.md Initialized

> **Layer Violations Detected:**
> - `app/Controllers/Home.php`: db_connect(), $this->db->table(), new ProyekModel()

## Dead Code Candidates
_Jalankan `php spark module:deadcode Home` untuk mengaudit dead code kandidat._

## Notes
- Modul diinisialisasi dan didaftarkan pada `app/Libraries/ModuleManager/ModuleCatalog.php`.
- **Cross-Module Callers & Consumed Services:**
  - Mengonsumsi `App\Services\SiteplanUrgentService` untuk kalkulasi peringatan mendesak (`getDashboardAlertCounts`, `countMkdtDateAlerts`).
  - Mengonsumsi `App\Services\SiteplanMenuService` untuk render menu cepat berdasarkan role (`renderForRole`, `getActionItemsForList`).
  - Mengonsumsi `App\Services\FileAccessService` untuk penanganan berkas dan lampiran.
  - Memanggil model `App\Models\ProyekModel` untuk verifikasi data proyek aktif.
  - Berinteraksi dengan `App\Controllers\Notif` untuk log aktivitas audit (`getActivity`).
- **Frontend Styling & Global Display Scale:**
  - `public/assets/css/dashboard.css` terintegrasi dengan variabel CSS skala global dari `public/assets/css/display-scale.css` (`--sigapp-ui-scale`, `--sigapp-space-ratio`, dan pewarisan `rem` dari `--sigapp-font-scale`) untuk pemisahan independen *UI Density* dan *Font Scale*. *(Catatan: `dashboard.css` saat ini belum terdaftar di kunci `assets_css` pada `ModuleCatalog['Home']` sehingga belum tercantum otomatis di `## File Inventory`).*

### Audit Verifikasi Migrasi (2026-11-22)
- **Status Audit:** `INCOMPLETE` (Readiness Score `40/100`, Status `MAPPED`).
- **File Flat / Non-Modular:** `app/Controllers/Home.php` (623 baris; fat controller `> 300` baris dan mencampur `view('template', $data)` pada L40 dengan `setJSON()` pada L190, L569, L584, L594, L613) serta `app/Services/DashboardService.php` (576 baris); belum ada pemisahan `Web\Home` / `Api\Home` maupun Repository layer.
- **Pelanggaran Controller (`app/Controllers/Home.php`):** Memanggil `new ProyekModel()` (L23), `db_connect()` (L25), dan `$this->db->table(...)` langsung pada 17 titik (L44, L200, L232, L255, L270, L285, L301, L333, L374, L384, L452, L459, L491, L517, L536, L609, L617).
- **Pelanggaran Service (`app/Services/DashboardService.php`):** Menginisialisasi `Database::connect()` (L13) dan melakukan query `$this->db->table(...)` langsung pada 15 titik (L113, L140, L159, L180, L203, L224, L352, L370, L415, L454, L505, L519, L526, L533, L539) tanpa Repository.
- **Rute (`app/Config/Routes.php`):** 5 rute eksplisit masih mengarah ke flat `Home::*` (L40, L41, L79, L122, L123) dan `$routes->setAutoRoute(true)` (L9) masih menyisakan 7 AutoRoute pada `Home`.

## Change Log
- **`2026-11-24` [`UI_REPORT`] — Integrasi Pengaturan Scale Layar Global (`--sigapp-ui-scale`, `--sigapp-space-ratio`, `--sigapp-font-scale`) pada Dashboard:**
  - **Routes & Public Contracts:** Tidak ada perubahan rute HTTP maupun kontrak API/Service backend.
  - **Controller / Service / Repo / Model:** Tidak ada perubahan backend.
  - **View / CSS / JS:**
    - `public/assets/css/dashboard.css`: Mengintegrasikan variabel skala global pada seluruh komponen `.dashboard-page` dan `#modal-chart-kavling` — spacing/gap/padding/margin `rem` dikalikan `var(--sigapp-space-ratio, 1)` (agar kepadatan UI bergerak independen dari ukuran huruf) dan dimensi `px` (`.prod-utility-icon`, `.activity-feed`, dll.) dikalikan `var(--sigapp-ui-scale, 1)`, sementara ukuran teks mengikuti basis `rem` (`--sigapp-font-scale`).
  - **Deprecations:** Tidak ada.
- `2026-11-22` [`AUDIT`] — Audit verifikasi migrasi modular: status `INCOMPLETE` (Score 40/100, `MAPPED`), ditemukan file flat `Home.php` & `DashboardService.php`, pelanggaran layer DB/Model di controller & service, serta rute eksplisit/AutoRoute masih ke `Home::*`.
- **2026-11-04 [`FEATURE` / `UI_REPORT`] — Dashboard Cards, Action Alerts, & Problem Ticket Table Refinements:**
  - **Routes & Public Contracts:**
    - Perbaikan rute alert tindakan: alert tagihan overdue dan tagihan jatuh tempo kini mengarah tepat ke `/tagihan/list`.
    - Respon JSON `POST /get-dashboard` menyertakan metrik rinci penjualan riil (`wawancara`, `akad_indent`, `akad_ajb`) dan array tiket masalah komprehensif (`recent_tickets` dengan `target_cluster_id`, `ref_type`, `ref_id`, `id_proyek`).
  - **Controller / Service / Repo / Model:**
    - `app/Controllers/Home.php`:
      - `getData()` diperluas untuk mengagregasi `jumlah_wawancara`, `jumlah_akad_indent` (berdasarkan `akad_indent = 1`, `is_ajb = 'PPJB'`, atau keberadaan tanggal akad indent), dan `jumlah_akad_ajb` (`akad = 1` pada rentang tanggal).
      - `getDashboardTiketMasalahSummary()` diperbarui dengan JOIN polimorfik kavling/others dan jalan untuk menyertakan atribut navigasi spasial: `ref_type`, `ref_id`, `id_proyek`, dan `target_cluster_id` (COALESCE dari `jk.id_cluster` atau `jo.id_cluster`).
    - `app/Services/DashboardService.php`:
      - Penyesuaian `default_tab` Admin dan Direksi menjadi `mkdt_sales`.
  - **View / CSS / JS:**
    - `app/Views/home/dashboard.php`:
      - Card 2 (Sales Periode): Menghapus persentase konversi & progress bar; menampilkan 6 metrik grid (Booking, Wawancara, SP3K, Batal, Akad Indent, Akad AJB).
      - Card 4 (Produksi Lapangan): Menghapus progress bar estimatif; menampilkan 3 metrik lapangan (Sedang Dibangun, Terlambat, Selesai 100%).
      - Menghapus tab "Ringkasan Eksekutif" (`#tab-eksekutif`) sehingga antarmuka langsung menyajikan tab departemen spesifik ("MKDT & Sales").
      - Memuat vendor CSS & JS SweetAlert2 secara lokal di view dashboard.
      - Widget Tabel Tiket Masalah: Penggabungan kolom `Waktu & Lokasi` (format Indonesia ramah baca + icon map marker), kolom `Keterangan` HTML truncate 1-baris clickable membuka modal detail, dan kolom `Aksi` dengan tombol Detail Modal & tombol Lokasi Siteplan.
    - `public/assets/js/dashboard.js`:
      - Integrasi render metrik baru Card 2 dan Card 4.
      - Helper format waktu Indonesia dan render interaktif baris tiket masalah (SweetAlert2/Modal tiket + navigasi siteplan berkoordinat cluster/kavling).
    - `app/Views/template/menu.php`:
      - Penambahan atribut `data-framework="laravel" data-asset-path="<?= base_url('app-assets') ?>/"` pada tag `<body>`.
    - `public/assets/images/placeholder.jpg`:
      - Penyediaan asset placeholder lokal untuk mencegah HTTP 404 pada profil pengguna/avatar.
  - **Deprecations:**
    - Tab `#tab-eksekutif` dihapus (data telah terwakili pada kartu metrik utama baris atas).
- **2026-11-04 [`FEATURE` / `UI_REPORT`] — Role-Aware Dashboard Overhaul & Modern UI Architecture:**
  - **Routes & Public Contracts:**
    - Rute eksplisit `GET /` & `GET /dashboard` diperbarui dengan menginjeksi metadata otorisasi `roleContext` ke view.
    - Rute eksplisit `POST /get-dashboard` ditingkatkan dengan respon JSON komprehensif: data ringkasan proyek, metrik 4 departemen (`mkdt_sales`, `produksi`, `legal`, `planning`), antrean tindakan personal (`action_queue`), kartu peringatan clickable (`alerts`), serta guard proteksi finansial.
    - Rute eksplisit `GET /loadaktivitas` dioptimalkan untuk paging feed audit log real-time.
  - **Controller / Service / Repo / Model:**
    - Pembuatan service baru `app/Services/DashboardService.php` (`getRoleContext`, `getDepartmentMetrics`, `getActionQueue`, `buildRoleAwareAlerts`) yang memisahkan logika bisnis agregasi data dari Controller.
    - Injeksi `DashboardService` pada `app/Controllers/Home.php` dan penerapan guard `can_view_finance` (khusus role Admin, Keuangan, MKDT, Sales, Direksi) pada payload respon `getDashboard()`.
    - Penambahan method `countMkdtDateAlerts()` pada `app/Services/SiteplanUrgentService.php` guna mendukung peringatan SP3K kedaluwarsa dan rencana akad dalam rentang 7 hari ke depan.
  - **View / CSS / JS:**
    - Pembuatan stylesheet modular `public/assets/css/dashboard.css` (985 baris) dengan cakupan terisolasi `.dashboard-page`, skema warna identitas `#2057a3`, sistem kartu metrik, responsive 1-line mobile tabs dengan horizontal scrolling halus, serta alert urgency badges.
    - Pembuatan JavaScript engine `public/assets/js/dashboard.js` (1052 baris) yang menangani AJAX data fetching, render role-aware dashboard tiles, modal visualisasi grafik ApexCharts/Chart.js, dan interaktivitas tautan langsung ke modul target.
    - Refaktor menyeluruh `app/Views/home/dashboard.php` (705 baris) menghasilkan view bersih dan terstruktur tanpa inline style maupun inline script tag.
  - **Deprecations:**
    - Method private helper `Home::buildDashboardAlerts` di-deprecate untuk kompatibilitas mundur; seluruh logika dipusatkan pada `DashboardService::buildRoleAwareAlerts`.
- `2026-11-04` [`SYNC`] — Sinkronisasi status & inventori awal modul Home melalui `module:sync`.
