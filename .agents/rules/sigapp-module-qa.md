# Aturan Penjaminan Mutu & Remediasi Bug (SIGAPP QA Rule)

Dokumen ini adalah aturan ringkas **4 Gerbang Kualitas (Quality Gates)** di SIGAPP.
Untuk prosedur operasional lengkap, checklist, dan format sertifikat, baca `.agents/skills/sigapp-module-qa/SKILL.md`.

---

## 1. GERBANG 1: Validasi Rencana (Oleh `Master Orchestrator`)
Dilarang menugaskan `sigapp-engineer` atau `sigapp-frontend` menulis kode sebelum Plan lolos checklist `.agents/skills/sigapp-module-qa/references/plan-validation-checklist.md`:
1. **Prinsip Karpathy:** Tanpa kode spekulatif, diff minimal, kriteria verifikasi eksplisit (`[Step] → verify: [check]`).
2. **Arsitektur Backend (`codeigniter4`) & Standar Frontend (`sigapp-ui-acuan`):**
   - Backend (`sigapp-engineer`): Thin Web/Api Controller, Rich Service, Dedicated Repository.
   - Frontend (`sigapp-frontend`): 3-Tier CSS eksternal (`public/assets/css/<modul>.css` dengan `filemtime`), CSS scoping, warna `#2057a3`, `#modal_detail` baseline, `divider divider-left`, mobile tab 1 baris, dan URL `FileAccessService`.
3. **Keamanan Finansial & Atomisitas DB:** Hitung ulang nominal uang di backend dari DB menggunakan `cleanNumeric()`; bungkus mutasi multi-tabel dengan `$db->transStart()` & `$db->transComplete()`.
4. **Zero-Downtime Shims:** Pertahankan rute/service lama sebagai delegator saat refaktor atau mengubah rute.
5. **Strategi Verifikasi:** Cantumkan skenario Playwright E2E, departemen login, dan pemeriksaan CLI `php spark`.
6. **Pembagian Fase & Dokumen Kerja:** Pecah pekerjaan menjadi fase eksekusi berurutan (`Fase A`, `Fase B`, `Fase C`, `Fase D`, ...) dengan *exit criteria* per fase dan tetapkan path dokumen kerja `docs/YYYY-MM-DD-<slug-nama-pekerjaan>.md`.

## 2. GERBANG 2: Inspeksi QA Independen (Oleh `sigapp-qa` — Fase 3)
Setelah `sigapp-engineer` dan/atau `sigapp-frontend` selesai, `sigapp-qa` menguji secara independen (**DILARANG mengubah file kode/dokumen apapun**):
1. **Codegraph Callers Audit (Wajib Semua Mode):** Pastikan 0 broken callers di PHP, View, maupun JS via MCP `codegraph_explore` + grep.
2. **Playwright E2E & UI Baseline Test (Wajib Semua Mode):** Baca `.agents/skills/sigapp-local-testing/SKILL.md`, login ke `http://localhost/sigapp.dev` dengan akun departemen terkait, pastikan **0 console error (404/500)**, checklist `sigapp-ui-acuan` terpenuhi, dan alur bisnis berjalan.
3. **Pemeriksaan Spark Kondisional:**
   - `php spark routes` (jika rute bertambah/berubah)
   - `php spark module:deadcode <Modul>` (jika simbol dihapus/dipindah)
   - `php spark module:status` (wajib pada mode `REFACTOR`, target `REFACTORED` $\ge 80\%$)
4. **Output:** Terbitkan Sertifikat Verifikasi QA berstatus **`PASS`** atau **`FAIL`** (beserta stack trace, RCA awal, dan domain bug: Backend vs Frontend).

## 3. GERBANG 3: Protokol Remediasi Bug (Fase 3B — Saat QA `FAIL`)
Jika `sigapp-qa` menerbitkan sertifikat `FAIL`:
1. **Dilarang Menebak Kode:** Gunakan stack trace dan MCP `codegraph_explore` (`sigapp-qa` / `sigapp-explorer`) untuk memastikan akar masalah dan *blast radius*.
2. **Bugfix Plan Artifact:** `Master Orchestrator` menyusun rencana perbaikan terstruktur.
3. **Surgical Fix Sesuai Domain:**
   - Bug Backend ──► dieksekusi oleh **`sigapp-engineer`**.
   - Bug Frontend/UI ──► dieksekusi oleh **`sigapp-frontend`**.
4. **Re-Verification Loop:** `sigapp-qa` menguji ulang seluruh Gerbang 2 hingga berstatus **`PASS`**.

## 4. GERBANG 4: Perekaman Perubahan & Sinkronisasi Dokumentasi Modul (Oleh `sigapp-docs` — Fase 4)
Dijalankan oleh `sigapp-docs` **HANYA SETELAH** sertifikat QA berstatus `PASS`:
1. **`docs/modules/<Modul>/MODULE.md` (WAJIB SEMUA MODE):** Jalankan `php spark module:sync <Modul>` dan rekam status modul terkini beserta perubahan yang baru dilakukan, mencakup: **Routes, Controller, Service, Repository, Model, View/CSS/JS, Contract Public untuk diakses, Fungsi-Fungsi Penting (`Key Functions`), Fungsi/Route/Shim Deprecated (`Deprecated & Legacy Shims`), serta Riwayat Perubahan (`Change Log`)**.
2. **`docs/YYYY-MM-DD-<slug-nama-pekerjaan>.md` (WAJIB SEMUA MODE):** Audit & finalisasi dokumen kerja (`Status Keseluruhan: COMPLETED`, semua fase `DONE`, entri log Fase 4, serta `Ringkasan File Final` sinkron dengan `git status --short`).
3. **`docs/MIGRATION_PROGRESS.md`:** Ubah menjadi `COMPLETED` **HANYA** untuk mode `REFACTOR` modul penuh (jangan ubah untuk `FEATURE`, `BUGFIX`, atau `UI_REPORT`).
4. **`docs/modul-notifikasi.md`:** Perbarui jika ada penambahan/perubahan notifikasi.
5. **Git Status:** Pastikan hanya file target tugas dan dokumen relevan yang berubah.
