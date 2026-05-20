<?php

namespace App\Libraries\Refactor\Contracts;

/**
 * Analyzer Interface
 * 
 * Base interface for components that analyze code or data structures.
 * 
 * @package App\Libraries\Refactor\Contracts
 */
interface AnalyzerInterface
{
    /**
     * Analyze the provided data
     * 
     * @param mixed $data Data to analyze
     * @return mixed Analysis results
     */
    public function analyze(mixed $data): mixed;
}
