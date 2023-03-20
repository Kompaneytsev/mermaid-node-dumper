<?php

declare(strict_types=1);

namespace Kompaneytsev\MermaidNodeDumper\Tests;

use Closure;
use JBZoo\MermaidPHP\Node;
use Kompaneytsev\MermaidNodeDumper\NodeDumper;
use Kompaneytsev\MermaidNodeDumper\StyledNode as MermaidNode;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\Return_;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class MermaidNodeDumperTest extends TestCase
{
    /**
     * @return array<string, array<string, array<string, (Closure)|(Closure)|(Closure)>|string|false>>
     */
    public static function dataProviderDump(): array
    {
        return [
            'simple case' => [
                'code' => file_get_contents(__DIR__ . '/data-provider/function.php.inc'),
                'expected' => file_get_contents(__DIR__ . '/data-provider/simple.expected.inc'),
                'formatRules' => [],
                'collapseRules' => [],
                'hideRules' => [],
            ],
            'styled return node' => [
                'code' => file_get_contents(__DIR__ . '/data-provider/function.php.inc'),
                'expected' => file_get_contents(__DIR__ . '/data-provider/styled_return_node.expected.inc'),
                'formatRules' => [
                    Return_::class => static function (string $id, Return_ $node): MermaidNode {
                        $prettyPrinter = new Standard();
                        $style = sprintf('style %s fill:#ff0,color:#000', $id);
                        $name = sprintf('%s (%s)', $node->getType(), $prettyPrinter->prettyPrint([$node]));

                        return new MermaidNode($id, $name, Node::ROUND, $style);
                    }
                ],
                'collapseRules' => [],
                'hideRules' => [],
            ],
            'collapse return node' => [
                'code' => file_get_contents(__DIR__ . '/data-provider/function.php.inc'),
                'expected' => file_get_contents(__DIR__ . '/data-provider/collapse_return_node.expected.inc'),
                'formatRules' => [],
                'collapseRules' => [
                    Return_::class => static function (Return_ $node): bool {
                        return true;
                    }
                ],
                'hideRules' => [],
            ],
            'hide identifier node' => [
                'code' => file_get_contents(__DIR__ . '/data-provider/function.php.inc'),
                'expected' => file_get_contents(__DIR__ . '/data-provider/hide_identifier_node.expected.inc'),
                'formatRules' => [],
                'collapseRules' => [],
                'hideRules' => [
                    Identifier::class => static function (Identifier $node): bool {
                        return true;
                    }
                ],
            ],
        ];
    }
    
    /**
     * @dataProvider dataProviderDump
     * @param array<string, Closure> $formatRules
     * @param array<string, Closure> $collapseRules
     * @param array<string, Closure> $hideRules
     */
    public function testDump(string $code, string $expected, array $formatRules, array $collapseRules, array $hideRules): void
    {
        $parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
        $ast = $parser->parse($code);

        if ($ast === null) {
            throw new RuntimeException();
        }

        $dumper = new NodeDumper($formatRules, $collapseRules, $hideRules);
        
        self::assertEquals($expected, (string) $dumper->dump($ast));
    }
}