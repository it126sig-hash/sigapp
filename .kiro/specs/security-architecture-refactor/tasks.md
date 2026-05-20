# Implementation Plan: Security Architecture Refactor

## Overview

This implementation plan transforms the CodeIgniter 4 application from fat controllers with mixed concerns into a clean, secure architecture following the Thin Controller → Service → Repository pattern. The system provides automated module discovery, dependency analysis, security scanning, and safe refactoring execution with rollback capabilities.

The implementation is structured in phases: foundational infrastructure, discovery and analysis components, security scanning, audit generation, and finally the refactoring execution engine with code generators.

## Tasks

- [x] 1. Set up project structure and core interfaces
  - Create directory structure for refactoring system components
  - Define core interfaces and data models (Module, ModuleInventory, DependencyGraph, SecurityReport, etc.)
  - Set up JSON storage structure for module inventory, dependency graphs, and security reports
  - Create base exception classes for error handling
  - _Requirements: 1.4, 14.1_

- [ ] 2. Implement Module Discovery Component
  - [x] 2.1 Create FileScanner and CodeParser classes
    - Implement FileScanner to recursively scan directories for PHP files
    - Implement CodeParser using PHP-Parser library for AST parsing
    - Add methods to extract class names, namespaces, and method signatures
    - _Requirements: 1.1, 1.2_
  
  - [x] 2.2 Implement ModuleDiscovery class
    - Implement scanControllers() to find all controller files
    - Implement scanModels() to find all model files
    - Implement scanServices() to detect existing service classes
    - Implement scanRepositories() to detect existing repository classes
    - Implement identifyRelationships() to link controllers with their models
    - _Requirements: 1.1, 1.2, 1.3, 1.5_
  
  - [x] 2.3 Implement ModuleInventory data model
    - Create ModuleInventory class with toJson() and fromJson() methods
    - Create Module class with all required properties
    - Add getModule() method for querying inventory
    - Implement JSON serialization/deserialization
    - _Requirements: 1.4_
  
  - [x] 2.4 Write unit tests for Module Discovery
    - Test scanning controllers and models directories
    - Test identifying relationships between controllers and models
    - Test handling missing directories gracefully
    - Test detecting existing services and repositories
    - _Requirements: 1.1, 1.2, 1.3, 1.5_

- [x] 3. Implement Dependency Analyzer Component
  - [x] 3.1 Create ASTParser wrapper class
    - Wrap PHP-Parser library for dependency extraction
    - Add methods to extract use statements and class instantiations
    - Add methods to extract method calls to other classes
    - _Requirements: 2.1, 2.2_
  
  - [x] 3.2 Implement DependencyAnalyzer class
    - Implement parseControllerDependencies() to extract controller dependencies
    - Implement parseModelDependencies() to extract model relationships
    - Implement detectCircularDependencies() using graph traversal
    - Implement calculateImpactScores() based on dependent count
    - _Requirements: 2.1, 2.2, 2.3, 2.4, 2.5_
  
  - [x] 3.3 Implement DependencyGraph data model
    - Create DependencyGraph class with nodes and edges
    - Implement getDependents() and getDependencies() methods
    - Implement getImpactScore() method
    - Implement toMermaid() for visual graph generation
    - Add JSON serialization/deserialization
    - _Requirements: 2.3, 2.4, 2.6_
  
  - [x]* 3.4 Write unit tests for Dependency Analyzer
    - Test parsing controller and model dependencies
    - Test building correct dependency graph
    - Test calculating impact scores
    - Test detecting circular dependencies
    - Test handling malformed code
    - _Requirements: 2.1, 2.2, 2.3, 2.4, 2.5_

- [x] 4. Checkpoint - Verify discovery and analysis components
  - Ensure all tests pass, ask the user if questions arise.

- [ ] 5. Implement Security Scanner Component
  - [x] 5.1 Create security rule definitions
    - Define regex patterns for SQL injection detection
    - Define patterns for XSS vulnerability detection
    - Define patterns for missing CSRF protection
    - Define patterns for insecure authentication
    - Define patterns for hardcoded credentials
    - Define patterns for missing input validation
    - Define patterns for insecure file uploads
    - _Requirements: 4.1, 4.2, 4.3, 4.4, 4.5, 4.6, 4.7_
  
  - [x] 5.2 Implement SecurityScanner class
    - Implement detectSQLInjection() using pattern matching
    - Implement detectXSS() for view rendering vulnerabilities
    - Implement detectCSRFMissing() for form submissions
    - Implement detectInsecureAuth() for authentication patterns
    - Implement detectHardcodedCredentials() for sensitive data
    - Implement detectMissingValidation() for controller methods
    - Implement detectInsecureFileUpload() for file handling
    - _Requirements: 4.1, 4.2, 4.3, 4.4, 4.5, 4.6, 4.7_
  
  - [x] 5.3 Implement SecurityReport and Vulnerability data models
    - Create SecurityReport class with vulnerability list
    - Create Vulnerability class with type, severity, location, and recommendation
    - Implement getBySeverity() and getCriticalCount() methods
    - Add JSON serialization/deserialization
    - _Requirements: 4.8_
  
  - [ ]* 5.4 Write unit tests for Security Scanner
    - Test detecting SQL injection in raw queries
    - Test detecting missing CSRF protection
    - Test detecting XSS in view rendering
    - Test detecting hardcoded credentials
    - Test false positive handling
    - _Requirements: 4.1, 4.2, 4.3, 4.4, 4.5, 4.6, 4.7_

- [x] 6. Implement Prioritization System Component
  - [x] 6.1 Create ImpactAnalyzer class
    - Implement analysis of module impact based on dependency graph
    - Calculate risk assessment (Low, Medium, High) for refactoring
    - Identify affected routes and views
    - Estimate number of files to be created or modified
    - _Requirements: 3.1, 3.2, 3.3, 12.1, 12.2, 12.3, 12.4, 12.5, 12.6_
  
  - [x] 6.2 Implement PrioritizationSystem class
    - Implement identifyLeafModules() for safe starting points
    - Implement identifyCoreModules() for high-risk targets
    - Implement calculatePriorityScore() combining impact and security
    - Implement applyManualOverride() for business priorities
    - Generate recommended refactoring order
    - _Requirements: 3.1, 3.2, 3.3, 3.4, 3.5_
  
  - [x] 6.3 Implement PriorityScore data model
    - Create PriorityScore class with all scoring metrics
    - Add category classification (LEAF, CORE, INTERMEDIATE)
    - Add manual priority override support
    - _Requirements: 3.1, 3.2, 3.3_
  
  - [ ]* 6.4 Write unit tests for Prioritization System
    - Test identifying leaf and core modules
    - Test calculating priority scores
    - Test applying manual overrides
    - Test generating refactoring order
    - _Requirements: 3.1, 3.2, 3.3, 3.4, 3.5_

- [x] 7. Implement Audit Generator Component
  - [x] 7.1 Create CodeAnalyzer class
    - Implement analysis of controller structure (method count, LOC, dependencies)
    - Implement identification of business logic in controllers
    - Implement identification of database queries in controllers
    - Implement complexity estimation (SIMPLE, MEDIUM, COMPLEX)
    - _Requirements: 5.1, 5.2, 5.3, 5.5_
  
  - [x] 7.2 Implement AuditGenerator class
    - Implement generateAudit() orchestrating all analysis steps
    - Implement analyzeControllerStructure() for structural analysis
    - Implement identifyBusinessLogic() for service extraction candidates
    - Implement identifyDatabaseQueries() for repository extraction candidates
    - Generate comprehensive recommendations
    - _Requirements: 5.1, 5.2, 5.3, 5.4, 5.5, 5.6_
  
  - [x] 7.3 Implement AuditReport and ControllerAnalysis data models
    - Create AuditReport class with all analysis results
    - Create ControllerAnalysis class with structural metrics
    - Implement toMarkdown() for human-readable report generation
    - Ensure NO code modification during audit phase
    - _Requirements: 5.6, 5.7_
  
  - [ ]* 7.4 Write unit tests for Audit Generator
    - Test controller structure analysis
    - Test business logic identification
    - Test database query identification
    - Test complexity estimation
    - Test that no code is modified during audit
    - _Requirements: 5.1, 5.2, 5.3, 5.5, 5.7_

- [x] 8. Checkpoint - Verify analysis and audit components
  - Ensure all tests pass, ask the user if questions arise.

- [x] 9. Implement Backup and Rollback System
  - [x] 9.1 Create BackupManager class
    - Implement createBackup() to create timestamped backups of files
    - Implement restoreBackup() to rollback to specific backup
    - Implement listBackups() to show available backups
    - Implement deleteBackup() for cleanup
    - Store backups with metadata (timestamp, module name, files included)
    - _Requirements: 11.1, 11.4_
  
  - [ ]* 9.2 Write unit tests for BackupManager
    - Test backup creation with multiple files
    - Test backup restoration
    - Test listing and deleting backups
    - Test handling backup failures
    - _Requirements: 11.1, 11.4_

- [x] 10. Implement Repository Generator Component
  - [x] 10.1 Create QueryAnalyzer class
    - Implement analysis of raw SQL queries
    - Implement conversion of raw queries to Query Builder syntax
    - Implement parameter binding identification
    - _Requirements: 8.2, 8.4_
  
  - [x] 10.2 Implement RepositoryGenerator class
    - Implement generate() to create repository class files
    - Implement generateCrudMethods() for standard CRUD operations
    - Implement convertToQueryBuilder() for safe query conversion
    - Implement generateComplexQueryMethod() for custom queries
    - Implement addParameterBinding() for SQL injection prevention
    - Generate code following PSR-12 standards with type hints and PHPDoc
    - _Requirements: 8.1, 8.2, 8.3, 8.4, 8.5, 8.6, 8.7, 15.1, 15.2, 15.3, 15.4_
  
  - [x]* 10.3 Write unit tests for Repository Generator
    - Test generating repository with CRUD methods
    - Test converting raw queries to Query Builder
    - Test parameter binding for SQL injection prevention
    - Test generated code syntax validity
    - _Requirements: 8.2, 8.3, 8.4, 8.5, 8.6_

- [x] 11. Implement Service Generator Component
  - [x] 11.1 Create ValidationExtractor class
    - Implement extraction of validation rules from controller code
    - Implement conversion to CodeIgniter 4 validation rule syntax
    - Implement generation of validation rule classes
    - _Requirements: 13.1, 13.2, 13.3, 13.4_
  
  - [x] 11.2 Implement ServiceGenerator class
    - Implement generate() to create service class files
    - Implement extraction of business logic from controllers
    - Implement generateServiceMethod() for each business operation
    - Implement addTransactionManagement() for database operations
    - Implement generateResultObject() for structured responses
    - Generate code with dependency injection and type hints
    - _Requirements: 7.1, 7.2, 7.3, 7.4, 7.5, 7.6, 7.7, 15.1, 15.2, 15.3, 15.4, 15.5_
  
  - [x]* 11.3 Write unit tests for Service Generator
    - Test generating service with business logic extraction
    - Test transaction management implementation
    - Test result object generation
    - Test validation rule extraction and integration
    - Test generated code syntax validity
    - _Requirements: 7.2, 7.4, 7.5, 7.6_

- [ ] 12. Implement Controller Refactorer Component
  - [x] 12.1 Create ControllerSplitter class
    - Implement split() to separate web and API methods
    - Implement identifyWebMethods() based on view rendering
    - Implement identifyApiMethods() based on JSON responses
    - Implement generateWebController() for HTML responses
    - Implement generateApiController() extending BaseApiController
    - _Requirements: 10.1, 10.2, 10.3, 10.4, 10.5, 10.6_
  
  - [~] 12.2 Implement ControllerRefactorer class
    - Implement refactor() to transform fat controllers to thin controllers
    - Implement injectService() for dependency injection
    - Implement replaceBusinessLogicWithServiceCalls() to delegate to services
    - Implement addErrorHandling() for proper exception handling
    - Implement splitWebAndApi() using ControllerSplitter
    - Ensure HTTP endpoints and request/response contracts are maintained
    - _Requirements: 6.1, 6.2, 6.3, 6.4, 6.5, 6.6, 10.7, 15.1, 15.2, 15.3, 15.4_
  
  - [ ]* 12.3 Write unit tests for Controller Refactorer
    - Test refactoring controller with service injection
    - Test splitting web and API controllers
    - Test maintaining HTTP endpoints and contracts
    - Test error handling implementation
    - Test generated code syntax validity
    - _Requirements: 6.1, 6.2, 6.3, 6.4, 6.6_

- [ ] 13. Implement Security Fixer Component
  - [~] 13.1 Implement SecurityFixer class
    - Implement fix() to apply security fixes based on SecurityReport
    - Implement addCSRFProtection() for form submissions
    - Implement addInputValidation() using CodeIgniter 4 validation
    - Implement addOutputEscaping() in views for XSS prevention
    - Implement replaceRawQueryWithQueryBuilder() for SQL injection prevention
    - Implement addAuthenticationCheck() for protected routes
    - Implement addAuthorizationCheck() for sensitive operations
    - Implement addFileUploadValidation() for secure file handling
    - _Requirements: 9.1, 9.2, 9.3, 9.4, 9.5, 9.6, 9.7, 9.8_
  
  - [ ]* 13.2 Write unit tests for Security Fixer
    - Test CSRF protection implementation
    - Test input validation implementation
    - Test output escaping implementation
    - Test raw query replacement with Query Builder
    - Test authentication and authorization checks
    - Test file upload validation
    - _Requirements: 9.2, 9.3, 9.4, 9.5, 9.6, 9.7, 9.8_

- [~] 14. Checkpoint - Verify code generation components
  - Ensure all tests pass, ask the user if questions arise.

- [ ] 15. Implement Refactor Engine Component
  - [~] 15.1 Create RefactorOptions and RefactorResult data models
    - Create RefactorOptions class with all configuration flags
    - Create RefactorResult class with success status and file lists
    - Implement toMarkdown() for result reporting
    - _Requirements: 11.5_
  
  - [~] 15.2 Implement RefactorEngine orchestration class
    - Implement refactor() to orchestrate complete refactoring workflow
    - Implement createBackup() before any modifications
    - Implement executeStep() for each refactoring step (Repository → Service → Controller → Security)
    - Implement rollback() on failure using BackupManager
    - Implement runTests() if tests exist
    - Integrate all generator components (Repository, Service, Controller, Security)
    - _Requirements: 11.1, 11.2, 11.3, 11.4, 11.5_
  
  - [~] 15.3 Implement step-by-step execution with error handling
    - Execute Repository generation first
    - Execute Service generation second
    - Execute Controller refactoring third
    - Execute Security fixes fourth
    - Execute Web/API splitting fifth
    - Handle errors at each step with detailed logging
    - _Requirements: 11.2, 11.3_
  
  - [ ]* 15.4 Write integration tests for Refactor Engine
    - Test complete refactoring workflow with sample module
    - Test refactoring with rollback on failure
    - Test refactoring with web/API split
    - Test refactoring fixes security issues
    - _Requirements: 11.1, 11.2, 11.3, 11.4_

- [ ] 16. Implement Progress Tracker Component
  - [~] 16.1 Create ProgressTracker class
    - Implement status tracking for each module (NOT_STARTED, AUDITED, IN_PROGRESS, COMPLETED, FAILED)
    - Implement recordAudit() to track audit completion
    - Implement recordRefactor() to track refactoring completion
    - Implement calculateOverallProgress() as percentage
    - Implement generateProgressReport() with filtering by status
    - Store progress data in JSON format
    - _Requirements: 14.1, 14.2, 14.3, 14.4, 14.5, 14.6_
  
  - [ ]* 16.2 Write unit tests for Progress Tracker
    - Test status transitions
    - Test progress calculation
    - Test progress report generation
    - Test filtering by status
    - _Requirements: 14.1, 14.2, 14.3, 14.4, 14.6_

- [ ] 17. Implement CLI Command Interface
  - [~] 17.1 Create CLI command for module discovery
    - Implement `php spark refactor:discover` command
    - Display discovered modules count and summary
    - Save module inventory to JSON file
    - _Requirements: 1.1, 1.2, 1.3, 1.4_
  
  - [~] 17.2 Create CLI command for dependency analysis
    - Implement `php spark refactor:analyze` command
    - Display dependency graph summary and impact scores
    - Save dependency graph to JSON file
    - Generate Mermaid diagram file
    - _Requirements: 2.1, 2.2, 2.3, 2.4, 2.5, 2.6_
  
  - [~] 17.3 Create CLI command for module prioritization
    - Implement `php spark refactor:prioritize` command
    - Display recommended refactoring order
    - Show leaf modules and core modules
    - Allow manual priority override via flags
    - _Requirements: 3.1, 3.2, 3.3, 3.4, 3.5_
  
  - [~] 17.4 Create CLI command for security scanning
    - Implement `php spark refactor:scan [module]` command
    - Display security vulnerabilities by severity
    - Save security report to JSON file
    - Generate markdown report
    - _Requirements: 4.1, 4.2, 4.3, 4.4, 4.5, 4.6, 4.7, 4.8_
  
  - [~] 17.5 Create CLI command for audit generation
    - Implement `php spark refactor:audit [module]` command
    - Display audit summary in terminal
    - Save detailed audit report to markdown file
    - Ensure no code modification occurs
    - _Requirements: 5.1, 5.2, 5.3, 5.4, 5.5, 5.6, 5.7_
  
  - [~] 17.6 Create CLI command for refactor execution
    - Implement `php spark refactor:execute [module]` command
    - Add flags for refactoring options (--no-repository, --no-service, etc.)
    - Display progress during execution
    - Display refactoring summary on completion
    - Prompt for rollback on failure
    - _Requirements: 11.1, 11.2, 11.3, 11.4, 11.5_
  
  - [~] 17.7 Create CLI command for progress tracking
    - Implement `php spark refactor:progress` command
    - Display overall progress percentage
    - Display module status table
    - Allow filtering by status (--status=COMPLETED)
    - _Requirements: 14.1, 14.2, 14.3, 14.4, 14.6_
  
  - [~] 17.8 Create CLI command for backup management
    - Implement `php spark refactor:backup:list` command
    - Implement `php spark refactor:backup:restore [backupId]` command
    - Implement `php spark refactor:backup:delete [backupId]` command
    - _Requirements: 11.4_

- [ ] 18. Implement Validation Rule Migration
  - [~] 18.1 Create ValidationMigrator class
    - Implement extraction of validation rules from controller methods
    - Implement generation of validation rule classes in app/Validation
    - Implement generation of validation error messages
    - Integrate with ServiceGenerator to use validation rule classes
    - _Requirements: 13.1, 13.2, 13.3, 13.4, 13.5, 13.6_
  
  - [ ]* 18.2 Write unit tests for Validation Migrator
    - Test extracting validation rules from controllers
    - Test generating validation rule classes
    - Test generating error messages
    - Test integration with service layer
    - _Requirements: 13.1, 13.2, 13.3, 13.6_

- [ ] 19. Implement Code Quality Enforcement
  - [~] 19.1 Create CodeGenerator base class
    - Implement PSR-12 code formatting
    - Implement proper namespacing following CodeIgniter 4 conventions
    - Implement type hint generation for parameters and return types
    - Implement PHPDoc comment generation
    - Implement dependency injection pattern generation
    - Implement PHP syntax validation for generated code
    - _Requirements: 15.1, 15.2, 15.3, 15.4, 15.5, 15.6, 15.7_
  
  - [~] 19.2 Integrate CodeGenerator with all generator components
    - Update RepositoryGenerator to use CodeGenerator
    - Update ServiceGenerator to use CodeGenerator
    - Update ControllerRefactorer to use CodeGenerator
    - Update SecurityFixer to use CodeGenerator
    - _Requirements: 15.1, 15.2, 15.3, 15.4, 15.5, 15.6_
  
  - [ ]* 19.3 Write unit tests for Code Quality Enforcement
    - Test PSR-12 compliance of generated code
    - Test proper namespacing
    - Test type hints and PHPDoc comments
    - Test PHP syntax validation
    - _Requirements: 15.1, 15.2, 15.3, 15.4, 15.7_

- [~] 20. Checkpoint - Verify complete system integration
  - Ensure all tests pass, ask the user if questions arise.

- [ ] 21. Create comprehensive documentation
  - [~] 21.1 Create user guide documentation
    - Document installation and setup instructions
    - Document CLI command usage with examples
    - Document recommended workflow (discover → analyze → prioritize → audit → execute)
    - Document backup and rollback procedures
    - Document troubleshooting common issues
  
  - [~] 21.2 Create developer documentation
    - Document system architecture and component interactions
    - Document extending the system (custom security rules, custom generators)
    - Document data model schemas (JSON structures)
    - Document error codes and handling strategies
  
  - [~] 21.3 Create example walkthrough
    - Create step-by-step example refactoring a sample module
    - Include before/after code comparisons
    - Include audit report example
    - Include security report example

- [ ] 22. Final integration testing and validation
  - [~] 22.1 Test complete workflow on sample modules
    - Create simple test module (single controller, single model)
    - Create complex test module (multiple models, dependencies, security issues)
    - Create mixed test module (web and API endpoints)
    - Run complete workflow on each test module
    - Verify generated code quality and correctness
  
  - [~] 22.2 Test error handling and rollback scenarios
    - Test rollback on code generation failure
    - Test rollback on test failure
    - Test handling of circular dependencies
    - Test handling of missing files
    - Test handling of malformed PHP code
  
  - [~] 22.3 Test on actual sigapp.dev modules
    - Select 2-3 real modules from sigapp.dev
    - Run discovery and analysis
    - Generate audit reports
    - Review audit reports with user
    - Execute refactoring on approved modules
    - Verify application functionality after refactoring

- [~] 23. Final checkpoint - System ready for production use
  - Ensure all tests pass, ask the user if questions arise.

## Notes

- Tasks marked with `*` are optional test tasks and can be skipped for faster MVP
- Each task references specific requirements for traceability
- The implementation follows a bottom-up approach: infrastructure → discovery → analysis → generation → orchestration
- Checkpoints ensure incremental validation at major milestones
- The system is designed to be safe-by-default with backup and rollback capabilities
- All code generation follows PSR-12 standards and CodeIgniter 4 best practices
- The CLI interface provides a user-friendly way to interact with all system components
- Testing strategy combines unit tests, integration tests, and manual validation on real modules

## Task Dependency Graph

```json
{
  "waves": [
    { "id": 0, "tasks": ["1"] },
    { "id": 1, "tasks": ["2.1", "2.2"] },
    { "id": 2, "tasks": ["2.3", "2.4"] },
    { "id": 3, "tasks": ["3.1"] },
    { "id": 4, "tasks": ["3.2", "3.3"] },
    { "id": 5, "tasks": ["3.4"] },
    { "id": 6, "tasks": ["5.1"] },
    { "id": 7, "tasks": ["5.2", "5.3"] },
    { "id": 8, "tasks": ["5.4", "6.1"] },
    { "id": 9, "tasks": ["6.2", "6.3"] },
    { "id": 10, "tasks": ["6.4", "7.1"] },
    { "id": 11, "tasks": ["7.2", "7.3"] },
    { "id": 12, "tasks": ["7.4", "9.1"] },
    { "id": 13, "tasks": ["9.2", "10.1"] },
    { "id": 14, "tasks": ["10.2"] },
    { "id": 15, "tasks": ["10.3", "11.1"] },
    { "id": 16, "tasks": ["11.2"] },
    { "id": 17, "tasks": ["11.3", "12.1"] },
    { "id": 18, "tasks": ["12.2"] },
    { "id": 19, "tasks": ["12.3", "13.1"] },
    { "id": 20, "tasks": ["13.2", "15.1"] },
    { "id": 21, "tasks": ["15.2", "15.3"] },
    { "id": 22, "tasks": ["15.4", "16.1"] },
    { "id": 23, "tasks": ["16.2", "17.1", "17.2", "17.3", "17.4"] },
    { "id": 24, "tasks": ["17.5", "17.6", "17.7", "17.8", "18.1"] },
    { "id": 25, "tasks": ["18.2", "19.1"] },
    { "id": 26, "tasks": ["19.2"] },
    { "id": 27, "tasks": ["19.3", "21.1", "21.2"] },
    { "id": 28, "tasks": ["21.3", "22.1"] },
    { "id": 29, "tasks": ["22.2", "22.3"] }
  ]
}
```
