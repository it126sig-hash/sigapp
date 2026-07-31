# Rencana Pindah Judul Halaman dan Breadcrumb ke Navbar

## Tujuan

Memindahkan judul halaman dan breadcrumb dari area konten/card ke navbar utama, supaya struktur halaman lebih konsisten dan header halaman tidak berulang di setiap view.

## Kondisi Saat Ini

- Navbar dirender dari `app/Views/template/menu.php` melalui `view_cell('\App\Libraries\Menu::get_menu')`.
- Konten halaman dirender setelah menu di `app/Views/template.php`.
- Banyak judul halaman masih berada di masing-masing view, misalnya `app/Views/kavling/list-stock.php`.
- Breadcrumb hardcoded ditemukan di beberapa view lama:
  - `app/Views/siteplan/master.php`
  - `app/Views/siteplan/pilih_proyek.php`
  - `app/Views/siteplan/test.php`
  - `app/Views/proyek/proyek_.php`
- Siteplan punya breadcrumb aktif dinamis lewat `#br_siteplan`, jadi ID/target update JS perlu ikut dipindah ke navbar.

## Rencana Implementasi

1. Update `app/Views/template.php`.
   - Teruskan metadata halaman ke template menu/navbar.
   - Gunakan data yang sudah ada dari controller, terutama `$data['title']`.
   - Siapkan fallback bila title atau breadcrumb tidak tersedia.

2. Update `app/Views/template/menu.php` dan `app/Views/template/generate_menu.php`.
   - Tambahkan slot judul halaman di dalam navbar.
   - Tambahkan slot breadcrumb di bawah atau samping judul, sesuai ruang navbar.
   - Pertahankan elemen navbar yang sudah ada: tombol menu mobile, logo mobile, notifikasi, dan user menu.

3. Standarkan format metadata halaman.
   - Gunakan pola:

   ```php
   $data['data']['title'] = 'Nama Halaman';
   $data['data']['breadcrumbs'] = [
       ['label' => 'Parent', 'url' => base_url('parent')],
       ['label' => 'Nama Halaman'],
   ];
   ```

   - Untuk halaman yang belum punya `breadcrumbs`, navbar cukup menampilkan title.

4. Migrasi breadcrumb hardcoded.
   - Hapus atau nonaktifkan markup `.content-header`, `.breadcrumbs-top`, dan `.breadcrumb-wrapper` dari view yang sudah dipindahkan.
   - Mulai dari view yang paling jelas:
     - `app/Views/siteplan/master.php`
     - `app/Views/siteplan/pilih_proyek.php`
     - `app/Views/proyek/proyek_.php`

5. Tangani breadcrumb dinamis Siteplan.
   - Pindahkan target `#br_siteplan` ke breadcrumb aktif di navbar, atau buat ID baru seperti `#navbar-page-breadcrumb-current`.
   - Update JS di `app/Views/siteplan/master.php` yang sekarang menjalankan:

   ```js
   $("#br_siteplan").html(dt_proyek.nama_proyek)
   ```

6. Bersihkan judul duplikat di view.
   - Untuk halaman yang header card hanya berisi judul, hapus judul tersebut dari card.
   - Untuk halaman yang header card juga berisi filter, tombol, atau action, pindahkan hanya judul/subtitle; kontrol halaman tetap berada di card.
   - Contoh halaman yang perlu hati-hati: `app/Views/cashout_subkon/index.php`.

7. Tambahkan CSS responsive.
   - Simpan style global di `public/assets/css/style.css`.
   - Desktop:
     - Judul tampil jelas.
     - Breadcrumb tampil ringkas.
   - Mobile:
     - Judul satu baris dengan ellipsis.
     - Breadcrumb disembunyikan atau dibuat horizontal scroll kecil agar navbar tidak terlalu tinggi.

8. Verifikasi.
   - Cek halaman:
     - Dashboard
     - Siteplan master
     - Pilih proyek
     - List stock
     - Cashout subkon
   - Jalankan syntax check untuk file PHP yang diedit.
   - Jalankan `git diff --check`.

## Catatan Risiko

- Worktree sedang memiliki banyak perubahan lain, jadi implementasi harus dibuat sempit dan tidak merapikan file yang tidak terkait.
- Karena navbar dirender sebelum konten, metadata halaman harus tersedia sebelum `template/menu` dipanggil.
- Jangan memindahkan filter/action halaman ke navbar pada tahap awal; cukup judul dan breadcrumb agar perubahan tetap aman.
