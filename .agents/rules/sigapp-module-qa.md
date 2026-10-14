# Aturan Penjaminan Mutu Tugas & Modul (SIGAPP QA & Remediation Rule)

Dokumen ini adalah aturan **WAJIB** bagi seluruh agen AI dan developer saat merencanakan, memverifikasi, mendokumentasikan, dan memperbaiki tugas atau modul di SIGAPP: baik penambahan fitur baru, perbaikan bug, pembaruan UI/laporan, maupun refaktor modul.

Sistem ini beroperasi dengan prinsip **Satu Alur Tertutup Adaptif (Adaptive Closed-Loop QA & Remediation)**: dari validasi rencana awal, audit pasca-implementasi, pelacakan bug dengan `codegraph`, eksekusi perbaikan secara bedah (*surgical*), hingga pembaruan dokumentasi ledger yang relevan.

---

## 1. GERBANG 1: Validasi Rencana (Pre-Execution Plan Gate)

Agen **DILARANG KERAS** menulis atau mengubah kode apapun sebelum menyusun rencana (Implementation Plan / Bugfix Plan) dan memastikan rencana tersebut lolos checklist best practice berikut:

1. **Prinsip Karpathy (Simplicity First & Surgical Changes) — [Wajib Semua Mode]:**
   - Tidak ada fitur spekulatif atau abstraksi berlebihan untuk kode sekali pakai.
   - Perubahan hanya menyentuh file dan baris yang benar-benar relevan dengan tugas.
   - Format rencana memuat kriteria sukses terukur: `[Step] → verify: [check]`.
2. **Pola CI4 Thin Controller & Layering — [Wajib Fitur Baru & Refaktor]:**
   - Web Controller hanya me-render view atau redirect.
   - API Controller hanya memvalidasi format input, memanggil Service, dan merespons JSON standar `{ status, message, data }`.
   - Logika bisnis dan penentuan status alur kerja wajib berada di `Service`.
   - Query kompleks (JOIN, subquery, agregasi) wajib berada di `Repository`.
3. **Keamanan Finansial & Transaksi Database — [Wajib Jika Menyentuh Keuangan/Mutasi DB]:**
   - Setiap operasi yang melibatkan mutasi lebih dari 1 tabel wajib dilindungi transaksi database (`$db->transStart()` / `$db->transComplete()`).
   - Angka finansial dari frontend tidak boleh dipercaya mentah-mentah; backend wajib menghitung ulang dari DB dengan helper `cleanNumeric()`.
4. **Jaminan Zero-Downtime & Kompatibilitas — [Wajib Khusus Refaktor & Perubahan Rute]:**
   - Endpoint dan controller lama tidak boleh dihapus mendadak. Harus disediakan shim / route alias delegator agar antarmuka atau AJAX lama tidak putus.
   - Dilarang mematikan autoRoute secara global jika modul lain masih membutuhkannya.
5. **Rencana Pengujian Eksplisit — [Wajib Semua Mode]:**
   - Wajib mencantumkan skenario verifikasi Playwright dengan kredensial departemen terkait (Keuangan, MKDT, Produksi, Legal, Promosi) serta pemanggilan skill `$sigapp-local-testing`.

---

## 2. GERBANG 2: Verifikasi Pasca-Pekerjaan (Post-Execution Verification)

Setelah menyelesaikan implementasi kode, agen **DILARANG** menganggap pekerjaan selesai atau beralih ke tugas lain sebelum melakukan verifikasi kualitas secara adaptif:

### A. Pilar Inti (Wajib Mutlak untuk Semua Mode: FEATURE, BUGFIX, REFACTOR, UI_REPORT)
1. **Audit Callers & Integritas Simbol (`codegraph`):**
   Gunakan MCP `codegraph_explore` atau grep untuk memastikan fungsi/metode yang diubah memiliki parameter, kembalian, dan callers yang valid. Pastikan 0 broken callers.
2. **Pengujian Antarmuka Playwright E2E UI:**
   - **WAJIB** muat skill `$sigapp-local-testing` sebelum membuka browser.
   - Buka aplikasi lokal (`http://localhost/sigapp.dev`) via Playwright MCP dan login dengan akun departemen yang berwenang:
     - Keuangan: `nisalis` / `nisalis123`
     - MKDT: `citra` / `citra123`
     - Produksi: `alika` / `alikas123`
     - Legal: `marlina` / `marlina123`
     - Promosi: `benny` / `benny123`
   - Pastikan UI terbuka sempurna, **0 console error JavaScript (500/404)**, dan fungsionalitas yang diubah berjalan sesuai harapan.

### B. Pilar Kondisional (Sesuai Komponen yang Disentuh)
3. **Integritas Route & Shims:**
   Jalankan `php spark routes` jika ada penambahan rute baru atau modifikasi endpoint controller. Pastikan tidak ada `Controller Not Found`.
4. **Audit Dead Code:**
   Jalankan `php spark module:deadcode <Modul>` jika ada metode yang dipindahkan, di-deprecate, atau dihapus.
5. **Skor Kesiapan Modular (`module:status`):**
   Wajib dijalankan pada mode `REFACTOR` modul (target skor kesiapan $\ge 80\%$, status `REFACTORED`).
6. **Isolasi Batas Modul (Boundary Check):**
   Pastikan controller/service tidak memanipulasi tabel pemilik modul lain secara langsung. Komunikasi lintas modul harus melalui Service/Contract publik.

---

## 3. GERBANG 3: Protokol Remediasi Bug (Bug Remediation Protocol)

Jika dalam tahap verifikasi atau pengujian ditemukan error, broken UI, atau regresi:

1. **STOP — Jangan Langsung Ubah Kode Tanpa Rencana:**
   Dilarang melakukan tebak-tebakan kode atau patch spekulatif.
2. **Root Cause Analysis (RCA):**
   - Ambil pesan error dan stack trace lengkap dari log atau response Playwright.
   - Gunakan MCP `codegraph_explore` untuk menelusuri simbol yang bermasalah, relasi pemanggil (*callers*), dan *blast radius* perubahannya.
   - Gunakan grep untuk memastikan konsistensi teks.
3. **Susun Bugfix Plan Artifact:**
   Buat rencana perbaikan terstruktur yang memuat gejala, akar masalah, blast radius, perbaikan bedah minimal, dan kriteria verifikasi ulang.
4. **Eksekusi Perbaikan Bedah (*Surgical Fix*):**
   Ubah hanya baris yang esensial. Pertahankan gaya kode dan konvensi yang ada.
5. **Loop Verification:**
   Jalankan kembali tes yang tadinya gagal. Jika sudah berhasil, ulangi seluruh verifikasi Gerbang 2 untuk memastikan tidak ada efek samping.

---

## 4. GERBANG 4: Sinkronisasi Dokumentasi & Ledger Adaptif (Docs Sync)

Dokumentasi disinkronkan secara selektif dan adaptif setelah seluruh pengujian hijau:

1. **Dokumentasi Modul (`MODULE.md`):**
   Jalankan `php spark module:sync <Modul>` jika ada penambahan/perubahan struktur controller, service, repo, rute, view, JS, atau tabel modul.
2. **Global Migration Ledger (`docs/MIGRATION_PROGRESS.md`):**
   Diperbarui **HANYA** jika tugas merupakan penyelesaian migrasi/refaktor modul penuh (`REFACTOR`):
   - Ubah status menjadi `COMPLETED`.
   - Catat commit hash / sesi dan tanggal verifikasi (`YYYY-MM-DD`).
   *(Catatan: Tugas berupa penambahan fitur biasa, bugfix, atau UI tweak TIDAK mengubah tabel global migration ledger).*
3. **Dokumentasi Notifikasi (`docs/modul-notifikasi.md`):**
   Wajib diperbarui jika tugas menambah atau mengubah event notifikasi (`tambah_notif`).
4. **Git Commit Terisolasi:**
   Commit perubahan kode beserta dokumentasi terkait secara mandiri tanpa mencampuradukkan perubahan lain.

---

## 5. Hubungan dengan Skill dan Subagent

- Gunakan skill **`sigapp-module-qa`** untuk panduan operasional detail dan checklist.
- Gunakan aturan **`sigapp-orchestrator`** sebagai pemandu siklus hidup pengembangannya.
- Agen utama dapat mengeksekusi langsung atau mendelegasikan verifikasi ke subagent (`invoke_subagent`) dengan prompt inspeksi yang mengacu pada aturan ini.
