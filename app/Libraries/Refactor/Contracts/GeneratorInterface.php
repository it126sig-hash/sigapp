<?php

namespace App\Libraries\Refactor\Contracts;

/**
 * Generator Interface
 * 
 * Base interface for components that generate code or reports.
 * 
 * @package App\Libraries\Refactor\Contracts
 */
interface GeneratorInterface
{
    /**
     * Generate output based on provided data
     * 
     * @param mixed $data Input data for generation
     * @return string Generated output (code, report, etc.)
     */
    public function generate(mixed $data): string;
}
