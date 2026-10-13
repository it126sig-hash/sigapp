# Checklist Validasi Plan Agen (Plan Best-Practice Gatekeeper)

Gunakan checklist ini untuk memvalidasi rencana (Implementation Plan atau Bugfix Plan) yang dibuat oleh agen AI SEBELUM mengeksekusi perubahan kode apapun di SIGAPP.

Setiap rencana harus memenuhi kriteria yang relevan dengan mode tugasnya (`FEATURE`, `BUGFIX`, `REFACTOR`, atau `UI_REPORT`).

---

## 1. Kepatuhan Prinsip Karpathy (Simplicity & Surgical) — [Wajib Semua Mode]

| Kriteria | Status | Panduan Evaluasi |
|---|:---:|---|
| **No Speculative Code** | [ ] | Apakah ada fitur/fungsi yang tidak diminta oleh user? Hapus jika ada. |
| **Minimum Necessary Diff** | [ ] | Apakah perbaikan hanya menyentuh file yang dibutuhkan? Dilarang merefaktor file yang tidak rusak kecuali tugasnya memang refaktor. |
| **Clear Success Criteria** | [ ] | Apakah setiap langkah memiliki kriteria verifikasi eksplisit (`[Step] → verify: [check]`)? |
| **No Orphaned Variables/Imports** | [ ] | Apakah rencana memastikan pembersihan variabel/import yang menjadi usang akibat perubahan? |

---

## 2. Standar Arsitektur CodeIgniter 4 (SIGAPP Style) — [Wajib Fitur Baru & Refaktor]

| Kriteria | Status | Panduan Evaluasi |
|---|:---:|---|
| **Thin Web Controller** | [ ] | Web Controller (`App\Controllers\Web\<Modul>`) hanya memanggil Service untuk mengambil data view, lalu me-render template atau redirect. Tidak ada logika `if/else` bisnis atau kalkulasi angka. *(Khusus Fitur & Refaktor)* |
| **Thin API Controller** | [ ] | API Controller (`App\Controllers\Api\<Modul>`) hanya memvalidasi format request, memanggil Service, dan mereturn format JSON standar `{ status, message, data }`. *(Khusus Fitur & Refaktor)* |
| **Rich Business Service** | [ ] | Semua kalkulasi bisnis, penentuan status alur kerja, dan aturan validasi berada di `App\Services\<Modul>`. |
| **Backend Financial Re-calculation** | [ ] | **KRITIS [Jika Sentuh Uang]:** Untuk modul finansial (Keuangan, MKDT, BPB, dll.), backend WAJIB menghitung ulang total tagihan/sisa pembayaran dari database menggunakan helper `cleanNumeric()`. Dilarang mempercayai angka kalkulasi dari input POST frontend. |
| **Dedicated Repository Layer** | [ ] | Query database kompleks (JOIN multi-tabel, subquery, agregasi report) ditempatkan di `App\Repositories\<Modul>`. Bukan query mentah di Controller/Service. *(Khusus Fitur & Refaktor)* |
| **Database Transactions** | [ ] | **KRITIS [Jika Mutasi Multi-Tabel]:** Setiap method yang mengeksekusi 2+ query mutasi (INSERT/UPDATE/DELETE) wajib dibungkus `$db->transStart()` dan `$db->transComplete()`. |

---

## 3. Perlindungan Kompatibilitas & Batas Modul — [Kondisional]

| Kriteria | Status | Panduan Evaluasi |
|---|:---:|---|
| **Legacy Route Preservation** | [ ] | **[Khusus Refaktor / Ubah Rute]:** Route lama di `app/Config/Routes.php` tetap dipertahankan dan bertindak sebagai *shim* / alias delegasi ke controller baru (Zero-Downtime). |
| **No Global Route Breaking** | [ ] | **[Khusus Refaktor]:** Tidak mematikan `$routes->setAutoRoute(true)` secara sepihak jika masih ada bagian aplikasi yang mengandalkannya. |
| **Owner Table Isolation** | [ ] | Perubahan database hanya memanipulasi tabel milik modul itu sendiri (`Owner Tables` tercantum di `MODULE.md`). Modul lain hanya boleh diakses via public Service method. |
| **Backward-Compatibility Shims** | [ ] | **[Khusus Refaktor]:** Jika ada modul lain yang memanggil service/repo lama di namespace root (`App\Services\` atau `App\Repositories\`), sediakan shim class yang meng-`extends` class modular baru. |

---

## 4. Rencana Pengujian Terukur (Verification Strategy) — [Wajib Semua Mode]

| Kriteria | Status | Panduan Evaluasi |
|---|:---:|---|
| **Playwright E2E Scenario** | [ ] | Rencana harus menentukan: halaman mana yang akan dibuka di browser, akun departemen mana yang akan digunakan untuk login, aksi apa yang diuji, dan pemanggilan skill `$sigapp-local-testing`. |
| **Codegraph Blast Radius Checked** | [ ] | Simbol-simbol yang diubah sudah dipetakan pemanggilnya (*callers*) menggunakan MCP `codegraph_explore` atau grep untuk mencegah regresi tak terduga. |
| **Automated Commands Defined** | [ ] | Perintah CLI terdaftar sesuai mode tugas (misal: `php spark routes` jika ada rute baru, `php spark module:status` jika refaktor, `php spark module:sync` jika ada perubahan struktur). |

---

## Formula Keputusan Plan

- **APPROVED**: Semua kriteria relevan di atas terpenuhi ([x]). Rencana siap dieksekusi.
- **NEEDS_REVISION**: Ada kriteria bertanda [ ] (terutama terkait validasi finansial, transaksi DB, atau callers blast radius). Rencana harus diperbaiki terlebih dahulu sebelum menulis kode.
