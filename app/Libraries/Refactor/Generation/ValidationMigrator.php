<?php

namespace App\Libraries\Refactor\Generation;

/**
 * ValidationMigrator
 *
 * Migrates inline validation rules from controllers to dedicated validation rule classes.
 * Uses ValidationExtractor to extract rules, generates validation class files in app/Validation/,
 * updates controller code to use the new validation classes, and generates language files
 * for validation messages.
 *
 * @package App\Libraries\Refactor\Generation
 */
class ValidationMigrator
{
    /**
     * @var ValidationExtractor Extractor for parsing validation rules from controllers
     */
    private ValidationExtractor $extractor;

    /**
     * @var CodeGenerator Code generator for creating validation classes
     */
    private CodeGenerator $codeGenerator;

    /**
     * @var string Base path for the application
     */
    private string $appPath;

    /**
     * Constructor
     *
     * @param ValidationExtractor $extractor Validation extractor instance
     * @param CodeGenerator $codeGenerator Code generator instance
     * @param string $appPath Application base path (defaults to APPPATH)
     */
    public function __construct(
        ValidationExtractor $extractor,
        CodeGenerator $codeGenerator,
        string $appPath = ''
    ) {
        $this->extractor = $extractor;
        $this->codeGenerator = $codeGenerator;
        $this->appPath = $appPath ?: (defined('APPPATH') ? APPPATH : '');
    }

    /**
     * Migrate a single controller's validation rules to dedicated classes
     *
     * Extracts inline validation rules from the controller, generates validation
     * rule classes, updates the controller code, and generates language files.
     *
     * @param string $controllerPath Path to the controller file
     * @return MigrationResult Result of the migration operation
     */
    public function migrate(string $controllerPath): MigrationResult
    {
        $result = new MigrationResult();
        $result->controllerPath = $controllerPath;

        if (!file_exists($controllerPath)) {
            $result->success = false;
            $result->error = "Controller file not found: {$controllerPath}";
            return $result;
        }

        $controllerCode = file_get_contents($controllerPath);
        $controllerName = $this->extractControllerName($controllerPath);

        // Extract validation rules from controller
        $extractedRules = $this->extractor->extractFromController($controllerCode);

        if (empty($extractedRules)) {
            $result->success = true;
            $result->skipped = true;
            $result->message = "No inline validation rules found in {$controllerName}";
            return $result;
        }

        // Merge all rules from the controller into a single set
        $allRules = [];
        foreach ($extractedRules as $ruleSet) {
            $allRules = array_merge($allRules, $ruleSet['rules']);
        }

        // Generate validation class name
        $validationClassName = $controllerName . 'Validation';

        // Generate validation class code
        $validationClassCode = $this->generateValidationClass($validationClassName, $allRules);
        $result->validationClassCode = $validationClassCode;
        $result->validationClassName = $validationClassName;

        // Generate language file
        $messages = $this->extractor->generateErrorMessages($allRules, $controllerName);
        $languageFileContent = $this->extractor->generateLanguageFile($messages, $controllerName);
        $result->languageFileContent = $languageFileContent;

        // Update controller code
        $updatedControllerCode = $this->updateControllerToUseValidationClass(
            $controllerCode,
            $validationClassName
        );
        $result->updatedControllerCode = $updatedControllerCode;

        // Write files if appPath is set
        if (!empty($this->appPath)) {
            $this->writeValidationClass($validationClassName, $validationClassCode, $result);
            $this->writeLanguageFile($controllerName, $languageFileContent, $result);
        }

        $result->success = true;
        $result->rulesExtracted = count($allRules);
        $result->methodsAffected = count($extractedRules);
        $result->message = "Successfully migrated {$result->rulesExtracted} validation rules from {$controllerName}";

        return $result;
    }

    /**
     * Batch migrate multiple controllers
     *
     * @param array<string> $controllerPaths Array of controller file paths
     * @return array<MigrationResult> Array of migration results
     */
    public function migrateAll(array $controllerPaths): array
    {
        $results = [];

        foreach ($controllerPaths as $path) {
            $results[] = $this->migrate($path);
        }

        return $results;
    }

    /**
     * Generate a validation rule class
     *
     * Creates a CodeIgniter 4 validation rule class with the given rules.
     *
     * @param string $className Validation class name (e.g., "TransaksiValidation")
     * @param array<string, string> $rules Validation rules (field => rule string)
     * @return string Generated PHP class code
     */
    public function generateValidationClass(string $className, array $rules): string
    {
        return $this->extractor->convertToRuleClass($className, $rules, 'App\\Validation');
    }

    /**
     * Update controller code to use a validation class instead of inline rules
     *
     * Replaces inline $rules = [...] assignments with calls to the validation class.
     *
     * @param string $controllerCode Original controller PHP code
     * @param string $validationClassName Validation class name to use
     * @return string Updated controller code
     */
    public function updateControllerToUseValidationClass(string $controllerCode, string $validationClassName): string
    {
        $useStatement = "use App\\Validation\\{$validationClassName};";

        // Add use statement if not already present
        if (strpos($controllerCode, $useStatement) === false) {
            $controllerCode = $this->addUseStatement($controllerCode, $useStatement);
        }

        // Replace inline $rules = [...] with validation class call
        $controllerCode = $this->replaceInlineRulesWithClassCall($controllerCode, $validationClassName);

        return $controllerCode;
    }

    /**
     * Generate a migration report in markdown format
     *
     * @param array<MigrationResult> $results Array of migration results
     * @return string Markdown report content
     */
    public function generateMigrationReport(array $results): string
    {
        $report = "# Validation Migration Report\n\n";
        $report .= "Generated: " . date('Y-m-d H:i:s') . "\n\n";

        // Summary
        $total = count($results);
        $successful = count(array_filter($results, fn(MigrationResult $r) => $r->success && !$r->skipped));
        $skipped = count(array_filter($results, fn(MigrationResult $r) => $r->skipped));
        $failed = count(array_filter($results, fn(MigrationResult $r) => !$r->success));

        $report .= "## Summary\n\n";
        $report .= "| Metric | Count |\n";
        $report .= "|--------|-------|\n";
        $report .= "| Total Controllers | {$total} |\n";
        $report .= "| Successfully Migrated | {$successful} |\n";
        $report .= "| Skipped (no rules) | {$skipped} |\n";
        $report .= "| Failed | {$failed} |\n\n";

        // Details
        $report .= "## Details\n\n";

        foreach ($results as $result) {
            $controllerName = basename($result->controllerPath, '.php');
            $status = $result->success ? ($result->skipped ? '⏭️ Skipped' : '✅ Success') : '❌ Failed';

            $report .= "### {$controllerName} - {$status}\n\n";

            if ($result->success && !$result->skipped) {
                $report .= "- **Rules Extracted**: {$result->rulesExtracted}\n";
                $report .= "- **Methods Affected**: {$result->methodsAffected}\n";
                $report .= "- **Validation Class**: `App\\Validation\\{$result->validationClassName}`\n";

                if (!empty($result->filesCreated)) {
                    $report .= "- **Files Created**:\n";
                    foreach ($result->filesCreated as $file) {
                        $report .= "  - `{$file}`\n";
                    }
                }
            } elseif ($result->skipped) {
                $report .= "- {$result->message}\n";
            } else {
                $report .= "- **Error**: {$result->error}\n";
            }

            $report .= "\n";
        }

        return $report;
    }

    /**
     * Extract controller name from file path
     *
     * @param string $controllerPath Controller file path
     * @return string Controller name without extension
     */
    private function extractControllerName(string $controllerPath): string
    {
        return basename($controllerPath, '.php');
    }

    /**
     * Add a use statement to controller code
     *
     * @param string $code Controller PHP code
     * @param string $useStatement Use statement to add
     * @return string Updated code with use statement
     */
    private function addUseStatement(string $code, string $useStatement): string
    {
        // Find the last existing use statement and add after it
        if (preg_match('/^(use\s+[^;]+;)\s*$/m', $code, $matches, PREG_OFFSET_CAPTURE)) {
            // Find all use statements to get the last one
            preg_match_all('/^use\s+[^;]+;\s*$/m', $code, $allMatches, PREG_OFFSET_CAPTURE);

            if (!empty($allMatches[0])) {
                $lastUse = end($allMatches[0]);
                $insertPos = $lastUse[1] + strlen($lastUse[0]);
                $code = substr($code, 0, $insertPos) . $useStatement . "\n" . substr($code, $insertPos);
            }
        } elseif (preg_match('/^namespace\s+[^;]+;\s*$/m', $code, $matches, PREG_OFFSET_CAPTURE)) {
            // No use statements, add after namespace
            $insertPos = $matches[0][1] + strlen($matches[0][0]);
            $code = substr($code, 0, $insertPos) . "\n" . $useStatement . "\n" . substr($code, $insertPos);
        }

        return $code;
    }

    /**
     * Replace inline validation rules with validation class calls
     *
     * @param string $code Controller PHP code
     * @param string $validationClassName Validation class name
     * @return string Updated code
     */
    private function replaceInlineRulesWithClassCall(string $code, string $validationClassName): string
    {
        // Match $rules = [...]; pattern (single or multi-line arrays)
        $pattern = '/(\$rules\s*=\s*)\[([^\]]*(?:\[[^\]]*\][^\]]*)*)\]\s*;/s';

        $replacement = '$rules = ' . $validationClassName . '::getRules();';

        $code = preg_replace($pattern, $replacement, $code);

        return $code;
    }

    /**
     * Write validation class file to disk
     *
     * @param string $className Validation class name
     * @param string $code Validation class code
     * @param MigrationResult $result Result object to update
     */
    private function writeValidationClass(string $className, string $code, MigrationResult $result): void
    {
        $directory = $this->appPath . 'Validation';

        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $filePath = $directory . DIRECTORY_SEPARATOR . $className . '.php';
        file_put_contents($filePath, $code);
        $result->filesCreated[] = $filePath;
    }

    /**
     * Write language file to disk
     *
     * @param string $controllerName Controller name for the language file
     * @param string $content Language file content
     * @param MigrationResult $result Result object to update
     */
    private function writeLanguageFile(string $controllerName, string $content, MigrationResult $result): void
    {
        $directory = $this->appPath . 'Language' . DIRECTORY_SEPARATOR . 'id';

        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $filePath = $directory . DIRECTORY_SEPARATOR . $controllerName . 'Validation.php';
        file_put_contents($filePath, $content);
        $result->filesCreated[] = $filePath;
    }
}
