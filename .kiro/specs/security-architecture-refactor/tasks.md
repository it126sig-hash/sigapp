# Implementation Plan: Security Architecture Refactor

## Overview

This implementation plan transforms a CodeIgniter 4 application from fat controllers with mixed concerns into a clean, secure architecture following the Thin Controller → Service → Repository pattern. The system provides automated discovery, dependency analysis, security scanning, audit generation, and safe refactoring execution with rollback capabilities.

## Tasks

- [ ] 1. Set up project structure and core interfaces
  - Create directory structure for refactoring system components
  - Define core data model classes (Module, ModuleInventory, DependencyGraph, SecurityReport, etc.)
  - Set up namespace structure following PSR-4 autoloading
  - Create base interfaces for components
  - _Requirements: 1.1, 1.2, 1.3, 1.4, 1.5_

- [ ] 2. Implement Module Discovery Component
  - [ ] 2.1 Create FileScanner and CodeParser utilities
    - Implement FileScanner to recursively scan directories for PHP files
    - Implement CodeParser using nikic/php-parser for AST parsing
    - Add file filtering logic (controllers, models, services, repositories)
    - _Requirements: 1.1, 1.2_
  
  - [ ] 2.2 Implement ModuleDiscovery class
    - Implement scanControllers() to identify all controller files
    - Implement scanModels() to identify all model files
    - Implement scanServices() to detect existing service classes
    - Implement scanRepositories() to detect existing repository classes
    - Implement identifyRelationships() to link controllers with their models
    - _Requirements: 1.1, 1.2, 1.3, 1.4, 1.5_
  
  - [ ] 2.3 Implement ModuleInventory and Module data classes
    - Create Module class with properties (name, paths, routes, methods)
    - Create ModuleInventory class with collection management
    - Implement toJson() and fromJson() serialization methods
    - Add getModule() lookup method
    - _Requirements: 1.4_
  
  - [ ]* 2.4 Write unit tests for Module Discovery
    - Test scanning controllers with various file structures
    - Test scanning models with namespaced and non-namespaced classes
    - Test relationship identification between controllers and models
    - Test handling missing directories gracefully
    - Test detecting existing services and repositories

- [ ] 3. Implement Dependency Analyzer Component
  - [ ] 3.1 Create ASTParser wrapper for dependency extraction
    - Implement parsing of use statements to identify imports
    - Implement parsing of method calls to identify runtime dependencies
    - Implement parsing of constructor dependencies
    - Add error handling for malformed PHP code
    - _Requirements: 2.1, 2.2_
  
  - [ ] 3.2 Implement DependencyAnalyzer class
    - Implement parseControllerDependencies() using AST analysis
    - Implement parseModelDependencies() using AST analysis
    - Implement detectCircularDependencies() using graph traversal
    - Implement calculateImpactScores() based on dependent count
    - _Requirements: 2.1, 2.2, 2.3, 2.4, 2.5_
  
  - [ ] 3.3 Implement DependencyGraph data class
    - Create DependencyGraph with nodes, edges, and impact scores
    - Implement getDependents() to find modules that depend on a given module
    - Implement getDependencies() to find modules a given module depends on
    - Implement getImpactScore() lookup method
    - Implement toMermaid() for visual graph representation
    - _Requirements: 2.3, 2.4, 2.6_
  
  - [ ]* 3.4 Write unit tests for Dependency Analyzer
    - Test parsing controller dependencies from use statements
    - Test parsing model dependencies from method calls
    - Test building dependency graph from parsed dependencies
    - Test calculating impact scores correctly
    - Test detecting circular dependencies
    - Test handling malformed PHP code

- [ ] 4. Implement Security Scanner Component
  - [ ] 4.1 Create security rule definitions
    - Define regex patterns for SQL injection detection (raw queries, string concatenation)
    - Define patterns for CSRF missing detection (form submissions without protection)
    - Define patterns for XSS detection (unescaped output in views)
    - Define patterns for insecure authentication (missing auth checks)
    - Define patterns for hardcoded credentials detection
    - Define patterns for missing validation detection
    - Define patterns for insecure file upload detection
    - _Requirements: 4.1, 4.2, 4.3, 4.4, 4.5, 4.6, 4.7_
  
  - [ ] 4.2 Implement SecurityScanner class
    - Implement scanModule() to analyze a module for vulnerabilities
    - Implement detectSQLInjection() using pattern matching
    - Implement detectXSS() using pattern matching
    - Implement detectCSRFMissing() using pattern matching
    - Implement detectInsecureAuth() using pattern matching
    - Implement detectHardcodedCredentials() using pattern matching
    - Implement detectMissingValidation() using pattern matching
    - Implement detectInsecureFileUpload() using pattern matching
    - _Requirements: 4.1, 4.2, 4.3, 4.4, 4.5, 4.6, 4.7_
  
  - [ ] 4.3 Implement SecurityReport and Vulnerability data classes
    - Create Vulnerability class with type, severity, location, description, recommendation
    - Create SecurityReport class with module name, vulnerabilities array, timestamp
    - Implement getBySeverity() filtering method
    - Implement getCriticalCount() aggregation method
    - Implement toJson() serialization method
    - _Requirements: 4.8_
  
  - [ ]* 4.4 Write unit tests for Security Scanner
    - Test detecting SQL injection in raw queries
    - Test detecting missing CSRF protection
    - Test detecting XSS vulnerabilities in views
    - Test detecting insecure authentication patterns
    - Test detecting hardcoded credentials
    - Test detecting missing input validation
    - Test detecting insecure file uploads

- [ ] 5. Checkpoint - Ensure all tests pass
  - Ensure all tests pass, ask the user if questions arise.

- [ ] 6. Implement Prioritization System Component
  - [ ] 6.1 Implement ImpactAnalyzer class
    - Calculate impact metrics based on dependency graph
    - Identify modules affected by refactoring a specific module
    - Generate impact analysis reports
    - _Requirements: 12.1, 12.2, 12.3, 12.4, 12.5, 12.6_
  
  - [ ] 6.2 Implement PrioritizationSystem class
    - Implement identifyLeafModules() to find modules with no dependents
    - Implement identifyCoreModules() to find modules with many dependents
    - Implement calculatePriorityScore() combining impact, depth, and security factors
    - Implement prioritize() to generate ordered refactoring list
    - Implement applyManualOverride() for user-specified priorities
    - _Requirements: 3.1, 3.2, 3.3, 3.4, 3.5_
  
  - [ ] 6.3 Implement PriorityScore data class
    - Create PriorityScore with module name, score, impact, depth, vulnerabilities
    - Add category classification (LEAF, CORE, INTERMEDIATE)
    - Add manual priority override support
    - _Requirements: 3.1, 3.2, 3.3_
  
  - [ ]* 6.4 Write unit tests for Prioritization System
    - Test identifying leaf modules (no dependents)
    - Test identifying core modules (many dependents)
    - Test calculating priority scores
    - Test manual priority overrides
    - Test generating recommended refactoring order

- [ ] 7. Implement Audit Generator Component
  - [ ] 7.1 Create CodeAnalyzer utility
    - Implement analysis of controller structure (method count, LOC, complexity)
    - Implement identification of business logic patterns
    - Implement identification of database query patterns
    - Implement complexity estimation algorithm
    - _Requirements: 5.1, 5.2, 5.3_
  
  - [ ] 7.2 Implement AuditGenerator class
    - Implement generateAudit() orchestration method
    - Implement analyzeControllerStructure() for controller analysis
    - Implement identifyBusinessLogic() to find logic to extract
    - Implement identifyDatabaseQueries() to find queries to move
    - Implement estimateComplexity() (SIMPLE, MEDIUM, COMPLEX)
    - _Requirements: 5.1, 5.2, 5.3, 5.4, 5.5, 5.7_
  
  - [ ] 7.3 Implement AuditReport and ControllerAnalysis data classes
    - Create ControllerAnalysis with metrics and flags
    - Create AuditReport with all analysis results
    - Implement toMarkdown() for human-readable report generation
    - _Requirements: 5.6_
  
  - [ ]* 7.4 Write unit tests for Audit Generator
    - Test controller structure analysis
    - Test business logic identification
    - Test database query identification
    - Test complexity estimation
    - Test audit report generation

- [ ] 8. Implement Code Generation Components
  - [ ] 8.1 Create CodeGenerator utility
    - Implement template-based code generation
    - Implement PSR-12 code formatting
    - Implement namespace and use statement management
    - Implement PHPDoc comment generation
    - Add PHP syntax validation for generated code
    - _Requirements: 15.1, 15.2, 15.3, 15.4, 15.6_
  
  - [ ] 8.2 Implement QueryAnalyzer utility
    - Parse raw SQL queries to understand structure
    - Convert raw queries to Query Builder syntax
    - Identify parameters for binding
    - _Requirements: 8.4_
  
  - [ ] 8.3 Implement ValidationExtractor utility
    - Extract validation rules from controller code
    - Convert inline validation to rule class format
    - Generate validation error messages
    - _Requirements: 13.1, 13.2, 13.3, 13.4, 13.5_

- [ ] 9. Implement Repository Generator Component
  - [ ] 9.1 Implement RepositoryGenerator class
    - Implement generate() orchestration method
    - Implement generateCrudMethods() for standard CRUD operations
    - Implement convertToQueryBuilder() for query conversion
    - Implement generateComplexQueryMethod() for custom queries
    - Implement addParameterBinding() for security
    - _Requirements: 8.1, 8.2, 8.3, 8.4, 8.5, 8.6, 8.7_
  
  - [ ]* 9.2 Write unit tests for Repository Generator
    - Test repository generation with CRUD methods
    - Test query conversion to Query Builder
    - Test parameter binding for security
    - Test generated code syntax validity

- [ ] 10. Implement Service Generator Component
  - [ ] 10.1 Implement ServiceGenerator class
    - Implement generate() orchestration method
    - Implement extractValidationRules() from controller code
    - Implement generateServiceMethod() for business logic
    - Implement addTransactionManagement() for data consistency
    - Implement generateResultObject() for structured responses
    - _Requirements: 7.1, 7.2, 7.3, 7.4, 7.5, 7.6, 7.7_
  
  - [ ]* 10.2 Write unit tests for Service Generator
    - Test service generation with business logic extraction
    - Test validation rule extraction
    - Test transaction management addition
    - Test result object generation
    - Test generated code syntax validity

- [ ] 11. Checkpoint - Ensure all tests pass
  - Ensure all tests pass, ask the user if questions arise.

- [ ] 12. Implement Controller Refactorer Component
  - [ ] 12.1 Implement ControllerSplitter utility
    - Implement split() to separate web and API methods
    - Implement identifyWebMethods() based on view rendering
    - Implement identifyApiMethods() based on JSON responses
    - Implement generateWebController() with view responses
    - Implement generateApiController() with JSON responses
    - _Requirements: 10.1, 10.2, 10.3, 10.4, 10.5, 10.6, 10.7_
  
  - [ ] 12.2 Implement ControllerRefactorer class
    - Implement refactor() orchestration method
    - Implement injectService() for dependency injection
    - Implement replaceBusinessLogicWithServiceCalls() to delegate to services
    - Implement addErrorHandling() for proper error responses
    - Implement splitWebAndApi() using ControllerSplitter
    - _Requirements: 6.1, 6.2, 6.3, 6.4, 6.5, 6.6_
  
  - [ ]* 12.3 Write unit tests for Controller Refactorer
    - Test controller refactoring with service injection
    - Test business logic replacement with service calls
    - Test Web/API controller splitting
    - Test error handling addition
    - Test generated code syntax validity

- [ ] 13. Implement Security Fixer Component
  - [ ] 13.1 Implement SecurityFixer class
    - Implement fix() orchestration method
    - Implement addCSRFProtection() to add CSRF filters
    - Implement addInputValidation() using validation rules
    - Implement addOutputEscaping() in view code
    - Implement replaceRawQueryWithQueryBuilder() for SQL injection prevention
    - Implement addAuthenticationCheck() for protected routes
    - Implement addAuthorizationCheck() for permission-based access
    - Implement addFileUploadValidation() for secure uploads
    - _Requirements: 9.1, 9.2, 9.3, 9.4, 9.5, 9.6, 9.7, 9.8_
  
  - [ ]* 13.2 Write unit tests for Security Fixer
    - Test CSRF protection addition
    - Test input validation addition
    - Test output escaping addition
    - Test raw query replacement with Query Builder
    - Test authentication check addition
    - Test authorization check addition
    - Test file upload validation addition

- [ ] 14. Implement Backup and Rollback System
  - [ ] 14.1 Implement BackupManager class
    - Implement createBackup() to create timestamped backups
    - Implement restoreBackup() to rollback changes
    - Implement listBackups() to show available backups
    - Implement deleteBackup() for cleanup
    - Add backup metadata tracking (files, timestamp, module)
    - _Requirements: 11.1, 11.4_
  
  - [ ]* 14.2 Write unit tests for Backup Manager
    - Test backup creation before refactoring
    - Test automatic rollback on failure
    - Test manual rollback by backup ID
    - Test backup cleanup

- [ ] 15. Implement Progress Tracker Component
  - [ ] 15.1 Implement ProgressTracker class
    - Create progress tracker JSON structure
    - Implement status tracking (NOT_STARTED, AUDITED, IN_PROGRESS, COMPLETED, FAILED)
    - Implement updateModuleStatus() to record progress
    - Implement getProgress() to calculate overall percentage
    - Implement generateReport() for progress visualization
    - Add filtering by status and priority
    - _Requirements: 14.1, 14.2, 14.3, 14.4, 14.5, 14.6_
  
  - [ ]* 15.2 Write unit tests for Progress Tracker
    - Test status tracking for modules
    - Test progress calculation
    - Test report generation
    - Test filtering by status

- [ ] 16. Implement Refactor Engine Component
  - [ ] 16.1 Implement RefactorEngine orchestration class
    - Implement refactor() main orchestration method
    - Implement createBackup() integration with BackupManager
    - Implement executeStep() for incremental refactoring
    - Implement rollback() for failure recovery
    - Implement runTests() integration (optional)
    - Add step tracking and logging
    - _Requirements: 11.1, 11.2, 11.3, 11.4, 11.5, 11.6_
  
  - [ ] 16.2 Implement RefactorOptions and RefactorResult data classes
    - Create RefactorOptions with boolean flags for each refactoring step
    - Create RefactorResult with success status, files changed, errors
    - Implement toMarkdown() for result report generation
    - _Requirements: 11.5_
  
  - [ ]* 16.3 Write integration tests for Refactor Engine
    - Test complete refactoring workflow
    - Test refactoring with rollback on failure
    - Test refactoring with Web/API split
    - Test refactoring fixes security issues

- [ ] 17. Checkpoint - Ensure all tests pass
  - Ensure all tests pass, ask the user if questions arise.

- [ ] 18. Implement CLI Command Interface
  - [ ] 18.1 Create CLI command structure using CodeIgniter 4 Commands
    - Create base command class with common utilities
    - Implement discover command for module discovery
    - Implement analyze command for dependency analysis
    - Implement scan command for security scanning
    - Implement prioritize command for module prioritization
    - _Requirements: 1.1, 2.1, 4.1, 3.1_
  
  - [ ] 18.2 Create audit and refactor CLI commands
    - Implement audit command for generating audit reports
    - Implement refactor command for executing refactoring
    - Implement rollback command for restoring backups
    - Implement progress command for viewing progress
    - Add command options and flags for customization
    - _Requirements: 5.1, 11.1, 11.4, 14.1_
  
  - [ ] 18.3 Add CLI output formatting and user interaction
    - Implement colored output for success/error/warning messages
    - Add progress bars for long-running operations
    - Add confirmation prompts for destructive operations
    - Implement table formatting for reports
    - Add verbose mode for detailed logging

- [ ] 19. Implement Error Handling System
  - [ ] 19.1 Create RefactorError class and error codes
    - Define error code constants for all error categories
    - Implement RefactorError data class with context
    - Create error logging utility
    - _Requirements: All requirements (error handling is cross-cutting)_
  
  - [ ] 19.2 Add error handling to all components
    - Add try-catch blocks with proper error categorization
    - Implement graceful degradation for non-critical errors
    - Add validation errors with helpful messages
    - Implement critical error handling (backup failures)
    - Add error reporting in CLI commands

- [ ] 20. Create Configuration System
  - [ ] 20.1 Create configuration file structure
    - Define configuration file format (JSON or PHP)
    - Add configuration for directories to scan
    - Add configuration for security rules
    - Add configuration for code generation templates
    - Add configuration for backup location
    - _Requirements: 1.1, 4.1_
  
  - [ ] 20.2 Implement configuration loader
    - Implement configuration file parsing
    - Add configuration validation
    - Provide default configuration values
    - Allow environment-specific overrides

- [ ] 21. Integration and Documentation
  - [ ] 21.1 Wire all components together
    - Create service container for dependency injection
    - Register all components in service container
    - Implement factory methods for component creation
    - Add component lifecycle management
    - _Requirements: 15.5_
  
  - [ ] 21.2 Create user documentation
    - Write README with installation instructions
    - Document CLI command usage with examples
    - Create workflow guide (discover → analyze → audit → refactor)
    - Document configuration options
    - Add troubleshooting guide
  
  - [ ] 21.3 Create developer documentation
    - Document architecture and component interactions
    - Add code examples for extending the system
    - Document security rule format for custom rules
    - Add contribution guidelines

- [ ] 22. Final checkpoint - Ensure all tests pass
  - Ensure all tests pass, ask the user if questions arise.

## Notes

- Tasks marked with `*` are optional test tasks and can be skipped for faster MVP
- Each task references specific requirements for traceability
- Checkpoints ensure incremental validation at major milestones
- The implementation follows a bottom-up approach: utilities → components → orchestration → CLI
- All generated code must follow PSR-12 standards and CodeIgniter 4 conventions
- Security is a primary concern throughout implementation
- Backup and rollback mechanisms are critical for safe refactoring
- The system is designed to be extensible for future enhancements

## Task Dependency Graph

```json
{
  "waves": [
    { "id": 0, "tasks": ["1"] },
    { "id": 1, "tasks": ["2.1", "8.1"] },
    { "id": 2, "tasks": ["2.2", "8.2", "8.3"] },
    { "id": 3, "tasks": ["2.3", "3.1"] },
    { "id": 4, "tasks": ["2.4", "3.2"] },
    { "id": 5, "tasks": ["3.3", "4.1"] },
    { "id": 6, "tasks": ["3.4", "4.2"] },
    { "id": 7, "tasks": ["4.3", "6.1"] },
    { "id": 8, "tasks": ["4.4", "6.2"] },
    { "id": 9, "tasks": ["6.3", "7.1"] },
    { "id": 10, "tasks": ["6.4", "7.2"] },
    { "id": 11, "tasks": ["7.3", "9.1"] },
    { "id": 12, "tasks": ["7.4", "9.2", "10.1"] },
    { "id": 13, "tasks": ["10.2", "12.1"] },
    { "id": 14, "tasks": ["12.2", "13.1"] },
    { "id": 15, "tasks": ["12.3", "13.2", "14.1"] },
    { "id": 16, "tasks": ["14.2", "15.1"] },
    { "id": 17, "tasks": ["15.2", "16.1"] },
    { "id": 18, "tasks": ["16.2", "19.1"] },
    { "id": 19, "tasks": ["16.3", "19.2", "20.1"] },
    { "id": 20, "tasks": ["20.2", "18.1"] },
    { "id": 21, "tasks": ["18.2"] },
    { "id": 22, "tasks": ["18.3", "21.1"] },
    { "id": 23, "tasks": ["21.2", "21.3"] }
  ]
}
```
