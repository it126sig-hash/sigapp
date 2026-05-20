# Task 6.2 Completion: Implement PrioritizationSystem Class

## Summary

Successfully implemented the `PrioritizationSystem` class that identifies leaf modules, core modules, calculates priority scores combining impact and security, applies manual overrides, and generates recommended refactoring order.

## Implementation Details

### Files Created

1. **`app/Libraries/Refactor/Analysis/PrioritizationSystem.php`**
   - Main prioritization system class
   - Implements all required methods for module prioritization
   - Follows CodeIgniter 4 best practices with dependency injection
   - Comprehensive PHPDoc documentation

2. **`tests/unit/Libraries/Refactor/Analysis/PrioritizationSystemTest.php`**
   - Comprehensive unit tests (20 test cases, 60 assertions)
   - Tests all functionality including edge cases
   - All tests passing successfully

## Features Implemented

### 1. Leaf Module Identification (REQ-3.1)
- `identifyLeafModules()`: Identifies modules with no dependents (impact score = 0)
- These are safe starting points for refactoring

### 2. Core Module Identification (REQ-3.2)
- `identifyCoreModules()`: Identifies modules with many dependents (>= 3)
- These are high-risk refactoring targets

### 3. Priority Score Calculation (REQ-3.3)
- `calculatePriorityScore()`: Combines multiple factors:
  - **Base score**: Impact score (number of dependents)
  - **Security factor**: Subtracts 2.0 per critical vulnerability (increases priority)
  - **Depth factor**: Adds 0.5 per dependency level (decreases priority)
- Lower score = higher priority (should be refactored sooner)

### 4. Manual Priority Override (REQ-3.4)
- `applyManualOverride()`: Allows business-critical modules to be prioritized
- Manual overrides take absolute precedence over calculated scores
- Validates priority values (must be >= 1)

### 5. Recommended Refactoring Order (REQ-3.5)
- `prioritize()`: Returns ordered list of `PriorityScore` objects
- `getRecommendedOrder()`: Returns ordered list of module names only
- Sorting logic:
  1. Manual overrides first (by override value)
  2. Then by calculated score (lower = higher priority)
  3. Tiebreaker: critical vulnerabilities (more = higher priority)

## Additional Features

### Helper Methods
- `getPriorityScore(string $module)`: Get priority score for specific module
- `getAllPriorityScores()`: Get all priority scores indexed by module name
- `addSecurityReport(SecurityReport $report)`: Add security report for enhanced prioritization

### Category Classification
- **LEAF**: Modules with no dependents (impact score = 0)
- **CORE**: Modules with many dependents (impact score >= 3)
- **INTERMEDIATE**: Modules with some dependents (1-2)

### Circular Dependency Handling
- Depth calculation includes cycle detection to prevent infinite recursion
- Uses visited array to track traversed modules

## Test Coverage

### Test Cases (20 total)
1. ✅ Identify leaf modules
2. ✅ Identify core modules
3. ✅ Calculate priority score for leaf module
4. ✅ Calculate priority score with critical vulnerabilities
5. ✅ Calculate priority score with dependency depth
6. ✅ Apply manual priority override
7. ✅ Manual override with invalid priority throws exception
8. ✅ Prioritize returns modules in correct order
9. ✅ Prioritize with manual overrides takes precedence
10. ✅ Get recommended order returns module names only
11. ✅ Get priority score returns correct score
12. ✅ Get priority score for non-existent module returns null
13. ✅ Get all priority scores returns all scores
14. ✅ Priority score category for leaf module
15. ✅ Priority score category for core module
16. ✅ Priority score category for intermediate module
17. ✅ Prioritization with equal scores uses critical vulnerabilities as tiebreaker
18. ✅ Circular dependency handling in depth calculation
19. ✅ Empty inventory returns empty results
20. ✅ Complex prioritization scenario

### Test Results
```
PHPUnit 10.5.27
Tests: 20, Assertions: 60, Warnings: 1
Status: ✅ ALL TESTS PASSING
```

## Code Quality

### PSR-12 Compliance
- ✅ Proper indentation and formatting
- ✅ Proper namespacing
- ✅ Type hints on all parameters and return types
- ✅ Comprehensive PHPDoc comments

### Best Practices
- ✅ Dependency injection via constructor
- ✅ Single Responsibility Principle
- ✅ Clear method names and documentation
- ✅ Proper error handling with exceptions
- ✅ Immutable data models (PriorityScore)

## Integration with Existing Components

### Dependencies
- `ModuleInventory`: Source of all modules to prioritize
- `DependencyGraph`: Provides impact scores and dependency relationships
- `SecurityReport`: Optional, enhances prioritization with vulnerability data
- `PriorityScore`: Data model for priority information

### Used By (Future)
- Audit Generator (Task 7.2)
- CLI Commands (Task 17.3)
- Progress Tracker (Task 16.1)

## Example Usage

```php
use App\Libraries\Refactor\Analysis\PrioritizationSystem;
use App\Libraries\Refactor\Models\ModuleInventory;
use App\Libraries\Refactor\Models\DependencyGraph;
use App\Libraries\Refactor\Models\SecurityReport;

// Create prioritization system
$inventory = ModuleInventory::fromJson($inventoryJson);
$graph = DependencyGraph::fromJson($graphJson);
$prioritizer = new PrioritizationSystem($inventory, $graph);

// Add security reports (optional)
$securityReport = SecurityReport::fromJson($reportJson);
$prioritizer->addSecurityReport($securityReport);

// Apply manual overrides (optional)
$prioritizer->applyManualOverride('CriticalModule', 1);

// Get leaf modules (safe starting points)
$leafModules = $prioritizer->identifyLeafModules();

// Get core modules (high-risk targets)
$coreModules = $prioritizer->identifyCoreModules();

// Get recommended refactoring order
$order = $prioritizer->getRecommendedOrder();
// Returns: ['CriticalModule', 'VulnerableModule', 'LeafModule1', ...]

// Get detailed priority scores
$prioritizedScores = $prioritizer->prioritize();
foreach ($prioritizedScores as $score) {
    echo "{$score->module}: {$score->score} ({$score->category})\n";
}
```

## Requirements Validation

| Requirement | Status | Implementation |
|------------|--------|----------------|
| REQ-3.1: Identify leaf modules | ✅ | `identifyLeafModules()` |
| REQ-3.2: Identify core modules | ✅ | `identifyCoreModules()` |
| REQ-3.3: Calculate priority score | ✅ | `calculatePriorityScore()` |
| REQ-3.4: Manual priority override | ✅ | `applyManualOverride()` |
| REQ-3.5: Generate refactoring order | ✅ | `prioritize()`, `getRecommendedOrder()` |

## Next Steps

Task 6.2 is complete. The next task in the sequence is:

- **Task 6.3**: Implement PriorityScore data model (already exists, created in Task 1)
- **Task 6.4**: Write unit tests for Prioritization System (completed as part of this task)

The Prioritization System Component (Task 6) is now complete and ready for integration with:
- Audit Generator (Task 7)
- CLI Commands (Task 17)
- Progress Tracker (Task 16)

## Notes

- All sub-tasks for Task 6.2 have been implemented:
  - ✅ Implement identifyLeafModules() for safe starting points
  - ✅ Implement identifyCoreModules() for high-risk targets
  - ✅ Implement calculatePriorityScore() combining impact and security
  - ✅ Implement applyManualOverride() for business priorities
  - ✅ Generate recommended refactoring order

- The implementation handles edge cases:
  - Empty inventory
  - Circular dependencies
  - Modules without security reports
  - Equal priority scores (uses tiebreaker)
  - Invalid manual priority values

- Manual overrides take absolute precedence, which is the correct behavior for business-critical modules that must be prioritized regardless of calculated scores.
