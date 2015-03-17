#!/bin/bash
script_dir=$(dirname $0)
cd $script_dir
php app/generate.php ../phamda
cd ../phamda
php-cs-fixer fix .
