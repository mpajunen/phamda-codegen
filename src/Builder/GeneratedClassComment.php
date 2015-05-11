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

class GeneratedClassComment
{
    public static function create($summary)
    {
        return <<<EOT
/**
 * $summary
 *
 * This class is automatically generated using the `\Phamda\Builder\InnerFunctions` class.
 *
 * For details about the code generation, please see: https://github.com/mpajunen/phamda-codegen
 */
EOT;
    }
}
