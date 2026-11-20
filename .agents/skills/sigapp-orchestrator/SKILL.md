---
name: sigapp-orchestrator
description: >
  Master Orchestrator untuk mengoordinasikan seluruh alur kerja pengembangan di SIGAPP dengan pembagian tugas tegas ke 5 Sub-Agent Spesialis (`sigapp-explorer`, `sigapp-engineer`, `sigapp-frontend`, `sigapp-qa`, `sigapp-docs`).
  Mendukung 4 mode tugas: Fitur Baru (FEATURE), Perbaikan Bug (BUGFIX), Refaktor & Migrasi Modul (REFACTOR), serta UI/Frontend/Laporan (UI_REPORT).
  Gunakan skill ini ketika user meminta: "buat fitur X", "tambahkan fungsi Y", "perbaiki bug/error Z",
  "ubah tampilan/cetak laporan W", "refaktor modul X", "buat planning modul X lalu implementasikan",
  "orkestrasi tugas X", atau memulai pengerjaan tugas pengembangan di SIGAPP.
---

# SIGAPP Master Orchestrator Playbook (Multi-Agent Architecture)

Playbook ini memandu agen utama bertindak sebagai **Master Orchestrator** yang mengoordinasikan **5 Sub-Agent Spesialis**. Setiap agen memiliki **satu tanggung jawab spesifik (*Single Responsibility*)** dan batasan ketat (*guardrails*) agar tidak ada tumpang tindih peran antara Backend, Frontend/UI, QA, dan Dokumentasi.

---

## 1. Pembagian Tugas & Tanggung Jawab per Agen (Strict Separation of Duties)

| Agen | Peran & Fase | Tanggung Jawab Utama (KPI) | Larangan Keras (*Guardrails*) |
|---|---|---|---|
| **0. Master Orchestrator** *(Agen Utama)* | Manajer Siklus Hidup & Gatekeeper *(Fase 1–4)* | - Mengklasifikasikan mode tugas (`FEATURE`, `BUGFIX`, `REFACTOR`, `UI_REPORT`)<br>- Menyusun & memvalidasi **Plan dalam bentuk File Artefak Markdown nyata** (`implementation_plan.md` / `bugfix_plan.md` di `<Artifact Directory Path>` via `write_file` dengan `ArtifactMetadata`: `UserFacing: true`, `RequestFeedback: true`), lalu meminta review/komentar & persetujuan User<br>- Mendefinisikan (`define_subagent`) & menugaskan (`invoke_subagent`) ke-5 Sub-Agent<br>- Mengawasi loop remediasi bug dan menerbitkan laporan akhir ke User | **DILARANG** hanya menulis plan panjang sebagai teks chat tanpa membuat file Artefak, **DILARANG** menyalin ulang seluruh isi artefak plan di balasan chat, **DILARANG** melanjut ke Fase 2 sebelum artefak plan disetujui User, **DILARANG** melakukan eksplorasi kasar ribuan baris sendiri, **DILARANG** menulis kode produksi backend/frontend sendiri (pengecualian penulisan bagi Master Orchestrator hanyalah membuat/merevisi file Artefak Plan di `<Artifact Directory Path>`), **DILARANG** menjalankan tes browser sendiri, **DILARANG** menutup tugas sebelum menerima sertifikat `PASS` dari `sigapp-qa`. |
| **1. `sigapp-explorer`** | Codebase Explorer & Architect *(Fase 1 & RCA)* | - Membaca `docs/modules/<Modul>/MODULE.md` (status modul, kontrak publik, fungsi penting, fungsi deprecated, & riwayat perubahan)<br>- Memetakan *callers*, *callees*, dan *blast radius* via MCP `codegraph_explore` + grep<br>- Memisahkan *File Inventory* Backend vs Frontend<br>- Melakukan Root Cause Analysis (RCA) saat terjadi bug | **100% READ-ONLY** (`EnableWriteTools: false`). Dilarang mengubah file apapun. |
| **2. `sigapp-engineer`** | Backend CI4 Engineer *(Fase 2 & 3B Backend)* | - Mengeksekusi perubahan kode **Backend** (`app/Controllers/`, `app/Services/`, `app/Repositories/`, `app/Models/`, `app/Config/Routes.php`) sesuai Plan<br>- **WAJIB FULL JSON DI SETIAP API (`/api/*`):** Setiap method di `App\Controllers\Api\<Modul>\*Controller` wajib mewarisi `BaseApiController` dan mengembalikan **Full JSON Murni (`RAW_JSON_READY`)** dengan envelope `{ success, messages, data, errors? }` untuk seluruh skenario (sukses `200/201` maupun gagal `400/401/403/404/422/500`), bebas string HTML `<button>`/`<span class="badge">` DataTables (gunakan `?format=raw` jika menjaga backward-compatibility view CI4 lama), serta menyediakan endpoint `/meta` sesuai [`docs/MODULAR_REFACTOR.md`](file:///c:/Users/Salivs/Data/laragon/www/sigapp.dev/docs/MODULAR_REFACTOR.md) & [`sigapp-vue/docs/API_SPA_CONTRACT.md`](file:///c:/Users/Salivs/Data/laragon/www/sigapp-vue/docs/API_SPA_CONTRACT.md)<br>- Mematuhi skill `codeigniter4` & `sigapp-notifikasi` (validasi finansial DB `cleanNumeric()`, transaksi DB, Zero-Downtime shims, Signed URL `FileAccessService` di backend)<br>- Menjalankan `php -l` pada file PHP backend yang diubah & melaporkan detail perubahan kontrak/fungsi penting/deprecated ke Orchestrator | **DILARANG** mengembalikan HTML view, HTTP redirect `302` ke halaman HTML, atau string HTML DataTables pada endpoint API baru. **DILARANG** mengubah file View/CSS/JS/Modal frontend (itu tanggung jawab `sigapp-frontend`). **DILARANG** menyentuh file di luar *File Inventory* Backend. **DILARANG** mengklaim tes E2E lulus sendiri. |
| **3. `sigapp-frontend`** | Frontend, UI & Report Specialist *(Fase 2 & 3B Frontend)* | - Mengeksekusi pembuatan/perubahan **Frontend & UI** (`app/Views/`, `public/assets/css/<modul>.css`, `public/assets/js/<modul>.js`, modal partials, template laporan)<br>- **WAJIB** mematuhi penuh skill `sigapp-ui-acuan` & `references/ui-recipes.md` (3-Tier CSS eksternal, `?v=<?= filemtime(...) ?>`, CSS scoping, `#2057a3`, `#modal_detail` baseline, `divider divider-left`, mobile tab 1 baris, URL `FileAccessService`) serta mengonsumsi response **Full JSON (`{ success, messages, data }`)** dari `/api/*`<br>- Menjalankan `php -l` pada file View PHP dan `git diff --check` & melaporkan perubahan View/CSS/JS ke Orchestrator | **DILARANG** mengubah logika bisnis di `app/Services/`, `app/Repositories/`, `app/Models/`, atau `Routes.php` (itu wewenang `sigapp-engineer`). **DILARANG** menulis inline `<style>`/`<script>` panjang di View atau mengotori `style.css` global. |
| **4. `sigapp-qa`** | Independent QA & E2E Inspector *(Fase 3)* | - Memuat `.agents/skills/sigapp-local-testing/SKILL.md` & `sigapp-module-qa`<br>- Menguji UI nyata via Playwright MCP sesuai akun departemen (0 console error + audit checklist `sigapp-ui-acuan`)<br>- **Memverifikasi Full JSON Contract pada `/api/*`:** Memastikan seluruh endpoint API mengembalikan JSON murni `{ success, messages, data }` (`RAW_JSON_READY`), tidak ada redirect `302` HTML atau HTML string debt pada response API baru<br>- Mengaudit *broken callers* via `codegraph_explore`<br>- Menjalankan CLI `php spark routes`, `module:deadcode`, `module:status`<br>- Mengeluarkan **Sertifikat QA (`PASS` / `FAIL` + RCA & klasifikasi bug Backend vs Frontend)** | **AUDITOR INDEPENDEN.** **DILARANG KERAS** memanggil `write_file`/`edit_file` untuk mengubah kode aplikasi, view, CSS, JS, maupun docs! Jika gagal, lapor `FAIL` ke Orkestrator agar diperbaiki oleh `sigapp-engineer` atau `sigapp-frontend`. |
| **5. `sigapp-docs`** | Docs & Ledger Custodian *(Fase 4 — Wajib Semua Mode)* | - **WAJIB SELALU** memperbarui `docs/modules/<Modul>/MODULE.md` di setiap tugas: menjalankan `php spark module:sync <Modul>` lalu mendokumentasikan status modul terkini, mencakup **Routes, Controller, Service, Repository, Model, View/Assets, Contract Public untuk diakses, Fungsi-Fungsi Penting (`Key Functions`), Fungsi/Route Deprecated (`Deprecated & Legacy Shims`), serta Riwayat Perubahan (`Change Log`)**<br>- Memperbarui `docs/MIGRATION_PROGRESS.md` (khusus `REFACTOR` selesai)<br>- Memperbarui `docs/modul-notifikasi.md` (jika ada perubahan notifikasi)<br>- Memverifikasi `git status --short` bersih & terisolasi | Hanya berjalan **SETELAH** Sertifikat QA berstatus `PASS`. **DILARANG** menyentuh file kode di `app/`, `public/`, atau `assets/`. |

> **Spesifikasi Lengkap Sub-Agent:** Buka dan gunakan definisi JSON `define_subagent` serta template `invoke_subagent` di [references/subagent-definitions.md](references/subagent-definitions.md).

---

## 2. Klasifikasi 4 Mode Tugas (Umum vs Migrasi Modular)

Orkestrator ini dirancang untuk **seluruh aktivitas pengembangan umum sehari-hari di SIGAPP**, **BUKAN hanya untuk migrasi modular**. Sebelum mendelegasikan tugas, Master Orchestrator mengidentifikasi permintaan ke dalam salah satu dari 4 mode:

1. **`FEATURE` (Pembuatan Fitur / Halaman / Modul Baru & Enhancement Umum):**
   - Pembuatan halaman/modul baru dari nol, endpoint baru, form/modal baru, laporan baru, atau penambahan kapabilitas pada modul yang sudah ada (baik modul yang sudah modular maupun yang masih legacy, maupun komponen global).
   - *Aturan Arsitektur:* Kode baru wajib mengikuti pola bersih `Thin Controller -> Service -> Repository` (`codeigniter4`) dan standar UI `sigapp-ui-acuan`, tanpa harus merombak seluruh kode legacy yang tidak terkait.
2. **`BUGFIX` (Perbaikan Bug & Error Harian):**
   - Penanganan runtime error, perbaikan logika kalkulasi backend (`sigapp-engineer`), atau perbaikan broken UI/JS/AJAX/modal (`sigapp-frontend`) di bagian manapun dari aplikasi.
   - *Aturan Arsitektur:* Ikuti arsitektur file yang sedang disentuh secara *surgical* (jangan memaksa refaktor struktur folder jika tugasnya hanya bugfix).
3. **`UI_REPORT` (Pembuatan / Perubahan UI, Frontend, Global Layout & Cetak Laporan):**
   - Pembuatan atau pembaruan tampilan halaman, modal, tab, divider, komponen global (`template/header.php`, sidebar, `style.css`), stylesheet `public/assets/css/<modul>.css`, script `public/assets/js/<modul>.js`, DataTables, atau laporan matriks/PDF/Excel yang dikerjakan oleh **`sigapp-frontend`** dengan acuan wajib `sigapp-ui-acuan`.
4. **`REFACTOR` (Khusus Migrasi ke Arsitektur Modular):**
   - Mode khusus ketika user secara eksplisit meminta migrasi/refaktor modul legacy ke struktur subfolder modular (`Web/<Modul>`, `Api/<Modul>`, `Services/<Modul>`, `Repositories/<Modul>`), pembuatan shims Zero-Downtime, dan penutupan utang migrasi di `docs/MIGRATION_PROGRESS.md`.

---

## 3. Alur Orkestrasi End-to-End & Handoff Antar-Agen

```
[User Request] ──► Master Orchestrator mengklasifikasikan Mode Tugas (FEATURE / BUGFIX / REFACTOR / UI_REPORT)
       │
       ▼
[FASE 1: Eksplorasi & Perencanaan]
  ├─ Orchestrator ──(invoke)──► [Sub-Agent 1: sigapp-explorer] (Read-Only)
  │    └─ Baca MODULE.md + MCP codegraph_explore (Callers & Blast Radius) + RCA (jika bug)
  │    └─ Kembalikan Laporan Eksplorasi & pemisahan File Inventory (Backend vs Frontend)
  ├─ Orchestrator WAJIB membuat file Artefak Plan nyata (`write_file` ke `<Artifact Directory Path>/implementation_plan.md`, atau `bugfix_plan.md` untuk mode `BUGFIX`) dengan `ArtifactMetadata` (`UserFacing: true`, `RequestFeedback: true`)
  ├─ Orchestrator memvalidasi Plan Artefak dengan checklist sigapp-module-qa/references/plan-validation-checklist.md
  └─ Orchestrator mengirim ringkasan singkat + tautan artefak (tanpa menyalin ulang isi plan di chat) untuk dikomentari & disetujui User ──► (Revisi file artefak bila ada komentar, tunggu Persetujuan User)
       │
       ▼
[FASE 2: Implementasi Bedah (Surgical Execution — Sesuai Domain)]
  ├─ Jika menyentuh Backend (Controller / Service / Repo / Model / Routes):
  │    └─ Orchestrator ──(invoke)──► [Sub-Agent 2: sigapp-engineer]
  │         ├─ Terapkan skill `codeigniter4` & `sigapp-notifikasi`
  │         ├─ Validasi finansial ulang dari DB (`cleanNumeric`) & DB Transaction jika relevan
  │         └─ Jalankan `php -l` & kembalikan kontrak endpoint/data ke Orchestrator
  │
  └─ Jika menyentuh Frontend / UI / View / CSS / JS / Modal / Laporan:
       └─ Orchestrator ──(invoke)──► [Sub-Agent 3: sigapp-frontend]
            ├─ WAJIB terapkan skill `sigapp-ui-acuan` & `references/ui-recipes.md`
            ├─ Buat/update CSS di `public/assets/css/<modul>.css` & JS di `public/assets/js/<modul>.js` (`filemtime`)
            ├─ Terapkan scoping CSS, warna `#2057a3`, `#modal_detail` baseline, `divider-left`, & `FileAccessService`
            └─ Jalankan `php -l` pada View & `git diff --check`
       │
       ▼
[FASE 3: Inspeksi QA Independen (Tanpa Edit Kode)]
  ├─ Orchestrator ──(invoke)──► [Sub-Agent 4: sigapp-qa]
  │    ├─ Wajib semua mode:
  │    │   1. Baca skill $sigapp-local-testing
  │    │   2. Audit callers via MCP codegraph_explore / grep (0 broken callers)
  │    │   3. Playwright E2E UI Test dengan akun departemen terkait (0 console error + audit checklist `sigapp-ui-acuan`)
  │    ├─ Kondisional:
  │    │   4. `php spark routes` (jika rute bertambah/berubah)
  │    │   5. `php spark module:deadcode <Modul>` (jika simbol dipindah/dihapus)
  │    │   6. `php spark module:status` (khusus REFACTOR, target >= 80% REFACTORED)
  │    └─ Kembalikan Sertifikat QA: `PASS` atau `FAIL` (beserta stack trace, RCA, & domain bug: Backend/Frontend)
       │
       ├── Jika Sertifikat QA == `FAIL`:
       │     ▼
       │   [FASE 3B: Loop Remediasi Bug Terencana]
       │     ├─ Orchestrator (bersama `sigapp-explorer` jika perlu RCA tambahan) WAJIB membuat/memperbarui file Artefak `<Artifact Directory Path>/bugfix_plan.md` (`write_file`/`edit_file` dengan `ArtifactMetadata`: `UserFacing: true`; `RequestFeedback: true` bila perubahan menyentuh scope/kontrak di luar plan awal, atau `RequestFeedback: false` untuk loop remediasi internal dalam scope yang sudah disetujui)
       │     ├─ Orchestrator mendelegasikan Surgical Fix sesuai domain bug:
       │     │   • Bug Backend (PHP/Service/Repo/Route) ──► invoke `sigapp-engineer`
       │     │   • Bug Frontend/UI (View/CSS/JS/Modal)  ──► invoke `sigapp-frontend`
       │     └─ Orchestrator ──(invoke ulang)──► [Sub-Agent 4: sigapp-qa] hingga Sertifikat QA == `PASS`
       │
       └── Jika Sertifikat QA == `PASS`:
             ▼
           [FASE 4: Perekaman Perubahan & Sinkronisasi Dokumen Modul (Wajib Semua Mode)]
             ├─ Orchestrator ──(invoke)──► [Sub-Agent 5: sigapp-docs]
             │    ├─ WAJIB: Jalankan `php spark module:sync <NamaModul>` & perbarui `docs/modules/<NamaModul>/MODULE.md`:
             │    │   • Status modul, Readiness Score, & Owner/Shared Tables
             │    │   • Public Contracts untuk diakses (Public HTTP Routes/Endpoints & Public PHP Service Contracts + Cross-Module Callers)
             │    │   • File & Method Inventory lengkap (Routes, Controller, Service, Repository, Model, View & Assets CSS/JS)
             │    │   • Fungsi-Fungsi Penting (`Key Functions & Core Business Logic`) per layer
             │    │   • Fungsi / Route / Class Shim Deprecated (`Deprecated & Legacy Shims`) beserta penggantinya (jika ada)
             │    │   • Catatan Riwayat Perubahan (`Change Log`) atas tugas yang baru diselesaikan
             │    ├─ Kondisional: Update `docs/MIGRATION_PROGRESS.md` (HANYA jika mode REFACTOR modul selesai)
             │    ├─ Kondisional: Update `docs/modul-notifikasi.md` (jika menambah/mengubah notifikasi)
             │    └─ Cek `git status --short` (pastikan terisolasi & bersih)
             └─ Master Orchestrator menerbitkan Laporan Akhir Orkestrasi ke User
```

### 3.1 Standar Wajib Pembuatan Plan dalam Bentuk Artefak (*Comment-Friendly Plan*)

Agar user dapat meninjau, menandai baris tertentu, dan memberi komentar secara terstruktur sebelum kode dieksekusi, Master Orchestrator **WAJIB** selalu menerbitkan setiap rencana kerja sebagai **File Artefak Markdown nyata** (bukan sekadar teks di chat).

#### A. Aturan Pembuatan File Artefak (Bukan Teks Chat)
- **Fase 1 (`FEATURE`, `REFACTOR`, `UI_REPORT`):** Wajib membuat `<Artifact Directory Path>/implementation_plan.md`.
- **Fase 1 (Mode `BUGFIX`) & Fase 3B (Remediasi QA `FAIL`):** Wajib membuat atau memperbarui `<Artifact Directory Path>/bugfix_plan.md`.
- **Metadata Artefak Wajib:** Gunakan `write_file` (atau `edit_file` saat merevisi) dengan `ArtifactMetadata`:
  ```json
  {
    "UserFacing": true,
    "RequestFeedback": true,
    "Summary": "Ringkasan singkat tujuan plan, modul target, dan keputusan utama yang perlu direview user."
  }
  ```
  > *Catatan Fase 3B:* Untuk loop remediasi bug internal yang sepenuhnya masih berada di dalam scope plan yang telah disetujui user, gunakan `UserFacing: true` dan `RequestFeedback: false`. Setel `RequestFeedback: true` bila perbaikan mengubah kontrak publik, menambah file/scope baru di luar plan awal, atau membutuhkan keputusan user.

#### B. Struktur Artefak Plan yang Mudah Dikomentari (*Comment-Friendly*)
Susun isi `implementation_plan.md` / `bugfix_plan.md` dengan bagian bernomor, sub-poin yang jelas, dan alert GitHub agar user mudah mengomentari bagian spesifik:
1. **Header Metadata:** Nama Modul, Mode Tugas (`FEATURE` / `BUGFIX` / `REFACTOR` / `UI_REPORT`), Status Validasi Checklist (`APPROVED` / `NEEDS_REVISION`), serta Departemen Pengujian.
2. **Tujuan & Ringkasan Permasalahan:** Latar belakang tugas (atau **Gejala, Langkah Reproduksi, & Root Cause** dengan nomor baris untuk `BUGFIX`).
3. **Asumsi & Keputusan yang Perlu Review User:** Gunakan blok `> [!IMPORTANT]` dan daftar bernomor per item keputusan/tradeoff agar user dapat memberi komentar langsung pada poin tersebut.
4. **Hasil Eksplorasi & *Blast Radius*:** Ringkasan temuan `sigapp-explorer` (`codegraph_explore` callers/callees, *Owner Tables*, dan *Shared Tables*).
5. **File Inventory Terpisah (Backend vs Frontend):** Daftar eksplisit file yang akan dibuat/diubah untuk `sigapp-engineer` dan `sigapp-frontend` dengan tautan clickable `file:///...`.
6. **Rencana Perubahan Detail & Kontrak Publik:** Rincian perubahan per layer (Routes, kontrak Full JSON `/api/*`, validasi finansial `cleanNumeric()` & transaksi DB, serta kepatuhan UI `sigapp-ui-acuan`).
7. **Langkah Eksekusi & Kriteria Verifikasi:** Urutan kerja bernomor berformat `[Step] → verify: [check]`.
8. **Strategi Verifikasi QA (`sigapp-qa`) & Sinkronisasi Docs (`sigapp-docs`):** Skenario Playwright E2E, akun departemen login, perintah CLI `php spark` (`routes`, `module:deadcode`, `module:status`), serta rencana pembaruan `MODULE.md`.
9. **Ringkasan Checklist Validasi Plan:** Status poin dari `.agents/skills/sigapp-module-qa/references/plan-validation-checklist.md`.

#### C. Presentasi ke User & Siklus Revisi dari Komentar
- Setelah artefak dibuat atau diperbarui, **DILARANG** menyalin ulang seluruh isi artefak plan ke dalam balasan chat.
- Arahkan user ke file artefak plan lewat tautan clickable, sorot **2–4 poin keputusan atau pertanyaan kunci** yang butuh masukan, lalu persilakan user memberi komentar langsung pada artefak atau menyetujuinya untuk masuk ke Fase 2.
- Jika user memberikan komentar atau revisi, perbarui **file artefak yang sama** (`edit_file` atau `write_file` dengan `Overwrite: true` + perbarui `ArtifactMetadata`), lalu minta konfirmasi ulang sebelum menugaskan `sigapp-engineer` / `sigapp-frontend`.

---

## 4. Matriks Eksekusi Adaptif per Mode Tugas & Agen Penanggung Jawab

| Tahap / Pemeriksaan | Agen Penanggung Jawab | `FEATURE` | `BUGFIX` | `REFACTOR` | `UI_REPORT` |
|---|---|:---:|:---:|:---:|:---:|
| **Fase 1: RCA / Blast Radius (`codegraph`)** | `sigapp-explorer` | ✅ Wajib | ✅ Wajib (RCA) | ✅ Wajib | ✅ Wajib (Callers/View) |
| **Fase 1: Penyusunan Plan Artefak (`.md` via `write_file`) & Validasi** | `Master Orchestrator` | ✅ Wajib (`implementation_plan.md`) | ✅ Wajib (`bugfix_plan.md`) | ✅ Wajib (`implementation_plan.md`) | ✅ Wajib (`implementation_plan.md`) |
| **Fase 2: Backend (`codeigniter4`, Shims, DB Trans)** | `sigapp-engineer` | ✅ Wajib | ⚙️ Jika bug Backend | ✅ Wajib + Shims | ⚙️ Jika butuh data baru |
| **Fase 2: Frontend & UI (`sigapp-ui-acuan`, CSS/JS)** | `sigapp-frontend` | ⚙️ Jika ada UI/View | ⚙️ Jika bug UI/View/JS | ⚙️ Jika sentuh View/JS | ✅ Wajib Mutlak |
| **Fase 3: Audit Callers via `codegraph`** | `sigapp-qa` | ✅ Wajib | ✅ Wajib | ✅ Wajib | ✅ Wajib |
| **Fase 3: Playwright E2E & UI Audit (`$sigapp-local-testing`)** | `sigapp-qa` | ✅ Wajib | ✅ Wajib | ✅ Wajib | ✅ Wajib |
| **Fase 3: `php spark routes` & `module:deadcode`** | `sigapp-qa` | ⚙️ Jika sentuh rute | ⚙️ Jika terkait rute | ✅ Wajib | ❌ Opsional |
| **Fase 3: `php spark module:status` ($\ge 80\%$)** | `sigapp-qa` | ⚙️ Jika modul `REFACTORED` | ❌ Opsional | ✅ Wajib | ❌ Opsional |
| **Fase 3B: Surgical Bug Remediation** | `sigapp-engineer` / `sigapp-frontend` | ⚙️ Sesuai domain bug | ⚙️ Sesuai domain bug | ⚙️ Sesuai domain bug | ⚙️ `sigapp-frontend` |
| **Fase 4: Update & Record `docs/modules/<Modul>/MODULE.md` (`module:sync` + Kontrak, Layer, Key/Deprecated Func, Change Log)** | `sigapp-docs` | ✅ Wajib Mutlak | ✅ Wajib Mutlak | ✅ Wajib Mutlak | ✅ Wajib Mutlak |
| **Fase 4: Update `docs/MIGRATION_PROGRESS.md`** | `sigapp-docs` | ❌ Tidak perlu | ❌ Tidak perlu | ✅ Wajib | ❌ Tidak perlu |
| **Fase 4: Update `docs/modul-notifikasi.md`** | `sigapp-docs` | ⚙️ Jika sentuh notif | ⚙️ Jika sentuh notif | ⚙️ Jika sentuh notif | ❌ Tidak perlu |

*Keterangan: ✅ Wajib Mutlak | ⚙️ Kondisional Berdasarkan Elemen yang Disentuh | ❌ Tidak Perlu*

---

## 5. Standar Wajib Perekaman Dokumentasi Modul (`docs/modules/<Modul>/MODULE.md`) oleh `sigapp-docs`

Setiap kali tugas selesai diverifikasi (`PASS` oleh `sigapp-qa`), `sigapp-docs` **WAJIB** menyinkronkan dan merekam perubahan pada `docs/modules/<Modul>/MODULE.md`. Dokumen ini berfungsi sebagai *Single Source of Truth* bagi agen maupun developer dan wajib memuat:

1. **Status Modul & Tabel Database:**
   - Status kesiapan (`REFACTORED` / `MAPPED`), `Readiness Score`, timestamp & commit sinkronisasi terakhir, `Owner Table(s)` (akses tulis eksklusif), dan `Shared Tables` (hanya baca).
2. **Contract Public untuk Diakses (`Contracts`):**
   - **A. Public HTTP Endpoints & Routes Inventory:** Daftar lengkap rute eksplisit maupun AutoRoute di `app/Config/Routes.php` (HTTP Method, URI/Pattern, Handler Controller, Status Migrasi).
   - **B. Public PHP Services (Backend Contract Antar-Modul):** Daftar class Service dan signature method `public` yang resmi menjadi kontrak untuk diakses oleh modul lain atau controller, beserta registri pemanggil lintas modul (*Cross-Module Callers*).
3. **Inventori Arsitektur Lengkap per Layer (`File Inventory`):**
   - **Controllers:** File Web Controller (`app/Controllers/Web/...`), API Controller (`app/Controllers/Api/...`), atau Legacy Controller beserta daftar public method-nya.
   - **Services:** File Service (`app/Services/...`) beserta jumlah baris dan method utamanya.
   - **Repositories:** File Repository (`app/Repositories/...`) beserta daftar public method query/data access-nya.
   - **Models:** File Model (`app/Models/...`) yang digunakan modul.
   - **Views & Frontend Assets:** Daftar file View (`app/Views/...`), file CSS modul (`public/assets/css/...`), dan file JS modul (`public/assets/js/...`).
4. **Fungsi-Fungsi Penting (`Key Functions & Core Business Logic`):**
   - Penjelasan ringkas fungsi/method krusial di tiap layer (Controller, Service, Repository, Model) yang memegang aturan bisnis inti, kalkulasi finansial (`cleanNumeric()`), mutasi status, atau transaksi DB.
5. **Fungsi / Endpoint / Shim Deprecated (`Deprecated & Legacy Shims`):**
   - Daftar fungsi/method, route lama, atau file *Zero-Downtime Shim* yang sudah berstatus **DEPRECATED** (dipertahankan hanya untuk backward-compatibility), dilengkapi petunjuk kontrak/fungsi baru penggantinya.
6. **Riwayat Perubahan Modul (`Change Log`):**
   - Rekaman kronologis setiap selesai tugas (`[YYYY-MM-DD] [FEATURE|BUGFIX|REFACTOR|UI_REPORT]`), mencakup ringkasan perubahan pada routes, controller, service, repo, model, view/assets, serta penambahan/deprekasi fungsi atau kontrak publik.

---

## 6. Kredensial Pengujian Departemen untuk Agen `sigapp-qa`

Orkestrator wajib menyertakan kredensial departemen yang sesuai saat memanggil `sigapp-qa`:

| Departemen | Modul Terkait | Username | Password |
|---|---|---|---|
| **Keuangan** | Keuangan, Pajak, CashOut, PencairanJaminan | `nisalis` | `nisalis123` |
| **MKDT** | MKDT, Konsumen, Transaksi | `citra` | `citra123` |
| **Planning** | Proyek, Cluster, Jalan, Tipe, Kavling, Siteplan | `purnama` | `purnama123` |
| **Legal** | Legal, HGB, PBB, PBG, AJB | `marlina` | `marlina123` |
| **Promosi** | MGM, Promosi, Event | `benny` | `benny123` |
| **Produksi** | Produksi, BPB, SPK, Gudang, TiketMasalah | `alika` | `alikas123` |

> **PERINGATAN PENGUJIAN:** Sebelum membuka browser lokal (`http://localhost/sigapp.dev`), `sigapp-qa` **WAJIB** membaca dan mengaktifkan skill `.agents/skills/sigapp-local-testing/SKILL.md`.

---

## 7. Format Laporan Akhir Orkestrator ke Pengguna

Setelah seluruh fase (1 s/d 4) selesai dan terverifikasi, Master Orchestrator memberikan laporan penutupan terstruktur:

```markdown
# 🎯 Laporan Orkestrasi Tugas: [Judul Tugas]

## 1. Ringkasan Eksekusi & Pembagian Peran
- **Modul:** `[NamaModul]` | **Mode Tugas:** `[FEATURE | BUGFIX | REFACTOR | UI_REPORT]`
- **Status Akhir:** `SUCCESS / COMPLETED`
- **Rantai Agen yang Bertugas:**
  - 🔍 **Explorer (`sigapp-explorer`):** Pemetaan codegraph callers & blast radius selesai
  - ⚙️ **Backend Engineer (`sigapp-engineer`):** [Implementasi backend selesai (`php -l` bersih) / N/A]
  - 🎨 **Frontend Specialist (`sigapp-frontend`):** [Implementasi View/CSS/JS sesuai `sigapp-ui-acuan` selesai / N/A]
  - 🛡️ **QA Inspector (`sigapp-qa`):** Sertifikat QA `PASS` (Playwright E2E, UI Check, & Codegraph bersih)
  - 📚 **Docs Custodian (`sigapp-docs`):** Perekaman perubahan & status modul di `MODULE.md` serta git status terverifikasi

## 2. Hasil Inspeksi QA Independen (`sigapp-qa`)
- [x] Audit Pemanggil & Integritas Simbol via Codegraph: Lulus (0 broken callers)
- [x] Playwright E2E UI Test (`http://localhost/sigapp.dev`): Lulus (Departemen: [NamaDepartemen], 0 console errors)
- [x/N/A] Kepatuhan Standar UI (`sigapp-ui-acuan`): [Lulus - CSS eksternal `filemtime`, scoping, `#2057a3` / N/A]
- [x/N/A] Integritas Rute (`php spark routes`): [OK / N/A]
- [x/N/A] Audit Kesiapan Modular (`php spark module:status`): [Skor: X% / N/A]

## 3. Penanganan Bug & Stabilitas (Fase 3B)
- [Tidak ada bug ditemukan / X bug telah diperbaiki secara surgical oleh `sigapp-engineer` / `sigapp-frontend`]

## 4. Perekaman Perubahan & Sinkronisasi Dokumentasi (`sigapp-docs`)
- [x] `docs/modules/[NamaModul]/MODULE.md`: Diperbarui (Status modul, Routes, Controller, Service, Repo, Model, View/CSS/JS, Public Contracts, Fungsi Penting, Fungsi Deprecated, & Change Log)
- [x/N/A] `docs/MIGRATION_PROGRESS.md`: [Diperbarui / N/A - Bukan Refaktor Modul]
- [x/N/A] `docs/modul-notifikasi.md`: [Diperbarui / N/A]
```
