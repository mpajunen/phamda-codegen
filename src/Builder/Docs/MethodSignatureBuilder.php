<?php

namespace Phamda\Builder\Docs;

use Phamda\Builder\AbstractMethodBuilder;
use Phamda\Printer\PhamdaPrinter;

class MethodSignatureBuilder extends AbstractMethodBuilder
{
    public function getSignature()
    {
        $method = (new PhamdaPrinter())->prettyPrint([$this->build()->getNode()]);

        return str_replace('public function ', 'Phamda::', trim(explode("\n", $method)[0]));
    }

    protected function createComment()
    {
        return '';
    }
}
