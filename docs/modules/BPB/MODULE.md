# Module: BPB

**Status:** `REFACTORED`  
**Readiness Score:** `100/100`  
**Last Synced:** `2026-11-13 03:09:22` @ `0fe64c1`  

## Purpose
Bon Permintaan Barang: alur pengajuan logistik lapangan, verifikasi bertingkat, dan tanda tangan digital.

## Out of scope
- Hal-hal di luar tanggung jawab domain BPB.

## Invariants
- Modul lain tidak boleh memanipulasi tabel pemilik secara langsung.
- Perubahan finansial/status harus diverifikasi di level Service.

## Database Tables
### Owner Table(s)
- `bpb_requests` *(akses tulis eksklusif hanya untuk modul BPB)*
- `bpb_items` *(akses tulis eksklusif hanya untuk modul BPB)*
- `bpb_files` *(akses tulis eksklusif hanya untuk modul BPB)*
- `bpb_history` *(akses tulis eksklusif hanya untuk modul BPB)*
- `bpb_signatures` *(akses tulis eksklusif hanya untuk modul BPB)*
- `user_signature_profiles` *(akses tulis eksklusif hanya untuk modul BPB)*

### Shared Tables (Dibaca dari modul lain)
- `proyek`
- `kavling`
- `users`

## Contracts
### A. Public HTTP Endpoints & Routes Inventory
| Method | URI / Pattern | Tipe | Handler | Status Migrasi | Target API / Catatan |
|---|---|---|---|---|---|
| `GET` | `/bpb` | `Explicit` | `BpbController::index` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/bpb/shared/(:segment)` | `Explicit` | `BpbController::shared/$1` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/bpb/(:num)/pdf` | `Explicit` | `BpbController::pdf/$1` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/bpb/signature/(:num)` | `Explicit` | `BpbController::signature/$1` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/bpb/verify/(:segment)` | `Explicit` | `BpbController::verify/$1` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/api/bpb/options` | `Explicit` | `BpbController::options` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/api/bpb/detail/(:num)` | `Explicit` | `BpbController::detail/$1` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/api/bpb/shared/(:segment)` | `Explicit` | `BpbController::shared/$1` | 🟢 MIGRATED | Detail BPB via share token (JSON) |
| `POST` | `/api/bpb/draft` | `Explicit` | `BpbController::draft` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/bpb/submit` | `Explicit` | `BpbController::submit` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/bpb/(:num)/update` | `Explicit` | `BpbController::update/$1` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/bpb/(:num)/sign` | `Explicit` | `BpbController::sign/$1` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/bpb/(:num)/reject` | `Explicit` | `BpbController::reject/$1` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/bpb/(:num)/cancel` | `Explicit` | `BpbController::cancel/$1` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `POST` | `/api/bpb/(:num)/status` | `Explicit` | `BpbController::status/$1` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/api/profile/signature` | `Explicit` | `ProfileSignatureController::show` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `GET` | `/api/profile/signature/image` | `Explicit` | `ProfileSignatureController::image` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `PUT` | `/api/profile/signature` | `Explicit` | `ProfileSignatureController::save` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |
| `DELETE` | `/api/profile/signature` | `Explicit` | `ProfileSignatureController::delete` | 🟢 MIGRATED | Endpoint terstruktur di Api/Web |

### B. Public PHP Services (Backend Contract Antar-Modul)
#### `BpbService.php`
- `list(array $input, int $userId)`
- `options(int $userId)`
- `detail(int $id, int $userId)`
- `detailByToken(string $token, int $userId)`
- `saveDraft(array $data, array $uploads, int $userId)`
- `submit(array $data, array $uploads, int $userId, string $ip, string $agent)`
- `update(int $id, array $data, array $uploads, int $userId, string $ip, string $agent)`
- `sign(int $id, array $data, int $userId, string $ip, string $agent)`
- `reject(int $id, string $reason, int $userId)`
- `cancel(int $id, string $reason, int $userId)`
- `changeStatus(int $id, array $data, array $uploads, int $userId, string $ip, string $agent)`
- `signaturePath(int $signatureId, int $userId)`
#### `BpbWorkflow.php`
- `labels()`
- `label(string $status)`
- `approvalStatus(bool $hasCc)`
- `afterSignature(string $status)`
- `canOperationalTransition(string $from, string $to)`
- `isTerminal(string $status)`
#### `BpbPdfService.php`
- `render(array $detail, int $userId)`
- `verify(string $token)`
#### `BpbFileService.php`
- `storeUploads(array $uploads, int $bpbId, string $category, int $userId, int $existingCount = 0)`
- `storeCanvas(string $dataUrl, string $logicalDir)`
- `copyPrivate(string $sourceLogicalPath, string $logicalDir)`
- `cleanup(array $logicalPaths)`
#### `BpbNumberFormatter.php`
- `format(int $sequence, int $month, int $year)`
#### `BpbDocumentHasher.php`
- `hash(array $request, array $items, array $files)`
#### `ProfileSignatureService.php`
- `get(int $userId)`
- `save(int $userId, string $password, string $dataUrl)`
- `delete(int $userId, string $password)`
- `absolutePath(int $userId)`

## File Inventory
### Controllers (3)
- `app/Controllers/Web/Bpb/BpbController.php` (90 baris, 5 public methods: `index()`, `shared(string $token)`, `signature(int $id)`, `pdf(int $id)`, `verify(string $token)`)
- `app/Controllers/Api/Bpb/BpbController.php` (129 baris, 11 public methods: `list()`, `options()`, `detail(int $id)`, `shared(string $token)`, `draft()`, `submit()`, `update($id = null)`, `sign(int $id)`, `reject(int $id)`, `cancel(int $id)`, `status(int $id)`)
- `app/Controllers/Api/Bpb/ProfileSignatureController.php` (54 baris, 4 public methods: `show($id = null)`, `image()`, `save()`, `delete($id = null)`)

### Services (7)
- `app/Services/Bpb/BpbService.php` (697 baris, 12 public methods)
- `app/Services/Bpb/BpbWorkflow.php` (69 baris, 6 public methods)
- `app/Services/Bpb/BpbPdfService.php` (323 baris, 2 public methods)
- `app/Services/Bpb/BpbFileService.php` (119 baris, 4 public methods)
- `app/Services/Bpb/BpbNumberFormatter.php` (17 baris, 1 public methods)
- `app/Services/Bpb/BpbDocumentHasher.php` (44 baris, 1 public methods)
- `app/Services/Bpb/ProfileSignatureService.php` (89 baris, 4 public methods)

### Repositories (1)
- `app/Repositories/Bpb/BpbRepository.php` (221 baris, 13 public methods: `list(array $input, int $userId)`, `detail(int $id)`, `findForVerification(string $token)`, `findByShareToken(string $token)`, `lock(int $id)`, `findBySubmissionKey(string $key)`, `options(int $excludeUserId)`, `listFilterOptions()`, `userSnapshot(int $userId)`, `hasGroup(int $userId, int $groupId)`, `isActiveUser(int $userId)`, `hasApproverSignature(int $bpbId)`, `nextNumber(int $year, int $month)`)

### Models (6)
- `app/Models/BpbRequestModel.php`
- `app/Models/BpbItemModel.php`
- `app/Models/BpbFileModel.php`
- `app/Models/BpbHistoryModel.php`
- `app/Models/BpbSignatureModel.php`
- `app/Models/UserSignatureProfileModel.php`

### Views (1 folder)
- `app/Views/bpb` (4 file: `index.php`, `pdf.php`, `shared.php`, `verify.php`)

### JavaScript (2 file)
- `public/assets/js/bpb.js` (835 baris)
- `public/assets/js/profile-signature.js` (23 baris)

### Zero-Downtime Shims (4 file)
- `app/Controllers/Web/BpbController.php`
- `app/Controllers/Api/BpbController.php`
- `app/Controllers/Api/ProfileSignatureController.php`
- `app/Repositories/BpbRepository.php`

## Key Functions
- **Alur Bisnis 4 Langkah BPB Baru:**
  1. **Pengajuan (Step 1 - Pemohon / `applicant`):** Pengisian detail permohonan logistik/barang, estimasi biaya, pemilihan approver & CC, unggah berkas pendukung, serta pembubuhan tanda tangan digital pemohon saat submit (`DRAFT` -> `WAITING_APPROVER`).
  2. **Persetujuan / ACC (Step 2 - Approver / `approver`):** Pemeriksaan pengajuan oleh pejabat/approver yang ditugaskan, verifikasi item, dan pembubuhan tanda tangan digital ACC (`WAITING_APPROVER` -> `APPROVED` atau transitif `WAITING_CC`).
  3. **Tandai Cair (Step 3 - Departemen UMUM `group_id = 2`):** Pencairan dana operasional oleh staf UMUM, input nominal aktual pencairan, nama penerima dana, nomor rekening tujuan, serta catatan pencairan (`APPROVED` -> `DISBURSED`).
  4. **Verifikasi Pengeluaran (Step 4 - Departemen UMUM `group_id = 2`):** Verifikasi fisik setelah barang dibeli, unggah nota/kuitansi/faktur riil, input tanggal pembelian riil, catatan verifikator, serta tanda tangan digital verifikator pengeluaran `expense_verifier` (`DISBURSED` -> `PURCHASED`).

- **Fitur Shareable Link & Halaman Mandiri Tanda Tangan:**
  - Token unik 64-karakter acak (`share_token`) di-generate otomatis saat BPB dibuat.
  - Endpoint `GET /bpb/shared/(:segment)` (`BpbController::shared()`) membuka halaman mandiri `bpb/shared.php`.
  - Halaman mandiri menyajikan ringkasan lengkap permohonan, tabel item barang, galeri nota/lampiran dengan preview lightbox, kartu tanda tangan digital 3 pihak (Pemohon, Approver, Verifikator Pengeluaran), tombol salin link dengan feedback SweetAlert2, serta modal aksi interaktif (Approve / Reject / Disbursed / Purchased) bagi user berwenang yang sedang login.

- **Controllers:**
  - `Web\Bpb\BpbController::index()`: Menampilkan dashboard daftar pengajuan BPB.
  - `Web\Bpb\BpbController::shared(string $token)`: Merender halaman mandiri shareable link berdasarkan `share_token`.
  - `Web\Bpb\BpbController::pdf(int $id)`: Download/stream cetak PDF resmi BPB.
  - `Web\Bpb\BpbController::signature(int $id)`: Stream gambar tanda tangan secara aman dengan verifikasi hak akses.
  - `Web\Bpb\BpbController::verify(string $token)`: Halaman verifikasi publik QR Code keaslian dokumen.
  - `Api\Bpb\BpbController`: Kumpulan REST API endpoint untuk operasi CRUD, sign, reject, cancel, submit, dan mutasi status operasional.

- **Services:**
  - `BpbService::detailByToken(string $token, int $userId)`: Mengambil data lengkap permohonan BPB berdasarkan `share_token` sekaligus menghitung izin aksi interaktif user yang sedang login.
  - `BpbService::submit()`: Validasi integritas permohonan, validasi ketat nama item, jumlah > 0, satuan, approver/mengetahui, verifikasi password pemohon, canvas digital signature, penyimpanan item & berkas lampiran, dan pencatatan audit log `bpb_history`.
  - `BpbService::sign()`: Validasi passphrase/canvas tanda tangan approver dan mutasi status ke `APPROVED` / `WAITING_CC`.
  - `BpbService::changeStatus()`: Menangani transisi operasional pencairan (`DISBURSED`) dan verifikasi pengeluaran nota (`PURCHASED`), mencakup validasi nominal `cleanNumeric()` dan penyimpanan tanda tangan verifikator `expense_verifier`.
  - `BpbWorkflow::canOperationalTransition(string $from, string $to)`: Enforcing aturan transisi status operasional yang sah.

- **Repositories:**
  - `BpbRepository::findByShareToken(string $token)`: Query pencarian BPB berdasarkan kolom `share_token`.
  - `BpbRepository::detail(int $id)`: Query relasional lengkap data BPB (pemohon, approver, item, berkas, tanda tangan, riwayat).
  - `BpbRepository::hasGroup(int $userId, int $groupId)`: Verifikasi keanggotaan grup pengguna (misal: grup UMUM ID 2).

- **Views & Frontend Assets:**
  - `app/Views/bpb/index.php`: Dashboard dan formulir pengajuan BPB. Formulir pengajuan dilengkapi tombol "Kembali" ganda (atas dan bawah formulir via `#bpb-form-back-bottom`), multiline textarea untuk keterangan spesifikasi item (`.bpb-item-note`), floating sticky action bar (`.bpb-sticky-actions`), serta peniadaan checkbox persetujuan syarat manual yang digantikan validasi password & canvas tanda tangan pemohon.
  - `app/Views/bpb/shared.php`: Halaman mandiri shareable link BPB dengan arsitektur responsif hybrid. Menggunakan tabel detail lengkap untuk layar desktop (`.table-responsive.d-none.d-md-block`) dan sistem mobile cards (`.bpb-mobile-items-list` & `.bpb-item-mobile-card` pada `.d-block.d-md-none.p-1`) untuk mengeliminasi horizontal overflow pada smartphone serta menyajikan badge nomor urut, nama barang, badge kuantitas/satuan, dan deskripsi spesifikasi item.
  - `public/assets/css/bpb.css`: Scoped stylesheet untuk modul BPB dan halaman mandiri (`.bpb-shared-page`), mencakup visualisasi alur status, 3 kartu tanda tangan digital, modal interaktif, kartu barang mobile (`.bpb-item-mobile-card`, `.bpb-item-mobile-desc`), bar aksi lengket formulir (`.bpb-sticky-actions`), textarea keterangan item (`.bpb-item-note`), dan dukungan penuh tema gelap (`.dark-layout`).
  - `public/assets/js/bpb.js`: Client-side logic untuk modul BPB: manajemen dynamic row item barang dengan multiline textarea, validasi formulir berlapis sebelum submit (kelengkapan barang, kuantitas positif > 0, satuan, approver, canvas signature, dan password), sinkronisasi navigasi tombol kembali bawah, salin shareable link dengan SweetAlert2 feedback, dan rendering visual alur approval.

## Deprecated & Legacy Shims
- **Removed / Deprecated UI Controls & Request Fields:**
  - Checkbox `#bpb-consent` ("Saya menyetujui...") & field request `consent` — **REMOVED / DEPRECATED**: Validasi integritas dokumen dan komitmen pengajuan sepenuhnya dialihkan secara legal ke pembubuhan tanda tangan canvas digital pemohon dan verifikasi password akun pengguna.
- **Deprecated Status Transitions:**
  - Transisi status `APPROVED -> PROCESSED` **DEPRECATED**. Alur operasional kini langsung melompat dari `APPROVED -> DISBURSED`. Transisi `PROCESSED -> DISBURSED` tetap dipertahankan semata-mata sebagai backward-compatibility shim untuk record lama yang terlanjur berstatus `PROCESSED`.
- **Deprecated Action Flags:**
  - Flag aksi `actions.process` pada payload API detail BPB **DEPRECATED** (selalu bernilai `false`).
- **Zero-Downtime Shim Classes:**
  - `app/Controllers/Web/BpbController.php` — **DEPRECATED (Zero-Downtime Shim)**: Gunakan `App\Controllers\Web\Bpb\BpbController`.
  - `app/Controllers/Api/BpbController.php` — **DEPRECATED (Zero-Downtime Shim)**: Gunakan `App\Controllers\Api\Bpb\BpbController`.
  - `app/Controllers/Api/ProfileSignatureController.php` — **DEPRECATED (Zero-Downtime Shim)**: Gunakan `App\Controllers\Api\Bpb\ProfileSignatureController`.
  - `app/Repositories/BpbRepository.php` — **DEPRECATED (Zero-Downtime Shim)**: Gunakan `App\Repositories\Bpb\BpbRepository`.

## Readiness Checklist
- [x] Has Service Layer
- [x] Has Repository Layer
- [x] Web & API Controllers Separated
- [x] Explicit Routes Defined
- [x] Zero Controller Layer Violations
- [x] MODULE.md Initialized

## Dead Code Candidates
_Jalankan `php spark module:deadcode BPB` untuk mengaudit dead code kandidat._

## Notes
- **Otorisasi Khusus Departemen UMUM:** Tindakan pencairan dana (`DISBURSED`) dan verifikasi pengeluaran nota (`PURCHASED`) dikunci secara ketat di level Service dan Controller hanya untuk anggota grup Departemen UMUM (`group_id = 2`).
- **Akses Shareable Link:** Endpoint `/bpb/shared/(:segment)` mewajibkan login pengguna untuk keamanan dokumen finansial perusahaan, namun tidak membatasi akses hanya pada pemohon sehingga tautan dapat dibagikan kepada seluruh stakeholder terkait.
- **Cross-Module Callers:** Modul BPB saat ini mengonsumsi data master dari modul `Proyek` (`proyek`), modul `Kavling` (`kavling`), dan data autentikasi `Users` (Myth:Auth). Modul ini mandiri dan belum dikonsumsi langsung melalui PHP Service oleh modul lain.

## Change Log
- **2026-11-13 [FEATURE / API_BRIDGE] — Penambahan Endpoint REST JSON Detail BPB via Share Token (`/api/bpb/shared/(:segment)`):**
  - **Routes & Public Contracts:**
    - Menambahkan rute API terproteksi `GET /api/bpb/shared/(:segment)` (`Api\Bpb\BpbController::shared/$1`) dengan filter autentikasi `login`.
    - Mengembalikan payload JSON murni terstruktur `{ success: true, messages: "Detail BPB berhasil dimuat.", data: BpbDetail }` yang siap dikonsumsi oleh `sigapp-vue` (`BpbSharedView.vue`).
  - **Backend (Controller):**
    - `Api\Bpb\BpbController`: Menambahkan method `shared(string $token)` yang mengeksekusi `BpbService::detailByToken($token, (int) user_id())`.
  - **Zero-Downtime:**
    - Rute web legacy `GET /bpb/shared/(:segment)` (`Web\Bpb\BpbController::shared/$1`) dan response `BpbService::detail` (`share_url` legacy) tetap utuh 100%.
- **2026-10-29 [UI_REPORT] — Penyempurnaan Form Pengajuan BPB (Sticky Action Bar, Textarea Keterangan, Tombol Kembali Bawah, dan Validasi Wajib Berlapis):**
  - **Routes & Public Contracts:** Tidak ada perubahan rute maupun signature public contract.
  - **Controller / Service / Repo / Model:**
    - `app/Services/Bpb/BpbService.php`: Menghapus dependensi validasi checkbox `consent` pada method `submit()` dan memperketat validasi server-side wajib untuk seluruh item (nama barang, kuantitas > 0, satuan), kelengkapan approver (`approver_id`), password pengguna, dan pembubuhan data URL tanda tangan canvas pemohon.
  - **View / CSS / JS:**
    - `app/Views/bpb/index.php`:
      * Menambahkan tombol "Kembali" kedua di bagian bawah formulir pengajuan (`#bpb-form-back-bottom`) mendampingi tombol "Simpan Draf" dan "Ajukan BPB".
      * Mengubah input teks satu baris keterangan item menjadi multiline textarea (`<textarea class="form-control bpb-item-note" rows="2">`).
      * Menghapus checkbox persetujuan syarat & ketentuan `#bpb-consent`.
      * Membungkus aksi formulir bagian bawah dalam kontainer sticky `.bpb-sticky-actions`.
    - `public/assets/css/bpb.css`:
      * Menambahkan styling `.bpb-sticky-actions` (sticky footer di bottom screen, background `#ffffff` / `.dark-layout #283046`, border-top subtle, box-shadow melayang, dan z-index form actions).
      * Menambahkan styling `.bpb-item-note` (min-height, resize vertikal, dan font size proporsional).
    - `public/assets/js/bpb.js`:
      * Event handler tombol kembali bawah `#bpb-form-back-bottom` tersinkronisasi dengan alur reset form / navigasi tab.
      * Template baris item baru (`renderItemRow`) menggunakan textarea `.bpb-item-note`.
      * Validasi client-side ketat: mengecek bahwa setiap item memiliki nama, kuantitas positif (> 0), dan satuan valid; memastikan approver dipilih; memastikan canvas tanda tangan pemohon terisi; serta password akun terisi sebelum request submit dikirim.
  - **Deprecations:**
    - Kontrol UI `#bpb-consent` dan validasi field `consent` dihapus/dideprecate (digantikan otentikasi password & digital signature canvas).
- **2026-10-29 [UI_REPORT] — Penyelarasan Mobile Card System pada Daftar Barang Halaman Shared BPB:**
  - **Routes & Public Contracts:** Tidak ada perubahan (perubahan murni pada view dan stylesheet frontend).
  - **Controller / Service / Repo / Model:** Tidak ada perubahan.
  - **View / CSS / JS:**
    - `app/Views/bpb/shared.php`: Mengubah card daftar barang (Card 3) menjadi sistem hybrid responsif:
      * Desktop (`.table-responsive.d-none.d-md-block`): Tetap menyajikan format tabel detail barang lengkap (No, Nama Barang, Kuantitas, Satuan, Keterangan).
      * Mobile (`.d-block.d-md-none.p-1`): Menyajikan sistem kartu `.bpb-mobile-items-list` berisi kartu `.bpb-item-mobile-card` yang memuat badge nomor urut `#`, nama barang dengan font semi-bold, badge kuantitas/satuan sekunder, dan deskripsi/spesifikasi item dengan pemisah garis putus-putus (`border-top: 1px dashed`).
    - `public/assets/css/bpb.css`:
      * Menambahkan scoped style `.bpb-shared-page .bpb-item-mobile-card` (border halus, radius 6px, padding nyaman, background card).
      * Menambahkan styling `.bpb-shared-page .bpb-item-mobile-desc` dengan teks muted dan pemisah dashed.
      * Menambahkan reset margin bawah untuk item terakhir (`:last-child`).
      * Menambahkan adaptasi tema gelap (`.dark-layout .bpb-shared-page .bpb-item-mobile-card` & `.dark-layout .bpb-shared-page .bpb-item-mobile-desc`).
      * Memastikan 0 horizontal overflow pada seluruh layar smartphone (< 768px).
  - **Deprecations:** Tidak ada.
- **2026-10-29 [FEATURE] — Pembaruan Alur 4 Langkah BPB, Shareable Link & Halaman Mandiri Tanda Tangan:**
  - **Routes & Public Contracts:**
    - Menambahkan rute publik terproteksi `GET /bpb/shared/(:segment)` (`BpbController::shared/$1`) dengan filter autentikasi `login`.
    - Menambahkan public contract service method `BpbService::detailByToken(string $token, int $userId)` dan repository method `BpbRepository::findByShareToken(string $token)`.
  - **Database & Migration:**
    - Migration `app/Database/Migrations/2026-10-29-000001_AddBpbShareToken.php`: Menambahkan kolom `share_token VARCHAR(64) UNIQUE NULL` pada tabel `bpb_requests`.
    - Model `BpbRequestModel`: Mendaftarkan field `share_token` ke dalam `$allowedFields`.
  - **Backend (Controller / Service / Repo):**
    - `Web\Bpb\BpbController`: Implementasi method `shared(string $token)` untuk merender view mandiri shareable link.
    - `BpbWorkflow`: Penyesuaian aturan transisi alur 4 langkah (`WAITING_APPROVER` -> `APPROVED` -> `DISBURSED` -> `PURCHASED`), pembatasan otorisasi aksi pencairan & verifikasi nota ke grup UMUM (`group_id = 2`), serta penambahan tipe signature `expense_verifier`.
    - `BpbService`: Auto-generation `share_token` (64 char hex), implementasi `detailByToken()`, penanganan upload nota pembelian fisik, verifikasi pengeluaran, dan pembubuhan tanda tangan verifikator.
    - `BpbRepository`: Implementasi method `findByShareToken()` untuk pencarian instan record permohonan.
  - **Frontend (View / CSS / JS):**
    - View baru `app/Views/bpb/shared.php`: Halaman mandiri responsif berisi visualisasi 3 kartu tanda tangan digital, tabel barang, preview lampiran lightbox, tombol salin shareable link, serta modal aksi terintegrasi.
    - `app/Views/bpb/index.php`: Penyesuaian modal detail dengan tombol "Bagikan Link", form pencairan dana, dan form verifikasi nota pengeluaran.
    - `public/assets/css/bpb.css`: Penambahan styling scoped `.bpb-shared-page`, 3 kartu tanda tangan digital, alur status, dan badge interaktif.
    - `public/assets/js/bpb.js`: Handler tombol salin shareable link via Clipboard API & fallback dengan SweetAlert2 feedback, penyesuaian alur 4 langkah, dan validasi form dinamis.
  - **Deprecations:**
    - Transisi lama `APPROVED -> PROCESSED` dan action flag `actions.process` di-deprecate; alur langsung melompat ke `DISBURSED`.
- `2026-10-23` [`MIGRATION`] — Refaktor arsitektur modular Thin Controller -> Service -> Repository selesai 100%.
