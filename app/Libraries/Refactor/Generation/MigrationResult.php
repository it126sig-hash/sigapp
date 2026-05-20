<?php

namespace App\Libraries\Refactor\Generation;

/**
 * MigrationResult
 *
 * Data class representing the result of a validation rule migration operation.
 * Contains information about what was migrated, generated files, and any errors.
 *
 * @package App\Libraries\Refactor\Generation
 */
class MigrationResult
{
    /**
     * @var bool Whether the migration was successful
     */
    public bool $success = false;

    /**
     * @var bool Whether the migration was skipped (no rules found)
     */
    public bool $skipped = false;

    /**
     * @var string Path to the controller that was migrated
     */
    public string $controllerPath = '';

    /**
     * @var string|null Name of the generated validation class
     */
    public ?string $validationClassName = null;

    /**
     * @var string|null Generated validation class code
     */
    public ?string $validationClassCode = null;

    /**
     * @var string|null Generated language file content
     */
    public ?string $languageFileContent = null;

    /**
     * @var string|null Updated controller code
     */
    public ?string $updatedControllerCode = null;

    /**
     * @var int Number of validation rules extracted
     */
    public int $rulesExtracted = 0;

    /**
     * @var int Number of methods affected by the migration
     */
    public int $methodsAffected = 0;

    /**
     * @var array<string> List of files created during migration
     */
    public array $filesCreated = [];

    /**
     * @var string|null Human-readable message about the migration
     */
    public ?string $message = null;

    /**
     * @var string|null Error message if migration failed
     */
    public ?string $error = null;
}
