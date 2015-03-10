<?php

namespace Phamda\Builder;

use PhpParser\Builder;
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
            ->addStmt(new Use_([new UseUse(new Name('Doctrine\Common\Collections\Collection'))]))
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
        foreach ($this->functions->getFunctions() as $function) {
            $methods[] = $this->createClassMethod($function);
        }

        return $methods;
    }

    private function createClassMethod(PhamdaFunction $function)
    {
        switch ($function->getWrapType()) {
            case 'curried':
                $builder = new CurriedMethodBuilder($function);
                break;
            case 'simple':
                $builder = new SimpleMethodBuilder($function);
                break;
            case 'wrapped':
                $builder = new WrappedMethodBuilder($function, $this->variables);
                break;
            default:
                throw new \LogicException(sprintf('Invalid method type "%s".', $function->getWrapType()));
        }

        return $builder->build();
    }
}
