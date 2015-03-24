<?php

namespace Phamda\Builder;

use Phamda\Phamda;
use PhpParser\Node\Stmt\ClassMethod;

class PhamdaFunctionCollection implements \Countable
{
    private $exampleStatements;
    private $functions;
    private $innerFunctions;

    public function __construct(array $methods, array $exampleMethods)
    {
        $filter = Phamda::filter(Phamda::isInstance(ClassMethod::class));

        foreach ($filter($methods) as $method) {
            $name = $method->name;

            $this->innerFunctions[$name] = $method;
            $this->functions[$name]      = null;
        }

        foreach ($filter($exampleMethods) as $method) {
            $this->exampleStatements[lcfirst(substr($method->name, strlen('test')))] = $method->stmts;
        }
    }

    public function getFunction($name)
    {
        return $this->functions[$name] ?: $this->createFunction($name);
    }

    /**
     * @return PhamdaFunction[]
     */
    public function getFunctions()
    {
        foreach (array_keys($this->innerFunctions) as $name) {
            yield $name => $this->getFunction($name);
        }
    }

    public function count()
    {
        return count($this->innerFunctions);
    }

    private function createFunction($name)
    {
        $getFunction = function ($name) { return $this->getFunction($name); };

        $this->functions[$name] = $function = new PhamdaFunction(
            $name,
            $this->innerFunctions[$name],
            $getFunction,
            isset($this->exampleStatements[$name]) ? $this->exampleStatements[$name] : []
        );

        return $function;
    }
}
