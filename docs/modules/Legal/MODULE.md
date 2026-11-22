# Module: Legal

**Status:** `REFACTORED`  
**Readiness Score:** `90/100`  
**Last Synced:** `2026-11-22 07:45:53` @ `4e1c7fe`  

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
- `kavling`
- `mkdt`

## Contracts
### A. Public HTTP Endpoints & Routes Inventory
| Method | URI / Pattern | Tipe | Handler | Status Migrasi | Target API / Catatan |
|---|---|---|---|---|---|
| `GET` | `/legalitas` | `Explicit` | `LegalController::list_legalitas` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/privacy-policy` | `Explicit` | `LegalPageController::privacyPolicy` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/toc` | `Explicit` | `LegalPageController::termsOfService` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/api/legal/meta` | `Explicit` | `LegalController::meta` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/legal/meta` | `Explicit` | `LegalController::meta` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/legal/save` | `Explicit` | `LegalController::save` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/api/legal/get` | `Explicit` | `LegalController::getDataByID` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/legal/get` | `Explicit` | `LegalController::getDataByID` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/legal/remove` | `Explicit` | `LegalController::removeDoc` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/legal/removeDoc` | `Explicit` | `LegalController::removeDoc` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/api/legal/getDoc` | `Explicit` | `LegalController::getDoc` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/legal/getDoc` | `Explicit` | `LegalController::getDoc` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/legal/upload` | `Explicit` | `LegalController::upload` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/api/legal/getListLegalitas` | `Explicit` | `LegalController::getListLegalitas` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/legal/getListLegalitas` | `Explicit` | `LegalController::getListLegalitas` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/legal/edit_others` | `Explicit` | `LegalController::edit_others` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |

### B. Public PHP Services (Backend Contract Antar-Modul)
#### `LegalService.php`
- `simpan(array $data)`
- `cleanNumeric(mixed $value)`
- `deleteDocument($id)`
- `uploadDocument($file, array $data)`
- `editOthers(array $data, int $id_kavling)`

## File Inventory
### Controllers (3)
- `app/Controllers/Web/Legal/LegalController.php` (23 baris, 2 public methods: `index()`, `list_legalitas()`)
- `app/Controllers/Web/Legal/LegalPageController.php` (27 baris, 2 public methods: `privacyPolicy()`, `termsOfService()`)
- `app/Controllers/Api/Legal/LegalController.php` (318 baris, 8 public methods: `save()`, `getDataByID()`, `removeDoc()`, `getDoc()`, `upload()`, `edit_others()`, `getListLegalitas()`, `meta()`)

### Services (1)
- `app/Services/Legal/LegalService.php` (175 baris, 5 public methods)

### Repositories (1)
- `app/Repositories/Legal/LegalRepository.php` (215 baris, 13 public methods: `getLegalDataById($id_legal)`, `insertLegal(array $data)`, `updateLegal(int $idLegal, array $data)`, `updateKavlingLegal(int $idKavling, int $idLegal)`, `getKavlingLegalTaxes($id_kavling)`, `getFileDocById($id)`, `deleteFileDoc($id)`, `getFileDocsByKavling($id_kavling, $id_group = 5)`, `insertFileDoc(array $data)`, `updateOthers($id_kavling, array $data)`, `getDatatableLegalitasData($var)`, `getDatatableLegalitasFilteredCount($var)`, `getDatatableLegalitasTotalCount()`)

### Models (1)
- `app/Models/LegalModel.php`

### Views (2 folder)
- `app/Views/legal` (1 file: `list-legalitas.php`)
- `app/Views/public` (4 file: `landing.php`, `legal_layout.php`, `privacy_policy.php`, `terms_of_service.php`)

### JavaScript (2 file)
- `public/assets/js/siteplan/legal.js` (497 baris)
- `public/assets/js/legal.js` (216 baris)

### Zero-Downtime Shims (5 file)
- `app/Controllers/Legal.php`
- `app/Controllers/Web/LegalPageController.php`
- `app/Controllers/Api/LegalController.php`
- `app/Services/LegalService.php`
- `app/Repositories/LegalRepository.php`

## Key Functions
- **Service (`app/Services/Legal/LegalService.php`):** `simpan(array $data)`, `cleanNumeric(mixed $value)`, `deleteDocument($id)`, `uploadDocument($file, array $data)`, `editOthers(array $data, int $id_kavling)`
- **Repository (`app/Repositories/Legal/LegalRepository.php`):** `getLegalDataById($id_legal)`, `insertLegal(array $data)`, `updateLegal(int $idLegal, array $data)`, `updateKavlingLegal(int $idKavling, int $idLegal)`, `getKavlingLegalTaxes($id_kavling)`

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
- [ ] Zero Controller Layer Violations
- [x] MODULE.md Initialized

> **Layer Violations Detected:**
> - `app/Controllers/Api/Legal/LegalController.php`: Database::connect()

## Dead Code Candidates
_Jalankan `php spark module:deadcode Legal` untuk mengaudit dead code kandidat._

## Notes
- **Cross-Module Callers:**
  - Modul `Siteplan`: Memanggil modal `app/Views/siteplan/legal.php` dan skrip `public/assets/js/siteplan/legal.js` yang berinteraksi dengan API `/api/legal/*` dan Service `LegalService`.
  - Modul `MKDT`: Membaca relasi status legalitas kavling untuk transaksi konsumen.
  - Modul `Keuangan`: Membaca kelengkapan legalitas kavling (sertifikat, PBG, AJB) saat verifikasi pencairan akad dan jaminan.

### Audit Verifikasi Migrasi (2026-11-22)
- **Ledger Status:** `COMPLETED (Catatan Minor)` — Status `REFACTORED`, Readiness Score `90/100`.
- **Temuan Nyata:**
  - `App\Controllers\Api\Legal\LegalController::meta()` memanggil `\Config\Database::connect()` dan menjalankan query langsung `$db->table('proyek'|'cluster'|'jalan')` pada L253-277 di lapisan Controller (seharusnya didelegasikan ke `LegalRepository`/`LegalService`).
- **Verifikasi Bersih (Non-Issue / Terkonfirmasi):**
  - `Api\Legal\LegalController` mewarisi `BaseApiController` (L10); `getListLegalitas()` (L183-248) masih menghasilkan HTML DataTables secara default namun sudah mendukung fallback `?format=raw` (L192-203).
  - `Web\Legal\LegalController` (22 baris) dan `Web\Legal\LegalPageController` (26 baris) tipis, hanya merender view, tanpa akses Repository langsung atau respons JSON.
  - `Services\Legal\LegalService` (174 baris) menggunakan `\Config\Database::connect()` (L20) hanya untuk manajemen transaksi DB (`transStart`/`transComplete`/`transStatus`/`transRollback` pada L36-68 dan L116-138), tanpa query `->table()`/`->query()` atau mutasi tabel modul lain.
  - 5 Zero-Downtime Shims (`app/Controllers/Legal.php`, `app/Controllers/Web/LegalPageController.php`, `app/Controllers/Api/LegalController.php`, `app/Services/LegalService.php`, `app/Repositories/LegalRepository.php`) terkonfirmasi sebagai thin delegator (13 baris) dengan 0 pelanggaran.
  - 16 rute eksplisit modular (`privacy-policy`, `toc`, `legalitas`, dan `/api/legal/*`) aktif di `app/Config/Routes.php` (0 legacy routes, 0 autoroutes).

## Change Log
- `2026-11-22` [`AUDIT`] — Audit verifikasi migrasi modular: status `COMPLETED (Catatan Minor)` (Score 90/100); terkonfirmasi pelanggaran minor `Database::connect()` pada `Api\Legal\LegalController::meta()` (L253-277) serta sinkronisasi skor & checklist `MODULE.md`.
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
