<?php

namespace Phamda\CodeGen\Builder\Tests;

use Phamda\CodeGen\Builder\BuilderInterface;
use Phamda\CodeGen\Functions\FunctionCollection;
use PhpParser\BuilderFactory;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\TraitUse;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\Stmt\UseUse;

class CollectionTestBuilder implements BuilderInterface
{
    private $functions;

    public function __construct(FunctionCollection $functions)
    {
        $this->functions = $functions;
    }

    public function build()
    {
        $factory = new BuilderFactory();

        return $factory->namespace('Phamda\Tests')
            ->addStmt($this->createUse('Phamda\Phamda'))
            ->addStmt($this->createUse('Phamda\Tests\Fixtures\ArrayCollection'))
            ->addStmt($this->createUse('Phamda\Tests\Fixtures\ArrayContainer'))
            ->addStmt($this->createClass($factory))
            ->getNode();
    }

    private function createUse($class)
    {
        return new Use_([new UseUse(new Name($class))]);
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
