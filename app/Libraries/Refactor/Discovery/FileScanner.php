<?php

namespace App\Libraries\Refactor\Discovery;

use App\Libraries\Refactor\Contracts\ScannerInterface;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

/**
 * FileScanner
 * 
 * Recursively scans directories for PHP files with filtering capabilities.
 * Supports filtering by file type (controllers, models, services, repositories).
 * 
 * @package App\Libraries\Refactor\Discovery
 */
class FileScanner implements ScannerInterface
{
    /**
     * File type filters
     */
    public const FILTER_CONTROLLERS = 'controllers';
    public const FILTER_MODELS = 'models';
    public const FILTER_SERVICES = 'services';
    public const FILTER_REPOSITORIES = 'repositories';
    public const FILTER_ALL = 'all';

    /**
     * @var array<string> Active filters
     */
    private array $filters = [];

    /**
     * @var array<string> Directories to exclude from scanning
     */
    private array $excludeDirs = [
        'vendor',
        'tests',
        'writable',
        'public',
        '.git',
        '.idea',
        'node_modules',
    ];

    /**
     * Set file type filters
     * 
     * @param array<string> $filters Array of filter constants
     * @return self
     */
    public function setFilters(array $filters): self
    {
        $this->filters = $filters;
        return $this;
    }

    /**
     * Add a single filter
     * 
     * @param string $filter Filter constant
     * @return self
     */
    public function addFilter(string $filter): self
    {
        if (!in_array($filter, $this->filters, true)) {
            $this->filters[] = $filter;
        }
        return $this;
    }

    /**
     * Set directories to exclude from scanning
     * 
     * @param array<string> $dirs Array of directory names to exclude
     * @return self
     */
    public function setExcludeDirs(array $dirs): self
    {
        $this->excludeDirs = $dirs;
        return $this;
    }

    /**
     * Scan directory recursively for PHP files
     * 
     * @param string $target Directory path to scan
     * @return array<string> Array of file paths
     * @throws \InvalidArgumentException If directory doesn't exist
     */
    public function scan(string $target): array
    {
        if (!is_dir($target)) {
            throw new \InvalidArgumentException("Directory not found: {$target}");
        }

        $files = [];

        try {
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($target, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::SELF_FIRST
            );

            /** @var SplFileInfo $file */
            foreach ($iterator as $file) {
                // Skip directories
                if ($file->isDir()) {
                    continue;
                }

                // Skip non-PHP files
                if ($file->getExtension() !== 'php') {
                    continue;
                }

                // Skip excluded directories
                if ($this->isInExcludedDir($file->getPathname())) {
                    continue;
                }

                // Apply filters if set
                if (!empty($this->filters) && !in_array(self::FILTER_ALL, $this->filters, true)) {
                    if (!$this->matchesFilters($file->getPathname())) {
                        continue;
                    }
                }

                $files[] = $file->getPathname();
            }
        } catch (\Exception $e) {
            throw new \RuntimeException("Error scanning directory: {$e->getMessage()}", 0, $e);
        }

        return $files;
    }

    /**
     * Scan multiple directories
     * 
     * @param array<string> $directories Array of directory paths
     * @return array<string> Array of file paths
     */
    public function scanMultiple(array $directories): array
    {
        $allFiles = [];

        foreach ($directories as $directory) {
            try {
                $files = $this->scan($directory);
                $allFiles = array_merge($allFiles, $files);
            } catch (\InvalidArgumentException $e) {
                // Skip non-existent directories
                continue;
            }
        }

        return array_unique($allFiles);
    }

    /**
     * Check if file path is in an excluded directory
     * 
     * @param string $filePath File path to check
     * @return bool
     */
    private function isInExcludedDir(string $filePath): bool
    {
        $normalizedPath = str_replace('\\', '/', $filePath);

        foreach ($this->excludeDirs as $excludeDir) {
            if (str_contains($normalizedPath, '/' . $excludeDir . '/')) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if file matches active filters
     * 
     * @param string $filePath File path to check
     * @return bool
     */
    private function matchesFilters(string $filePath): bool
    {
        $normalizedPath = str_replace('\\', '/', $filePath);

        foreach ($this->filters as $filter) {
            switch ($filter) {
                case self::FILTER_CONTROLLERS:
                    if (str_contains($normalizedPath, '/Controllers/')) {
                        return true;
                    }
                    break;

                case self::FILTER_MODELS:
                    if (str_contains($normalizedPath, '/Models/')) {
                        return true;
                    }
                    break;

                case self::FILTER_SERVICES:
                    if (str_contains($normalizedPath, '/Services/')) {
                        return true;
                    }
                    break;

                case self::FILTER_REPOSITORIES:
                    if (str_contains($normalizedPath, '/Repositories/')) {
                        return true;
                    }
                    break;
            }
        }

        return false;
    }

    /**
     * Get count of files that would be scanned
     * 
     * @param string $target Directory path
     * @return int Number of files
     */
    public function count(string $target): int
    {
        return count($this->scan($target));
    }

    /**
     * Check if directory contains any PHP files
     * 
     * @param string $target Directory path
     * @return bool
     */
    public function hasPhpFiles(string $target): bool
    {
        return $this->count($target) > 0;
    }
}
