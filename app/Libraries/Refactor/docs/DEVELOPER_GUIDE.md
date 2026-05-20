# Panduan Developer - Arsitektur Sistem Refactoring

## Daftar Isi

- [Arsitektur Sistem](#arsitektur-sistem)
- [Diagram Interaksi Komponen](#diagram-interaksi-komponen)
- [Struktur Direktori](#struktur-direktori)
- [Cara Memperluas Sistem](#cara-memperluas-sistem)
- [Skema Data Model](#skema-data-model)
- [Error Codes dan Penanganan](#error-codes-dan-penanganan)
- [Panduan Testing](#panduan-testing)

---

## Arsitektur Sistem

Sistem refactoring terdiri dari 4 layer utama yang bekerja secara berurutan:

```
┌─────────────────────────────────────────────────────────────┐
│                    CLI Command Interface                      │
│  (RefactorDiscover, RefactorAnalyze, RefactorPrioritize,    │
│   RefactorScan, RefactorAudit, RefactorExecute,             │
│   RefactorProgress, RefactorBackup)                          │
└─────────────────────────┬───────────────────────────────────┘
                          │
┌─────────────────────────▼───────────────────────────────────┐
│                   Discovery Layer                             │
│  ┌──────────────┐  ┌──────────────────┐  ┌───────────────┐ │
│  │ FileScanner  │  │ ModuleDiscovery  │  │  CodeParser   │ │
│  └──────────────┘  └──────────────────┘  └───────────────┘ │
└─────────────────────────┬───────────────────────────────────┘
                          │
┌─────────────────────────▼───────────────────────────────────┐
│                   Analysis Layer                              │
│  ┌──────────────────┐  ┌─────────────────┐  ┌───────────┐ │
│  │DependencyAnalyzer│  │ImpactAnalyzer   │  │CodeAnalyzer│ │
│  └──────────────────┘  └─────────────────┘  └───────────┘ │
│  ┌──────────────────┐  ┌─────────────────┐                 │
│  │PrioritizationSys │  │ AuditGenerator  │                 │
│  └──────────────────┘  └─────────────────┘                 │
└─────────────────────────┬───────────────────────────────────┘
                          │
┌─────────────────────────▼───────────────────────────────────┐
│                   Security Layer                              │
│  ┌──────────────────┐  ┌─────────────────┐                 │
│  │ SecurityScanner  │  │  SecurityRules  │                 │
│  └──────────────────┘  └─────────────────┘                 │
└─────────────────────────┬───────────────────────────────────┘
                          │
┌─────────────────────────▼───────────────────────────────────┐
│                   Execution Layer                             │
│  ┌──────────────────┐  ┌─────────────────┐  ┌───────────┐ │
│  │  RefactorEngine  │  │  BackupManager  │  │ProgressTr │ │
│  └──────────────────┘  └─────────────────┘  └───────────┘ │
│  ┌──────────────────┐  ┌─────────────────┐  ┌───────────┐ │
│  │RepositoryGen     │  │  ServiceGen     │  │CtrlRefact │ │
│  └──────────────────┘  └─────────────────┘  └───────────┘ │
│  ┌──────────────────┐  ┌─────────────────┐                 │
│  │  SecurityFixer   │  │ControllerSplit  │                 │
│  └──────────────────┘  └─────────────────┘                 │
└─────────────────────────────────────────────────────────────┘
                          │
┌─────────────────────────▼───────────────────────────────────┐
│                   Storage Layer (JSON)                        │
│  ┌──────────────────┐  ┌─────────────────┐  ┌───────────┐ │
│  │module_inventory  │  │dependency_graph │  │ progress  │ │
│  └──────────────────┘  └─────────────────┘  └───────────┘ │
│  ┌──────────────────┐  ┌─────────────────┐                 │
│  │security_reports  │  │    backups      │                 │
│  └──────────────────┘  └─────────────────┘                 │
└─────────────────────────────────────────────────────────────┘
```

### Pipeline Alur Data

```
Discovery ──► Analysis ──► Security ──► Audit ──► Generation ──► Execution
    │              │            │          │            │             │
    ▼              ▼            ▼          ▼            ▼             ▼
ModuleInventory  DepGraph  SecReport  AuditReport  Generated    RefactorResult
   .json          .json      .json      .md         Code           .md
```

---

## Diagram Interaksi Komponen

### Flow: Discovery

```
CLI (refactor:discover)
    │
    ▼
ModuleDiscovery
    ├── FileScanner.scanDirectory(app/Controllers)
    │       └── returns: array of PHP file paths
    ├── FileScanner.scanDirectory(app/Models)
    │       └── returns: array of PHP file paths
    ├── CodeParser.parseFile(controllerPath)
    │       └── returns: class name, methods, use statements
    ├── identifyRelationships()
    │       └── matches controllers with their models
    └── returns: ModuleInventory
            └── saved to: writable/refactor/module_inventory.json
```

### Flow: Analysis

```
CLI (refactor:analyze)
    │
    ▼
DependencyAnalyzer
    ├── loads ModuleInventory from JSON
    ├── ASTParser.parseFile(each controller)
    │       └── extracts: use statements, method calls, instantiations
    ├── buildGraph()
    │       └── creates nodes and edges
    ├── detectCircularDependencies()
    │       └── DFS traversal for cycles
    ├── calculateImpactScores()
    │       └── count of dependents per module
    └── returns: DependencyGraph
            ├── saved to: writable/refactor/dependency_graph.json
            └── saved to: writable/refactor/dependency_graph.mmd
```

### Flow: Refactoring Execution

```
CLI (refactor:execute Module)
    │
    ▼
RefactorEngine.refactor(moduleName, options)
    │
    ├── 1. BackupManager.createBackup(files)
    │       └── saves original files with metadata
    │
    ├── 2. RepositoryGenerator.generate(modelName, queries)
    │       ├── QueryAnalyzer.analyzeQueries()
    │       ├── generateCrudMethods()
    │       ├── convertToQueryBuilder()
    │       └── writes: app/Repositories/{Module}Repository.php
    │
    ├── 3. ServiceGenerator.generate(controllerName, businessLogic)
    │       ├── ValidationExtractor.extract()
    │       ├── generateServiceMethods()
    │       ├── addTransactionManagement()
    │       └── writes: app/Services/{Module}Service.php
    │
    ├── 4. ControllerRefactorer.refactor(controllerPath, serviceName)
    │       ├── injectService()
    │       ├── replaceBusinessLogicWithServiceCalls()
    │       ├── addErrorHandling()
    │       └── modifies: app/Controllers/{Module}.php
    │
    ├── 5. SecurityFixer.fix(securityReport)
    │       ├── addCSRFProtection()
    │       ├── addInputValidation()
    │       ├── replaceRawQueryWithQueryBuilder()
    │       └── modifies: affected files
    │
    ├── 6. ControllerSplitter.split(controllerCode)
    │       ├── identifyWebMethods()
    │       ├── identifyApiMethods()
    │       ├── generateWebController()
    │       └── writes: app/Controllers/Api/{Module}Controller.php
    │
    └── 7. ProgressTracker.recordRefactor(moduleName)
            └── updates: writable/refactor/progress.json
```

---

## Struktur Direktori

```
app/Libraries/Refactor/
├── Contracts/                    # Interface definitions
│   ├── AnalyzerInterface.php
│   ├── GeneratorInterface.php
│   ├── ParserInterface.php
│   └── ScannerInterface.php
│
├── Discovery/                    # Discovery layer
│   ├── FileScanner.php          # Recursive file scanning
│   ├── CodeParser.php           # PHP code parsing (AST)
│   └── ModuleDiscovery.php      # Module discovery orchestrator
│
├── Analysis/                     # Analysis layer
│   ├── ASTParser.php            # AST parsing wrapper
│   ├── DependencyAnalyzer.php   # Dependency graph builder
│   ├── ImpactAnalyzer.php       # Impact score calculator
│   ├── PrioritizationSystem.php # Module prioritization
│   ├── CodeAnalyzer.php         # Code structure analyzer
│   └── AuditGenerator.php       # Audit report generator
│
├── Security/                     # Security layer
│   ├── SecurityRules.php        # Security rule definitions
│   ├── SecurityScanner.php      # Vulnerability scanner
│   └── SecurityFixer.php        # Security fix applier (legacy)
│
├── Generation/                   # Code generation
│   ├── CodeGenerator.php        # Base code generator (PSR-12)
│   ├── QueryAnalyzer.php        # SQL query analyzer
│   ├── RepositoryGenerator.php  # Repository class generator
│   ├── ServiceGenerator.php     # Service class generator
│   ├── ValidationExtractor.php  # Validation rule extractor
│   └── ValidationMigrator.php   # Validation migration
│
├── Execution/                    # Execution layer
│   ├── RefactorEngine.php       # Main orchestrator
│   ├── BackupManager.php        # Backup/restore management
│   ├── ProgressTracker.php      # Progress tracking
│   ├── ControllerRefactorer.php # Controller transformation
│   ├── ControllerSplitter.php   # Web/API splitting
│   └── SecurityFixer.php        # Security fix execution
│
├── Models/                       # Data models
│   ├── Module.php               # Module data model
│   ├── ModuleInventory.php      # Module inventory collection
│   ├── DependencyGraph.php      # Dependency graph model
│   ├── SecurityReport.php       # Security report model
│   ├── Vulnerability.php        # Vulnerability model
│   ├── AuditReport.php          # Audit report model
│   ├── ControllerAnalysis.php   # Controller analysis model
│   ├── ImpactAnalysis.php       # Impact analysis model
│   ├── PriorityScore.php        # Priority score model
│   ├── RefactorOptions.php      # Refactoring options
│   ├── RefactorResult.php       # Refactoring result
│   ├── SplitResult.php          # Controller split result
│   └── Backup.php               # Backup metadata model
│
├── Exceptions/                   # Exception classes
│   ├── RefactorException.php    # Base exception
│   ├── DiscoveryException.php   # Discovery errors (1xxx)
│   ├── AnalysisException.php    # Analysis errors (2xxx)
│   ├── SecurityException.php    # Security errors (3xxx)
│   ├── RefactorExecutionException.php  # Execution errors (4xxx)
│   ├── BackupException.php      # Backup errors (4xxx)
│   └── ValidationException.php  # Validation errors (5xxx)
│
└── docs/                         # Documentation
    ├── USER_GUIDE.md
    ├── DEVELOPER_GUIDE.md
    └── EXAMPLE_WALKTHROUGH.md
```

---

## Cara Memperluas Sistem

### Menambah Custom Security Rules

Security rules didefinisikan di `Security/SecurityRules.php`. Untuk menambah rule baru:

```php
// Di SecurityRules.php, tambahkan rule baru ke array rules

public static function getCustomRules(): array
{
    return [
        [
            'type' => 'CUSTOM_VULNERABILITY',
            'severity' => 'HIGH',
            'pattern' => '/your_regex_pattern_here/',
            'description' => 'Deskripsi kerentanan yang terdeteksi',
            'recommendation' => 'Rekomendasi perbaikan',
        ],
    ];
}
```

**Struktur rule:**

| Field | Tipe | Deskripsi |
|-------|------|-----------|
| `type` | string | Identifier unik (e.g., `SQL_INJECTION`, `XSS`, `CUSTOM_RULE`) |
| `severity` | string | `CRITICAL`, `HIGH`, `MEDIUM`, atau `LOW` |
| `pattern` | string | Regex pattern untuk mendeteksi kerentanan |
| `description` | string | Penjelasan kerentanan |
| `recommendation` | string | Saran perbaikan |

**Tipe kerentanan yang sudah ada:**
- `SQL_INJECTION` - Raw query dengan input user
- `XSS` - Output tanpa escaping
- `CSRF_MISSING` - Form tanpa CSRF protection
- `INSECURE_AUTH` - Pattern autentikasi tidak aman
- `HARDCODED_CREDENTIALS` - Kredensial hardcoded
- `MISSING_VALIDATION` - Input tanpa validasi
- `INSECURE_FILE_UPLOAD` - Upload file tanpa validasi

### Menambah Custom Generator

Untuk membuat generator baru, implementasikan `GeneratorInterface`:

```php
<?php

namespace App\Libraries\Refactor\Generation;

use App\Libraries\Refactor\Contracts\GeneratorInterface;

class CustomGenerator implements GeneratorInterface
{
    private CodeGenerator $codeGen;

    public function __construct(CodeGenerator $codeGen)
    {
        $this->codeGen = $codeGen;
    }

    /**
     * Generate custom component
     *
     * @param string $moduleName Nama modul
     * @param array $data Data untuk generation
     * @return string Generated code
     */
    public function generate(string $moduleName, array $data): string
    {
        // Gunakan CodeGenerator untuk memastikan PSR-12 compliance
        $namespace = "App\\CustomComponent";
        $className = $moduleName . 'Custom';

        $code = $this->codeGen->generateClassHeader($namespace, $className);
        
        // Tambahkan methods
        foreach ($data as $item) {
            $code .= $this->generateMethod($item);
        }

        $code .= $this->codeGen->generateClassFooter();

        // Validasi syntax PHP
        $this->codeGen->validateSyntax($code);

        return $code;
    }

    private function generateMethod(array $item): string
    {
        // Implementation
    }
}
```

### Mengintegrasikan Generator Baru ke RefactorEngine

Tambahkan generator baru sebagai dependency di `RefactorEngine`:

```php
// Di RefactorEngine constructor
public function __construct(
    // ... existing dependencies
    private CustomGenerator $customGen
) {}

// Di method refactor(), tambahkan step baru
private function executeCustomStep(string $moduleName): StepResult
{
    $data = $this->prepareCustomData($moduleName);
    $code = $this->customGen->generate($moduleName, $data);
    
    $filePath = APPPATH . "CustomComponent/{$moduleName}Custom.php";
    file_put_contents($filePath, $code);
    
    return new StepResult(true, [$filePath]);
}
```

---

## Skema Data Model

### Module Inventory (`writable/refactor/module_inventory.json`)

```json
{
  "discoveredAt": "2024-01-15T10:30:00+07:00",
  "modules": {
    "Transaksi": {
      "name": "Transaksi",
      "controllerPath": "app/Controllers/Transaksi.php",
      "modelPaths": [
        "app/Models/TransaksiModel.php",
        "app/Models/KeuanganModel.php"
      ],
      "servicePath": null,
      "repositoryPath": null,
      "routes": [
        "POST /transaksi/simpan",
        "GET /transaksi/list",
        "PUT /transaksi/update/{id}",
        "DELETE /transaksi/delete/{id}"
      ],
      "methods": [
        "index",
        "simpan",
        "update",
        "delete",
        "detail",
        "export",
        "approve",
        "reject"
      ]
    }
  },
  "controllers": [
    "app/Controllers/Transaksi.php",
    "app/Controllers/Keuangan.php"
  ],
  "models": [
    "app/Models/TransaksiModel.php",
    "app/Models/KeuanganModel.php"
  ],
  "services": [],
  "repositories": []
}
```

### Dependency Graph (`writable/refactor/dependency_graph.json`)

```json
{
  "nodes": ["Transaksi", "Keuangan", "Kavling", "Konsumen"],
  "edges": {
    "Transaksi": ["Keuangan", "Kavling"],
    "Keuangan": ["Konsumen"],
    "Kavling": ["Konsumen"],
    "Konsumen": []
  },
  "impactScores": {
    "Transaksi": 0,
    "Keuangan": 1,
    "Kavling": 1,
    "Konsumen": 2
  },
  "circular": []
}
```

**Penjelasan field:**
- `nodes`: Daftar semua modul
- `edges`: Map dari modul ke daftar modul yang menjadi dependency-nya
- `impactScores`: Jumlah modul lain yang bergantung pada modul ini
- `circular`: Array of circular dependency chains (e.g., `[["A", "B", "A"]]`)

### Security Report (`writable/refactor/security_reports/{module}_security_report.json`)

```json
{
  "moduleName": "Transaksi",
  "scannedAt": "2024-01-15T10:35:00+07:00",
  "vulnerabilities": [
    {
      "type": "SQL_INJECTION",
      "severity": "CRITICAL",
      "filePath": "app/Controllers/Transaksi.php",
      "lineNumber": 45,
      "description": "Raw SQL query with user input concatenation",
      "recommendation": "Use Query Builder with parameter binding",
      "codeSnippet": "$query = \"SELECT * FROM transaksi WHERE id = \" . $id;"
    },
    {
      "type": "CSRF_MISSING",
      "severity": "HIGH",
      "filePath": "app/Controllers/Transaksi.php",
      "lineNumber": 78,
      "description": "Form submission without CSRF protection",
      "recommendation": "Add CSRF filter to route or use csrf_field() in form",
      "codeSnippet": null
    },
    {
      "type": "XSS",
      "severity": "MEDIUM",
      "filePath": "app/Controllers/Transaksi.php",
      "lineNumber": 120,
      "description": "Unescaped output in view data",
      "recommendation": "Use esc() helper for all user-provided output",
      "codeSnippet": "$data['nama'] = $this->request->getPost('nama');"
    }
  ]
}
```

### Progress Tracker (`writable/refactor/progress.json`)

```json
{
  "modules": {
    "Transaksi": {
      "status": "COMPLETED",
      "auditedAt": "2024-01-15T10:40:00+07:00",
      "refactoredAt": "2024-01-15T11:20:00+07:00",
      "vulnerabilitiesFixed": 3,
      "backupId": "backup_20240115_112000"
    },
    "Keuangan": {
      "status": "AUDITED",
      "auditedAt": "2024-01-15T12:00:00+07:00",
      "refactoredAt": null,
      "vulnerabilitiesFixed": 0,
      "backupId": null
    },
    "Kavling": {
      "status": "NOT_STARTED",
      "auditedAt": null,
      "refactoredAt": null,
      "vulnerabilitiesFixed": 0,
      "backupId": null
    }
  },
  "overallProgress": 33.33,
  "totalModules": 3,
  "completedModules": 1
}
```

**Status yang tersedia:**
| Status | Deskripsi |
|--------|-----------|
| `NOT_STARTED` | Modul belum diproses |
| `AUDITED` | Audit selesai, menunggu eksekusi |
| `IN_PROGRESS` | Refactoring sedang berjalan |
| `COMPLETED` | Refactoring selesai |
| `FAILED` | Refactoring gagal |

### Backup Metadata (`writable/refactor/backups/{backupId}/metadata.json`)

```json
{
  "id": "backup_20240115_112000",
  "moduleName": "Transaksi",
  "createdAt": "2024-01-15T11:20:00+07:00",
  "description": "Pre-refactoring backup",
  "files": [
    {
      "originalPath": "app/Controllers/Transaksi.php",
      "backupPath": "writable/refactor/backups/backup_20240115_112000/Transaksi.php",
      "checksum": "abc123def456"
    }
  ]
}
```

---

## Error Codes dan Penanganan

### Hierarki Exception

```
RefactorException (base)
├── DiscoveryException        (1xxx)
├── AnalysisException         (2xxx)
├── SecurityException         (3xxx)
├── RefactorExecutionException (4xxx)
│   └── BackupException       (4xxx)
└── ValidationException       (5xxx)
```

### Daftar Error Codes

#### Discovery Errors (1xxx)

| Code | Constant | Deskripsi | Severity |
|------|----------|-----------|----------|
| 1001 | `ERROR_FILE_NOT_FOUND` | File tidak ditemukan | ERROR |
| 1002 | `ERROR_PERMISSION_DENIED` | Tidak ada izin akses | ERROR |
| 1003 | `ERROR_PARSE_FAILED` | Gagal parsing file PHP | WARNING |
| 1004 | `ERROR_INVALID_CONFIGURATION` | Konfigurasi tidak valid | ERROR |
| 1005 | `ERROR_DIRECTORY_NOT_FOUND` | Direktori tidak ditemukan | ERROR |

#### Analysis Errors (2xxx)

| Code | Constant | Deskripsi | Severity |
|------|----------|-----------|----------|
| 2001 | `ERROR_CIRCULAR_DEPENDENCY` | Circular dependency terdeteksi | WARNING |
| 2002 | `ERROR_MISSING_DEPENDENCY` | Dependency tidak ditemukan di inventory | WARNING |
| 2003 | `ERROR_INVALID_GRAPH` | Struktur graph tidak valid | ERROR |
| 2004 | `ERROR_ANALYSIS_FAILED` | Analisis gagal | ERROR |

#### Security Errors (3xxx)

| Code | Constant | Deskripsi | Severity |
|------|----------|-----------|----------|
| 3001 | `ERROR_RULE_LOAD_FAILED` | Gagal memuat security rule | WARNING |
| 3002 | `ERROR_PATTERN_INVALID` | Pattern regex tidak valid | WARNING |
| 3003 | `ERROR_SCAN_FAILED` | Scan gagal | ERROR |

#### Refactoring Errors (4xxx)

| Code | Constant | Deskripsi | Severity |
|------|----------|-----------|----------|
| 4001 | `ERROR_BACKUP_FAILED` | Gagal membuat backup | CRITICAL |
| 4002 | `ERROR_CODE_GEN_FAILED` | Gagal generate kode | ERROR |
| 4003 | `ERROR_FILE_WRITE_FAILED` | Gagal menulis file | ERROR |
| 4004 | `ERROR_TEST_FAILED` | Test gagal setelah refactoring | ERROR |
| 4005 | `ERROR_ROLLBACK_FAILED` | Gagal melakukan rollback | CRITICAL |
| 4006 | `ERROR_STEP_FAILED` | Step refactoring gagal | ERROR |

#### Validation Errors (5xxx)

| Code | Constant | Deskripsi | Severity |
|------|----------|-----------|----------|
| 5001 | `ERROR_MODULE_NOT_FOUND` | Modul tidak ditemukan di inventory | ERROR |
| 5002 | `ERROR_INVALID_OPTIONS` | Opsi refactoring tidak valid | ERROR |
| 5003 | `ERROR_PREREQUISITE_MISSING` | Prasyarat belum terpenuhi | ERROR |
| 5004 | `ERROR_INVALID_INPUT` | Input tidak valid | ERROR |

### Strategi Penanganan Error

#### Severity CRITICAL
- Proses dihentikan segera
- Rollback otomatis jika memungkinkan
- Pesan error detail ditampilkan ke user
- Contoh: Backup gagal → refactoring dibatalkan

#### Severity ERROR
- Proses dihentikan untuk step tersebut
- User diberi opsi untuk rollback atau lanjut
- Error di-log dengan context lengkap

#### Severity WARNING
- Proses tetap dilanjutkan
- Warning ditampilkan di summary
- Contoh: Circular dependency → dilaporkan tapi analisis tetap jalan

#### Severity INFO
- Informasi tambahan untuk debugging
- Tidak mempengaruhi alur proses

### Contoh Penanganan Error

```php
try {
    $result = $engine->refactor('Transaksi', $options);
} catch (BackupException $e) {
    // CRITICAL: Backup gagal, refactoring tidak boleh dilanjutkan
    CLI::error("Backup gagal: " . $e->getMessage());
    CLI::error("Refactoring dibatalkan demi keamanan.");
} catch (RefactorExecutionException $e) {
    // ERROR: Step gagal, tawarkan rollback
    CLI::error("Refactoring gagal: " . $e->getMessage());
    if ($e->getContext()['step'] ?? null) {
        CLI::write("Gagal di step: " . $e->getContext()['step']);
    }
    // Rollback tersedia via backup ID
} catch (ValidationException $e) {
    // ERROR: Input tidak valid, tampilkan pesan
    CLI::error($e->getMessage());
} catch (RefactorException $e) {
    // Generic error
    CLI::error("[{$e->getCategory()}] {$e->getMessage()}");
}
```

---

## Panduan Testing

### Setup Testing

Project menggunakan PHPUnit untuk testing. Test files berada di `tests/` directory.

```bash
# Jalankan semua test
php vendor/bin/phpunit

# Jalankan test untuk komponen tertentu
php vendor/bin/phpunit tests/Libraries/Refactor/

# Jalankan test dengan filter
php vendor/bin/phpunit --filter=ModuleDiscoveryTest
```

### Struktur Test

```
tests/
└── Libraries/
    └── Refactor/
        ├── Discovery/
        │   ├── FileScannerTest.php
        │   ├── CodeParserTest.php
        │   └── ModuleDiscoveryTest.php
        ├── Analysis/
        │   ├── DependencyAnalyzerTest.php
        │   ├── ImpactAnalyzerTest.php
        │   └── PrioritizationSystemTest.php
        ├── Security/
        │   └── SecurityScannerTest.php
        ├── Generation/
        │   ├── RepositoryGeneratorTest.php
        │   ├── ServiceGeneratorTest.php
        │   └── CodeGeneratorTest.php
        └── Execution/
            ├── RefactorEngineTest.php
            ├── BackupManagerTest.php
            └── ControllerRefactorerTest.php
```

### Menulis Test Baru

```php
<?php

namespace Tests\Libraries\Refactor;

use CodeIgniter\Test\CIUnitTestCase;
use App\Libraries\Refactor\Discovery\ModuleDiscovery;

class ModuleDiscoveryTest extends CIUnitTestCase
{
    private ModuleDiscovery $discovery;

    protected function setUp(): void
    {
        parent::setUp();
        // Setup test fixtures
    }

    public function testDiscoverFindsControllers(): void
    {
        $inventory = $this->discovery->discover();
        $this->assertNotEmpty($inventory->controllers);
    }

    public function testDiscoverHandlesMissingDirectory(): void
    {
        // Test graceful handling of missing directories
        $discovery = new ModuleDiscovery('/nonexistent/path', ...);
        $inventory = $discovery->discover();
        $this->assertEmpty($inventory->modules);
    }
}
```

### Tips Testing

1. **Gunakan fixture files** - Buat sample PHP files untuk testing di `tests/fixtures/`
2. **Test edge cases** - File kosong, syntax error, file tanpa class
3. **Test error handling** - Pastikan exception yang benar di-throw
4. **Jangan mock filesystem** - Gunakan file asli di temp directory untuk integration test
5. **Validasi generated code** - Gunakan `php -l` untuk memastikan syntax valid
