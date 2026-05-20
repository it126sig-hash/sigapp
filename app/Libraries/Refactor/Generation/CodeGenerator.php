<?php

namespace App\Libraries\Refactor\Generation;

use App\Libraries\Refactor\Contracts\GeneratorInterface;

/**
 * CodeGenerator
 * 
 * Utility for generating PHP code with proper formatting, namespacing, and documentation.
 * Implements PSR-12 coding standards and provides template-based code generation.
 * 
 * @package App\Libraries\Refactor\Generation
 */
class CodeGenerator implements GeneratorInterface
{
    /**
     * @var int Indentation level (number of spaces per indent)
     */
    private int $indentSize = 4;

    /**
     * @var array<string> Use statements to be added to generated code
     */
    private array $useStatements = [];

    /**
     * @var string|null Namespace for generated code
     */
    private ?string $namespace = null;

    /**
     * @var array<string, mixed> Template variables for code generation
     */
    private array $templateVars = [];

    /**
     * Generate code based on provided data
     * 
     * @param mixed $data Input data for generation (template string or array with template and vars)
     * @return string Generated code
     */
    public function generate(mixed $data): string
    {
        if (is_string($data)) {
            return $this->processTemplate($data);
        }

        if (is_array($data) && isset($data['template'])) {
            $this->templateVars = $data['vars'] ?? [];
            return $this->processTemplate($data['template']);
        }

        return '';
    }

    /**
     * Set namespace for generated code
     * 
     * @param string $namespace Namespace (e.g., "App\Services")
     * @return self
     */
    public function setNamespace(string $namespace): self
    {
        $this->namespace = $namespace;
        return $this;
    }

    /**
     * Add use statement
     * 
     * @param string $class Fully qualified class name
     * @param string|null $alias Optional alias for the class
     * @return self
     */
    public function addUseStatement(string $class, ?string $alias = null): self
    {
        if ($alias) {
            $this->useStatements[$class] = $alias;
        } else {
            $this->useStatements[] = $class;
        }
        return $this;
    }

    /**
     * Add multiple use statements
     * 
     * @param array<string> $classes Array of fully qualified class names
     * @return self
     */
    public function addUseStatements(array $classes): self
    {
        foreach ($classes as $class) {
            $this->addUseStatement($class);
        }
        return $this;
    }

    /**
     * Generate class code
     * 
     * @param string $className Class name
     * @param array<string, mixed> $options Class options (extends, implements, properties, methods, etc.)
     * @return string Generated class code
     */
    public function generateClass(string $className, array $options = []): string
    {
        $code = "<?php\n\n";

        // Add namespace
        if ($this->namespace) {
            $code .= "namespace {$this->namespace};\n\n";
        }

        // Add use statements
        if (!empty($this->useStatements)) {
            $code .= $this->generateUseStatements() . "\n\n";
        }

        // Add class PHPDoc
        if (isset($options['description'])) {
            $code .= $this->generateClassDocBlock($className, $options['description']);
        }

        // Class declaration
        $code .= "class {$className}";

        if (isset($options['extends'])) {
            $code .= " extends {$options['extends']}";
        }

        if (isset($options['implements'])) {
            $implements = is_array($options['implements']) 
                ? implode(', ', $options['implements']) 
                : $options['implements'];
            $code .= " implements {$implements}";
        }

        $code .= "\n{\n";

        // Add properties
        if (isset($options['properties']) && is_array($options['properties'])) {
            foreach ($options['properties'] as $property) {
                $code .= $this->generateProperty($property);
            }
            $code .= "\n";
        }

        // Add constructor
        if (isset($options['constructor'])) {
            $code .= $this->generateConstructor($options['constructor']);
            $code .= "\n";
        }

        // Add methods
        if (isset($options['methods']) && is_array($options['methods'])) {
            foreach ($options['methods'] as $method) {
                $code .= $this->generateMethod($method);
                $code .= "\n";
            }
        }

        $code .= "}\n";

        return $this->formatCode($code);
    }

    /**
     * Generate method code
     * 
     * @param array<string, mixed> $methodData Method data (name, visibility, params, return, body, etc.)
     * @return string Generated method code
     */
    public function generateMethod(array $methodData): string
    {
        $code = '';

        // Add method PHPDoc
        if (isset($methodData['description']) || isset($methodData['params']) || isset($methodData['return'])) {
            $code .= $this->generateMethodDocBlock($methodData);
        }

        // Method signature
        $visibility = $methodData['visibility'] ?? 'public';
        $static = isset($methodData['static']) && $methodData['static'] ? 'static ' : '';
        $name = $methodData['name'];

        $code .= $this->indent(1) . "{$visibility} {$static}function {$name}(";

        // Parameters
        if (isset($methodData['params']) && is_array($methodData['params'])) {
            $params = [];
            foreach ($methodData['params'] as $param) {
                $paramStr = '';
                
                // Type hint
                if (isset($param['type'])) {
                    $paramStr .= $param['type'] . ' ';
                }
                
                // Parameter name
                $paramStr .= '$' . $param['name'];
                
                // Default value
                if (isset($param['default'])) {
                    $defaultValue = $param['default'];
                    // Don't quote special values like [], {}, null, true, false, or numeric values
                    $specialValues = ['[]', '{}', 'null', 'true', 'false'];
                    if (is_string($defaultValue) && !in_array($defaultValue, $specialValues) && !is_numeric($defaultValue)) {
                        $defaultValue = "'{$defaultValue}'";
                    }
                    $paramStr .= " = {$defaultValue}";
                }
                
                $params[] = $paramStr;
            }
            $code .= implode(', ', $params);
        }

        $code .= ")";

        // Return type
        if (isset($methodData['return'])) {
            $code .= ": {$methodData['return']}";
        }

        $code .= "\n" . $this->indent(1) . "{\n";

        // Method body
        if (isset($methodData['body'])) {
            $body = is_array($methodData['body']) 
                ? implode("\n", $methodData['body']) 
                : $methodData['body'];
            $code .= $this->indentLines($body, 2);
        }

        $code .= "\n" . $this->indent(1) . "}\n";

        return $code;
    }

    /**
     * Generate property code
     * 
     * @param array<string, mixed> $propertyData Property data (name, visibility, type, default, etc.)
     * @return string Generated property code
     */
    public function generateProperty(array $propertyData): string
    {
        $code = '';

        // Add property PHPDoc
        if (isset($propertyData['description']) || isset($propertyData['type'])) {
            $code .= $this->indent(1) . "/**\n";
            
            if (isset($propertyData['description'])) {
                $code .= $this->indent(1) . " * " . $propertyData['description'] . "\n";
                $code .= $this->indent(1) . " *\n";
            }
            
            if (isset($propertyData['type'])) {
                $code .= $this->indent(1) . " * @var " . $propertyData['type'] . "\n";
            }
            
            $code .= $this->indent(1) . " */\n";
        }

        // Property declaration
        $visibility = $propertyData['visibility'] ?? 'private';
        $static = isset($propertyData['static']) && $propertyData['static'] ? 'static ' : '';
        $name = $propertyData['name'];

        $code .= $this->indent(1) . "{$visibility} {$static}";

        // Type declaration (PHP 7.4+)
        if (isset($propertyData['type'])) {
            $code .= $propertyData['type'] . ' ';
        }

        $code .= "\${$name}";

        // Default value
        if (isset($propertyData['default'])) {
            $defaultValue = $propertyData['default'];
            if (is_string($defaultValue) && $defaultValue !== 'null' && $defaultValue !== '[]' && !is_numeric($defaultValue)) {
                $defaultValue = "'{$defaultValue}'";
            }
            $code .= " = {$defaultValue}";
        }

        $code .= ";\n";

        return $code;
    }

    /**
     * Generate constructor code
     * 
     * @param array<string, mixed> $constructorData Constructor data (params, body, etc.)
     * @return string Generated constructor code
     */
    public function generateConstructor(array $constructorData): string
    {
        $methodData = array_merge([
            'name' => '__construct',
            'visibility' => 'public',
        ], $constructorData);

        return $this->generateMethod($methodData);
    }

    /**
     * Generate PHPDoc comment for class
     * 
     * @param string $className Class name
     * @param string $description Class description
     * @return string Generated PHPDoc block
     */
    public function generateClassDocBlock(string $className, string $description): string
    {
        $doc = "/**\n";
        $doc .= " * {$className}\n";
        $doc .= " *\n";
        
        // Split description into lines if it's too long
        $lines = $this->wrapText($description, 80);
        foreach ($lines as $line) {
            $doc .= " * {$line}\n";
        }
        
        $doc .= " *\n";
        $doc .= " * @package {$this->namespace}\n";
        $doc .= " */\n";

        return $doc;
    }

    /**
     * Generate PHPDoc comment for method
     * 
     * @param array<string, mixed> $methodData Method data
     * @return string Generated PHPDoc block
     */
    public function generateMethodDocBlock(array $methodData): string
    {
        $doc = $this->indent(1) . "/**\n";

        // Description
        if (isset($methodData['description'])) {
            $lines = $this->wrapText($methodData['description'], 75);
            foreach ($lines as $line) {
                $doc .= $this->indent(1) . " * {$line}\n";
            }
            $doc .= $this->indent(1) . " *\n";
        }

        // Parameters
        if (isset($methodData['params']) && is_array($methodData['params'])) {
            foreach ($methodData['params'] as $param) {
                $type = $param['type'] ?? 'mixed';
                $name = $param['name'];
                $description = $param['description'] ?? '';
                $doc .= $this->indent(1) . " * @param {$type} \${$name}";
                if ($description) {
                    $doc .= " {$description}";
                }
                $doc .= "\n";
            }
        }

        // Return type
        if (isset($methodData['return'])) {
            $returnType = $methodData['return'];
            $returnDesc = $methodData['returnDescription'] ?? '';
            $doc .= $this->indent(1) . " * @return {$returnType}";
            if ($returnDesc) {
                $doc .= " {$returnDesc}";
            }
            $doc .= "\n";
        }

        $doc .= $this->indent(1) . " */\n";

        return $doc;
    }

    /**
     * Generate use statements section
     * 
     * @return string Generated use statements
     */
    private function generateUseStatements(): string
    {
        $statements = [];

        foreach ($this->useStatements as $key => $value) {
            if (is_numeric($key)) {
                // No alias
                $statements[] = "use {$value};";
            } else {
                // With alias
                $statements[] = "use {$key} as {$value};";
            }
        }

        // Sort alphabetically (PSR-12)
        sort($statements);

        return implode("\n", $statements);
    }

    /**
     * Format code according to PSR-12 standards
     * 
     * @param string $code Code to format
     * @return string Formatted code
     */
    public function formatCode(string $code): string
    {
        // Remove trailing whitespace from each line
        $lines = explode("\n", $code);
        $lines = array_map('rtrim', $lines);
        
        // Ensure single blank line between use statements and class declaration
        $code = implode("\n", $lines);
        $code = preg_replace("/(\n\n)\n+/", "\n\n", $code);
        
        // Ensure file ends with single newline
        $code = rtrim($code) . "\n";

        return $code;
    }

    /**
     * Validate PHP syntax of generated code
     * 
     * @param string $code PHP code to validate
     * @return array{valid: bool, error: string|null} Validation result
     */
    public function validateSyntax(string $code): array
    {
        // Create temporary file for syntax check
        $tempFile = tempnam(sys_get_temp_dir(), 'php_syntax_check_');
        file_put_contents($tempFile, $code);

        // Run PHP syntax check
        $output = [];
        $returnCode = 0;
        exec("php -l " . escapeshellarg($tempFile) . " 2>&1", $output, $returnCode);

        // Clean up
        unlink($tempFile);

        if ($returnCode === 0) {
            return ['valid' => true, 'error' => null];
        }

        return [
            'valid' => false,
            'error' => implode("\n", $output),
        ];
    }

    /**
     * Process template with variables
     * 
     * @param string $template Template string with {{variable}} placeholders
     * @return string Processed template
     */
    private function processTemplate(string $template): string
    {
        $code = $template;

        // Replace template variables
        foreach ($this->templateVars as $key => $value) {
            $placeholder = '{{' . $key . '}}';
            $code = str_replace($placeholder, $value, $code);
        }

        return $code;
    }

    /**
     * Generate indentation string
     * 
     * @param int $level Indentation level
     * @return string Indentation string
     */
    private function indent(int $level): string
    {
        return str_repeat(' ', $level * $this->indentSize);
    }

    /**
     * Indent multiple lines of code
     * 
     * @param string $code Code to indent
     * @param int $level Indentation level
     * @return string Indented code
     */
    private function indentLines(string $code, int $level): string
    {
        $lines = explode("\n", $code);
        $indented = [];

        foreach ($lines as $line) {
            if (trim($line) === '') {
                $indented[] = '';
            } else {
                $indented[] = $this->indent($level) . $line;
            }
        }

        return implode("\n", $indented);
    }

    /**
     * Wrap text to specified width
     * 
     * @param string $text Text to wrap
     * @param int $width Maximum line width
     * @return array<string> Array of wrapped lines
     */
    private function wrapText(string $text, int $width): array
    {
        $words = explode(' ', $text);
        $lines = [];
        $currentLine = '';

        foreach ($words as $word) {
            if (strlen($currentLine . ' ' . $word) <= $width) {
                $currentLine .= ($currentLine ? ' ' : '') . $word;
            } else {
                if ($currentLine) {
                    $lines[] = $currentLine;
                }
                $currentLine = $word;
            }
        }

        if ($currentLine) {
            $lines[] = $currentLine;
        }

        return $lines;
    }

    /**
     * Reset generator state
     * 
     * @return self
     */
    public function reset(): self
    {
        $this->useStatements = [];
        $this->namespace = null;
        $this->templateVars = [];
        return $this;
    }

    /**
     * Set indentation size
     * 
     * @param int $size Number of spaces per indent level
     * @return self
     */
    public function setIndentSize(int $size): self
    {
        $this->indentSize = $size;
        return $this;
    }
}
