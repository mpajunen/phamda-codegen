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
        $builders = Phamda::map(Phamda::construct(ListDocFunctionBuilder::class), $this->functions->getFunctions());

        return $this->getHeader(count($this->functions))
            . implode("\n", Phamda::map(function (ListDocFunctionBuilder $builder) { return $builder->getLink(); }, $builders))
            . "\n"
            . implode("\n", Phamda::map(function (ListDocFunctionBuilder $builder) { return $builder->getSection(); }, $builders))
        ;
    }

    private function getHeader($count)
    {
        return <<<EOT
# Phamda functions

Currently included functions ($count):


EOT;
    }
}
