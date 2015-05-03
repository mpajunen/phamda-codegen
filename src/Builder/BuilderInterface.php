<?php

namespace Phamda\CodeGen\Builder;

use PhpParser\Builder;

interface BuilderInterface
{
    /**
     * @return Builder
     */
    public function build();
}
