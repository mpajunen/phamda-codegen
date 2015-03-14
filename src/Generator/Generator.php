<?php

namespace Phamda\Generator;

use Phamda\Builder\PhamdaBuilder;
use Phamda\Builder\PhamdaFunctionCollection;
use Phamda\Builder\Tests\BasicTestBuilder;
use Phamda\Builder\Tests\CollectionTestBuilder;
use Phamda\Printer\PhamdaPrinter;
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
        return (new PhamdaBuilder($this->getSourceFunctions()))->build();
    }

    private function createBasicTests()
    {
        return (new BasicTestBuilder($this->getSourceFunctions()))->build();
    }

    private function createCollectionTests()
    {
        return (new CollectionTestBuilder($this->getSourceFunctions()))->build();
    }

    private function getSourceFunctions()
    {
        return new PhamdaFunctionCollection($this->getSourceStatements()[3]->expr->items);
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
        return $this->getFileComment() . (new PhamdaPrinter())->prettyPrint([$node]);
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
