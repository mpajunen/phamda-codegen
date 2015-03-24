<?php

namespace Phamda\Builder;

use PhpParser\Node\Stmt\ClassMethod;

class PhamdaFunctionCollection implements \Countable
{
    private $exampleStatements;
    private $functions;
    private $innerFunctions;
    private $types;

    public function __construct(array $methodGroups, array $exampleFunctions)
    {
        foreach ($methodGroups as $group => $methods) {
            foreach ($methods as $method) {
                if (! $method instanceof ClassMethod) {
                    continue;
                }
                $name = $method->name;

                $this->innerFunctions[$name] = $method;
                $this->functions[$name]      = null;
                $this->types[$name]          = $group;
            }
        }

        ksort($this->types, SORT_STRING | SORT_FLAG_CASE);

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
        foreach (array_keys($this->types) as $name) {
            yield $name => $this->getFunction($name);
        }
    }

    public function count()
    {
        return count($this->types);
    }

    private function createFunction($name)
    {
        $getFunction = function ($name) { return $this->getFunction($name); };

        $this->functions[$name] = $function = new PhamdaFunction(
            $name,
            $this->types[$name],
            $this->innerFunctions[$name],
            $getFunction,
            isset($this->exampleStatements[$name]) ? $this->exampleStatements[$name] : []
        );

        return $function;
    }
}
