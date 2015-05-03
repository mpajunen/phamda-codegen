<?php

/*
 * This file is part of the Phamda Code Generator library
 *
 * (c) Mikael Pajunen <mikael.pajunen@gmail.com>
 *
 * For the full copyright and license information, please see the LICENSE
 * file that was distributed with this source code.
 */

if (! isset($argv[1])) {
    die("Output directory needed as the first parameter!\n");
}
if (! is_dir($argv[1])) {
    die(sprintf("'%s' is not a valid output directory!\n", $argv[1]));
}

require __DIR__ . '/vendor/autoload.php';
require $argv[1] . '/vendor/autoload.php';

$generator = new \Phamda\CodeGen\Generator();
$generator->generate($argv[1]);
