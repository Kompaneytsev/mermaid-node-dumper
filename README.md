# Mermaid node dumper

Output ast tree built by [nikic/php-parser](https://github.com/nikic/php-parser) and represented in [mermaid](https://mermaid.js.org/) using [jbzoo/mermaid-php](https://github.com/JBZoo/Mermaid-PHP).
Extended `\Kompaneytsev\MermaidNodeDumper\StyledNode` supports mermaid styles, check out [advanced example](#advanced-example).

## Simple example
```php
<?php

require 'vendor/autoload.php';

use Kompaneytsev\MermaidNodeDumper\NodeDumper;
use PhpParser\ParserFactory;

$code = <<<'CODE'
<?php

function test($foo)
{
    var_dump($foo);
}
CODE;

$parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
$ast = $parser->parse($code);
$dumper = new NodeDumper;

echo $dumper->dump($ast)->getLiveEditorUrl();
```

### Output
https://mermaid-js.github.io/mermaid-live-editor/#/edit/eyJjb2RlIjoiZ3JhcGggVEI7XG4gICAgSURfMShcIlN0bXRfRnVuY3Rpb25cIik7XG4gICAgSURfMihcIklkZW50aWZpZXJcIik7XG4gICAgSURfMyhcIlBhcmFtXCIpO1xuICAgIElEXzQoXCJFeHByX1ZhcmlhYmxlXCIpO1xuICAgIElEXzUoXCJTdG10X0V4cHJlc3Npb25cIik7XG4gICAgSURfNihcIkV4cHJfRnVuY0NhbGxcIik7XG4gICAgSURfNyhcIk5hbWVcIik7XG4gICAgSURfOChcIkFyZ1wiKTtcbiAgICBJRF85KFwiRXhwcl9WYXJpYWJsZVwiKTtcblxuICAgIElEXzEtLT5JRF8yO1xuICAgIElEXzEtLT5JRF8zO1xuICAgIElEXzMtLT5JRF80O1xuICAgIElEXzEtLT5JRF81O1xuICAgIElEXzUtLT5JRF82O1xuICAgIElEXzYtLT5JRF83O1xuICAgIElEXzYtLT5JRF84O1xuICAgIElEXzgtLT5JRF85O1xuIiwibWVybWFpZCI6eyJ0aGVtZSI6ImZvcmVzdCJ9fQ==/app

## Install
```shell
composer require --dev kompaneytsev/mermaid-node-dumper
```

### Tests
```shell
vendor/bin/phpunit
```

### Phpstan
```shell
vendor/bin/phpstan analyse
```

## Advanced usage

```php
<?php

declare(strict_types=1);

require 'vendor/autoload.php';

use JBZoo\MermaidPHP\Node;
use Kompaneytsev\MermaidNodeDumper\NodeDumper;
use Kompaneytsev\MermaidNodeDumper\StyledNode as MermaidNode;
use PhpParser\Error;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Return_;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;

$code = <<<'CODE'
<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;

class LuckyController
{
    public function number(): Response
    {
        $number = random_int(0, 100);

        return new Response(
            '<html><body>Lucky number: '.$number.'</body></html>'
        );
    }
}

CODE;

$parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
try {
    $ast = $parser->parse($code);
} catch (Error $error) {
    echo "Parse error: {$error->getMessage()}\n";
    return;
}

$dumper = new NodeDumper([
    ClassMethod::class => static function (string $id, ClassMethod $node): MermaidNode {
        $style = sprintf('style %s fill:#f9f,color:#000', $id);
        $name = sprintf('%s (%s)', $node->getType(), $node->name);

        return new MermaidNode($id, $name, Node::ROUND, $style);
    },
    Return_::class => static function (string $id, Return_ $node): MermaidNode {
        $prettyPrinter = new Standard();
        $style = sprintf('style %s fill:#ff0,color:#000', $id);
        $name = sprintf('%s (%s)', $node->getType(), $prettyPrinter->prettyPrint([$node]));

        return new MermaidNode($id, $name, Node::ROUND, $style);
    },
],[
    Return_::class => static function (Return_ $node): bool
    {
        return true;
    }
], [
    Identifier::class => static function (Identifier $node): bool
    {
        return true;
    }
]);

echo (string) $dumper->dump($ast);
```