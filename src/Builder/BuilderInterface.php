<?php

namespace Phamda\Builder;

use PhpParser\Builder;

interface BuilderInterface
{
    /**
     * @return Builder
     */
    public function build();
}
