<?php

namespace Phamda\Generator;

use Phamda\Builder\PhamdaBuilder;
use Phamda\Builder\PhamdaFunctionCollection;
use Phamda\Builder\Tests\BasicTestBuilder;
use PhpParser\Builder;
use PhpParser\Lexer;
use PhpParser\Node;
use PhpParser\Parser;
use PhpParser\PrettyPrinter;

class Generator
{
    public function generate($outDir)
    {
        $this->writeClass($outDir . '/src/Phamda.php', $this->createPhamda());
        $this->writeClass($outDir . '/tests/PhamdaTest.php', $this->createTests());
    }

    private function writeClass($filename, Node $node)
    {
        file_put_contents($filename, $this->printBuilder($node) . "\n");
    }

    private function createPhamda()
    {
        return (new PhamdaBuilder(... $this->getSourceData()))->build();
    }

    private function createTests()
    {
        return (new BasicTestBuilder(... $this->getSourceData()))->build();
    }

    private function getSourceData()
    {
        $statements = $this->getSourceStatements();

        $variables = [];
        foreach ($statements[0]->expr->items as $arrayItem) {
            $variables[$arrayItem->value->var->name] = $arrayItem->value->expr;
        }

        $functions = new PhamdaFunctionCollection($statements[1]->expr->items);

        return [
            $functions,
            $variables,
        ];
    }

    private function getSourceStatements()
    {
        return $this->createParser()
            ->parse(file_get_contents(__DIR__ . '/../Functions/InnerFunctions.php'))[0]
            ->stmts;
    }

    private function createParser()
    {
        return new Parser(new Lexer\Emulative());
    }

    private function printBuilder(Node $node)
    {
        return (new PrettyPrinter\Standard())
            ->prettyPrintFile([$node]);
    }
}
