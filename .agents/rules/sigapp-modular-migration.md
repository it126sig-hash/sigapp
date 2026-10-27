# Aturan & Standar Migrasi Modular SIGAPP

Dokumen ini adalah aturan WAJIB saat menjalankan mode tugas **`REFACTOR`** (migrasi modul bertahap) di bawah koordinasi `sigapp-orchestrator`.

---

## 1. Prinsip Utama: Zero-Downtime & Anti-Regresi

1. **Backward-Compatible:** Proses migrasi tidak boleh memutus operasional harian.
2. **Zero-Downtime Route & Service Shims:** Ketika controller dipecah ke `Web/<Modul>` dan `Api/<Modul>`, rute lama di `app/Config/Routes.php` dan class service/repo lama yang masih dipanggil modul lain **wajib dipertahankan sebagai alias/shim delegator**.
3. **AutoRoute CI4 Tetap Aktif:** Jangan mematikan `$routes->setAutoRoute(true)` secara global selama fase migrasi.
4. **Skema Database Non-Breaking:** Dilarang menghapus atau mengubah nama kolom/tabel aktif tanpa proses migrasi bertahap.

---

## 2. Batas Wilayah Modul (Module Boundary)

Setiap modul bisnis memiliki wilayah kepemilikan yang didefinisikan dalam `docs/modules/<NamaModul>/MODULE.md`:
- **Kepemilikan Tabel (`Owner Tables`):** Hanya modul pemilik yang diizinkan melakukan **INSERT, UPDATE, DELETE** pada tabel miliknya. Modul lain dilarang memanipulasi tabel modul lain secara langsung.
- **Komunikasi Antar-Modul (`Contracts`):**
  - *Frontend (Browser/JS/AJAX):* Melalui Public REST API (`/api/...`) dengan format JSON standar `{ status, message, data }`.
  - *Backend Antar-Modul:* Melalui Direct PHP Public Service Methods (dilarang loopback HTTP `curl`).
- **Struktur Namespace Modular:**
  - Web Controllers: `app/Controllers/Web/<Modul>/` (`App\Controllers\Web\<Modul>`)
  - API Controllers: `app/Controllers/Api/<Modul>/` (`App\Controllers\Api\<Modul>`)
  - Services: `app/Services/<Modul>/` (`App\Services\<Modul>`)
  - Repositories: `app/Repositories/<Modul>/` (`App\Repositories\<Modul>`)
- **Aturan Shared Views/Modals & Routes (`app/Config/Routes.php` & Folder View Bersama):**
  - File rute global `app/Config/Routes.php` boleh dimodifikasi untuk mendaftarkan rute eksplisit modul target (dengan tetap mempertahankan rute lama sebagai shim).
  - Untuk folder view bersama (`app/Views/master/`, `app/Views/siteplan/partials/`, `app/Views/setting/`), agen hanya boleh mengubah partial/view yang spesifik milik modul target tanpa merusak partial departemen lain.
  - Komponen *Shared Infrastructure* (`FileAccessService`, `StorageService`, `BaseApiController`, `FileController`) boleh dikonsumsi lintas modul tanpa dianggap melanggar batas wilayah.

---

## 3. Pembagian Tugas Agen pada Mode `REFACTOR`

1. **Fase 1 (`sigapp-explorer` + `Master Orchestrator`):**
   - `sigapp-explorer` membaca `docs/modules/<Modul>/MODULE.md`, memetakan *File Inventory* (Backend vs Frontend), *Owner Tables*, rute, *callers* via `codegraph_explore`, dan dead code.
   - `Master Orchestrator` menyusun Implementation Plan refaktor dan meminta persetujuan User.
2. **Fase 2 Backend (`sigapp-engineer`):**
   - Memisahkan `Web/<Modul>/<Modul>Controller` dan `Api/<Modul>/<Modul>Controller`.
   - Mengekstraksi logika bisnis & validasi finansial DB (`cleanNumeric()`, transaksi DB) ke `Services/<Modul>/<Modul>Service` dan query JOIN kompleks ke `Repositories/<Modul>/<Modul>Repository`.
   - Menyiapkan rute delegator & class shim Zero-Downtime, membersihkan dead code backend (0 callers), dan menjalankan `php -l`.
3. **Fase 2 Frontend (`sigapp-frontend`):**
   - Menyesuaikan View (`app/Views/`), memindahkan inline CSS/JS ke `public/assets/css/<modul>.css` dan `public/assets/js/<modul>.js` dengan `filemtime(...)`, serta memastikan kepatuhan penuh terhadap skill **`sigapp-ui-acuan`**.
4. **Fase 3 (`sigapp-qa`):**
   - Mengaudit 0 broken callers via `codegraph_explore`.
   - Menguji UI E2E via Playwright MCP dengan skill `sigapp-local-testing` (0 console errors + audit checklist `sigapp-ui-acuan`).
   - Menjalankan `php spark routes`, `php spark module:deadcode <Modul>`, dan memastikan `php spark module:status` mencapai status `REFACTORED` dengan skor $\ge 80\%$.
5. **Fase 4 (`sigapp-docs`):**
   - Setelah `sigapp-qa` menerbitkan sertifikat `PASS`, menjalankan `php spark module:sync <Modul>`, memperbarui baris modul di `docs/MIGRATION_PROGRESS.md` menjadi `COMPLETED`, dan memastikan `git status` bersih serta terisolasi.
