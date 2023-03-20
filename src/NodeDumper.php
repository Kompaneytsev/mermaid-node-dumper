<?php

declare(strict_types=1);

namespace Kompaneytsev\MermaidNodeDumper;

use Closure;
use JBZoo\MermaidPHP\Link;
use PhpParser\Node;

class NodeDumper
{
    private const ID_PREFIX = 'ID_';

    private StyledGraph $graph;
    private int $nodeCounter = 0;

    /**
     * @var array<string, Closure>
     */
    private array $formatRules;

    /**
     * @var array<string, Closure>
     */
    private array $collapseRules;

    /**
     * @var array<string, Closure>
     */
    private array $hideRules;

    /**
     * @param array<string, Closure> $formatRules
     * @param array<string, Closure> $collapseRules
     * @param array<string, Closure> $hideRules
     */
    public function __construct(array $formatRules = [], array $collapseRules = [], array $hideRules = [])
    {
        $this->formatRules = $formatRules;
        $this->collapseRules = $collapseRules;
        $this->hideRules = $hideRules;
    }

    /**
     * @param array<Node>|Node $node Node or array to dump
     * @return StyledGraph
     */
    public function dump(array|Node $node): StyledGraph
    {
        $this->graph = new StyledGraph();
        $this->dumpRecursive($node);
        return $this->graph;
    }

    /**
     * @param array<Node>|Node $node
     */
    private function dumpRecursive(array|Node $node, StyledNode $parentNode = null): void
    {
        if ($node instanceof Node) {
            if ($this->isHide($node)) {
                return;
            }

            $this->nodeCounter++;
            $mermaidNode = $this->formatMermaidByType(self::ID_PREFIX . $this->nodeCounter, $node);

            $this->graph->addNode($mermaidNode);

            if ($parentNode !== null) {
                $this->graph->addLink(new Link($parentNode, $mermaidNode));
            }

            if (!$this->isCollapsed($node)) {
                foreach ($node->getSubNodeNames() as $key) {
                    $value = $node->$key;

                    if (is_array($value) || $value instanceof Node) {
                        $this->dumpRecursive($value, $mermaidNode);
                    }
                }
            }
        }

        if (is_array($node)) {
            foreach ($node as $value) {
                if ($value instanceof Node) {
                    $this->dumpRecursive($value, $parentNode);
                }
            }
        }
    }

    private function formatMermaidByType(string $id, Node $node): StyledNode
    {
        if (array_key_exists(get_class($node), $this->formatRules)) {
            return $this->formatRules[get_class($node)]($id, $node);
        }

        return new StyledNode($id, $node->getType());
    }
    
    private function isCollapsed(Node $node): bool
    {
        if (array_key_exists(get_class($node), $this->collapseRules)) {
            return $this->collapseRules[get_class($node)]($node);
        }

        return false;
    }
    
    private function isHide(Node $node): bool
    {
        if (array_key_exists(get_class($node), $this->hideRules)) {
            return $this->hideRules[get_class($node)]($node);
        }

        return false;
    }
}