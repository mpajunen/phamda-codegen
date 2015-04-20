<?php

namespace Phamda\Builder\Docs;

use Phamda\Builder\PhamdaFunctionCollection;
use Phamda\Phamda;

class ListDocBuilder
{
    private $functions;

    public function __construct(PhamdaFunctionCollection $functions)
    {
        $this->functions = $functions;
    }

    public function build()
    {
        return $this->getHeader(count($this->functions))
            . $this->getPart('getSection');
    }

    private function getPart($method)
    {
        $process = Phamda::pipe(
            Phamda::map([ListDocFunctionBuilder::class, $method]),
            Phamda::implode("\n")
        );

        return $process($this->functions->getFunctions());
    }

    private function getHeader($count)
    {
        return <<<EOT
Phamda functions
================

Currently included functions ($count):


EOT;
    }
}
