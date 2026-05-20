# Audit Models Documentation

This document describes the `AuditReport` and `ControllerAnalysis` data models used in the Audit Generator Component.

## Overview

These models are designed to store comprehensive analysis results during the **audit phase** of the refactoring workflow. The audit phase is **read-only** and does NOT modify any code.

## ControllerAnalysis

The `ControllerAnalysis` class contains structural metrics and analysis results for a controller.

### Properties

| Property | Type | Description |
|----------|------|-------------|
| `methodCount` | `int` | Number of methods in the controller |
| `linesOfCode` | `int` | Total lines of code in the controller |
| `methods` | `array` | Array of method details (name, lines, complexity, hasBusinessLogic) |
| `hasValidation` | `bool` | Whether the controller has validation logic |
| `hasBusinessLogic` | `bool` | Whether the controller has business logic to extract |
| `hasDirectQueries` | `bool` | Whether the controller has direct database queries |
| `dependencies` | `array` | Array of dependency class names |
| `routeCount` | `int` | Number of routes handled by the controller |
| `hasMixedResponses` | `bool` | Whether the controller handles both web and API requests |
| `webMethodCount` | `int` | Number of methods that return views |
| `apiMethodCount` | `int` | Number of methods that return JSON |

### Key Methods

#### `addMethod(string $methodName, int $lines, string $complexity, bool $hasBusinessLogic): void`
Add a method to the analysis with its metrics.

#### `addDependency(string $dependency): void`
Add a dependency class name to the analysis.

#### `getAverageLinesPerMethod(): float`
Calculate the average lines per method.

#### `needsRefactoring(): bool`
Check if the controller needs refactoring based on analysis.

#### `getRefactoringComplexity(): string`
Get estimated refactoring complexity (Simple, Medium, Complex).

#### `toArray(): array`
Convert analysis to array representation.

#### `toJson(): string`
Convert analysis to JSON string.

#### `fromArray(array $data): self`
Create instance from array data.

#### `fromJson(string $json): self`
Create instance from JSON string.

### Example Usage

```php
use App\Libraries\Refactor\Models\ControllerAnalysis;

$analysis = new ControllerAnalysis();
$analysis->linesOfCode = 250;
$analysis->routeCount = 5;
$analysis->hasBusinessLogic = true;
$analysis->hasDirectQueries = true;
$analysis->hasMixedResponses = true;

$analysis->addMethod('index', 30, 'Simple', false);
$analysis->addMethod('store', 50, 'Medium', true);
$analysis->addDependency('TransaksiModel');

echo "Complexity: " . $analysis->getRefactoringComplexity(); // "Medium"
echo "Needs Refactoring: " . ($analysis->needsRefactoring() ? 'Yes' : 'No'); // "Yes"
```

## AuditReport

The `AuditReport` class contains comprehensive analysis results for a module before refactoring.

### Properties

| Property | Type | Description |
|----------|------|-------------|
| `moduleName` | `string` | Name of the module being audited |
| `controllerAnalysis` | `ControllerAnalysis` | Controller structure analysis |
| `businessLogicToExtract` | `array` | Business logic items to move to Service layer |
| `queriesToMove` | `array` | Database queries to move to Repository layer |
| `securityReport` | `SecurityReport` | Security vulnerability findings |
| `impactAnalysis` | `ImpactAnalysis` | Impact analysis for this module |
| `complexity` | `string` | Estimated refactoring complexity |
| `recommendations` | `array` | Array of recommendations |
| `generatedAt` | `DateTime` | Timestamp when audit was generated |
| `controllerPath` | `string` | Path to the controller file |
| `modelPaths` | `array` | Array of model paths |
| `shouldSplitWebApi` | `bool` | Whether to split into Web and API controllers |

### Key Methods

#### `__construct(string $moduleName, ControllerAnalysis $controllerAnalysis, SecurityReport $securityReport, ImpactAnalysis $impactAnalysis)`
Create a new AuditReport instance.

#### `addBusinessLogic(string $method, string $description, string $lines): void`
Add a business logic item to extract.

#### `addQueryToMove(string $method, string $query, string $type, string $lines): void`
Add a database query to move to Repository.

#### `addRecommendation(string $recommendation): void`
Add a recommendation for refactoring.

#### `hasSecurityIssues(): bool`
Check if the module has security issues.

#### `hasCriticalSecurityIssues(): bool`
Check if the module has critical security issues.

#### `getSummary(): array`
Get summary statistics.

#### `toMarkdown(): string`
Convert report to markdown format for human-readable output. This is the primary method for generating audit reports.

#### `toArray(): array`
Convert report to array representation.

#### `toJson(): string`
Convert report to JSON string.

#### `fromArray(array $data): self`
Create instance from array data.

#### `fromJson(string $json): self`
Create instance from JSON string.

### Example Usage

```php
use App\Libraries\Refactor\Models\AuditReport;
use App\Libraries\Refactor\Models\ControllerAnalysis;
use App\Libraries\Refactor\Models\SecurityReport;
use App\Libraries\Refactor\Models\ImpactAnalysis;

// Create component analyses
$controllerAnalysis = new ControllerAnalysis();
// ... populate controller analysis

$securityReport = new SecurityReport('Transaksi');
// ... populate security report

$impactAnalysis = new ImpactAnalysis('Transaksi');
// ... populate impact analysis

// Create audit report
$auditReport = new AuditReport(
    'Transaksi',
    $controllerAnalysis,
    $securityReport,
    $impactAnalysis
);

$auditReport->controllerPath = 'app/Controllers/Transaksi.php';
$auditReport->modelPaths = ['app/Models/TransaksiModel.php'];

// Add findings
$auditReport->addBusinessLogic(
    'store',
    'Transaction validation and calculation logic',
    '45-67'
);

$auditReport->addQueryToMove(
    'store',
    'INSERT INTO transaksi ...',
    'INSERT',
    '55-58'
);

$auditReport->addRecommendation('Create TransaksiService to handle business logic');
$auditReport->addRecommendation('Create TransaksiRepository for database operations');

// Generate markdown report
$markdown = $auditReport->toMarkdown();
file_put_contents('audit_report_transaksi.md', $markdown);

// Or save as JSON
$json = $auditReport->toJson();
file_put_contents('audit_report_transaksi.json', $json);
```

## Markdown Report Format

The `toMarkdown()` method generates a comprehensive, human-readable report with the following sections:

1. **Header** - Module name, generation timestamp, complexity
2. **Controller Analysis** - Metrics, characteristics, dependencies, methods table
3. **Business Logic to Extract** - List of business logic items to move to Service layer
4. **Database Queries to Move** - List of queries to move to Repository layer
5. **Security Analysis** - Vulnerability breakdown with severity levels
6. **Impact Analysis** - Risk level, dependent modules, affected routes, warnings
7. **Recommendations** - List of actionable recommendations
8. **Summary** - Quick overview of key metrics
9. **Important Notice** - Reminder that this is audit-only (no code modified)

## Integration with Audit Generator

These models are used by the `AuditGenerator` component:

```php
use App\Libraries\Refactor\Analysis\AuditGenerator;

$auditGenerator = new AuditGenerator($inventory, $scanner, $impactAnalyzer, $codeAnalyzer);
$auditReport = $auditGenerator->generateAudit('Transaksi');

// Save markdown report
$markdown = $auditReport->toMarkdown();
file_put_contents("audits/audit_transaksi.md", $markdown);

// Save JSON for programmatic access
$json = $auditReport->toJson();
file_put_contents("audits/audit_transaksi.json", $json);
```

## Design Principles

### 1. Read-Only Audit Phase
The audit phase **NEVER** modifies code. These models only store analysis results.

### 2. Comprehensive Analysis
The models capture all information needed to make informed refactoring decisions:
- Structural metrics
- Security vulnerabilities
- Impact on other modules
- Specific code locations (line numbers)
- Actionable recommendations

### 3. Multiple Output Formats
- **JSON**: For programmatic access and storage
- **Markdown**: For human-readable reports
- **Array**: For internal processing

### 4. Serialization Support
All models support:
- `toArray()` / `fromArray()` for array conversion
- `toJson()` / `fromJson()` for JSON serialization

### 5. Type Safety
All properties use PHP type hints for better IDE support and runtime safety.

## Requirements Mapping

These models satisfy the following requirements:

- **REQ-5.6**: Generate comprehensive audit report with all findings
  - ✅ `AuditReport` contains all analysis results
  - ✅ `toMarkdown()` generates human-readable report
  - ✅ Includes controller analysis, security findings, impact analysis, recommendations

- **REQ-5.7**: Ensure audit phase does not modify any code
  - ✅ Models are data-only (no code modification methods)
  - ✅ Markdown report includes explicit notice: "This is an audit report only. No code has been modified."
  - ✅ Models only store analysis results, never modify source files

## Testing

Both models have been tested for:
- ✅ Object creation and property assignment
- ✅ Method functionality (addMethod, addBusinessLogic, etc.)
- ✅ JSON serialization and deserialization
- ✅ Markdown generation
- ✅ Summary generation
- ✅ Complexity calculation
- ✅ PHP syntax validation

## File Locations

- `app/Libraries/Refactor/Models/ControllerAnalysis.php`
- `app/Libraries/Refactor/Models/AuditReport.php`

## Dependencies

These models depend on:
- `App\Libraries\Refactor\Models\SecurityReport`
- `App\Libraries\Refactor\Models\Vulnerability`
- `App\Libraries\Refactor\Models\ImpactAnalysis`
- `DateTime` (PHP built-in)

## Next Steps

After implementing these models, the next steps are:
1. Implement the `AuditGenerator` component that uses these models
2. Implement the `CodeAnalyzer` to populate `ControllerAnalysis`
3. Integrate with the CLI command for generating audit reports
4. Create audit report storage mechanism (file system or database)
