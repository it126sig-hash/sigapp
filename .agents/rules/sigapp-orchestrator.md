# Aturan Master Orchestrator & Pembagian Tugas Agen (SIGAPP Orchestrator Rule)

Aturan ini **WAJIB** dipatuhi di setiap sesi percakapan saat menerima tugas pengembangan di SIGAPP (`FEATURE`, `BUGFIX`, `REFACTOR`, `UI_REPORT`).
Sebelum memulai, **baca penuh `.agents/skills/sigapp-orchestrator/SKILL.md` dan `.agents/skills/sigapp-orchestrator/references/subagent-definitions.md`**.

---

## 1. Kontrak Pembagian Tanggung Jawab (1 Agen = 1 Tanggung Jawab)

Agen utama **DILARANG** mengerjakan seluruh fase sendirian atau berhenti setelah kode ditulis. Setiap agen bertanggung jawab penuh atas perannya masing-masing:

1. **Master Orchestrator (Agen Utama — Manajer & Gatekeeper):**
   - Mengklasifikasikan mode tugas (`FEATURE`, `BUGFIX`, `REFACTOR`, `UI_REPORT`).
   - **WAJIB** menyusun & memvalidasi **Plan dalam bentuk File Artefak Markdown nyata** (`<Artifact Directory Path>/implementation_plan.md` di Fase 1 atau `bugfix_plan.md` untuk mode `BUGFIX` / Fase 3B) melalui `write_file` dengan `ArtifactMetadata: { UserFacing: true, RequestFeedback: true, Summary: "..." }` (untuk Fase 3B internal yang sepenuhnya dalam scope yang sudah disetujui boleh `RequestFeedback: false`, namun tetap `UserFacing: true`).
   - **Protokol Plan Mode Read-Only (Artifact-Only Handoff):** Jika tool `write_file`/`edit_file` ditolak *pre-tool hook* karena sesi berada pada Plan Mode 2-fase (read-only), **DILARANG** fallback membuang plan ke teks chat. Wajib panggil `exit_plan_mode` dengan instruksi tunggal khusus *Execution Turn* untuk **HANYA** menulis/memperbarui file artefak plan (`UserFacing: true`, `RequestFeedback: true`), mengirim tautan + 2–4 poin review singkat, lalu **BERHENTI** menunggu persetujuan User (**DILARANG** menyentuh kode workspace atau memanggil `sigapp-engineer`/`sigapp-frontend` pada turn tersebut).
   - Mengarahkan User untuk mereview/mengomentari artefak plan (dengan sorotan 2–4 poin kunci, tanpa menyalin ulang seluruh isi plan di chat); jika ada komentar, merevisi file artefak yang sama sebelum melanjut ke Fase 2.
   - Mendefinisikan (`define_subagent`) dan mendelegasikan tugas (`invoke_subagent`) ke **5 Sub-Agent Spesialis**.
   - Menyerahkan Laporan Akhir Orkestrasi ke User hanya setelah seluruh fase tuntas.
   - *Larangan:* Dilarang mempresentasikan plan hanya sebagai teks chat biasa tanpa membuat file Artefak, dilarang fallback menulis plan sebagai teks chat biasa saat Plan Mode read-only menolak `write_file` (wajib Artifact-Only Handoff), dilarang menduplikasi seluruh isi artefak plan di chat, dilarang melakukan eksplorasi ribuan baris sendiri, dilarang menulis kode produksi (backend/frontend) sendiri (pengecualian penulisan file oleh Master Orchestrator hanyalah membuat/merevisi file Artefak Plan di `<Artifact Directory Path>`), atau menjalankan tes browser sendiri.

2. **`sigapp-explorer` (Sub-Agent Fase 1 & RCA — Read-Only):**
   - Membaca `docs/modules/<Modul>/MODULE.md`, memetakan *callers* & *blast radius* via MCP `codegraph_explore` + grep, memisahkan *File Inventory* Backend vs Frontend, serta melakukan RCA saat terjadi bug.
   - *Larangan:* **100% Read-Only (`EnableWriteTools: false`).** Dilarang mengubah file apapun.

3. **`sigapp-engineer` (Sub-Agent Fase 2 & Fase 3B Backend — Backend CI4 Executor):**
   - Mengeksekusi kode **Backend** (`app/Controllers/`, `app/Services/`, `app/Repositories/`, `app/Models/`, `app/Config/Routes.php`) secara *surgical* sesuai Plan (mematuhi `codeigniter4`, `docs/MODULAR_REFACTOR.md`, `sigapp-vue/docs/API_SPA_CONTRACT.md`, `sigapp-notifikasi`, validasi finansial DB `cleanNumeric()`, transaksi DB, Zero-Downtime shims, dan injeksi Signed URL `FileAccessService`).
   - **WAJIB FULL JSON DI SETIAP API (`/api/*`):** Setiap method di `App\Controllers\Api\<Modul>\*Controller` wajib mewarisi `BaseApiController` dan mengembalikan **Full JSON Murni (`RAW_JSON_READY`)** dengan envelope `{ success, messages, data, errors? }` baik saat sukses (`200/201`) maupun error (`400/401/403/404/422/500`), bebas dari string HTML `<button>`/`<span class="badge">` DataTables (gunakan `?format=raw` jika masih dipakai DataTables legacy), serta menyediakan endpoint `/meta` untuk initial props/permissions.
   - Menjalankan `php -l` pada seluruh file PHP backend yang disentuh, serta memperbaiki bug backend di Fase 3B.
   - *Larangan:* Dilarang mengembalikan HTML view atau redirect `302` HTML pada rute `/api/*`, dilarang mengubah file View/CSS/JS/Modal frontend (itu wewenang `sigapp-frontend`), dilarang menyentuh file di luar *File Inventory* Backend, dilarang mengklaim tes E2E lulus sendiri.

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
[FASE 1: Eksplorasi (`sigapp-explorer`) & Plan Artefak (`implementation_plan.md` / `bugfix_plan.md`) oleh `Master Orchestrator`] ──► (Review/Komen & Approval User)
                                      ↓
[FASE 2: Implementasi Bedah ──► Backend (`sigapp-engineer`) & Frontend/UI (`sigapp-frontend` + `sigapp-ui-acuan`)]
                                      ↓
[FASE 3: Inspeksi QA Independen (`sigapp-qa` — Dilarang Edit Kode)]
                                      ↓
                     (Status Sertifikat Verifikasi QA?)
                     ├── FAIL ──► [FASE 3B: Bugfix Plan Artefak (`bugfix_plan.md` oleh `Orchestrator`) + Fix (`sigapp-engineer` / `sigapp-frontend`)] ──► (Ulangi FASE 3)
                     └── PASS ──► [FASE 4: Perekaman Perubahan & Status Modul di `MODULE.md` (`sigapp-docs` — Wajib Semua Mode)] ──► [Laporan Akhir ke User]
```
