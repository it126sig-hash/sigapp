# Contoh Walkthrough - Refactoring Modul Transaksi

Dokumen ini menunjukkan langkah demi langkah proses refactoring modul Transaksi dari fat controller menjadi arsitektur Thin Controller, Service, dan Repository.

## Daftar Isi

- [Kondisi Awal (Before)](#kondisi-awal-before)
- [Langkah 1: Discovery](#langkah-1-discovery)
- [Langkah 2: Analyze](#langkah-2-analyze)
- [Langkah 3: Prioritize](#langkah-3-prioritize)
- [Langkah 4: Security Scan](#langkah-4-security-scan)
- [Langkah 5: Audit](#langkah-5-audit)
- [Langkah 6: Execute](#langkah-6-execute)
- [Hasil Akhir (After)](#hasil-akhir-after)

---

## Kondisi Awal (Before)

### Controller: app/Controllers/Transaksi.php

Berikut adalah contoh fat controller yang mencampur HTTP handling, business logic, dan database queries dalam satu file:

```php
<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\TransaksiModel;
use App\Models\KeuanganModel;

class Transaksi extends BaseController
{
    protected $transaksiModel;
    protected $keuanganModel;

    public function __construct()
    {
        $this->transaksiModel = new TransaksiModel();
        $this->keuanganModel = new KeuanganModel();
    }

    /**
     * Simpan transaksi baru - MASALAH: business logic + query + validation
     * semua tercampur di controller
     */
    public function simpan()
    {
        // Validation langsung di controller
        $rules = [
            'nominal' => 'required|numeric|greater_than[0]',
            'keterangan' => 'required|min_length[3]',
            'tanggal' => 'required|valid_date',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // Business logic di controller
        $nominal = $this->request->getPost('nominal');
        $diskon = $this->request->getPost('diskon') ?? 0;
        $ppn = $nominal * 0.11;
        $totalBayar = ($nominal - $diskon) + $ppn;

        // Generate nomor transaksi (business logic)
        $tahun = date('Y');
        $bulan = date('m');
        $lastNumber = $this->transaksiModel->where('YEAR(created_at)', $tahun)
            ->countAllResults();
        $nomorTransaksi = "TRX/{$tahun}/{$bulan}/" . str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);

        // Raw SQL query - KERENTANAN SQL INJECTION
        $db = \Config\Database::connect();
        $query = "SELECT * FROM kavling WHERE id = " . $this->request->getPost('kavling_id');
        $kavling = $db->query($query)->getRow();

        // Simpan tanpa CSRF protection
        $data = [
            'nomor_transaksi' => $nomorTransaksi,
            'kavling_id' => $this->request->getPost('kavling_id'),
            'nominal' => $nominal,
            'diskon' => $diskon,
            'ppn' => $ppn,
            'total_bayar' => $totalBayar,
            'keterangan' => $this->request->getPost('keterangan'),
            'status' => 'PENDING',
        ];

        $this->transaksiModel->insert($data);

        // Update keuangan (business logic)
        $this->keuanganModel->where('kavling_id', $data['kavling_id'])
            ->set('saldo', "saldo + {$totalBayar}", false)
            ->update();

        return redirect()->to('/transaksi')->with('success', 'Transaksi berhasil disimpan');
    }

    /**
     * List transaksi - MASALAH: raw query, no escaping
     */
    public function index()
    {
        $search = $this->request->getGet('search');

        // Raw query dengan input user - SQL INJECTION
        $db = \Config\Database::connect();
        $sql = "SELECT t.*, k.nama_kavling FROM transaksi t
                JOIN kavling k ON t.kavling_id = k.id
                WHERE t.keterangan LIKE '%{$search}%'
                ORDER BY t.created_at DESC";
        $transaksi = $db->query($sql)->getResult();

        // XSS: data langsung ke view tanpa escaping
        return view('transaksi/index', [
            'transaksi' => $transaksi,
            'search' => $search,
        ]);
    }

    /**
     * API endpoint - tercampur dengan web controller
     */
    public function apiList()
    {
        $transaksi = $this->transaksiModel->findAll();
        return $this->response->setJSON([
            'status' => 'success',
            'data' => $transaksi,
        ]);
    }

    /**
     * API detail
     */
    public function apiDetail($id)
    {
        $transaksi = $this->transaksiModel->find($id);
        if (!$transaksi) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Transaksi tidak ditemukan',
            ])->setStatusCode(404);
        }
        return $this->response->setJSON([
            'status' => 'success',
            'data' => $transaksi,
        ]);
    }
}
```

### Masalah yang Teridentifikasi

1. **SQL Injection** - Raw query dengan input user tanpa parameter binding
2. **XSS** - Data user langsung dikirim ke view tanpa escaping
3. **CSRF Missing** - Form submission tanpa CSRF protection
4. **Fat Controller** - Business logic (kalkulasi, generate nomor) di controller
5. **Mixed Concerns** - Web dan API endpoint dalam satu controller
6. **No Repository** - Database query langsung di controller

---

## Langkah 1: Discovery

```bash
$ php spark refactor:discover
```

**Output:**

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
  [Transaksi] - 4 methods, 2 models
  [Keuangan] - 6 methods, 1 models
  [Kavling] - 5 methods, 1 models
  [Konsumen] - 7 methods, 2 models
  [CashOut] - 3 methods, 1 models
  [ChecklistGroup] - 4 methods, 1 models
  ...

Module inventory saved to: writable/refactor/module_inventory.json
```

---

## Langkah 2: Analyze

```bash
$ php spark refactor:analyze
```

**Output:**

```
=== Dependency Analysis ===

Loading module inventory...
Loaded 25 modules.

Analyzing dependencies...
Analysis Complete!

Dependency Graph Summary:
  Nodes (modules): 25
  Edges (dependencies): 38
  Circular dependencies: 0

Impact Scores (higher = more modules depend on it):
  Konsumen: 6 ██████
  Keuangan: 4 ████
  Kavling: 3 ███
  Transaksi: 1 █
  CashOut: 0
  ChecklistGroup: 0

Dependency graph saved to: writable/refactor/dependency_graph.json
Mermaid diagram saved to: writable/refactor/dependency_graph.mmd
```

---

## Langkah 3: Prioritize

```bash
$ php spark refactor:prioritize
```

**Output:**

```
=== Module Prioritization ===

Loading dependency graph and inventory...
Calculating priority scores...

Module Classification:

  LEAF Modules (safe starting points, no dependents):
    * CashOut
    * ChecklistGroup
    * ConfigColor
    * Transaksi

  CORE Modules (high-risk, many dependents):
    * Konsumen
    * Keuangan

  INTERMEDIATE Modules:
    * Kavling
    * DanaAkad
    * Direksi

Recommended Refactoring Order:

  1. [LEAF] CashOut (score: 88.50)
  2. [LEAF] ChecklistGroup (score: 85.20)
  3. [LEAF] ConfigColor (score: 82.00)
  4. [LEAF] Transaksi (score: 78.30)
  5. [INTERMEDIATE] Kavling (score: 62.10)
  6. [INTERMEDIATE] DanaAkad (score: 55.40)
  7. [CORE] Keuangan (score: 40.20)
  8. [CORE] Konsumen (score: 32.50)

Prioritization complete!
```

Modul Transaksi termasuk LEAF (tidak ada modul lain yang bergantung padanya), sehingga aman untuk direfactor lebih awal.

---

## Langkah 4: Security Scan

```bash
$ php spark refactor:scan Transaksi
```

**Output:**

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

### Contoh Security Report (JSON)

```json
{
  "moduleName": "Transaksi",
  "scannedAt": "2024-01-15T10:35:00+07:00",
  "vulnerabilities": [
    {
      "type": "SQL_INJECTION",
      "severity": "CRITICAL",
      "filePath": "app/Controllers/Transaksi.php",
      "lineNumber": 52,
      "description": "Raw SQL query with user input concatenation in simpan()",
      "recommendation": "Use Query Builder with parameter binding",
      "codeSnippet": "$query = \"SELECT * FROM kavling WHERE id = \" . $this->request->getPost('kavling_id');"
    },
    {
      "type": "SQL_INJECTION",
      "severity": "CRITICAL",
      "filePath": "app/Controllers/Transaksi.php",
      "lineNumber": 78,
      "description": "Raw SQL query with user input in index() search",
      "recommendation": "Use Query Builder with parameter binding",
      "codeSnippet": "WHERE t.keterangan LIKE '%{$search}%'"
    },
    {
      "type": "CSRF_MISSING",
      "severity": "HIGH",
      "filePath": "app/Controllers/Transaksi.php",
      "lineNumber": 25,
      "description": "Form submission in simpan() without CSRF protection",
      "recommendation": "Add CSRF filter to route or use csrf_field() in form"
    },
    {
      "type": "XSS",
      "severity": "MEDIUM",
      "filePath": "app/Controllers/Transaksi.php",
      "lineNumber": 85,
      "description": "Unescaped user input passed to view",
      "recommendation": "Use esc() helper for all user-provided output in views"
    },
    {
      "type": "MISSING_VALIDATION",
      "severity": "LOW",
      "filePath": "app/Controllers/Transaksi.php",
      "lineNumber": 95,
      "description": "API endpoint apiList() has no input validation",
      "recommendation": "Add request validation for API parameters"
    }
  ]
}
```

---

## Langkah 5: Audit

```bash
$ php spark refactor:audit Transaksi
```

**Output:**

```
=== Audit Generator ===

Generating audit for module: Transaksi
(No code will be modified)

Audit Summary:
  Module: Transaksi
  Complexity: COMPLEX

Controller Analysis:
  Methods: 4
  Lines of Code: 120
  Has Business Logic: Yes
  Has Direct Queries: Yes
  Has Validation: Yes

Business Logic to Extract:
  * hitungTotal - kalkulasi nominal, diskon, PPN, total bayar
  * generateNomorTransaksi - generate nomor transaksi unik
  * updateSaldoKeuangan - update saldo setelah transaksi

Queries to Move to Repository:
  * findKavlingById - SELECT kavling by ID
  * searchTransaksiWithKavling - SELECT transaksi JOIN kavling with search
  * getLastTransactionNumber - COUNT transaksi per tahun

Security Issues:
  Critical: 2
  High: 1
  Medium: 1
  Low: 1

Recommendations:
  * Ekstrak business logic ke TransaksiService
  * Pindahkan query ke TransaksiRepository dengan Query Builder
  * Tambahkan CSRF protection pada route POST
  * Gunakan parameter binding untuk semua query
  * Pisahkan API endpoint ke app/Controllers/Api/TransaksiController.php
  * Tambahkan esc() pada semua output ke view

Audit report saved to: writable/refactor/audits/Transaksi_audit.md
```

### Contoh Audit Report (Markdown)

File `writable/refactor/audits/Transaksi_audit.md`:

```markdown
# Audit Report: Transaksi

Generated: 2024-01-15 10:40:00

## Ringkasan

| Metrik | Nilai |
|--------|-------|
| Kompleksitas | COMPLEX |
| Jumlah Method | 4 |
| Lines of Code | 120 |
| Business Logic di Controller | Ya |
| Direct Queries | Ya |
| Kerentanan Keamanan | 5 |

## Business Logic yang Perlu Diekstrak

### 1. Kalkulasi Total Transaksi
- Lokasi: simpan(), baris 35-38
- Logika: nominal - diskon + PPN (11%)
- Target: TransaksiService::hitungTotal()

### 2. Generate Nomor Transaksi
- Lokasi: simpan(), baris 41-45
- Logika: Format TRX/YYYY/MM/NNNN
- Target: TransaksiService::generateNomorTransaksi()

### 3. Update Saldo Keuangan
- Lokasi: simpan(), baris 65-67
- Logika: Tambah saldo kavling setelah transaksi
- Target: TransaksiService::updateSaldoKeuangan()

## Query yang Perlu Dipindah ke Repository

### 1. Find Kavling by ID
- Lokasi: simpan(), baris 52
- Query: SELECT * FROM kavling WHERE id = ?
- Target: TransaksiRepository::findKavlingById()

### 2. Search Transaksi with Kavling
- Lokasi: index(), baris 78-81
- Query: SELECT t.*, k.nama_kavling FROM transaksi t JOIN kavling...
- Target: TransaksiRepository::searchWithKavling()

## Kerentanan Keamanan

| # | Tipe | Severity | Baris | Rekomendasi |
|---|------|----------|-------|-------------|
| 1 | SQL_INJECTION | CRITICAL | 52 | Gunakan Query Builder |
| 2 | SQL_INJECTION | CRITICAL | 78 | Gunakan parameter binding |
| 3 | CSRF_MISSING | HIGH | 25 | Tambah CSRF filter |
| 4 | XSS | MEDIUM | 85 | Gunakan esc() |
| 5 | MISSING_VALIDATION | LOW | 95 | Tambah validasi API |

## Rekomendasi Refactoring

1. Buat `app/Repositories/TransaksiRepository.php`
2. Buat `app/Services/TransaksiService.php`
3. Refactor `app/Controllers/Transaksi.php` menjadi thin controller
4. Buat `app/Controllers/Api/TransaksiController.php` untuk API endpoints
5. Perbaiki semua kerentanan keamanan
```

---

## Langkah 6: Execute

```bash
$ php spark refactor:execute Transaksi
```

**Output:**

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

[Step 1/5] Creating backup...
  Backup created: backup_20240115_112000

[Step 2/5] Generating Repository...
  Created: app/Repositories/TransaksiRepository.php

[Step 3/5] Generating Service...
  Created: app/Services/TransaksiService.php

[Step 4/5] Refactoring Controller...
  Modified: app/Controllers/Transaksi.php

[Step 5/5] Splitting Web/API...
  Created: app/Controllers/Api/TransaksiController.php

Refactoring Complete!

Files Created:
  + app/Repositories/TransaksiRepository.php
  + app/Services/TransaksiService.php
  + app/Controllers/Api/TransaksiController.php

Files Modified:
  ~ app/Controllers/Transaksi.php

Steps Completed:
  v Repository Generation
  v Service Generation
  v Controller Refactoring
  v Security Fixes
  v Web/API Split

Backup ID: backup_20240115_112000
Use "php spark refactor:backup --restore backup_20240115_112000" to rollback.
```

---

## Hasil Akhir (After)

### Repository: app/Repositories/TransaksiRepository.php

```php
<?php

namespace App\Repositories;

use CodeIgniter\Database\BaseConnection;

/**
 * TransaksiRepository
 *
 * Repository untuk operasi database modul Transaksi.
 * Menggunakan Query Builder untuk keamanan dan konsistensi.
 *
 * @package App\Repositories
 */
class TransaksiRepository
{
    /**
     * Database connection instance
     *
     * @var BaseConnection
     */
    private BaseConnection $db;

    /**
     * Constructor
     *
     * @param BaseConnection|null $db Database connection
     */
    public function __construct(?BaseConnection $db = null)
    {
        $this->db = $db ?? \Config\Database::connect();
    }

    /**
     * Cari kavling berdasarkan ID
     *
     * @param int $kavlingId ID kavling
     * @return object|null
     */
    public function findKavlingById(int $kavlingId): ?object
    {
        return $this->db->table('kavling')
            ->where('id', $kavlingId)
            ->get()
            ->getRow();
    }

    /**
     * Cari transaksi dengan join kavling dan filter pencarian
     *
     * @param string|null $search Kata kunci pencarian
     * @return array
     */
    public function searchWithKavling(?string $search = null): array
    {
        $builder = $this->db->table('transaksi t')
            ->select('t.*, k.nama_kavling')
            ->join('kavling k', 't.kavling_id = k.id');

        if ($search) {
            $builder->like('t.keterangan', $search);
        }

        return $builder->orderBy('t.created_at', 'DESC')
            ->get()
            ->getResult();
    }

    /**
     * Hitung jumlah transaksi pada tahun tertentu
     *
     * @param int $tahun Tahun
     * @return int
     */
    public function countByYear(int $tahun): int
    {
        return $this->db->table('transaksi')
            ->where('YEAR(created_at)', $tahun)
            ->countAllResults();
    }

    /**
     * Simpan transaksi baru
     *
     * @param array $data Data transaksi
     * @return int|string Insert ID
     */
    public function create(array $data): int|string
    {
        $this->db->table('transaksi')->insert($data);
        return $this->db->insertID();
    }

    /**
     * Update saldo keuangan kavling
     *
     * @param int $kavlingId ID kavling
     * @param float $amount Jumlah yang ditambahkan
     * @return bool
     */
    public function updateSaldoKeuangan(int $kavlingId, float $amount): bool
    {
        return $this->db->table('keuangan')
            ->where('kavling_id', $kavlingId)
            ->set('saldo', "saldo + {$amount}", false)
            ->update();
    }

    /**
     * Ambil semua transaksi
     *
     * @return array
     */
    public function findAll(): array
    {
        return $this->db->table('transaksi')
            ->orderBy('created_at', 'DESC')
            ->get()
            ->getResult();
    }

    /**
     * Cari transaksi berdasarkan ID
     *
     * @param int $id ID transaksi
     * @return object|null
     */
    public function findById(int $id): ?object
    {
        return $this->db->table('transaksi')
            ->where('id', $id)
            ->get()
            ->getRow();
    }
}
```

### Service: app/Services/TransaksiService.php

```php
<?php

namespace App\Services;

use App\Repositories\TransaksiRepository;

/**
 * TransaksiService
 *
 * Service layer untuk business logic modul Transaksi.
 * Menangani kalkulasi, validasi bisnis, dan orkestrasi operasi.
 *
 * @package App\Services
 */
class TransaksiService
{
    /**
     * Repository instance
     *
     * @var TransaksiRepository
     */
    private TransaksiRepository $repository;

    /**
     * Tarif PPN
     */
    private const PPN_RATE = 0.11;

    /**
     * Constructor
     *
     * @param TransaksiRepository|null $repository
     */
    public function __construct(?TransaksiRepository $repository = null)
    {
        $this->repository = $repository ?? new TransaksiRepository();
    }

    /**
     * Hitung total transaksi
     *
     * @param float $nominal Nominal transaksi
     * @param float $diskon Diskon
     * @return array{nominal: float, diskon: float, ppn: float, totalBayar: float}
     */
    public function hitungTotal(float $nominal, float $diskon = 0): array
    {
        $ppn = $nominal * self::PPN_RATE;
        $totalBayar = ($nominal - $diskon) + $ppn;

        return [
            'nominal' => $nominal,
            'diskon' => $diskon,
            'ppn' => $ppn,
            'totalBayar' => $totalBayar,
        ];
    }

    /**
     * Generate nomor transaksi unik
     *
     * Format: TRX/YYYY/MM/NNNN
     *
     * @return string
     */
    public function generateNomorTransaksi(): string
    {
        $tahun = date('Y');
        $bulan = date('m');
        $lastNumber = $this->repository->countByYear((int) $tahun);

        return "TRX/{$tahun}/{$bulan}/" . str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Proses simpan transaksi baru
     *
     * @param array $input Data input dari request
     * @return array{success: bool, message: string, data?: array}
     */
    public function simpanTransaksi(array $input): array
    {
        // Validasi kavling exists
        $kavling = $this->repository->findKavlingById((int) $input['kavling_id']);
        if (!$kavling) {
            return [
                'success' => false,
                'message' => 'Kavling tidak ditemukan',
            ];
        }

        // Hitung total
        $kalkulasi = $this->hitungTotal(
            (float) $input['nominal'],
            (float) ($input['diskon'] ?? 0)
        );

        // Generate nomor
        $nomorTransaksi = $this->generateNomorTransaksi();

        // Prepare data
        $data = [
            'nomor_transaksi' => $nomorTransaksi,
            'kavling_id' => (int) $input['kavling_id'],
            'nominal' => $kalkulasi['nominal'],
            'diskon' => $kalkulasi['diskon'],
            'ppn' => $kalkulasi['ppn'],
            'total_bayar' => $kalkulasi['totalBayar'],
            'keterangan' => $input['keterangan'],
            'status' => 'PENDING',
        ];

        // Simpan dengan transaction
        $db = \Config\Database::connect();
        $db->transStart();

        $insertId = $this->repository->create($data);
        $this->repository->updateSaldoKeuangan(
            (int) $input['kavling_id'],
            $kalkulasi['totalBayar']
        );

        $db->transComplete();

        if ($db->transStatus() === false) {
            return [
                'success' => false,
                'message' => 'Gagal menyimpan transaksi',
            ];
        }

        $data['id'] = $insertId;

        return [
            'success' => true,
            'message' => 'Transaksi berhasil disimpan',
            'data' => $data,
        ];
    }

    /**
     * Ambil daftar transaksi dengan pencarian
     *
     * @param string|null $search Kata kunci pencarian
     * @return array
     */
    public function getTransaksiList(?string $search = null): array
    {
        return $this->repository->searchWithKavling($search);
    }

    /**
     * Ambil detail transaksi
     *
     * @param int $id ID transaksi
     * @return array{success: bool, data?: object, message?: string}
     */
    public function getDetail(int $id): array
    {
        $transaksi = $this->repository->findById($id);

        if (!$transaksi) {
            return [
                'success' => false,
                'message' => 'Transaksi tidak ditemukan',
            ];
        }

        return [
            'success' => true,
            'data' => $transaksi,
        ];
    }
}
```

### Controller (Refactored): app/Controllers/Transaksi.php

```php
<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Services\TransaksiService;

/**
 * Transaksi Web Controller
 *
 * Thin controller yang hanya menangani HTTP concerns.
 * Business logic didelegasikan ke TransaksiService.
 *
 * @package App\Controllers
 */
class Transaksi extends BaseController
{
    /**
     * Service instance
     *
     * @var TransaksiService
     */
    private TransaksiService $transaksiService;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->transaksiService = new TransaksiService();
    }

    /**
     * Tampilkan daftar transaksi
     *
     * @return string
     */
    public function index(): string
    {
        $search = $this->request->getGet('search');
        $transaksi = $this->transaksiService->getTransaksiList($search);

        return view('transaksi/index', [
            'transaksi' => $transaksi,
            'search' => esc($search),
        ]);
    }

    /**
     * Simpan transaksi baru
     *
     * @return \CodeIgniter\HTTP\RedirectResponse
     */
    public function simpan(): \CodeIgniter\HTTP\RedirectResponse
    {
        // Validasi input
        $rules = [
            'nominal' => 'required|numeric|greater_than[0]',
            'keterangan' => 'required|min_length[3]',
            'tanggal' => 'required|valid_date',
            'kavling_id' => 'required|integer',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        // Delegasi ke service
        $result = $this->transaksiService->simpanTransaksi(
            $this->request->getPost()
        );

        if (!$result['success']) {
            return redirect()->back()
                ->withInput()
                ->with('error', $result['message']);
        }

        return redirect()->to('/transaksi')
            ->with('success', $result['message']);
    }
}
```

### API Controller: app/Controllers/Api/TransaksiController.php

```php
<?php

namespace App\Controllers\Api;

use App\Controllers\Api\BaseApiController;
use App\Services\TransaksiService;

/**
 * Transaksi API Controller
 *
 * API controller yang mengembalikan response JSON.
 * Extends BaseApiController untuk helper response standar.
 *
 * @package App\Controllers\Api
 */
class TransaksiController extends BaseApiController
{
    /**
     * Service instance
     *
     * @var TransaksiService
     */
    private TransaksiService $transaksiService;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->transaksiService = new TransaksiService();
    }

    /**
     * GET /api/transaksi - List semua transaksi
     *
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function index(): \CodeIgniter\HTTP\ResponseInterface
    {
        $search = $this->request->getGet('search');
        $transaksi = $this->transaksiService->getTransaksiList($search);

        return $this->respond([
            'status' => 'success',
            'data' => $transaksi,
        ]);
    }

    /**
     * GET /api/transaksi/{id} - Detail transaksi
     *
     * @param int $id ID transaksi
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function show(int $id): \CodeIgniter\HTTP\ResponseInterface
    {
        $result = $this->transaksiService->getDetail($id);

        if (!$result['success']) {
            return $this->failNotFound($result['message']);
        }

        return $this->respond([
            'status' => 'success',
            'data' => $result['data'],
        ]);
    }

    /**
     * POST /api/transaksi - Simpan transaksi baru
     *
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function create(): \CodeIgniter\HTTP\ResponseInterface
    {
        // Validasi input
        $rules = [
            'nominal' => 'required|numeric|greater_than[0]',
            'keterangan' => 'required|min_length[3]',
            'tanggal' => 'required|valid_date',
            'kavling_id' => 'required|integer',
        ];

        if (!$this->validate($rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $result = $this->transaksiService->simpanTransaksi(
            $this->request->getJSON(true)
        );

        if (!$result['success']) {
            return $this->fail($result['message']);
        }

        return $this->respondCreated([
            'status' => 'success',
            'message' => $result['message'],
            'data' => $result['data'],
        ]);
    }
}
```

---

## Perbandingan Before vs After

### Ringkasan Perubahan

| Aspek | Before | After |
|-------|--------|-------|
| Arsitektur | Fat Controller | Thin Controller + Service + Repository |
| SQL Injection | Raw query dengan concatenation | Query Builder dengan parameter binding |
| XSS | Data langsung ke view | Menggunakan `esc()` helper |
| CSRF | Tidak ada protection | CSRF filter di route |
| Business Logic | Di controller | Di TransaksiService |
| Database Access | Di controller | Di TransaksiRepository |
| Web/API | Tercampur | Terpisah (Controllers/ dan Controllers/Api/) |
| Error Handling | Minimal | Structured result objects |
| Transaction | Tidak ada | `transStart()`/`transComplete()` |
| Type Hints | Tidak ada | Lengkap di semua method |
| PHPDoc | Minimal | Comprehensive |

### Struktur File

**Before:**
```
app/
  Controllers/
    Transaksi.php          (120 LOC, mixed concerns)
```

**After:**
```
app/
  Controllers/
    Transaksi.php          (55 LOC, HTTP only)
  Controllers/Api/
    TransaksiController.php (70 LOC, JSON responses)
  Services/
    TransaksiService.php   (130 LOC, business logic)
  Repositories/
    TransaksiRepository.php (95 LOC, database access)
```

---

## Tips dan Catatan

1. **Selalu review audit report** sebelum menjalankan execute. Pastikan rekomendasi sesuai dengan kebutuhan.

2. **Mulai dari modul LEAF** yang tidak memiliki dependents. Ini meminimalkan risiko breaking changes.

3. **Test manual setelah refactoring** - Buka browser dan test semua endpoint yang terpengaruh.

4. **Gunakan rollback** jika ada masalah. Backup otomatis dibuat sebelum setiap refactoring.

5. **Refactoring bertahap** - Tidak perlu refactor semua modul sekaligus. Lakukan satu per satu dan verifikasi.

6. **Perhatikan circular dependencies** - Jika ada, refactor salah satu modul terlebih dahulu untuk memutus siklus.
