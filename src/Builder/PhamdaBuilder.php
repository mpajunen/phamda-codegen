<?php

namespace Phamda\Builder;

use PhpParser\Builder;
use PhpParser\BuilderFactory;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\TraitUse;

class PhamdaBuilder implements BuilderInterface
{
    private $factory;
    private $functions;

    public function __construct(array $functions)
    {
        $this->factory   = new BuilderFactory();
        $this->functions = $functions;
    }

    public function build()
    {
        return $this->factory->namespace('Phamda')
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
        $methods = [];
        foreach ($this->functions as $name => list($type, $function)) {
            $methods[] = $this->createClassMethod($type, $name, $function);
        }

        return $methods;
    }

    private function createClassMethod($type, $name, Closure $closure)
    {
        switch ($type) {
            case 'curried':
                $builder = new CurriedMethodBuilder($name, $closure);
                break;
            case 'simple':
                $builder = new SimpleMethodBuilder($name, $closure);
                break;
            case 'wrapped':
                $builder = new WrappedMethodBuilder($name, $closure);
                break;
            default:
                throw new \LogicException(sprintf('Invalid method type "%s".'));
        }

        return $builder->build();
    }
}
