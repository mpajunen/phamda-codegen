<?php

namespace Phamda\Generator;

use Phamda\Builder\PhamdaBuilder;
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
        return (new PhamdaBuilder($this->getInnerFunctions()))->build();
    }

    private function createTests()
    {
        return (new BasicTestBuilder($this->getInnerFunctions()))->build();
    }

    private function getInnerFunctions()
    {
        $functions = [];
        foreach ($this->getInnerFunctionsMainNodes() as $itemGroup) {
            foreach ($itemGroup->value->items as $item) {
                $functions[$item->key->value] = [$itemGroup->key->value, $item->value];
            }
        }
        ksort($functions);

        return $functions;
    }

    private function getInnerFunctionsMainNodes()
    {
        return $this->createParser()
            ->parse(file_get_contents(__DIR__ . '/../Functions/InnerFunctions.php'))[0]
            ->stmts[1]
            ->expr
            ->items;
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
