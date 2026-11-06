# Dokumentasi Teknis Modul: Laporan Booking & Akad

**Modul Induk:** `MKDT` (Manajemen Konsumen dan Transaksi)  
**Dokumen Induk:** [`docs/modules/MKDT/MODULE.md`](MODULE.md)  
**Versi:** 2.0 (Overhaul Milestone 1–4)  
**Tanggal Terakhir Diperbarui:** 2026-11-05  
**Status:** `PRODUCTION-READY`  

---

## 1. Executive Overview & Business Background

### 1.1 Latar Belakang Bisnis
Subfitur **Laporan Booking & Akad** (`/laporan/booking-akad`) merupakan instrumen analitik eksekutif dalam sistem SIGAPP yang digunakan oleh manajemen pengembang, divisi pemasaran (MKDT), dan keuangan untuk memantau produktivitas penjualan unit kavling perumahan selama rentang 1 tahun penuh. Laporan ini menyajikan performa bulanan (Januari hingga Desember) dalam struktur matriks 6 kolom status per tahun (`Booking`, `Wawancara`, `SP3K`, `Batal`, `Akad Indent`, `Akad AJB`), dengan persentase konversi kohort disematkan langsung di dalam sel `Booking`. Fitur ini juga mendukung perbandingan performa antara dua tahun terpilih (*side-by-side*).

### 1.2 Masalah Legacy: Anomali Rasio Kalender 1400%
Pada sistem lama, rasio konversi dihitung menggunakan metode kuantitas kalender:
$$\text{Rasio Legacy} = \frac{\text{Jumlah Transaksi Akad yang Selesai di Bulan } M}{\text{Jumlah Transaksi Booking Baru di Bulan } M} \times 100\%$$

Metode ini memiliki kelemahan fundamental:
1. **Mismatch Waktu (Time-Lag Anomaly):** Proses real estat dari pemesanan awal (*booking*) hingga penandatanganan akta jual beli (*akad*) memerlukan waktu berminggu-minggu hingga berbulan-bulan (pengumpulan berkas, wawancara bank, penerbitan SP3K, hingga persiapan akta PPJB/AJB).
2. **Distorsi Statistik:** Transaksi akad yang terealisasi pada suatu bulan hampir selalu berasal dari *booking* bulan-bulan sebelumnya. Apabila pada bulan berjalan terdapat 14 transaksi akad yang cair sementara hanya ada 1 booking baru, rumus legacy menghasilkan rasio $\frac{14}{1} \times 100\% = 1400\%$. Angka ini tidak realistis dan tidak merefleksikan laju konversi penjualan yang sebenarnya.

### 1.3 Solusi: Konversi Riil Berbasis Kohort Transaksi Unik (`mkdt.uniq_id`) Disematkan pada Kolom Booking
Melalui pembaruan ini, rasio konversi dihitung secara presisi berbasis **Cohort Real Conversion Rate**:
- Pelacakan dilakukan terhadap entitas transaksi unik berdasarkan `mkdt.uniq_id`.
- Setiap baris bulan $M$ mengukur kelompok (*cohort*) transaksi yang melakukan booking pada bulan $M$ tersebut, lalu menghitung berapa banyak di antara mereka yang sepanjang siklus hidupnya hingga saat ini telah berhasil mencapai tahap Akad (baik Akad Indent/PPJB maupun Akad AJB).
- Kolom mandiri *Konversi (%)* dihilangkan dari tabel matriks (menjadikannya matriks ramping 6 kolom per tahun). Nilai persentase konversi kohort riil ditampilkan langsung di bawah angka booking kuantitas utama (`.booking-cell-wrapper` & `.booking-konversi-sub`) pada setiap baris bulanan dan baris Total Tahun.
- Rasio konversi dibatasi secara matematis pada interval $[0.0\%, 100.0\%]$, memberikan data akurat bagi manajemen mengenai efektivitas konversi penjualan tanpa anomali persentase.

---

## 2. Routes & Security Permissions

### 2.1 Inventori Endpoint HTTP
Laporan Booking & Akad diatur dalam arsitektur terpisah antara antarmuka Web dan API:

| HTTP Method | URI Endpoint | Handler Controller | Tipe | Deskripsi |
|---|---|---|---|---|
| `GET` | `/laporan/booking-akad` | `App\Controllers\Web\BookingAkadReportController::index` | Web HTML View | Merender halaman utama laporan Booking & Akad dengan filter proyek dan dropdown tahun |
| `POST` | `/api/laporan/booking-akad/summary` | `App\Controllers\Api\BookingAkadReportController::summary` | REST JSON | Mengembalikan data ringkasan matriks agregasi 12 bulan (6 status & konversi kohort) untuk 1 atau 2 tahun |
| `POST` | `/api/laporan/booking-akad/detail` | `App\Controllers\Api\BookingAkadReportController::detail` | REST JSON DataTables | Menyajikan data server-side DataTables 7 kolom rincian unit kavling per bulan & status |

### 2.2 Otorisasi & Kontrol Akses (RBAC)
Akses ke menu dan API Laporan Booking & Akad dilindungi secara terpusat:
- **Pemeriksaan Akses:** Menggunakan `MenuAccessService::canAccessUrl((int) user_id(), 'laporan/booking-akad')`.
- **Level HTTP Response:** Jika pengguna tidak memiliki izin akses:
  - Web Controller mengembalikan status `403 Forbidden` dan merender template `errors/html/unauthorized`.
  - Api Controller mengembalikan JSON payload:
    ```json
    {
      "status": "error",
      "code": 403,
      "messages": "Anda tidak memiliki akses ke laporan Booking & Akad."
    }
    ```
- **Filter Proyek Aktif:** Pengguna wajib memilih proyek aktif pada sesi. Jika tidak ada proyek aktif, API mengembalikan kode HTTP `422 Unprocessable Entity` ("Pilih proyek aktif terlebih dahulu.").

### 2.3 Proteksi CSRF
Seluruh request `POST` pada endpoint API diwajibkan menyertakan token CSRF CodeIgniter 4. Pada setiap respon detail DataTables, token CSRF yang baru diregenerasi dan dikembalikan via field `token` (`csrf_hash()`) untuk mencegah token kedaluwarsa pada interaksi berturut-turut.

---

## 3. Architectural Data Flow

Pola arsitektur mengikuti standar resmi CodeIgniter 4 di SIGAPP: **Thin Controller → Rich Service → Dedicated Repository → Database**.

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                       BROWSER CLIENT (FRONTEND)                             │
│   app/Views/laporan/booking-akad.php                                        │
│   public/assets/js/laporan-booking-akad.js                                  │
│   public/assets/css/laporan-booking-akad.css                                 │
└───────────────────────┬───────────────────────────────▲─────────────────────┘
                        │ HTTP POST (AJAX / DataTables) │ JSON Response
                        ▼                               │
┌───────────────────────────────────────────────────────┴─────────────────────┐
│                       WEB / API CONTROLLERS                                 │
│   app/Controllers/Web/BookingAkadReportController.php                       │
│   app/Controllers/Api/BookingAkadReportController.php                       │
│   - Validasi sesi & MenuAccessService::canAccessUrl()                       │
│   - Delegasi parameter ke Service                                           │
└───────────────────────┬───────────────────────────────▲─────────────────────┘
                        │ Method Invocation             │ Data Array
                        ▼                               │
┌───────────────────────────────────────────────────────┴─────────────────────┐
│                          SERVICE LAYER                                      │
│   app/Services/BookingAkadReportService.php                                 │
│   - Konstanta 6 status transaksi (CATEGORIES)                               │
│   - Perhitungan konversi kohort riil (bulanan & total tertimbang tahunan)   │
│   - Resolusi status terakhir bertingkat (Status Precedence)                 │
│   - Fallback catatan transaksi bertingkat & format tanggal aman             │
│   - Pemformatan baris modal detail 7 kolom                                  │
└───────────────────────┬───────────────────────────────▲─────────────────────┘
                        │ Query Params & Normalization  │ Raw Rows
                        ▼                               │
┌───────────────────────────────────────────────────────┴─────────────────────┐
│                        REPOSITORY LAYER                                     │
│   app/Repositories/BookingAkadReportRepository.php                          │
│   - getMonthlyStatusTotals(): Single-pass SQL aggregation                   │
│   - getDetailRows(): Server-side DataTables query                           │
│   - Multi-table JOIN (mkdt, kavling, jalan, cluster, konsumen, hargajual)   │
│   - Safe zero-date filtering (> '1970-01-01')                               │
└───────────────────────┬───────────────────────────────▲─────────────────────┘
                        │ SQL Queries                   │ Result Sets
                        ▼                               │
┌───────────────────────────────────────────────────────┴─────────────────────┐
│                      DATABASE (MySQL / MariaDB)                             │
│   Tabel: mkdt, konsumen, kavling, jalan, cluster, proyek, hargajual, tipe   │
└─────────────────────────────────────────────────────────────────────────────┘
```

---

## 4. The 6 Statuses Pipeline & Precedence Hierarchy

### 4.1 Definisi 6 Kategori Status Resmi (Requirement R1)
Setiap transaksi yang tercatat di tabel `mkdt` dipartisi secara eksklusif ke dalam salah satu dari 6 kategori status transaksi:

| # | Kategori Status | Key Teknis | Warna Badge | Deskripsi Bisnis |
|---|---|---|---|---|
| 1 | **Booking** | `booking` | `secondary` (`badge-light-secondary`) | Unit telah dipesan konsumen (booking fee terbayar) dan belum memasuki tahap lanjutan. |
| 2 | **Wawancara** | `wawancara` | `warning` (`badge-light-warning`) | Konsumen telah melewati tahap wawancara perbankan KPR. |
| 3 | **SP3K** | `sp3k` | `primary` (`badge-light-primary`) | Surat Penegasan Persetujuan Penyediaan Kredit (SP3K) telah diterbitkan bank. |
| 4 | **Batal** | `batal` | `danger` (`badge-light-danger`) | Transaksi dibatalkan oleh konsumen atau ditolak perbankan. |
| 5 | **Akad Indent** | `akad_indent` | `info` (`badge-light-info`) | Perjanjian Pengikatan Jual Beli (PPJB) telah dilaksanakan untuk unit indent. |
| 6 | **Akad AJB** | `akad_ajb` | `success` (`badge-light-success`) | Akta Jual Beli (AJB) resmi telah ditandatangani di hadapan Notaris/PPAT. |

### 4.2 Pemetaan Logika Database & Agregasi Event-Based (Database Mapping & Event-Based Totals)
Dalam `BookingAkadReportRepository::getMonthlyStatusTotals()`, `getAvailableYearBounds()`, dan `compileDetailQuery()`, masing-masing kolom status dihitung berdasarkan kondisi kualifikasi tahap dan ekspresi tanggal efektif (`stageDateSql()`) pada bulan $M$ bersangkutan:

| Kategori Status | Tanggal Aktivitas Acuan | Kriteria Kualifikasi & Query SQL Event-Based |
|---|---|---|
| **Booking** | `m.booking_tgl` | `m.booking_tgl IS NOT NULL AND m.booking_tgl > '1970-01-01'` dalam rentang bulan $M$ (`m.booking_tgl >= $startDate AND m.booking_tgl < $endDate`). Juga menghitung kohort `total_booking_cohort` dan `booking_converted_akad` (`akadAjbConditionSql() OR akadIndentConditionSql()`). |
| **Wawancara** | `stageDateSql('m.wawancara_tgl')` | `wawancaraConditionSql()`: `(COALESCE(m.wawancara, 0) = 1 OR (m.wawancara_tgl IS NOT NULL AND m.wawancara_tgl > '1970-01-01'))` dengan tanggal efektif dalam rentang bulan $M$. |
| **SP3K** | `stageDateSql('m.sp3k_tgl')` | `sp3kConditionSql()`: `(COALESCE(m.sp3k, 0) = 1 OR (m.sp3k_tgl IS NOT NULL AND m.sp3k_tgl > '1970-01-01') OR (m.sp3k_no IS NOT NULL AND TRIM(m.sp3k_no) != '' AND TRIM(m.sp3k_no) != '-'))` dengan tanggal efektif dalam rentang bulan $M$. |
| **Batal** | `batalDateSql()` | `(COALESCE(m.is_batal, 0) = 1 OR COALESCE(m.status_mkdt, '') = 'Batal')` dengan tanggal pembatalan efektif (`COALESCE(m.mkdt_batal_tgl, m.keuangan_batal_tgl, m.updated_at, m.booking_tgl)`) dalam rentang bulan $M$. |
| **Akad Indent** | `stageDateSql('m.akad_indent_tgl')` | `akadIndentConditionSql()`: `(COALESCE(m.akad_indent, 0) = 1 OR (m.akad_indent_tgl IS NOT NULL AND m.akad_indent_tgl > '1970-01-01'))` dengan tanggal efektif dalam rentang bulan $M$. |
| **Akad AJB** | `stageDateSql('m.akad_tgl')` | `akadAjbConditionSql()`: `(COALESCE(m.akad, 0) = 1 OR (m.akad_tgl IS NOT NULL AND m.akad_tgl > '1970-01-01'))` dengan tanggal efektif dalam rentang bulan $M$. |

#### Fallback Tanggal Efektif Tahap (`stageDateSql`):
Untuk memastikan transaksi yang telah memiliki flag tahap (`= 1`) atau nomor SP3K valid (`sp3k_no` bukan kosong/`'-'`) tetapi tanggal tahapnya masih bernilai `'0000-00-00'` atau `NULL` tidak terlewat dari perhitungan bulanan, ekspresi `stageDateSql($column)` menggunakan fallback bertingkat dengan guard `> '1970-01-01'`:
```sql
COALESCE(
    CASE WHEN $column IS NOT NULL AND $column > '1970-01-01' THEN DATE($column) END,
    CASE WHEN m.booking_tgl IS NOT NULL AND m.booking_tgl > '1970-01-01' THEN DATE(m.booking_tgl) END,
    CASE WHEN m.updated_at IS NOT NULL AND m.updated_at > '1970-01-01' THEN DATE(m.updated_at) END,
    CASE WHEN m.created_at IS NOT NULL AND m.created_at > '1970-01-01' THEN DATE(m.created_at) END
)
```

#### Keselarasan 100% Query Modal Detail (`compileDetailQuery`):
Query server-side DataTables pada `BookingAkadReportRepository::compileDetailQuery(...)` menggunakan helper kondisi (`wawancaraConditionSql()`, `sp3kConditionSql()`, `akadIndentConditionSql()`, `akadAjbConditionSql()`) dan filter rentang tanggal efektif (`stageDateSql()`) yang persis sama dengan agregasi di atas. Hal ini menjamin bahwa jumlah baris (`recordsTotal`) pada modal detail ketika diklik selalu **identik 100%** dengan angka yang tertera pada sel matriks.

### 4.3 Hierarki Prevalensi Status (Status Precedence Hierarchy)
Dalam riwayat transaksi riil, satu baris data dapat memiliki multiple flag atau tanggal historis yang terisi (misalnya transaksi yang pernah wawancara, mendapat SP3K, lalu kemudian batal). Dalam penentuan **Status Terakhir** pada modal detail maupun SQL aggregation, diterapkan aturan hierarki ketat:

$$\mathbf{Batal} \succ \mathbf{Akad\ AJB} \succ \mathbf{Akad\ Indent} \succ \mathbf{SP3K} \succ \mathbf{Wawancara} \succ \mathbf{Booking}$$

1. **Batal:** Memiliki prioritas absolut. Jika transaksi dibatalkan (`is_batal = 1` atau `status_mkdt = 'Batal'`), status terakhirnya adalah **Batal**, mengesampingkan status lain yang pernah dicapai.
2. **Akad AJB:** Merupakan tahap akhir transaksi yang sukses. Mengesampingkan Akad Indent, SP3K, Wawancara, dan Booking.
3. **Akad Indent:** Tahap akta sementara (PPJB). Mengesampingkan SP3K, Wawancara, dan Booking.
4. **SP3K:** Persetujuan kredit bank. Mengesampingkan Wawancara dan Booking.
5. **Wawancara:** Tahap wawancara bank. Mengesampingkan status Booking awal.
6. **Booking:** Tahap dasar apabila belum ada tahapan lebih lanjut yang dicapai.

### 4.4 Penanganan Dirty Zero-Dates (`'0000-00-00'`) & Scanning Rentang Tahun Lengkap
Pada basis data peninggalan legacy, ditemukan tanggal berisi nilai `'0000-00-00'` atau `'0000-00-00 00:00:00'`.
- **Aturan Repositori:** Seluruh kondisi tanggal wajib menyertakan filter `> '1970-01-01'`. Klausa `IS NOT NULL` saja tidak cukup karena MySQL menganggap `'0000-00-00'` bukan NULL, yang menyebabkan `YEAR()` menghasilkan `0`.
- **Scanning Batas Tahun (`getAvailableYears`):** Repositori memindai seluruh tanggal aktivitas (`booking_tgl`, `wawancara_tgl`, `sp3k_tgl`, `akad_indent_tgl`, `akad_tgl`, dan `batalDateExpr`) dengan filter `> '1970-01-01'` untuk menjamin seluruh tahun aktivitas terdeteksi pada dropdown filter.
- **Aturan Service (`formatDate`):** String tanggal yang diawali `'0000'` atau bernilai `<= '1970-01-01'` secara otomatis disanitasi menjadi `-`, mencegah exception parsing tanggal pada PHP.

---

## 5. Cohort Real Conversion Math & Formulas (Requirement R2)

### 5.1 Metodologi Konversi Kohort
Konversi dihitung berdasarkan kelompok transaksi pemesanan awal (*booking cohort*) yang terjadi pada bulan $M$, kemudian ditelusuri apakah transaksi tersebut telah mencapai status Akad (baik **Akad Indent** maupun **Akad AJB**) hingga saat ini. Persentase ini disematkan di dalam sel kolom `Booking` pada baris bulanan dan Total Tahun.

### 5.2 Formula Konversi Bulanan
Untuk bulan $M \in [1, 12]$ pada tahun bersangkutan:

$$\text{Konversi}_M = \begin{cases} 
\text{round}\left(\dfrac{\text{Booking Cohort Terkonversi Akad}_M}{\text{Total Booking Cohort}_M} \times 100, 1\right), & \text{jika } \text{Total Booking Cohort}_M > 0 \\ 
0.0, & \text{jika } \text{Total Booking Cohort}_M = 0 
\end{cases}$$

Di mana:
- **`Total Booking Cohort` ($D_M$):** `COUNT(DISTINCT m.uniq_id)` untuk seluruh transaksi yang tanggal booking-nya (`m.booking_tgl`) berada dalam rentang bulan $M$ tahun berjalan.
- **`Booking Cohort Terkonversi Akad` ($N_M$):** `COUNT(DISTINCT m.uniq_id)` dari kelompok booking bulan $M$ tersebut yang memenuhi kriteria:
  - Tidak berstatus Batal (`is_batal = 0` dan `status_mkdt != 'Batal'`), DAN
  - Telah mencapai Akad Indent (`akad_indent = 1` atau `akad_indent_tgl > '1970-01-01'`) ATAU Akad AJB (`akad = 1` atau `akad_tgl > '1970-01-01'`).

### 5.3 Formula Total Tertimbang Tahunan (Annual Weighted Total)
Baris total tahunan pada footer tabel matriks dihitung bukan dengan merata-ratakan persentase 12 bulan (unweighted average), melainkan menggunakan **akumulasi tertimbang riil** dari seluruh cohort sepanjang tahun:

$$\text{Konversi}_{\text{Tahun}} = \begin{cases} 
\text{round}\left(\dfrac{\sum_{M=1}^{12} N_M}{\sum_{M=1}^{12} D_M} \times 100, 1\right), & \text{jika } \sum_{M=1}^{12} D_M > 0 \\ 
0.0, & \text{jika } \sum_{M=1}^{12} D_M = 0 
\end{cases}$$

### 5.4 Penjagaan Batas Nilai (Bounds Enforcement)
Untuk menjamin integritas statistik dan mencegah anomali matematika:
```php
$konversi = max(0.0, min(100.0, (float) $konversi));
```
Seluruh nilai persentase dijamin berada pada rentang $[0.0\%, 100.0\%]$ dengan format angka 1 desimal (misalnya `52.2%`).

---

## 6. Modal Detail 7 Columns Data Dictionary & Siteplan Integration

### 6.1 Kamus Data 7 Kolom Modal Detail (Requirement R3)
Ketika pengguna mengklik angka metrik pada tabel matriks bulanan, modal detail (`#booking_akad_detail_modal`) menampilkan tabel DataTables yang terdiri dari tepat 7 kolom:

| No | Kolom Tampilan | Alignment (R4) | Field Sumber JSON | Format & Renderer Tampilan |
|---|---|---|---|---|
| 1 | **No** | `text-center` (40px) | *Generated Index* | Nomor urut dinamis DataTables: `meta.row + meta.settings._iDisplayStart + 1` |
| 2 | **Kavling** | `text-left` | `row.kavling` (`nama_jalan`, `no_kavling`, `sales`) | Nama Jalan + No. Kavling.<br>Baris baru: `<small class="text-muted d-block">${sales}</small>` |
| 3 | **Konsumen** | `text-left` | `row.konsumen` (`nama_konsumen`, `tipe_rumah`, `is_subsidi`) | Nama Konsumen.<br>Baris baru: `<small class="text-muted d-block">${tipe_rumah}</small>` + badge `<span class="badge badge-light-success">Subsidi</span>` atau `badge-light-primary` (Komersil) |
| 4 | **Tanggal Booking** | `text-center` | `row.tanggal_booking` | Tanggal transaksi booking unit berformat `d-m-Y` (misal: `09-10-2024`) |
| 5 | **Status Terakhir** | `text-left` | `row.status_terakhir`, `status_terakhir_badge`, `tanggal_status_terakhir` | Tanggal milestone status terakhir: `<span class="booking-akad-status-date">${tglStatus}</span>`.<br>Badge status semantik: `<span class="badge badge-light-${badge}">${status}</span>` |
| 6 | **Catatan / Keterangan** | `text-left` | `row.catatan_mkdt` | Teks catatan transaksi MKDT bertingkat dari `mkdt` (`keterangan_batal` -> `keterangan` -> `keterangan_status`) atau `-` jika kosong |
| 7 | **Aksi** | `text-center` (70px) | `row.id_kavling`, `row.id_mkdt`, `row.kavling` | Tombol ikon mata: `<button class="btn btn-info btn-sm booking-akad-view-kavling" data-id-kavling="..." data-id-mkdt="..." title="Lihat Detail Kavling"><i class="fa fa-eye"></i></button>` |

### 6.2 Standar Perataan Teks (Requirement R4)
Sesuai standar UI acuan SIGAPP:
- **Rata Kiri (`text-left`):** Kolom teks naratif (`Kavling`, `Konsumen`, `Status Terakhir`, `Catatan / Keterangan`, serta nama bulan pada tabel matriks).
- **Rata Kanan (`text-right`):** Kolom angka metrik kuantitas transaksi (`Booking`, `Wawancara`, `SP3K`, `Batal`, `Akad Indent`, `Akad AJB`).
- **Rata Tengah (`text-center`):** Kolom nomor urut (`No`), kolom tanggal (`Tanggal Booking`), kolom tombol aksi (`Aksi`), serta persentase konversi (`.booking-konversi-sub`).

### 6.3 Integrasi Multi-Modal dengan Siteplan (`#modal_detail`) & Stacked Modal
Tombol aksi pada kolom ke-7 memungkinkan pengguna membuka modal detail teknis unit kavling siteplan (`app/Views/siteplan/partials/modal_detail.php`) langsung dari baris laporan tanpa meninggalkan halaman maupun menutup modal laporan.

#### Mekanisme Stacked Modal & Solusi Stabilitas Runtime:
1. **Dukungan Stacked Modal Global (`window.initSigappModalStacking`):**
   Fungsi `openKavlingDetailModal()` memanggil `window.initSigappModalStacking()` dari `scripts.js`. Hal ini menjamin:
   - Modal detail kavling siteplan (`#modal_detail`) dibuka secara bertumpuk (*stacked*) di atas modal laporan detail (`#booking_akad_detail_modal`).
   - Z-index modal kavling diatur ke `1070` (di atas backdrop modal laporan `1050`) sehingga interaksi form dan penutupan modal berjalan mulus tanpa konflik overlay.
2. **Eliminasi Konflik SyntaxError Deklarasi Global:**
   Deklarasi duplikat `var loaded = [];` pada `booking-akad.php` telah dihapus karena bertabrakan dengan `let loaded = [];` pada `scripts.js:346` yang sebelumnya memicu fatal `SyntaxError: Identifier 'loaded' has already been declared`.
3. **Pemuatan Vendor Script Kondisional:**
   Pemuatan `vendors.min.js` dibuat kondisional (`if (typeof window.jQuery === 'undefined')`) untuk mencegah inisialisasi ulang jQuery yang merusak plugin yang telah terpasang.
4. **Pemuatan Stylesheet Terisolasi:**
   Template `booking-akad.php` menyertakan stylesheet modal siteplan di bagian `<head>`:
   ```html
   <style><?= view('siteplan/partials/modal_detail_styles') ?></style>
   ```
5. **Pengamanan Objek dan Variabel Global:**
   - Fallback aman objek `dt_proyek.id_perumahan_sikumbang = dt_proyek.id_perumahan_sikumbang || ''`.
   - Inisialisasi aman: `window.base_url`, `window.loaded = window.loaded || []`, `window.editdtt`, `csrfName`, dan `csrfHash`.
6. **Pencegahan String Manipulation TypeError:**
   Sebelum memanggil fungsi `detail_kavling(sh, idKavling)`, parameter `nama_jalan` dan `no_kavling` dipastikan bertipe string murni via `String(button.data('nama-jalan') || '')` untuk mencegah error `.replace is not a function`.

---

## 7. Verification Resources (Dataset Proyek 13)

### 7.1 Profil Proyek Acuan Pengujian
- **ID Proyek:** `13`
- **Nama Proyek:** `SANGGAR INDAH PALASTRI` (SIP)
- **Nama Perusahaan:** `PT. Sanggarindah Karyasentosa Raya`
- **Total Record Transaksi MKDT:** `242` transaksi aktif/historis
- **Integritas Uniq ID:** 242 dari 242 record (100%) memiliki `uniq_id` valid dan unik.

### 7.2 Partisi Ground Truth 6 Status & Verifikasi 5 Kolom Status pada Proyek 13
Pemeriksaan menyeluruh pada basis data riil Proyek 13 menunjukkan partisi lengkap 100% berdasarkan hierarki status terakhir (`TC-R03`) serta kesesuaian penuh akumulasi 5 kolom status lintas tahun dengan kondisi database (`TC-R09`):

| Kategori Status | Aturan Kualifikasi Kolom / Hierarki | Jumlah Transaksi (Partisi Status Terakhir) | Contoh `id_mkdt` | Contoh `uniq_id` | Tanggal Contoh |
|---|---|---|---|---|---|
| **Batal** | `is_batal = 1` atau `status_mkdt = 'Batal'` | 9 | `165` | `6836afc13cfcc7.50933516` | 2025-07-29 |
| **Akad AJB** | `akad = 1` atau `akad_tgl > '1970-01-01'` | 171 | `145` | `67d7d7b6d15b28.06630173` | 2025-07-30 |
| **Akad Indent** | `akad_indent = 1` atau `akad_indent_tgl > '1970-01-01'` | 18 | `423` | `6aa1238460ce57.05065444` | 2026-10-07 |
| **SP3K** | `sp3k = 1`, `sp3k_tgl > '1970-01-01'`, atau `sp3k_no` valid (`!= ''`, `!= '-'`) | 15 | `456` | `6ad5d866e48c74.31619289` | 2026-10-27 |
| **Wawancara** | `wawancara = 1` atau `wawancara_tgl > '1970-01-01'` | 11 | `455` | `6aba25b1730ab4.93189517` | 2026-10-21 |
| **Booking** | `booking_tgl IS NOT NULL AND booking_tgl > '1970-01-01'` (Tahap dasar) | 18 | `459` | `6ad73741a423f3.42834707` | 2026-10-13 |
| **Total** | | **242** | | | |

*Uji Keseimbangan Partisi Status Terakhir (`TC-R03`):* $171 + 18 + 15 + 11 + 18 + 9 = 242$.
*Uji Akumulasi 5 Kolom Lintas Tahun (`TC-R09`):* Total penjumlahan matriks bulanan untuk masing-masing dari 5 kolom (`booking`, `wawancara`, `sp3k`, `akad_indent`, `akad_ajb`) pada seluruh tahun tersedia cocok 100% dengan jumlah baris database yang memenuhi kondisi kualifikasi kolom terkait (tanpa ada record ber-flag/nomor valid dengan zero-date yang terbuang berkat fallback `stageDateSql()`).

### 7.3 Benchmark Konversi Kohort Tahun 2026
Pada tahun booking 2026 di Proyek 13:
- **Total Booking Cohort:** 69 unit transaksi unik.
- **Terkonversi ke Akad:** 36 unit (18 Akad Indent + 18 Akad AJB).
- **Hasil Konversi Tahunan Riil:** $\dfrac{36}{69} \times 100\% = 52.17\% \approx \mathbf{52.2\%}$.

### 7.4 Suite Pengujian Otomatis
Untuk memvalidasi integritas logika bisnis dan data layer, jalankan perintah berikut:

```bash
# 1. Unit Test Feature Overhaul & Edge Cases (25 tests, 83 assertions)
vendor/bin/phpunit --no-coverage tests/unit/BookingAkadReportServiceOverhaulTest.php

# 2. Unit Test Service Standard (17 tests, 89 assertions)
vendor/bin/phpunit --no-coverage tests/unit/BookingAkadReportServiceTest.php

# 3. Database Live Integration Test pada Proyek 13 (9 tests, 561 assertions)
vendor/bin/phpunit --no-coverage tests/database/BookingAkadReportLiveDbTest.php

# 4. Standalone Runner Seluruh Tingkat (38 tests, 100% Pass)
php tests/bin/run_booking_akad_suite.php

# 5. Sinkronisasi & Verifikasi Dokumentasi Modul
php spark module:sync MKDT --check-only
```
