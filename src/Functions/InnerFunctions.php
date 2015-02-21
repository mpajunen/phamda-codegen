<?php

namespace Phamda\Functions;

use Phamda\Phamda;

$variables = [
    $value = null,
    $a = function () {
    },
    $b = function () {
    },
    $function = function () {
    },
];

$functions = [
    'curried' => [

        'all'     =>
        /**
         * @param callable $function
         * @param array    $list
         *
         * @return bool
         */
            function (callable $function, array $list) {
                foreach ($list as $value) {
                    if (! $function($value)) {
                        return false;
                    }
                }

                return true;
            },

        'and_'    =>
        /**
         * @param callable $a
         * @param callable $b
         *
         * @return callable
         */
            function (callable $a, callable $b) {
                return function (...$arguments) use ($a, $b) {
                    return $a(...$arguments) && $b(...$arguments);
                };
            },

        'any'     =>
        /**
         * @param callable $function
         * @param array    $list
         *
         * @return bool
         */
            function (callable $function, array $list) {
                foreach ($list as $value) {
                    if ($function($value)) {
                        return true;
                    }
                }

                return false;
            },

        'curry' =>
        /**
         * @param callable $function
         *
         * @return callable
         */
            function (callable $function) {
                $reflection = static::createReflection($function);
                return Phamda::curryN($reflection->getNumberOfParameters(), $function);
            },

        'curryN'  =>
        /**
         * @param int      $count
         * @param callable $function
         *
         * @return callable
         */
            function ($count, callable $function) {
                return function (... $arguments) use ($function, $count) {
                    $remainingCount = $count - count($arguments);

                    if ($remainingCount <= 0) {
                        return $function(... $arguments);
                    } else {
                        $existingArguments = $arguments;

                        return Phamda::curryN($remainingCount, function (... $arguments) use ($function, $existingArguments) {
                            return $function(... array_merge($existingArguments, $arguments));
                        });
                    }
                };
            },

        'eq'      =>
        /**
         * @param mixed $a
         * @param mixed $b
         *
         * @return bool
         */
            function ($a, $b) {
                return $a === $b;
            },

        'filter'  =>
        /**
         * @param callable $function
         * @param array    $list
         *
         * @return array
         */
            function (callable $function, array $list) {
                return array_filter($list, $function);
            },

        'identity' =>
        /**
         * @param mixed $a
         *
         * @return mixed
         */
            function ($a) {
                return $a;
            },

        'map'     =>
        /**
         * @param callable $function
         * @param array    $list
         *
         * @return array
         */
            function (callable $function, array $list) {
                return array_map($function, $list);
            },

        'not'    =>
        /**
         * @param callable $function
         *
         * @return callable
         */
            function (callable $function) {
                return function (... $arguments) use ($function) {
                    return ! $function(...$arguments);
                };
            },

        'or_'     =>
        /**
         * @param callable $a
         * @param callable $b
         *
         * @return callable
         */
            function (callable $a, callable $b) {
                return function (...$arguments) use ($a, $b) {
                    return $a(...$arguments) || $b(...$arguments);
                };
            },

        'pick'    =>
        /**
         * @param array $names
         * @param array $item
         *
         * @return array
         */
            function (array $names, array $item) {
                $new = [];
                foreach ($names as $name) {
                    if (array_key_exists($name, $item)) {
                        $new[$name] = $item[$name];
                    }
                }

                return $new;
            },

        'pickAll' =>
        /**
         * @param array $names
         * @param array $item
         *
         * @return array
         */
            function (array $names, array $item) {
                $new = [];
                foreach ($names as $name) {
                    $new[$name] = isset($item[$name]) ? $item[$name] : null;
                }

                return $new;
            },

        'prop'    =>
        /**
         * @param string       $name
         * @param array|object $object
         *
         * @return mixed
         */
            function ($name, $object) {
                return is_object($object) ? $object->$name : $object[$name];
            },

        'propEq'  =>
        /**
         * @param string       $name
         * @param mixed        $value
         * @param array|object $object
         *
         * @return bool
         */
            function ($name, $value, $object) {
                return is_object($object)
                    ? $object->$name === $value
                    : $object[$name] === $value;
            },

        'reduce'  =>
        /**
         * @param callable $function
         * @param mixed    $initial
         * @param array    $list
         *
         * @return mixed
         */
            function (callable $function, $initial, array $list) {
                return array_reduce($list, $function, $initial);
            },

        'sort'    =>
        /**
         * @param callable $comparator
         * @param array    $list
         *
         * @return array
         */
            function (callable $comparator, array $list) {
                usort($list, $comparator);

                return $list;
            },

        'zip'     =>
        /**
         * @param array $a
         * @param array $b
         *
         * @return array
         */
            function (array $a, array $b) {
                $zipped = [];
                foreach (array_intersect_key($a, $b) as $key => $value) {
                    $zipped[$key] = [$value, $b[$key]];
                }

                return $zipped;
            },

        'zipWith' =>
        /**
         * @param callable $function
         * @param array    $a
         * @param array    $b
         *
         * @return array
         */
            function (callable $function, array $a, array $b) {
                $zipped = [];
                foreach (array_intersect_key($a, $b) as $key => $value) {
                    $zipped[$key] = $function($value, $b[$key]);
                }

                return $zipped;
            },
    ],
    'simple'  => [

        'compose'  =>
        /**
         * @param callable ...$functions
         *
         * @return callable
         */
            function (callable ... $functions) {
                return Phamda::pipe(... array_reverse($functions));
            },

        'pipe'     =>
        /**
         * @param callable ...$functions
         *
         * @return callable
         */
            function (callable ... $functions) {
                if (count($functions) < 2) {
                    throw new \LogicException('Pipe requires at least two argument functions.');
                }

                return function (... $arguments) use ($functions) {
                    $result = null;
                    foreach ($functions as $function) {
                        $result = $result !== null
                            ? $function($result)
                            : $function(...$arguments);
                    }

                    return $result;
                };
            },
    ],
    'wrapped' => [

        'always' =>
        /**
         * @param mixed $value
         *
         * @return callable
         */
            function () use ($value) {
                return $value;
            },
    ],
];
