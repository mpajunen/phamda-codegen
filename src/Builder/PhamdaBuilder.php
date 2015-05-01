<?php

namespace Phamda\Builder;

use Phamda\Phamda;
use PhpParser\BuilderFactory;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\TraitUse;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\Stmt\UseUse;

class PhamdaBuilder implements BuilderInterface
{
    private $factory;
    private $functions;
    private $variables;

    public function __construct(PhamdaFunctionCollection $functions, array $variables = [])
    {
        $this->factory   = new BuilderFactory();
        $this->functions = $functions;
        $this->variables = $variables;
    }

    public function build()
    {
        return $this->factory->namespace('Phamda')
            ->addStmt(new Use_([new UseUse(new Name('Phamda\Collection\Collection'))]))
            ->addStmt(new Use_([new UseUse(new Name('Phamda\Exception\InvalidFunctionCompositionException'))]))
            ->addStmt($this->createClass())
            ->getNode();
    }

    private function createClass()
    {
        return $this->factory->class('Phamda')
            ->addStmt(new TraitUse([new Name('CoreFunctionsTrait')]))
            ->addStmts($this->createClassMethods());
    }

    private function createClassMethods()
    {
        $create = function (PhamdaFunction $function) { return (new MethodBuilder($function))->build(); };

        return Phamda::map($create, $this->functions->getFunctions());
    }
}
