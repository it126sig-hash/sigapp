# Panduan Pengguna - Sistem Refactoring Otomatis

## Daftar Isi

- [Pendahuluan](#pendahuluan)
- [Instalasi dan Setup](#instalasi-dan-setup)
- [Perintah CLI](#perintah-cli)
- [Workflow yang Direkomendasikan](#workflow-yang-direkomendasikan)
- [Backup dan Rollback](#backup-dan-rollback)
- [Troubleshooting](#troubleshooting)

---

## Pendahuluan

Sistem Refactoring Otomatis adalah tool CLI untuk CodeIgniter 4 yang membantu developer melakukan refactoring arsitektur secara sistematis. Sistem ini mentransformasi fat controller menjadi arsitektur **Thin Controller → Service → Repository** sambil memperbaiki kerentanan keamanan.

Fitur utama:
- **Discovery** otomatis semua modul (controller, model, service, repository)
- **Analisis dependensi** antar modul dengan impact score
- **Security scanning** untuk mendeteksi kerentanan (SQL injection, XSS, CSRF, dll)
- **Audit report** tanpa mengubah kode
- **Eksekusi refactoring** dengan backup dan rollback otomatis
- **Progress tracking** untuk memantau kemajuan refactoring

---

## Instalasi dan Setup

Tidak diperlukan instalasi khusus. Sistem ini sudah terintegrasi sebagai library di dalam project CodeIgniter 4.

### Prasyarat

- PHP 8.1 atau lebih tinggi
- CodeIgniter 4.x
- Akses CLI ke project

### Verifikasi

Jalankan perintah berikut untuk memastikan semua command tersedia:

```bash
php spark list
```

Anda akan melihat group **Refactor** dengan 8 perintah yang tersedia.

---

## Perintah CLI

### 1. `php spark refactor:discover`

Memindai seluruh aplikasi untuk menemukan semua modul (controller, model, service, repository).

**Penggunaan:**
```bash
php spark refactor:discover
php spark refactor:discover --path /custom/app/path
```

**Opsi:**
| Opsi | Deskripsi |
|------|-----------|
| `--path` | Path aplikasi yang akan dipindai (default: APPPATH) |

**Output:**
- Menampilkan jumlah controller, model, service, dan repository yang ditemukan
- Menyimpan inventory ke `writable/refactor/module_inventory.json`

**Contoh output:**
```
=== Module Discovery ===
Scanning application for modules...

Discovery Complete!

Summary:
  Controllers:   25
  Models:        18
  Services:      3
  Repositories:  2
  Total Modules: 25

Discovered Modules:
  [Transaksi] - 8 methods, 2 models
  [Keuangan] - 6 methods, 1 models
  [Kavling] - 5 methods, 1 models
  ...

Module inventory saved to: writable/refactor/module_inventory.json
```

---

### 2. `php spark refactor:analyze`

Menganalisis dependensi antar modul dan menghitung impact score.

**Penggunaan:**
```bash
php spark refactor:analyze
php spark refactor:analyze --inventory /path/to/inventory.json
```

**Opsi:**
| Opsi | Deskripsi |
|------|-----------|
| `--inventory` | Path ke file module inventory JSON |

**Prasyarat:** Jalankan `refactor:discover` terlebih dahulu.

**Output:**
- Menampilkan ringkasan dependency graph (nodes, edges, circular dependencies)
- Menampilkan impact score per modul
- Menyimpan graph ke `writable/refactor/dependency_graph.json`
- Menyimpan diagram Mermaid ke `writable/refactor/dependency_graph.mmd`

**Contoh output:**
```
=== Dependency Analysis ===

Loading module inventory...
Loaded 25 modules.

Analyzing dependencies...
Analysis Complete!

Dependency Graph Summary:
  Nodes (modules): 25
  Edges (dependencies): 42
  Circular dependencies: 1

Impact Scores (higher = more modules depend on it):
  Konsumen: 8 ████████
  Keuangan: 5 █████
  Kavling: 3 ███
  Transaksi: 0

⚠ Circular Dependencies Detected:
  Keuangan → Transaksi → Keuangan

Dependency graph saved to: writable/refactor/dependency_graph.json
Mermaid diagram saved to: writable/refactor/dependency_graph.mmd
```

---

### 3. `php spark refactor:prioritize`

Menghitung priority score dan merekomendasikan urutan refactoring.

**Penggunaan:**
```bash
php spark refactor:prioritize
php spark refactor:prioritize --graph /path/to/graph.json --inventory /path/to/inventory.json
```

**Opsi:**
| Opsi | Deskripsi |
|------|-----------|
| `--graph` | Path ke dependency graph JSON |
| `--inventory` | Path ke module inventory JSON |

**Prasyarat:** Jalankan `refactor:discover` dan `refactor:analyze` terlebih dahulu.

**Output:**
- Klasifikasi modul: LEAF (aman untuk mulai), CORE (risiko tinggi), INTERMEDIATE
- Urutan refactoring yang direkomendasikan berdasarkan score

**Contoh output:**
```
=== Module Prioritization ===

Loading dependency graph and inventory...
Calculating priority scores...

Module Classification:

  LEAF Modules (safe starting points, no dependents):
    • ChecklistGroup
    • ConfigColor
    • CashOut

  CORE Modules (high-risk, many dependents):
    • Konsumen
    • Keuangan

  INTERMEDIATE Modules:
    • Transaksi
    • Kavling
    • DanaAkad

Recommended Refactoring Order:

  1. [LEAF] ChecklistGroup (score: 85.50)
  2. [LEAF] ConfigColor (score: 82.30)
  3. [LEAF] CashOut (score: 78.00)
  4. [INTERMEDIATE] Transaksi (score: 65.20)
  5. [INTERMEDIATE] Kavling (score: 60.10)
  6. [CORE] Keuangan (score: 45.00)
  7. [CORE] Konsumen (score: 30.50)

Prioritization complete!
```

---

### 4. `php spark refactor:scan [module]`

Memindai modul untuk mendeteksi kerentanan keamanan.

**Penggunaan:**
```bash
# Scan satu modul
php spark refactor:scan Transaksi

# Scan semua modul
php spark refactor:scan

# Dengan custom inventory path
php spark refactor:scan Transaksi --inventory /path/to/inventory.json
```

**Argumen:**
| Argumen | Deskripsi |
|---------|-----------|
| `module` | Nama modul yang akan dipindai (opsional, scan semua jika kosong) |

**Opsi:**
| Opsi | Deskripsi |
|------|-----------|
| `--inventory` | Path ke module inventory JSON |

**Prasyarat:** Jalankan `refactor:discover` terlebih dahulu.

**Output:**
- Menampilkan jumlah kerentanan per modul
- Ringkasan berdasarkan severity (CRITICAL, HIGH, MEDIUM, LOW)
- Menyimpan report ke `writable/refactor/security_reports/`

**Contoh output:**
```
=== Security Scanner ===

Scanning module: Transaksi

  [Transaksi] 5 vulnerabilities found

Scan Complete!

Vulnerability Summary:
  CRITICAL: 2
  HIGH:     1
  MEDIUM:   1
  LOW:      1

  TOTAL:    5

Security reports saved to: writable/refactor/security_reports/
```

---

### 5. `php spark refactor:audit [module]`

Menghasilkan laporan audit detail untuk sebuah modul **tanpa mengubah kode apapun**.

**Penggunaan:**
```bash
php spark refactor:audit Transaksi
php spark refactor:audit Transaksi --inventory /path/to/inventory.json --graph /path/to/graph.json
```

**Argumen:**
| Argumen | Deskripsi |
|---------|-----------|
| `module` | Nama modul yang akan diaudit (wajib) |

**Opsi:**
| Opsi | Deskripsi |
|------|-----------|
| `--inventory` | Path ke module inventory JSON |
| `--graph` | Path ke dependency graph JSON |

**Prasyarat:** Jalankan `refactor:discover` dan `refactor:analyze` terlebih dahulu.

**Output:**
- Analisis struktur controller (method count, LOC, dependencies)
- Business logic yang perlu diekstrak ke Service
- Query yang perlu dipindah ke Repository
- Kerentanan keamanan yang ditemukan
- Rekomendasi refactoring
- Menyimpan report ke `writable/refactor/audits/{module}_audit.md`

**Contoh output:**
```
=== Audit Generator ===

Generating audit for module: Transaksi
(No code will be modified)

Audit Summary:
  Module: Transaksi
  Complexity: COMPLEX

Controller Analysis:
  Methods: 8
  Lines of Code: 450
  Has Business Logic: Yes
  Has Direct Queries: Yes
  Has Validation: Yes

Business Logic to Extract:
  • hitungTotalTransaksi - kalkulasi total dengan diskon
  • prosesApproval - workflow approval multi-level
  • generateNomorTransaksi - generate nomor unik

Queries to Move to Repository:
  • SELECT transaksi with JOIN keuangan
  • UPDATE status transaksi with conditions

Security Issues:
  Critical: 2
  High: 1
  Medium: 1
  Low: 1

Recommendations:
  • Ekstrak business logic ke TransaksiService
  • Pindahkan query ke TransaksiRepository dengan Query Builder
  • Tambahkan CSRF protection pada form submission
  • Gunakan parameter binding untuk mencegah SQL injection

Audit report saved to: writable/refactor/audits/Transaksi_audit.md
```

---

### 6. `php spark refactor:execute [module] [options]`

Menjalankan proses refactoring pada modul yang ditentukan.

**Penggunaan:**
```bash
# Refactoring lengkap
php spark refactor:execute Transaksi

# Skip pembuatan repository
php spark refactor:execute Transaksi --no-repository

# Hanya refactor controller dan service
php spark refactor:execute Transaksi --no-security --no-split

# Jalankan test setelah refactoring
php spark refactor:execute Transaksi --run-tests

# Custom inventory path
php spark refactor:execute Transaksi --inventory /path/to/inventory.json
```

**Argumen:**
| Argumen | Deskripsi |
|---------|-----------|
| `module` | Nama modul yang akan direfactor (wajib) |

**Opsi:**
| Opsi | Deskripsi |
|------|-----------|
| `--no-repository` | Skip pembuatan repository class |
| `--no-service` | Skip pembuatan service class |
| `--no-controller` | Skip refactoring controller |
| `--no-security` | Skip perbaikan keamanan |
| `--no-split` | Skip pemisahan Web/API controller |
| `--run-tests` | Jalankan test setelah refactoring |
| `--inventory` | Path ke module inventory JSON |

**Prasyarat:** Jalankan `refactor:discover` terlebih dahulu. Disarankan juga menjalankan `refactor:audit` untuk review sebelum eksekusi.

**Proses eksekusi:**
1. Membuat backup semua file yang akan dimodifikasi
2. Generate Repository class
3. Generate Service class
4. Refactor Controller (thin controller)
5. Perbaiki kerentanan keamanan
6. Split Web/API controller (jika ada)
7. Jalankan test (jika `--run-tests`)

**Contoh output (sukses):**
```
=== Refactor Execution ===

Module: Transaksi
Refactoring Options:
  Create Repository:    Yes
  Create Service:       Yes
  Refactor Controller:  Yes
  Fix Security:         Yes
  Split Web/API:        Yes
  Run Tests:            No

Proceed with refactoring? [y/n]: y

Starting refactoring...

Refactoring Complete!

Files Created:
  + app/Repositories/TransaksiRepository.php
  + app/Services/TransaksiService.php
  + app/Controllers/Api/TransaksiController.php

Files Modified:
  ~ app/Controllers/Transaksi.php

Steps Completed:
  ✓ Repository Generation
  ✓ Service Generation
  ✓ Controller Refactoring
  ✓ Security Fixes
  ✓ Web/API Split

Backup ID: backup_20240115_112000
Use "php spark refactor:backup --restore backup_20240115_112000" to rollback.
```

**Contoh output (gagal):**
```
Refactoring Failed!
Error: Failed to generate Service: Unable to parse business logic

Would you like to rollback? [y/n]: y
Rollback complete.
```

---

### 7. `php spark refactor:progress`

Menampilkan progress refactoring keseluruhan dan status per modul.

**Penggunaan:**
```bash
# Tampilkan semua
php spark refactor:progress

# Filter berdasarkan status
php spark refactor:progress --status=COMPLETED
php spark refactor:progress --status=NOT_STARTED
php spark refactor:progress --status=FAILED
```

**Opsi:**
| Opsi | Deskripsi |
|------|-----------|
| `--status` | Filter berdasarkan status (NOT_STARTED, AUDITED, IN_PROGRESS, COMPLETED, FAILED) |

**Contoh output:**
```
=== Refactoring Progress ===

Overall Progress: [████████░░░░░░░░░░░░░░░░░░░░░░] 26.7%

Status Summary:
  Completed:   4
  In Progress: 1
  Audited:     3
  Failed:      0
  Not Started: 7

Module Details:

  Transaksi                     [COMPLETED   ] audited: 2024-01-15 refactored: 2024-01-15
  ChecklistGroup                [COMPLETED   ] audited: 2024-01-14 refactored: 2024-01-14
  ConfigColor                   [COMPLETED   ] audited: 2024-01-14 refactored: 2024-01-14
  CashOut                       [COMPLETED   ] audited: 2024-01-15 refactored: 2024-01-15
  Keuangan                      [IN_PROGRESS ] audited: 2024-01-16
  Kavling                       [AUDITED     ] audited: 2024-01-16
  DanaAkad                      [AUDITED     ] audited: 2024-01-16
  Konsumen                      [AUDITED     ] audited: 2024-01-16
  Direksi                       [NOT_STARTED ]
  Cluster                       [NOT_STARTED ]
  ...
```

---

### 8. `php spark refactor:backup`

Mengelola backup yang dibuat selama proses refactoring.

**Penggunaan:**
```bash
# List semua backup
php spark refactor:backup

# Restore backup
php spark refactor:backup --restore backup_20240115_112000

# Hapus backup
php spark refactor:backup --delete backup_20240115_112000
```

**Opsi:**
| Opsi | Deskripsi |
|------|-----------|
| `--restore` | Restore backup berdasarkan ID |
| `--delete` | Hapus backup berdasarkan ID |

**Contoh output (list):**
```
=== Backup Management ===

Found 3 backup(s):

  ID: backup_20240115_112000
    Module:      Transaksi
    Created:     2024-01-15 11:20:00
    Files:       4
    Description: Pre-refactoring backup

  ID: backup_20240115_140000
    Module:      Keuangan
    Created:     2024-01-15 14:00:00
    Files:       3
    Description: Pre-refactoring backup

Usage:
  Restore: php spark refactor:backup --restore <backupId>
  Delete:  php spark refactor:backup --delete <backupId>
```

---

## Workflow yang Direkomendasikan

Berikut adalah urutan langkah yang direkomendasikan untuk melakukan refactoring:

### Langkah 1: Discovery

```bash
php spark refactor:discover
```

Pindai seluruh aplikasi untuk membangun inventory modul.

### Langkah 2: Analyze

```bash
php spark refactor:analyze
```

Analisis dependensi antar modul dan hitung impact score.

### Langkah 3: Prioritize

```bash
php spark refactor:prioritize
```

Tentukan urutan refactoring berdasarkan prioritas. Mulai dari modul **LEAF** (tanpa dependents) untuk meminimalkan risiko.

### Langkah 4: Scan (Opsional)

```bash
php spark refactor:scan
```

Scan kerentanan keamanan di semua modul untuk gambaran umum.

### Langkah 5: Audit

```bash
php spark refactor:audit NamaModul
```

Review audit report sebelum melakukan refactoring. Pastikan Anda memahami apa yang akan diubah.

### Langkah 6: Execute

```bash
php spark refactor:execute NamaModul
```

Eksekusi refactoring. Sistem akan membuat backup otomatis sebelum mengubah file apapun.

### Langkah 7: Verifikasi

Setelah refactoring:
1. Periksa file yang dihasilkan
2. Jalankan test jika ada
3. Test manual di browser
4. Jika ada masalah, rollback dengan `php spark refactor:backup --restore <backupId>`

### Langkah 8: Ulangi

Ulangi langkah 5-7 untuk modul berikutnya sesuai urutan prioritas.

---

## Backup dan Rollback

### Mekanisme Backup

Setiap kali `refactor:execute` dijalankan, sistem secara otomatis:
1. Membuat backup semua file yang akan dimodifikasi
2. Menyimpan backup dengan timestamp dan metadata
3. Menampilkan Backup ID yang bisa digunakan untuk rollback

### Cara Rollback

Jika refactoring menyebabkan masalah:

```bash
# Lihat daftar backup
php spark refactor:backup

# Restore ke kondisi sebelum refactoring
php spark refactor:backup --restore backup_20240115_112000
```

### Rollback Otomatis

Jika proses refactoring gagal di tengah jalan, sistem akan:
1. Mendeteksi kegagalan
2. Menanyakan apakah ingin rollback
3. Jika ya, mengembalikan semua file ke kondisi semula

### Membersihkan Backup Lama

Setelah yakin refactoring berhasil, hapus backup yang tidak diperlukan:

```bash
php spark refactor:backup --delete backup_20240115_112000
```

---

## Troubleshooting

### Error: "Module inventory not found"

**Penyebab:** Anda belum menjalankan discovery.

**Solusi:**
```bash
php spark refactor:discover
```

### Error: "Dependency graph not found"

**Penyebab:** Anda belum menjalankan analisis dependensi.

**Solusi:**
```bash
php spark refactor:analyze
```

### Error: "Module 'X' not found in inventory"

**Penyebab:** Nama modul tidak cocok dengan yang ada di inventory. Nama modul case-sensitive.

**Solusi:** Periksa nama modul yang tersedia:
```bash
php spark refactor:discover
```

### Error: "Failed to create backup"

**Penyebab:** Tidak ada izin menulis ke direktori `writable/refactor/backups/`.

**Solusi:**
```bash
chmod -R 775 writable/refactor/
```

### Error: "Failed to parse file"

**Penyebab:** File PHP memiliki syntax error yang tidak bisa di-parse.

**Solusi:** Perbaiki syntax error di file tersebut terlebih dahulu, lalu jalankan ulang discovery.

### Refactoring gagal di tengah proses

**Solusi:**
1. Sistem akan menanyakan apakah ingin rollback
2. Jika memilih "y", semua file dikembalikan ke kondisi semula
3. Jika memilih "n", backup tetap tersimpan dan bisa di-restore nanti

### Circular dependency terdeteksi

**Penyebab:** Dua atau lebih modul saling bergantung satu sama lain.

**Solusi:** Ini adalah warning, bukan error fatal. Anda tetap bisa melanjutkan refactoring, tapi perlu hati-hati dengan urutan refactoring modul yang terlibat circular dependency.

### File yang dihasilkan tidak sesuai harapan

**Solusi:**
1. Rollback dengan `php spark refactor:backup --restore <backupId>`
2. Jalankan audit ulang: `php spark refactor:audit NamaModul`
3. Review rekomendasi audit
4. Jalankan execute dengan opsi yang lebih spesifik, misalnya `--no-split` jika tidak perlu pemisahan Web/API

### Progress tidak terupdate

**Penyebab:** File progress tracker mungkin corrupt.

**Solusi:** File progress disimpan di `writable/refactor/progress.json`. Anda bisa menghapus file ini dan menjalankan ulang proses dari awal.
