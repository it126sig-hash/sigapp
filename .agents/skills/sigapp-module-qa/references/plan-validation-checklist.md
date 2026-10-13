# Checklist Validasi Plan Agen (Plan Best-Practice Gatekeeper)

Gunakan checklist ini untuk memvalidasi rencana (implementation plan) yang dibuat oleh agen AI SEBELUM mengeksekusi perubahan kode apapun pada modul SIGAPP.

Setiap rencana harus memenuhi checklist ini. Jika ada poin yang tidak terpenuhi, perbaiki rencana terlebih dahulu sebelum menulis kode.

---

## 1. Kepatuhan Prinsip Karpathy (Simplicity & Surgical)

| Kriteria | Status | Panduan Evaluasi |
|---|:---:|---|
| **No Speculative Code** | [ ] | Apakah ada fitur/fungsi yang tidak diminta oleh user? Hapus jika ada. |
| **Minimum Necessary Diff** | [ ] | Apakah perbaikan hanya menyentuh file yang dibutuhkan? Dilarang merefaktor file yang tidak rusak. |
| **Clear Success Criteria** | [ ] | Apakah setiap langkah memiliki kriteria verifikasi eksplisit (`[Step] → verify: [check]`)? |
| **No Orphaned Variables/Imports** | [ ] | Apakah rencana memastikan pembersihan variabel/import yang menjadi usang akibat perubahan? |

---

## 2. Standar Arsitektur CodeIgniter 4 (SIGAPP Style)

| Kriteria | Status | Panduan Evaluasi |
|---|:---:|---|
| **Thin Web Controller** | [ ] | Web Controller (`App\Controllers\Web\<Modul>`) hanya memanggil Service untuk mengambil data view, lalu me-render template atau redirect. Tidak ada logika `if/else` bisnis atau kalkulasi angka. |
| **Thin API Controller** | [ ] | API Controller (`App\Controllers\Api\<Modul>`) hanya memvalidasi format request, memanggil Service, dan mereturn format JSON standar `{ status, message, data }`. |
| **Rich Business Service** | [ ] | Semua kalkulasi bisnis, penentuan status alur kerja, dan aturan validasi berada di `App\Services\<Modul>`. |
| **Backend Financial Re-calculation** | [ ] | **KRITIS:** Untuk modul finansial (Keuangan, MKDT, BPB, dll.), backend WAJIB menghitung ulang total tagihan/sisa pembayaran dari database menggunakan helper `cleanNumeric()`. Dilarang mempercayai angka kalkulasi dari input POST frontend. |
| **Dedicated Repository Layer** | [ ] | Query database kompleks (JOIN multi-tabel, subquery, agregasi report) ditempatkan di `App\Repositories\<Modul>`. Bukan query mentah di Controller/Service. |
| **Database Transactions** | [ ] | Setiap method yang mengeksekusi 2+ query mutasi (INSERT/UPDATE/DELETE) wajib dibungkus `$db->transStart()` dan `$db->transComplete()`. |

---

## 3. Perlindungan Migrasi & Batas Modul (Zero-Downtime)

| Kriteria | Status | Panduan Evaluasi |
|---|:---:|---|
| **Legacy Route Preservation** | [ ] | Route lama di `app/Config/Routes.php` tetap dipertahankan dan bertindak sebagai *shim* / alias delegasi ke controller baru. |
| **No Global Route Breaking** | [ ] | Tidak mematikan `$routes->setAutoRoute(true)` secara sepihak jika masih ada bagian aplikasi yang mengandalkannya. |
| **Owner Table Isolation** | [ ] | Perubahan database hanya memanipulasi tabel milik modul itu sendiri (`Owner Tables` tercantum di `MODULE.md`). Modul lain hanya boleh diakses via public Service method. |
| **Backward-Compatibility Shims** | [ ] | Jika ada modul lain yang memanggil service/repo lama di namespace root (`App\Services\` atau `App\Repositories\`), sediakan shim class yang meng-`extends` class modular baru. |

---

## 4. Rencana Pengujian Terukur (Verification Strategy)

| Kriteria | Status | Panduan Evaluasi |
|---|:---:|---|
| **Playwright E2E Scenario** | [ ] | Rencana harus menentukan: halaman mana yang akan dibuka di browser, akun departemen mana yang akan digunakan untuk login, dan aksi apa yang diuji. |
| **Automated Commands Defined** | [ ] | Perintah CLI terdaftar: `php spark module:status`, `php spark module:deadcode <Modul>`, `php spark module:sync <Modul>`. |
| **Codegraph Blast Radius Checked** | [ ] | Simbol-simbol yang diubah sudah dipetakan pemanggilnya (*callers*) menggunakan MCP `codegraph_explore` atau grep untuk mencegah regresi tak terduga. |

---

## Formula Keputusan Plan

- **APPROVED**: Semua kriteria di atas terpenuhi ([x]). Rencana siap dieksekusi.
- **NEEDS_REVISION**: Ada kriteria bertanda [ ] (terutama terkait validasi finansial, transaksi DB, atau zero-downtime shims). Rencana harus disederhanakan atau diperbaiki terlebih dahulu.
