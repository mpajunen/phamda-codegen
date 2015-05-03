<?php

namespace Phamda\CodeGen\Builder\Tests;

use Phamda\CodeGen\Builder\BuilderInterface;
use Phamda\CodeGen\Functions\FunctionCollection;
use Phamda\Phamda;
use PhpParser\BuilderFactory;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\TraitUse;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\Stmt\UseUse;

class BasicTestBuilder implements BuilderInterface
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
            ->addStmt(new Use_([new UseUse(new Name('Phamda\Phamda'))]))
            ->addStmt($this->createClass($factory))
            ->getNode();
    }

    private function createClass(BuilderFactory $factory)
    {
        return $factory->class('BasicTest')
            ->extend('\PHPUnit_Framework_TestCase')
            ->addStmt(new TraitUse([
                new Name('BasicProvidersTrait'),
                new Name('CurryTestTrait'),
            ]))
            ->addStmts($this->createClassMethods());
    }

    private function createClassMethods()
    {
        $create = Phamda::pipe(
            Phamda::reject(Phamda::invoker(0, 'returnsObject')),
            Phamda::map(Phamda::pipe(
                Phamda::construct(BasicTestMethodBuilder::class),
                Phamda::invoker(0, 'build')
            ))
        );

        return $create($this->functions->getFunctions());
    }
}
