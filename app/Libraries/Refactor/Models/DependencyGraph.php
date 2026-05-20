<?php

namespace App\Libraries\Refactor\Models;

/**
 * Dependency Graph Data Model
 * 
 * Represents the dependency relationships between modules and provides
 * methods to query dependencies and calculate impact scores.
 * 
 * @package App\Libraries\Refactor\Models
 */
class DependencyGraph
{
    /**
     * Array of module names (nodes in the graph)
     * 
     * @var string[]
     */
    public array $nodes = [];

    /**
     * Array of dependency edges [from => [to1, to2, ...]]
     * Represents "from depends on to"
     * 
     * @var array<string, string[]>
     */
    public array $edges = [];

    /**
     * Array of impact scores [module => score]
     * Impact score = number of modules that depend on this module
     * 
     * @var array<string, int>
     */
    public array $impactScores = [];

    /**
     * Array of circular dependency chains
     * Each chain is an array of module names forming a cycle
     * 
     * @var array<int, string[]>
     */
    public array $circular = [];

    /**
     * Add a node (module) to the graph
     * 
     * @param string $module Module name
     * @return void
     */
    public function addNode(string $module): void
    {
        if (!in_array($module, $this->nodes, true)) {
            $this->nodes[] = $module;
        }
    }

    /**
     * Add a dependency edge (from depends on to)
     * 
     * @param string $from Module that has the dependency
     * @param string $to Module that is depended upon
     * @return void
     */
    public function addEdge(string $from, string $to): void
    {
        $this->addNode($from);
        $this->addNode($to);

        if (!isset($this->edges[$from])) {
            $this->edges[$from] = [];
        }

        if (!in_array($to, $this->edges[$from], true)) {
            $this->edges[$from][] = $to;
        }
    }

    /**
     * Get modules that the given module depends on
     * 
     * @param string $module Module name
     * @return string[]
     */
    public function getDependencies(string $module): array
    {
        return $this->edges[$module] ?? [];
    }

    /**
     * Get modules that depend on the given module
     * 
     * @param string $module Module name
     * @return string[]
     */
    public function getDependents(string $module): array
    {
        $dependents = [];

        foreach ($this->edges as $from => $toList) {
            if (in_array($module, $toList, true)) {
                $dependents[] = $from;
            }
        }

        return $dependents;
    }

    /**
     * Get the impact score for a module
     * 
     * @param string $module Module name
     * @return int Number of modules that depend on this module
     */
    public function getImpactScore(string $module): int
    {
        return $this->impactScores[$module] ?? 0;
    }

    /**
     * Set the impact score for a module
     * 
     * @param string $module Module name
     * @param int $score Impact score
     * @return void
     */
    public function setImpactScore(string $module, int $score): void
    {
        $this->impactScores[$module] = $score;
    }

    /**
     * Convert graph to Mermaid diagram syntax
     * 
     * @return string Mermaid diagram
     */
    public function toMermaid(): string
    {
        $lines = ['graph TD'];

        foreach ($this->edges as $from => $toList) {
            foreach ($toList as $to) {
                $lines[] = "    {$from} --> {$to}";
            }
        }

        // Add nodes with no dependencies or dependents
        foreach ($this->nodes as $node) {
            $hasDependencies = isset($this->edges[$node]) && count($this->edges[$node]) > 0;
            $hasDependents = count($this->getDependents($node)) > 0;

            if (!$hasDependencies && !$hasDependents) {
                $lines[] = "    {$node}";
            }
        }

        return implode("\n", $lines);
    }

    /**
     * Convert graph to JSON string
     * 
     * @return string
     */
    public function toJson(): string
    {
        $data = [
            'nodes' => $this->nodes,
            'edges' => $this->edges,
            'impactScores' => $this->impactScores,
            'circular' => $this->circular,
        ];

        return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Create DependencyGraph instance from JSON string
     * 
     * @param string $json JSON string
     * @return self
     * @throws \JsonException
     */
    public static function fromJson(string $json): self
    {
        $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);

        $graph = new self();
        $graph->nodes = $data['nodes'] ?? [];
        $graph->edges = $data['edges'] ?? [];
        $graph->impactScores = $data['impactScores'] ?? [];
        $graph->circular = $data['circular'] ?? [];

        return $graph;
    }
}
