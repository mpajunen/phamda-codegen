<?php

namespace Phamda\Builder;

use PhpParser\Builder;
use PhpParser\BuilderFactory;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\TraitUse;

class PhamdaBuilder
{
    private $functions;

    public function __construct(array $functions)
    {
        $this->functions = $functions;
    }

    /**
     * @return Builder
     */
    public function build()
    {
        $factory = new BuilderFactory();

        return $factory->namespace('Phamda')
            ->addStmt($this->createClass($factory));
    }

    private function createClass(BuilderFactory $factory)
    {
        return $factory->class('Phamda')
             ->addStmt(new TraitUse([new Name('CoreFunctionsTrait')]))
             ->addStmts($this->createClassMethods());
    }

    private function createClassMethods()
    {
        $methods = [];
        foreach ($this->functions as $function) {
            $methods[] = (new PhamdaMethodBuilder($function))->build();
        }

        return $methods;
    }
}
