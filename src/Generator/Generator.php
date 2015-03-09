<?php

namespace Phamda\Generator;

use Phamda\Builder\PhamdaBuilder;
use Phamda\Builder\PhamdaFunctionCollection;
use Phamda\Builder\Tests\BasicTestBuilder;
use Phamda\Builder\Tests\CollectionTestBuilder;
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
        $this->writeClass($outDir . '/tests/BasicTest.php', $this->createBasicTests());
        $this->writeClass($outDir . '/tests/CollectionTest.php', $this->createCollectionTests());
    }

    private function writeClass($filename, Node $node)
    {
        file_put_contents($filename, $this->printFile($node) . "\n");
    }

    private function createPhamda()
    {
        return (new PhamdaBuilder(... $this->getSourceData()))->build();
    }

    private function createBasicTests()
    {
        return (new BasicTestBuilder(... $this->getSourceData()))->build();
    }

    private function createCollectionTests()
    {
        return (new CollectionTestBuilder(... $this->getSourceData()))->build();
    }

    private function getSourceData()
    {
        $statements = $this->getSourceStatements();

        $variables = [];
        foreach ($statements[1]->expr->items as $arrayItem) {
            $variables[$arrayItem->value->var->name] = $arrayItem->value->expr;
        }

        $functions = new PhamdaFunctionCollection($statements[2]->expr->items);

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

    private function printFile(Node $node)
    {
        return $this->getFileComment() . $this->printBuilder($node);
    }

    private function printBuilder(Node $node)
    {
        return (new PrettyPrinter\Standard())
            ->prettyPrint([$node]);
    }

    private function getFileComment()
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
