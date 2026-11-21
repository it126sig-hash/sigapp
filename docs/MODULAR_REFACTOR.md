# Arsitektur & Refaktor Modular CI4 SIGAPP: Konteks, Keputusan, dan Standar Eksekusi

Dokumen ini adalah sumber kebenaran arsitektur untuk **Refaktor Modular Bertahap (Zero-Downtime)** pada aplikasi SIGAPP (CodeIgniter 4) sekaligus fondasi backend untuk migrasi ke **Vue 3 (`shadcn-vue`) + Capacitor Mobile App**. Baca dokumen ini bersama:
- [`docs/MIGRATION_PROGRESS.md`](file:///c:/Users/Salivs/Data/laragon/www/sigapp.dev/docs/MIGRATION_PROGRESS.md) & `.agents/rules/sigapp-modular-migration.md` — Papan status dan aturan refaktor modular CI4.
- [`sigapp-vue/docs/VUE_MOBILE_PRD.md`](file:///c:/Users/Salivs/Data/laragon/www/sigapp-vue/docs/VUE_MOBILE_PRD.md) — Product Requirements Document (PRD) migrasi Frontend Vue 3 & Capacitor.
- [`sigapp-vue/docs/VUE_MIGRATION_PROGRESS.md`](file:///c:/Users/Salivs/Data/laragon/www/sigapp-vue/docs/VUE_MIGRATION_PROGRESS.md) — Pelacak kesiapan API Vue-Ready dan progres menuju Cutover 80%.
- [`sigapp-vue/docs/API_SPA_CONTRACT.md`](file:///c:/Users/Salivs/Data/laragon/www/sigapp-vue/docs/API_SPA_CONTRACT.md) — Spesifikasi kontrak teknis REST API JSON, Bearer Token, dan Signed URL.

---

## 1. Masalah yang Diselesaikan

1. **Struktur Flat Legacy:** Pada awalnya, seluruh file `Controllers/`, `Services/`, `Repositories/`, dan `Models/` bercampur dalam satu direktori datar tanpa pengelompokan domain bisnis yang tegas.
2. **Pelanggaran Layering:** Sebagian controller legacy masih memanggil `new *Model()`, `model()`, `db_connect()`, atau `$this->db->table()` secara langsung, serta mencampuradukkan rendering HTML view dengan endpoint JSON AJAX.
3. **Konteks Agen AI Meluber (*Context Bleed*):** Tanpa batas wilayah (*Module Boundary*) dan inventori file eksplisit, agen AI maupun developer rentan memodifikasi file lintas departemen yang berujung pada regresi tidak terduga.
4. **Kebutuhan Zero-Downtime:** Sistem SIGAPP digunakan secara aktif setiap hari oleh berbagai departemen (Keuangan, MKDT, Planning, Legal, Promosi, Produksi). Refaktor tidak boleh memutus URL/endpoint lama, job cron, atau panggilan service lintas modul yang sedang berjalan.

---

## 2. Prinsip Desain: Karpathy Simplicity First & Zero-Downtime

### 2.1 Mengapa Layer Ber-Subfolder (Bukan `app/Modules/<Nama>/Contracts/` & `DTOs/`)?
Mengikuti pedoman **Karpathy Simplicity First** (*minimum code that solves the problem, nothing speculative*):
- **Tanpa Boilerplate `Contracts/` & `DTOs/` Ekstra:** Membuat puluhan interface kosong (`Contracts/`) dan kelas pembungkus data (`DTOs/`) untuk aplikasi monolit CodeIgniter 4 hanya menggandakan jumlah file dan memperbesar beban konteks tanpa manfaat nyata.
- **Kontrak Aktual yang Langsung Terbaca:**
  - **Kontrak Backend Antar-Modul:** Adalah **Public Methods** pada kelas `App\Services\<Modul>\*Service` (mengembalikan array terstruktur atau tipe primitif yang jelas, bukan instance `Model` mentah).
  - **Kontrak Frontend (Browser/AJAX & Vue/Capacitor — *Vue-Ready API Contract*):** Adalah **Public REST API Endpoints** (`/api/<modul>/...`) yang ditangani oleh `App\Controllers\Api\<Modul>\*Controller` dengan format JSON standar `{ success, messages, data }`. Mengacu pada [`sigapp-vue/docs/API_SPA_CONTRACT.md`](file:///c:/Users/Salivs/Data/laragon/www/sigapp-vue/docs/API_SPA_CONTRACT.md), setiap endpoint API wajib mengembalikan objek data JSON murni (bebas dari string HTML `<button onclick>` / `<span class="badge">` khusus jQuery DataTables) serta menyediakan akses API terhadap *initial page props* (opsi dropdown, konfigurasi pajak, dan *permission flags*).
- **Kompatibilitas Penuh Autoloader CI4:** Menggunakan subfolder di dalam struktur standar CI4 (`app/Controllers/Web/<Modul>/`, `app/Controllers/Api/<Modul>/`, `app/Services/<Modul>/`, `app/Repositories/<Modul>/`) langsung bekerja secara native dengan PSR-4 namespace `App\` tanpa perlu merombak `Config\Autoload::$psr4` setiap kali modul dipindahkan.

### 2.2 Zero-Downtime Shims
Agar pemindahan kelas ke subfolder `<Modul>/` tidak mematahkan kode pemanggil lama maupun AutoRoute legacy:
- File controller, service, atau repository di lokasi lama (misal `app/Controllers/Proyek.php` atau `app/Services/ProyekService.php`) **wajib dipertahankan** sebagai kelas *shim/delegator* tipis yang melakukan `extends` atau mendelegasikan panggilan ke kelas modular baru (`App\Controllers\Web\Proyek\ProyekController` atau `App\Services\Proyek\ProyekService`).
- Seluruh file shim sebuah modul wajib didaftarkan pada properti `'shims'` di `app/Libraries/ModuleManager/ModuleCatalog.php` agar terpetakan secara resmi dan tidak dianggap sebagai *unmapped files*.

---

## 3. Struktur Arsitektur Modular Aktual

Setiap modul bisnis di SIGAPP diorganisasikan ke dalam struktur layer ber-subfolder berikut:

```text
app/
├── Controllers/
│   ├── Web/<Modul>/          # Namespace: App\Controllers\Web\<Modul>
│   │   └── <Modul>Controller.php   (Hanya merender HTML View & redirect)
│   └── Api/<Modul>/          # Namespace: App\Controllers\Api\<Modul>
│       └── <Modul>Controller.php   (Extends BaseApiController, khusus endpoint JSON /api/...)
├── Services/
│   └── <Modul>/              # Namespace: App\Services\<Modul>
│       └── <Modul>Service.php      (Logika bisnis, validasi, transaksi DB, & kontrak publik antar-modul)
├── Repositories/
│   └── <Modul>/              # Namespace: App\Repositories\<Modul>
│       └── <Modul>Repository.php   (Seluruh query Builder/JOIN, filter DataTables, & akses Model)
├── Models/                   # Namespace: App\Models
│   └── <Entity>Model.php           (Definisi tabel, allowedFields, & konfigurasi entitas CI4)
└── Views/
    └── <modul_folder>/             (Template tampilan spesifik modul)

public/assets/
├── css/<modul>.css           # Stylesheet eksternal modul (dimuat dengan filemtime())
└── js/<modul>.js             # Script frontend modul (dimuat dengan filemtime())

docs/modules/<Modul>/
└── MODULE.md                 # Kontrak & inventori modul (disinkronkan via php spark module:sync)
```

### Aturan Ketergantungan Layer (Layering Rules)
| Layer | Tanggung Jawab Utama | Boleh Memanggil | Larangan Keras |
|---|---|---|---|
| **Web Controller** (`Web/<Modul>`) | Otorisasi halaman, menyiapkan data awal tampilan, `return view(...)` | `Services/<Modul>`, `Services` modul lain | Dilarang query DB (`db_connect()`, `$this->db`, `new *Model()`, `model()`). |
| **API Controller** (`Api/<Modul>`) | Menerima request AJAX/REST (Web & Vue/Capacitor), memanggil Service, dan **WAJIB mengembalikan Full JSON murni (`RAW_JSON_READY`)** untuk seluruh skenario (sukses `200/201` maupun error `400/401/403/404/422/500`) dengan envelope `{ success, messages, data, errors? }` sesuai [`sigapp-vue/docs/API_SPA_CONTRACT.md`](file:///c:/Users/Salivs/Data/laragon/www/sigapp-vue/docs/API_SPA_CONTRACT.md) (termasuk endpoint `/meta` untuk initial props/permissions) | `Services/<Modul>`, `Services` modul lain | **Dilarang keras** merender HTML halaman utuh, melakukan HTTP redirect `302` ke halaman HTML, melempar HTML exception, menyisipkan string HTML `<button onclick>`/`<span class="badge">` pada response JSON baru (gunakan `?format=raw` jika menjaga kompatibilitas DataTables lama), atau memanggil Model/DB langsung. |
| **Service** (`Services/<Modul>`) | Aturan bisnis, sanitasi angka (`cleanNumeric`), transaksi DB (`db_connect()` hanya untuk `transBegin/Commit/Rollback`), pemicu notifikasi/audit | `Repositories/<Modul>`, `Services` modul lain, Shared Infrastructure | Dilarang menulis query SQL/Builder kompleks (serahkan ke Repository). |
| **Repository** (`Repositories/<Modul>`) | Semua operasi baca/tulis database, JOIN multi-tabel, agregasi, dan filter DataTables | `Models` milik modul, Query Builder (`$this->db->table()`) | Dilarang menyimpan logika HTTP request/response atau session UI. |
| **Model** (`app/Models`) | Representasi tabel database tunggal (`$table`, `$primaryKey`, `$allowedFields`) | Framework `CodeIgniter\Model` | Dilarang diakses langsung dari Controller. |

---

## 4. Batas Wilayah Modul (Module Boundary) & Shared Resources

Definisi resmi ke-16 modul bisnis beserta daftar file dan tabelnya dikelola secara terpusat di [`app/Libraries/ModuleManager/ModuleCatalog.php`](file:///c:/Users/Salivs/Data/laragon/www/sigapp.dev/app/Libraries/ModuleManager/ModuleCatalog.php) dan didokumentasikan di `docs/modules/<Modul>/MODULE.md`.

1. **Kepemilikan Tabel (`Owner Tables`):**
   - Hanya modul pemilik (`Owner Table(s)`) yang diizinkan melakukan operasi tulis (**INSERT, UPDATE, DELETE**) pada tabel miliknya.
   - Modul lain hanya boleh membaca tabel bersama (`Shared Tables`) untuk keperluan JOIN tampilan/laporan atau memutakhirkan data melalui **Public Service Methods** milik modul pemilik.
2. **Aturan Rute Global (`app/Config/Routes.php`):**
   - Saat merefaktor suatu modul, agen **diizinkan** memodifikasi `app/Config/Routes.php` untuk menambahkan grup rute eksplisit modul target (`Web\<Modul>` dan `Api\<Modul>`), dengan syarat rute legacy tetap dipertahankan sebagai alias/fallback (*Zero-Downtime*).
3. **Aturan Shared Views & Modals (`app/Views/siteplan/`, `app/Views/master/`, `app/Views/setting/`):**
   - Beberapa folder view digunakan bersama oleh lebih dari satu modul (misalnya partial modal departemen di `app/Views/siteplan/partials/` atau halaman master di `app/Views/master/`).
   - Agen yang sedang merefaktor modul X **hanya boleh** memodifikasi file view/partial yang secara spesifik melayani domain modul X, dan dilarang mengubah partial milik departemen/modul lain.
4. **Shared Infrastructure (`ModuleCatalog::getSharedInfrastructure()`):**
   - Komponen lintas sistem seperti `BaseController`, `BaseApiController`, `FileController`, `FileAccessService`, `StorageService`, dan `ImageThumbnailService` adalah infrastruktur bersama yang boleh dikonsumsi oleh seluruh modul tanpa dianggap melanggar batas modul.

---

## 5. CLI Spark Tooling Resmi (ModuleManager)

SIGAPP dilengkapi 4 perintah CLI Spark resmi di `app/Commands/` yang didukung oleh `app/Libraries/ModuleManager/` (`ModuleCatalog`, `ModuleScanner`, `ModuleDocGenerator`):

### 5.1 `php spark module:map`
- **Fungsi:** Memindai seluruh codebase (`Controllers`, `Services`, `Repositories`, `Models`, `Views`, `Assets JS`, dan `Routes.php`), mencocokkannya dengan `ModuleCatalog` (termasuk daftar `shims` dan `sharedInfrastructure`), lalu menyimpan peta JSON lengkap ke `writable/modules/module_map.json`.
- **Indikator Sukses:** Wajib menghasilkan **0 file belum terpetakan (*unmapped files*)**. Jika ada file baru yang belum terdaftar, tambahkan ke modul yang sesuai di `ModuleCatalog.php`.

### 5.2 `php spark module:status`
- **Fungsi:** Menampilkan papan skor kesiapan (*Readiness Dashboard*) untuk seluruh **16 modul bisnis** SIGAPP di terminal.
- **Rumus Skor Kesiapan (0–100%):**
  1. **Service Layer (20%):** `20%` jika seluruh service utama modul sudah berada di subfolder `app/Services/<Modul>/` (`10%` jika masih flat di `app/Services/`).
  2. **Repository Layer (20%):** `20%` jika seluruh repository utama modul sudah berada di subfolder `app/Repositories/<Modul>/` (`10%` jika masih flat di `app/Repositories/`).
  3. **Controller Web/API Split (20%):** `20%` jika seluruh controller modul sudah dipisah ke subfolder `app/Controllers/Web/<Modul>/` dan `app/Controllers/Api/<Modul>/` (`10%` jika baru sebagian).
  4. **Clean Explicit Routes & Zero Controller Violations (20%):** `20%` jika seluruh rute terdaftar eksplisit (0 AutoRoute legacy pada controller utama) **DAN** terdapat **0 pelanggaran layer di Controller** (tidak ada `db_connect()`, `Database::connect()`, `$this->db->table()`, `new *Model()`, atau `model()` di Controller).
  5. **Documentation Initialized (20%):** `20%` jika `docs/modules/<Modul>/MODULE.md` tersedia.
- **Syarat Status `REFACTORED`:** Modul hanya mendapatkan status `REFACTORED` apabila mencapai **skor >= 80%** serta ketiga layer utama (`Controllers`, `Services`, `Repositories`) telah berada di dalam subfolder modular `<Modul>/`.

### 5.3 `php spark module:sync [<NamaModul> | --all] [--check-only]`
- **Fungsi:** Menghasilkan atau memperbarui dokumen kontrak `docs/modules/<NamaModul>/MODULE.md` secara otomatis berdasarkan hasil pemindaian kode aktual.
- **Pelestarian Seksi Manual:** `ModuleDocGenerator` menjaga 4 seksi manual (`## Purpose`, `## Out of scope`, `## Invariants`, dan `## Notes`) agar tidak pernah tertimpa, sembari memperbarui bagian otomatis:
  - Status, Readiness Score, & Timestamp/Commit Hash terakhir
  - Owner Tables & Shared Tables
  - Public HTTP Endpoints & Routes Inventory (bebas duplikasi)
  - Public PHP Services (Backend Contract)
  - File Inventory (`Controllers`, `Services`, `Repositories`, `Models`, `Views`, `JavaScript`, dan `Zero-Downtime Shims`)
  - Readiness Checklist (6 poin termasuk `Zero Controller Layer Violations` beserta rincian pelanggaran jika ada)
- **Opsi `--check-only`:** Memvalidasi status kesiapan tanpa menulis ulang file `MODULE.md`.

### 5.4 `php spark module:deadcode [<NamaModul> | --all]`
- **Fungsi:** Mengintegrasikan hasil pemindaian modul dengan database **CodeGraph** (`.codegraph/graph.db`) untuk mendeteksi method pada `Services` dan `Repositories` yang memiliki `0 callers` (kandidat dead code yang perlu diaudit dan dibersihkan saat refaktor).

---

## 6. Struktur Dokumen `docs/modules/<Modul>/MODULE.md`

Setiap modul wajib memiliki satu dokumen `MODULE.md` tunggal (tidak ada lagi file terpisah `module.generated.md` atau `.module-baseline.json`). Struktur baku `MODULE.md` adalah:

```markdown
# Module: <NamaModul>

**Status:** `REFACTORED` | `MAPPED`
**Readiness Score:** `100/100`
**Last Synced:** `<YYYY-MM-DD HH:MM:SS>` @ `<git-hash>`

## Purpose
<Deskripsi manual tanggung jawab bisnis utama modul>

## Out of scope
<Hal-hal yang sengaja tidak ditangani oleh modul ini>

## Invariants
<Aturan bisnis & teknis mutlak yang tidak boleh dilanggar>

## Database Tables
### Owner Table(s)
### Shared Tables (Dibaca dari modul lain)

## Contracts
### A. Public HTTP Endpoints & Routes Inventory
### B. Public PHP Services (Backend Contract Antar-Modul)

## File Inventory
### Controllers / Services / Repositories / Models / Views / JavaScript / Zero-Downtime Shims

## Readiness Checklist
- [x] Has Service Layer
- [x] Has Repository Layer
- [x] Web & API Controllers Separated
- [x] Explicit Routes Defined
- [x] Zero Controller Layer Violations
- [x] MODULE.md Initialized

## Dead Code Candidates
## Notes
```

---

## 7. Alur Kerja Standar Refaktor Modul (1 Sesi = 1 Modul)

1. **Fase 1 — Eksplorasi & Perencanaan (`sigapp-explorer` + `Master Orchestrator`):**
   - Baca `docs/MIGRATION_PROGRESS.md`, `docs/modules/<Modul>/MODULE.md`, dan `.agents/rules/sigapp-modular-migration.md`.
   - Petakan seluruh *callers* lintas file via `codegraph_explore` dan jalankan `php spark module:deadcode <Modul>`.
   - Susun *Implementation Plan* dan minta persetujuan User.
2. **Fase 2 — Implementasi Backend & Frontend (`sigapp-engineer` + `sigapp-frontend`):**
   - Pindahkan dan pisahkan controller ke `app/Controllers/Web/<Modul>/` dan `app/Controllers/Api/<Modul>/`.
   - Pastikan **setiap endpoint di `app/Controllers/Api/<Modul>/` mengembalikan Full JSON murni (`RAW_JSON_READY`)** melalui `BaseApiController` (`{ success, messages, data, errors? }`) baik untuk response sukses maupun error (`400/401/403/404/422/500`), bebas dari string HTML DataTables (gunakan `?format=raw` bila endpoint masih dipakai DataTables legacy), serta sediakan endpoint `/meta` untuk opsi dropdown & permission flags.
   - Pindahkan/ekstrak logika bisnis ke `app/Services/<Modul>/` dan seluruh query DB/Model ke `app/Repositories/<Modul>/` hingga **0 Controller Layer Violations**.
   - Pasang *Zero-Downtime Shims* untuk file lama dan daftarkan di `ModuleCatalog.php`.
   - Daftarkan rute eksplisit di `app/Config/Routes.php` tanpa menghapus rute legacy.
   - Rapikan View & aset CSS/JS sesuai standar `sigapp-ui-acuan`.
3. **Fase 3 — Verifikasi & Audit QA (`sigapp-qa`):**
   - Jalankan `php spark module:map` (pastikan 0 unmapped files) dan `php spark module:status` (pastikan status modul `REFACTORED` dengan skor $\ge 80\%$).
   - Verifikasi bahwa seluruh endpoint `/api/<modul>/*` mengembalikan **100% Full JSON** (bukan HTML/redirect `302` dan bebas HTML debt pada mode raw).
   - Jalankan pengujian E2E Playwright menggunakan akun departemen sesuai skill `sigapp-local-testing`.
4. **Fase 4 — Sinkronisasi Dokumen & Ledger (`sigapp-docs`):**
   - Jalankan `php spark module:sync <Modul>` untuk memperbarui `docs/modules/<Modul>/MODULE.md`.
   - Perbarui status modul menjadi `COMPLETED` di `docs/MIGRATION_PROGRESS.md`.
   - Pastikan `git status` bersih dan hanya mencakup file modul target serta dokumen terkait.
