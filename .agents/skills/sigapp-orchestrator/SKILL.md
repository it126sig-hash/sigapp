---
name: sigapp-orchestrator
description: >
  Master Orchestrator untuk mengoordinasikan seluruh alur kerja pengembangan di SIGAPP: perencanaan (plan),
  implementasi bedah (surgical), pendelegasian inspeksi QA adaptif, remediasi bug terencana berbasis codegraph,
  dan sinkronisasi dokumen ledger. Mendukung 4 mode tugas: Fitur Baru (FEATURE), Perbaikan Bug (BUGFIX),
  Refaktor & Migrasi Modul (REFACTOR), serta UI/Frontend/Laporan (UI_REPORT).
  Gunakan skill ini ketika user meminta: "buat fitur X", "tambahkan fungsi Y", "perbaiki bug/error Z",
  "ubah tampilan/cetak laporan W", "refaktor modul X", "buat planning modul X lalu implementasikan",
  "orkestrasi tugas X", atau memulai pengerjaan tugas di sesi chat baru.
---

# SIGAPP Master Orchestrator Playbook

Playbook ini memandu agen utama bertindak sebagai **Master Orchestrator** mandiri untuk seluruh aktivitas rekayasa perangkat lunak di SIGAPP.

Tugas utama Orkestrator adalah memastikan bahwa setiap permintaan pengembangan—baik penambahan fitur baru, perbaikan bug, penyesuaian UI/laporan, maupun refaktor modul—dieksekusi secara tuntas (*end-to-end*): **bukan hanya menulis kode, tetapi secara otomatis memicu pendelegasian inspeksi QA (Playwright & Codegraph), menangani bug jika ditemukan, dan menyinkronkan dokumentasi ledger secara adaptif.**

---

## 1. Klasifikasi 4 Mode Tugas

Sebelum mengeksekusi, Orkestrator mengidentifikasi tugas ke dalam salah satu dari 4 mode berikut:

1. **`FEATURE` (Fitur Baru & Enhancement):** Penambahan endpoint baru, form baru, alur transaksi baru, atau penambahan kapabilitas pada modul yang ada.
2. **`BUGFIX` (Perbaikan Bug & Error):** Penanganan runtime error, perbaikan logika kalkulasi, penanganan broken route/AJAX, atau regression fix.
3. **`REFACTOR` (Refaktor & Migrasi Modul):** Pemisahan controller ke Web/Api, pemindahan logika ke Service/Repository, penyiapan shims Zero-Downtime, dan penutupan utang migrasi modular.
4. **`UI_REPORT` (UI, Frontend & Cetak Laporan):** Pembaruan tampilan view, JavaScript/AJAX front-end, modal, DataTables, atau template laporan mPDF/dompdf/PhpSpreadsheet.

---

## 2. Alur Orkestrasi Lengkap (End-to-End Coordination)

```
[User Request] ──► "Buat fitur X" / "Perbaiki bug Y" / "Ubah laporan Z" / "Refaktor modul W"
       │
       ▼
[FASE 1: Perencanaan & Validasi Gate]
  ├─ Baca: Context modul di docs/modules/[NamaModul]/MODULE.md
  ├─ Audit Codegraph: MCP codegraph_explore (Petakan pemanggil & blast radius)
  ├─ Terapkan Prinsip: karpathy-guidelines & codeigniter4
  ├─ Validasi Plan: checklist sigapp-module-qa/references/plan-validation-checklist.md
  └─ Terbitkan Implementation / Bugfix Plan Artifact ──► (Tunggu Konfirmasi User)
       │
       ▼
[FASE 2: Implementasi Bedah (Surgical Execution)]
  ├─ FEATURE: Thin Controller -> Service -> Repository (Validasi finansial DB & DB Trans jika relevan)
  ├─ BUGFIX: Perbaiki akar masalah secara minimal tanpa menyentuh kode sehat di sekitarnya
  ├─ REFACTOR: Pisahkan Web & Api Controller, Service, Repo, dan buat Shim Delegator Zero-Downtime
  └─ UI_REPORT: Perbarui View/JS/Report template dengan menjaga konsistensi API contracts
       │
       ▼
[FASE 3: Pendelegasian Inspeksi QA Adaptif (Subagent / Direct)]
  ├─ Wajib Semua Mode:
  │   1. Audit pemanggil & simbol via MCP codegraph_explore / grep (0 broken callers)
  │   2. Playwright E2E UI Test (Wajib baca $sigapp-local-testing, login akun departemen, 0 console error)
  ├─ Kondisional Berdasarkan Perubahan:
  │   3. php spark routes (jika ada rute baru/berubah)
  │   4. php spark module:deadcode (jika ada metode/simbol dihapus atau dipindah)
  │   5. php spark module:status (jika mode REFACTOR, target skor >= 80%)
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
  │      [FASE 4: Sinkronisasi Dokumen & Ledger Adaptif]
  │        ├─ php spark module:sync [NamaModul] (jika struktur/rute/kontrak berubah)
  │        ├─ Update docs/MIGRATION_PROGRESS.md (khusus mode REFACTOR modul selesai)
  │        ├─ Update docs/modul-notifikasi.md (jika menambah/mengubah notifikasi)
  │        └─ Terbitkan Laporan Penyelesaian Tugas ke Pengguna
```

---

## 3. Matriks Eksekusi Adaptif per Mode Tugas

| Tahap / Pemeriksaan | `FEATURE` | `BUGFIX` | `REFACTOR` | `UI_REPORT` |
|---|:---:|:---:|:---:|:---:|
| **Fase 1: RCA / Blast Radius (`codegraph`)** | ✅ Wajib | ✅ Wajib (RCA) | ✅ Wajib | ✅ Wajib (Cek Callers/View) |
| **Fase 1: Validasi Plan (`sigapp-module-qa`)** | ✅ Wajib | ✅ Wajib (Bugfix Plan) | ✅ Wajib | ✅ Wajib |
| **Fase 2: Pola Thin Controller → Service → Repo** | ✅ Wajib | ⚙️ Ikuti Arsitektur Modul | ✅ Wajib + Shims | ⚙️ Fokus View/JS/Report |
| **Fase 2: Validasi Finansial DB & DB Trans** | ⚙️ Jika mutasi/uang | ⚙️ Jika mutasi/uang | ✅ Wajib | ⚙️ Jika kalkulasi angka |
| **Fase 3: Audit Callers via `codegraph`** | ✅ Wajib | ✅ Wajib | ✅ Wajib | ✅ Wajib |
| **Fase 3: Playwright E2E (`$sigapp-local-testing`)** | ✅ Wajib | ✅ Wajib | ✅ Wajib | ✅ Wajib |
| **Fase 3: `php spark routes` & `module:deadcode`** | ⚙️ Jika sentuh rute | ⚙️ Jika bug terkait rute | ✅ Wajib | ❌ Opsional |
| **Fase 3: `php spark module:status` ($\ge 80\%$)** | ⚙️ Jika modul `REFACTORED` | ❌ Opsional | ✅ Wajib | ❌ Opsional |
| **Fase 4: `php spark module:sync <Modul>`** | ⚙️ Jika ubah struktur | ⚙️ Jika kontrak berubah | ✅ Wajib | ❌ Opsional |
| **Fase 4: Update `docs/MIGRATION_PROGRESS.md`** | ❌ Tidak perlu | ❌ Tidak perlu | ✅ Wajib | ❌ Tidak perlu |
| **Fase 4: Update `docs/modul-notifikasi.md`** | ⚙️ Jika sentuh notif | ⚙️ Jika sentuh notif | ⚙️ Jika sentuh notif | ❌ Tidak perlu |

*Keterangan: ✅ Wajib Mutlak | ⚙️ Kondisional Berdasarkan Elemen yang Disentuh | ❌ Tidak Perlu*

---

## 4. Cara Orkestrator Mendelegasikan ke Subagent QA

Orkestrator dapat mendelegasikan pemeriksaan pasca-implementasi ke subagent terpisah menggunakan tool `invoke_subagent`.

### Template Pemanggilan Subagent QA Adaptif

Segera setelah Fase 2 (implementasi) selesai, sesuaikan instruksi pemanggilan subagent berdasarkan mode tugas:

```json
{
  "Subagents": [
    {
      "TypeName": "self",
      "Role": "Module QA Inspector",
      "Model": "inherit",
      "Prompt": "TUGAS: Lakukan verifikasi QA pasca-implementasi untuk modul [NamaModul] pada mode [FEATURE/BUGFIX/REFACTOR/UI_REPORT].\n\nDetail Perubahan:\n- [Deskripsi singkat perubahan yang baru dilakukan]\n\nLangkah Wajib:\n1. BACA SKILL: Wajib load dan ikuti aturan $sigapp-local-testing sebelum menjalankan pengujian browser.\n2. CODEGRAPH AUDIT: Gunakan MCP codegraph_explore untuk memvalidasi simbol/fungsi yang baru diubah dan pastikan 0 pemanggil putus.\n3. PLAYWRIGHT E2E: Buka `http://localhost/sigapp.dev`, login sebagai departemen [NamaDepartemen] (user=[User], pass=[Pass]), dan verifikasi skenario [Fitur/Bugfix/UI yang diuji]. Pastikan 0 console error (404/500).\n4. PEMERIKSAAN KONDISIONAL:\n   - Jika ada rute baru/berubah: jalankan `php spark routes`.\n   - Jika ada penghapusan/pemindahan simbol: jalankan `php spark module:deadcode [NamaModul]`.\n   - Jika mode REFACTOR: jalankan `php spark module:status` (target >= 80%).\n\nLaporkan hasil audit secara terstruktur: apakah LOLOS (green) atau GAGAL (sertakan stack trace dan detail error)."
    }
  ]
}
```

### Penanganan Respon Subagent QA

- **Jika Subagent Melaporkan GAGAL (Error Ditemukan):**
  Orkestrator **tidak boleh menyerah atau menyuruh user memperbaikinya**. Orkestrator mengambil alih untuk menjalankan alur penanganan bug:
  1. Catat error message & stack trace.
  2. Buka `codegraph_explore` untuk menganalisis akar masalah (*Root Cause Analysis*).
  3. Buat draf **Bugfix Plan Artifact**.
  4. Perbaiki file kode yang bermasalah secara bedah (*surgical fix*).
  5. Panggil kembali subagent QA untuk verifikasi ulang hingga lolos.

- **Jika Subagent Melaporkan LOLOS (Semua Hijau):**
  Orkestrator melanjutkan ke Fase 4 (Sinkronisasi Dokumen Adaptif).

---

## 5. Kredensial Pengujian Departemen untuk Agen QA

Orkestrator wajib menyertakan kredensial berikut saat memerintahkan pengujian Playwright:

| Departemen | Modul Terkait | Username | Password |
|---|---|---|---|
| **Keuangan** | Keuangan, Pajak, CashOut, PencairanJaminan | `nisalis` | `nisalis123` |
| **MKDT** | MKDT, Konsumen, Transaksi | `citra` | `citra123` |
| **Produksi** | Produksi, BPB, SPK, Gudang, TiketMasalah | `alika` | `alikas123` |
| **Legal** | Legal, HGB, PBB, PBG, AJB | `marlina` | `marlina123` |
| **Promosi** | MGM, Promosi, Event | `benny` | `benny123` |

> **PERINGATAN PENGUJIAN:** Sebelum membuka browser lokal, agen QA **WAJIB** membaca dan mengaktifkan skill `$sigapp-local-testing`.

---

## 6. Format Laporan Akhir Orkestrator ke Pengguna

Setelah seluruh fase selesai, Orkestrator memberikan laporan penutupan terstruktur:

```markdown
# 🎯 Laporan Orkestrasi Tugas: [Judul Tugas]

## 1. Ringkasan Eksekusi
- **Modul:** [NamaModul]
- **Mode Tugas:** `[FEATURE | BUGFIX | REFACTOR | UI_REPORT]`
- **Status Akhir:** `SUCCESS / COMPLETED`
- **Ringkasan Perubahan:** [Penjelasan ringkas dan presisi mengenai kode yang diubah/dibuat]

## 2. Hasil Inspeksi QA Adaptif
- [x] Audit Pemanggil & Integritas Simbol via Codegraph: Lulus (0 broken callers)
- [x] Playwright E2E UI Test: Lulus (Role: [NamaRole], 0 console errors)
- [x/N/A] Integritas Rute (`php spark routes`): [OK / Tidak Ada Perubahan Rute]
- [x/N/A] Audit Kesiapan Modular (`php spark module:status`): [Skor: X% / N/A]

## 3. Penanganan Bug & Stabilitas (Jika Terjadi Regresi)
- [Tidak ada bug ditemukan / X bug telah diperbaiki secara surgical via Fase 3B]

## 4. Sinkronisasi Dokumentasi Adaptif
- [x/N/A] `docs/modules/[NamaModul]/MODULE.md`: [Disinkronkan via module:sync / N/A]
- [x/N/A] `docs/MIGRATION_PROGRESS.md`: [Diperbarui / N/A - Bukan Refaktor Modul]
- [x/N/A] `docs/modul-notifikasi.md`: [Diperbarui / N/A]

Tugas telah selesai diverifikasi secara end-to-end tanpa error atau regresi.
```
