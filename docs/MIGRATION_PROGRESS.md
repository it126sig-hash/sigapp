# SIGAPP Modular Migration Progress Ledger

> **Dokumen ini adalah sumber kebenaran tunggal (Single Source of Truth) untuk melacak progres migrasi modular lintas percakapan.**
> Setiap kali selesai merefaktor satu modul di sesi chat baru, perbarui baris modul tersebut di dokumen ini dan lakukan git commit.

---

## 1. Rangkuman Progres Global

* **Total Modul:** 16
* **Status:** `0 Selesai` | `0 Sedang Dikerjakan` | `16 Antre (Pending)`
* **Strategi:** Migrasi modular bertahap (1 modul per sesi *fresh chat*) dengan jaminan **Zero-Downtime**.

---

## 2. Papan Status Modul (Urutan Eksekusi yang Direkomendasikan)

Urutan di bawah ini disusun dari **Modul Daun (Leaf/Paling Terisolasi)** menuju **Modul Inti (Core/Tinggi Keterikatan)** untuk meminimalisasi risiko breaking changes.

| No | Modul | Kategori | Tingkat Kesulitan | Status | Sesi / Commit Hash | Terakhir Diverifikasi |
|---|---|---|---|---|---|---|
| 1 | **BPB** | Leaf Module | Rendah (Sudah modern Web/Api) | `PENDING` | - | - |
| 2 | **TiketMasalah** | Leaf Module | Rendah (Sudah Web/Api) | `PENDING` | - | - |
| 3 | **MGM** | Leaf Module | Rendah (Sudah Web/Api) | `PENDING` | - | - |
| 4 | **PencairanJaminan** | Leaf Module | Rendah | `PENDING` | - | - |
| 5 | **Legal** | Intermediate | Sedang | `PENDING` | - | - |
| 6 | **AuditTrail** | Intermediate | Rendah | `PENDING` | - | - |
| 7 | **Auth** | Infrastructure | Sedang | `PENDING` | - | - |
| 8 | **Notifikasi** | Infrastructure | Sedang | `PENDING` | - | - |
| 9 | **Proyek** | Core Master Data | Sedang | `PENDING` | - | - |
| 10 | **Produksi** | Core Construction | Sedang - Tinggi | `PENDING` | - | - |
| 11 | **UserManagement** | Core Master Data | Sedang | `PENDING` | - | - |
| 12 | **Settings** | Core System | Sedang | `PENDING` | - | - |
| 13 | **Siteplan** | Core Spasial UI | Tinggi (Multi-dept JS) | `PENDING` | - | - |
| 14 | **Transaksi** | Core Domain | Tinggi | `PENDING` | - | - |
| 15 | **MKDT** | Core Domain | Sangat Tinggi (Fat Controller) | `PENDING` | - | - |
| 16 | **Keuangan** | Core Financial | Sangat Tinggi (Terbesar) | `PENDING` | - | - |

---

## 3. Protokol Memulai di Fresh Chat (Untuk Agen AI)

Ketika memulai sesi percakapan baru untuk merefaktor satu modul:

1. **Langkah 1 (Baca Konteks Awal):**
   * Baca file ini: `docs/MIGRATION_PROGRESS.md` untuk konfirmasi status antrean.
   * Baca dokumen spesifik modul: `docs/modules/<NamaModul>/MODULE.md`.
   * Baca aturan migrasi: `.agents/rules/sigapp-modular-migration.md`.
2. **Langkah 2 (Fokus Wilayah):**
   * Agen **HANYA** diizinkan memodifikasi file yang terdaftar di `File Inventory` milik modul tersebut.
   * Dilarang mengubah file modul lain tanpa izin eksplisit.
3. **Langkah 3 (Selesaikan & Verifikasi):**
   * Pisahkan controller ke `Web/` & `Api/` jika belum.
   * Pastikan endpoint lama tetap hidup sebagai fallback (*Zero-Downtime*).
   * Uji antarmuka modul menggunakan Playwright.
   * Jalankan `php spark module:sync <NamaModul>` untuk memperbarui `MODULE.md`.
   * Update status modul di tabel di atas menjadi `COMPLETED` beserta commit hash.
