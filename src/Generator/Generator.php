<?php

namespace Phamda\Generator;

use Phamda\Builder\BuilderInterface;
use Phamda\Builder\PhamdaBuilder;
use Phamda\Builder\PhamdaFunctionCollection;
use Phamda\Builder\Tests\BasicTestBuilder;
use Phamda\Builder\Tests\CollectionTestBuilder;
use Phamda\Printer\PhamdaPrinter;
use Phamda\Tests\FunctionExampleTest;
use PhpParser\Builder;
use PhpParser\Lexer;
use PhpParser\Node;
use PhpParser\Parser;
use PhpParser\PrettyPrinter;

class Generator
{
    public function generate($outDir)
    {
        $functions = $this->getSourceFunctions();
        $write     = function ($fileSubPath, $content) use ($outDir) {
            file_put_contents($outDir . '/' . $fileSubPath, $content . "\n");
        };

        $write('src/Phamda.php', $this->printClass(new PhamdaBuilder($functions)));
        $write('tests/BasicTest.php', $this->printClass(new BasicTestBuilder($functions)));
        $write('tests/CollectionTest.php', $this->printClass(new CollectionTestBuilder($functions)));
    }

    private function printClass(BuilderInterface $builder)
    {
        return $this->getPhpFileComment() . (new PhamdaPrinter())->prettyPrint([$builder->build()]);
    }

    private function getSourceFunctions()
    {
        return new PhamdaFunctionCollection(
            $this->getSourceStatements()[3]->expr->items,
            $this->getCustomExampleSource()
        );
    }

    private function getCustomExampleSource()
    {
        $file = (new \ReflectionClass(FunctionExampleTest::class))->getFileName();

        foreach ($this->createParser()->parse(file_get_contents($file))[0]->stmts as $node) {
            if ($node instanceof Node\Stmt\Class_) {
                return $node->stmts;
            }
        }

        throw new \LogicException(sprintf('Class statement not found.'));
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

    private function getPhpFileComment()
    {
        return <<<EOT
<?php

/*
 * This file is part of the Phamda library
 *
 * (c) Mikael Pajunen <mikael.pajunen@gmail.com>
 *
 * For the full copyright and license information, please see the LICENSE
 * file that was distributed with this source code.
 */


EOT;
    }
}
