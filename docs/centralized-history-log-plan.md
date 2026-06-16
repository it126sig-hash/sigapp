# Centralized History Log (history_log)

## Context

Saat ini setiap modul punya tabel history sendiri dengan struktur berbeda-beda:

| Tabel | Scope | Kolom inti |
|---|---|---|
| `mkdt_change_history` | per kavling | `id_kavling`, `id_mkdt`, `action`, `summary`, `old_data`, `new_data` |
| `produksi_change_history` | per kavling | `id_kavling`, `id_produksi`, `action`, `summary`, `old_data`, `new_data`, `files` |
| `dana_jaminan_history` | per kavling | `id_kavling`, `id_mkdt`, `id_dana_akad`, `id_pengajuan`, `aksi`, `deskripsi`, `snapshot` |
| `target_siteplan_history` | per target (lintas kavling) | `id_target`, `aksi`, `deskripsi`, `snapshot` |
| `cashout_subkon_history` | per SPK (1 SPK bisa cover banyak kavling) | `id_cashout_subkon`, `status`, `keterangan` |
| `produksi_jalan_progress_history` | per jalan — **TETAP TERPISAH**, tidak digabung | `id_others`, `progres`, `keterangan`, `foto` |

User ingin semua history (kecuali jalan) digabung jadi satu tabel terpusat `history_log`, supaya:
1. Ada satu tempat untuk melihat seluruh riwayat perubahan.
2. Desain cukup fleksibel (kolom JSON) untuk modul baru di masa depan yang strukturnya berbeda-beda.
3. Bisa difilter berdasarkan `module` di halaman "Riwayat Perubahan" baru (terpisah dari tab kavling yang sudah ada).
4. Migrasi dilakukan **backfill + cutover penuh**: data lama disalin ke `history_log`, semua read/write modul dialihkan ke tabel baru, tabel lama dibiarkan ada (arsip/rollback) tapi tidak lagi dipakai aplikasi.

## 1. Tabel baru: `history_log`

Migration: `app/Database/Migrations/2026-06-15-000001_CreateHistoryLogTable.php`

```php
'id'             => INT unsigned, auto_increment, PK
'module'         => VARCHAR(50)   // 'mkdt' | 'produksi' | 'dana_jaminan' | 'target_siteplan' | 'cashout_subkon' | ... (modul baru nanti)
'reference_type' => VARCHAR(50), null   // entitas utama modul: 'mkdt' | 'produksi' | 'dana_akad' | 'pengajuan_jaminan' | 'target_siteplan' | 'cashout_subkon'
'reference_id'   => INT unsigned, null  // id entitas di reference_type
'id_kavling'     => INT unsigned, null, index  // diisi jika perubahan terkait 1 kavling spesifik
'id_proyek'      => INT unsigned, null, index  // untuk filter halaman Riwayat Perubahan per proyek aktif
'action'         => VARCHAR(80)         // kode aksi, mis. 'set_harga_jual', 'pencairan', 'update'
'summary'        => TEXT, null          // deskripsi human-readable (gabungan summary/deskripsi/keterangan)
'old_data'       => LONGTEXT, null      // JSON, data sebelum berubah
'new_data'       => LONGTEXT, null      // JSON, data setelah berubah / snapshot
'metadata'       => LONGTEXT, null      // JSON, field tambahan spesifik modul (files, status code, id_mkdt/id_dana_akad/id_pengajuan, dll)
'add_by'         => INT unsigned, null, index
'created_at'     => DATETIME, null, index
```

Index tambahan: `(module, id_kavling, created_at)`, `(module, id_proyek, created_at)`, `(reference_type, reference_id)`.

`down()`: drop table (table baru, aman).

## 2. Model

`app/Models/HistoryLogModel.php` — model standar CI4, `returnType = 'array'`, `allowedFields` sesuai kolom di atas, `useTimestamps = false` (created_at diisi manual agar konsisten dengan pola modul lain).

## 3. Repository: `app/Repositories/HistoryRepository.php`

- `insert(array $fields): bool` — insert ke `history_log`.
- `getList(array $filters, int $limit, int $offset): array` — filter: `module` (string|array), `id_proyek`, `id_kavling`, `reference_type`, `reference_id`, `date_from`/`date_to`, `action`; join `users` untuk `username`; order `created_at DESC, id DESC`.
- `count(array $filters): int` — hitung total untuk pagination, filter sama seperti `getList`.
- `getByKavling(int $idKavling, array $modules, int $limit, int $offset)` — khusus tab kavling existing (tidak wajib untuk scope sekarang karena UI module-filter ada di halaman baru, tapi disiapkan agar `MkdtHistoryService::getHistory()` bisa pindah ke sini tanpa ubah kontrak).
  - Untuk `cashout_subkon` (relasi many-to-many ke kavling lewat `cashout_subkon_kavling`), tambahkan UNION/JOIN: baris `history_log` dengan `reference_type='cashout_subkon'` ikut tampil jika `reference_id` ada di `cashout_subkon_kavling` untuk `id_kavling` tersebut.

## 4. Service: `app/Services/HistoryService.php`

- `MODULE_LABELS` — map `module` → label tampilan: `mkdt` => 'MKDT / Kavling', `produksi` => 'Produksi', `dana_jaminan` => 'Dana Jaminan', `target_siteplan` => 'Target Siteplan', `cashout_subkon` => 'Cashout Subkon'.
- `log(string $module, array $fields): bool`
  - Normalisasi: `json_encode` untuk `old_data`/`new_data`/`metadata` jika berupa array, default `created_at = date('Y-m-d H:i:s')`, default `add_by = user_id()`.
- `getList(array $filters, int $limit, int $offset): array`
  - Decode JSON kolom, attach `module_label`, return shape: `['data' => [...], 'total' => int, 'limit' => ..., 'offset' => ...]`.
- `getByKavling(...)` — delegasi ke repo, decode JSON, tetap kompatibel dengan shape yang dipakai `MkdtHistoryService::getHistory()` (`history`, `history_total`, `history_limit`, `history_offset`, `history_next_offset`, `history_has_more`).

## 5. Backfill migration (data lama → history_log)

Migration baru: `app/Database/Migrations/2026-06-15-000002_BackfillHistoryLog.php`

Mapping per tabel sumber (`up()` jalankan raw SQL `INSERT INTO history_log (...) SELECT ...`):

- **mkdt_change_history** → `module='mkdt'`, `reference_type='mkdt'`, `reference_id=id_mkdt`, `id_kavling=id_kavling`, `action=action`, `summary=summary`, `old_data=old_data`, `new_data=new_data`, `add_by`, `created_at`. `id_proyek` via join `kavling k JOIN jalan j ON j.id_jalan=k.id_jalan JOIN cluster c ON c.id_cluster=j.id_cluster` → `c.id_proyek`.
- **produksi_change_history** → `module='produksi'`, `reference_type='produksi'`, `reference_id=id_produksi`, `id_kavling=id_kavling`, `action=action`, `summary=summary`, `old_data=old_data`, `new_data=new_data`, `metadata=JSON_OBJECT('files', files)`, `add_by`, `created_at`, `id_proyek` via join sama seperti di atas.
- **dana_jaminan_history** → `module='dana_jaminan'`, `reference_type` = `'pengajuan_jaminan'` jika `id_pengajuan` terisi, selain itu `'dana_akad'`; `reference_id` = `id_pengajuan` atau `id_dana_akad` (yang terisi); `id_kavling=id_kavling`, `action=aksi`, `summary=deskripsi`, `new_data=snapshot`, `metadata=JSON_OBJECT('id_mkdt', id_mkdt, 'id_dana_akad', id_dana_akad, 'id_pengajuan', id_pengajuan)`, `add_by`, `created_at`, `id_proyek` via join kavling sama seperti di atas.
- **target_siteplan_history** → `module='target_siteplan'`, `reference_type='target_siteplan'`, `reference_id=id_target`, `id_kavling=NULL`, `action=aksi`, `summary=deskripsi`, `new_data=snapshot`, `add_by`, `created_at`, `id_proyek` via join `target_siteplan t ON t.id_target = id_target` → `t.id_proyek` (kolom ini sudah ada langsung di `target_siteplan`).
- **cashout_subkon_history** → `module='cashout_subkon'`, `reference_type='cashout_subkon'`, `reference_id=id_cashout_subkon`, `id_kavling=NULL`, `action` = mapping dari `status` (mis. status→'update'/'spk_terbit', detail mapping ditentukan saat implementasi berdasar nilai `status` yang dipakai di `CashoutSubkonRepo`), `summary=keterangan`, `metadata=JSON_OBJECT('status', status)`, `add_by`, `created_at`. `id_proyek` via join `cashout_subkon_kavling csk JOIN kavling k ... JOIN jalan ... JOIN cluster ...` (ambil salah satu / first kavling terkait — cukup untuk filter proyek, karena 1 SPK biasanya dalam 1 proyek).

`down()`: `DELETE FROM history_log WHERE module IN ('mkdt','produksi','dana_jaminan','target_siteplan','cashout_subkon')` (additive-safe, tidak menyentuh tabel lama).

Tabel lama (`mkdt_change_history`, dst.) **tidak dihapus** — dibiarkan sebagai arsip.

## 6. Update writer & reader per modul (cutover)

Untuk masing-masing, ganti implementasi internal agar baca/tulis ke `history_log` via `HistoryService`, **tanpa mengubah kontrak publik** (signature method & shape return tetap sama, supaya controller/JS existing tidak perlu berubah):

- **`app/Services/MkdtHistoryService.php`**
  - `log()`: ganti `MkdtHistoryRepository::insert()` → `HistoryService::log('mkdt', [...])` dengan mapping `id_mkdt`→`reference_id`, `reference_type='mkdt'`, `action`, `summary`, `old_data`, `new_data`.
  - `getHistory()`: ganti `MkdtHistoryRepository::countByKavling/getByKavling` → `HistoryService::getByKavling($idKavling, ['mkdt'], $limit, $offset)`. Pertahankan field `action`, `summary`, `old_data`, `new_data`, `username` pada setiap row (mapping dari `reference_id`/JSON kolom).
  - `app/Repositories/MkdtHistoryRepository.php` — boleh dibiarkan ada (unused) atau dihapus jika tidak dirujuk tempat lain (cek dulu).

- **`app/Repositories/ProduksiRepository.php`**
  - `insertProduksiChangeHistory()`, `countProduksiChangeHistory()`, `getProduksiChangeHistory()`, `hasProduksiChangeHistoryTable()` → delegasi ke `HistoryService`/`HistoryRepository` dengan `module='produksi'`, `reference_type='produksi'`, `reference_id=id_produksi`, `metadata.files` ↔ `files`. Pertahankan nama method agar `app/Controllers/Produksi.php` dan `app/Controllers/Api/ProduksiController.php` tidak perlu berubah.

- **`app/Services/DanaJaminanService.php`**
  - `saveHistory()` (baris ~576) → `HistoryService::log('dana_jaminan', [...])` dengan mapping `aksi`→`action`, `deskripsi`→`summary`, `snapshot`→`new_data`, `id_mkdt/id_dana_akad/id_pengajuan`→`metadata` + `reference_type/reference_id`.
  - `getHistory()` (baris ~431) → query ke `history_log` (via `HistoryRepository::getList(['module'=>'dana_jaminan','id_kavling'=>$idKavling])`) sambil mempertahankan shape return (`token`, `success`, `data` dengan field `aksi`, `deskripsi`, `snapshot`, `username`, `created_at`).

- **`app/Services/TargetSiteplanService.php`** + **`app/Repositories/TargetSiteplanRepository.php`**
  - Insert history (baris ~121) → `HistoryService::log('target_siteplan', [...])` dengan `reference_type='target_siteplan'`, `reference_id=id_target`, `aksi`→`action`, `deskripsi`→`summary`, `snapshot`→`new_data`, `id_proyek` dari `target_siteplan.id_proyek`.
  - `TargetSiteplanRepository::getHistory()` (baris ~67) → query `history_log` filter `module='target_siteplan'`, `reference_id=$idTarget`. Pertahankan shape (`aksi`, `deskripsi`, `snapshot`, `add_by`, `created_at`).
  - `app/Models/TargetSiteplanHistoryModel.php` — dibiarkan ada (unused) atau dihapus jika tidak dirujuk lagi.

- **`app/Repositories/Keuangan/Cashout/CashoutSubkonRepo.php`**
  - `saveHistory()` (baris ~309) dan dua pemanggilan `cashoutSubkonHistoryModel->save()` (baris ~239, ~248) → `HistoryService::log('cashout_subkon', [...])` dengan `reference_type='cashout_subkon'`, `reference_id=id_cashout_subkon`, `keterangan`→`summary`, `status`→`metadata.status` + `action` (mapping status code → string action, mis. `0`→`'update'`).
  - `getHistoryByIDCashoutSubkon()` (baris ~388) → query `history_log` filter `module='cashout_subkon'`, `reference_id=$id_cashout_subkon`. Pertahankan shape (kolom `keterangan`, `status` dari `metadata`, `username`, `created_at`) supaya `CashoutSubkon::getHistory()` & view `cashout_subkon/index.php` tidak perlu berubah.
  - `app/Models/CashoutSubkonHistoryModel.php` — dibiarkan ada (unused) atau dihapus jika tidak dirujuk lagi.

## 7. Halaman baru: "Riwayat Perubahan"

- **Route** — tambahkan di `app/Config/Routes.php`:
  ```php
  $routes->get('riwayat-perubahan', 'RiwayatPerubahan::index');
  $routes->post('api/riwayat-perubahan/list', 'Api\HistoryController::list');
  ```
- **Controller web**: `app/Controllers/RiwayatPerubahan.php` — render view, kirim daftar modul (dari `HistoryService::MODULE_LABELS`) untuk dropdown filter.
- **Controller API**: `app/Controllers/Api/HistoryController.php::list()` — terima filter `module` (opsional, bisa multi), `date_from`, `date_to`, pagination; scope `id_proyek = active_proyek_id()` (kecuali admin/superadmin bisa lihat semua — ikuti pola `ActiveProyekService::userCanAccess()`); panggil `HistoryService::getList()`; return JSON (DataTables-style atau pagination biasa — ikuti pola existing, mis. `MkdtHistoryService::getHistory()`).
- **View**: `app/Views/riwayat_perubahan/index.php` — tabel/timeline dengan dropdown filter modul (opsi: Semua, MKDT, Produksi, Dana Jaminan, Target Siteplan, Cashout Subkon), filter tanggal, pagination. Tampilkan kolom: tanggal, modul (label), aksi, summary, user, dan tombol "detail" untuk lihat `old_data`/`new_data`/`metadata` (modal JSON viewer sederhana).
- **Menu**: tambahkan entry baru ke tabel `menus` via migration kecil (`name='Riwayat Perubahan'`, `url='riwayat-perubahan'`, `icon='history'`, `parent_id` sesuai grouping yang sesuai — cek `app/Libraries/Menu.php` & `app/Views/template/menu.php` untuk pola existing), plus `menu_roles` agar role yang sesuai (admin/owner) bisa akses — ikuti pola `SiteplanMenuService`.

## Verifikasi

1. Jalankan migration (`php spark migrate`) — pastikan `history_log` terbuat dan backfill berjalan tanpa error (cek jumlah row di `history_log` per `module` cocok dengan jumlah row tabel lama).
2. Uji tiap modul end-to-end di browser:
   - MKDT: ubah harga jual / data konsumen → cek tab "Riwayat Perubahan Kavling" di `mkdt.php` tetap tampil dengan benar (sekarang dari `history_log`).
   - Produksi: update progres/foto → cek history produksi tetap tampil.
   - Dana Jaminan: ajukan/cairkan → cek `PencairanJaminan::history()`.
   - Target Siteplan: simpan target → cek history target.
   - Cashout Subkon: simpan SPK → cek `CashoutSubkon::getHistory()`.
3. Buka halaman baru `/riwayat-perubahan`, uji filter per modul menunjukkan data yang benar dan scoped ke proyek aktif.
4. Pastikan `produksi_jalan_progress_history` (jalan) tidak terpengaruh sama sekali.
