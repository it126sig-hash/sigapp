# Security Architecture Refactor System

This library provides a systematic approach to refactoring CodeIgniter 4 applications from fat controllers with mixed concerns into a clean, secure architecture following the Thin Controller → Service → Repository pattern.

## Directory Structure

```
app/Libraries/Refactor/
├── Contracts/              # Base interfaces for components
│   ├── AnalyzerInterface.php
│   ├── GeneratorInterface.php
│   ├── ParserInterface.php
│   └── ScannerInterface.php
├── Models/                 # Core data models
│   ├── DependencyGraph.php
│   ├── Module.php
│   ├── ModuleInventory.php
│   ├── PriorityScore.php
│   ├── RefactorOptions.php
│   ├── RefactorResult.php
│   ├── SecurityReport.php
│   └── Vulnerability.php
├── Exceptions/             # Exception classes for error handling
│   ├── RefactorException.php
│   ├── DiscoveryException.php
│   ├── AnalysisException.php
│   ├── SecurityException.php
│   ├── RefactorExecutionException.php
│   └── ValidationException.php
├── Discovery/              # Module discovery components (to be implemented)
├── Analysis/               # Dependency and impact analysis (to be implemented)
├── Security/               # Security scanning components (to be implemented)
├── Generation/             # Code generation components (to be implemented)
├── Execution/              # Refactoring execution components (to be implemented)
└── README.md
```

## Core Data Models

### Module
Represents a functional unit of the application consisting of a controller, its related models, views, and business logic.

**Properties:**
- `name`: Module name (typically the controller name without suffix)
- `controllerPath`: Absolute path to the controller file
- `modelPaths`: Array of absolute paths to related model files
- `servicePath`: Absolute path to the service file (if exists)
- `repositoryPath`: Absolute path to the repository file (if exists)
- `routes`: Array of route definitions for this module
- `methods`: Array of public method names in the controller

### ModuleInventory
Contains the complete inventory of all discovered modules and their components in the CodeIgniter 4 application.

**Properties:**
- `modules`: Array of Module objects indexed by module name
- `controllers`: Array of all controller file paths
- `models`: Array of all model file paths
- `services`: Array of all service file paths
- `repositories`: Array of all repository file paths
- `discoveredAt`: Timestamp when the inventory was discovered

**Methods:**
- `addModule(Module $module)`: Add a module to the inventory
- `getModule(string $name)`: Get a module by name
- `toJson()`: Convert inventory to JSON string
- `fromJson(string $json)`: Create inventory from JSON string

### DependencyGraph
Represents the dependency relationships between modules and provides methods to query dependencies and calculate impact scores.

**Properties:**
- `nodes`: Array of module names (nodes in the graph)
- `edges`: Array of dependency edges [from => [to1, to2, ...]]
- `impactScores`: Array of impact scores [module => score]
- `circular`: Array of circular dependency chains

**Methods:**
- `addNode(string $module)`: Add a node (module) to the graph
- `addEdge(string $from, string $to)`: Add a dependency edge
- `getDependencies(string $module)`: Get modules that the given module depends on
- `getDependents(string $module)`: Get modules that depend on the given module
- `getImpactScore(string $module)`: Get the impact score for a module
- `toMermaid()`: Convert graph to Mermaid diagram syntax
- `toJson()`: Convert graph to JSON string

### Vulnerability
Represents a security vulnerability found in module code.

**Constants:**
- Types: `SQL_INJECTION`, `XSS`, `CSRF_MISSING`, `INSECURE_AUTH`, `HARDCODED_CREDENTIALS`, `MISSING_VALIDATION`, `INSECURE_FILE_UPLOAD`
- Severities: `CRITICAL`, `HIGH`, `MEDIUM`, `LOW`

**Properties:**
- `type`: Vulnerability type
- `severity`: Severity level
- `filePath`: Absolute path to the file containing the vulnerability
- `lineNumber`: Line number where the vulnerability was found
- `description`: Human-readable description of the vulnerability
- `recommendation`: Recommendation for fixing the vulnerability
- `codeSnippet`: Optional code snippet showing the vulnerable code

### SecurityReport
Contains security vulnerability findings for a specific module.

**Properties:**
- `moduleName`: Name of the module that was scanned
- `vulnerabilities`: Array of Vulnerability objects found in the module
- `scannedAt`: Timestamp when the scan was performed

**Methods:**
- `addVulnerability(Vulnerability $vulnerability)`: Add a vulnerability to the report
- `getBySeverity(string $severity)`: Get vulnerabilities filtered by severity level
- `getCriticalCount()`: Get count of critical vulnerabilities
- `hasCriticalVulnerabilities()`: Check if the module has any critical vulnerabilities
- `toJson()`: Convert report to JSON string

### PriorityScore
Represents the priority score for a module in the refactoring order.

**Constants:**
- Categories: `LEAF` (no dependents), `CORE` (many dependents), `INTERMEDIATE` (some dependents)

**Properties:**
- `module`: Module name
- `score`: Calculated priority score (lower = higher priority)
- `impactScore`: Impact score (number of modules that depend on this module)
- `dependencyDepth`: Dependency depth (how deep in the dependency tree)
- `criticalVulnerabilities`: Number of critical vulnerabilities in the module
- `category`: Module category
- `manualPriority`: Manual priority override (if set by user)

**Methods:**
- `isLeaf()`: Check if this module is a leaf module (no dependents)
- `isCore()`: Check if this module is a core module (many dependents)
- `getEffectivePriority()`: Get the effective priority (manual override takes precedence over calculated score)
- `setManualPriority(?int $priority)`: Set manual priority override (null to clear)
- `hasManualOverride()`: Check if manual priority override is set
- `toArray()`: Convert to array representation
- `toJson()`: Convert to JSON string
- `fromArray(array $data)`: Create instance from array data
- `fromJson(string $json)`: Create instance from JSON string

### RefactorOptions
Configuration options for the refactoring process.

**Properties:**
- `createRepository`: Whether to create repository classes (default: true)
- `createService`: Whether to create service classes (default: true)
- `refactorController`: Whether to refactor the controller (default: true)
- `fixSecurity`: Whether to fix security vulnerabilities (default: true)
- `separateWebApi`: Whether to separate Web and API controllers (default: true)
- `runTests`: Whether to run tests after refactoring (default: false)
- `createGitCommits`: Whether to create git commits for each step (default: true)

**Factory Methods:**
- `all()`: Create options with all features enabled
- `minimal()`: Create options with minimal refactoring
- `securityOnly()`: Create options for security fixes only

### RefactorResult
Contains the results of a refactoring operation.

**Properties:**
- `success`: Whether the refactoring was successful
- `filesCreated`: Array of file paths that were created
- `filesModified`: Array of file paths that were modified
- `stepsCompleted`: Array of refactoring steps that were completed
- `backupId`: Backup ID for rollback (if backup was created)
- `errorMessage`: Error message if refactoring failed
- `completedAt`: Timestamp when refactoring completed

**Methods:**
- `success()`: Create a successful result
- `failure(string $errorMessage)`: Create a failed result with error message
- `toMarkdown()`: Convert result to markdown report

## Base Interfaces

### ScannerInterface
Base interface for components that scan and analyze code.

**Methods:**
- `scan(string $target)`: Scan the specified path or module

### AnalyzerInterface
Base interface for components that analyze code or data structures.

**Methods:**
- `analyze(mixed $data)`: Analyze the provided data

### GeneratorInterface
Base interface for components that generate code or reports.

**Methods:**
- `generate(mixed $data)`: Generate output based on provided data

### ParserInterface
Base interface for components that parse code or files.

**Methods:**
- `parse(string $content)`: Parse the provided content

## Exception Classes

### RefactorException
Base exception class for all refactoring system exceptions. Provides common functionality for error handling and reporting.

**Properties:**
- `category`: Error category (DISCOVERY, ANALYSIS, SECURITY, REFACTOR, VALIDATION)
- `severity`: Error severity (CRITICAL, ERROR, WARNING, INFO)
- `filePath`: Related file path (if applicable)
- `lineNumber`: Related line number (if applicable)
- `context`: Additional context data

**Methods:**
- `setFilePath(string $filePath)`: Set the related file path
- `setLineNumber(int $lineNumber)`: Set the related line number
- `setContext(array $context)`: Set additional context data
- `isCritical()`: Check if this is a critical error
- `toArray()`: Convert exception to array

### DiscoveryException
Exception thrown during module discovery operations. Includes file system errors, parse errors, and configuration errors.

**Error Codes:**
- `ERROR_FILE_NOT_FOUND` (1001)
- `ERROR_PERMISSION_DENIED` (1002)
- `ERROR_PARSE_FAILED` (1003)
- `ERROR_INVALID_CONFIGURATION` (1004)
- `ERROR_DIRECTORY_NOT_FOUND` (1005)

**Factory Methods:**
- `fileNotFound(string $filePath)`: Create exception for file not found
- `permissionDenied(string $filePath)`: Create exception for permission denied
- `parseFailed(string $filePath, string $reason)`: Create exception for parse failure
- `directoryNotFound(string $directoryPath)`: Create exception for directory not found
- `invalidConfiguration(string $message)`: Create exception for invalid configuration

### AnalysisException
Exception thrown during dependency analysis and impact analysis operations.

**Error Codes:**
- `ERROR_CIRCULAR_DEPENDENCY` (2001)
- `ERROR_MISSING_DEPENDENCY` (2002)
- `ERROR_INVALID_GRAPH` (2003)
- `ERROR_ANALYSIS_FAILED` (2004)

**Factory Methods:**
- `circularDependency(array $chain)`: Create exception for circular dependency
- `missingDependency(string $module, string $dependency)`: Create exception for missing dependency
- `invalidGraph(string $reason)`: Create exception for invalid graph
- `analysisFailed(string $module, string $reason)`: Create exception for analysis failure

### SecurityException
Exception thrown during security scanning operations.

**Error Codes:**
- `ERROR_RULE_LOAD_FAILED` (3001)
- `ERROR_PATTERN_INVALID` (3002)
- `ERROR_SCAN_FAILED` (3003)

**Factory Methods:**
- `ruleLoadFailed(string $ruleName, string $reason)`: Create exception for rule load failure
- `patternInvalid(string $pattern, string $reason)`: Create exception for invalid pattern
- `scanFailed(string $module, string $reason)`: Create exception for scan failure

### RefactorExecutionException
Exception thrown during refactoring execution operations.

**Error Codes:**
- `ERROR_BACKUP_FAILED` (4001)
- `ERROR_CODE_GEN_FAILED` (4002)
- `ERROR_FILE_WRITE_FAILED` (4003)
- `ERROR_TEST_FAILED` (4004)
- `ERROR_ROLLBACK_FAILED` (4005)
- `ERROR_STEP_FAILED` (4006)

**Factory Methods:**
- `backupFailed(string $reason)`: Create exception for backup failure
- `codeGenerationFailed(string $component, string $reason)`: Create exception for code generation failure
- `fileWriteFailed(string $filePath, string $reason)`: Create exception for file write failure
- `testFailed(string $testOutput)`: Create exception for test failure
- `rollbackFailed(string $backupId, string $reason)`: Create exception for rollback failure
- `stepFailed(string $step, string $reason)`: Create exception for step failure

### ValidationException
Exception thrown during validation operations.

**Error Codes:**
- `ERROR_MODULE_NOT_FOUND` (5001)
- `ERROR_INVALID_OPTIONS` (5002)
- `ERROR_PREREQUISITE_MISSING` (5003)
- `ERROR_INVALID_INPUT` (5004)

**Factory Methods:**
- `moduleNotFound(string $moduleName)`: Create exception for module not found
- `invalidOptions(string $reason)`: Create exception for invalid options
- `prerequisiteMissing(string $prerequisite, string $action)`: Create exception for missing prerequisite
- `invalidInput(string $field, string $reason)`: Create exception for invalid input

## JSON Storage Structure

The refactoring system stores data in JSON format under `writable/refactor/`:

```
writable/refactor/
├── module_inventory.json       # Complete inventory of discovered modules
├── dependency_graph.json       # Dependency relationships between modules
├── progress.json               # Progress tracking for refactoring operations
├── security_reports/           # Individual security scan reports per module
│   ├── ModuleName.json
│   └── ...
└── backups/                    # Backup files for rollback functionality
    ├── backup_YYYYMMDD_HHMMSS/
    └── ...
```

## PSR-4 Autoloading

All classes in this library follow PSR-4 autoloading standards with the namespace `App\Libraries\Refactor`.

Example usage:
```php
use App\Libraries\Refactor\Models\Module;
use App\Libraries\Refactor\Models\ModuleInventory;

$module = new Module('Transaksi', 'app/Controllers/Transaksi.php');
$inventory = new ModuleInventory();
$inventory->addModule($module);
```

## Next Steps

The following components will be implemented in subsequent tasks:
1. Module Discovery Component (FileScanner, CodeParser, ModuleDiscovery)
2. Dependency Analyzer Component (ASTParser, DependencyAnalyzer)
3. Security Scanner Component (SecurityScanner with rule definitions)
4. Prioritization System Component (ImpactAnalyzer, PrioritizationSystem)
5. Audit Generator Component (CodeAnalyzer, AuditGenerator)
6. Code Generation Components (CodeGenerator, QueryAnalyzer, ValidationExtractor)
7. Repository Generator Component
8. Service Generator Component
9. Controller Refactorer Component
10. Security Fixer Component
11. Backup and Rollback System (BackupManager)
12. Progress Tracker Component
13. Refactor Engine Component (orchestration)
14. CLI Command Interface
15. Error Handling System
16. Configuration System

## License

This library is part of the sigapp.dev CodeIgniter 4 application.
