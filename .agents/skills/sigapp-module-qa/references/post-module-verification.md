# Panduan Verifikasi Pasca Pengerjaan Modul (Post-Module Verification)

Gunakan panduan ini segera setelah selesai melakukan perubahan, refaktor, atau perbaikan pada suatu modul di SIGAPP.

Dilarang menganggap tugas selesai sebelum kelima tahapan verifikasi di bawah ini lolos 100%.

---

## Tahap 1: Verifikasi Status & Skor Kesiapan Modul

Jalankan perintah Spark:
```bash
php spark module:status
```

**Kriteria Lolos:**
- Modul target memiliki status `REFACTORED`.
- Skor kesiapan (*Readiness Score*) modul mencapai minimal **80%** (ideal: 100%).
- Struktur memenuhi: Controller Web/Api terpisah, Service tersedia, Repository tersedia, Rute terdaftar, dan Dokumentasi ada.

---

## Tahap 2: Verifikasi Integritas Rute & Zero-Downtime Shims

Periksa apakah seluruh rute modul (baik rute baru `/api/...` maupun rute lama) tetap aktif:

1. Jalankan pengecekan rute melalui Spark:
   ```bash
   php spark routes
   ```
2. Pastikan rute lama tidak menghasilkan error atau Controller Not Found.
3. Pastikan controller baru di `app/Controllers/Web/<Modul>/` dan `app/Controllers/Api/<Modul>/` terpanggil dengan benar.

---

## Tahap 3: Audit Callers & Dead Code dengan CodeGraph

Pastikan tidak ada fungsi yang patah atau metode dead code yang tertinggal:

1. Jalankan deteksi dead code bawaan:
   ```bash
   php spark module:deadcode <NamaModul>
   ```
2. Untuk setiap metode yang baru diubah atau dipindahkan, gunakan MCP `codegraph_explore` atau grep untuk memastikan seluruh pemanggil (*callers*) di file JavaScript, View, maupun Service lain sudah dialihkan ke endpoint/service baru.

---

## Tahap 4: Verifikasi Batas Wilayah & Database Safety

Buka file [MODULE.md](file:///c:/Users/Salivs/Data/laragon/www/sigapp.dev/docs/modules/Proyek/MODULE.md) milik modul target dan periksa:
- **Owner Tables**: Pastikan query INSERT / UPDATE / DELETE pada tabel pemilik hanya dilakukan oleh modul ini.
- **Shared Tables**: Jika modul membaca data dari modul lain, pastikan tidak melakukan update langsung (`$db->table('tabel_lain')->update()`). Harus memanggil public Service method dari modul pemilik.
- **Transaksi DB**: Periksa apakah method yang memodifikasi lebih dari satu baris/tabel memiliki `$db->transStart()` dan `$db->transComplete()`.

---

## Tahap 5: Pengujian Antarmuka E2E dengan Playwright (WAJIB)

Gunakan tool browser Playwright (`browser_navigate`, `browser_click`, `browser_fill_form`, dll.) untuk menguji modul secara langsung di environment lokal:

- **Target URL**: `http://localhost/sigapp.dev`
- **Kredensial Login Berdasarkan Hak Akses Modul:**
  - **Keuangan** (Modul Keuangan, PencairanJaminan): User `nisalis`, Password `nisalis123`
  - **MKDT** (Modul MKDT, Transaksi): User `citra`, Password `citra123`
  - **Produksi** (Modul Produksi, BPB, TiketMasalah): User `alika`, Password `alikas123`
  - **Legal** (Modul Legal): User `marlina`, Password `marlina123`
  - **Promosi** (Modul MGM, Promosi): User `benny`, Password `benny123`

**Langkah Pengujian UI:**
1. Lakukan login dengan akun yang sesuai.
2. Buka menu atau navigasikan ke halaman modul target.
3. Periksa apakah DataTables atau tabel data ter-load sempurna tanpa error.
4. Buka konsol browser (`browser_console_messages`): **Wajib 0 error 500 / 404**.
5. Cek interaksi penting (contoh: buka modal detail, filter data, atau tombol aksi).

---

## Format Sertifikat Verifikasi Akhir

Setelah seluruh tahap lolos, tampilkan sertifikat verifikasi dalam ringkasan respon:

```markdown
### 🛡️ Sertifikat Verifikasi Modul: [Nama Modul]
- **Tanggal/Waktu:** [YYYY-MM-DD HH:mm]
- **Skor Kesiapan:** [X]% (Status: REFACTORED)
- **Zero-Downtime Shims:** OK (Rute lama & baru aktif)
- **Dead Code & Callers:** Bersih (Diverifikasi dengan codegraph)
- **Database Atomicity:** Transaksi aman & Backend finansial valid
- **Playwright E2E UI Test:** LULUS (Role: [Nama User], 0 Console Errors)
```
