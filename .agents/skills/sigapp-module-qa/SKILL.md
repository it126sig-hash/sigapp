---
name: sigapp-module-qa
description: >
  Sistem penjamin mutu, validasi plan, verifikasi pasca-pengerjaan modul, sinkronisasi dokumentasi,
  dan remediasi bug terencana untuk arsitektur CodeIgniter 4 SIGAPP.
  Gunakan skill ini ketika: (1) mereview/memvalidasi implementation plan agar sesuai best practice CI4 dan Karpathy,
  (2) melakukan pengecekan/audit kembali setelah selesai mengerjakan atau merefaktor suatu modul,
  (3) memastikan sinkronisasi docs/modules/<Modul>/MODULE.md dan docs/MIGRATION_PROGRESS.md,
  (4) menemukan bug/error dan perlu menyusun bugfix plan berbasis codegraph,
  (5) mengeksekusi fix bug secara bedah (surgical) dan memverifikasi hingga tuntas.
  Triggers: "selesai modul X", "cek modul X", "verifikasi modul X", "audit modul X", "review plan",
  "apakah plan sudah sesuai", "ada bug di modul X", "fix error modul X", "update docs modul X".
---

# SIGAPP Module QA, Plan Validation & Remediation Playbook

Skill ini memandu agen AI untuk menjalankan proses penjaminan mutu menyeluruh dalam **satu alur tertutup (Unified Closed-Loop)**: dari validasi rencana awal, audit pasca pengerjaan modul, pelacakan akar masalah dengan `codegraph`, eksekusi perbaikan bedah (*surgical fix*), hingga sinkronisasi dokumen ledger.

---

## Ringkasan Alur Kerja Terpadu (The Closed-Loop Cycle)

```
[Tahap 1: Validasi Plan] ──(Sesuai Best Practice?)──► [Eksekusi Pengerjaan Modul]
                                                                │
                                                                ▼
                                                    [Tahap 2: Verifikasi Pasca-Modul]
                                                                │
                                        ┌───────────────────────┴───────────────────────┐
                                  (Ada Error/Bug?)                                (Semua Hijau)
                                        │                                               │
                                        ▼                                               ▼
                         [Tahap 3: Susun Bugfix Plan]                     [Tahap 5: Sinkronisasi Docs]
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
2. Periksa apakah plan mematuhi:
   - **Prinsip Karpathy**: Tidak ada kode spekulatif, perubahan seminimal mungkin, kriteria sukses terukur.
   - **CI4 Layering**: Thin Web/Api Controller, Rich Service, Dedicated Repository.
   - **Financial Safety**: Backend selalu menghitung ulang tagihan/cicilan dari DB; jangan percaya frontend.
   - **Zero-Downtime**: Route lama tetap aktif sebagai delegator/shim.
   - **Testing Plan**: Mencantumkan pengujian Playwright dan role departemen terkait.
3. Jika ada poin yang belum terpenuhi, perbaiki dokumen plan terlebih dahulu sebelum meminta konfirmasi user.

---

### TAHAP 2 — Pengecekan Kembali Pasca Pengerjaan Modul (Post-Module Verification)
*Gunakan tahap ini segera setelah pengerjaan atau refaktor modul selesai.*

Ikuti panduan lengkap di [references/post-module-verification.md](references/post-module-verification.md) untuk mengeksekusi 5 pilar verifikasi:

1. **Status & Skor Kesiapan**:
   ```bash
   php spark module:status
   ```
   *Target: Status `REFACTORED`, skor kesiapan $\ge 80\%$.*
2. **Integritas Rute & Shims**:
   Periksa apakah rute lama dan baru merespons dengan benar tanpa Controller Not Found.
3. **Audit Dead Code & Callers**:
   Jalankan deteksi dead code:
   ```bash
   php spark module:deadcode <NamaModul>
   ```
   Gunakan MCP `codegraph_explore` untuk memastikan tidak ada simbol putus.
4. **Isolasi Batas Modul**:
   Pastikan tidak ada query langsung yang memanipulasi tabel milik modul lain.
5. **Pengujian Antarmuka Playwright (Wajib)**:
   Buka `http://localhost/sigapp.dev` via Playwright MCP dan login menggunakan akun departemen terkait:
   - **Keuangan**: `nisalis` / `nisalis123`
   - **MKDT**: `citra` / `citra123`
   - **Produksi**: `alika` / `alikas123`
   - **Legal**: `marlina` / `marlina123`
   - **Promosi**: `benny` / `benny123`
   *Target: 0 console errors (500/404), halaman render sempurna, aksi utama berhasil.*

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
4. **Looping Verifikasi**: Uji kembali skenario yang tadinya gagal, lalu ulangi **TAHAP 2** secara utuh hingga kelima pilar berstatus hijau.

---

### TAHAP 5 — Sinkronisasi Dokumentasi & Ledger
*Gunakan tahap ini setelah Tahap 2 lolos 100% tanpa error.*

Ikuti petunjuk di [references/docs-sync-guide.md](references/docs-sync-guide.md):

1. **Perbarui Dokumen Modul**:
   ```bash
   php spark module:sync <NamaModul>
   ```
   Pastikan file [docs/modules/<NamaModul>/MODULE.md](file:///c:/Users/Salivs/Data/laragon/www/sigapp.dev/docs/modules/Proyek/MODULE.md) ter-update dengan timestamp dan daftar rute terbaru.
2. **Perbarui Global Ledger**:
   Buka [docs/MIGRATION_PROGRESS.md](file:///c:/Users/Salivs/Data/laragon/www/sigapp.dev/docs/MIGRATION_PROGRESS.md):
   - Ubah status modul menjadi `COMPLETED`.
   - Catat commit hash atau penanda sesi.
   - Perbarui tanggal verifikasi terakhir dan ringkasan global.
3. **Dokumen Notifikasi**: Jika ada perubahan notifikasi, perbarui [docs/modul-notifikasi.md](file:///c:/Users/Salivs/Data/laragon/www/sigapp.dev/docs/modul-notifikasi.md).
4. **Periksa Status Git**: Pastikan perubahan siap di-commit secara mandiri dan terisolasi.

---

## Delegasi ke Subagent (Background QA Runner)

Jika pengguna menginginkan proses audit ini dikerjakan di latar belakang sementara sesi utama mengerjakan hal lain, Anda dapat meluncurkan subagent menggunakan tool `invoke_subagent`:

```json
{
  "Subagents": [
    {
      "TypeName": "self",
      "Role": "Module QA Specialist",
      "Model": "inherit",
      "Prompt": "Jalankan protokol verifikasi sigapp-module-qa untuk modul [NamaModul]. Ikuti langkah di .agents/skills/sigapp-module-qa/SKILL.md: jalankan module:status, periksa rute, cek deadcode via codegraph, dan uji UI dengan Playwright role terkait. Jika ada bug, susun bugfix plan dan perbaiki. Terakhir, jalankan module:sync dan update docs/MIGRATION_PROGRESS.md. Berikan laporan sertifikat verifikasi akhir."
    }
  ]
}
```

---

## File Referensi Terkait
- [Checklist Validasi Plan](references/plan-validation-checklist.md)
- [Panduan Verifikasi Pasca-Modul](references/post-module-verification.md)
- [Playbook Penanganan Bug](references/bugfix-playbook.md)
- [Panduan Sinkronisasi Dokumentasi](references/docs-sync-guide.md)
- [Aturan Utama Modul QA](file:///c:/Users/Salivs/Data/laragon/www/sigapp.dev/.agents/rules/sigapp-module-qa.md)
