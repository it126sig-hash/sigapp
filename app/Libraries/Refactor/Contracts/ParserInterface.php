<?php

namespace App\Libraries\Refactor\Contracts;

/**
 * Parser Interface
 * 
 * Base interface for components that parse code or files.
 * 
 * @package App\Libraries\Refactor\Contracts
 */
interface ParserInterface
{
    /**
     * Parse the provided content
     * 
     * @param string $content Content to parse (file path or code string)
     * @return mixed Parsed result
     */
    public function parse(string $content): mixed;
}
