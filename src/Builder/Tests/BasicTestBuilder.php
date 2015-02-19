<?php

namespace Phamda\Builder\Tests;

use Phamda\Builder\BuilderInterface;
use PhpParser\Builder;
use PhpParser\BuilderFactory;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\TraitUse;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\Stmt\UseUse;

class BasicTestBuilder implements BuilderInterface
{
    private $functions;

    public function __construct(array $functions)
    {
        $this->functions = $functions;
    }

    public function build()
    {
        $factory = new BuilderFactory();

        return $factory->namespace('Phamda\Tests')
            ->addStmt(new Use_([new UseUse(new Name('Phamda\Phamda'))]))
            ->addStmt($this->createClass($factory))
            ->getNode();
    }

    private function createClass(BuilderFactory $factory)
    {
        return $factory->class('PhamdaTest')
            ->extend('\PHPUnit_Framework_TestCase')
            ->addStmt(new TraitUse([
                new Name('BasicProvidersTrait'),
            ]))
            ->addStmts($this->createClassMethods());
    }

    private function createClassMethods()
    {
        $methods = [];
        foreach ($this->functions as $name => list(, $function)) {
            $methods[] = (new BasicTestMethodBuilder($name, $function))->build();
        }

        return $methods;
    }
}
