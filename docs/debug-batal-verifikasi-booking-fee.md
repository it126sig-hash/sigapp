# Debug & Worklog Implementasi — Fitur Pembatalan Verifikasi Booking Fee (Modul Keuangan)

**Tanggal Mulai:** 2026-11-19  
**Modul Target:** `Keuangan`  
**Mode Tugas:** `FEATURE`  
**Status Saat Ini:** `COMPLETED` *(QA Putaran 2 PASS, Sinkronisasi Dokumentasi Fase 4, & Bugfix Pasca-Rilis Overflow `#modal_divisi3` PASS — 2026-11-19)*

---

## 1. Ringkasan Permintaan & Keputusan Bisnis Terkonfirmasi

**Permintaan Pengguna:**  
Menambahkan menu di `app/Views/keuangan/list-tagihan.php` untuk membatalkan booking fee yang sudah diverifikasi (dengan referensi `docs/modules/Keuangan/MODULE.md`), sekaligus merekam setiap pekerjaan, langkah, kontrak, dan temuan untuk keperluan debugging di `docs/`.

**4 Keputusan Bisnis Final (Disetujui User):**
1. **Semantik Pembatalan (Unverify / Buka Kunci Verifikasi):**
   - Mencabut `mkdt_booking_payment.verified_by` dan `verified_at` menjadi `NULL` sehingga status kembali *"Belum Diverifikasi"*.
   - Nominal & tanggal booking di MKDT/NUP maupun Modal Bayar terbuka kembali untuk dikoreksi lalu diverifikasi ulang.
   - Baris `log_pembayaran`, `log_pembayaran_detail`, `finance_ledger`, `mkdt_payment_summary`, dan laporan Cash-In **tetap sinkron dengan `mkdt.booking_fee`** (tidak membatalkan transaksi kavling / tidak men-nol-kan kas).
2. **Lokasi UI:**
   - **Tab ke-4 `BOOKING FEE TERVERIFIKASI`** pada `app/Views/keuangan/list-tagihan.php` (lazy-load DataTable server-side untuk seluruh booking fee terverifikasi di proyek aktif, termasuk MKDT yang sudah `Lunas`, `Akad`, `Promo Rp 0`, dan `NUP` tanpa kavling).
   - **Tombol sekunder `Batalkan Verifikasi`** di dalam card Booking Fee (`#keu-booking-fee-section`) pada Modal Bayar (`#modal_divisi3`, partial `app/Views/keuangan/partials/modal_bayar_tagihan.php` / `public/assets/js/tagihan-bayar-modal.js`).
3. **Otorisasi (RBAC):**
   - **Admin & Keuangan (`in_groups(['1', '3'])`)**, sama dengan hak akses verifikasi saat ini, ditambah pengecekan akses proyek aktif (`ActiveProyekService::userCanAccess()`).
4. **Guard Status Transaksi & Audit Trail:**
   - **Blokir** pembatalan verifikasi jika `mkdt.status_mkdt === 'Batal'`, `mkdt.is_batal == 1`, atau `mkdt.refund_paid == 1`.
   - Status lain (termasuk `Akad` dan `is_lunas == 1`) **diizinkan** dengan syarat mengisi alasan wajib (3–500 karakter) yang dicatat secara transaksional ke tabel `history_log` melalui `HistoryService::log('keuangan', ...)` (`action: 'batal_verifikasi_booking'`), **tanpa** mengirim notifikasi ke MKDT.

---

## 2. Ringkasan Fase 1: Eksplorasi & Analisis (`sigapp-explorer`)

### 2.1 Mekanisme Verifikasi Booking Fee Saat Ini
- `BookingPaymentService::synchronize()` menjaga invarian `mkdt.booking_fee > 0 ⇔ 1 canonical log_pembayaran` (`payment_type = 'Booking'`, `id_keuangan = ''`, detail kategori `BO` dengan `booking_is_installment = 0`) + 1 ledger income di `finance_ledger` + rekalkulasi `mkdt_payment_summary` (`total_booking`).
- Promo Rp 0 (`mkdt.booking_fee = 0`) memiliki baris `mkdt_booking_payment` dengan `id_pembayaran = NULL`. Jika Keuangan pernah mencatat `BO` pada promo, `booking_is_installment = 1` dan diperlakukan sebagai `UM`.
- Setelah `verified_at` terisi:
  - `BookingPaymentService::assertEditable()` menolak perubahan `booking_fee`/`booking_tgl` di `TransaksiService`, `NupService`, dan `Mkdt::save()`.
  - `BookingPaymentService::assertMayDelete()` memblokir hapus pembayaran promo (`booking_is_installment = 1`) dan pembayaran kanonik booking.
  - `BookingPaymentService::assertManualAllocationAllowed()` memblokir alokasi `BO` baru setelah terverifikasi atau jika `booking_fee > 0`.

### 2.2 Peta Callers, Blast Radius & Pitfalls
- **Konsumen `BookingPaymentService` lintas modul:** `TransaksiService`, `NupService`, legacy `Mkdt`, `KeuanganService`, `PembayaranService`, `BookingFeeMigrationService`, serta frontend `mkdt.js`, `nup.js`, `tagihan-bayar-modal.js`. Seluruhnya membaca `verified_at` secara dinamis sehingga pembatalan verifikasi (`unverify`) tidak memerlukan perubahan kode di modul MKDT/Transaksi/NUP.
- **Shared Replacement Payment Pairs (`owners > 1`):** Data migrasi historis mencatat 3 pasang MKDT berbagi 1 `id_pembayaran`: `[71, 72]`, `[74, 75]`, dan `[318, 319]`. Membatalkan verifikasi satu MKDT membuka kunci MKDT tersebut sambil menampilkan badge info `Pembayaran Bersama`; `synchronize()` tetap menjaga integritas apabila pasangan MKDT lainnya masih terverifikasi.
- **Bug Eksisting NUP di `BookingPaymentRepository::findProjectIdByMkdt()`:** Query lama memakai `INNER JOIN kavling`, menyebabkan transaksi NUP (`id_kavling IS NULL`, proyek disimpan di `mkdt.id_proyek`) mengembalikan `null` (`409`). Diperbaiki dengan `LEFT JOIN` + `COALESCE(c.id_proyek, m.id_proyek)`.
- **Lokasi `#modal_divisi3` (`modal_bayar_tagihan.php` + `tagihan-bayar-modal.js`):** Di-include pada 3 halaman (`app/Views/keuangan/list-tagihan.php`, `app/Views/siteplan/keuangan.php`, dan `app/Views/kavling/list-kavling.php`). Helper `confirmUnverifyBookingFee` ditempatkan di `tagihan-bayar-modal.js` agar tombol di dalam Modal Bayar bekerja lintas seluruh halaman tersebut, dan permission `can_unverify` dikirim dari backend pada `getBooking()`.
- **Kompatibilitas SQLite pada PHPUnit:** `HistoryRepository` menerima `?BaseConnection $db = null` agar logika transaksi dan pengujian SQLite di `tests/database/BookingInstallmentSeparationTest.php` memakai koneksi DB yang sama dan aman terhadap tabel minimal.

---

## 3. Kronologi Pekerjaan & Log Debugging

### Fase 2A — Eksekusi Backend CI4 (`sigapp-engineer`)
- **Status:** `DONE` *(2026-11-19)*
- **File Inventory yang Diubah (8 File):**
  1. `app/Repositories/AuditTrail/HistoryRepository.php` — Constructor menerima `?BaseConnection $db = null` agar kompatibel dengan transaksi aktif dan koneksi SQLite unit test.
  2. `app/Services/AuditTrail/HistoryService.php` — Tambah `'keuangan' => 'Keuangan'` pada `MODULE_LABELS` dan `'batal_verifikasi_booking' => 'Batal Verifikasi Booking Fee'` pada `formatActionLabel()`.
  3. `app/Config/Routes.php` — Pada grup `api/tagihan/booking` (`filter => ['login', 'csrf']`), ditambahkan:
     - `POST api/tagihan/booking/batal-verifikasi` → `BookingPaymentController::unverify`
     - `POST api/tagihan/booking/terverifikasi` → `BookingPaymentController::verifiedList`
  4. `app/Repositories/Keuangan/BookingPaymentRepository.php`:
     - Perbaikan `findProjectIdByMkdt(int $idMkdt): ?int` memakai `LEFT JOIN` + `COALESCE(c.id_proyek, m.id_proyek)` dan fallback aman untuk tabel minimal SQLite (mengatasi error `409` pada NUP tanpa kavling).
     - Tambah `clearVerification(int $idMkdt): void`, `findUsernameById(?int $userId): ?string`, `getVerifiedList(int $idProyek, array $filters = []): array`, serta helper privat `buildVerifiedQuery()`.
  5. `app/Services/Keuangan/BookingPaymentService.php`:
     - Injeksi `HistoryService(new HistoryRepository($this->db))`.
     - Tambah `isUnverifyBlocked(array $mkdt): bool` (blokir `status_mkdt === 'Batal'`, `is_batal === 1`, `refund_paid === 1`).
     - Perluas `getBooking(int $idMkdt): array` dengan `verified_by_name`, `can_unverify`, dan `unverify_blocked_reason`.
     - Tambah `unverify(int $idMkdt, int $actorId, string $reason): array` (validasi alasan 3–500 karakter via `mb_strlen`, DB transaction + `FOR UPDATE`, guard terverifikasi & tidak diblokir, reset `verified_by`/`verified_at` ke `NULL`, rekam audit ke `history_log`).
     - Tambah `getVerifiedList(int $idProyek, array $filters, bool $canManage): array` (Pure JSON tanpa HTML).
  6. `app/Controllers/Api/Keuangan/BookingPaymentController.php` — Tambah `unverify(): ResponseInterface` (RBAC group `1`/`3`, validasi input, cek proyek via `ActiveProyekService`, Full JSON `200`/`403`/`409`/`422`/`500`) dan `verifiedList(): ResponseInterface` (support DataTables server-side + `?format=raw`, Pure JSON tanpa HTML).
  7. `app/Controllers/Api/Keuangan/TagihanController.php` — Tambah `'can_verify_booking'` dan `'can_unverify_booking'` pada `meta()['data']['permissions']`.
  8. `tests/database/BookingInstallmentSeparationTest.php` — Perluasan skema SQLite (`status_mkdt`, `is_batal`, `refund_paid`, `id_proyek`, `history_log`), `resetDataCache()` di `setUp()`, dan 5 unit test baru untuk unverify, guard alasan/status, promo Rp 0, dan NUP fallback.
- **Kontrak JSON Utama (Pure JSON / `RAW_JSON_READY`):**
  - `POST /api/tagihan/booking/batal-verifikasi`
    - Request: `{ id_mkdt: int, reason: string (3..500) }`
    - `200`: `{ success: true, messages: "...", data: BookingState, token: csrf_hash }`
    - `403` / `409` / `422` / `500`: `{ success: false, messages: "...", errors?: {...}, token: csrf_hash }`
  - `POST /api/tagihan/booking/terverifikasi` (support `?format=raw`)
    - Request: `{ id_proyek?, id_cluster?, id_jalan?, search? / search[value]?, draw?, start?, length?, page?, per_page? }`
    - Item fields: `id_mkdt, id_kavling, id_proyek, nama_proyek, nama_konsumen, nama_jalan, no_kavling, no_tipe_rumah, tipe_rumah, is_nup, no_nup, status_mkdt, is_batal, refund_paid, is_lunas, booking_fee, booking_tgl, is_promo, id_pembayaran, verified_by, verified_by_name, verified_at, shared_owners_count, can_unverify, unverify_blocked_reason`
    - Default `200`: `{ success: true, messages: "...", token: csrf_hash, draw, recordsTotal, recordsFiltered, data: [...], meta: { page, per_page, total } }`
    - `?format=raw` `200`: `{ success: true, messages: "...", data: { items: [...], meta: { page, per_page, total } }, token: csrf_hash }`
- **Hasil Verifikasi Teknis Backend:**
  - `php -l` pada ke-8 file PHP: **PASS** (0 syntax error).
  - `vendor/bin/phpunit tests/database/BookingInstallmentSeparationTest.php`: **PASS** (`Tests: 15, Assertions: 86`).
  - Uji regresi 3 suite (`BookingInstallmentSeparationTest`, `BookingPaymentAggregationTest`, `MkdtFinancialPaymentValidationTest`): **PASS** (`Tests: 22, Assertions: 102`).
  - `php spark routes`: 3 route `api/tagihan/booking/{verifikasi,batal-verifikasi,terverifikasi}` terdaftar dengan filter `cors honeypot login login csrf`.
  - Smoke check read-only MySQL lokal: `getVerifiedList()` & `getBooking(356)` berjalan tanpa SQL error (`27` baris terverifikasi terdeteksi pada proyek `13`).

### Fase 2B — Eksekusi Frontend & UI (`sigapp-frontend`)
- **Status:** `DONE` *(2026-11-19)*
- **File Inventory yang Diubah/Dibuat (4 File):**
  1. `public/assets/css/booking-fee.css` *(Diubah)* — Tambah `#modal_divisi3 .booking-fee-verified-meta` & `.booking-fee-lock-note`; perbaiki wrap `.booking-fee-history-actions`; tambah scoping `.list-tagihan-page` untuk header Tab 4, tabel `#table_booking_terverifikasi` (`#eef5ff`/`#2057a3`), soft tinted semantic badges (Section 4.3 `sigapp-ui-acuan`), mobile tabs 1 baris horizontal scroll pada `@media (max-width: 767.98px)`, serta `.swal2-container { z-index: 12000 !important; }`.
  2. `public/assets/js/keuangan/booking-verified.js` *(FILE BARU)* — IIFE mengekspos `window.BookingVerifiedTab = { init, reload, adjust }`: lazy DataTable server-side ke `POST api/tagihan/booking/terverifikasi`, CSRF sync di `dataSrc` & `error`, render 5 kolom client-side Pure JSON ter-escape (`keuEscapeHtml`/`keuEscapeAttribute`), event delegation untuk `.btn-unverify-booking`, `.btn-print-booking-verified`, `.btn-open-booking-modal`, tombol refresh `#btn_refresh_booking_terverifikasi`, dan Enter-search `#table_booking_terverifikasi_filter input`.
  3. `public/assets/js/tagihan-bayar-modal.js` *(Diubah)* — Tambah helper `keuFormatDate()`, `keuFormatDateTime()`, `window.confirmUnverifyBookingFee(options)`, dan `unverifyBookingFee()`; perbarui `renderBookingFee()` untuk metadata verifier, tombol `Batalkan Verifikasi`, & catatan terkunci; sinkronisasi `BookingVerifiedTab.reload()` + `isi_data(true)` pada `verifyBookingFee()`; fallback `e3` ke `activeProyekId()` pada `printBookingReceipt()`. Tetap kompatibel dengan `siteplan/keuangan.php` & `kavling/list-kavling.php`.
  4. `app/Views/keuangan/list-tagihan.php` *(Diubah)* — Load `assets/css/booking-fee.css` & `assets/js/keuangan/booking-verified.js` dengan `?v=<?= filemtime(...) ?>`; tambah Tab ke-4 `#tab_booking_terverifikasi-tab` (`BOOKING FEE TERVERIFIKASI`) dan panel `#tab_booking_terverifikasi`; hubungkan `shown.bs.tab`, `isi_data()`, `#btn_terapkan_filter`, dan `#btn_clear_filter_*` ke `window.BookingVerifiedTab`; perbaiki scoping search Tab 1 menjadi `#data_table_filter input` agar tidak mengganggu search input Tab 2/3/4.
- **Kepatuhan `sigapp-ui-acuan`:** 3-Tier CSS/JS eksternal (`filemtime`), CSS scoping `.list-tagihan-page` & `#modal_divisi3`, palet `#2057a3`, soft tinted badges, mobile 1-row horizontal tabs, z-index Swal2 `12000`.
- **Hasil Verifikasi Sintaks & Diff Frontend:**
  - `php -l app/Views/keuangan/list-tagihan.php`: **PASS** (`No syntax errors detected`).
  - `node --check public/assets/js/tagihan-bayar-modal.js` & `public/assets/js/keuangan/booking-verified.js`: **PASS** (`NODE_CHECK_OK`).
  - `git diff --check`: **PASS** (exit 0).

### Fase 3 & 3B — Inspeksi QA Independen & Remediasi Bug (`sigapp-qa`)
- **Putaran 1 (`2026-11-19 10:47`) — Sertifikat QA:** `FAIL`
  - **Yang Sudah Lulus 100%:**
    - `php -l` seluruh file PHP Backend & View (`0 syntax errors`), `git diff --check` 12 file target bersih.
    - `vendor/bin/phpunit tests/database/BookingInstallmentSeparationTest.php`: `15 tests, 86 assertions` **PASS**.
    - `php spark routes` (3 route `api/tagihan/booking/*`) & `php spark module:deadcode Keuangan` (0 dead code pada method baru/diubah) **PASS**.
    - Playwright E2E pada `http://localhost/sigapp.dev/tagihan/list` (akun `nisalis`):
      - Asset `booking-fee.css`, `tagihan-bayar-modal.js`, `keuangan/booking-verified.js` dimuat via `?v=filemtime` HTTP `200 OK`.
      - Mobile tabs `375x812`: 1 baris `nowrap` horizontal scroll (`clientWidth: 315`, `scrollWidth: 711`, `docScrollWidth: 360 <= 375`).
      - Kontrak Pure JSON `POST api/tagihan/booking/terverifikasi` (default & `?format=raw`, `27` baris di proyek `13`, `0` tag HTML), isolasi pencarian Tab 1 vs Tab 4.
      - Uji E2E pada `id_mkdt = 365` (`Dimas Pratama Samsudin`): validasi alasan `< 3` char (UI + API `422`), Unverify dari Tab 4 (`200`), sinkronisasi `#modal_divisi3` → Re-Verify (`200`), Unverify dari `#modal_divisi3` (Swal2 `z-index: 12000` di atas backdrop `1040`/modal `1050`) → Re-Verify kembali (DB bersih terverifikasi).
      - Audit Read-Only MySQL `history_log`: 2 record (`id: 4809`, `4810`) terekam lengkap (`module: 'keuangan'`, `action: 'batal_verifikasi_booking'`, `old_data`, `new_data`, `metadata.reason`, `add_by: 31`).
      - RBAC non-Keuangan (akun `citra` MKDT di `/list-kavling`): `can_unverify_booking: false`, `can_unverify: false`, `POST api/tagihan/booking/batal-verifikasi` ditolak `403`.
  - **Temuan Kegagalan & RCA (Domain: Frontend — `sigapp-frontend`):**
    1. **Broken Cross-Page Caller (`tagihan-bayar-modal.js` → `siteplan/master.js::isi_data`):**
       - `confirmUnverifyBookingFee()` dan `verifyBookingFee()` di `public/assets/js/tagihan-bayar-modal.js` memanggil global `isi_data(true)` untuk me-refresh tabel `list-tagihan.php`.
       - Di `/siteplan/view` (`public/assets/js/siteplan/master.js:2633`), fungsi global `isi_data()` adalah dispatcher pembuka modal kavling Siteplan. Ketika `#modal_divisi3` dibuka dari List Jatuh Tempo / Urgent Notification (`editdtt.length === 0`), pemanggilan `isi_data(true)` memicu alert error `"Terjad Kesalahan — Tidak ada kavling yang dipilih"`; dan saat `editdtt.length === 1`, `isi_data(true)` memicu `open_keuangan()` ganda yang me-reset `#keu-booking-fee-section` dan `#fm-keuangan`.
    2. **Double Reload Network Request di `list-tagihan.php`:**
       - `confirmUnverifyBookingFee()` dan `verifyBookingFee()` memanggil `window.BookingVerifiedTab.reload()` DAN `isi_data(true)` (yang juga memanggil `BookingVerifiedTab.reload()`), sehingga `POST api/tagihan/booking/terverifikasi` terpicu 2x beruntun.
- **Fase 3B (Bugfix Frontend oleh `sigapp-frontend` — `2026-11-19`):** `DONE`
  - **Audit `isi_data` lintas kode:** Hanya 3 definisi (`list-tagihan.php:719`, `siteplan/master.js:2633`, `siteplan/test.js:467`); `list-kavling.php` / `list-stock.php` / `cashout_kavling` tidak mendefinisikan `isi_data`.
  - **Perbaikan di `app/Views/keuangan/list-tagihan.php`:**
    - Mengangkat deklarasi `let riwayatBayarTable = null;` ke scope bersama `listTagihanTable` (lazy-init di tab `#tab_riwayat_bayar` tetap utuh).
    - `isi_data()` me-reload `listTagihanTable`, `riwayatBayarTable` (jika aktif), dan `window.BookingVerifiedTab.reload()` masing-masing 1x.
    - Mendaftarkan alias eksplisit `window.refreshTagihanList = function(keepModalOpen = false) { isi_data(keepModalOpen); };`.
  - **Perbaikan di `public/assets/js/tagihan-bayar-modal.js`:**
    - Menambahkan `syncTagihanViewsAfterBookingChange()` yang memanggil `window.refreshTagihanList(true)` jika tersedia, dengan fallback `window.BookingVerifiedTab.reload()` — tanpa pernah memanggil `isi_data()` global.
    - Mengalihkan seluruh 4 pemanggilan `isi_data(true)` (`confirmUnverifyBookingFee`, `verifyBookingFee`, `removeRiwayatBayar`, `save_keuangan`) ke `syncTagihanViewsAfterBookingChange()`.
  - **Hasil Syntax & Diff Check:** `php -l` (`0`), `node --check` (`0`), `git diff --check` (`0`), 0 referensi `\bisi_data\b` tersisa di `tagihan-bayar-modal.js`.
- **Putaran 2 (`2026-11-19 11:13 WIB` — `sigapp-qa`):** `PASS`
  - **Audit Codegraph & Grep:** 0 broken callers (0 referensi `isi_data` tersisa di `tagihan-bayar-modal.js`; seluruh 4 titik sync memakai `syncTagihanViewsAfterBookingChange()`; shims legacy `App\Controllers\Api\BookingPaymentController`, `App\Services\BookingPaymentService`, & `App\Repositories\BookingPaymentRepository` tetap utuh).
  - **CLI Checks:**
    - `php -l app/Views/keuangan/list-tagihan.php`: **PASS** (`0` syntax error).
    - `vendor/bin/phpunit tests/database/BookingInstallmentSeparationTest.php`: **PASS** (`15/15` tests, `86` assertions).
    - `php spark routes`: 3 route `api/tagihan/booking/{verifikasi,batal-verifikasi,terverifikasi}` aktif dengan filter `login`, `csrf`.
    - `git diff --check` (12 file target): **PASS** (`exit 0`).
    - `php spark module:deadcode Keuangan`: 0 dead code pada simbol baru.
  - **Playwright E2E (`/public/siteplan/view` & `/public/tagihan/list`, akun `nisalis`, 0 console errors, 0 HTTP 404/500):**
    - `/public/siteplan/view` (`editdtt.length === 0` dari List Jatuh Tempo & `editdtt.length === 1` dari kavling `#kav832`): `Unverify` & `Re-Verify` (`200 OK`) berjalan tanpa memanggil `siteplan/master.js::isi_data()`, tanpa error `"Tidak ada kavling yang dipilih"`, `tagihan/ambilsatu` hanya `1x`, form modal tidak ter-reset.
    - `/public/tagihan/list`: `Unverify` dari Tab 4 memicu tepat **1x** `POST api/tagihan/booking/terverifikasi` + **1x** `tagihan/list/ambil-grouped` (`200 OK`), bukan 2x; `Re-Verify` dari Modal Bayar dengan Tab 2 terbuka ikut me-reload Tab 1 (`1x`), Tab 2 (`1x`), dan Tab 4 (`1x`); mobile viewport `375x812` menjaga 4 tab 1 baris horizontal scroll (`scrollWidth: 711`, `clientWidth: 315`, warna `#2057a3`, asset `?v=filemtime`).
    - **Audit DB Read-Only:** `mkdt_booking_payment` untuk `id_mkdt = 365` terpulihkan `Terverifikasi` (`1500000`, `2026-07-10`, `verified_by: 31`); `history_log` merekam entri `batal_verifikasi_booking` (`id: 4811, 4812, 4813`).
- **Sertifikat QA Akhir:** `PASS` *(2026-11-19 11:13 WIB)*

### Fase 4 — Sinkronisasi Dokumentasi Modul (`sigapp-docs`)
- **Status:** `DONE` *(2026-11-19)*
- **Pendaftaran `ModuleCatalog.php` & `php spark module:sync Keuangan`:**
  - `app/Libraries/ModuleManager/ModuleCatalog.php`: Pada modul `Keuangan`, menambahkan `'keuangan/booking-verified.js'` ke `assets_js`, menambahkan `'assets_css' => ['booking-fee.css']`, dan menambahkan `'mkdt_booking_payment'` ke `owner_tables`.
  - `php spark module:sync Keuangan`: Berhasil (`[SYNCED] Keuangan -> docs\modules\Keuangan\MODULE.md`, `Last Synced: 2026-11-19 11:15:46 @ 8193fa7`, status `REFACTORED`, readiness `100/100`).
- **Pembaruan `docs/modules/Keuangan/MODULE.md` & `docs/modul-booking-fee.md`:**
  - `docs/modules/Keuangan/MODULE.md`: Sinkronisasi `Owner Table(s)` (`mkdt_booking_payment`), `Contracts` HTTP (`POST /api/tagihan/booking/batal-verifikasi`, `POST /api/tagihan/booking/terverifikasi`) & PHP Services (`BookingPaymentService::unverify()`, `getVerifiedList()`), `File Inventory` (termasuk subbagian `CSS (1 file): public/assets/css/booking-fee.css` & `JS (10 file): public/assets/js/keuangan/booking-verified.js`, `tagihan-bayar-modal.js`), kurasi `Key Functions` (backend & frontend), `Notes` (kontrak API & integrasi `HistoryService`), serta entri `Change Log` teratas `2026-11-19 [FEATURE]`.
  - `docs/modul-booking-fee.md`: Penambahan subbagian `### Pembatalan Verifikasi Keuangan` mencakup `POST /api/tagihan/booking/terverifikasi`, `POST /api/tagihan/booking/batal-verifikasi`, guard status `Batal`/refund, pembukaan kunci edit & re-verify tanpa menghapus kas/ledger/summary, serta pencatatan `history_log`.
  - `docs/MIGRATION_PROGRESS.md` & `docs/modul-notifikasi.md`: Tidak diubah (sesuai aturan mode `FEATURE` dan tidak ada perubahan notifikasi).
- **Verifikasi Fase 4 & `git status --short`:**
  - `php -l app/Libraries/ModuleManager/ModuleCatalog.php` -> `No syntax errors detected`.
  - `git diff --check -- app/Libraries/ModuleManager/ModuleCatalog.php docs/modul-booking-fee.md` -> exit 0; pada `docs/modules/Keuangan/MODULE.md` hanya baris 5 (`**Last Synced:** ...  `) bawaan generator `ModuleDocGenerator.php` yang memakai 2-spasi Markdown break (seluruh baris kurasi manual bersih).
  - File lingkup tugas ini pada `git status --short` (12 file implementasi/test + 5 file katalog/dokumentasi):
    - `M app/Config/Routes.php`
    - `M app/Controllers/Api/Keuangan/BookingPaymentController.php`
    - `M app/Controllers/Api/Keuangan/TagihanController.php`
    - `M app/Libraries/ModuleManager/ModuleCatalog.php`
    - `M app/Repositories/AuditTrail/HistoryRepository.php`
    - `M app/Repositories/Keuangan/BookingPaymentRepository.php`
    - `M app/Services/AuditTrail/HistoryService.php`
    - `M app/Services/Keuangan/BookingPaymentService.php`
    - `M app/Views/keuangan/list-tagihan.php`
    - `M public/assets/css/booking-fee.css`
    - `?? public/assets/js/keuangan/booking-verified.js`
    - `M public/assets/js/tagihan-bayar-modal.js`
    - `M tests/database/BookingInstallmentSeparationTest.php`
    - `M docs/modul-booking-fee.md`
    - `M docs/modules/Keuangan/MODULE.md`
    - `?? docs/debug-batal-verifikasi-booking-fee.md`
    - *(Artefak QA workspace terkait: `M writable/modules/deadcode_Keuangan.json`; perubahan tak terkait di workspace tetap dibiarkan utuh: modul `Siteplan`/`Transaksi` & `docs/TARGET_FEATURE_PROGRESS.md`).*

---

## 4. Bugfix Pasca-Rilis: Overflow Tombol di Card Booking Fee (`#modal_divisi3`)

**Status:** `PASS` *(Terverifikasi oleh `sigapp-qa` pada 2026-11-19 11:57)*
**Mode Tugas:** `BUGFIX` (Hanya 2 file Frontend — Backend & Routes tidak berubah)

### 4.1 Gejala
- Di dalam Modal Bayar (`#modal_divisi3`, partial `app/Views/keuangan/partials/modal_bayar_tagihan.php`), tombol aksi sisi kanan (`Kuitansi Booking` ketika berdampingan dengan `Batalkan Verifikasi` atau `Verifikasi`) di dalam `.booking-fee-history-actions` melampaui batas kanan `.booking-fee-card` sehingga terpotong/overflow.
- Pada blok metadata verifikasi (`.booking-fee-verified-meta`), separator `<span>&middot;</span>` menggantung di akhir atau awal baris ketika nama verifier dan timestamp terbungkus menjadi 2 baris.

### 4.2 Root Cause Analysis (RCA)
1. **Penyempitan kolom `col-md-4` + clipping global `.card`:**
   - Pada `app/Views/keuangan/partials/modal_bayar_tagihan.php` (baris 609–610), area `#keu-booking-fee-section` berada di dalam kolom `col-md-4 col-sm-12 col-lg-4` -> `.card`, sementara `public/assets/css/style.css` (baris 971–974) menerapkan `.card, .section-card { overflow: hidden !important; }`. Setiap child yang melebar melampaui kontainer langsung terklip di sisi kanan.
2. **Aturan flex non-wrapping pada baris riwayat booking:**
   - Di `public/assets/css/booking-fee.css`, `.booking-fee-history-row` sebelumnya memakai `justify-content: space-between` dengan `> span { flex: 1 1 180px }`, dan `.booking-fee-history-actions` memakai `flex: 0 0 auto; margin-left: auto; justify-content: flex-end;` tanpa pengaturan wrap/shrink pada `.btn`. Akibatnya pada lebar `col-md-4` (`1280x720` maupun `1440x900`), kombinasi tombol tidak dapat membungkus penuh dan mendorong tombol kanan melewati tepi `.booking-fee-card`.
3. **Separator middot & margin ikon inkonsisten di `renderBookingFee(booking)`:**
   - `public/assets/js/tagihan-bayar-modal.js` merender `<span>&middot;</span>` sebagai item flex terpisah di `.booking-fee-verified-meta` serta memberi class `mr-25` pada ikon di `verifyButton` dan `unverifyButton`.

### 4.3 Rincian Diff Perubahan (2 File Frontend)
1. `public/assets/css/booking-fee.css` (baris 1–116):
   - `#modal_divisi3 .booking-fee-card`: Menambah `box-sizing: border-box; max-width: 100%; overflow: hidden;`.
   - `#modal_divisi3 .booking-fee-history-row`: Menggunakan `gap: 0.5rem 0.65rem; max-width: 100%;` (menghapus `justify-content: space-between`).
   - `#modal_divisi3 .booking-fee-history-row > span`: Mengubah menjadi `flex: 1 1 100%; min-width: 0; overflow-wrap: anywhere;`.
   - `#modal_divisi3 .booking-fee-history-actions`: Mengganti `flex: 0 0 auto; margin-left: auto; justify-content: flex-end;` menjadi `flex: 1 1 100%; flex-wrap: wrap; gap: 0.45rem; justify-content: flex-start; max-width: 100%; min-width: 0;`.
   - Menambahkan `#modal_divisi3 .booking-fee-history-actions .btn`: `align-items: center; display: inline-flex; flex: 1 1 auto; font-size: 0.76rem; gap: 0.3rem; justify-content: center; line-height: 1.25; max-width: 100%; min-width: 0; padding: 0.4rem 0.65rem; text-align: center; white-space: normal;`.
   - `#modal_divisi3 .booking-fee-verified-meta` & `> span`: Mengubah ke stack vertikal (`flex-direction: column; align-items: flex-start; gap: 0.2rem; max-width: 100%; overflow-wrap: anywhere;`) dengan `> span { max-width: 100%; min-width: 0; }`.
2. `public/assets/js/tagihan-bayar-modal.js` (`renderBookingFee(booking)`, baris 338–350):
   - Menghapus class `mr-25` pada ikon di `verifyButton` (`fas fa-check-circle`) dan `unverifyButton` (`fas fa-undo-alt`) agar konsisten dengan CSS `gap: 0.3rem`.
   - Menghapus `<span>&middot;</span>` pada `verifiedMeta` agar tidak ada middot menggantung saat membungkus 2 baris.

### 4.4 Hasil Bounding-Box QA (`sigapp-qa` — PASS `2026-11-19 11:57`)
- **Status Kesehatan Runtime:** `0` console error, `0` HTTP `404`/`500`.
- **Matriks Hasil Pengujian Bounding-Box:**

| Skenario / Halaman | Kasus Uji | Viewport | Hasil Pengukuran Bounding-Box & Layout |
|---|---|---|---|
| `/public/tagihan/list` | Terverifikasi (`id_mkdt: 365`) | `1440x900` | `leftOverflowPx=0`, `rightOverflowPx=0`, `scrollWidth <= clientWidth`, `overlap: false`, `hasMiddot: false`, `isTwoRows: true` |
| `/public/tagihan/list` | Terverifikasi (`id_mkdt: 365`) | `1280x720` | `leftOverflowPx=0`, `rightOverflowPx=0`, `scrollWidth <= clientWidth`, `overlap: false`, `hasMiddot: false`, `isTwoRows: true`; kedua tombol membungkus rapi ke 2 baris penuh di dalam batas `.booking-fee-card` |
| `/public/tagihan/list` | Terverifikasi (`id_mkdt: 365`) | `375x812` | `leftOverflowPx=0`, `rightOverflowPx=0`, `scrollWidth <= clientWidth`, `overlap: false`, `hasMiddot: false`, `isTwoRows: true` |
| `/public/tagihan/list` | Belum Diverifikasi (`id_mkdt: 411`) | `1440x900` | `leftOverflowPx=0`, `rightOverflowPx=0`, `scrollWidth <= clientWidth`, `overlap: false` |
| `/public/tagihan/list` | Belum Diverifikasi (`id_mkdt: 411`) | `1280x720` | `leftOverflowPx=0`, `rightOverflowPx=0`, `scrollWidth <= clientWidth`, `overlap: false` |
| `/public/siteplan/view` | Regresi Terverifikasi (`id_mkdt: 356`) | `1440x900` | `leftOverflowPx=0`, `rightOverflowPx=0`, `scrollWidth <= clientWidth`, `overlap: false`, `hasMiddot: false`, `isTwoRows: true` |
