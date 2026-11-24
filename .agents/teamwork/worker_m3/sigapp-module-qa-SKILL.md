---
name: sigapp-module-qa
description: >
  Sistem penjamin mutu independen, validasi plan, verifikasi pasca-implementasi (`sigapp-qa`),
  protokol RCA & remediasi bug (`sigapp-explorer` + `sigapp-engineer` / `sigapp-frontend`), serta sinkronisasi dokumentasi adaptif (`sigapp-docs`)
  untuk seluruh alur pengembangan CodeIgniter 4 di SIGAPP (FEATURE, BUGFIX, REFACTOR, UI_REPORT).
  Gunakan skill ini ketika: (1) mereview/memvalidasi implementation plan atau bugfix plan agar sesuai best practice,
  (2) menjalankan audit independen pasca-implementasi via `sigapp-qa` (Playwright E2E, `sigapp-ui-acuan` UI audit, Codegraph callers, spark verification),
  (3) menyusun RCA & bugfix plan berbasis codegraph saat QA menemukan error,
  (4) melakukan sinkronisasi docs/modules/<Modul>/MODULE.md dan docs/MIGRATION_PROGRESS.md via `sigapp-docs`.
  Triggers: "selesai tugas X", "cek modul X", "verifikasi tugas X", "audit modul X", "review plan",
  "apakah plan sudah sesuai", "ada bug di modul X", "fix error modul X", "update docs modul X".
---

# SIGAPP Quality Assurance, Plan Validation & Remediation Playbook

Skill ini mendefinisikan standar penjaminan mutu (*Quality Gates*) dalam arsitektur multi-agen SIGAPP, dengan pemisahan wewenang yang tegas antara **Auditor (`sigapp-qa`)**, **Eksekutor Backend (`sigapp-engineer`)**, **Spesialis Frontend/UI (`sigapp-frontend`)**, dan **Penjaga Dokumen (`sigapp-docs`)**.

---

## Ringkasan Alur Kerja & Pemisahan Wewenang (Check-and-Balance Cycle)

```
[GERBANG 1: Validasi Plan] (Oleh Master Orchestrator berdasarkan laporan `sigapp-explorer`)
         │
         ▼
[Eksekusi Kode Bedah] (Oleh `sigapp-engineer` [Backend] & `sigapp-frontend` [Frontend/UI] — Fase 2)
         │
         ▼
[GERBANG 2: Inspeksi QA Independen] (Oleh `sigapp-qa` — Fase 3: DILARANG EDIT KODE)
         │
         ├──────────────────────────┴──────────────────────────┐
   (Sertifikat: FAIL)                                   (Sertifikat: PASS)
         │                                                     │
         ▼                                                     ▼
[GERBANG 3: Remediasi Bug Terencana (Fase 3B)]      [GERBANG 4: Sinkronisasi Docs Adaptif (Fase 4)]
  1. RCA dari laporan `sigapp-qa` / `sigapp-explorer`   Dikerjakan oleh `sigapp-docs`:
  2. Orchestrator susun Bugfix Plan Artifact            - `php spark module:sync <Modul>`
  3. Fix oleh `sigapp-engineer` / `sigapp-frontend`     - Update `MIGRATION_PROGRESS.md` (khusus REFACTOR)
  4. Loop kembali ke GERBANG 2 (`sigapp-qa`)            - Update `modul-notifikasi.md` (jika relevan)
```

---

## Panduan Langkah Demi Langkah per Gerbang

### GERBANG 1 — Validasi Rencana (Pre-Execution Plan Gatekeeper)
*Dijalankan oleh **Master Orchestrator** sebelum memberi tugas ke `sigapp-engineer`.*

1. Bandingkan draf plan dengan checklist di [references/plan-validation-checklist.md](references/plan-validation-checklist.md).
2. Pastikan plan memenuhi kriteria sesuai mode tugas:
   - **Prinsip Karpathy (Wajib Semua Mode)**: Tanpa kode spekulatif, diff minimal, kriteria sukses terukur (`[Step] → verify: [check]`).
   - **Pola CI4 Layering (Wajib Fitur Baru & Refaktor)**: Thin Web/Api Controller, Rich Service, Dedicated Repository (`codeigniter4`).
   - **Standar UI (Wajib Jika Sentuh View/Modal)**: Patuhi `sigapp-ui-acuan` (`#2057a3`, `#modal_detail` baseline, `divider divider-left`, `FileAccessService`).
   - **Keamanan Finansial & Transaksi DB**: Backend selalu menghitung ulang angka dari DB via `cleanNumeric()`; mutasi multi-tabel wajib dibungkus `$db->transStart()` & `$db->transComplete()`.
   - **Zero-Downtime Shims (Wajib Refaktor/Ubah Route)**: Route/service lama tetap aktif sebagai delegator/shim.
   - **Strategi Pengujian (`sigapp-qa`)**: Mencantumkan skenario Playwright E2E, departemen login, dan aktivasi `.agents/skills/sigapp-local-testing/SKILL.md`.

---

### GERBANG 2 — Inspeksi QA Independen (`sigapp-qa` — Fase 3)
*Dijalankan oleh subagent **`sigapp-qa`**. Sebagai auditor independen, `sigapp-qa` **DILARANG KERAS** mengubah file kode aplikasi/dokumentasi.*

Ikuti panduan lengkap di [references/post-module-verification.md](references/post-module-verification.md):

1. **Audit Callers & Integritas Simbol (`codegraph`) — [Wajib Semua Mode]:**
   Gunakan MCP `codegraph_explore` dan grep untuk memastikan seluruh pemanggil (*callers*) di JavaScript, View, maupun Service lain sinkron dengan simbol/endpoint yang baru diubah (target: **0 broken callers**).
2. **Pengujian Antarmuka Playwright E2E — [Wajib Semua Mode]:**
   - **WAJIB:** Baca dan aktifkan `.agents/skills/sigapp-local-testing/SKILL.md`.
   - Buka `http://localhost/sigapp.dev` via Playwright MCP dan login sesuai akun departemen:
     - **Keuangan**: `nisalis` / `nisalis123`
     - **MKDT**: `citra` / `citra123`
     - **Planning**: `purnama` / `purnama123`
     - **Legal**: `marlina` / `marlina123`
     - **Promosi**: `benny` / `benny123`
     - **Produksi**: `alika` / `alikas123`
   - *Target: 0 console errors (500/404), halaman render sempurna, skenario interaksi utama berhasil.*
3. **Pemeriksaan CLI Kondisional:**
   - **Integritas Rute**: `php spark routes` (jika ada penambahan/perubahan rute).
   - **Audit Dead Code**: `php spark module:deadcode <NamaModul>` (jika ada metode dipindah/dihapus).
   - **Skor Kesiapan Modular**: `php spark module:status` (khusus mode `REFACTOR`, target `REFACTORED` $\ge 80\%$).
4. **Keluarkan Sertifikat Verifikasi QA:**
   - Status **`PASS`** jika seluruh pemeriksaan lulus 100%.
   - Status **`FAIL`** jika ditemukan error/regresi, dilengkapi stack trace, console log, dan Root Cause Analysis (RCA) awal.

---

### GERBANG 3 — Remediasi Bug Terencana (Fase 3B — Saat Sertifikat QA `FAIL`)
*Dikoordinasikan oleh **Master Orchestrator**, dieksekusi oleh **`sigapp-engineer`** (Backend) atau **`sigapp-frontend`** (Frontend/UI), dan diuji ulang oleh **`sigapp-qa`**.*

Ikuti [references/bugfix-playbook.md](references/bugfix-playbook.md):
1. **RCA Berbasis Codegraph:** Gunakan temuan dari `sigapp-qa` (dan panggil `sigapp-explorer` jika butuh penelusuran tambahan) dengan MCP `codegraph_explore` + grep untuk memetakan akar masalah dan *blast radius*.
2. **Bugfix Plan Artifact:** Master Orchestrator menyusun rencana perbaikan terstruktur (Gejala, Root Cause, Blast Radius, Surgical Diff, Kriteria Verifikasi).
3. **Surgical Fix Sesuai Domain:**
   - **Bug Backend** (PHP error, Service/Repo/Controller/Routes): dieksekusi oleh **`sigapp-engineer`**.
   - **Bug Frontend/UI** (View, CSS, JS, Modal, ketidaksesuaian `sigapp-ui-acuan`): dieksekusi oleh **`sigapp-frontend`**.
4. **Verifikasi Ulang oleh `sigapp-qa`:** Panggil kembali `sigapp-qa` untuk menjalankan Gerbang 2 dari awal hingga menerbitkan sertifikat **`PASS`**.

---

### GERBANG 4 — Perekaman Perubahan & Sinkronisasi Dokumentasi Modul (`sigapp-docs` — Fase 4)
*Dijalankan oleh subagent **`sigapp-docs`** HANYA setelah `sigapp-qa` menerbitkan sertifikat **`PASS`**.*

Ikuti [references/docs-sync-guide.md](references/docs-sync-guide.md):
1. **Sinkronisasi & Perekaman `docs/modules/<Modul>/MODULE.md` [WAJIB SEMUA MODE]:**
   - Pastikan file baru terdaftar di `app/Libraries/ModuleManager/ModuleCatalog.php` (jika ada) dan jalankan `php spark module:sync <NamaModul>`.
   - Perbarui dan pastikan `MODULE.md` memuat status modul terkini secara menyeluruh:
     - **Routes & Public Contracts:** Inventori HTTP Endpoints (`Routes.php`), Public PHP Service Methods, serta registri pemanggil lintas modul (*Cross-Module Callers*).
     - **File & Method Inventory per Layer:** Controller (`Web`/`Api`/Legacy), Service, Repository, Model, serta View & Frontend Assets (`CSS`/`JS`).
     - **Key Functions (Fungsi-Fungsi Penting):** Penjelasan method/fungsi inti per layer (validasi finansial `cleanNumeric()`, alur status, transaksi DB).
     - **Deprecated & Legacy Shims:** Daftar fungsi/endpoint/shim yang sudah deprecated (jika ada) beserta acuan fungsi/endpoint baru penggantinya.
     - **Change Log:** Catatan riwayat perubahan terbaru atas tugas yang baru diselesaikan.
2. **Update Global Ledger (`docs/MIGRATION_PROGRESS.md`) [Khusus Mode `REFACTOR`]:** Ubah status modul menjadi `COMPLETED` beserta commit/sesi dan tanggal verifikasi. *(Dilarang diubah untuk mode `FEATURE`, `BUGFIX`, atau `UI_REPORT`).*
3. **Update `docs/modul-notifikasi.md` [Kondisional]:** Wajib diperbarui jika ada perubahan notifikasi.
4. **Cek Isolasi Git:** Jalankan `git status --short` untuk memastikan hanya file target tugas dan dokumen relevan yang berubah.

---

## File Referensi Terkait
- [Definisi & Prompt 5 Sub-Agent Spesialis](../sigapp-orchestrator/references/subagent-definitions.md)
- [Checklist Validasi Plan](references/plan-validation-checklist.md)
- [Panduan Verifikasi Pasca-Tugas (`sigapp-qa`)](references/post-module-verification.md)
- [Playbook Penanganan Bug (`sigapp-explorer` + `sigapp-engineer`)](references/bugfix-playbook.md)
- [Panduan Sinkronisasi Dokumentasi Adaptif (`sigapp-docs`)](references/docs-sync-guide.md)
