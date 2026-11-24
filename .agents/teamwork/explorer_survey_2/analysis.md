# Analisis Arsitektur Frontend & UI: Laporan Booking & Akad

**Penyusun:** Explorer 2 (`teamwork_explorer`)  
**Fase:** Survey & Analisis Arsitektur Frontend  
**Tanggal:** 2026-11-04  
**Target Modul:** `app/Views/laporan/booking-akad.php`, `public/assets/js/laporan-booking-akad.js`, `public/assets/css/laporan-booking-akad.css`

---

## 1. Executive Summary

Modul Laporan Booking & Akad memerlukan pembaruan menyeluruh pada antarmuka frontend untuk mengakomodasi:
1. **R1 (6 Kategori Status Transaksi)**: Menggantikan 4 metrik status saat ini (`jumlah_booking`, `booking_aktif`, `booking_batal`, `jumlah_akad`) menjadi 6 kategori status lengkap: `booking`, `wawancara`, `sp3k`, `batal`, `akad_indent`, dan `akad_ajb`.
2. **R2 (Konversi Real Berbasis Kode Transaksi)**: Menampilkan kolom rasio konversi riil dari transaksi booking yang telah mencapai status akad (`akad_indent` atau `akad_ajb`) berbasis kode transaksi unik per bulan dan baris total tahunan.
3. **R3 (Kolom Detail Modal & Tombol Aksi)**: Merestrukturisasi tabel modal detail dari 8 kolom lama menjadi 7 kolom spesifik: No, Kavling, Konsumen, Tanggal booking, Status Terakhir, Tanggal status terakhir, dan Tombol aksi. Tombol aksi membuka modal detail unit kavling (`#modal_detail`) secara mulus di atas modal detail transaksi.
4. **R4 (Perataan Teks Sesuai Standar SIGAPP UI Acuan)**: Memastikan teks rata kiri (`text-left`), tanggal rata tengah (`text-center`), kuantitas/nominal rata kanan (`text-right`), dan kolom aksi/nomor rata tengah (`text-center`).

Penyelidikan mendalam menemukan beberapa temuan kritis terkait dependensi asset yang belum lengkap di view saat ini (khususnya CSS styling `#modal_detail`, variabel runtime JS, dan multi-modal stacking), yang jika tidak ditangani dengan tepat dapat menyebabkan runtime JS error atau kerusakan visual saat tombol aksi ditekan.

---

## 2. Analisis Rendering Matriks Laporan & Filter (R1 & R2)

### 2.1 Struktur Eksisting & Rendering Matriks
- **Lokasi View**: `app/Views/laporan/booking-akad.php` baris 81–87.
  Tabel menggunakan ID `#booking_akad_matrix` dengan struktur semantic:
  ```html
  <table id="booking_akad_matrix" class="table table-bordered table-hover mb-0">
      <thead id="booking_akad_matrix_head"></thead>
      <tbody id="booking_akad_matrix_body"></tbody>
      <tfoot id="booking_akad_matrix_foot"></tfoot>
  </table>
  ```
- **Mekanisme Rendering di JS** (`public/assets/js/laporan-booking-akad.js`):
  - Saat ini didefinisikan 5 kategori dalam array `categories`:
    ```javascript
    var categories = [
      { key: "booking", field: "jumlah_booking", label: "Jumlah Booking", shortLabel: "Booking", isDrilldown: true },
      { key: "booking_aktif", field: "booking_aktif", label: "Booking Aktif", shortLabel: "Aktif", isDrilldown: true },
      { key: "booking_batal", field: "booking_batal", label: "Booking Batal", shortLabel: "Batal", isDrilldown: true },
      { key: "akad", field: "jumlah_akad", label: "Jumlah Akad", shortLabel: "Akad", isDrilldown: true },
      { key: "rasio_akad", field: "rasio_akad", label: "Rasio Akad (%)", shortLabel: "Rasio (%)", isDrilldown: false }
    ];
    ```
  - `renderHeader(years)`:
    - Baris 1: Kolom Bulan (`<th rowspan="2" class="booking-akad-month-column">Bulan</th>`) + Header Tahun (`<th colspan="5" class="text-center booking-akad-year-heading...">Tahun</th>`).
    - Baris 2: Sub-heading 5 kategori per tahun.
  - `renderSummary(data)`:
    - Melakukan iterasi 12 bulan (`data.months`).
    - Setiap sel metrik ber-drilldown dirender melalui helper `detailButton(...)` yang menghasilkan tag `<button type="button" class="booking-akad-value-button" data-year="..." data-month="..." data-category="...">`. Jika nilai `0`, diberikan class `.is-zero` (warna teks `#9ca3af`, tanpa underline).
    - Sel rasio dirender melalui `rasioBadge(...)` yang menghasilkan badge pill (`.is-positive` berlatar hijau `#ecfdf3` / teks `#166534`, atau `.is-zero` berlatar abu-abu `#f3f4f6`).
  - Baris `tfoot`:
    - Menampilkan total tahunan per kategori (`data.totals`).

### 2.2 Penyesuaian untuk R1 & R2
1. **Ekspansi Kategori (6 Status + 1 Konversi = 7 Kolom per Tahun)**:
   Array `categories` di `laporan-booking-akad.js` wajib diperbarui menjadi:
   | No | Key Kategori | Field JSON | Label Lengkap | Short Label | Drilldown? | Tipe Tampilan |
   | :--- | :--- | :--- | :--- | :--- | :--- | :--- |
   | 1 | `booking` | `jumlah_booking` | Booking | Booking | Ya | `detailButton` (angka) |
   | 2 | `wawancara` | `jumlah_wawancara` | Wawancara | Wawancara | Ya | `detailButton` (angka) |
   | 3 | `sp3k` | `jumlah_sp3k` | SP3K | SP3K | Ya | `detailButton` (angka) |
   | 4 | `batal` | `jumlah_batal` | Batal | Batal | Ya | `detailButton` (angka) |
   | 5 | `akad_indent` | `jumlah_akad_indent` | Akad Indent | Akad Indent | Ya | `detailButton` (angka) |
   | 6 | `akad_ajb` | `jumlah_akad_ajb` | Akad AJB | Akad AJB | Ya | `detailButton` (angka) |
   | 7 | `konversi` | `rasio_konversi` | Konversi (%) | Konversi (%) | Tidak | `rasioBadge` (persentase) |

2. **Perubahan Colspan Header**:
   - `renderHeader`: `th` tahun berubah dari `colspan="5"` menjadi `colspan="7"`.
   - Jika mode pembanding aktif (2 tahun), total kolom adalah: 1 (Bulan) + 7 (Tahun A) + 7 (Tahun B) = 15 kolom.

3. **Penyesuaian Lebar Tabel & CSS**:
   - Pada `public/assets/css/laporan-booking-akad.css`:
     - Lebar minimum saat ini: `#booking_akad_matrix { min-width: 1080px; }` dan `is-comparison { min-width: 1980px; }`.
     - Rekomendasi pembaruan:
       - 1 Tahun (8 kolom): `min-width: 1200px;` (rata-rata 140px per kolom agar judul sub-heading tidak terpotong).
       - 2 Tahun (15 kolom): `min-width: 2200px;`.
   - Sticky header kolom pertama (`.booking-akad-month-column` dan `td:first-child`) sudah diatur dengan `position: sticky; left: 0; z-index: 2;` sehingga scroll horizontal tetap nyaman.

4. **Chart Tren Bulanan**:
   - Fungsi `chartDatasets` saat ini memetakan 4 metrik. Perlu disesuaikan untuk menampilkan seri bar grafik 6 status transaksi (atau status kunci: Booking, Wawancara, SP3K, Batal, Akad Indent, Akad AJB) dengan skema warna yang harmonis sesuai `chartColors`.

### 2.3 Mekanisme Filter Proyek & Tahun
- **Proyek Aktif**:
  - Diambil dari sesi via `ActiveProyekService`. Jika tidak ada proyek yang dipilih, `#booking_akad_no_project` muncul, dan seluruh kontrol filter serta bagian laporan dinonaktifkan (`hidden`). Pergantian proyek dilakukan secara global melalui navbar SIGAPP.
- **Tahun**:
  - Dropdown `#booking_akad_year_a` memuat daftar tahun dari backend (`$availableYears`).
  - Tombol `#booking_akad_add_comparison` memunculkan kontrol `#booking_akad_comparison_controls` yang memuat `#booking_akad_year_b` dan tombol hapus `#booking_akad_remove_comparison`.
  - Tombol `#booking_akad_apply` mengirim request AJAX POST ke `api/laporan/booking-akad/summary`.
  - Penanganan state loading dan pesan kesalahan (#booking_akad_error) sudah terstruktur rapi.

---

## 3. Analisis Mendalam Detail Modal (R3)

### 3.1 Pemicu & Mekanisme Pengambilan Data
- **Trigger**: Klik tombol `.booking-akad-value-button` di sel matriks.
- **Fungsi**: `openDetail(button)` membaca `data-year`, `data-month`, `data-category`, dan `data-label`.
- **Target Modal**: `#booking_akad_detail_modal` (menggunakan Bootstrap 4 Modal `modal-xl`).
- **Endpoint Data**: `api/laporan/booking-akad/detail` (method POST, server-side DataTables).
- **Payload Request**:
  ```json
  {
    "year": 2026,
    "month": 4,
    "category": "wawancara",
    "draw": 1,
    "start": 0,
    "length": 10,
    "search[value]": "",
    "order[0][column]": 3,
    "order[0][dir]": "asc",
    "csrf_token": "..."
  }
  ```

### 3.2 Pemetaan Kolom Lama vs Kolom Baru Sesuai R3
Tabel detail saat ini (`#booking_akad_detail_table`) memiliki kolom lama:
`No`, `Jenis Laporan`, `Kavling & Cluster`, `Konsumen & Tipe`, `Tanggal`, `Status MKDT`, `Nominal`, `Aksi` (8 kolom).

Berdasarkan **R3** pada `ORIGINAL_REQUEST.md`, tabel detail WAJIB memiliki **7 kolom** berikut:

| No | Nama Kolom Header | Field Data / Key JSON | Format & Komponen Visual | Alignment |
| :---: | :--- | :--- | :--- | :---: |
| 1 | **No** | (Index row) | Nomor urut dinamis DataTables: `meta.row + meta.settings._iDisplayStart + 1` | `text-center` (width: 45px) |
| 2 | **Kavling** | `nama_jalan`, `no_kavling`, `sales` | **Baris 1**: `Nama Jalan + ' No. ' + No Kavling` (font-weight: 700)<br>**Baris 2**: `<small class="text-muted d-block">Sales: {nama_sales}</small>` | `text-left` |
| 3 | **Konsumen** | `nama_konsumen`, `tipe_rumah`, `is_subsidi` | **Baris 1**: `{nama_konsumen}` (font-weight: 600)<br>**Baris 2**: `<small class="text-muted d-block">Tipe: {tipe_rumah}</small>`<br>**Baris 3**: Badge status subsidi/komersil:<br>`<span class="badge badge-success">Subsidi</span>` atau `<span class="badge badge-secondary">Komersil</span>` | `text-left` |
| 4 | **Tanggal Booking** | `booking_tgl` | Format tanggal Indonesia: `DD-MM-YYYY` via `formatDate(data)` | `text-center` |
| 5 | **Status Terakhir** | `status_terakhir`, `catatan_mkdt` | **Baris 1**: Badge status semantik (`booking-akad-badge-{status}`):<br>- Booking (Biru soft)<br>- Wawancara (Amber/Warning soft)<br>- SP3K (Primary soft)<br>- Batal (Merah soft)<br>- Akad Indent (Ungu soft)<br>- Akad AJB (Hijau soft)<br>**Baris 2**: Catatan/keterangan MKDT:<br>`<small class="text-muted d-block mt-25">{catatan_mkdt}</small>` | `text-left` |
| 6 | **Tanggal Status Terakhir** | `tanggal_status_terakhir` | Tanggal dari status terakhir (misal: tanggal wawancara, SP3K, batal, akad indent, atau akad AJB) via `formatDate(data)` | `text-center` |
| 7 | **Aksi** | `id_kavling`, `id_mkdt`, `nama_jalan`, `no_kavling` | Tombol ikon detail unit kavling:<br>`<button type="button" class="btn btn-info btn-sm booking-akad-view-kavling" data-id-kavling="{id}" data-id-mkdt="{id_mkdt}" data-nama-jalan="{jalan}" data-no-kavling="{no}" title="Lihat Detail Kavling"><i class="fa fa-eye"></i></button>` | `text-center` (width: 60px) |

---

## 4. Investigasi Kritis: Integrasi Tombol Aksi & Modal Detail Kavling

### 4.1 Anatomi `app/Views/siteplan/partials/modal_detail.php`
`modal_detail.php` adalah komponen modal besar (~2400 baris) dengan ID `#modal_detail`. Modal ini memuat:
- **Header Kavling**: Alamat lengkap (`.label_alamat`), ID Sikumbang (`.id_sikumbang`).
- **Kartu Harga Jual**: Rincian pricelist yang dapat di-collapse (`#detailHargaJualPricelist`).
- **Kartu Konsumen**: SPPTB, Nama Konsumen, Kode Referal (+ generate QR), NIK, NPWP, Kontak, dan Sales.
- **Tab Panel Konten**:
  1. *Ringkasan*: Status Kavling, Tanggal Akad, Bank, Notaris, SP3K, Progress Bangunan, Keuangan (Donut Chart Piutang vs Cash In), Cashout, Hutang Subkon, Retensi & Hasil Akad (Donut Chart).
  2. *Keuangan / Harga Jual*: Tagihan, Cash In & Cash Out, perbandingan harga.
  3. *Status & Akad*: Tanggal dan berkas legalitas.
  4. *Spesifikasi Teknis*: Gambar ilustrasi, denah arsitektural, spesifikasi teknis lantai/atap/pondasi.
  5. *Bangunan*: Konstruksi, foto konstruksi exterior/interior, listrik PLN, air tanah/PDAM.
  6. *Legal & Pajak*: Sertipikat, PBB, BPHTB, PPH 4(2), bukti bayar pajak.
  7. *Riwayat Perubahan*: Timeline riwayat perubahan kavling via endpoint `api/riwayat-perubahan/kavling`.

### 4.2 Dependensi Asset yang Dibutuhkan `#modal_detail`

Melalui penelusuran menyeluruh pada `siteplan-detail-modal.js` dan halaman pembanding (`cash-in.php`, `hasil-akad.php`, `list-kavling.php`), ditemukan dependensi wajib berikut:

#### A. Dependensi CSS
1. **`app/Views/siteplan/partials/modal_detail_styles.php` (CRITICAL)**:
   - **Status di `booking-akad.php` saat ini**: **BELUM DIMUAT** (Missing).
   - **Dampak jika hilang**: Seluruh layout `#modal_detail` (grid dashboard 2 kolom, flex layout sidebar 320px vs content, timeline cashout, badge tagihan, progress bar fisik bangunan) kehilangan style dasar sehingga modal tampil rusak dan berantakan.
   - **Solusi**: Tambahkan `<style><?= view('siteplan/partials/modal_detail_styles') ?></style>` di dalam `booking-akad.php`.

#### B. Dependensi JavaScript & Library Vendor
1. **`app-assets/vendors/js/charts/chart.min.js`**:
   - Dibutuhkan untuk render donat chart `#keuanganChart` dan `#hasilAkadChart` di tab Ringkasan.
   - **Status**: Sudah dimuat di `booking-akad.php` baris 149.
2. **`app-assets/vendors/js/pickers/flatpickr/flatpickr.min.js`**:
   - Dibutuhkan untuk input tanggal readonly/disabled pada pricelist (`#dt-pl_tgl_harga`, `#dt-tgl_harga`, dll.).
   - **Status**: Belum dimuat di `booking-akad.php`. Meskipun `scripts.js:setDatePicker` memiliki pengecekan `if (fp && ...)` yang aman, memuat `flatpickr.min.js` mencegah inkonsistensi elemen flatpickr human-friendly.
3. **`public/assets/js/siteplan-detail-modal.js`**:
   - Script inti yang menangani AJAX `siteplan/get/detail`, populasi form, manipulasi tab, dan event handlers.
   - **Status**: Sudah dimuat di `booking-akad.php` baris 169.

#### C. Variabel Global Runtime (JavaScript Context)
`siteplan-detail-modal.js` membaca beberapa variabel global di window. Di `booking-akad.php` baris 162–168, sebagian sudah ada:
```javascript
if (typeof dt_proyek === 'undefined') var dt_proyek = <?= json_encode($activeProyek ?? new \stdClass()) ?>;
if (typeof csrfName === 'undefined') var csrfName = '<?= csrf_token() ?>';
if (typeof csrfHash === 'undefined') var csrfHash = '<?= csrf_hash() ?>';
if (typeof not_found === 'undefined') var not_found = '<?= base_url('assets/images/not-found.png') ?>';
if (typeof editdtt === 'undefined') var editdtt = [];
if (typeof roleid === 'undefined') var roleid = '<?= user_id() ? (session()->get('role_id') ?? 0) : 0 ?>';
```

**Temuan Kritis Variabel Runtime**:
1. **`loaded` variable**:
   - Pada `siteplan-detail-modal.js` baris 497: `loaded['sm'] = true;` dan baris 560: `loaded = [];`.
   - Di `scripts.js` baris 346 terdapat `let loaded = [];`. Namun jika script dieksekusi dalam konteks berbeda atau sebelum `scripts.js`, penugasan properti ke variabel yang belum diinisialisasi akan memicu `ReferenceError: loaded is not defined`.
   - **Solusi**: Tambahkan `if (typeof loaded === 'undefined') var loaded = [];` pada inisialisasi inline script di `booking-akad.php`.
2. **`id_perumahan_sikumbang`**:
   - Pada `siteplan-detail-modal.js` baris 527–528:
     ```javascript
     let id_kavling_sikumbang = dt_proyek.id_perumahan_sikumbang + sh.data.nama_jalan.replace(/\s+/g, '').toUpperCase() + no_kavling_pad;
     ```
   - Jika `dt_proyek.id_perumahan_sikumbang` bernilai `undefined`, operasi string menghasilkan `"undefinedJALAN01"`. Jika `sh.data.nama_jalan` bernilai `undefined`/`null`, pemanggilan `.replace()` akan melempar fatal runtime error `TypeError: Cannot read properties of undefined (reading 'replace')`.
   - **Solusi**: Pastikan button aksi selalu memasang `data-nama-jalan` dan `data-no-kavling`, serta lakukan sanitasi pada pembentukan objek `sh`: `nama_jalan: String(button.data("nama-jalan") || "")`.

### 4.3 Multi-Modal Stacking & Z-Index Architecture
Ketika tombol aksi ditekan:
1. `#booking_akad_detail_modal` saat ini sedang terbuka (`.modal.show`).
2. Event handler tombol aksi memanggil `detail_kavling(sh, idKavling)`, yang melakukan request AJAX ke `siteplan/get/detail`.
3. Pada callback `success`, fungsi memanggil `$("#modal_detail").modal('show')`.

**Bagaimana SIGAPP Menangani Dua Modal Bertumpuk?**
Di `public/assets/js/scripts.js` baris 41–82 terdapat fungsi global `initSigappModalStacking()`:
- Saat modal baru dibuka (`show.bs.modal`):
  ```javascript
  const zIndex = 1050 + ($(".modal.show").not(this).length * 20);
  $(this).css("z-index", zIndex);
  ```
  - Modal pertama (`#booking_akad_detail_modal`): `z-index: 1050`, backdrop: `1040`.
  - Modal kedua (`#modal_detail`): `z-index: 1070`, backdrop: `1060` (dengan class `.sigapp-modal-stack`).
  - Hasil: `#modal_detail` berada tepat di atas `#booking_akad_detail_modal` tanpa menimpa atau merusak DOM modal pertama.
- Saat modal kedua ditutup (`hidden.bs.modal`):
  ```javascript
  const openModalCount = $(".modal.show").length;
  if (openModalCount > 0) {
    $("body").addClass("modal-open");
  }
  ```
  - Class `modal-open` dipertahankan pada elemen `body` sehingga modal pertama tetap bisa di-scroll secara normal tanpa bug layar terkunci.

**Rekomendasi Penguatan Tombol Aksi**:
Untuk menjamin Tombol Aksi 100% bebas error:
1. Sediakan helper fungsi terstandar di `laporan-booking-akad.js`:
   ```javascript
   function openKavlingDetail(idKavling, meta) {
     if (!idKavling) return;
     meta = meta || {};

     var sh = {
       id: "kav" + idKavling,
       data: {
         tipe: "kavling",
         id_kavling: idKavling,
         id_mkdt: meta.id_mkdt || null,
         id_keuangan: null,
         id_legal: null,
         id_produksi: null,
         nama_jalan: String(meta.nama_jalan || ""),
         no_kavling: String(meta.no_kavling || "")
       },
       data2: {
         harga_akhir: "-",
         id_hargajual: "",
         id_komplain: null,
         no_tipe_rumah: "",
         tipe_rumah: "",
         harga_akhir_tgl: "",
         harga_akhir_oleh: ""
       }
     };

     window.editdtt = [sh];
     if (typeof window.loaded === "undefined") {
       window.loaded = [];
     }

     if (typeof detail_kavling === "function") {
       detail_kavling(sh, idKavling);
     } else {
       console.error("[BookingAkad] Fungsi detail_kavling tidak tersedia.");
     }
   }
   ```
2. Hubungkan tombol aksi DataTables ke fungsi ini via event delegation `$(document).on("click", ".booking-akad-view-kavling", ...)` dengan membaca dataset dari atribut tombol.

---

## 5. Analisis Perataan Teks Tampilan (R4)

Berdasarkan **R4**, aturan perataan teks wajib seragam antara tabel matriks laporan dan tabel modal detail:
> *"Semua teks pada tabel laporan dan tabel modal detail dibuat rata kiri, kecuali kolom nominal (rata kanan) dan kolom tanggal."*

### 5.1 Matriks Laporan (`#booking_akad_matrix`)
1. **Kolom Bulan (`th` & `td:first-child`)**:
   - Teks nama bulan: **Rata Kiri (`text-left`)**.
   - Sudah terakomodasi di CSS (`.booking-akad-month-column { text-align: left; }`).
2. **Sub-heading Metrik**:
   - Label metrik pada header: Rata tengah (`text-center`) atau rata kanan agar selaras dengan angka di bawahnya.
3. **Sel Angka Kuantitas / Status (Booking, Wawancara, SP3K, Batal, Akad Indent, Akad AJB)**:
   - Nilai angka: **Rata Kanan (`text-right`)**, dibungkus tombol `.booking-akad-value-button` dengan `text-align: right`.
4. **Sel Rasio Konversi (%)**:
   - Persentase konversi: **Rata Tengah (`text-center`)** atau Rata Kanan (`text-right`), dibungkus `.booking-akad-rasio-badge`.
5. **Baris Total (`tfoot`)**:
   - Label "Total Tahun": **Rata Kiri (`text-left`)**.
   - Angka total: **Rata Kanan (`text-right`)**.
   - Total rasio: **Rata Tengah (`text-center`)**.

### 5.2 Tabel Modal Detail (`#booking_akad_detail_table`)
Penerapan class pada header (`th`) dan konfigurasi DataTables (`columns.className`):

| Index | Kolom | Kategori Data | Class Header (`<th>`) | Class DataTables (`className`) | Alasan / Rujukan R4 |
| :---: | :--- | :--- | :--- | :--- | :--- |
| 0 | No | Urutan baris | `text-center` | `text-center` | Nomor urut kompak |
| 1 | Kavling | Teks gabungan | `text-left` | `text-left` | Teks nama jalan, no kavling, dan nama sales |
| 2 | Konsumen | Teks gabungan | `text-left` | `text-left` | Teks nama konsumen, tipe, badge |
| 3 | Tanggal Booking | Tanggal | `text-center` | `text-center` | Pengecualian tanggal sesuai R4 |
| 4 | Status Terakhir | Teks & Catatan | `text-left` | `text-left` | Teks status dan keterangan MKDT |
| 5 | Tanggal Status Terakhir | Tanggal | `text-center` | `text-center` | Pengecualian tanggal sesuai R4 |
| 6 | Aksi | Tombol aksi | `text-center` | `text-center` | Tombol ikon terpusat |

### 5.3 Aturan CSS Spesifik di `laporan-booking-akad.css`
Untuk memastikan konsistensi tanpa terganggu style bawaan DataTables:
```css
/* Pastikan alignment sel detail modal strictly patuh R4 */
#booking_akad_detail_table th.text-left,
#booking_akad_detail_table td.text-left {
    text-align: left !important;
}

#booking_akad_detail_table th.text-center,
#booking_akad_detail_table td.text-center {
    text-align: center !important;
}

#booking_akad_detail_table th.text-right,
#booking_akad_detail_table td.text-right {
    text-align: right !important;
}

/* Penataan isi multi-line sel Kavling & Konsumen */
.booking-akad-cell-title {
    color: #111827;
    font-size: .86rem;
    font-weight: 700;
    line-height: 1.35;
}

.booking-akad-cell-subtitle {
    color: #6b7280;
    font-size: .74rem;
    line-height: 1.3;
}

.booking-akad-cell-note {
    color: #4b5563;
    font-size: .74rem;
    font-style: italic;
    line-height: 1.35;
}
```

---

## 6. Gap Analysis & Rencana Tindakan untuk Fase Implementasi

| Area | Kondisi Saat Ini | Target Kondisi (Requirements) | Tindakan yang Harus Dilakukan |
| :--- | :--- | :--- | :--- |
| **Header Matriks** | Colspan 5, 4 status + 1 rasio | Colspan 7, 6 status + 1 konversi riil | Update `renderHeader` dan `categories` di `laporan-booking-akad.js` |
| **Lebar Matriks** | `min-width: 1080px / 1980px` | `min-width: 1200px / 2200px` | Update CSS `#booking_akad_matrix` di `laporan-booking-akad.css` |
| **Kolom Modal Detail** | 8 kolom lama (ada Jenis Laporan & Nominal) | 7 kolom spesifik R3 (tanpa Jenis Laporan & Nominal) | Update `<th>` di `booking-akad.php` dan `columns` array di `laporan-booking-akad.js` |
| **Style Modal Detail** | `modal_detail_styles.php` belum dimuat | Style `#modal_detail` tampil rapi dan responsif | Sertakan `view('siteplan/partials/modal_detail_styles')` di `booking-akad.php` |
| **Dependensi Flatpickr** | Belum dimuat di view | Flatpickr tersedia untuk form `#modal_detail` | Tambahkan script vendor `flatpickr.min.js` di `booking-akad.php` |
| **JS Runtime Variables** | `loaded` belum didefinisikan secara eksplisit | Variabel aman dari ReferenceError | Tambahkan `if (typeof loaded === 'undefined') var loaded = [];` |
| **Tombol Aksi Kavling** | Inline handler langsung membaca data button | Fungsi terisolasi dengan sanitasi data lengkap | Standarisasi helper `openKavlingDetail` dan pastikan payload `sh` lengkap |
| **Perataan Teks (R4)** | Sebagian kolom modal belum eksplisit rata kiri | Teks rata kiri, tanggal tengah, nominal kanan | Terapkan class `text-left` dan `text-center` di header dan DataTables column defs |

---

## 7. Rekomendasi untuk Spesialis Penerus

1. **Untuk `sigapp-engineer` (Backend)**:
   - Sesuaikan `BookingAkadReportService::getSummary` dan `BookingAkadReportRepository` agar mengembalikan 6 kategori status (`jumlah_booking`, `jumlah_wawancara`, `jumlah_sp3k`, `jumlah_batal`, `jumlah_akad_indent`, `jumlah_akad_ajb`) serta kalkulasi `rasio_konversi` berbasis kode transaksi unik per bulan dan total tahunan.
   - Pada `BookingAkadReportRepository::getDetailRows`, sertakan field: `sales`, `is_subsidi`, `tipe_rumah` (nama tipe pricelist), `status_terakhir`, `tanggal_status_terakhir`, dan `catatan_mkdt` (`keterangan` dari tabel `mkdt`).
2. **Untuk `sigapp-frontend` (Frontend & UI)**:
   - Terapkan struktur tabel baru di `booking-akad.php` dan `laporan-booking-akad.js`.
   - Muat `modal_detail_styles` dan `flatpickr.min.js`.
   - Perbarui style CSS di `laporan-booking-akad.css` sesuai spesifikasi R4 dan semantic badges.
3. **Untuk `sigapp-qa`**:
   - Verifikasi pembukaan `#modal_detail` di atas `#booking_akad_detail_modal` tanpa runtime error di console browser (`TypeError`, `ReferenceError`).
   - Verifikasi bahwa setelah `#modal_detail` ditutup, scroll pada `#booking_akad_detail_modal` tetap berfungsi normal.
