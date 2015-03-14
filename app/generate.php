<?php

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../../phamda/vendor/autoload.php'; //! !!

if (! isset($argv[1])) {
    echo 'Output directory needed as the first parameter!';
}
if (! is_dir($argv[1])) {
    echo sprintf('"%s" is not a valid output directory.', $argv[1]);
}

$generator = new \Phamda\Generator\Generator();
$generator->generate($argv[1]);
