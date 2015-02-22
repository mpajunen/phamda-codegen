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

        'add'        =>
        /**
         * @param int|float $a
         * @param int|float $b
         *
         * @return int|float
         */
            function ($a, $b) {
                return $a + $b;
            },

        'all'        =>
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

        'and_'       =>
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

        'any'        =>
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

        'curry'      =>
        /**
         * @param callable $function
         *
         * @return callable
         */
            function (callable $function) {
                $reflection = static::createReflection($function);

                return Phamda::curryN($reflection->getNumberOfParameters(), $function);
            },

        'curryN'     =>
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

        'divide'     =>
        /**
         * @param int|float $a
         * @param int|float $b
         *
         * @return int|float
         */
            function ($a, $b) {
                return $a / $b;
            },

        'eq'         =>
        /**
         * @param mixed $a
         * @param mixed $b
         *
         * @return bool
         */
            function ($a, $b) {
                return $a === $b;
            },

        'comparator' =>
        /**
         * @param callable $predicate
         *
         * @return callable
         */
            function (callable $predicate) {
                return function ($a, $b) use ($predicate) {
                    return $predicate($a, $b) ? -1 : ($predicate($b, $a) ? 1 : 0);
                };
            },

        'filter'     =>
        /**
         * @param callable $function
         * @param array    $list
         *
         * @return array
         */
            function (callable $function, array $list) {
                return array_filter($list, $function);
            },

        'gt'         =>
        /**
         * @param mixed $a
         * @param mixed $b
         *
         * @return bool
         */
            function ($a, $b) {
                return $a > $b;
            },

        'gte'        =>
        /**
         * @param mixed $a
         * @param mixed $b
         *
         * @return bool
         */
            function ($a, $b) {
                return $a >= $b;
            },

        'identity'   =>
        /**
         * @param mixed $a
         *
         * @return mixed
         */
            function ($a) {
                return $a;
            },

        'lt'         =>
        /**
         * @param mixed $a
         * @param mixed $b
         *
         * @return bool
         */
            function ($a, $b) {
                return $a < $b;
            },

        'lte'        =>
        /**
         * @param mixed $a
         * @param mixed $b
         *
         * @return bool
         */
            function ($a, $b) {
                return $a <= $b;
            },

        'map'        =>
        /**
         * @param callable $function
         * @param array    $list
         *
         * @return array
         */
            function (callable $function, array $list) {
                return array_map($function, $list);
            },

        'max' =>
        /**
         * @param array $list
         *
         * @return mixed
         */
            function (array $list) {
                return static::getCompareResult(Phamda::gt(), $list);
            },

        'maxBy' =>
        /**
         * @param callable $getValue
         * @param array    $list
         *
         * @return mixed
         */
            function (callable $getValue, array $list) {
                return static::getCompareByResult(Phamda::gt(), $getValue, $list);
            },

        'min' =>
        /**
         * @param array $list
         *
         * @return mixed
         */
            function (array $list) {
                return static::getCompareResult(Phamda::lt(), $list);
            },

        'minBy' =>
        /**
         * @param callable $getValue
         * @param array    $list
         *
         * @return mixed
         */
            function (callable $getValue, array $list) {
                return static::getCompareByResult(Phamda::lt(), $getValue, $list);
            },

        'modulo'     =>
        /**
         * @param int $a
         * @param int $b
         *
         * @return int
         */
            function ($a, $b) {
                return $a % $b;
            },

        'multiply'   =>
        /**
         * @param int|float $a
         * @param int|float $b
         *
         * @return int|float
         */
            function ($a, $b) {
                return $a * $b;
            },

        'negate'     =>
        /**
         * @param int|float $a
         *
         * @return int|float
         */
            function ($a) {
                return Phamda::multiply($a, -1);
            },

        'not'        =>
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

        'or_'        =>
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

        'pick'       =>
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

        'pickAll'    =>
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

        'pluck'      =>
        /**
         * @param string $name
         * @param array  $list
         *
         * @return mixed
         */
            function ($name, array $list) {
                return Phamda::map(Phamda::prop($name), $list);
            },

        'product'    =>
        /**
         * @param int[]|float[] $values
         *
         * @return int|float
         */
            function (array $values) {
                return Phamda::reduce(Phamda::multiply(), 1, $values);
            },

        'prop'       =>
        /**
         * @param string       $name
         * @param array|object $object
         *
         * @return mixed
         */
            function ($name, $object) {
                return is_object($object) ? $object->$name : $object[$name];
            },

        'propEq'     =>
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

        'reduce'     =>
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

        'sort'       =>
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

        'subtract'   =>
        /**
         * @param int|float $a
         * @param int|float $b
         *
         * @return int|float
         */
            function ($a, $b) {
                return $a - $b;
            },

        'sum'        =>
        /**
         * @param int[]|float[] $values
         *
         * @return int|float
         */
            function (array $values) {
                return Phamda::reduce(Phamda::add(), 0, $values);
            },

        'zip'        =>
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

        'zipWith'    =>
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

        'compose' =>
        /**
         * @param callable ...$functions
         *
         * @return callable
         */
            function (... $functions) {
                return Phamda::pipe(... array_reverse($functions));
            },

        'false'   =>
        /**
         * @return callable
         */
            function () {
                return function () {
                    return false;
                };
            },

        'pipe'    =>
        /**
         * @param callable ...$functions
         *
         * @return callable
         */
            function (... $functions) {
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

        'true'    =>
        /**
         * @return callable
         */
            function () {
                return function () {
                    return true;
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
