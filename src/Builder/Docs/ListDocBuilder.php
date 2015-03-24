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
            . $this->getSection('getLink', $builders)
            . "\n"
            . $this->getSection('getSection', $builders);
    }

    private function getSection($method, array $builders)
    {
        $process = Phamda::pipe(
            Phamda::map(Phamda::unary(Phamda::invoker(0, $method))),
            Phamda::implode("\n")
        );

        return $process($builders);
    }

    private function getHeader($count)
    {
        return <<<EOT
# Phamda functions

Currently included functions ($count):


EOT;
    }
}
