---
name: sigapp-orchestrator
description: >
  Master Orchestrator untuk mengoordinasikan perencanaan, implementasi, pendelegasian subagent inspeksi QA,
  penanganan bug terencana, dan sinkronisasi dokumen pada modul SIGAPP.
  Gunakan skill ini ketika user meminta: "buat planning untuk refaktor modul X lalu implementasikan",
  "refaktor modul X", "migrasi modul X", "orkestrasi modul X", atau memulai pengerjaan modul di sesi chat baru.
---

# SIGAPP Master Orchestrator Playbook

Playbook ini memandu agen utama bertindak sebagai **Master Orchestrator** mandiri.

Tugas utama Orkestrator adalah memastikan bahwa perintah refaktor atau implementasi modul dari pengguna dijalankan secara tuntas: **bukan hanya menulis kode, tetapi secara otomatis memicu pendelegasian agen/subagent untuk memeriksa hasil pekerjaan, memperbaiki bug jika ditemukan, dan memperbarui seluruh dokumentasi ledger.**

---

## Alur Orkestrasi Lengkap (End-to-End Coordination)

```
[User Request] ──► "Buat planning untuk refaktor modul [NamaModul], lalu implementasikan"
       │
       ▼
[FASE 1: Perencanaan & Validasi]
  ├─ Baca: docs/modules/[NamaModul]/MODULE.md & docs/MIGRATION_PROGRESS.md
  ├─ Terapkan Skill: codeigniter4, karpathy-guidelines
  ├─ Validasi Plan: Gunakan checklist sigapp-module-qa/references/plan-validation-checklist.md
  └─ Terbitkan Implementation Plan Artifact ──► (Tunggu Konfirmasi User)
       │
       ▼
[FASE 2: Implementasi Bedah (Surgical)]
  ├─ Pisahkan Web & Api Controller
  ├─ Ekstraksi Service & Repository
  └─ Buat Shim Delegator Route Lama (Zero-Downtime)
       │
       ▼
[FASE 3: Pendelegasian Inspeksi QA (Subagent Delegation)]
  ├─ Panggil Subagent QA (atau jalankan 5-pilar verifikasi langsung):
  │   1. php spark module:status (Skor >= 80%)
  │   2. php spark routes & cek response shims
  │   3. php spark module:deadcode + MCP codegraph_explore
  │   4. Cek batas modul & transaksi database
  │   5. Playwright E2E UI Test dengan kredensial departemen
  │
  ├─ Apakah QA menemukan error / regresi?
  │   ├── YA:
  │   │    ▼
  │   │  [FASE 3B: Remediasi Bug Terencana]
  │   │    ├─ Stop & RCA via codegraph_explore + grep
  │   │    ├─ Buat Bugfix Plan Artifact
  │   │    ├─ Eksekusi Surgical Fix
  │   │    └─ Loop kembali ke FASE 3
  │   └── TIDAK:
  │        ▼
  │      [FASE 4: Sinkronisasi Dokumen & Ledger]
  │        ├─ php spark module:sync [NamaModul]
  │        ├─ Update docs/MIGRATION_PROGRESS.md (Status: COMPLETED, commit hash, tgl)
  │        ├─ Update docs/modul-notifikasi.md (jika relevan)
  │        └─ Terbitkan Sertifikat Penyelesaian Modul
```

---

## Cara Orkestrator Mendelegasikan ke Subagent

Orkestrator dapat mendelegasikan pemeriksaan pasca-implementasi ke subagent terpisah menggunakan tool `invoke_subagent`.

### 1. Template Pemanggilan Subagent QA (Inspector)

Segera setelah Fase 2 (implementasi) selesai, panggil subagent untuk memeriksa kode:

```json
{
  "Subagents": [
    {
      "TypeName": "self",
      "Role": "Module QA Inspector",
      "Model": "inherit",
      "Prompt": "TUGAS: Lakukan audit dan inspeksi mendalam terhadap modul [NamaModul] yang baru saja direfaktor.\n\nLangkah Wajib:\n1. Jalankan `php spark module:status` dan pastikan skor >= 80% dengan status REFACTORED.\n2. Periksa apakah rute lama dan rute baru terdaftar via `php spark routes` tanpa 404/500.\n3. Jalankan `php spark module:deadcode [NamaModul]` dan gunakan MCP `codegraph_explore` untuk memeriksa apakah ada simbol atau caller yang patah.\n4. Jalankan pengujian Playwright E2E pada `http://localhost/sigapp.dev` dengan login departemen terkait ([NamaDepartemen]: user=[User], password=[Password]). Pastikan 0 console error.\n\nLaporkan hasil audit secara terstruktur: apakah LOLOS (green) atau GAGAL (ada bug beserta error trace lengkap)."
    }
  ]
}
```

### 2. Penanganan Respon Subagent QA

- **Jika Subagent Melaporkan GAGAL (Error Ditemukan):**
  Orkestrator **tidak boleh menyerah atau menyuruh user memperbaikinya**. Orkestrator mengambil alih untuk menjalankan alur penanganan bug:
  1. Catat error message & stack trace.
  2. Buka `codegraph_explore` untuk menganalisis akar masalah (*Root Cause Analysis*).
  3. Buat draf **Bugfix Plan Artifact**.
  4. Perbaiki file kode yang bermasalah secara bedah (*surgical fix*).
  5. Panggil kembali subagent QA untuk verifikasi ulang hingga lolos.

- **Jika Subagent Melaporkan LOLOS (Semua Hijau):**
  Orkestrator melanjutkan ke Fase 4 (Sinkronisasi Dokumen).

### 3. Template Pemanggilan Subagent Docs Sync (Opsional)

Jika ingin mendelegasikan sinkronisasi dokumen:

```json
{
  "Subagents": [
    {
      "TypeName": "self",
      "Role": "Docs & Ledger Sync",
      "Model": "inherit",
      "Prompt": "TUGAS: Sinkronkan seluruh dokumentasi untuk modul [NamaModul] yang telah lolos verifikasi QA.\n\n1. Jalankan `php spark module:sync [NamaModul]` untuk memperbarui docs/modules/[NamaModul]/MODULE.md.\n2. Perbarui docs/MIGRATION_PROGRESS.md: ubah status modul menjadi COMPLETED, catat sesi/commit hash, dan tanggal verifikasi.\n3. Laporkan rangkuman perubahan dokumentasi."
    }
  ]
}
```

---

## Kredensial Pengujian Departemen untuk Agen QA

Orkestrator wajib menyertakan kredensial berikut saat memerintahkan pengujian Playwright:

| Departemen | Modul Terkait | Username | Password |
|---|---|---|---|
| **Keuangan** | Keuangan, PencairanJaminan | `nisalis` | `nisalis123` |
| **MKDT** | MKDT, Transaksi | `citra` | `citra123` |
| **Produksi** | Produksi, BPB, TiketMasalah | `alika` | `alikas123` |
| **Legal** | Legal | `marlina` | `marlina123` |
| **Promosi** | MGM, Promosi | `benny` | `benny123` |

---

## Format Laporan Akhir Orkestrator ke Pengguna

Setelah seluruh fase 1 s.d. 4 selesai, Orkestrator memberikan laporan penutupan terstruktur:

```markdown
# 🎯 Laporan Orkestrasi Modul: [NamaModul]

## 1. Ringkasan Eksekusi
- **Modul:** [NamaModul]
- **Status Akhir:** `COMPLETED` (Readiness Score: [X]%)
- **Arsitektur:** Controller Web & Api terpisah, Service logika bisnis, Repository query, Shims Zero-Downtime aktif.

## 2. Hasil Inspeksi Agen QA
- [x] Status & Readiness Score: [X]%
- [x] Integritas Rute & Shims: OK (Tanpa 404/500)
- [x] Audit Callers & Dead Code via CodeGraph: Bersih
- [x] Database Boundary & Transactions: Aman ($db->transStart aktif)
- [x] Playwright E2E Test: LULUS (Login role [Role], 0 console error)

## 3. Penanganan Bug (Jika Ada)
- [Tidak ada bug ditemukan / X bug telah diperbaiki secara surgical]

## 4. Sinkronisasi Dokumentasi
- [x] `docs/modules/[NamaModul]/MODULE.md` disinkronkan via `php spark module:sync`
- [x] `docs/MIGRATION_PROGRESS.md` diperbarui menjadi `COMPLETED`
- [x] Commit hash: `[Hash]`

Modul telah siap digunakan dan tidak ada utang migrasi yang tertinggal.
```
