<?php

namespace Phamda\Builder\Tests;

use Phamda\Builder\BuilderInterface;
use Phamda\Builder\PhamdaFunctionCollection;
use PhpParser\Builder;
use PhpParser\BuilderFactory;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\TraitUse;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\Stmt\UseUse;

class CollectionTestBuilder implements BuilderInterface
{
    private $functions;

    public function __construct(PhamdaFunctionCollection $functions)
    {
        $this->functions = $functions;
    }

    public function build()
    {
        $factory = new BuilderFactory();

        return $factory->namespace('Phamda\Tests')
            ->addStmt(new Use_([new UseUse(new Name('Phamda\Phamda'))]))
            ->addStmt(new Use_([new UseUse(new Name('Phamda\Tests\Fixtures\ArrayCollection'))]))
            ->addStmt(new Use_([new UseUse(new Name('Phamda\Tests\Fixtures\ArrayContainer'))]))
            ->addStmt($this->createClass($factory))
            ->getNode();
    }

    private function createClass(BuilderFactory $factory)
    {
        return $factory->class('CollectionTest')
            ->extend('\PHPUnit_Framework_TestCase')
            ->addStmt(new TraitUse([
                new Name('BasicProvidersTrait'),
                new Name('CollectionTestTrait'),
            ]))
            ->addStmts($this->createClassMethods());
    }

    private function createClassMethods()
    {
        $methods = [];
        foreach ($this->functions->getFunctions() as $function) {
            if ($function->isCollectionFunction()) {
                $methods[] = (new CollectionTestMethodBuilder($function, false))->build();
                $methods[] = (new CollectionTestMethodBuilder($function, true))->build();
            }
        }

        return $methods;
    }
}
