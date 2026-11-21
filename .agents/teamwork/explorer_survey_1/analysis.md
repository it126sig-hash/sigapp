# Technical Analysis: Laporan Booking & Akad Overhaul

**Investigator**: Explorer 1 (`teamwork_explorer`)  
**Timestamp**: 2026-11-04  
**Project**: SIGAPP (CodeIgniter 4)  
**Target Module**: MKDT - Laporan Booking & Akad (`/laporan/booking-akad`)  

---

## 1. Executive Summary

Modul Laporan Booking & Akad saat ini menggunakan pola **Thin Controller → Service → Repository**, namun perhitungannya memiliki kekurangan mendasar:
1. **Rasio Akad Konvensional yang Keliru**: Menghitung `(jumlah akad yang terjadi di bulan M) / (jumlah booking yang terjadi di bulan M)` secara kuantitas kalender. Akibatnya, rasio bisa mencapai ratusan persen (misal Februari 2026: 1 booking, 14 akad = 1.400%) atau 0% saat tidak ada booking namun ada akad.
2. **Kategori Belum Lengkap (R1)**: Hanya mengagregasi 4 status (`booking`, `booking_aktif`, `booking_batal`, `akad`), belum mengakomodasi 6 status transaksi lengkap: `booking`, `wawancara`, `sp3k`, `batal`, `akad indent`, dan `akad AJB`.
3. **Ketiadaan Konversi Riil Berbasis Kode Transaksi (R2)**: Belum melacak *cohort conversion* riil menggunakan kode transaksi unik (`mkdt.uniq_id`), yaitu berapa persen dari transaksi booking pada bulan bersangkutan yang akhirnya mencapai akad (`akad_indent` maupun `akad AJB`).
4. **Modal Detail Belum Sesuai Kebutuhan (R3)**: Modal saat ini hanya menampilkan kolom dasar (`alamat_kavling`, `nama_konsumen`, `tanggal_transaksi`, `status_mkdt`, `nominal`), belum memuat nama sales muted, tipe pricelist + badge subsidi/komersil, status terakhir + catatan MKDT, dan tanggal status terakhir.
5. **Kerapihan Teks & Stabilitas Modal Unit (R4 & R3)**: Penataan rata kiri belum konsisten di semua kolom non-nominal/tanggal, serta dependensi JavaScript `modal_detail` (`window.loaded`, Select2, Flatpickr) perlu dijamin agar pemanggilan modal kavling dari dalam modal laporan tidak mengalami runtime error.

---

## 2. File Inventory & Architecture

Modul terdaftar di `app/Libraries/ModuleManager/ModuleCatalog.php` dalam grup bisnis **`MKDT`**.

| Layer | File Path | Status / Peran |
|---|---|---|
| **Routes** | `app/Config/Routes.php` (lines 463–483) | Mendaftarkan route Web (`GET laporan/booking-akad`) dan API (`POST api/laporan/booking-akad/summary`, `POST api/laporan/booking-akad/detail`) dengan filter `login`. |
| **Web Controller** | `app/Controllers/Web/BookingAkadReportController.php` | Thin Controller: pengecekan hak akses `laporan/booking-akad`, resolusi `ActiveProyekService`, pemuatan tahun aktif, render template `laporan/booking-akad`. |
| **Api Controller** | `app/Controllers/Api/BookingAkadReportController.php` | Thin Controller: validasi otorisasi & active project, handling AJAX POST `summary` & `detail`, response JSON standar dengan CSRF token refresh. |
| **Service Layer** | `app/Services/BookingAkadReportService.php` | Business logic: orchestrator range tahun, kalkulasi agregasi 12 bulan, kalkulasi rasio konversi riil (R2), normalisasi parameter server-side DataTables (R3). |
| **Repository Layer** | `app/Repositories/BookingAkadReportRepository.php` | Data access: query agregasi database MySQL (`mkdt`, `kavling`, `jalan`, `cluster`, `proyek`, `konsumen`, `tipe`, `hargajual`), filter per bulan/tahun, DataTables query builder. |
| **View (UI)** | `app/Views/laporan/booking-akad.php` | Blade/PHP view: filter proyek, selector tahun & pembanding, matrix table 12 bulan, container Chart.js, modal DataTables detail transaksi, partial `siteplan/partials/modal_detail`. |
| **Frontend Style** | `public/assets/css/laporan-booking-akad.css` | Styling tabel matriks perbandingan, badge rasio/status, card layout. |
| **Frontend Logic** | `public/assets/js/laporan-booking-akad.js` | AJAX handler, render header & sel matriks interaktif, Chart.js tren, DataTables server-side modal detail, handler klik tombol buka detail kavling. |
| **Migration** | `app/Database/Migrations/2026-10-28-000001_AddBookingAkadReport.php` | Migrasi menu `laporan/booking-akad` dan indeks DB `idx_mkdt_booking_akad_report`. |
| **Unit Test** | `tests/unit/BookingAkadReportServiceTest.php` | PHPUnit test untuk aggregasi service, error handling tahun pembanding, dan DataTables request normalization. |
| **Documentation** | `docs/modules/MKDT/MODULE.md` | Dokumentasi modul MKDT yang mencatat kontrak rute, controller, service, repository, dan change log. |

---

## 3. Database Schema & Status Tracking Analysis

### 3.1 Struktur Tabel Inti & Relasi
- **`mkdt` (Manajemen Konsumen dan Transaksi)**:
  Tabel master transaksi penjualan unit kavling. Primary key: `id_mkdt`. Foreign keys: `id_kavling`, `id_konsumen`, `id_hargajual`, `id_proyek`.
- **`kavling`**:
  Unit fisik properti. PK: `id_kavling`. FK: `id_jalan`, `id_tipe`, `id_legal`, `id_produksi`, `id_keuangan`, `id_mkdt`.
- **`jalan` & `cluster` & `proyek`**:
  Hirarki lokasi: `kavling.id_jalan` → `jalan.id_cluster` → `cluster.id_proyek` → `proyek.id_proyek`.
- **`konsumen`**:
  Data pembeli. PK: `id_konsumen`. Menyimpan `nama_konsumen`, `sales`, `id_sales`, `kode_referal`.
- **`tipe`**:
  Master tipe rumah proyek. PK: `id_tipe`. Menyimpan `tipe_rumah` (misal `29/60`, `36/72`), `no_tipe_rumah`, `is_subsidi`.
- **`hargajual`**:
  Pricelist resmi unit kavling. PK: `id`. Menyimpan `id_tipe`, `hargajual`, `hargajual_net`, `is_subsidi`.

### 3.2 Identitas Kode Transaksi Unik (`uniq_id`)
Berdasarkan audit schema dan kode di `app/Services/TransaksiService.php` (baris 857–881):
- Setiap transaksi awal saat booking dibuat memiliki `uniq_id` (string 23+ karakter acak, misal `67d7dcbd8200f7.27535517`).
- Ketika terjadi transaksi **Ganti Nama** atau **Ganti Kavling**, `uniq_id` lama **wajib dipertahankan** ke record `mkdt` baru (`$uniqId = $row->uniq_id`).
- Hasil audit aktual database pada 392 seluruh baris tabel `mkdt`:
  - `total_rows`: 392
  - `has_uniq_id`: 392 (100% terisi, 0 NULL).
  - Khusus proyek "Sanggar Indah Palastri" (ID Proyek 13): 242 baris, 242 terisi `uniq_id`.
- **Kesimpulan**: `m.uniq_id` adalah representasi valid dan definitif dari `kode_transaksi` untuk mengukur konversi riil per transaksi unik.

### 3.3 Logika Deteksi 6 Kategori Status Transaksi
Berdasarkan konvensi `SiteplanVisualStatusService`, `BackfillMkdtAkadIndentAndAjb`, dan model data `mkdt`:

| Status | Indikator Kondisi Database | Kolom Tanggal Terkait | Keterangan Bisnis |
|---|---|---|---|
| **Booking** | `m.booking_tgl IS NOT NULL` dan `(m.is_batal = 0 OR m.is_batal IS NULL)` | `m.booking_tgl` | Konsumen telah memesan kavling dan menyetor booking fee. |
| **Wawancara** | `m.wawancara = 1` ATAU `(m.wawancara_tgl IS NOT NULL AND m.wawancara_tgl > '1970-01-01')` | `m.wawancara_tgl` | Berkas konsumen telah melalui tahap wawancara perbankan. |
| **SP3K** | `m.sp3k = 1` ATAU `(m.sp3k_tgl IS NOT NULL AND m.sp3k_tgl > '1970-01-01')` | `m.sp3k_tgl` | Surat Penegasan Persetujuan Penyediaan Kredit (SP3K) bank telah terbit. |
| **Batal** | `m.is_batal = 1` ATAU `m.status_mkdt = 'Batal'` | `COALESCE(m.mkdt_batal_tgl, m.keuangan_batal_tgl, m.updated_at)` | Transaksi dibatalkan oleh MKDT/Keuangan/Konsumen. |
| **Akad Indent** | `(m.akad_indent = 1 OR m.is_ajb = 'PPJB')` DAN `(m.is_batal = 0 OR m.is_batal IS NULL)` | `m.akad_indent_tgl` | Konsumen telah melakukan penandatanganan akad kredit bertahap (PPJB) sebelum sertifikat/AJB terbit. |
| **Akad AJB** | `(m.akad = 1 OR m.status_mkdt = 'Akad' OR m.is_ajb = 'AJB')` DAN `(m.akad_indent = 0 OR m.akad_indent IS NULL)` DAN `(m.is_batal = 0 OR m.is_batal IS NULL)` | `m.akad_tgl` | Konsumen telah menyelesaikan akad jual beli (AJB) paripurna. |

### 3.4 Resolusi Status Terakhir (Latest Status Hierarchy)
Dalam riwayat transaksi satu konsumen, sebuah unit kavling dapat melewati beberapa milestone bertahap (Booking → Wawancara → SP3K → Akad Indent → Akad AJB). Untuk kolom **Status Terakhir** dan **Tanggal status terakhir** di modal detail (R3), evaluasi harus menggunakan prioritas mundur (dari milestone paling akhir ke paling awal):

```sql
CASE
    WHEN (m.is_batal = 1 OR m.status_mkdt = 'Batal') THEN 'Batal'
    WHEN (m.akad = 1 OR (m.akad_tgl IS NOT NULL AND m.akad_tgl > '1970-01-01')) 
         AND (m.akad_indent = 0 OR m.akad_indent IS NULL OR m.is_ajb = 'AJB' OR m.akad_tgl >= m.akad_indent_tgl) 
         THEN 'Akad AJB'
    WHEN (m.akad_indent = 1 OR (m.akad_indent_tgl IS NOT NULL AND m.akad_indent_tgl > '1970-01-01') OR m.is_ajb = 'PPJB') 
         THEN 'Akad Indent'
    WHEN (m.sp3k = 1 OR (m.sp3k_tgl IS NOT NULL AND m.sp3k_tgl > '1970-01-01')) 
         THEN 'SP3K'
    WHEN (m.wawancara = 1 OR (m.wawancara_tgl IS NOT NULL AND m.wawancara_tgl > '1970-01-01')) 
         THEN 'Wawancara'
    ELSE 'Booking'
END AS status_terakhir
```

Dan untuk **Tanggal Status Terakhir**:
```sql
CASE
    WHEN (m.is_batal = 1 OR m.status_mkdt = 'Batal') 
         THEN COALESCE(DATE(m.mkdt_batal_tgl), DATE(m.keuangan_batal_tgl), DATE(m.updated_at), m.booking_tgl)
    WHEN (m.akad = 1 OR (m.akad_tgl IS NOT NULL AND m.akad_tgl > '1970-01-01')) 
         AND (m.akad_indent = 0 OR m.akad_indent IS NULL OR m.is_ajb = 'AJB' OR m.akad_tgl >= m.akad_indent_tgl) 
         THEN m.akad_tgl
    WHEN (m.akad_indent = 1 OR (m.akad_indent_tgl IS NOT NULL AND m.akad_indent_tgl > '1970-01-01') OR m.is_ajb = 'PPJB') 
         THEN m.akad_indent_tgl
    WHEN (m.sp3k = 1 OR (m.sp3k_tgl IS NOT NULL AND m.sp3k_tgl > '1970-01-01')) 
         THEN m.sp3k_tgl
    WHEN (m.wawancara = 1 OR (m.wawancara_tgl IS NOT NULL AND m.wawancara_tgl > '1970-01-01')) 
         THEN m.wawancara_tgl
    ELSE m.booking_tgl
END AS tanggal_status_terakhir
```

---

## 4. Calculation Logic & Real Conversion (R2)

### 4.1 Definisi Konversi Real Berbasis Kode Transaksi
Pengukuran konversi riil mengadopsi model **Cohort Conversion**:
- **Populasi Basis (Penyebut / Denominator)**:
  Jumlah transaksi unik (`COUNT(DISTINCT m.uniq_id)`) yang melakukan booking pada bulan bersangkutan (`YEAR(m.booking_tgl) = $year AND MONTH(m.booking_tgl) = $month`).
- **Transaksi Terkonversi (Pembilang / Numerator)**:
  Dari transaksi unik yang booking pada bulan tersebut, berapa banyak yang **akhirnya mencapai akad** (baik mencapai `Akad Indent` maupun `Akad AJB`), tanpa dibatasi apakah akadnya terjadi di bulan yang sama atau bulan-bulan/tahun berikutnya:
  ```sql
  COUNT(DISTINCT CASE 
      WHEN (
          (m.is_batal = 0 OR m.is_batal IS NULL) AND m.status_mkdt != 'Batal'
          AND (
              m.akad = 1 
              OR m.akad_indent = 1 
              OR (m.akad_tgl IS NOT NULL AND m.akad_tgl > '1970-01-01')
              OR (m.akad_indent_tgl IS NOT NULL AND m.akad_indent_tgl > '1970-01-01')
              OR m.status_mkdt = 'Akad'
          )
      ) THEN m.uniq_id 
  END) AS booking_terkonversi_akad
  ```
- **Rasio Konversi Riil (%)**:
  $$\text{Rasio Konversi} = \frac{\text{booking\_terkonversi\_akad}}{\text{jumlah\_booking}} \times 100\%$$
- **Baris Total Tahunan**:
  $$\text{Total Konversi Tahunan} = \frac{\sum_{m=1}^{12} \text{booking\_terkonversi\_akad}_m}{\sum_{m=1}^{12} \text{jumlah\_booking}_m} \times 100\%$$

### 4.2 Analisis Pilihan Model Matriks Bulanan

Terdapat 2 kemungkinan interpretasi tata letak kolom 12 bulan:

#### Opsi A: Model Aktivitas Bulanan (Event Occurrence per Month)
- Kolom: `Booking`, `Wawancara`, `SP3K`, `Batal`, `Akad Indent`, `Akad AJB`, `Konversi (%)`.
- Nilai tiap kolom angka (Booking s.d. Akad AJB) merefleksikan **volume aktivitas yang terjadi di bulan tersebut**:
  - `Booking`: transaksi dengan `booking_tgl` di bulan M.
  - `Wawancara`: transaksi dengan `wawancara_tgl` di bulan M.
  - `SP3K`: transaksi dengan `sp3k_tgl` di bulan M.
  - `Batal`: transaksi dengan `mkdt_batal_tgl` di bulan M.
  - `Akad Indent`: transaksi dengan `akad_indent_tgl` di bulan M.
  - `Akad AJB`: transaksi dengan `akad_tgl` di bulan M.
- Kolom `Konversi (%)` dihitung dari cohort: berapa % dari booking bulan M yang akhirnya mencapai akad.
- **Kelebihan**: Memberikan gambaran produktivitas tim operasional/sales di tiap bulan berjalan (berapa yang wawancara bulan ini, berapa akad bulan ini).
- **Drilldown**: Mengklik angka sel membuka transaksi yang mengalami event tersebut pada bulan bersangkutan.

#### Opsi B: Model Kemajuan Cohort Booking (Booking Funnel per Cohort Month)
- Seluruh kolom mengacu pada **kumpulan booking di bulan M**:
  - `Booking`: total booking di bulan M.
  - `Wawancara`: berapa dari booking bulan M yang sudah wawancara.
  - `SP3K`: berapa dari booking bulan M yang sudah SP3K.
  - `Batal`: berapa dari booking bulan M yang batal.
  - `Akad Indent`: berapa dari booking bulan M yang mencapai Akad Indent.
  - `Akad AJB`: berapa dari booking bulan M yang mencapai Akad AJB.
  - `Konversi (%)`: persentase mencapai akad.
- **Kelebihan**: Konsistensi funnel 100% dari input booking awal.

**Rekomendasi Explorer**:
Opsi A (Aktivitas Bulanan + Konversi Riil Cohort) adalah arsitektur paling umum pada sistem laporan eksekutif properti dan sejalan dengan kode existing `getMonthlyAkadTotals`, di mana manajemen ingin melihat volume pencapaian akad per bulan sekaligus rasio keberhasilan konversi booking di bulan tersebut. Implementor dapat mendukung Opsi A dengan fleksibilitas filter di repository.

---

## 5. R3 Detail Modal Data Flow & Dependencies

### 5.1 Spesifikasi Kolom DataTables Detail Modal
Sesuai R3, modal detail memiliki 8 kolom:

| No | Nama Kolom | Sumber Data / Formula SQL | Format Tampilan | Rata Teks (R4) |
|---|---|---|---|---|
| 1 | **No** | Counter baris DataTables | `meta.row + meta.settings._iDisplayStart + 1` | Rata kiri (`text-left`) |
| 2 | **Kavling** | `j.nama_jalan`, `k.no_kavling`, `c.sales` | `<div>{nama_jalan} No. {no_kavling}</div><small class="text-muted d-block">{sales}</small>` | Rata kiri (`text-left`) |
| 3 | **Konsumen** | `c.nama_konsumen`, `COALESCE(thj.tipe_rumah, t.tipe_rumah, '-')`, `m.is_subsidi` | `<div>{nama_konsumen}</div><div class="small text-muted">{tipe} <span class="badge {badge-class}">{Subsidi/Komersil}</span></div>` | Rata kiri (`text-left`) |
| 4 | **Tanggal Booking** | `m.booking_tgl` | `formatDate(m.booking_tgl)` (dd-mm-yyyy) | Kolom tanggal |
| 5 | **Status Terakhir** | Hierarchy CASE SQL (Bagian 3.4) + Catatan MKDT (`COALESCE(NULLIF(m.keterangan_batal, ''), NULLIF(m.keterangan, ''), m.keterangan_status, '-')`) | `<span class="badge {status-badge}">{status_terakhir}</span><div class="small text-muted mt-25">{catatan}</div>` | Rata kiri (`text-left`) |
| 6 | **Tanggal Status Terakhir** | Hierarchy CASE SQL (Bagian 3.4) | `formatDate(tanggal_status_terakhir)` (dd-mm-yyyy) | Kolom tanggal |
| 7 | **Nominal** | `m.booking_fee` / `m.harga_jual_net` | `Rp {format_rupiah}` | Rata kanan (`text-right`) |
| 8 | **Aksi** | `m.id_kavling`, `m.id_mkdt`, `k.no_kavling`, `j.nama_jalan` | Button icon `<button class="btn btn-sm btn-info booking-akad-view-kavling"><i class="fa fa-eye"></i></button>` | Rata kiri / icon center |

### 5.2 Dependencies & Eliminasi Error Modal Unit Kavling (`siteplan/partials/modal_detail`)
Tombol aksi memanggil modal detail kavling (`#modal_detail`) melalui `assets/js/siteplan-detail-modal.js`.
Potensi break / runtime error yang ditemukan pada view existing:
1. **`window.loaded` Undefined**:
   Pada `siteplan-detail-modal.js` baris 497 terdapat kode `loaded['sm'] = true;`. Jika `var loaded` tidak diinisialisasi secara global di view, browser akan melempar `Uncaught ReferenceError: loaded is not defined`.
   **Solusi**: Tambahkan `window.loaded = window.loaded || {};` pada script pembuka view.
2. **Missing Select2 & Flatpickr Assets**:
   Modal detail kavling memiliki tab pricelist dan tagihan yang berisi input dengan class `.flatpickr-human-friendly` dan dropdown bank Select2. Jika `select2.min.css` / `select2.full.min.js` dan `flatpickr.min.css` / `flatpickr.min.js` belum dimuat di halaman laporan, form pada tab detail kavling akan mengalami error visual atau inisialisasi.
   **Solusi**: Pastikan dependensi vendor tersebut ter-load di header/footer `app/Views/laporan/booking-akad.php`.
3. **Modal Stacking (Z-Index)**:
   Karena modal detail transaksi (`#booking_akad_detail_modal`) berstatus terbuka, saat tombol aksi ditekan untuk membuka `#modal_detail`, kedua modal berada dalam kondisi tumpuk (*stacked modals*). Fungsi `initSigappModalStacking()` di `public/assets/js/scripts.js` mengelola z-index dinamis (1050 + offset) dan kelas `modal-open` pada `body`.
   **Solusi**: Pastikan backdrop stacking tetap terjaga dan event `hidden.bs.modal` pada `#modal_detail` tidak menghilangkan kelas `modal-open` di `body` jika `#booking_akad_detail_modal` masih aktif.

---

## 6. Performance & Architectural Considerations

1. **Thin Controller Pattern**:
   - `BookingAkadReportController` (Web/Api) harus tetap ramping (< 80 baris), hanya bertanggung jawab atas autentikasi, otorisasi menu, resolusi HTTP request, dan formatting response.
   - Seluruh logika bisnis (kalkulasi konversi, pemetaan label bulan, perlakuan 6 status) berada di `BookingAkadReportService`.
   - Seluruh eksekusi query (grouping, aggregation, DataTables filter/sort/pagination) berada di `BookingAkadReportRepository`.
2. **Optimasi Agregasi Database (Single-Pass Query)**:
   Hindari menjalankan 12 query terpisah per bulan. Gunakan query agregasi bersarang atau `SUM(CASE WHEN ...)` dan `COUNT(DISTINCT CASE WHEN ...)` dikelompokkan berdasarkan `YEAR(...)` dan `MONTH(...)`.
3. **Pemanfaatan Indeks DB**:
   Indeks existing pada tabel `mkdt`:
   - `idx_mkdt_booking_akad_report` (`booking_tgl`, `akad_tgl`, `status_mkdt`, `is_batal`).
   - Disarankan penambahan indeks gabungan jika belum ada:
     `idx_mkdt_status_flow` pada `(id_kavling, uniq_id, booking_tgl, akad_indent_tgl, akad_tgl)`.
4. **CSRF & Security**:
   Setiap request AJAX DataTables dan summary wajib membawa token CSRF aktif dan mengembalikan token baru (`csrf_hash()`) untuk mencegah expired token pada navigasi berulang.

---

## 7. Recommended Implementation Steps

1. **Backend / Repository Layer**:
   - Perbarui `BookingAkadReportRepository::getMonthlySummaryTotals()` untuk menghasilkan matriks 6 status + data numerator/denominator konversi per bulan dalam 1-2 query efisien.
   - Perbarui `BookingAkadReportRepository::getDetailRows()` dengan query join lengkap (`mkdt`, `kavling`, `jalan`, `cluster`, `proyek`, `konsumen`, `tipe`, `hargajual`) dan kolom SELECT sesuai spesifikasi Bagian 5.1.
2. **Backend / Service Layer**:
   - Perbarui konstanta `CATEGORIES` di `BookingAkadReportService`: `['booking', 'wawancara', 'sp3k', 'batal', 'akad_indent', 'akad_ajb']`.
   - Implementasikan formula rasio konversi riil `rasio_konversi` pada level bulanan dan baris total tahunan.
3. **Frontend / View & Assets Layer**:
   - Perbarui `public/assets/js/laporan-booking-akad.js`: sesuaikan definisi `categories`, update header 6 kolom, perbaiki DataTables modal detail (8 kolom R3, perataan teks R4, handling tombol aksi kavling).
   - Perbarui `app/Views/laporan/booking-akad.php`: lengkapi asset vendor CSS/JS (Select2, Flatpickr, global `window.loaded`), perbaiki thead modal detail.
   - Perbarui `public/assets/css/laporan-booking-akad.css`: styling badge untuk 6 status dan format perataan teks.
4. **Testing & QA**:
   - Perbarui unit test `tests/unit/BookingAkadReportServiceTest.php` untuk memvalidasi perhitungan 6 status dan rasio konversi berbasis `uniq_id`.
   - Uji data riil proyek "Sanggar Indah Palastri" (ID Proyek 13) di database lokal.
5. **Documentation (`sigapp-docs`)**:
   - Sinkronisasi `docs/modules/MKDT/MODULE.md` sesuai format standar SIGAPP.
