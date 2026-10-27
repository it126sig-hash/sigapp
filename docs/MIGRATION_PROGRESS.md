# SIGAPP Modular Migration Progress Ledger

> **Dokumen ini adalah sumber kebenaran tunggal (Single Source of Truth) untuk melacak progres migrasi modular lintas percakapan.**
> Setiap kali selesai merefaktor satu modul di sesi chat baru, perbarui baris modul tersebut di dokumen ini dan lakukan git commit.

---

## 1. Rangkuman Progres Global

* **Total Modul:** 16
* **Status:** `6 Selesai` | `0 Sedang Dikerjakan` | `10 Antre (Pending)`
* **Strategi:** Migrasi modular bertahap (1 modul per sesi *fresh chat*) dengan jaminan **Zero-Downtime**.

---

## 2. Papan Status Modul (Urutan Eksekusi yang Direkomendasikan)

Urutan di bawah ini disusun dari **Modul Daun (Leaf/Paling Terisolasi)** menuju **Modul Inti (Core/Tinggi Keterikatan)** untuk meminimalisasi risiko breaking changes.

> *Catatan Konsolidasi:*
> - Fitur Pencairan Jaminan (Retensi Perbankan) telah dikonsolidasikan langsung ke dalam sub-domain Pencairan Bank / Akad pada modul **Keuangan** sebagai bagian utuh dari Cash-In.
> - Fitur Master Sales External dan Pengelolaan/Pencairan Fee Sales dikonsolidasikan di bawah modul **SalesExternal**.

| No | Modul | Kategori | Tingkat Kesulitan | Status | Sesi / Commit Hash | Terakhir Diverifikasi |
|---|---|---|---|---|---|---|
| 1 | **BPB** | Leaf Module | Rendah (Sudah modern Web/Api) | `COMPLETED` | Session Refactor (100% Score) | 2026-10-23 |
| 2 | **TiketMasalah** | Leaf Module | Rendah (Sudah modular Web/Api) | `COMPLETED` | `80a0180` (100% Score) | 2026-10-23 |
| 3 | **MGM** | Leaf Module | Rendah (Sudah modular Web/Api) | `COMPLETED` | Session Refactor (90% Score) | 2026-10-23 |
| 4 | **SalesExternal** | Intermediate (Master Sales & Fee Sales) | Sedang (Sudah Web/Api, belum di subfolder `<Modul>/`) | `PENDING` | - (60% Score) | - |
| 5 | **Legal** | Intermediate | Sedang | `PENDING` | - | - |
| 6 | **AuditTrail** | Intermediate | Rendah | `PENDING` | - | - |
| 7 | **Auth** | Infrastructure | Sedang | `PENDING` | - | - |
| 8 | **Notifikasi** | Infrastructure | Sedang | `PENDING` | - | - |
| 9 | **Proyek** | Core Master Data | Sedang | `COMPLETED` | `65c937d` (90% Score) | 2026-10-23 |
| 10 | **Produksi** | Core Construction | Sedang - Tinggi | `PENDING` | - | - |
| 11 | **UserManagement** | Core Master Data | Sedang | `COMPLETED` | Session Refactor (100% Score) | 2026-10-23 |
| 12 | **Settings** | Core System | Sedang | `COMPLETED` | `8442c4e` (100% Score) | 2026-10-23 |
| 13 | **Siteplan** | Core Spasial UI | Tinggi (Multi-dept JS) | `PENDING` | - | - |
| 14 | **Transaksi** | Core Domain | Tinggi | `PENDING` | - | - |
| 15 | **MKDT** | Core Domain | Sangat Tinggi (Fat Controller) | `PENDING` | - | - |
| 16 | **Keuangan** | Core Financial | Sangat Tinggi (Terbesar + Pencairan Akad/Jaminan) | `PENDING` | - | - |

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
   * **Aturan Shared Views/Modals & Routes:**
     * File rute global `app/Config/Routes.php` **boleh dimodifikasi** untuk mendaftarkan rute eksplisit modul target (dengan tetap mempertahankan rute lama sebagai shim/fallback).
     * Untuk folder view bersama (`app/Views/master/`, `app/Views/siteplan/partials/`, `app/Views/setting/`), agen hanya boleh mengubah partial/view yang spesifik milik modul target tanpa merusak partial departemen lain.
3. **Langkah 3 (Selesaikan & Verifikasi):**
   * Pisahkan controller ke `Web/<Modul>/` & `Api/<Modul>/`, serta pindahkan Service ke `Services/<Modul>/` dan Repository ke `Repositories/<Modul>/`.
   * Pastikan endpoint dan class lama tetap hidup sebagai fallback (*Zero-Downtime Shims*) dan terdaftar di `ModuleCatalog.php`.
   * Uji antarmuka modul menggunakan Playwright.
   * Jalankan `php spark module:sync <NamaModul>` untuk memperbarui `MODULE.md`.
   * Update status modul di tabel di atas menjadi `COMPLETED` beserta commit hash dan skor kesiapan.
