<?php

namespace App\Libraries\Refactor\Generation;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\String_;
use PhpParser\NodeFinder;
use PhpParser\ParserFactory;

/**
 * ValidationExtractor
 * 
 * Utility for extracting validation rules from controller code and converting them
 * to CodeIgniter 4 validation rule class format. Supports generating validation
 * error messages and creating reusable validation rule classes.
 * 
 * @package App\Libraries\Refactor\Generation
 */
class ValidationExtractor
{
    /**
     * @var CodeGenerator Code generator instance for creating validation classes
     */
    private CodeGenerator $codeGenerator;

    /**
     * @var \PhpParser\Parser PHP parser instance
     */
    private $parser;

    /**
     * @var NodeFinder Node finder for AST traversal
     */
    private NodeFinder $nodeFinder;

    /**
     * Constructor
     * 
     * @param CodeGenerator $codeGenerator Code generator instance
     */
    public function __construct(CodeGenerator $codeGenerator)
    {
        $this->codeGenerator = $codeGenerator;
        $this->parser = (new ParserFactory())->createForNewestSupportedVersion();
        $this->nodeFinder = new NodeFinder();
    }

    /**
     * Extract validation rules from controller code
     * 
     * Parses controller PHP code and extracts all validation rule arrays.
     * Returns an array of validation rule sets with their context (method name, line number).
     * 
     * @param string $controllerCode Controller PHP code
     * @return array<array{method: string, rules: array<string, string>, line: int}> Extracted validation rules
     */
    public function extractFromController(string $controllerCode): array
    {
        try {
            $ast = $this->parser->parse($controllerCode);
        } catch (\Exception $e) {
            return [];
        }

        if (!$ast) {
            return [];
        }

        $validationRules = [];

        // Find all method nodes
        $methods = $this->nodeFinder->findInstanceOf($ast, Node\Stmt\ClassMethod::class);

        foreach ($methods as $method) {
            $methodName = $method->name->toString();
            
            // Find validation rule assignments in this method
            $assignments = $this->nodeFinder->findInstanceOf($method, Assign::class);
            
            foreach ($assignments as $assignment) {
                // Check if this is a $rules = [...] assignment
                if ($assignment->var instanceof Variable && 
                    $assignment->var->name === 'rules' &&
                    $assignment->expr instanceof Array_) {
                    
                    $rules = $this->parseRulesArray($assignment->expr);
                    
                    if (!empty($rules)) {
                        $validationRules[] = [
                            'method' => $methodName,
                            'rules' => $rules,
                            'line' => $assignment->getLine(),
                        ];
                    }
                }
            }
        }

        return $validationRules;
    }

    /**
     * Parse validation rules array from AST node
     * 
     * @param Array_ $arrayNode Array AST node
     * @return array<string, string> Parsed rules (field => rule string)
     */
    private function parseRulesArray(Array_ $arrayNode): array
    {
        $rules = [];

        foreach ($arrayNode->items as $item) {
            if (!$item instanceof ArrayItem) {
                continue;
            }

            // Get field name (array key)
            $fieldName = null;
            if ($item->key instanceof String_) {
                $fieldName = $item->key->value;
            }

            // Get rule string (array value)
            $ruleString = null;
            if ($item->value instanceof String_) {
                $ruleString = $item->value->value;
            }

            if ($fieldName && $ruleString) {
                $rules[$fieldName] = $ruleString;
            }
        }

        return $rules;
    }

    /**
     * Convert inline validation rules to validation rule class format
     * 
     * Takes extracted validation rules and generates a CodeIgniter 4 validation rule class.
     * 
     * @param string $className Validation rule class name (e.g., "BankValidation")
     * @param array<string, string> $rules Validation rules (field => rule string)
     * @param string $namespace Namespace for the validation class (default: "App\Validation")
     * @return string Generated validation rule class code
     */
    public function convertToRuleClass(string $className, array $rules, string $namespace = 'App\\Validation'): string
    {
        $this->codeGenerator->reset();
        $this->codeGenerator->setNamespace($namespace);

        // Generate rule set methods for the validation class
        $methods = [];

        // Create a method that returns the validation rules
        $rulesArrayCode = $this->generateRulesArrayCode($rules);
        
        $methods[] = [
            'name' => 'getRules',
            'visibility' => 'public',
            'static' => true,
            'return' => 'array',
            'description' => 'Get validation rules',
            'returnDescription' => 'Validation rules array',
            'body' => "return {$rulesArrayCode};",
        ];

        // Create individual field validation methods for reusability
        foreach ($rules as $field => $ruleString) {
            $methodName = 'get' . $this->toCamelCase($field) . 'Rules';
            
            $methods[] = [
                'name' => $methodName,
                'visibility' => 'public',
                'static' => true,
                'return' => 'string',
                'description' => "Get validation rules for {$field} field",
                'returnDescription' => 'Validation rule string',
                'body' => "return '{$ruleString}';",
            ];
        }

        // Generate the class
        $classCode = $this->codeGenerator->generateClass($className, [
            'description' => "Validation rules for {$className}",
            'methods' => $methods,
        ]);

        return $classCode;
    }

    /**
     * Generate PHP array code from rules array
     * 
     * @param array<string, string> $rules Validation rules
     * @return string PHP array code
     */
    private function generateRulesArrayCode(array $rules): string
    {
        $lines = ["["];
        
        foreach ($rules as $field => $ruleString) {
            $lines[] = "    '{$field}' => '{$ruleString}',";
        }
        
        $lines[] = "]";
        
        return implode("\n", $lines);
    }

    /**
     * Generate validation error messages
     * 
     * Creates human-readable error messages for validation rules.
     * Returns an array suitable for CodeIgniter 4 language files.
     * 
     * @param array<string, string> $rules Validation rules (field => rule string)
     * @param string $context Context for error messages (e.g., "Bank", "Transaction")
     * @return array<string, array<string, string>> Error messages (field => [rule => message])
     */
    public function generateErrorMessages(array $rules, string $context = ''): array
    {
        $messages = [];

        foreach ($rules as $field => $ruleString) {
            $fieldMessages = [];
            $ruleList = $this->parseRuleString($ruleString);
            
            foreach ($ruleList as $rule) {
                $ruleName = $rule['name'];
                $params = $rule['params'];
                
                $message = $this->generateErrorMessage($field, $ruleName, $params, $context);
                
                if ($message) {
                    $fieldMessages[$ruleName] = $message;
                }
            }
            
            if (!empty($fieldMessages)) {
                $messages[$field] = $fieldMessages;
            }
        }

        return $messages;
    }

    /**
     * Parse rule string into individual rules with parameters
     * 
     * @param string $ruleString Rule string (e.g., "required|max_length[255]|alpha_numeric")
     * @return array<array{name: string, params: array<string>}> Parsed rules
     */
    private function parseRuleString(string $ruleString): array
    {
        $rules = [];
        $ruleParts = explode('|', $ruleString);

        foreach ($ruleParts as $rulePart) {
            $rulePart = trim($rulePart);
            
            if (empty($rulePart)) {
                continue;
            }

            // Check if rule has parameters (e.g., max_length[255])
            if (preg_match('/^([a-z_]+)\[(.+)\]$/i', $rulePart, $matches)) {
                $rules[] = [
                    'name' => $matches[1],
                    'params' => explode(',', $matches[2]),
                ];
            } else {
                $rules[] = [
                    'name' => $rulePart,
                    'params' => [],
                ];
            }
        }

        return $rules;
    }

    /**
     * Generate error message for a specific validation rule
     * 
     * @param string $field Field name
     * @param string $rule Rule name
     * @param array<string> $params Rule parameters
     * @param string $context Context for the message
     * @return string|null Error message or null if rule is not recognized
     */
    private function generateErrorMessage(string $field, string $rule, array $params, string $context): ?string
    {
        $fieldLabel = $this->toHumanReadable($field);
        $contextPrefix = $context ? "{$context} " : '';

        return match ($rule) {
            'required' => "{$contextPrefix}{$fieldLabel} harus diisi.",
            'permit_empty' => null, // No error message needed for permit_empty
            'max_length' => isset($params[0]) 
                ? "{$contextPrefix}{$fieldLabel} maksimal {$params[0]} karakter."
                : "{$contextPrefix}{$fieldLabel} terlalu panjang.",
            'min_length' => isset($params[0])
                ? "{$contextPrefix}{$fieldLabel} minimal {$params[0]} karakter."
                : "{$contextPrefix}{$fieldLabel} terlalu pendek.",
            'exact_length' => isset($params[0])
                ? "{$contextPrefix}{$fieldLabel} harus tepat {$params[0]} karakter."
                : "{$contextPrefix}{$fieldLabel} panjang tidak sesuai.",
            'alpha' => "{$contextPrefix}{$fieldLabel} hanya boleh berisi huruf.",
            'alpha_numeric' => "{$contextPrefix}{$fieldLabel} hanya boleh berisi huruf dan angka.",
            'alpha_numeric_space' => "{$contextPrefix}{$fieldLabel} hanya boleh berisi huruf, angka, dan spasi.",
            'alpha_dash' => "{$contextPrefix}{$fieldLabel} hanya boleh berisi huruf, angka, underscore, dan dash.",
            'numeric' => "{$contextPrefix}{$fieldLabel} harus berupa angka.",
            'integer' => "{$contextPrefix}{$fieldLabel} harus berupa bilangan bulat.",
            'decimal' => "{$contextPrefix}{$fieldLabel} harus berupa angka desimal.",
            'is_natural' => "{$contextPrefix}{$fieldLabel} harus berupa angka natural (0, 1, 2, ...).",
            'is_natural_no_zero' => "{$contextPrefix}{$fieldLabel} harus berupa angka natural lebih dari 0.",
            'valid_email' => "{$contextPrefix}{$fieldLabel} harus berupa email yang valid.",
            'valid_emails' => "{$contextPrefix}{$fieldLabel} harus berupa email yang valid.",
            'valid_url' => "{$contextPrefix}{$fieldLabel} harus berupa URL yang valid.",
            'valid_ip' => "{$contextPrefix}{$fieldLabel} harus berupa IP address yang valid.",
            'valid_date' => "{$contextPrefix}{$fieldLabel} harus berupa tanggal yang valid.",
            'matches' => isset($params[0])
                ? "{$contextPrefix}{$fieldLabel} harus sama dengan {$this->toHumanReadable($params[0])}."
                : "{$contextPrefix}{$fieldLabel} tidak cocok.",
            'differs' => isset($params[0])
                ? "{$contextPrefix}{$fieldLabel} harus berbeda dengan {$this->toHumanReadable($params[0])}."
                : "{$contextPrefix}{$fieldLabel} harus berbeda.",
            'in_list' => isset($params[0])
                ? "{$contextPrefix}{$fieldLabel} harus salah satu dari: {$params[0]}."
                : "{$contextPrefix}{$fieldLabel} tidak valid.",
            'is_unique' => "{$contextPrefix}{$fieldLabel} sudah digunakan.",
            'greater_than' => isset($params[0])
                ? "{$contextPrefix}{$fieldLabel} harus lebih besar dari {$params[0]}."
                : "{$contextPrefix}{$fieldLabel} terlalu kecil.",
            'greater_than_equal_to' => isset($params[0])
                ? "{$contextPrefix}{$fieldLabel} harus lebih besar atau sama dengan {$params[0]}."
                : "{$contextPrefix}{$fieldLabel} terlalu kecil.",
            'less_than' => isset($params[0])
                ? "{$contextPrefix}{$fieldLabel} harus lebih kecil dari {$params[0]}."
                : "{$contextPrefix}{$fieldLabel} terlalu besar.",
            'less_than_equal_to' => isset($params[0])
                ? "{$contextPrefix}{$fieldLabel} harus lebih kecil atau sama dengan {$params[0]}."
                : "{$contextPrefix}{$fieldLabel} terlalu besar.",
            'uploaded' => "{$contextPrefix}{$fieldLabel} harus diupload.",
            'max_size' => isset($params[1])
                ? "{$contextPrefix}{$fieldLabel} maksimal {$params[1]} KB."
                : "{$contextPrefix}{$fieldLabel} terlalu besar.",
            'max_dims' => isset($params[1], $params[2])
                ? "{$contextPrefix}{$fieldLabel} maksimal {$params[1]}x{$params[2]} pixels."
                : "{$contextPrefix}{$fieldLabel} dimensi terlalu besar.",
            'mime_in' => isset($params[1])
                ? "{$contextPrefix}{$fieldLabel} harus berupa file: {$params[1]}."
                : "{$contextPrefix}{$fieldLabel} tipe file tidak valid.",
            'ext_in' => isset($params[1])
                ? "{$contextPrefix}{$fieldLabel} harus berupa file dengan ekstensi: {$params[1]}."
                : "{$contextPrefix}{$fieldLabel} ekstensi file tidak valid.",
            'is_image' => "{$contextPrefix}{$fieldLabel} harus berupa gambar.",
            default => null,
        };
    }

    /**
     * Convert field name to human-readable label
     * 
     * @param string $field Field name (e.g., "id_kavling", "bank_name")
     * @return string Human-readable label (e.g., "ID Kavling", "Bank Name")
     */
    private function toHumanReadable(string $field): string
    {
        // Replace underscores with spaces
        $label = str_replace('_', ' ', $field);
        
        // Capitalize each word
        $label = ucwords($label);
        
        return $label;
    }

    /**
     * Convert field name to camelCase for method names
     * 
     * @param string $field Field name (e.g., "id_kavling", "bank_name")
     * @return string CamelCase name (e.g., "IdKavling", "BankName")
     */
    private function toCamelCase(string $field): string
    {
        // Replace underscores with spaces, capitalize words, remove spaces
        return str_replace(' ', '', ucwords(str_replace('_', ' ', $field)));
    }

    /**
     * Generate language file content for validation error messages
     * 
     * Creates a PHP array suitable for CodeIgniter 4 language files.
     * 
     * @param array<string, array<string, string>> $messages Error messages (field => [rule => message])
     * @param string $groupName Group name for the language file (e.g., "Bank", "Transaction")
     * @return string PHP language file content
     */
    public function generateLanguageFile(array $messages, string $groupName = 'Validation'): string
    {
        $code = "<?php\n\n";
        $code .= "/**\n";
        $code .= " * {$groupName} Validation Language File\n";
        $code .= " * \n";
        $code .= " * Contains validation error messages for {$groupName}\n";
        $code .= " */\n\n";
        $code .= "return [\n";

        foreach ($messages as $field => $fieldMessages) {
            $code .= "    // {$this->toHumanReadable($field)} validation messages\n";
            
            foreach ($fieldMessages as $rule => $message) {
                $key = "{$field}.{$rule}";
                $code .= "    '{$key}' => '{$message}',\n";
            }
            
            $code .= "\n";
        }

        $code .= "];\n";

        return $code;
    }

    /**
     * Extract all validation rules from multiple controller files
     * 
     * @param array<string> $controllerPaths Array of controller file paths
     * @return array<string, array> Validation rules grouped by controller
     */
    public function extractFromMultipleControllers(array $controllerPaths): array
    {
        $allRules = [];

        foreach ($controllerPaths as $path) {
            if (!file_exists($path)) {
                continue;
            }

            $code = file_get_contents($path);
            $rules = $this->extractFromController($code);

            if (!empty($rules)) {
                $controllerName = basename($path, '.php');
                $allRules[$controllerName] = $rules;
            }
        }

        return $allRules;
    }

    /**
     * Generate validation rule class with custom validation methods
     * 
     * @param string $className Validation rule class name
     * @param array<string, string> $customRules Custom validation rules (method name => rule logic)
     * @param string $namespace Namespace for the validation class
     * @return string Generated validation rule class code
     */
    public function generateCustomRuleClass(string $className, array $customRules, string $namespace = 'App\\Validation'): string
    {
        $this->codeGenerator->reset();
        $this->codeGenerator->setNamespace($namespace);

        $methods = [];

        foreach ($customRules as $methodName => $ruleLogic) {
            $methods[] = [
                'name' => $methodName,
                'visibility' => 'public',
                'params' => [
                    ['type' => 'string', 'name' => 'str', 'description' => 'Value to validate'],
                    ['type' => 'string', 'name' => 'fields', 'description' => 'Field name'],
                    ['type' => 'array', 'name' => 'data', 'description' => 'All form data'],
                    ['type' => 'string|null', 'name' => 'error', 'default' => 'null', 'description' => 'Error message reference'],
                ],
                'return' => 'bool',
                'description' => "Custom validation rule: {$methodName}",
                'returnDescription' => 'True if validation passes, false otherwise',
                'body' => $ruleLogic,
            ];
        }

        $classCode = $this->codeGenerator->generateClass($className, [
            'description' => "Custom validation rules for {$className}",
            'methods' => $methods,
        ]);

        return $classCode;
    }
}

