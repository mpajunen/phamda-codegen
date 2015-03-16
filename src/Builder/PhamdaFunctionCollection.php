<?php

namespace Phamda\Builder;

use PhpParser\Node\Stmt\ClassMethod;

class PhamdaFunctionCollection implements \Countable
{
    private $closures;
    private $exampleStatements;
    private $functions;
    private $types;

    public function __construct(array $closureGroups, array $exampleFunctions)
    {
        foreach ($closureGroups as $group) {
            foreach ($group->value->items as $item) {
                $name = $item->key->value;

                $this->closures[$name]  = $item->value;
                $this->functions[$name] = null;
                $this->types[$name]     = $group->key->value;
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
            $this->closures[$name],
            $getFunction,
            isset($this->exampleStatements[$name]) ? $this->exampleStatements[$name] : []
        );

        return $function;
    }
}
