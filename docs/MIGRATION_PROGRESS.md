# SIGAPP Modular Migration Progress Ledger

> **Dokumen ini adalah sumber kebenaran tunggal (Single Source of Truth) untuk melacak progres migrasi arsitektur modular Backend CI4 lintas percakapan.**
> Setiap kali selesai merefaktor satu modul di sesi chat baru, perbarui baris modul tersebut di dokumen ini dan lakukan git commit.
>
> **Keterkaitan dengan Migrasi Vue 3 (`shadcn-vue`) & Mobile App (Capacitor):**
> - Ruang lingkup produk, arsitektur repo Vue, dan target **80% Parity Cutover** didokumentasikan di [`sigapp-vue/docs/VUE_MOBILE_PRD.md`](file:///c:/Users/Salivs/Data/laragon/www/sigapp-vue/docs/VUE_MOBILE_PRD.md).
> - Progres kesiapan **Wave 0**, status **API Vue-Ready (`RAW_JSON_READY`)**, dan pembangunan UI Vue/Capacitor dilacak secara khusus di [`sigapp-vue/docs/VUE_MIGRATION_PROGRESS.md`](file:///c:/Users/Salivs/Data/laragon/www/sigapp-vue/docs/VUE_MIGRATION_PROGRESS.md).
> - Standar teknis endpoint REST API JSON murni (bebas string HTML DataTables), Bearer Token, dan Signed URL mengacu pada [`sigapp-vue/docs/API_SPA_CONTRACT.md`](file:///c:/Users/Salivs/Data/laragon/www/sigapp-vue/docs/API_SPA_CONTRACT.md).
> - Panduan scaffolding repo terpisah (`sigapp-vue`) mengacu pada [`sigapp-vue/docs/VUE_PROJECT_SCAFFOLDING.md`](file:///c:/Users/Salivs/Data/laragon/www/sigapp-vue/docs/VUE_PROJECT_SCAFFOLDING.md).

---

## 1. Rangkuman Progres Global

* **Total Modul:** 16
* **Status:** `12 Selesai` | `0 Sedang Dikerjakan` | `4 Antre (Pending)`
* **Strategi:** Migrasi modular bertahap (1 modul per sesi *fresh chat*) dengan jaminan **Zero-Downtime**.

---

## 2. Papan Status Modul (Urutan Eksekusi yang Direkomendasikan)

Urutan di bawah ini disusun dari **Modul Daun (Leaf/Paling Terisolasi)** menuju **Modul Inti (Core/Tinggi Keterikatan)** untuk meminimalisasi risiko breaking changes.

> *Catatan Konsolidasi & Prioritas Akselerasi Wave 0 (Vue/Mobile):*
> - **Prioritas Wave 0 Vue/Capacitor:** Modul **#7 `Auth`** dan **#8 `Notifikasi`** diprioritaskan untuk dikerjakan lebih dulu sebagai fondasi infrastruktur **Wave 0** (Bearer Token `DeviceSessionService`, endpoint `/api/auth/me`, dan Push Notification FCM/WebPush) sebelum memulai pembangunan aplikasi di repo Vue. Lihat checklist lengkapnya di [`sigapp-vue/docs/VUE_MIGRATION_PROGRESS.md`](file:///c:/Users/Salivs/Data/laragon/www/sigapp-vue/docs/VUE_MIGRATION_PROGRESS.md).
> - Fitur Pencairan Jaminan (Retensi Perbankan) telah dikonsolidasikan langsung ke dalam sub-domain Pencairan Bank / Akad pada modul **Keuangan** sebagai bagian utuh dari Cash-In.
> - Fitur Master Sales External dan Pengelolaan/Pencairan Fee Sales dikonsolidasikan di bawah modul **SalesExternal**.

| No | Modul | Kategori | Tingkat Kesulitan | Status | Sesi / Commit Hash | Terakhir Diverifikasi |
|---|---|---|---|---|---|---|
| 1 | **BPB** | Leaf Module | Rendah (Sudah modern Web/Api) | `COMPLETED` | Session Refactor (100% Score) | 2026-10-23 |
| 2 | **TiketMasalah** | Leaf Module | Rendah (Sudah modular Web/Api) | `COMPLETED` | `80a0180` (100% Score) | 2026-10-23 |
| 3 | **MGM** | Leaf Module | Rendah (Sudah modular Web/Api) | `COMPLETED` | Layer Fix (100% Score) | 2026-11-12 |
| 4 | **SalesExternal** | Intermediate (Master Sales & Fee Sales) | Sedang (Modular Web/Api & Services) | `COMPLETED` | `e238e4a` (100% Score) | 2026-11-09 |
| 5 | **Legal** | Intermediate | Sedang | `COMPLETED` | `e658eba` (100% Score) | 2026-11-11 |
| 6 | **AuditTrail** | Intermediate | Rendah | `COMPLETED` | Refactor QA-PASS (100% Score) | 2026-11-12 |
| 7 | **Auth** | Infrastructure | Sedang | `COMPLETED` | Wave 0 API Bridge (`QA-PASS`) | 2026-11-10 |
| 8 | **Notifikasi** | Infrastructure | Sedang | `COMPLETED` | Refactor QA-PASS (100% Score) | 2026-11-12 |
| 9 | **Proyek** | Core Master Data | Sedang | `COMPLETED` | `65c937d` (90% Score) | 2026-10-23 |
| 10 | **Produksi** | Core Construction | Sedang - Tinggi | `COMPLETED` | Refactor QA-PASS (100% Score) | 2026-11-12 |
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
