---
name: sigapp-module-qa
description: >
  Sistem penjamin mutu, validasi plan, verifikasi pasca-implementasi, sinkronisasi dokumentasi adaptif,
  dan remediasi bug terencana untuk seluruh alur pengembangan CodeIgniter 4 di SIGAPP (Fitur, Bugfix, Refaktor, UI/Laporan).
  Gunakan skill ini ketika: (1) mereview/memvalidasi implementation plan atau bugfix plan agar sesuai best practice,
  (2) melakukan audit pasca-implementasi (Playwright E2E, Codegraph callers, spark verification),
  (3) memastikan sinkronisasi docs/modules/<Modul>/MODULE.md dan docs/MIGRATION_PROGRESS.md secara adaptif,
  (4) menemukan bug/error runtime dan perlu menyusun RCA serta bugfix plan berbasis codegraph,
  (5) mengeksekusi surgical fix dan memverifikasi hingga tuntas.
  Triggers: "selesai tugas X", "cek modul X", "verifikasi tugas X", "audit modul X", "review plan",
  "apakah plan sudah sesuai", "ada bug di modul X", "fix error modul X", "update docs modul X".
---

# SIGAPP Quality Assurance, Plan Validation & Remediation Playbook

Skill ini memandu agen AI untuk menjalankan proses penjaminan mutu menyeluruh dalam **satu alur tertutup adaptif (Adaptive Closed-Loop)**: dari validasi rencana awal, audit pasca pengerjaan tugas, pelacakan akar masalah dengan `codegraph`, eksekusi perbaikan bedah (*surgical fix*), hingga sinkronisasi dokumen ledger yang relevan.

---

## Ringkasan Alur Kerja Terpadu (The Closed-Loop Cycle)

```
[Tahap 1: Validasi Plan] ──(Sesuai Best Practice?)──► [Eksekusi Pengerjaan Tugas]
                                                                │
                                                                ▼
                                                    [Tahap 2: Verifikasi Pasca-Tugas (Adaptif)]
                                                                │
                                        ┌───────────────────────┴───────────────────────┐
                                  (Ada Error/Bug?)                                (Semua Hijau)
                                        │                                               │
                                        ▼                                               ▼
                         [Tahap 3: Susun Bugfix Plan]                     [Tahap 5: Sinkronisasi Docs Adaptif]
                         (RCA via Codegraph & Grep)                       (module:sync & Ledger Update)
                                        │                                               │
                                        ▼                                               ▼
                         [Tahap 4: Surgical Bug Fix]                               [Selesai & Commit]
                                        │
                                        └──────────► (Ulangi Tahap 2)
```

---

## Panduan Langkah Demi Langkah

### TAHAP 1 — Validasi Rencana (Pre-Execution Plan Gatekeeper)
*Gunakan tahap ini saat membuat atau meninjau implementation plan sebelum kode disentuh.*

1. Buka dan bandingkan draf plan dengan checklist di [references/plan-validation-checklist.md](references/plan-validation-checklist.md).
2. Periksa apakah plan mematuhi kriteria yang sesuai dengan mode tugas:
   - **Prinsip Karpathy (Wajib Semua Mode)**: Tidak ada kode spekulatif, perubahan seminimal mungkin, kriteria sukses terukur.
   - **Pola CI4 Layering (Wajib Fitur Baru & Refaktor)**: Thin Web/Api Controller, Rich Service, Dedicated Repository.
   - **Keamanan Finansial (Wajib Finansial/Mutasi DB)**: Backend selalu menghitung ulang tagihan/cicilan dari DB via `cleanNumeric()`; lindungi dengan transaksi `$db->transStart()`.
   - **Zero-Downtime Shims (Wajib Refaktor/Perubahan Route)**: Route lama tetap aktif sebagai delegator/shim.
   - **Testing Plan (Wajib Semua Mode)**: Mencantumkan pengujian Playwright E2E, role departemen terkait, dan pemanggilan `$sigapp-local-testing`.
3. Jika ada poin yang belum terpenuhi, perbaiki dokumen plan terlebih dahulu sebelum meminta konfirmasi user.

---

### TAHAP 2 — Verifikasi Pasca-Pekerjaan (Post-Execution Verification)
*Gunakan tahap ini segera setelah pengerjaan fitur, bugfix, UI, atau refaktor modul selesai.*

Ikuti panduan lengkap di [references/post-module-verification.md](references/post-module-verification.md) untuk mengeksekusi verifikasi kualitas adaptif:

1. **Audit Callers & Integritas Simbol (`codegraph`) — [Wajib Mutlak Semua Mode]:**
   Gunakan MCP `codegraph_explore` atau grep untuk memastikan seluruh pemanggil (*callers*) di JavaScript, View, maupun Service lain sesuai dengan definisi metode/endpoint baru.
2. **Pengujian Antarmuka Playwright E2E (Wajib Mutlak Semua Mode):**
   - **WAJIB:** Buka dan aktifkan skill `$sigapp-local-testing`.
   - Buka `http://localhost/sigapp.dev` via Playwright MCP dan login menggunakan akun departemen terkait:
     - **Keuangan**: `nisalis` / `nisalis123`
     - **MKDT**: `citra` / `citra123`
     - **Produksi**: `alika` / `alikas123`
     - **Legal**: `marlina` / `marlina123`
     - **Promosi**: `benny` / `benny123`
   - *Target: 0 console errors (500/404), halaman render sempurna, aksi utama berhasil.*
3. **Pemeriksaan Kondisional Sesuai Elemen yang Disentuh:**
   - **Integritas Rute**: Jalankan `php spark routes` jika ada penambahan/perubahan rute.
   - **Audit Dead Code**: Jalankan `php spark module:deadcode <NamaModul>` jika ada metode yang dipindah atau dihapus.
   - **Skor Kesiapan Modular**: Jalankan `php spark module:status` khusus jika menyelesaikan refaktor modul (target status `REFACTORED`, skor $\ge 80\%$).
   - **Isolasi Batas Modul**: Pastikan tidak ada query langsung yang memanipulasi tabel pemilik modul lain.

---

### TAHAP 3 — Penyusunan Rencana Fix Bug (Bug Remediation Planning)
*Gunakan tahap ini JIKA Tahap 2 menemukan error, broken route, atau kegagalan UI.*

1. **Hentikan Segala Perubahan Kode.** Jangan melakukan perbaikan tanpa diagnosis terstruktur.
2. Ikuti [references/bugfix-playbook.md](references/bugfix-playbook.md):
   - Ambil stack trace atau pesan error console secara utuh.
   - Panggil MCP tool `codegraph_explore` untuk memeriksa dependensi dan pemanggil (*callers*):
     ```json
     {
       "ServerName": "codegraph",
       "ToolName": "codegraph_explore",
       "Arguments": {
         "projectPath": "c:\\Users\\Salivs\\Data\\laragon\\www\\sigapp.dev",
         "query": "SimbolYangError"
       }
     }
     ```
   - Gunakan grep untuk memvalidasi teks secara lengkap.
3. Buat draf **Bugfix Plan Artifact**:
   - Gejala & Reproduksi
   - Akar Masalah (*Root Cause*)
   - *Blast Radius* (dampak perubahan ke fungsi lain)
   - Rencana Perbaikan Bedah (*Surgical Diff*)
   - Kriteria Verifikasi Ulang

---

### TAHAP 4 — Eksekusi Perbaikan Bug (Surgical Bug Fixing)
*Gunakan tahap ini setelah Bugfix Plan tersusun.*

1. Ubah **hanya baris yang bermasalah**. Jangan merombak kode di sekitarnya.
2. Ikuti konvensi penamaan dan arsitektur yang sudah ada di modul tersebut.
3. Bersihkan sisa variabel atau helper yang tidak terpakai akibat perubahan.
4. **Looping Verifikasi**: Uji kembali skenario yang tadinya gagal, lalu ulangi **TAHAP 2** secara utuh hingga seluruh indikator hijau.

---

### TAHAP 5 — Sinkronisasi Dokumentasi & Ledger Adaptif
*Gunakan tahap ini setelah Tahap 2 lolos 100% tanpa error.*

Ikuti petunjuk di [references/docs-sync-guide.md](references/docs-sync-guide.md):

1. **Perbarui Dokumen Modul (`MODULE.md`) [Kondisional]:**
   ```bash
   php spark module:sync <NamaModul>
   ```
   *Jalankan jika ada penambahan/perubahan file controller, service, repo, rute, view, JS, atau tabel.*
2. **Perbarui Global Migration Ledger (`MIGRATION_PROGRESS.md`) [Khusus Refaktor Modul]:**
   Jika tugas adalah penyelesaian migrasi modul (`REFACTOR`), buka [docs/MIGRATION_PROGRESS.md](file:///c:/Users/Salivs/Data/laragon/www/sigapp.dev/docs/MIGRATION_PROGRESS.md):
   - Ubah status modul menjadi `COMPLETED`.
   - Catat commit hash atau penanda sesi.
   - Perbarui tanggal verifikasi terakhir dan ringkasan global.
   *(Jika tugas berupa penambahan fitur biasa atau bugfix, file ini TIDAK perlu diubah).*
3. **Dokumen Notifikasi [Kondisional]:** Jika ada perubahan notifikasi, perbarui [docs/modul-notifikasi.md](file:///c:/Users/Salivs/Data/laragon/www/sigapp.dev/docs/modul-notifikasi.md).
4. **Periksa Status Git**: Pastikan perubahan siap di-commit secara mandiri dan terisolasi.

---

## Delegasi ke Subagent (Background QA Runner)

Jika proses verifikasi didelegasikan ke latar belakang melalui `invoke_subagent`:

```json
{
  "Subagents": [
    {
      "TypeName": "self",
      "Role": "Module QA Specialist",
      "Model": "inherit",
      "Prompt": "Jalankan protokol verifikasi sigapp-module-qa untuk tugas di modul [NamaModul] (Mode: [FEATURE/BUGFIX/REFACTOR/UI_REPORT]). Ikuti langkah di .agents/skills/sigapp-module-qa/SKILL.md: buka $sigapp-local-testing, uji UI via Playwright role terkait (0 console errors), cek callers via codegraph, dan jalankan pengecekan spark yang relevan. Jika ada bug, susun bugfix plan dan perbaiki. Lakukan sync docs yang relevan jika seluruh tes hijau."
    }
  ]
}
```

---

## File Referensi Terkait
- [Checklist Validasi Plan](references/plan-validation-checklist.md)
- [Panduan Verifikasi Pasca-Tugas](references/post-module-verification.md)
- [Playbook Penanganan Bug](references/bugfix-playbook.md)
- [Panduan Sinkronisasi Dokumentasi Adaptif](references/docs-sync-guide.md)
- [Aturan Utama QA Modul](file:///c:/Users/Salivs/Data/laragon/www/sigapp.dev/.agents/rules/sigapp-module-qa.md)
