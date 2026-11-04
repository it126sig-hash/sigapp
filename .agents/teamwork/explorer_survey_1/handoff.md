# Handoff Report: Survey Phase — Laporan Booking & Akad Overhaul

**Agent**: Explorer 1 (`teamwork_explorer`)  
**Target Recipient**: Orchestrator / Implementer Engineers (`sigapp-engineer`, `sigapp-frontend`)  
**Working Directory**: `c:\Users\Salivs\Data\laragon\www\sigapp.dev\.agents\teamwork\explorer_survey_1\`  
**Date**: 2026-11-04  

---

## 1. Observation

1. **Rute & Controllers**:
   - `app/Config/Routes.php` (baris 463–483):
     ```php
     $routes->group('laporan', ['namespace' => 'App\Controllers\Web', 'filter' => 'login'], function ($routes) {
         ...
         $routes->get('booking-akad', 'BookingAkadReportController::index');
     });
     $routes->group('api/laporan/booking-akad', ['namespace' => 'App\Controllers\Api', 'filter' => 'login'], function ($routes) {
         $routes->post('summary', 'BookingAkadReportController::summary');
         $routes->post('detail', 'BookingAkadReportController::detail');
     });
     ```
   - `app/Controllers/Web/BookingAkadReportController.php` (baris 25–47): Memeriksa izin menu `laporan/booking-akad`, meresolusi active project dari `ActiveProyekService`, memanggil `getAvailableYears()`, dan me-render `template` dengan view `laporan/booking-akad`.
   - `app/Controllers/Api/BookingAkadReportController.php` (baris 25–73): Memeriksa hak akses dan active project, memanggil `reportService->getSummary()` dan `reportService->getDetail()`, mengembalikan response JSON standar + CSRF token hash.

2. **Kalkulasi & Service Layer Existing**:
   - `app/Services/BookingAkadReportService.php` (baris 10, 60–80, 94):
     ```php
     public const CATEGORIES = ['booking', 'booking_aktif', 'booking_batal', 'akad'];
     ...
     $summary[$year][$month]['rasio_akad'] = $booking > 0 ? round(($akad / $booking) * 100, 1) : 0.0;
     ```
     Kategori hanya 4 (belum memuat wawancara, SP3K, akad indent, akad AJB terpisah). Rasio dihitung naif dari `jumlah_akad / jumlah_booking` pada bulan yang sama.

3. **Query & Data Access Existing**:
   - `app/Repositories/BookingAkadReportRepository.php` (baris 32–66):
     `getMonthlyBookingTotals()` mengelompokkan berdasarkan `m.booking_tgl`, sedangkan `getMonthlyAkadTotals()` mengelompokkan berdasarkan `m.akad_tgl`. Ini membandingkan kuantitas kalender kejadian yang berbeda, bukan kohort transaksi.

4. **Identitas Kode Transaksi Unik di Database**:
   - Kolom `m.uniq_id` pada tabel `mkdt`:
     Hasil audit query database lokal pada tabel `mkdt`:
     - Total baris: 392 baris.
     - `has_uniq_id`: 392 (100% memiliki nilai non-empty).
     - Proyek "Sanggar Indah Palastri" (ID Proyek 13): 242 baris, seluruh 242 baris terisi `uniq_id`.
   - `app/Services/TransaksiService.php` (baris 860–864):
     ```php
     $row = $this->db->table('mkdt')->select('uniq_id')->where('id_mkdt', $opt['id_mkdt_old'])->get()->getRow();
     if ($row && $row->uniq_id) { $uniqId = $row->uniq_id; }
     ```
     `uniq_id` dipertahankan saat terjadi proses pergantian nama maupun pergantian unit kavling.

5. **Data Status & Field Transaksi**:
   - Migrasi `app/Database/Migrations/2026-11-02-000001_BackfillMkdtAkadIndentAndAjb.php`:
     - **Akad Indent**: `m.akad_indent = 1`, `m.is_ajb = 'PPJB'`, tanggal: `m.akad_indent_tgl`.
     - **Akad AJB**: `(m.akad_indent = 0 OR m.akad_indent IS NULL)` dan `(m.akad = 1 OR m.status_mkdt = 'Akad')`, `m.is_ajb = 'AJB'`, tanggal: `m.akad_tgl`.
   - Status lainnya:
     - **Batal**: `m.is_batal = 1` ATAU `m.status_mkdt = 'Batal'`. Tanggal: `COALESCE(m.mkdt_batal_tgl, m.keuangan_batal_tgl, m.updated_at)`.
     - **SP3K**: `m.sp3k = 1` ATAU `(m.sp3k_tgl IS NOT NULL AND m.sp3k_tgl > '1970-01-01')`. Tanggal: `m.sp3k_tgl`.
     - **Wawancara**: `m.wawancara = 1` ATAU `(m.wawancara_tgl IS NOT NULL AND m.wawancara_tgl > '1970-01-01')`. Tanggal: `m.wawancara_tgl`.
     - **Booking**: `m.booking_tgl IS NOT NULL` dan `(m.is_batal = 0 OR m.is_batal IS NULL)`. Tanggal: `m.booking_tgl`.

6. **View & Aksi Modal Kavling**:
   - `app/Views/laporan/booking-akad.php` (baris 123–132):
     Thead detail modal saat ini: `No`, `Jenis Laporan`, `Kavling & Cluster`, `Konsumen & Tipe`, `Tanggal`, `Status MKDT`, `Nominal`, `Aksi`.
   - `public/assets/js/siteplan-detail-modal.js` (baris 497):
     Mengakses `loaded['sm'] = true;`. Jika `window.loaded` belum didefinisikan, memicu `Uncaught ReferenceError: loaded is not defined`.
   - Dependensi CSS/JS Select2 dan Flatpickr belum dimuat di header view laporan, yang dibutuhkan saat modal detail kavling (`siteplan/partials/modal_detail`) dibuka.

---

## 2. Logic Chain

1. Dari **Observasi 4**, diketahui bahwa `mkdt.uniq_id` terisi 100% pada semua record dan secara konsisten dipertahankan saat perpindahan kavling/nama. Oleh karena itu, `m.uniq_id` adalah kunci transaksi unik yang memenuhi syarat Requirement R2 untuk melacak konversi riil.
2. Dari **Observasi 2 & 3**, logika perbandingan kuantitas per bulan (`jumlah_akad / jumlah_booking`) terbukti menghasilkan anomali (misal pada Februari 2026: 1 booking dan 14 akad menghasilkan 1.400%). Dengan mengganti formula menjadi:
   $$\text{Konversi Riil} = \frac{\text{Jumlah Unique Booking Bulan M yang Mencapai Akad Indent/AJB}}{\text{Total Unique Booking Bulan M}} \times 100\%$$
   maka hasil konversi merefleksikan konversi aktual dari cohort booking bulan tersebut (misal pada April 2026: 16 booking, 14 mencapai akad = 87.5%).
3. Dari **Observasi 5**, hierarki 6 status transaksi dapat didefinisikan secara deterministik:
   Batal → Akad AJB → Akad Indent → SP3K → Wawancara → Booking. Ini memungkinkan resolusi kolom `Status Terakhir` dan `Tanggal status terakhir` secara akurat pada setiap baris modal detail (R3).
4. Dari **Observasi 6**, tombol aksi modal kavling memerlukan inisialisasi `window.loaded = window.loaded || {};` serta pemuatan Select2 & Flatpickr vendor assets pada view agar saat dibuka tidak terjadi runtime error di browser.
5. Dari **Observasi 1**, arsitektur kode saat ini sudah mematuhi Thin Controller → Service → Repository. Perubahan hanya perlu dilakukan secara bedah (*surgical*) di Service, Repository, View, dan JS/CSS terkait tanpa merombak controller atau routing.

---

## 3. Caveats

1. **Definisi Kolom Matriks Bulanan (Aktivitas vs Cohort)**:
   Ada dua interpretasi bisnis: apakah 6 angka per bulan adalah jumlah aktivitas yang terjadi di bulan tersebut (Model A), atau jumlah status dari cohort booking bulan tersebut (Model B). Rekomendasi teknis adalah Model A (Aktivitas Bulanan) dengan kolom Konversi (%) yang mengukur cohort riil, karena selaras dengan kebutuhan manajemen memantau capaian bulanan tim.
2. **Keterangan Catatan MKDT**:
   Catatan pada tabel `mkdt` tersimpan dalam beberapa field: `keterangan_batal` (untuk status batal), `keterangan` (mkdt_keterangan), dan `keterangan_status`. Pengambilan catatan harus menggunakan fallback bertingkat: `COALESCE(NULLIF(m.keterangan_batal, ''), NULLIF(m.keterangan, ''), m.keterangan_status, '-')`.
3. **Data Dummy / Eksperimen**:
   Pada proyek Sanggar Indah Palastri terdapat 1 record (`id_mkdt = 349`) yang memiliki status `Akad Indent` (tgl: 2026-07-29) dan kemudian berlanjut ke `Akad AJB` (tgl: 2026-11-02). Logika status terakhir harus memprioritaskan Akad AJB jika tanggal akad AJB lebih baru atau sama dengan akad indent.

---

## 4. Conclusion

1. **Penyebab Utama Masalah**:
   Laporan Booking & Akad sebelumnya belum mengimplementasikan pelacakan cohort berbasis `uniq_id` dan belum membedakan 6 status transaksi (`booking`, `wawancara`, `sp3k`, `batal`, `akad indent`, `akad AJB`).
2. **Rekomendasi Solusi Teknis**:
   - **Repository (`BookingAkadReportRepository.php`)**: Implementasikan query agregasi bulanan single-pass yang menghitung 6 status transaksi dan cohort conversion berbasis `m.uniq_id`. Perbarui query `getDetailRows()` untuk memuat 8 kolom R3.
   - **Service (`BookingAkadReportService.php`)**: Perbarui konstanta kategori menjadi 6 status, hitung rasio konversi riil bulanan dan total tahunan.
   - **View (`booking-akad.php`)**: Sesuaikan header tabel DataTables detail modal, tambahkan aset Select2, Flatpickr, dan deklarasi variabel global `window.loaded`.
   - **Frontend JS/CSS (`laporan-booking-akad.js`, `laporan-booking-akad.css`)**: Sesuaikan definisi kategori matriks, format 8 kolom DataTables rata kiri (R4), dan styling badge.
   - **Dokumentasi (`docs/modules/MKDT/MODULE.md`)**: Update via sync dokumentasi.

---

## 5. Verification Method

1. **Unit Test Service**:
   Jalankan PHPUnit untuk menguji aggregasi dan formula baru:
   ```bash
   vendor/bin/phpunit tests/unit/BookingAkadReportServiceTest.php
   ```
2. **Database Verification Query**:
   Jalankan verifikasi data proyek aktif "Sanggar Indah Palastri" (ID Proyek 13):
   ```sql
   SELECT 
       YEAR(m.booking_tgl) AS tahun,
       MONTH(m.booking_tgl) AS bulan,
       COUNT(DISTINCT m.uniq_id) AS total_booking,
       COUNT(DISTINCT CASE WHEN (m.akad = 1 OR m.akad_indent = 1 OR m.status_mkdt = 'Akad') THEN m.uniq_id END) AS terkonversi_akad,
       ROUND(COUNT(DISTINCT CASE WHEN (m.akad = 1 OR m.akad_indent = 1 OR m.status_mkdt = 'Akad') THEN m.uniq_id END) / COUNT(DISTINCT m.uniq_id) * 100, 1) AS rasio_persen
   FROM mkdt m
   JOIN kavling k ON k.id_kavling = m.id_kavling
   JOIN jalan j ON j.id_jalan = k.id_jalan
   JOIN cluster cl ON cl.id_cluster = j.id_cluster
   WHERE cl.id_proyek = 13 AND m.booking_tgl >= '2026-01-01' AND m.booking_tgl < '2027-01-01'
   GROUP BY YEAR(m.booking_tgl), MONTH(m.booking_tgl);
   ```
3. **Kondisi Invalidasi**:
   - Jika rasio konversi pada bulan bersangkutan masih melebihi 100% atau bernilai > 0% saat total booking = 0.
   - Jika pembukaan modal detail unit kavling dari modal laporan memicu `ReferenceError: loaded is not defined` di console browser.
   - Jika teks nama konsumen atau kavling di tabel modal detail berformat rata kanan atau tengah (tidak rata kiri).
