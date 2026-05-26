<?php

// Simple test to check if our classes can be loaded
require_once 'vendor/autoload.php';

// Define APPPATH constant
define('APPPATH', __DIR__ . '/app/');

echo "Testing class loading...\n";

try {
    // Test FileScanner
    $fileScanner = new \App\Libraries\Refactor\Discovery\FileScanner();
    echo "✓ FileScanner loaded successfully\n";
    
    // Test CodeParser
    $codeParser = new \App\Libraries\Refactor\Discovery\CodeParser();
    echo "✓ CodeParser loaded successfully\n";
    
    // Test ModuleDiscovery
    $moduleDiscovery = new \App\Libraries\Refactor\Discovery\ModuleDiscovery(
        APPPATH, 
        $fileScanner, 
        $codeParser
    );
    echo "✓ ModuleDiscovery loaded successfully\n";
    
    // Test basic scanning
    echo "\nTesting basic functionality...\n";
    $controllers = $moduleDiscovery->scanControllers();
    echo "✓ scanControllers() returned " . count($controllers) . " results\n";
    
    echo "\nAll basic tests passed!\n";
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}