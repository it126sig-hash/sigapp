# Module: Legal

**Status:** `REFACTORED`  
**Readiness Score:** `100/100`  
**Last Synced:** `2026-11-11 13:35:00` @ `e658eba`  

## Purpose
Administrasi dan tracking perizinan lahan, sertifikat HGB/PBB/PBG/AJB, dan dokumen legalitas kavling maupun proyek perumahan.

## Out of scope
- Transaksi jual-beli kavling dan pemesanan konsumen (dikelola oleh modul `MKDT` dan `Transaksi`).
- Pembayaran, pemungutan pajak PPH/BPHTB, dan pencairan dana jaminan sertifikat (dikelola oleh modul `Keuangan`).
- Pembangunan fisik kavling dan tahapan konstruksi (dikelola oleh modul `Produksi`).

## Invariants
- Modul lain tidak boleh memanipulasi tabel pemilik (`legal`) secara langsung.
- Perubahan status legalitas, upload dokumen, dan manipulasi nominal pajak/biaya legal wajib melalui `App\Services\Legal\LegalService`.
- Nilai finansial (seperti nominal pajak PPH/BPHTB atau biaya sertifikat) wajib disanitasi menggunakan `cleanNumeric()` sebelum kalkulasi atau penyimpanan ke database.
- Akses dan mutasi data legalitas unit mengandalkan integritas relasi `kavling.id_legal`.

## Database Tables
### Owner Table(s)
- `legal` *(akses tulis eksklusif hanya untuk modul Legal)*

### Shared Tables (Dibaca dari modul lain)
- `kavling` *(membaca dan memperbarui kolom `id_legal`)*
- `mkdt` *(membaca informasi transaksi konsumen untuk kelengkapan legalitas)*
- `cluster` *(membaca nama cluster untuk filter dan DataTables)*
- `jalan` *(membaca nama jalan/blok kavling)*
- `proyek` *(membaca referensi proyek perumahan)*

## Contracts
### A. Public HTTP Endpoints & Routes Inventory
| Method | URI / Pattern | Tipe | Handler | Status Migrasi | Target API / Catatan |
|---|---|---|---|---|---|
| `GET` | `/legalitas` | `Explicit` | `App\Controllers\Web\Legal\LegalController::list_legalitas` | 🟢 MIGRATED | Halaman utama daftar status legalitas kavling |
| `GET` | `/privacy-policy` | `Explicit` | `App\Controllers\Web\Legal\LegalPageController::privacyPolicy` | 🟢 MIGRATED | Halaman web publik Kebijakan Privasi |
| `GET` | `/toc` | `Explicit` | `App\Controllers\Web\Legal\LegalPageController::termsOfService` | 🟢 MIGRATED | Halaman web publik Syarat & Ketentuan Layanan |
| `GET` | `/api/legal/meta` | `Explicit` | `App\Controllers\Api\Legal\LegalController::meta` | 🟢 MIGRATED | Metadata dropdown filter proyek, cluster, & jalan |
| `POST` | `/api/legal/meta` | `Explicit` | `App\Controllers\Api\Legal\LegalController::meta` | 🟢 MIGRATED | Metadata dropdown filter proyek, cluster, & jalan |
| `POST` | `/api/legal/save` | `Explicit` | `App\Controllers\Api\Legal\LegalController::save` | 🟢 MIGRATED | Simpan / update legalitas kavling via Service |
| `GET` | `/api/legal/get` | `Explicit` | `App\Controllers\Api\Legal\LegalController::getDataByID` | 🟢 MIGRATED | Ambil data detail legalitas berdasarkan ID |
| `POST` | `/api/legal/get` | `Explicit` | `App\Controllers\Api\Legal\LegalController::getDataByID` | 🟢 MIGRATED | Ambil data detail legalitas berdasarkan ID |
| `POST` | `/api/legal/remove` | `Explicit` | `App\Controllers\Api\Legal\LegalController::removeDoc` | 🟢 MIGRATED | Hapus dokumen legalitas via Service |
| `POST` | `/api/legal/removeDoc` | `Explicit` | `App\Controllers\Api\Legal\LegalController::removeDoc` | 🟢 MIGRATED | Alias hapus dokumen legalitas |
| `GET` | `/api/legal/getDoc` | `Explicit` | `App\Controllers\Api\Legal\LegalController::getDoc` | 🟢 MIGRATED | Ambil daftar dokumen legalitas kavling |
| `POST` | `/api/legal/getDoc` | `Explicit` | `App\Controllers\Api\Legal\LegalController::getDoc` | 🟢 MIGRATED | Ambil daftar dokumen legalitas kavling |
| `POST` | `/api/legal/upload` | `Explicit` | `App\Controllers\Api\Legal\LegalController::upload` | 🟢 MIGRATED | Upload berkas dokumen fisik legalitas |
| `GET` | `/api/legal/getListLegalitas` | `Explicit` | `App\Controllers\Api\Legal\LegalController::getListLegalitas` | 🟢 MIGRATED | DataTables server-side & raw array JSON (`?format=raw`) |
| `POST` | `/api/legal/getListLegalitas` | `Explicit` | `App\Controllers\Api\Legal\LegalController::getListLegalitas` | 🟢 MIGRATED | DataTables server-side & raw array JSON (`?format=raw`) |
| `POST` | `/api/legal/edit_others` | `Explicit` | `App\Controllers\Api\Legal\LegalController::edit_others` | 🟢 MIGRATED | Update spesifikasi khusus kavling via Service |

### B. Public PHP Services (Backend Contract Antar-Modul)
#### `App\Services\Legal\LegalService`
- `simpan(array $data)`: Simpan/update record legalitas lengkap, sanitasi input finansial, dan update relasi kavling.
- `cleanNumeric(mixed $value)`: Helper pembersihan format nominal rupiah menjadi format numerik baku (float/int).
- `deleteDocument($id)`: Hapus dokumen legalitas dari database dan storage fisik.
- `uploadDocument($file, array $data)`: Upload file fisik ke direktori storage dan catat record di database.
- `editOthers(array $data, int $id_kavling)`: Pembaruan metadata/spesifikasi khusus kavling.

## File Inventory
### Controllers (3)
- `app/Controllers/Web/Legal/LegalController.php` (23 baris, 2 public methods: `index()`, `list_legalitas()`)
- `app/Controllers/Web/Legal/LegalPageController.php` (27 baris, 2 public methods: `privacyPolicy()`, `termsOfService()`)
- `app/Controllers/Api/Legal/LegalController.php` (318 baris, 8 public methods: `save()`, `getDataByID()`, `removeDoc()`, `getDoc()`, `upload()`, `edit_others()`, `getListLegalitas()`, `meta()`)

### Services (1)
- `app/Services/Legal/LegalService.php` (175 baris, 5 public methods: `simpan()`, `cleanNumeric()`, `deleteDocument()`, `uploadDocument()`, `editOthers()`)

### Repositories (1)
- `app/Repositories/Legal/LegalRepository.php` (215 baris, 13 public methods: `getLegalDataById($id_legal)`, `insertLegal(array $data)`, `updateLegal(int $idLegal, array $data)`, `updateKavlingLegal(int $idKavling, int $idLegal)`, `getKavlingLegalTaxes($id_kavling)`, `getFileDocById($id)`, `deleteFileDoc($id)`, `getFileDocsByKavling($id_kavling, $id_group = 5)`, `insertFileDoc(array $data)`, `updateOthers($id_kavling, array $data)`, `getDatatableLegalitasData($var)`, `getDatatableLegalitasFilteredCount($var)`, `getDatatableLegalitasTotalCount()`)

### Models (1)
- `app/Models/LegalModel.php`

### Views (2 folder + 1 shared modal)
- `app/Views/legal` (1 file: `list-legalitas.php`)
- `app/Views/public` (4 file: `landing.php`, `legal_layout.php`, `privacy_policy.php`, `terms_of_service.php`)
- `app/Views/siteplan/legal.php` (Shared modal interaktif legalitas yang dipanggil dari peta siteplan)

### CSS & Frontend Styling (1 file)
- `public/assets/css/legal.css` (3-Tier CSS eksternal ber-scope `.legal-page`, `#modal-legal`, `#modal-legalitas`, `#modal-upload-legal`, tema `#2057a3`, responsive, support dark mode)

### JavaScript (2 file)
- `public/assets/js/siteplan/legal.js` (497 baris)
- `public/assets/js/legal.js` (216 baris)

### Zero-Downtime Shims (5 file)
- `app/Controllers/Legal.php` — Shim fallback controller web
- `app/Controllers/Web/LegalPageController.php` — Shim fallback controller halaman publik
- `app/Controllers/Api/LegalController.php` — Shim fallback controller API
- `app/Services/LegalService.php` — Shim fallback service di root namespace
- `app/Repositories/LegalRepository.php` — Shim fallback repository di root namespace

## Key Functions & Core Business Logic
- **Controller (`App\Controllers\Api\Legal\LegalController`):**
  - `meta()`: Menyediakan data metadata proyek, cluster, dan jalan untuk filter dropdown cascading pada modal legalitas.
  - `save()`: Validasi request input legalitas kavling, konversi format numerik, dan delegasi penyimpanan ke `LegalService::simpan()`.
  - `getListLegalitas()`: Mengelola permintaan data legalitas baik untuk server-side DataTables maupun raw JSON array (`?format=raw`).
  - `removeDoc()` & `upload()`: Mengelola alur hapus dan upload file dokumen legalitas pendukung.
  - `edit_others()`: Mengubah status spesifikasi kavling (hook ke `LegalService::editOthers`).
- **Service (`App\Services\Legal\LegalService`):**
  - `simpan(array $data)`: Logika bisnis utama penyimpanan legalitas kavling; menangani kalkulasi finansial (`cleanNumeric()`), sanitasi input, transaksi DB, dan sinkronisasi ke tabel `legal` & `kavling`.
  - `cleanNumeric(mixed $value)`: Pembersihan format input nominal mata uang (membuang `.` atau format ribuan rupiah) menjadi float/integer murni untuk mencegah kegagalan kalkulasi DB.
  - `uploadDocument($file, array $data)`: Pengunggahan dokumen fisik legalitas lahan/unit ke penyimpanan server dan pencatatan riwayat di database.
  - `deleteDocument($id)`: Validasi keberadaan file dokumen legal, penghapusan file fisik dari storage via `FileAccessService`/`unlink`, dan penghapusan record di DB.
  - `editOthers(array $data, int $id_kavling)`: Pembaruan metadata/status spesifikasi khusus pada kavling.
- **Repository (`App\Repositories\Legal\LegalRepository`):**
  - `insertLegal(array $data)`: Insert record baru pada tabel `legal`.
  - `updateLegal(int $idLegal, array $data)`: Update record legalitas berdasarkan ID legal.
  - `updateKavlingLegal(int $idKavling, int $idLegal)`: Memperbarui relasi `id_legal` pada tabel `kavling`.
  - `getLegalDataById($id_legal)`: Query detail legalitas kavling beserta data pelengkap.
  - `getDatatableLegalitasData($var)`: Query kompleks JOIN tabel `kavling`, `cluster`, `jalan`, `legal`, dan `mkdt` dengan filter proyek/cluster/jalan untuk server-side DataTables.
  - `getDatatableLegalitasFilteredCount($var)` & `getDatatableLegalitasTotalCount()`: Kalkulasi jumlah data hasil filter dan total data.

## Deprecated & Legacy Shims
- **Deprecated Routes / Endpoints:**
  - `GET /legalitas` -> Rute lama tetap dipertahankan dan sekarang diarahkan ke controller modular `App\Controllers\Web\Legal\LegalController::list_legalitas`.
  - Rute API flat tanpa namespace telah dimigrasikan ke rute eksplisit ber-prefix `/api/legal/*`.
- **Deprecated Classes / Shims (Zero-Downtime Shims):**
  - `app/Controllers/Legal.php` — **DEPRECATED (Zero-Downtime Shim)**: Gunakan `App\Controllers\Web\Legal\LegalController`.
  - `app/Controllers/Web/LegalPageController.php` — **DEPRECATED (Zero-Downtime Shim)**: Gunakan `App\Controllers\Web\Legal\LegalPageController`.
  - `app/Controllers/Api/LegalController.php` — **DEPRECATED (Zero-Downtime Shim)**: Gunakan `App\Controllers\Api\Legal\LegalController`.
  - `app/Services/LegalService.php` — **DEPRECATED (Zero-Downtime Shim)**: Gunakan `App\Services\Legal\LegalService`.
  - `app/Repositories/LegalRepository.php` — **DEPRECATED (Zero-Downtime Shim)**: Gunakan `App\Repositories\Legal\LegalRepository`.

## Readiness Checklist
- [x] Has Service Layer
- [x] Has Repository Layer
- [x] Web & API Controllers Separated
- [x] Explicit Routes Defined
- [x] Zero Controller Layer Violations
- [x] MODULE.md Initialized

## Dead Code Candidates
_Jalankan `php spark module:deadcode Legal` untuk mengaudit dead code kandidat._

## Notes
- **Cross-Module Callers:**
  - Modul `Siteplan`: Memanggil modal `app/Views/siteplan/legal.php` dan skrip `public/assets/js/siteplan/legal.js` yang berinteraksi dengan API `/api/legal/*` dan Service `LegalService`.
  - Modul `MKDT`: Membaca relasi status legalitas kavling untuk transaksi konsumen.
  - Modul `Keuangan`: Membaca kelengkapan legalitas kavling (sertifikat, PBG, AJB) saat verifikasi pencairan akad dan jaminan.

## Change Log
- **`2026-11-11` [`REFACTOR`] — Refactoring Lengkap Modul Legal (Thin Controller, Modular Structure, External Assets):**
  - **Routes & Public Contracts:**
    - Web: `/legalitas` diarahkan ke `App\Controllers\Web\Legal\LegalController::list_legalitas`.
    - Web Publik: `/privacy-policy` & `/toc` diarahkan ke `App\Controllers\Web\Legal\LegalPageController`.
    - Api: Mendaftarkan endpoint eksplisit `/api/legal/meta`, `/api/legal/save`, `/api/legal/get`, `/api/legal/remove`, `/api/legal/removeDoc`, `/api/legal/getDoc`, `/api/legal/upload`, `/api/legal/getListLegalitas` (mendukung `?format=raw`), `/api/legal/edit_others`.
    - Service: `App\Services\Legal\LegalService` menyediakan kontrak `simpan`, `cleanNumeric`, `deleteDocument`, `uploadDocument`, dan `editOthers`.
  - **Controller / Service / Repo / Model:**
    - Memisahkan controller menjadi `App\Controllers\Web\Legal\LegalController`, `App\Controllers\Web\Legal\LegalPageController`, dan `App\Controllers\Api\Legal\LegalController`.
    - Memindahkan Service ke `App\Services\Legal\LegalService` dan Repository ke `App\Repositories\Legal\LegalRepository`.
    - Menyediakan 5 Zero-Downtime Shims (`Legal.php`, `Web/LegalPageController.php`, `Api/LegalController.php`, `LegalService.php`, `LegalRepository.php`).
    - Memperbaiki bug SQL JOIN `users d` (`d.id = legal.edit_by`), membersihkan model kavling yang tidak terpakai, dan memperbaiki ikon tombol delete.
  - **View / CSS / JS:**
    - Membuat file CSS eksternal `public/assets/css/legal.css` (3-Tier CSS ber-scope `.legal-page` & ID modal, warna `#2057a3`, cache-busting `filemtime`).
    - Membuat file JS eksternal `public/assets/js/legal.js` (Server-side DataTables, cascading Select2, global actions `editLegalitas` & `removeDoc`, cache-busting `filemtime`).
    - Membersihkan view `app/Views/legal/list-legalitas.php` dari deadcode modal generator Adel (`#add-modal`, `#edit-modal`) serta inline style/script.
    - Membersihkan modal siteplan `app/Views/siteplan/legal.php` (menghapus 440+ baris inline styling dan memuat `legal.css`).
    - Mengaudit dan menyesuaikan `public/assets/js/siteplan/legal.js`.
  - **Deprecations:**
    - Mengarahkan 5 class legacy di root namespace ke class modular baru sebagai Zero-Downtime Shim.
