# Aturan Penjaminan Mutu Modul (SIGAPP Module QA & Remediation)

Dokumen ini adalah aturan **WAJIB** bagi seluruh agen AI dan developer saat merencanakan, memverifikasi, mendokumentasikan, dan memperbaiki modul di SIGAPP.

Sistem ini beroperasi dengan prinsip **Satu Agen Terpadu (Closed-Loop QA & Remediation)**: dari validasi rencana awal, audit pasca pengerjaan, pelacakan bug dengan `codegraph`, eksekusi perbaikan secara bedah (*surgical*), hingga pembaruan dokumentasi ledger.

---

## 1. GERBANG 1: Validasi Rencana (Pre-Execution Plan Gate)

Agen **DILARANG KERAS** menulis atau mengubah kode apapun sebelum menyusun rencana (plan) dan memastikan rencana tersebut lolos checklist best practice berikut:

1. **Prinsip Karpathy (Simplicity First & Surgical Changes):**
   - Tidak ada fitur spekulatif atau abstraksi berlebihan untuk kode sekali pakai.
   - Perubahan hanya menyentuh file dan baris yang benar-benar relevan dengan tugas.
   - Format rencana memuat kriteria sukses terukur: `[Step] → verify: [check]`.
2. **Pola CI4 Thin Controller:**
   - Web Controller hanya me-render view atau redirect.
   - API Controller hanya memvalidasi format input, memanggil Service, dan merespons JSON standar `{ status, message, data }`.
   - Logika bisnis, penentuan status, dan **validasi ulang angka finansial dari database** wajib berada di `Service`.
   - Query kompleks (JOIN, subquery, agregasi) wajib berada di `Repository`.
3. **Jaminan Zero-Downtime:**
   - Endpoint dan controller lama tidak boleh dihapus mendadak. Harus disediakan shim / route alias delegator agar antarmuka atau AJAX lama tidak putus.
   - Dilarang mematikan autoRoute secara global jika modul lain masih membutuhkannya.
4. **Keamanan Finansial & Transaksi Database:**
   - Setiap operasi yang melibatkan mutasi lebih dari 1 tabel wajib dilindungi atomisitas database (`$db->transStart()` / `$db->transComplete()`).
   - Angka finansial dari frontend tidak boleh dipercaya mentah-mentah; backend wajib menghitung ulang dari DB.
5. **Rencana Pengujian Eksplisit:**
   - Wajib mencantumkan skenario verifikasi Playwright dengan kredensial departemen terkait (Keuangan, MKDT, Produksi, Legal, Promosi).

---

## 2. GERBANG 2: Verifikasi Pasca Pengerjaan Modul (Post-Module Verification)

Setelah menyelesaikan pekerjaan pada suatu modul, agen **DILARANG** menganggap pekerjaan selesai atau beralih ke tugas lain sebelum melakukan verifikasi 5 pilar berikut:

1. **Pilar 1 — Status & Skor Kesiapan:**
   Jalankan `php spark module:status`. Skor kesiapan modul harus $\ge 80\%$ dengan status minimal `REFACTORED`.
2. **Pilar 2 — Integritas Route & Shims:**
   Pastikan route lama dan route baru keduanya terdaftar dan dapat merespons tanpa HTTP 404 / 500.
3. **Pilar 3 — Audit Callers & Dead Code:**
   Jalankan `php spark module:deadcode <Modul>` dan manfaatkan MCP `codegraph` (`codegraph_explore`) atau grep untuk memastikan tidak ada pemanggil yang tertinggal atau rute putus.
4. **Pilar 4 — Isolasi Batas Modul (Boundary Check):**
   Pastikan controller/service modul tidak memanipulasi tabel pemilik modul lain secara langsung. Komunikasi lintas modul harus melalui Service/Contract publik.
5. **Pilar 5 — Pengujian Antarmuka Playwright (Wajib):**
   Buka aplikasi lokal (`http://localhost/sigapp.dev`) menggunakan Playwright MCP dan uji modul dengan akun departemen yang berwenang:
   - Keuangan: `nisalis` / `nisalis123`
   - MKDT: `citra` / `citra123`
   - Produksi: `alika` / `alikas123`
   - Legal: `marlina` / `marlina123`
   - Promosi: `benny` / `benny123`
   Pastikan UI terbuka sempurna, tidak ada console error JavaScript, dan request AJAX mengembalikan status 200/201.

---

## 3. GERBANG 3: Sinkronisasi Dokumentasi & Ledger (Docs Sync)

Setiap pengerjaan modul yang telah terverifikasi bersih wajib disinkronkan dokumentasinya secara konsisten:

1. **Pembaruan Dokumen Modul:**
   Jalankan `php spark module:sync <Modul>` untuk memperbarui `docs/modules/<Modul>/MODULE.md` dari kode aktual.
2. **Pembaruan Global Migration Ledger:**
   Buka `docs/MIGRATION_PROGRESS.md`, perbarui baris modul tersebut:
   - Status: Ubah menjadi `COMPLETED`.
   - Sesi / Commit Hash: Catat hash commit terkait (atau ID sesi pengerjaan).
   - Terakhir Diverifikasi: Catat tanggal hari ini (`YYYY-MM-DD`).
3. **Dokumentasi Notifikasi (Jika Relevan):**
   Jika modul menambah atau memodifikasi notifikasi, perbarui `docs/modul-notifikasi.md` sesuai skill `sigapp-notifikasi`.
4. **Git Commit Mandiri:**
   Commit perubahan modul dan dokumentasinya secara terisolasi tanpa mencampuradukkan perubahan modul lain.

---

## 4. GERBANG 4: Protokol Remediasi Bug (Bug Remediation Protocol)

Jika dalam tahap verifikasi atau pengujian ditemukan error, broken UI, atau regresi:

1. **STOP — Jangan Langsung Ubah Kode Tanpa Rencana:**
   Dilarang melakukan tebak-tebakan kode atau "quick and dirty patch" yang berpotensi merusak area lain.
2. **Root Cause Analysis (RCA):**
   - Ambil pesan error dan stack trace lengkap dari log atau response Playwright.
   - Gunakan MCP `codegraph_explore` untuk menelusuri simbol yang bermasalah, relasi pemanggil (*callers*), dan *blast radius* perubahannya.
   - Gunakan grep untuk memastikan tidak ada tempat lain yang terpengaruh.
3. **Susun Bugfix Plan Artifact:**
   Buat rencana perbaikan terstruktur yang memuat:
   - Deskripsi error & langkah reproduksi
   - Hasil analisis akar masalah (file dan baris yang keliru)
   - Solusi bedah minimal (*surgical change*)
   - Kriteria verifikasi ulang
4. **Eksekusi Perbaikan Bedah (*Surgical Fix*):**
   Ubah hanya baris yang esensial. Pertahankan gaya kode dan konvensi yang ada.
5. **Loop Verification:**
   Jalankan kembali tes yang sebelumnya gagal. Jika sudah berhasil, ulangi seluruh verifikasi Gerbang 2 untuk memastikan tidak ada efek samping.

---

## 5. Hubungan dengan Skill dan Subagent

- Gunakan skill **`sigapp-module-qa`** untuk memandu eksekusi operasional dan melihat checklist detail di `.agents/skills/sigapp-module-qa/references/`.
- Seluruh siklus ini diutamakan berjalan dalam **1 agen terpadu** demi menjaga konteks diagnosis dan memori error runtime.
- Jika pengguna meminta audit di latar belakang (*background check*), agen dapat meluncurkan subagent dengan instruksi yang merujuk pada aturan ini.
