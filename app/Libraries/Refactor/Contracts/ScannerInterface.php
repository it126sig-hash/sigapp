<?php

namespace App\Libraries\Refactor\Contracts;

/**
 * Scanner Interface
 * 
 * Base interface for components that scan and analyze code.
 * 
 * @package App\Libraries\Refactor\Contracts
 */
interface ScannerInterface
{
    /**
     * Scan the specified path or module
     * 
     * @param string $target Target path or module name to scan
     * @return mixed Scan results
     */
    public function scan(string $target): mixed;
}
