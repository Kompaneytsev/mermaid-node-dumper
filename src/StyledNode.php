<?php

declare(strict_types=1);

namespace Kompaneytsev\MermaidNodeDumper;

use JBZoo\MermaidPHP\Node;

class StyledNode extends Node
{
    private string $style;

    public function __construct(string $identifier, string $title = '', string $form = self::ROUND, string $style = '')
    {
        parent::__construct($identifier, $title, $form);
        $this->style = $style;
    }

    public function getStyle(): string
    {
        return $this->style;
    }
}