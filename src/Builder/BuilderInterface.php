<?php

/*
 * This file is part of the Phamda Code Generator library
 *
 * (c) Mikael Pajunen <mikael.pajunen@gmail.com>
 *
 * For the full copyright and license information, please see the LICENSE
 * file that was distributed with this source code.
 */

namespace Phamda\CodeGen\Builder;

use PhpParser\Builder;

interface BuilderInterface
{
    /**
     * @return Builder
     */
    public function build();
}
