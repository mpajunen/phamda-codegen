<?php

namespace Phamda\Builder;

use PhpParser\Node\Stmt\ClassMethod;

class PhamdaFunctionCollection implements \Countable
{
    private $exampleStatements;
    private $functions;
    private $innerFunctions;

    public function __construct(array $methods, array $exampleFunctions)
    {
        foreach ($methods as $method) {
            if (! $method instanceof ClassMethod) {
                continue;
            }
            $name = $method->name;

            $this->innerFunctions[$name] = $method;
            $this->functions[$name]      = null;
        }

        foreach ($exampleFunctions as $function) {
            if ($function instanceof ClassMethod) {
                $this->exampleStatements[lcfirst(substr($function->name, strlen('test')))] = $function->stmts;
            }
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
