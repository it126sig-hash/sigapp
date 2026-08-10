# Implementasi Tag Masalah & Peningkatan UI Siteplan

Berikut adalah rencana implementasi untuk fitur "Tag Masalah" berdasarkan permintaan:

## Ringkasan Perubahan
1. **Perubahan Tombol "Tag Masalah"**: Jika tidak ada kavling/item yang dipilih, tombol Tag Masalah akan berubah menjadi opsi menggambar bentuk (Rectangle, Circle, Triangle, Titik Manual).
2. **Kondisi Tampil Item "Others" Masalah**: Item manual yang digambar khusus untuk Tag Masalah (tipe: `masalah`) tidak akan di-load secara default di Siteplan. Item ini hanya akan muncul jika filter "Masalah" sedang aktif.
3. **Pilihan User (Nama & Departemen)**: Pilihan pada "User yang dilibatkan" akan dimodifikasi agar menampilkan "Nama - Departemen" (misal: "Budi - Keuangan").

---

## Proposed Changes

### 1. Tag Masalah Drawing Tools
Kita akan menambahkan state dan panel UI baru pada dock floating (mirip dengan saat menambah jalan/produksi manual).

#### [MODIFY] `public/assets/js/siteplan/tiket-masalah.js`
- Modifikasi fungsi `openTiketMasalahAction()`:
  - Jika `editdtt.length > 0`, langsung buka modal `modal_tiket_masalah`.
  - Jika `editdtt.length === 0`, munculkan menu "Drawing Mode" dengan tombol: `[Kotak]`, `[Lingkaran]`, `[Segitiga]`, `[Manual Polygon]`, dan `[Batal]`.
- Tambahkan logika `Konva.js` untuk menggambar bentuk-bentuk tersebut secara dinamis di atas canvas.
- Setelah bentuk selesai digambar, sistem akan mengirim request AJAX (Background) ke `Siteplan::add_others()` dengan tipe `masalah` untuk menyimpan bentuk tersebut, lalu modal tiket masalah akan otomatis terbuka untuk item baru tersebut.

#### [MODIFY] `app/Views/siteplan/master.php`
- Tambahkan container HTML tersembunyi (`d-none`) di dalam `#filter-side` atau sebagai floating dock baru untuk memuat tombol-tombol Drawing Mode (Rect, Circle, Triangle, Manual).

---

### 2. Visibilitas Item "Masalah" (Hidden by Default)

#### [MODIFY] `app/Controllers/Siteplan.php`
- Pada method `get_others()`, ubah query builder agar **mengecualikan** (`exclude`) item dengan tipe `masalah` secara default.
- Item tipe `masalah` hanya akan di-fetch apabila request dari frontend secara eksplisit meminta data masalah (`$this->request->getPost('show_masalah') == 1`).

#### [MODIFY] `public/assets/js/siteplan/master.js`
- Modifikasi pemanggilan `siteplanOthersRequest` di dalam `load_kavling()`.
- Tambahkan logika pengecekan apakah filter "Masalah" sedang aktif (melalui dropdown filter/kategori). Jika aktif, tambahkan parameter `show_masalah: 1` pada request AJAX.

---

### 3. Modifikasi Select User & Departemen

#### [MODIFY] `app/Services/TiketMasalahService.php`
- Pada method `getUserList()`, tambahkan operasi `JOIN` ke tabel `auth_groups_users` dan `auth_groups` untuk mendapatkan nama departemen/role dari setiap user.
- Kembalikan response yang mengandung `department`.

#### [MODIFY] `public/assets/js/siteplan/tiket-masalah.js`
- Pada fungsi `initAssignedUsersSelect2()`, ubah format option dropdown dari `${u.name} (${u.username})` menjadi `${u.name} - ${u.department}`.

---

> [!WARNING]
> ## Open Questions (Mohon Konfirmasi)
> 1. **Data Cluster & Jalan untuk Item Masalah**: Saat menyimpan item `others` ke database, sistem membutuhkan field `id_jalan` dan `id_cluster`. Karena pengguna bisa menggambar masalah di sembarang tempat, apakah aman jika kita set *Cluster* dan *Jalan* secara otomatis ke salah satu yang terdekat/default? Ataukah user akan diminta memilihnya di dalam form Tiket Masalah?
> 2. **Warna Default Bentuk (Shape)**: Saat bentuk digambar (sebelum disave atau saat di-load di filter Masalah), apakah warnanya mengikuti warna prioritas masalah (Merah/Kuning/dsb) sesuai standar filter saat ini?
> 3. **Manual Titik**: Apakah yang dimaksud "pilih titik manual" adalah menggambar bentuk Polygon bebas dengan titik demi titik (seperti fitur tambah kavling manual) atau hanya sekadar menaruh 1 buah penanda (marker point)?
