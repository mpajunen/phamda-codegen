#!/bin/sh
script_dir=$(dirname $0)
php $script_dir/generate.php $1
php-cs-fixer fix $1 --level=none --fixers=align_equals --fixers=visibility
