<?php

declare(strict_types=1);

namespace Kompaneytsev\MermaidNodeDumper;

use JBZoo\MermaidPHP\Graph;
use JBZoo\MermaidPHP\Helper;

class StyledGraph extends Graph
{
    private const RENDER_SHIFT = 4;
    
    /**
     * @var StyledNode[]
     */
    protected array $nodes = [];

    /**
     * @inheritDoc
     */
    public function render(bool $isMainGraph = true, int $shift = 0): string
    {
        $spaces = \str_repeat(' ', $shift);
        $spacesSub = \str_repeat(' ', $shift + self::RENDER_SHIFT);

        if ($isMainGraph) {
            /** @phpstan-ignore-next-line */
            $result = ["graph {$this->params['direction']};"];
        } else {
            /** @phpstan-ignore-next-line */
            $result = ["{$spaces}subgraph " . Helper::escape((string)$this->params['title'])];
        }

        if (\count($this->nodes) > 0) {
            $tmp = [];
            foreach ($this->nodes as $node) {
                $tmp[] = $spacesSub . $node;
                
                if ($node instanceof StyledNode && $node->getStyle() !== '') {                                                     // <----- added if statement
                    $tmp[] = $spacesSub . $node->getStyle();
                }
            }
            if ($this->params['abc_order']) {
                \sort($tmp);
            }
            $result = \array_merge($result, $tmp);
            if ($isMainGraph) {
                $result[] = '';
            }
        }

        if (\count($this->links) > 0) {
            $tmp = [];
            foreach ($this->links as $link) {
                $tmp[] = $spacesSub . $link;
            }
            if ($this->params['abc_order']) {
                \sort($tmp);
            }
            $result = \array_merge($result, $tmp);
            if ($isMainGraph) {
                $result[] = '';
            }
        }

        foreach ($this->subGraphs as $subGraph) {
            $result[] = $subGraph->render(false, $shift + 4);
        }

        if ($isMainGraph && \count($this->styles) > 0) {
            foreach ($this->styles as $style) {
                $result[] = $spaces . $style . ';';
            }
        }

        if (!$isMainGraph) {
            $result[] = "{$spaces}end";
        }

        return \implode(\PHP_EOL, $result);
    }
}