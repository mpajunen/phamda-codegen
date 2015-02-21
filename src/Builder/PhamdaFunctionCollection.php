<?php

namespace Phamda\Builder;

class PhamdaFunctionCollection
{
    private $closures;
    private $functions;
    private $types;

    public function __construct(array $closureGroups)
    {
        foreach ($closureGroups as $group) {
            foreach ($group->value->items as $item) {
                $name = $item->key->value;

                $this->closures[$name]  = $item->value;
                $this->functions[$name] = null;
                $this->types[$name]     = $group->key->value;
            }
        }

        ksort($this->types);
    }

    public function getFunction($name)
    {
        return $this->functions[$name] ?: $this->createFunction($name);
    }

    public function getFunctions()
    {
        foreach (array_keys($this->types) as $name) {
            yield $name => $this->getFunction($name);
        }
    }

    private function createFunction($name)
    {
        $getFunction = function ($name) { return $this->getFunction($name); };

        $this->functions[$name] = $function = new PhamdaFunction($name, $this->types[$name], $this->closures[$name], $getFunction);

        return $function;
    }
}
