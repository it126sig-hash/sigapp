Implementasikan /sigapp-orchestrator. Merefaktor modular Modul `Transaksi` di aplikasi SIGAPP (CodeIgniter 4) untuk kebutuhan produksi agar mencapai status `REFACTORED` dengan Readiness Score 100%.

Working directory: c:\Users\Salivs\Data\laragon\www\sigapp.dev
Integrity mode: development

Dokumen Rencana Lengkap: `C:\Users\Salivs\.gemini\antigravity\brain\405d9c9e-64a1-4f14-bc7f-ec3e1d14e796\prioritas_sisa_migrasi_modular.md`

## Requirements

### R1. Backend Execution & Modular Separation
- Buat Repositories di `app/Repositories/Transaksi/` (`TransaksiRepository.php`, `HargaJualRepository.php`, `KavlingRepository.php`).
- Buat Services di `app/Services/Transaksi/` (`TransaksiService.php`, `KavlingRequestService.php`, `HargaJualService.php`).
- Buat Web Controller di `app/Controllers/Web/Transaksi/HargajualController.php` (tanpa instansiasi Model atau Database langsung).
- Buat Api Controllers di `app/Controllers/Api/Transaksi/` (`TransaksiController.php`, `HargajualController.php`, `KavlingRequestController.php`, `MkdtController.php`, `KavlingController.php`) dengan kontrak RAW_JSON_READY (`{ success, messages, data, token }` dan key root legacy).
- Buat 11 Zero-Downtime Shims di lokasi lama (`Transaksi.php`, `Hargajual.php` sebagai Dual-Shim Delegator, `Kavling.php`, `Api/TransaksiController.php`, `Api/MkdtController.php`, `Api/KavlingRequestController.php`, `TransaksiService.php`, `KavlingRequestService.php`, `TransaksiRepository.php`, `HargaJualRepository.php`, `KavlingRepository.php`).
- Daftarkan rute eksplisit di `app/Config/Routes.php` (menghilangkan AutoRoute pada controller Transaksi).
- Perbarui `app/Libraries/ModuleManager/ModuleCatalog.php` untuk modul Transaksi (`owner_tables`, `controllers`, `services`, `repositories`, `shims`, `assets_js`).

### R2. Frontend Execution & UI Standards
- Ekstrak inline `<style>` dan `<script>` dari `app/Views/hargajual/index.php` ke `public/assets/css/hargajual.css` dan `public/assets/js/hargajual.js` dengan `filemtime()` sesuai `sigapp-ui-acuan`.
- Sediakan konfigurasi `window.HARGAJUAL_CONFIG` di view untuk URL dan CSRF.

### R3. QA & Verifikasi
- PHP syntax check `php -l` pada seluruh file PHP baru dan termodifikasi.
- PHPUnit tests: `tests/unit/KavlingCompositeQueryTest.php`, `tests/unit/MkdtConsumerReplacementHistoryTest.php`, `tests/database/ConsumerReplacementServiceTest.php`.
- Spark CLI: `php spark module:map` (0 unmapped files), `php spark module:status Transaksi` (target 100% REFACTORED), `php spark routes` (0 AutoRoutes untuk Transaksi).
- E2E Playwright pengujian halaman `/hargajual` dan modal transaksi siteplan dengan `$sigapp-local-testing`.

### R4. Sinkronisasi Dokumentasi
- Jalankan `php spark module:sync Transaksi` dan perbarui `docs/modules/Transaksi/MODULE.md`.
- Perbarui baris Transaksi di `docs/MIGRATION_PROGRESS.md` menjadi `COMPLETED` (100% Score).

## Acceptance Criteria

### Backend & Architecture
- [ ] 11 Zero-Downtime Shims aktif di lokasi lama dan mewarisi class baru tanpa memutus pemanggil eksternal.
- [ ] `app/Controllers/Hargajual.php` bertindak sebagai Dual-Shim Delegator yang mendelegasikan request web ke `Web/Transaksi/HargajualController` dan request AJAX ke `Api/Transaksi/HargajualController`.
- [ ] Rute eksplisit Web dan API terdaftar di `app/Config/Routes.php` dan `php spark routes` mencatat 0 AutoRoutes pada Transaksi.
- [ ] Endpoint API Transaksi mengembalikan envelope JSON `{ success, messages, data, token }` sembari mempertahankan key root legacy.
- [ ] `ModuleCatalog.php` mencakup modul Transaksi lengkap dengan `owner_tables` (`kavling`, `kavling_request`, `file_hargajual`, `hargajual`).

### Frontend
- [ ] Tidak ada inline `<style>` atau `<script>` di `app/Views/hargajual/index.php`.
- [ ] File `public/assets/css/hargajual.css` dan `public/assets/js/hargajual.js` dimuat dengan cache buster `filemtime()`.
- [ ] UI `/hargajual` dan modal transaksi di Siteplan berjalan tanpa console error.

### Verification & Testing
- [ ] Syntax check `php -l` lulus untuk seluruh file PHP baru dan shim.
- [ ] Test PHPUnit (`KavlingCompositeQueryTest`, `MkdtConsumerReplacementHistoryTest`, `ConsumerReplacementServiceTest`) lulus 100%.
- [ ] `php spark module:map` menghasilkan 0 unmapped files untuk Transaksi.
- [ ] `php spark module:status Transaksi` menampilkan status `REFACTORED` dengan skor 100%.
- [ ] Pengujian E2E Playwright pada `/hargajual` dan modal transaksi Siteplan bebas error (0 error 404/500).

### Documentation
- [ ] `docs/modules/Transaksi/MODULE.md` tersinkronisasi dan terdokumentasi penuh.
- [ ] `docs/MIGRATION_PROGRESS.md` menunjukkan status Transaksi `COMPLETED` (100% Score).
