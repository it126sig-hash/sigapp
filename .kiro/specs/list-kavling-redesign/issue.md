# Redesain Halaman List Kavling & Global DataTables

## Deskripsi Issue
Tugas ini bertujuan untuk mengubah tampilan `list-kavling.php` menjadi lebih compact dan fungsional. Selain itu, penyesuaian gaya tampilan pada tabel (DataTables) ini harus dibuat menjadi **global** agar otomatis teraplikasi pada seluruh DataTables dalam aplikasi SIGAPP.

### Detail Pekerjaan

#### 1. Penerapan Gaya Global untuk DataTables (Compact Mode)
**Tujuan:** Membuat tabel menampilkan lebih banyak data per layar tanpa perlu banyak scroll vertikal.
- Ubah/Tambahkan custom CSS global (misal pada `app-assets` atau `assets/css/` yang diload di semua halaman) agar setiap tabel DataTables (khususnya yang memiliki class `compact` atau secara umum) memiliki:
  - Font size maksimal `10px`.
  - Padding cell (td, th) maksimal `4px` vertikal dan `6px` horizontal.
  - Teks rata tengah (`text-align: center`) dan vertikal di tengah (`vertical-align: middle`).
- Pastikan gaya ini tertimpa dengan benar (override) dari style bawaan plugin DataTables.

#### 2. Reposisi Kolom Aksi di `list-kavling.php`
**Tujuan:** Memudahkan pengguna mengakses aksi baris tanpa harus scroll jauh ke sisi kanan tabel.
- Pindahkan definisi "Action Column" (Tombol Edit & Lihat Data) menjadi kolom pertama (Index `0`).
- Sesuaikan inisialisasi `fixedColumns` pada JavaScript DataTables di `list-kavling.php` untuk memastikan kolom aksi ikut membeku (ter-freeze) di sisi kiri layar bersama dengan kolom-kolom esensial lainnya (minimal 5 kolom).

#### 3. Integrasi Fungsi Edit & Modal Siteplan
**Tujuan:** Memungkinkan setiap departemen mengedit baris kavling secara langsung di halaman list kavling.
- Render conditional view modal dari directory `app/Views/siteplan/` sesuai dengan role pengguna yang login (seperti pola di `siteplan/master.php`).
- Modifikasi tombol Edit agar ketika diklik dapat memicu pembukaan Modal spesifik departemen dengan mempassing `id_kavling`.
- Inisialisasi/Siapkan variabel global js yang dibutuhkan oleh modal siteplan (seperti `rolename`, `roleid`, `has_akses`, dll) apabila belum tersedia di halaman `list-kavling.php`.
- Setelah edit berhasil dan modal tersimpan, trigger `table.draw()` / reload pada DataTable untuk melihat perubahannya seketika.

## Referensi Requirements
Untuk detail lebih lengkap tentang batasan role dan modal departemen yang dimuat, harap baca dokumen acuan:
`c:\Users\Salivs\Data\laragon\www\sigapp.dev\.kiro\specs\list-kavling-redesign\requirements.md`
