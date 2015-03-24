<?php

namespace Phamda\Builder\Tests;

use Phamda\Builder\BuilderInterface;
use Phamda\Builder\PhamdaFunction;
use Phamda\Builder\PhamdaFunctionCollection;
use Phamda\Phamda;
use PhpParser\Builder;
use PhpParser\BuilderFactory;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\TraitUse;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\Stmt\UseUse;

class BasicTestBuilder implements BuilderInterface
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
            ->addStmt($this->createClass($factory))
            ->getNode();
    }

    private function createClass(BuilderFactory $factory)
    {
        return $factory->class('BasicTest')
            ->extend('\PHPUnit_Framework_TestCase')
            ->addStmt(new TraitUse([
                new Name('BasicProvidersTrait'),
            ]))
            ->addStmts($this->createClassMethods());
    }

    private function createClassMethods()
    {
        $create = Phamda::pipe(
            Phamda::reject(function (PhamdaFunction $function) { return in_array($function->getName(), $this->getSkipped()); }),
            Phamda::map(Phamda::pipe(
                Phamda::construct(BasicTestMethodBuilder::class),
                Phamda::invoker(0, 'build')
            ))
        );

        return $create($this->functions->getFunctions());
    }

    private function getSkipped()
    {
        return [
            '_',
            'clone_',
            'construct',
            'constructN',
            'invoker',
            'partial',
            'partialN',
        ];
    }
}
