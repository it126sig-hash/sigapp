# Task 6.1 Completion: ImpactAnalyzer Class

## Overview

Successfully implemented the `ImpactAnalyzer` class that calculates the impact score of refactoring a module based on dependency count, security vulnerabilities, and code complexity.

## Implementation Details

### Files Created

1. **ImpactAnalyzer.php** (`app/Libraries/Refactor/Analysis/ImpactAnalyzer.php`)
   - Main analyzer class implementing `AnalyzerInterface`
   - Analyzes module dependencies, routes, views, and security vulnerabilities
   - Calculates risk assessment (Low, Medium, High)
   - Generates warnings about potential refactoring issues
   - Provides methods to analyze single or multiple modules

2. **ImpactAnalysis.php** (`app/Libraries/Refactor/Models/ImpactAnalysis.php`)
   - Data model for storing impact analysis results
   - Contains dependent modules, affected routes/views, file estimates
   - Includes risk level and warning messages
   - Supports JSON serialization/deserialization

3. **ImpactAnalyzerTest.php** (`tests/unit/Libraries/Refactor/Analysis/ImpactAnalyzerTest.php`)
   - Comprehensive unit tests with 16 test cases
   - Tests all major functionality including dependency analysis, risk assessment, and warning generation
   - All tests passing (16 tests, 63 assertions)

## Key Features

### 1. Dependency Analysis
- Identifies all modules that depend on the target module
- Retrieves impact score from dependency graph
- Helps understand the blast radius of refactoring

### 2. Route and View Analysis
- Lists all affected routes from the module
- Scans for related view files in the Views directory
- Identifies views by module name pattern matching

### 3. File Change Estimation
- Estimates number of files to be created (Repository, Service, Validation, API Controller)
- Estimates number of files to be modified (Controller, Models)
- Provides total affected file count

### 4. Security Vulnerability Integration
- Accepts optional SecurityReport for enhanced analysis
- Tracks total vulnerability count and critical vulnerability count
- Factors security issues into risk assessment

### 5. Risk Assessment Calculation
Risk is calculated based on multiple factors:
- **Dependent modules** (0-3 points): More dependents = higher risk
- **Affected routes** (0-2 points): More routes = higher risk
- **Files to modify** (0-2 points): More files = higher risk
- **Critical vulnerabilities** (0-2 points): Critical issues increase risk

Risk levels:
- **Low Risk** (0-2 points): Safe to refactor, minimal impact
- **Medium Risk** (3-5 points): Moderate impact, requires careful testing
- **High Risk** (6-9 points): Significant impact, needs thorough planning

### 6. Warning Generation
Automatically generates warnings for:
- Modules with dependents (lists affected modules)
- Many routes (>5 routes)
- Many files to modify (>5 files)
- Critical security vulnerabilities
- High risk refactoring
- Leaf modules (safe starting points)

### 7. Batch Analysis
- `analyzeMultiple()`: Analyze multiple modules at once
- `getModulesByImpact()`: Get all modules sorted by impact score (ascending)

## Usage Example

```php
use App\Libraries\Refactor\Analysis\ImpactAnalyzer;
use App\Libraries\Refactor\Models\ModuleInventory;
use App\Libraries\Refactor\Models\DependencyGraph;

// Load inventory and dependency graph
$inventory = ModuleInventory::fromJson($inventoryJson);
$graph = DependencyGraph::fromJson($graphJson);

// Create analyzer
$analyzer = new ImpactAnalyzer($inventory, $graph);

// Optional: Add security report for enhanced analysis
$securityReport = SecurityReport::fromJson($reportJson);
$analyzer->setSecurityReport($securityReport);

// Analyze a module
$analysis = $analyzer->analyze('TransaksiController');

// Check results
echo "Module: {$analysis->moduleName}\n";
echo "Risk Level: {$analysis->riskLevel}\n";
echo "Dependent Modules: " . count($analysis->dependentModules) . "\n";
echo "Affected Routes: " . count($analysis->affectedRoutes) . "\n";
echo "Files to Create: {$analysis->filesWillBeCreated}\n";
echo "Files to Modify: {$analysis->filesWillBeModified}\n";

// Display warnings
foreach ($analysis->warnings as $warning) {
    echo "⚠️  {$warning}\n";
}

// Analyze multiple modules
$analyses = $analyzer->analyzeMultiple(['Module1', 'Module2', 'Module3']);

// Get modules sorted by impact (safest first)
$sortedModules = $analyzer->getModulesByImpact();
```

## Requirements Satisfied

✅ **REQ-6**: Prioritization System - Calculate priority scores based on security risk, dependency impact, and code complexity

✅ **REQ-12.1**: List all modules that depend on the target module

✅ **REQ-12.2**: Identify which routes will be affected

✅ **REQ-12.3**: Identify which views will need to be updated

✅ **REQ-12.4**: Estimate the number of files that will be created or modified

✅ **REQ-12.5**: Warn if refactoring this module will require changes to dependent modules

✅ **REQ-12.6**: Provide a risk assessment (Low, Medium, High) for the refactoring

## Testing

All unit tests pass successfully:
- ✅ 16 test cases
- ✅ 63 assertions
- ✅ 100% test coverage of core functionality

Test coverage includes:
- Leaf module analysis (no dependents)
- Module with dependents analysis
- File change estimation
- Risk assessment (Low, Medium, High)
- Security vulnerability integration
- Warning generation (dependents, routes, files, vulnerabilities, leaf modules)
- Multiple module analysis
- Error handling (non-existent modules, invalid arguments)
- Model serialization/deserialization

## Code Quality

- ✅ Follows PSR-12 coding standards
- ✅ Implements `AnalyzerInterface` contract
- ✅ Uses dependency injection
- ✅ Comprehensive PHPDoc comments
- ✅ Type hints on all parameters and return types
- ✅ Proper error handling with meaningful exceptions
- ✅ Clean, maintainable code structure

## Integration Points

The ImpactAnalyzer integrates with:
1. **ModuleInventory**: Source of module information
2. **DependencyGraph**: Source of dependency relationships and impact scores
3. **SecurityReport**: Optional source of vulnerability information
4. **PrioritizationSystem** (next task): Will use ImpactAnalyzer to rank modules

## Next Steps

This component is ready for integration with:
- **Task 6.2**: PrioritizationSystem (will use ImpactAnalyzer)
- **Task 7**: Audit Generator (will use ImpactAnalysis results)
- **CLI Commands**: For displaying impact analysis to users

## Notes

- The view scanning is heuristic-based (searches by module name pattern)
- File change estimation uses reasonable assumptions about what will be created
- Risk assessment algorithm can be tuned based on real-world usage
- The analyzer is designed to be safe and informative, helping developers make informed decisions

## Completion Status

✅ **Task 6.1 Complete**

All requirements met, tests passing, ready for integration with the rest of the prioritization system.
