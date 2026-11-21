# Panduan Perekaman Perubahan & Sinkronisasi Dokumentasi Modul (`sigapp-docs`)

Dokumentasi dalam SIGAPP bukan sekadar catatan pasif, melainkan **kontrak batas wilayah dan sumber kebenaran hidup (*Living Source of Truth*) yang dibaca oleh sistem dan agen AI lain**.

Panduan ini mengatur cara subagent **`sigapp-docs`** merekam setiap perubahan dan memperbarui status modul pada `docs/modules/<NamaModul>/MODULE.md` setelah suatu tugas selesai diverifikasi (`PASS` oleh `sigapp-qa`).

---

## 1. Perekaman Perubahan & Sinkronisasi `docs/modules/<NamaModul>/MODULE.md` [WAJIB SEMUA MODE]

Setiap kali tugas (`FEATURE`, `BUGFIX`, `REFACTOR`, maupun `UI_REPORT`) selesai dan lulus QA, `sigapp-docs` **WAJIB SELALU** memperbarui `docs/modules/<NamaModul>/MODULE.md`.

### Langkah 1A — Sinkronisasi Otomatis via `module:sync`
Jika tugas menambahkan file Controller, Service, Repository, Model, View, CSS, atau JS baru pada modul terkait:
1. Pastikan path file baru telah terdaftar pada array modul terkait di `app/Libraries/ModuleManager/ModuleCatalog.php`.
2. Jalankan perintah sinkronisasi:
   ```bash
   php spark module:sync <NamaModul>
   ```
   Perintah ini otomatis memindai ulang kode aktual untuk memperbarui:
   - **Status & Readiness Score** (`REFACTORED` / `MAPPED`)
   - **Database Tables** (`Owner Table(s)` dan `Shared Tables`)
   - **Contracts**:
     - *A. Public HTTP Endpoints & Routes Inventory* (`Routes.php`: Method, URI, Tipe, Handler, Status Migrasi)
     - *B. Public PHP Services (Backend Contract Antar-Modul)* (Seluruh public method pada Service modul)
   - **File Inventory Lengkap per Layer**:
     - `Controllers` (beserta jumlah baris & daftar public methods)
     - `Services` (beserta jumlah baris & daftar public methods)
     - `Repositories` (beserta jumlah baris & daftar public methods)
     - `Models`
     - `Views` (beserta daftar file `.php` di dalamnya)
     - `CSS & JavaScript` (`public/assets/css/...` dan `public/assets/js/...`)
     - `Zero-Downtime Shims`

### Langkah 1B — Perekaman Kurasi & Kontrak Modul di `MODULE.md`
`ModuleDocGenerator` menjaga agar bagian kurasi (`## Purpose`, `## Out of scope`, `## Invariants`, `## Key Functions`, `## Deprecated & Legacy Shims`, `## Change Log`, dan `## Notes`) **tidak tertimpa** saat `php spark module:sync` dijalankan.

Setelah menjalankan `module:sync`, `sigapp-docs` **WAJIB** membuka dan memperbarui bagian-bagian berikut di `docs/modules/<NamaModul>/MODULE.md`:

#### 1. `## Key Functions` (Fungsi-Fungsi Penting per Layer)
Dokumentasikan fungsi/method krusial di Controller, Service, Repository, Model, maupun JS beserta tanggung jawab bisnisnya:
```markdown
## Key Functions
- **Controller (`App\Controllers\Api\<Modul>\<Modul>Controller`):**
  - `save()` / `store()`: Validasi input request dan delegasi penyimpanan ke Service.
- **Service (`App\Services\<Modul>\<Modul>Service`):**
  - `namaMethodPenting(array $data)`: Logika bisnis utama, kalkulasi ulang finansial dari DB via `cleanNumeric()`, dan transaksi DB (`transStart`/`transComplete`).
- **Repository (`App\Repositories\<Modul>\<Modul>Repository`):**
  - `getDatatableData(array $params)`: Query JOIN kompleks dan filter server-side DataTables.
- **Model (`App\Models\<Modul>Model`):**
  - Definisi entitas dan akses tulis eksklusif ke tabel pemilik (`owner_tables`).
```

#### 2. `## Deprecated & Legacy Shims` (Fungsi / Endpoint / Shim Deprecated)
Catat seluruh fungsi, method, route lama, atau class shim yang sudah *deprecated* (dipertahankan hanya demi Zero-Downtime) beserta acuan kontrak baru penggantinya:
```markdown
## Deprecated & Legacy Shims
- **Deprecated Routes / Endpoints:**
  - `ANY /<legacy_controller>/<method>` -> **DEPRECATED**, gunakan rute eksplisit `POST /api/<modul>/<endpoint>`.
- **Deprecated Classes / Shims:**
  - `app/Services/<Modul>Service.php` (Root Namespace Shim) -> **DEPRECATED**, gunakan `App\Services\<Modul>\<Modul>Service`.
- **Deprecated Methods:**
  - `namaMethodLama()` -> **DEPRECATED**, digantikan oleh `namaMethodBaru()`.
```
*(Jika tidak ada fungsi/route deprecated selain shim standar, tuliskan secara eksplisit statusnya).*

#### 3. `## Notes` (Termasuk Cross-Module Callers / Public Contract Consumers)
Pastikan daftar pemanggil lintas modul (*Cross-Module Callers*) yang mengonsumsi *Public Contracts* modul ini (baik via PHP Service maupun REST API `/api/...`) tercatat dengan akurat.

#### 4. `## Change Log` (Riwayat Perubahan Modul)
Tambahkan baris/poin baru di urutan paling atas pada bagian `## Change Log` setiap kali tugas selesai:
```markdown
## Change Log
- **`YYYY-MM-DD` [`FEATURE` | `BUGFIX` | `REFACTOR` | `UI_REPORT`] — [Judul Singkat Tugas]:**
  - **Routes & Public Contracts:** [Rute/kontrak yang ditambah/diubah, atau Tidak berubah]
  - **Controller / Service / Repo / Model:** [File & fungsi backend yang ditambah/diubah]
  - **View / CSS / JS:** [File view/asset yang ditambah/diubah]
  - **Deprecations:** [Fungsi/route yang di-deprecate jika ada, atau Tidak ada]
```

### Opsi `--check-only`:
Gunakan opsi ini jika hanya ingin memeriksa apakah `MODULE.md` sudah ada:
```bash
php spark module:sync <NamaModul> --check-only
```

---

## 2. Pembaruan Global Migration Ledger (`docs/MIGRATION_PROGRESS.md`) [Khusus Refaktor Modul]

File [docs/MIGRATION_PROGRESS.md](file:///c:/Users/Salivs/Data/laragon/www/sigapp.dev/docs/MIGRATION_PROGRESS.md) adalah sumber kebenaran tunggal (*Single Source of Truth*) untuk melacak progres migrasi modular sistem SIGAPP.

### Aturan Adaptif:
- **KHUSUS Mode `REFACTOR`:** Perbarui file ini HANYA jika tugas adalah penyelesaian migrasi/refaktor modul penuh menjadi arsitektur baru.
- **TIDAK PERLU untuk `FEATURE`, `BUGFIX`, atau `UI_REPORT`:** Penambahan fitur biasa, perbaikan bug harian, atau penyesuaian UI tidak mengubah papan status migrasi modular.

### Jika Memperbarui untuk Mode `REFACTOR`:
1. **Perbarui Tabel Papan Status Modul:**
   Cari baris modul yang bersangkutan pada tabel Bagian 2, lalu perbarui kolom:
   - **Status**: Ubah menjadi `COMPLETED`.
   - **Sesi / Commit Hash**: Isi dengan commit hash terbaru (misal: `c717d6c`) atau penanda sesi.
   - **Terakhir Diverifikasi**: Isi dengan tanggal hari verifikasi dilakukan (`YYYY-MM-DD`).
2. **Perbarui Rangkuman Progres Global:**
   Sesuaikan angka di Bagian 1:
   ```markdown
   * **Total Modul:** 16
   * **Status:** `X Selesai` | `0 Sedang Dikerjakan` | `Y Antre (Pending)`
   ```

---

## 3. Pembaruan Dokumen Notifikasi (`docs/modul-notifikasi.md`) [Kondisional]

Sesuai aturan `sigapp-general-rules.md` dan skill `sigapp-notifikasi`:
- Jika tugas menambahkan `tambah_notif()` baru, mengubah outbox, email digest, atau web push:
- Agen **WAJIB** membuka dan memperbarui file [docs/modul-notifikasi.md](file:///c:/Users/Salivs/Data/laragon/www/sigapp.dev/docs/modul-notifikasi.md) dengan mencatat:
  1. Trigger event baru
  2. Penerima / role target
  3. Pesan template & level prioritas

---

## 4. Finalisasi Dokumen & Log Pekerjaan (`docs/YYYY-MM-DD-<slug-nama-pekerjaan>.md`) [WAJIB SEMUA MODE]

Setiap tugas (`FEATURE`, `BUGFIX`, `REFACTOR`, maupun `UI_REPORT`) wajib terekam dalam file log pekerjaan di `docs/` dengan konvensi nama `docs/YYYY-MM-DD-<slug-nama-pekerjaan>.md` (tanggal mulai lokal + slug *kebab-case* huruf kecil; contoh aktual: `docs/2026-11-21-revamp-request-kavling-dan-badge-nup.md`).

### Checklist Audit & Finalisasi oleh `sigapp-docs`:
1. Buka file `docs/YYYY-MM-DD-<slug-nama-pekerjaan>.md` yang diinisialisasi oleh Master Orchestrator di awal Fase 2 (jika belum ada, buat baru memakai template standar di `.agents/skills/sigapp-orchestrator/SKILL.md` bagian 3.3).
2. Pastikan **`Status Keseluruhan`** diubah menjadi `COMPLETED` (`DONE — Lulus QA Independen & Sinkronisasi Docs`).
3. Pastikan seluruh baris pada **`## 2. Pelacak Progres per Fase`** berstatus `DONE` (termasuk fase QA `sigapp-qa` dan Docs `sigapp-docs`).
4. Tambahkan entri log Fase 4 ber-timestamp di **`## 3. Log Progres & Debugging`** (`### [YYYY-MM-DD HH:mm] Fase 4 / ... — Sinkronisasi Dokumentasi & Katalog Modul (sigapp-docs) — DONE`) yang merangkum hasil `module:sync`, pembaruan `MODULE.md`, `MIGRATION_PROGRESS.md` (jika `REFACTOR`), dan `modul-notifikasi.md` (jika relevan).
5. Periksa & sinkronkan **`## 4. Ringkasan File Final`** agar cocok 100% dengan output `git status --short` (atau daftar file final aktual pekerjaan).

---

## 5. Pemeriksaan Git & Komitmen Terisolasi

1. Periksa file yang termodifikasi:
   ```bash
   git status
   ```
2. Pastikan file yang berubah **hanya** milik inventori tugas target dan dokumen terkait yang relevan.
3. Dilarang mencampuradukkan perubahan kode tugas/modul lain dalam satu commit yang sama.
