# Analysis Component

This directory contains components for analyzing CodeIgniter 4 code to understand dependencies, relationships, and impact of changes.

## Components

### ASTParser

**Purpose**: Wrapper class for PHP-Parser library focused on dependency extraction.

**Key Features**:
- Extract use statements (imports) from PHP files
- Extract class instantiations (`new ClassName()`)
- Extract method calls (both instance and static)
- Extract constructor dependencies
- Parse comprehensive class information

**Usage**:
```php
use App\Libraries\Refactor\Analysis\ASTParser;

$parser = new ASTParser();

// Extract all dependencies
$dependencies = $parser->extractAllDependencies('app/Controllers/UserController.php');

// Extract specific dependency types
$uses = $parser->extractUseStatements($filePath);
$instantiations = $parser->extractClassInstantiations($filePath);
$calls = $parser->extractMethodCalls($filePath);
```

**Status**: ✅ Implemented (Task 3.1)

### DependencyAnalyzer (Planned)

**Purpose**: Build dependency graphs showing how modules depend on each other.

**Key Features**:
- Parse controller and model dependencies
- Build dependency graph with nodes and edges
- Calculate impact scores
- Detect circular dependencies

**Status**: 🔄 Planned (Task 3.2)

### ImpactAnalyzer (Planned)

**Purpose**: Analyze the impact of refactoring a specific module.

**Key Features**:
- Identify affected modules
- Identify affected routes and views
- Estimate number of files to be modified
- Calculate risk assessment

**Status**: 🔄 Planned (Task 6.1)

### PrioritizationSystem (Planned)

**Purpose**: Rank modules for refactoring based on dependencies and impact.

**Key Features**:
- Identify leaf modules (safe starting points)
- Identify core modules (high-risk targets)
- Calculate priority scores
- Generate recommended refactoring order

**Status**: 🔄 Planned (Task 6.2)

## Architecture

The Analysis component follows a layered architecture:

```
┌─────────────────────────────────────┐
│     PrioritizationSystem            │
│  (Ranks modules for refactoring)    │
└──────────────┬──────────────────────┘
               │
               ▼
┌─────────────────────────────────────┐
│       ImpactAnalyzer                │
│  (Calculates refactoring impact)    │
└──────────────┬──────────────────────┘
               │
               ▼
┌─────────────────────────────────────┐
│     DependencyAnalyzer              │
│  (Builds dependency graphs)         │
└──────────────┬──────────────────────┘
               │
               ▼
┌─────────────────────────────────────┐
│         ASTParser                   │
│  (Extracts dependencies from code)  │
└─────────────────────────────────────┘
```

## Design Principles

### Single Responsibility
Each component has a focused responsibility:
- **ASTParser**: Code parsing and dependency extraction
- **DependencyAnalyzer**: Graph building and analysis
- **ImpactAnalyzer**: Impact calculation
- **PrioritizationSystem**: Module ranking

### Dependency Injection
All components use constructor injection for dependencies, making them:
- Easy to test with mocks
- Flexible and configurable
- Loosely coupled

### Separation of Concerns
- **Discovery** (Discovery directory): Finding modules and files
- **Analysis** (this directory): Understanding relationships and dependencies
- **Security** (Security directory): Identifying vulnerabilities
- **Generation** (Generation directory): Creating new code
- **Execution** (Execution directory): Applying refactorings

## Testing

All components in this directory have comprehensive unit tests in `tests/unit/Refactor/Analysis/`.

### Running Tests

```bash
# Run all Analysis tests
vendor/bin/phpunit tests/unit/Refactor/Analysis/

# Run specific component tests
vendor/bin/phpunit tests/unit/Refactor/Analysis/ASTParserTest.php
```

## Data Flow

```
Source Code Files
       ↓
   ASTParser (extracts dependencies)
       ↓
   DependencyAnalyzer (builds graph)
       ↓
   ImpactAnalyzer (calculates impact)
       ↓
   PrioritizationSystem (ranks modules)
       ↓
   Recommended Refactoring Order
```

## Integration Points

### With Discovery Component
- Uses `ModuleInventory` to get list of modules to analyze
- Uses `FileScanner` to locate files

### With Models Component
- Produces `DependencyGraph` data model
- Produces `PriorityScore` data model

### With Security Component
- Provides dependency information for security analysis
- Helps identify security-critical modules

### With Execution Component
- Provides prioritization for refactoring order
- Provides impact analysis for rollback decisions

## Future Enhancements

1. **Caching**: Cache parsed AST and dependency graphs for performance
2. **Incremental Analysis**: Only re-analyze changed files
3. **Visualization**: Generate visual dependency graphs (Mermaid, GraphViz)
4. **Metrics**: Calculate code metrics (complexity, coupling, cohesion)
5. **AI Integration**: Use AI to suggest optimal refactoring strategies

## References

- [PHP-Parser Documentation](https://github.com/nikic/PHP-Parser)
- [Design Document](../../../.kiro/specs/security-architecture-refactor/design.md)
- [Requirements Document](../../../.kiro/specs/security-architecture-refactor/requirements.md)
