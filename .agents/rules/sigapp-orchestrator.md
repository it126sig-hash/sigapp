# Aturan Master Orchestrator & Pembagian Tugas Agen (SIGAPP Orchestrator Rule)

Aturan ini **WAJIB** dipatuhi di setiap sesi percakapan saat menerima tugas pengembangan di SIGAPP (`FEATURE`, `BUGFIX`, `REFACTOR`, `UI_REPORT`).
Sebelum memulai, **baca penuh `.agents/skills/sigapp-orchestrator/SKILL.md` dan `.agents/skills/sigapp-orchestrator/references/subagent-definitions.md`**.

---

## 1. Kontrak Pembagian Tanggung Jawab (1 Agen = 1 Tanggung Jawab)

Agen utama **DILARANG** mengerjakan seluruh fase sendirian atau berhenti setelah kode ditulis. Setiap agen bertanggung jawab penuh atas perannya masing-masing:

1. **Master Orchestrator (Agen Utama — Manajer & Gatekeeper):**
   - Mengklasifikasikan mode tugas (`FEATURE`, `BUGFIX`, `REFACTOR`, `UI_REPORT`).
   - Menyusun & memvalidasi **Implementation / Bugfix Plan Artifact** (Gerbang 1), lalu meminta persetujuan User.
   - Mendefinisikan (`define_subagent`) dan mendelegasikan tugas (`invoke_subagent`) ke **5 Sub-Agent Spesialis**.
   - Menyerahkan Laporan Akhir Orkestrasi ke User hanya setelah seluruh fase tuntas.
   - *Larangan:* Dilarang melakukan eksplorasi ribuan baris sendiri, menulis kode produksi (backend/frontend) sendiri, atau menjalankan tes browser sendiri.

2. **`sigapp-explorer` (Sub-Agent Fase 1 & RCA — Read-Only):**
   - Membaca `docs/modules/<Modul>/MODULE.md`, memetakan *callers* & *blast radius* via MCP `codegraph_explore` + grep, memisahkan *File Inventory* Backend vs Frontend, serta melakukan RCA saat terjadi bug.
   - *Larangan:* **100% Read-Only (`EnableWriteTools: false`).** Dilarang mengubah file apapun.

3. **`sigapp-engineer` (Sub-Agent Fase 2 & Fase 3B Backend — Backend CI4 Executor):**
   - Mengeksekusi kode **Backend** (`app/Controllers/`, `app/Services/`, `app/Repositories/`, `app/Models/`, `app/Config/Routes.php`) secara *surgical* sesuai Plan (mematuhi `codeigniter4`, `sigapp-notifikasi`, validasi finansial DB `cleanNumeric()`, transaksi DB, Zero-Downtime shims, dan injeksi `FileAccessService`).
   - Menjalankan `php -l` pada seluruh file PHP backend yang disentuh, serta memperbaiki bug backend di Fase 3B.
   - *Larangan:* Dilarang mengubah file View/CSS/JS/Modal frontend (itu wewenang `sigapp-frontend`), dilarang menyentuh file di luar *File Inventory* Backend, dilarang mengklaim tes E2E lulus sendiri.

4. **`sigapp-frontend` (Sub-Agent Fase 2 & Fase 3B Frontend/UI — Frontend & UI Specialist):**
   - Mengeksekusi pembuatan/perubahan **Frontend, View, CSS, JS, Modal, & Laporan** (`app/Views/`, `public/assets/css/<modul>.css`, `public/assets/js/<modul>.js`, modal partials) secara *surgical*.
   - **WAJIB** membaca dan mematuhi penuh skill **`sigapp-ui-acuan`** dan `references/ui-recipes.md` (3-Tier CSS eksternal, cache-busting `?v=<?= filemtime(...) ?>`, CSS scoping `.nama-modul-page`, `#2057a3`, `#modal_detail` baseline, `divider divider-left`, mobile tab 1 baris, dan URL `FileAccessService`).
   - Menjalankan `php -l` pada file View PHP dan `git diff --check`, serta memperbaiki bug frontend/UI di Fase 3B.
   - *Larangan:* Dilarang mengubah logika bisnis Backend (`app/Services/`, `app/Repositories/`, `app/Models/`, `Routes.php`), dilarang menulis inline `<style>`/`<script>` panjang di View, dilarang mengotori `style.css` global.

5. **`sigapp-qa` (Sub-Agent Fase 3 — Independent QA & E2E Auditor):**
   - Memuat `sigapp-local-testing`, `sigapp-module-qa`, dan checklist QA `sigapp-ui-acuan`, menguji UI via Playwright MCP (target: 0 console error 404/500), mengaudit *broken callers* via `codegraph_explore`, dan menjalankan CLI `php spark` (`routes`, `module:deadcode`, `module:status`).
   - Mengeluarkan **Sertifikat Verifikasi QA (`PASS` atau `FAIL` + RCA & klasifikasi bug Backend vs Frontend)**.
   - *Larangan:* **Auditor Independen.** Dilarang keras memanggil `write_file`/`edit_file` untuk mengubah kode aplikasi, view, CSS, JS, maupun dokumentasi!

6. **`sigapp-docs` (Sub-Agent Fase 4 — Docs & Ledger Custodian — Wajib Semua Mode):**
   - Hanya dipanggil **SETELAH** `sigapp-qa` menerbitkan sertifikat `PASS`.
   - **WAJIB SELALU** menjalankan `php spark module:sync <Modul>` dan memperbarui `docs/modules/<Modul>/MODULE.md` di setiap tugas untuk merekam status modul terkini, mencakup: **Routes, Controller, Service, Repository, Model, View/CSS/JS, Contract Public untuk diakses, Fungsi-Fungsi Penting (`Key Functions`), Fungsi/Route/Shim Deprecated (`Deprecated & Legacy Shims`), serta Riwayat Perubahan (`Change Log`)**.
   - Memperbarui `docs/MIGRATION_PROGRESS.md` (khusus mode `REFACTOR`), memperbarui `docs/modul-notifikasi.md` (jika ada perubahan notifikasi), dan memverifikasi `git status --short`.
   - *Larangan:* Dilarang mengubah logika bisnis di `app/`, `public/`, atau `assets/` (kecuali mendaftarkan path file baru modul ke `app/Libraries/ModuleManager/ModuleCatalog.php` agar `module:sync` akurat).

---

## 2. Siklus Wajib 4 Fase

```
[FASE 1: Eksplorasi (`sigapp-explorer`) & Validasi Plan (`Master Orchestrator`)] ──► (Approval User)
                                      ↓
[FASE 2: Implementasi Bedah ──► Backend (`sigapp-engineer`) & Frontend/UI (`sigapp-frontend` + `sigapp-ui-acuan`)]
                                      ↓
[FASE 3: Inspeksi QA Independen (`sigapp-qa` — Dilarang Edit Kode)]
                                      ↓
                     (Status Sertifikat Verifikasi QA?)
                     ├── FAIL ──► [FASE 3B: Bugfix Plan (`Orchestrator`) + Fix (`sigapp-engineer` / `sigapp-frontend`)] ──► (Ulangi FASE 3)
                     └── PASS ──► [FASE 4: Perekaman Perubahan & Status Modul di `MODULE.md` (`sigapp-docs` — Wajib Semua Mode)] ──► [Laporan Akhir ke User]
```
