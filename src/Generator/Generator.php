<?php

namespace Phamda\Generator;

use Phamda\Builder\PhamdaBuilder;
use Phamda\Builder\Tests\BasicTestBuilder;
use PhpParser\Builder;
use PhpParser\Lexer;
use PhpParser\Parser;
use PhpParser\PrettyPrinter;

class Generator
{
    public function generate($outDir)
    {
        file_put_contents($outDir . '/src/Phamda.php', $this->printBuilder($this->createPhamda()) . "\n");
        file_put_contents($outDir . '/tests/PhamdaTest.php', $this->printBuilder($this->createTests()) . "\n");
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
        return $this->createParser()
            ->parse(file_get_contents(__DIR__ . '/../Functions/InnerFunctions.php'))
            [0]
            ->stmts;
    }

    private function createParser()
    {
        return new Parser(new Lexer\Emulative());
    }

    private function printBuilder(Builder $builder)
    {
        return (new PrettyPrinter\Standard())
            ->prettyPrintFile([$builder->getNode()]);
    }
}
