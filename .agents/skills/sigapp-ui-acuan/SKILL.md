---
name: sigapp-ui-acuan
description: Standar desain UI, komponen visual, dan arsitektur pembuatan asset CSS/JS di SIGAPP (CodeIgniter 4 + Bootstrap 4 / Vuexy). Gunakan skill ini SELALU ketika membuat halaman baru, memodifikasi view CI4, membuat/mengubah file CSS di public/assets/css/, mendesain modal Bootstrap, tabel DataTables, laporan matriks, form/Select2/dropzone upload, tab mobile, divider section, atau menampilkan file dari FileAccessService agar seluruh desain modul konsisten dan seragam.
---

# SIGAPP UI Acuan & Standar Asset Style

Skill ini adalah **sumber kebenaran tunggal (Single Source of Truth)** untuk desain antarmuka (UI) dan cara pembuatan asset stylesheet/script di SIGAPP. Agen spesialis frontend (**`sigapp-frontend`**) dan auditor QA (**`sigapp-qa`**) WAJIB mematuhi aturan ini agar seluruh halaman, modal, tabel, dan laporan baru memiliki tampilan yang seragam dan tidak mengotori view dengan inline style.

---

## 1. Standar Pembuatan & Penempatan Asset Style (CSS & JS Architecture)

### 1.1 Hirarki Penempatan File CSS (3-Tier CSS System)
Jangan menaruh CSS di sembarang tempat. Gunakan hirarki berikut:

1. **Tier 1 — Global Stylesheet (`public/assets/css/style.css`)**
   - Otomatis dimuat di seluruh halaman melalui `app/Views/template/header.php`.
   - **Hanya** berisi `:root` design tokens, override tema global Bootstrap 4 / Vuexy, Select2 global, DataTables global, floating navbar/sidebar, notification center, dan mobile bottom navigation.
   - **DILARANG** menambahkan style spesifik satu halaman/modul ke dalam `style.css` kecuali komponen tersebut dipakai di seluruh aplikasi.

2. **Tier 2 — Module / Page Stylesheet (`public/assets/css/<kebab-case-modul>.css`) — WAJIB UNTUK MODUL/HALAMAN**
   - Setiap modul atau halaman fitur (CRUD, dashboard departemen, laporan) **WAJIB** memiliki file CSS eksternal sendiri di `public/assets/css/` (contoh acuan: `bpb.css`, `laporan-hasil-akad.css`, `laporan-cash-in.css`, `mgm.css`, `booking-fee.css`, `legal-pages.css`).
   - Logika JavaScript halaman juga wajib dipisah ke `public/assets/js/<kebab-case-modul>.js`.
   - **DILARANG** menulis blok `<style>...</style>` atau `<script>...</script>` panjang di dalam file View (`app/Views/...`), kecuali inisialisasi konfigurasi JSON singkat (`window.NAMA_MODUL_CONFIG = <?= json_encode(...) ?>`).

3. **Tier 3 — Shared Modal Partials (`app/Views/siteplan/partials/*_styles.php`)**
   - Khusus untuk modal lintas-halaman yang di-embed ke dalam Siteplan dan modul lain (seperti `app/Views/siteplan/partials/modal_detail_styles.php` dan `modal_tiket_masalah_styles.php`), style dikelola pada file partial dédiée atau file CSS di `public/assets/css/`.

### 1.2 Cara Memuat Asset & Cache Busting yang Benar (`filemtime`)
Karena `app/Views/template.php` memuat `template/header` sebelum `$content`, file view modul memuat vendor CSS tambahan dan CSS modul di bagian atas file view (atau JS di bagian bawah) dengan aturan:
- **WAJIB menggunakan `filemtime(FCPATH . 'assets/...')`** untuk cache-busting agar browser mengambil versi terbaru saat file diedit tanpa mematikan browser cache.
- **DILARANG menggunakan `?v=<?= time() ?>`** pada kode baru karena memaksa download ulang di setiap request.

```php
<!-- 1. Vendor CSS spesifik halaman (jika belum ada di template/header.php) -->
<link rel="stylesheet" type="text/css" href="<?= base_url('app-assets/vendors/css/tables/datatable/dataTables.bootstrap4.min.css') ?>">
<link rel="stylesheet" type="text/css" href="<?= base_url('app-assets/vendors/css/tables/datatable/responsive.bootstrap4.min.css') ?>">
<link rel="stylesheet" type="text/css" href="<?= base_url('app-assets/vendors/css/forms/select/select2.min.css') ?>">
<link rel="stylesheet" type="text/css" href="<?= base_url('app-assets/vendors/css/extensions/sweetalert2.min.css') ?>">

<!-- 2. Module CSS (WAJIB paling akhir setelah vendor CSS, menggunakan filemtime) -->
<link rel="stylesheet" type="text/css" href="<?= base_url('assets/css/nama-modul.css') ?>?v=<?= filemtime(FCPATH . 'assets/css/nama-modul.css') ?>">
```

Dan di bagian bawah view untuk JavaScript modul:
```php
<script src="<?= base_url('assets/js/nama-modul.js') ?>?v=<?= filemtime(FCPATH . 'assets/js/nama-modul.js') ?>"></script>
```

### 1.3 Aturan Scoping & Penamaan Class CSS
- **Wajib Gunakan Root Scope**: Setiap halaman harus memiliki class root unik pada `.app-content` (misal: `<div class="app-content content bpb-page">` atau `.hasil-akad-report-page`), dan setiap modal memiliki ID unik (`#bpb-detail-modal`, `#modal_detail`).
- **Prefix Semua Class Modul**: Gunakan prefix konsisten sesuai modul (`.bpb-*`, `.hasil-akad-*`, `.cash-in-*`, `.tm-*`).
- **Jangan Merusak Global Bootstrap**: Jangan pernah menulis selector telanjang seperti `.card { ... }`, `.table th { ... }`, atau `.modal-header { ... }` di dalam file CSS modul. Selalu awali dengan root scope (`.bpb-page .card`, `#bpb-detail-modal .modal-header`).
- **Hindari `!important` Berlebihan**: Hanya gunakan `!important` jika harus menimpa inline style dari plugin pihak ketiga (DataTables, Select2, Flatpickr) atau utilitas Bootstrap (`[hidden] { display: none !important; }`).

---

## 2. Design Tokens & Identitas Visual SIGAPP

Semua variabel warna dasar sudah didefinisikan pada `:root` di `public/assets/css/style.css`. Gunakan nilai-nilai ini secara konsisten dan **jangan pernah mengembalikan warna ungu bawaan Vuexy (`#7367f0`) atau biru Bootstrap mentah (`#007bff`)**.

### 2.1 Palet Warna Resmi
| Token / Fungsi | CSS Variable / Hex | Penggunaan Utama |
| :--- | :--- | :--- |
| **Primary Blue** | `var(--sigapp-primary-solid)` / `#2057a3` | Warna utama tombol, tab aktif, link, border fokus, aksen kiri divider/item |
| **Primary Dark (Hover)** | `var(--primary-dark)` / `#163f78` | State `:hover` dan `:focus` pada elemen primary |
| **Primary Soft / Tint** | `var(--primary-light)` / `#eaf2ff` atau `#edf4ff` | Background chip filter aktif, icon box, highlight baris/tab, alert primary |
| **Primary Gradient** | `var(--sigapp-primary-gradient)` / `linear-gradient(145deg, #2057a3 0%, #1f7a8c 100%)` | `.btn-primary`, `.nav-pills .nav-link.active`, hero card (`#modal_detail`) |
| **Info / Teal** | `var(--sigapp-info)` / `#1f7a8c` | Tahun pembanding laporan, badge info, sub-aksen sekunder |
| **Success / Hijau** | `var(--sigapp-success)` / `#2a9d8f` (`#15803d` untuk badge teks tegas) | Status selesai/disetujui/lunas |
| **Warning / Amber** | `var(--sigapp-warning)` / `#e9c46a` (`#d97706` untuk teks/ikon tegas) | Status menunggu/proses/perhatian |
| **Danger / Merah** | `var(--sigapp-danger)` / `#e76f51` (`#dc2626` untuk error/overdue) | Status ditolak/batal/terlambat, tombol hapus |
| **Page Background** | `var(--sigapp-light)` / `#f4f7f6` | Background utama `body` |
| **Modal Body BG** | `#f3f5f7` atau `#f5f7fa` | Background `.modal-body` agar `.card` putih di dalamnya kontras |
| **Surface / Card** | `#ffffff` (Subtle header/row: `#f8fafc` / `#f9fafb`) | Background `.card`, `.modal-header`, `.modal-footer`, `thead` |
| **Border Standar** | `#e5e7eb` (Card/Modal) & `#d8dde3` (Input/Table) | Border tipis dan bersih tanpa bayangan berat |
| **Text Hierarchy** | Utama: `#111827` · Sekunder: `#374151` · Muted/Label: `#6b7280` | Kontras teks untuk keterbacaan data padat |

### 2.2 Tipografi & Skala Ukuran
- **Font Utama**: `'Plus Jakarta Sans', sans-serif` (dimuat di `style.css`).
- **Font Monospace**: `ui-monospace, SFMono-Regular, Menlo, Consolas, monospace` — gunakan untuk Nomor Dokumen (BPB/NUP), ID Tiket, Hash, dan Timestamp log.
- **Catatan Penting Skala `rem`**: `public/assets/css/style.css` menetapkan `html { font-size: 80%; }`. Artinya `1rem` sudah berukuran kompak (~12.8px).
  - Judul Halaman (`.content-header-title`): bawaan template / `1.35rem–1.5rem` (`font-weight: 700`).
  - Judul Modal / Card (`.modal-title`, `.card-title`): `1rem–1.05rem` (`font-weight: 700`, `#111827`).
  - Section Divider (`.divider-text`): `0.85rem–1rem` (`font-weight: 700`, `#111827`).
  - Label Form / Info Label / Table Header: `0.72rem–0.78rem` (`font-weight: 700`, `#6b7280`).
  - Isi Tabel / Form Control / Body Copy: `0.82rem–0.9rem` (`color: #111827`).

### 2.3 Border Radius & Elevation
- Global `style.css` memberikan radius bulat pada elemen dasar (`.card` `16px`, `.btn`/`.form-control`/`.select2` `12px`).
- Untuk **komponen di dalam Modal Detail, Laporan Matriks, dan Halaman Operasional Padat**, gunakan card yang lebih tegas dan rapi:
  - Card dalam modal/laporan: `border: 1px solid #e5e7eb; border-radius: 8px; box-shadow: none;`
  - Modal content: `border: 1px solid #e5e7eb; border-radius: 10px;`

---

## 3. Pola Standar Layout Halaman (Page Archetypes)

Ketika diminta membuat halaman baru, pilih salah satu dari **2 pola baku** berikut (lihat contoh kode lengkap di [`references/ui-recipes.md`](references/ui-recipes.md)):

### Pola A: Halaman Operasional / CRUD Daftar & Form
*Acuan utama: `app/Views/bpb/index.php` + `public/assets/css/bpb.css` dan `app/Views/tiket_masalah/index.php`*
1. **Wrapper**: `<div class="app-content content <modul>-page">` dengan `.content-overlay`, `.header-navbar-shadow`, dan `.content-wrapper`.
2. **Content Header**: Judul halaman di kolom kiri (`.content-header-left`) dan tombol CTA utama (`.btn-primary`) di kolom kanan (`.content-header-right`).
3. **List Card**:
   - `.card-header.border-bottom.d-flex.flex-wrap.align-items-center.justify-content-between`: Judul daftar di kiri, grup tombol kontrol di kanan (`btn-outline-primary` untuk Filter + badge counter aktif, `btn-outline-secondary` untuk Refresh).
   - Baris **Active Filter Chips** (`.<modul>-active-filters`) tepat di bawah header untuk menampilkan chip filter yang sedang aktif beserta tombol hapus (`×`) dan "Bersihkan".
   - `.card-datatable.table-responsive` untuk tabel desktop, dilengkapi container `#<modul>-mobile-list` untuk tampilan kartu di layar mobile (`< 768px`).
4. **Filter Drawer (Slide-out Modal)**: Jika filter memiliki > 2 field, tempatkan di dalam modal slide-out kanan (`.modal-dialog-slideout` / `.modal-slide-in`), bukan memenuhi atas tabel.

### Pola B: Halaman Laporan & Matriks Analitik
*Acuan utama: `app/Views/laporan/hasil-akad.php` + `public/assets/css/laporan-hasil-akad.css` dan `app/Views/laporan/cash-in.php`*
1. **Top Filter Card (`.<modul>-filter-card`)**:
   - Sisi kiri: Judul menggunakan `.divider.divider-left` (`font-size: 1.1rem; font-weight: 700`) + subjudul informasi proyek aktif (`Ringkasan ... proyek <strong>...</strong>`).
   - Sisi kanan: Kontrol filter berjajar rapi (`display: flex; flex-wrap: wrap; align-items: flex-end; gap: .65rem;`) dengan label kecil di atas select (`font-size: .75rem; font-weight: 700; color: #6b7280`) dan tombol `Tampilkan` (`.btn-primary`).
2. **State Alerts**: Sediakan `.alert.alert-warning` saat proyek aktif belum dipilih dan `.alert.alert-danger` (`hidden`) untuk pesan gagal muat.
3. **Matrix / Data Card & Chart Card**:
   - Header card menjelaskan cara interaksi (misal: *"Klik nominal untuk melihat transaksi penyusunnya"*), indikator spinner loading inline, dan tombol toggle Chart (`.btn-outline-primary.btn-sm`).
   - Tabel matriks mendukung sticky kolom pertama (`position: sticky; left: 0`), header utama berwarna `#2057a3` (dan `#1f7a8c` untuk pembanding), serta baris `tfoot` dengan `border-top: 2px solid #2057a3`.

---

## 4. Standar Komponen UI Wajib

### 4.1 Modal Baseline (`#modal_detail` & `#bpb-detail-modal`)
Acuan utama modal adalah `#modal_detail` di `app/Views/siteplan/partials/modal_detail.php` (+ `modal_detail_styles.php`) dan `#bpb-detail-modal` di `public/assets/css/bpb.css`:
- **Dialog**: Lebar responsif sesuai kepadatan data (`max-width: min(1200px, calc(100vw - 32px))` atau `modal-xl`).
- **Content**: `border: 1px solid #e5e7eb; border-radius: 10px; overflow: hidden;`
- **Header**: Background `#fff`, `border-bottom: 1px solid #e5e7eb`, padding kompak (`0.9rem 1.2rem`), judul tegas (`1.05rem`, `font-weight: 700`, `#111827`), opsional icon box `.detail-title-icon` (`background: #edf4ff; color: #2057a3; border-radius: .65rem`) dan badge status di sebelah tombol close.
- **Body**: Background abu-abu muda (`#f3f5f7` atau `#f5f7fa`), `overflow-y: auto`, `padding: 1rem`. Semua kelompok informasi di dalamnya dibungkus `.card` putih (`border: 1px solid #e5e7eb; border-radius: 8px; box-shadow: none;`).
- **Data Rows (`.info-row`)**: Baris key-value menggunakan kotak tipis (`background: #f9fafb; border: 1px solid #edf0f2; border-radius: 6px; padding: .45rem .55rem;`) dengan label `.info-label` (`#6b7280`, `0.78rem`, `font-weight: 700`) dan nilai `.info-value` (`#111827`, `font-weight: 600`, `overflow-wrap: anywhere`).
- **Footer**: Background `#fff`, `border-top: 1px solid #e5e7eb`, sticky di bagian bawah pada mobile.

### 4.2 Section Dividers (`divider divider-left`)
Gunakan struktur ini untuk semua sub-judul bagian di dalam form, card, maupun modal:
```html
<div class="divider divider-left">
    <div class="divider-text">Judul Section</div>
</div>
```
Jika mengedit kode lama yang masih menggunakan `<div class="divider">` biasa pada area yang disentuh, migrasikan menjadi `divider divider-left`.

### 4.3 Status Badges & Semantic Pills
Gunakan gaya *soft tinted badge* dengan border tipis atau status dot (seperti `.bpb-status-badge` di `bpb.css` atau `.tm-badge-status` di `modal_tiket_masalah_styles.php`):
- **Draft / Netral**: `background: #f3f4f6; color: #4b5563; border: 1px solid #6b7280;`
- **Menunggu / Proses (Primary)**: `background: #edf3ff; color: #2057a3; border: 1px solid #2057a3;`
- **Menunggu / Perhatian (Warning)**: `background: #fff7ed; color: #9a3412; border: 1px solid #d97706;`
- **Disetujui / Selesai (Success)**: `background: #ecfdf3; color: #166534; border: 1px solid #15803d;`
- **Ditolak / Urgensi Tinggi (Danger)**: `background: #fef2f2; color: #991b1b; border: 1px solid #dc2626;`

### 4.4 Mobile Tabs (Satu Baris Horizontal Scroll)
Untuk `.nav-tabs` atau `.nav-pills` di dalam modal maupun halaman padat, navigasi tab di mobile **wajib tetap 1 baris** dan bisa di-scroll ke samping:
```css
@media (max-width: 767.98px) {
    .your-scope .nav-tabs,
    .your-scope .nav-pills {
        flex-direction: row !important;
        flex-wrap: nowrap;
        overflow-x: auto;
        padding-bottom: .25rem;
    }

    .your-scope .nav-tabs .nav-link,
    .your-scope .nav-pills .nav-link {
        white-space: nowrap;
    }
}
```

### 4.5 File Upload Dropzone & FileAccessService
1. **Komponen Dropzone**: Gunakan pola visual `.bpb-dropzone` (`border: 2px dashed #b8c9df; border-radius: .6rem; background: #f8faff; color: #455b78;`) yang berubah menjadi `border-color: #2057a3; background: #eef4ff;` saat `:hover`, `:focus`, atau `.is-dragging`, disertai grid pratinjau file di bawahnya.
2. **Aturan Wajib `FileAccessService`**:
   - **DILARANG** membuat URL file publik langsung di View atau JS untuk dokumen/upload bisnis, seperti `base_url('uploads/...')`.
   - Backend controller/service wajib menginjeksi `FileAccessService` dan mengembalikan field `access_url`, `download_url`, atau `thumbnail_url` (atau menggunakan `FileAccessService::pathUrl()` untuk path legacy).
   - JS preview wajib memprioritaskan field `*_access_url` untuk file tersimpan, dan hanya memakai `URL.createObjectURL(file)` untuk file lokal yang baru dipilih sebelum diunggah.
   - Asset statis aplikasi (CSS, JS, logo, gambar template) tetap menggunakan `base_url('assets/...')`.

### 4.6 Z-Index, Dark Layout, & Mobile Bottom Nav Awareness
- **SweetAlert2 di Atas Modal**: Jika halaman menggunakan SweetAlert2 di dalam/bersama Bootstrap Modal, pastikan `.swal2-container { z-index: 12000 !important; }`.
- **Mobile Bottom Navigation**: Pada layar `max-width: 767.98px`, SIGAPP menampilkan `#sigapp-mobile-bottom-nav` (`z-index: 1038`). Jangan menaruh tombol floating baru yang menutupi area bawah layar mobile.
- **Dark Mode (`.dark-layout`)**: Jika membuat overlay atau panel mengambang kustom, tambahkan selector `.dark-layout .nama-komponen` dengan background `#283046` dan border `rgba(255, 255, 255, .08)`.

---

## 5. Checklist Implementasi & QA UI

1. **Cek Struktur Asset**: Apakah CSS baru sudah ditaruh di `public/assets/css/<modul>.css` (bukan inline `<style>` ratusan baris di view) dan di-load dengan `?v=<?= filemtime(...) ?>`?
2. **Cek Scoping CSS**: Apakah semua selector di file CSS modul sudah diawali `.nama-modul-page` atau `#id-modal` sehingga tidak merusak halaman lain?
3. **Cek Konsistensi Visual**: Apakah warna primary menggunakan `#2057a3`, divider menggunakan `divider divider-left`, dan modal mengikuti anatomi `#modal_detail` / `#bpb-detail-modal`?
4. **Cek File URL**: Apakah semua pratinjau/unduhan dokumen menggunakan URL dari `FileAccessService`?
5. **Cek Responsivitas Desktop & Mobile**: Apakah tab tetap 1 baris (horizontal scroll), modal nyaman di-scroll pada layar kecil, dan tabel memiliki `.table-responsive` atau mobile card list?
6. **Cek Kualitas Kode**: Jalankan `php -l` pada file view PHP yang disentuh dan `git diff --check` sebelum menyelesaikan tugas.
