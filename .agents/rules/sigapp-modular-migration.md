# Aturan & Standar Migrasi Modular SIGAPP

Dokumen ini adalah aturan WAJIB bagi agen/developer saat melakukan refaktor modul di SIGAPP secara bertahap (satu per satu).

---

## 1. Prinsip Utama: Zero-Downtime & Anti-Regresi

1. **Aplikasi Harus Selalu Bisa Diakses:**
   Proses migrasi tidak boleh memutus operasional harian. Setiap perubahan kode harus backward-compatible.
2. **Jangan Hapus Endpoint Lama Mendadak:**
   Ketika controller dipecah menjadi `Web/` dan `Api/`, endpoint/route lama **harus tetap dipertahankan** (sebagai alias delegator ke controller baru) sampai seluruh skrip JavaScript/View yang memanggilnya dialihkan dan diverifikasi.
3. **AutoRoute CI4 Tetap Aktif:**
   Jangan mematikan `$routes->setAutoRoute(true)` secara global selama fase migrasi.
4. **Skema Database Non-Breaking:**
   Dilarang menghapus atau mengubah nama kolom/tabel aktif tanpa proses migrasi bertahap.

---

## 2. Batas Wilayah Modul (Module Boundary)

Setiap modul bisnis memiliki wilayah kepemilikan yang didefinisikan dalam `docs/modules/<NamaModul>/MODULE.md`.

### A. Kepemilikan Tabel (Owner Tables)
* Hanya modul pemilik yang memiliki izin melakukan operasi **INSERT, UPDATE, DELETE** pada tabel miliknya.
* Modul lain dilarang keras melakukan manipulasi langsung ke tabel modul lain (`$db->table('tabel_lain')->update()`).

### B. Komunikasi Antar-Modul (Contracts)
1. **Frontend (Browser / JS / AJAX / Mobile):**
   * Gunakan **Public REST API Endpoint** (`/api/...`).
   * Mengembalikan respons JSON standar `{ status, message, data }`.
2. **Backend Antar-Modul:**
   * Gunakan **Direct PHP Public Service Methods**.
   * Dilarang melakukan loopback HTTP call (`curl` ke localhost). Panggil method Service resmi dari modul pemilik.
   * Tetap jaga transaksi atomik database (`$db->transStart()` / `$db->transComplete()`).

### C. Standar Struktur Folder Modul (Namespace & Path)
Setiap modul diorganisir ke dalam subfolder modul tersendiri:
* **Web Controllers**: `app/Controllers/Web/<Modul>/` (Namespace: `App\Controllers\Web\<Modul>`)
* **API Controllers**: `app/Controllers/Api/<Modul>/` (Namespace: `App\Controllers\Api\<Modul>`)
* **Services**: `app/Services/<Modul>/` (Namespace: `App\Services\<Modul>`)
* **Repositories**: `app/Repositories/<Modul>/` (Namespace: `App\Repositories\<Modul>`)
* **Backward-Compatibility Shims**: Untuk service atau repository yang dipanggil oleh modul lain atau legacy AutoRoute, sediakan shim class tipis di `app/Services/` atau `app/Repositories/` yang meng-`extends` class modular baru.

---

## 3. SOP Alur Migrasi Satu Modul (Step-by-Step)

Ketika diperintahkan untuk merefaktor suatu modul (misal: `"refaktor modul X"`), ikuti tahapan berikut secara ketat:

### Langkah 1: Preflight & Analisis Batas
1. Baca dokumen `docs/modules/<Modul>/MODULE.md`.
2. Periksa daftar file inventory, routes (eksplisit & AutoRoute), dan tabel pemilik.
3. Jalankan `php spark module:deadcode <Modul>` untuk melihat kandidat dead code.

### Langkah 2: Audit & Pemisahan Controller
1. Jika controller masih campuran View + JSON:
   * Buat `App\Controllers\Web\<Modul>\<Modul>Controller` untuk render view.
   * Buat `App\Controllers\Api\<Modul>\<Modul>Controller` untuk endpoint JSON.
2. Pertahankan route lama di `app/Config/Routes.php` yang mendelegasikan ke controller baru (Zero-Downtime alias).

### Langkah 3: Ekstraksi Logika Bisnis ke Service
1. Pindahkan kalkulasi bisnis, validasi finansial, dan manipulasi data dari Controller ke `App\Services\<Modul>\<Modul>Service`.
2. Jika ada query multi-tabel / JOIN kompleks, tempatkan di `App\Repositories\<Modul>\<Modul>Repository`.

### Langkah 4: Pembersihan Dead Code Terkonfirmasi
1. Hapus method yang terbukti tidak memiliki pemanggil (0 callers) dan bukan route aktif.
2. Bersihkan variabel/helper yang tidak lagi digunakan.

### Langkah 5: Pengujian E2E Playwright (Wajib)
1. Jalankan pengujian antarmuka dengan Playwright menggunakan akun departemen terkait:
   * Keuangan: `nisalis` / `nisalis123`
   * MKDT: `citra` / `citra123`
   * Produksi: `alika` / `alikas123`
   * Legal: `marlina` / `marlina123`
   * Promosi: `benny` / `benny123`
2. Pastikan tidak ada console error (500/404) dan alur bisnis utama berjalan mulus.

### Langkah 6: Sinkronisasi Dokumentasi & Commit
1. Jalankan `php spark module:sync <Modul>` untuk memperbarui `docs/modules/<Modul>/MODULE.md` secara otomatis.
2. Perbarui baris modul tersebut di `docs/MIGRATION_PROGRESS.md` menjadi `COMPLETED` beserta commit hash.
3. Periksa `git status` dan commit perubahan modul tersebut secara terisolasi.

---

## 4. Protokol Fresh Chat (Sesi Percakapan Baru per Modul)

Untuk menjaga context window tetap bersih dan hemat token:

1. **Aturan Pembacaan File:**
   Agen di fresh chat **HANYA** perlu membaca:
   * File ini: `.agents/rules/sigapp-modular-migration.md`
   * Global ledger: `docs/MIGRATION_PROGRESS.md`
   * Dokumen target: `docs/modules/<TargetModul>/MODULE.md`
   * File-file di dalam `File Inventory` milik modul yang sedang dikerjakan.
   * **DILARANG** membaca file controller/service modul lain kecuali file Contract publik yang dipanggil.
2. **Template Prompt Memulai Sesi Fresh Chat:**
   User cukup memberikan instruksi:
   > *"Kerjakan refaktor Modul <NamaModul> sesuai docs/modules/<NamaModul>/MODULE.md dan SOP di .agents/rules/sigapp-modular-migration.md."*
3. **Hasil Akhir Sesi:**
   Setiap akhir sesi fresh chat, pastikan `MODULE.md` dan `MIGRATION_PROGRESS.md` telah disinkronkan dan di-commit ke Git.
