# Requirements Document

## Introduction

Fitur ini mencakup redesain halaman `list-kavling.php` yang menampilkan data posisi konsumen aktif dalam format tabel DataTables. Perubahan meliputi: (1) tampilan tabel yang lebih compact/dense agar lebih banyak data terlihat per layar, (2) mempertahankan freeze column yang sudah ada, (3) memindahkan tombol aksi (Edit dan Lihat Data) ke kolom paling kiri tabel, dan (4) mengintegrasikan modal/form dari `/app/Views/siteplan` agar setiap departemen dapat mengubah data kavling langsung dari halaman list ini.

## Glossary

- **List_Kavling_Page**: Halaman `list-kavling.php` yang menampilkan tabel posisi konsumen aktif.
- **DataTable**: Komponen tabel interaktif berbasis jQuery DataTables yang digunakan pada halaman ini.
- **Action_Column**: Kolom pada tabel yang berisi tombol Edit dan Lihat Data untuk setiap baris data.
- **Freeze_Column**: Fitur `fixedColumns` pada DataTables yang mengunci sejumlah kolom di sisi kiri agar tetap terlihat saat tabel di-scroll horizontal.
- **Compact_Mode**: Tampilan tabel dengan padding sel yang dikurangi, font size lebih kecil, dan row height yang lebih rapat sehingga lebih banyak baris terlihat dalam satu layar.
- **Departemen**: Unit kerja yang memiliki akses ke data kavling, meliputi: Marketing Data (role 4), Legal (role 5), Planning (role 6), Produksi (role 7), Sales (role 8), Direksi (role 9), Keuangan (role 3), Pajak (role 10), dan Admin (role 1).
- **Siteplan_Modal**: Modal dan form yang berada di `/app/Views/siteplan/` (planning.php, produksi.php, sales.php, legal.php, mkdt.php, direksi.php, keuangan.php, pajak.php) yang digunakan untuk mengubah data kavling per departemen.
- **Role**: Identitas peran pengguna yang menentukan departemen dan hak akses terhadap modal tertentu.

## Requirements

### Requirement 1: Compact Table Display

**User Story:** Sebagai pengguna, saya ingin tabel menampilkan lebih banyak data per layar, sehingga saya tidak perlu terlalu banyak scroll vertikal untuk melihat data.

#### Acceptance Criteria

1. THE List_Kavling_Page SHALL menerapkan CSS compact mode pada DataTable dengan padding sel maksimal 4px vertikal dan 6px horizontal.
2. THE List_Kavling_Page SHALL menggunakan font size 10px atau lebih kecil pada seluruh sel tabel.
3. THE DataTable SHALL menggunakan class `compact` pada elemen `<table>` untuk mengaktifkan tampilan dense bawaan DataTables.
4. WHEN tabel dirender, THE DataTable SHALL menampilkan minimal 20 baris data dalam viewport tanpa scroll vertikal pada resolusi layar 1366x768.
5. THE List_Kavling_Page SHALL mempertahankan keterbacaan data dengan `vertical-align: middle` dan `text-align: center` pada semua sel.

---

### Requirement 2: Freeze Column Dipertahankan

**User Story:** Sebagai pengguna, saya ingin kolom-kolom kiri tetap terlihat saat saya scroll horizontal, sehingga saya selalu tahu data kavling mana yang sedang saya lihat.

#### Acceptance Criteria

1. THE DataTable SHALL mempertahankan konfigurasi `fixedColumns` dengan minimal 5 kolom dari kiri yang di-freeze.
2. WHEN pengguna melakukan scroll horizontal pada tabel, THE DataTable SHALL memastikan kolom-kolom yang di-freeze tetap terlihat dan tidak ikut bergeser.
3. THE List_Kavling_Page SHALL memuat library `dataTables.fixedColumns.js` dan CSS `fixedColumns.bootstrap4.css` yang sudah ada.
4. WHEN Action_Column dipindahkan ke posisi paling kiri, THE DataTable SHALL menyesuaikan jumlah `leftColumns` agar Action_Column ikut ter-freeze bersama kolom identitas kavling.

---

### Requirement 3: Action Column di Posisi Paling Kiri

**User Story:** Sebagai pengguna, saya ingin tombol Edit dan Lihat Data berada di kolom paling kiri tabel, sehingga saya dapat langsung mengakses aksi tanpa perlu scroll ke kanan terlebih dahulu.

#### Acceptance Criteria

1. THE DataTable SHALL menempatkan Action_Column sebagai kolom pertama (index 0) pada tabel, sebelum kolom NO.
2. THE Action_Column SHALL menampilkan tombol "Edit" dan tombol "Lihat Data" untuk setiap baris data.
3. WHEN Action_Column berada di posisi paling kiri, THE DataTable SHALL menyertakan Action_Column dalam jumlah kolom yang di-freeze (`leftColumns`).
4. THE Action_Column SHALL memiliki lebar yang cukup untuk menampilkan kedua tombol tanpa terpotong, dengan lebar minimal 80px.
5. WHEN pengguna mengklik tombol "Edit" pada suatu baris, THE List_Kavling_Page SHALL membuka Siteplan_Modal yang sesuai dengan Role pengguna yang sedang login.
6. WHEN pengguna mengklik tombol "Lihat Data" pada suatu baris, THE List_Kavling_Page SHALL menampilkan modal detail kavling (`modal_detail`) dengan data kavling yang dipilih.

---

### Requirement 4: Integrasi Modal Departemen dari Siteplan

**User Story:** Sebagai pengguna dari departemen tertentu, saya ingin dapat mengubah data kavling langsung dari halaman list, sehingga saya tidak perlu berpindah ke halaman siteplan untuk melakukan perubahan.

#### Acceptance Criteria

1. THE List_Kavling_Page SHALL memuat Siteplan_Modal yang sesuai berdasarkan Role pengguna yang sedang login, mengikuti pola kondisional yang sama dengan `siteplan/master.php`.
2. WHERE Role pengguna adalah Planning (role 6) atau Admin (role 1), THE List_Kavling_Page SHALL memuat view `siteplan/planning`.
3. WHERE Role pengguna adalah Produksi (role 7) atau Admin (role 1), THE List_Kavling_Page SHALL memuat view `siteplan/produksi`.
4. WHERE Role pengguna adalah Sales (role 8) atau Admin (role 1), THE List_Kavling_Page SHALL memuat view `siteplan/sales`.
5. WHERE Role pengguna adalah Legal (role 5) atau Admin (role 1), THE List_Kavling_Page SHALL memuat view `siteplan/legal`.
6. WHERE Role pengguna adalah Marketing Data (role 4) atau Admin (role 1), THE List_Kavling_Page SHALL memuat view `siteplan/mkdt`.
7. WHERE Role pengguna adalah Direksi (role 9) atau Admin (role 1), THE List_Kavling_Page SHALL memuat view `siteplan/direksi`.
8. WHERE Role pengguna adalah Keuangan (role 3) atau Admin (role 1), THE List_Kavling_Page SHALL memuat view `siteplan/keuangan`.
9. WHERE Role pengguna adalah Pajak (role 10) atau Admin (role 1), THE List_Kavling_Page SHALL memuat view `siteplan/pajak`.
10. WHEN tombol "Edit" diklik pada suatu baris, THE List_Kavling_Page SHALL mengirimkan data `id_kavling` dari baris tersebut ke Siteplan_Modal yang aktif untuk mengisi form dengan data yang ada.
11. WHEN Siteplan_Modal berhasil menyimpan perubahan, THE DataTable SHALL melakukan refresh (`table.draw()`) untuk menampilkan data terbaru.
12. IF Role pengguna tidak memiliki Siteplan_Modal yang sesuai, THEN THE List_Kavling_Page SHALL menyembunyikan tombol "Edit" dan hanya menampilkan tombol "Lihat Data".

---

### Requirement 5: Kompatibilitas Script dan Dependensi

**User Story:** Sebagai developer, saya ingin semua script dan CSS yang dibutuhkan dimuat dengan benar, sehingga fitur berjalan tanpa error.

#### Acceptance Criteria

1. THE List_Kavling_Page SHALL memuat semua script JavaScript yang dibutuhkan oleh Siteplan_Modal yang diintegrasikan, termasuk flatpickr, select2, sweetalert2, dan jquery.validate.
2. THE List_Kavling_Page SHALL memuat variabel global yang dibutuhkan Siteplan_Modal, termasuk `rolename`, `roleid`, `has_akses`, `pph`, `ppn`, `conf`, dan `li_keu`.
3. IF variabel global yang dibutuhkan Siteplan_Modal tidak tersedia, THEN THE List_Kavling_Page SHALL menginisialisasi variabel tersebut dengan nilai default yang aman sebelum modal dimuat.
4. THE List_Kavling_Page SHALL mempertahankan semua fungsionalitas yang sudah ada, termasuk filter proyek/cluster/blok, export Excel/PDF, dan tab riwayat eksport.
