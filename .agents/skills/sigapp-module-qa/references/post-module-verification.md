# Panduan Verifikasi Pasca Pengerjaan Tugas (Post-Execution Verification)

Gunakan panduan ini segera setelah selesai melakukan perubahan kode (fitur baru, perbaikan bug, penyesuaian UI/laporan, atau refaktor modul) di SIGAPP.

Dilarang menganggap tugas selesai sebelum seluruh pengujian yang relevan lolos 100%.

---

## 1. Verifikasi Inti (Wajib Mutlak untuk Semua Mode Tugas)

### A. Audit Callers & Integritas Simbol dengan CodeGraph
Pastikan tidak ada fungsi yang patah, parameter yang tidak cocok, atau pemanggil JavaScript/View yang tertinggal:
1. Panggil MCP `codegraph_explore` untuk simbol/metode yang diubah:
   ```json
   {
     "ServerName": "codegraph",
     "ToolName": "codegraph_explore",
     "Arguments": {
       "projectPath": "c:\\Users\\Salivs\\Data\\laragon\\www\\sigapp.dev",
       "query": "NamaSimbolAtauMethod"
     }
   }
   ```
2. Pastikan seluruh pemanggil (*callers*) di file JavaScript, View, maupun Service lain sudah sinkron dan tidak ada error.

### B. Pengujian Antarmuka E2E dengan Playwright
Gunakan tool browser Playwright (`browser_navigate`, `browser_click`, `browser_fill_form`, dll.) untuk menguji modul secara langsung di environment lokal:

- **ATURAN WAJIB**: Muat dan ikuti panduan `$sigapp-local-testing` sebelum membuka browser.
- **Target URL**: `http://localhost/sigapp.dev`
- **Kredensial Login Berdasarkan Hak Akses Modul:**
  - **Keuangan** (Keuangan, Pajak, CashOut, PencairanJaminan): User `nisalis`, Password `nisalis123`
  - **MKDT** (MKDT, Konsumen, Transaksi): User `citra`, Password `citra123`
  - **Produksi** (Produksi, BPB, SPK, Gudang, TiketMasalah): User `alika`, Password `alikas123`
  - **Legal** (Legal, HGB, PBB, PBG, AJB): User `marlina`, Password `marlina123`
  - **Promosi** (MGM, Promosi, Event): User `benny`, Password `benny123`

**Langkah Pengujian UI:**
1. Lakukan login dengan akun yang sesuai.
2. Buka menu atau navigasikan ke halaman modul target.
3. Periksa apakah DataTables atau tabel data ter-load sempurna tanpa error.
4. Buka konsol browser (`browser_console_messages`): **Wajib 0 error 500 / 404**.
5. Uji skenario interaksi utama (contoh: buka form, submit data, modal detail, filter data, atau tombol aksi).

---

## 2. Verifikasi Kondisional (Berdasarkan Elemen yang Disentuh)

### A. Integritas Rute (`php spark routes`)
*Jalankan jika ada penambahan rute baru atau modifikasi endpoint controller.*
```bash
php spark routes
```
- Pastikan rute baru terdaftar dan controller mengarah ke namespace yang benar (`App\Controllers\Web` atau `App\Controllers\Api`).
- Jika tugas adalah refaktor, pastikan rute lama tidak menghasilkan Controller Not Found.

### B. Audit Dead Code (`php spark module:deadcode <Modul>`)
*Jalankan jika ada metode/simbol yang dipindahkan atau dihapus.*
```bash
php spark module:deadcode <NamaModul>
```
- Pastikan tidak ada fungsi yang tidak terpakai atau pemanggil yang terlupakan.

### C. Skor Kesiapan Modul (`php spark module:status`)
*Khusus dijalankan pada tugas Refaktor Modul Penuh (`REFACTOR`).*
```bash
php spark module:status
```
- **Kriteria Lolos Refaktor:** Status `REFACTORED`, skor kesiapan (*Readiness Score*) $\ge 80\%$.

### D. Verifikasi Batas Wilayah & Database Safety
*Jalankan jika perubahan menyentuh interaksi database.*
- **Owner Tables**: Pastikan query INSERT / UPDATE / DELETE pada tabel pemilik hanya dilakukan oleh modul ini.
- **Transaksi DB**: Periksa apakah method yang memodifikasi lebih dari satu tabel memiliki `$db->transStart()` dan `$db->transComplete()`.
- **Backend Financial**: Pastikan nominal uang dihitung ulang dari database menggunakan helper `cleanNumeric()`.

---

## Format Sertifikat Verifikasi Akhir

Setelah seluruh tahap lolos, tampilkan sertifikat verifikasi dalam ringkasan respon:

```markdown
### 🛡️ Sertifikat Verifikasi Tugas: [Judul Tugas]
- **Modul:** [Nama Modul]
- **Mode Tugas:** [FEATURE / BUGFIX / REFACTOR / UI_REPORT]
- **Tanggal/Waktu:** [YYYY-MM-DD HH:mm]
- **Audit Callers (Codegraph):** Bersih (0 broken callers)
- **Playwright E2E UI Test:** LULUS (Role: [Nama User], 0 Console Errors)
- **Integritas Rute / Shims:** [OK / N/A]
- **Database Safety & Transaksi:** [Aman / N/A]
- **Skor Kesiapan Modular:** [X% REFACTORED / N/A]
```
