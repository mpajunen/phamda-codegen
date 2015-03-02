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

        'add'         =>
        /**
         * @param int|float $a
         * @param int|float $b
         *
         * @return int|float
         */
            function ($a, $b) {
                return $a + $b;
            },

        'all'         =>
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

        'any'         =>
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

        'both'        =>
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

        'clone_'      =>
        /**
         * @param object $object
         *
         * @return mixed
         */
            function ($object) {
                return clone $object;
            },

        'comparator'  =>
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

        'construct'   =>
        /**
         * @param string $class
         *
         * @return object
         */
            function ($class) {
                return Phamda::constructN(static::getConstructorArity($class), $class);
            },

        'constructN'  =>
        /**
         * @param int    $arity
         * @param string $class
         *
         * @return object
         */
            function ($arity, $class) {
                return static::_curryN($arity, function (...$arguments) use ($class) {
                    return new $class(...$arguments);
                });
            },

        'contains'    =>
        /**
         * @param mixed $value
         * @param array $list
         *
         * @return bool
         */
            function ($value, array $list) {
                return in_array($value, $list, true);
            },

        'curry'       =>
        /**
         * @param callable $function
         * @param mixed    ...$initialArguments
         *
         * @return callable
         */
            function (callable $function, ...$initialArguments) {
                return static::_curryN(static::getArity($function), $function, ...$initialArguments);
            },

        'curryN'      =>
        /**
         * @param int      $length
         * @param callable $function
         * @param mixed    ...$initialArguments
         *
         * @return callable
         */
            function ($length, callable $function, ...$initialArguments) {
                return static::_curryN($length, $function, ...$initialArguments);
            },

        'dec' =>
        /**
         * @param int|float $number
         *
         * @return int|float
         */
        function ($number) {
            return Phamda::add(-1, $number);
        },

        'defaultTo' =>
        /**
         * @param mixed $default
         * @param mixed $value
         *
         * @return mixed
         */
        function ($default, $value) {
            return $value !== null ? $value : $default;
        },

        'divide'      =>
        /**
         * @param int|float $a
         * @param int|float $b
         *
         * @return int|float
         */
            function ($a, $b) {
                return $a / $b;
            },

        'either'         =>
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

        'eq'          =>
        /**
         * @param mixed $a
         * @param mixed $b
         *
         * @return bool
         */
            function ($a, $b) {
                return $a === $b;
            },

        'filter'      =>
        /**
         * @param callable $function
         * @param array    $list
         *
         * @return array
         */
            function (callable $function, array $list) {
                $result = [];
                foreach ($list as $key => $value) {
                    if ($function($value, $key, $list)) {
                        $result[$key] = $value;
                    }
                }

                return $result;
            },

        'first'       =>
        /**
         * @param array $list
         *
         * @return mixed
         */
            function (array $list) {
                return reset($list);
            },

        'flip'        =>
        /**
         * @param callable $function
         *
         * @return callable
         */
            function (callable $function) {
                return function ($a, $b, ...$arguments) use ($function) {
                    return $function($b, $a, ...$arguments);
                };
            },

        'groupBy'     =>
        /**
         * @param callable $function
         * @param array    $list
         *
         * @return array[]
         */
            function (callable $function, array $list) {
                return Phamda::reduce(function (array $lists, $value) use ($function) {
                    $lists[$function($value)][] = $value;

                    return $lists;
                }, [], $list);
            },

        'gt'          =>
        /**
         * @param mixed $a
         * @param mixed $b
         *
         * @return bool
         */
            function ($a, $b) {
                return $a > $b;
            },

        'gte'         =>
        /**
         * @param mixed $a
         * @param mixed $b
         *
         * @return bool
         */
            function ($a, $b) {
                return $a >= $b;
            },

        'identity'    =>
        /**
         * @param mixed $a
         *
         * @return mixed
         */
            function ($a) {
                return $a;
            },

        'ifElse'      =>
        /**
         * @param callable $condition
         * @param callable $onTrue
         * @param callable $onFalse
         *
         * @return mixed
         */
            function (callable $condition, callable $onTrue, callable $onFalse) {
                return function (...$arguments) use ($condition, $onTrue, $onFalse) {
                    return $condition(...$arguments) ? $onTrue(...$arguments) : $onFalse(...$arguments);
                };
            },

        'inc' =>
        /**
         * @param int|float $number
         *
         * @return int|float
         */
            function ($number) {
                return Phamda::add(1, $number);
            },

        'indexOf'     =>
        /**
         * @param mixed $value
         * @param array $list
         *
         * @return int|string|false
         */
            function ($value, array $list) {
                foreach ($list as $key => $current) {
                    if ($value === $current) {
                        return $key;
                    }
                }

                return false;
            },

        'isEmpty'     =>
        /**
         * @param array $list
         *
         * @return bool
         */
            function (array $list) {
                return empty($list);
            },

        'isInstance'  =>
        /**
         * @param string $class
         * @param object $object
         *
         * @return bool
         */
            function ($class, $object) {
                return $object instanceof $class;
            },

        'last'        =>
        /**
         * @param array $list
         *
         * @return mixed
         */
            function (array $list) {
                return end($list);
            },

        'lt'          =>
        /**
         * @param mixed $a
         * @param mixed $b
         *
         * @return bool
         */
            function ($a, $b) {
                return $a < $b;
            },

        'lte'         =>
        /**
         * @param mixed $a
         * @param mixed $b
         *
         * @return bool
         */
            function ($a, $b) {
                return $a <= $b;
            },

        'map'         =>
        /**
         * @param callable $function
         * @param array    $list
         *
         * @return array
         */
            function (callable $function, array $list) {
                $result = [];
                foreach ($list as $key => $value) {
                    $result[$key] = $function($value, $key, $list);
                }

                return $result;
            },

        'max'         =>
        /**
         * @param array $list
         *
         * @return mixed
         */
            function (array $list) {
                return static::getCompareResult(Phamda::gt(), $list);
            },

        'maxBy'       =>
        /**
         * @param callable $getValue
         * @param array    $list
         *
         * @return mixed
         */
            function (callable $getValue, array $list) {
                return static::getCompareByResult(Phamda::gt(), $getValue, $list);
            },

        'min'         =>
        /**
         * @param array $list
         *
         * @return mixed
         */
            function (array $list) {
                return static::getCompareResult(Phamda::lt(), $list);
            },

        'minBy'       =>
        /**
         * @param callable $getValue
         * @param array    $list
         *
         * @return mixed
         */
            function (callable $getValue, array $list) {
                return static::getCompareByResult(Phamda::lt(), $getValue, $list);
            },

        'modulo'      =>
        /**
         * @param int $a
         * @param int $b
         *
         * @return int
         */
            function ($a, $b) {
                return $a % $b;
            },

        'multiply'    =>
        /**
         * @param int|float $a
         * @param int|float $b
         *
         * @return int|float
         */
            function ($a, $b) {
                return $a * $b;
            },

        'negate'      =>
        /**
         * @param int|float $a
         *
         * @return int|float
         */
            function ($a) {
                return Phamda::multiply($a, -1);
            },

        'none'        =>
        /**
         * @param callable $function
         * @param array    $list
         *
         * @return bool
         */
            function (callable $function, array $list) {
                return ! Phamda::any($function, $list);
            },

        'not'         =>
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

        'partition'   =>
        /**
         * @param callable $predicate
         * @param array    $list
         *
         * @return array[]
         */
            function (callable $predicate, array $list) {
                return Phamda::reduce(function (array $lists, $value) use ($predicate) {
                    $lists[$predicate($value) ? 0 : 1][] = $value;

                    return $lists;
                }, [[], []], $list);
            },

        'path'        =>
        /**
         * @param string       $path
         * @param array|object $object
         *
         * @return mixed
         */
            function ($path, $object) {
                return Phamda::pathOn('.', $path, $object);
            },

        'pathOn'      =>
        /**
         * @param string       $separator
         * @param string       $path
         * @param array|object $object
         *
         * @return mixed
         */
            function ($separator, $path, $object) {
                foreach (explode($separator, $path) as $name) {
                    $object = Phamda::prop($name, $object);
                }

                return $object;
            },

        'pick'        =>
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

        'pickAll'     =>
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

        'pluck'       =>
        /**
         * @param string $name
         * @param array  $list
         *
         * @return mixed
         */
            function ($name, array $list) {
                return Phamda::map(Phamda::prop($name), $list);
            },

        'product'     =>
        /**
         * @param int[]|float[] $values
         *
         * @return int|float
         */
            function (array $values) {
                return Phamda::reduce(Phamda::multiply(), 1, $values);
            },

        'prop'        =>
        /**
         * @param string       $name
         * @param array|object $object
         *
         * @return mixed
         */
            function ($name, $object) {
                return is_object($object) ? $object->$name : $object[$name];
            },

        'propEq'      =>
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

        'reduce'      =>
        /**
         * @param callable $function
         * @param mixed    $initial
         * @param array    $list
         *
         * @return mixed
         */
            function (callable $function, $initial, array $list) {
                foreach ($list as $key => $value) {
                    $initial = $function($initial, $value, $key, $list);
                }

                return $initial;
            },

        'reduceRight' =>
        /**
         * @param callable $function
         * @param mixed    $initial
         * @param array    $list
         *
         * @return mixed
         */
            function (callable $function, $initial, array $list) {
                return Phamda::reduce($function, $initial, array_reverse($list));
            },

        'reject'      =>
        /**
         * @param callable $function
         * @param array    $list
         *
         * @return array
         */
            function (callable $function, array $list) {
                return Phamda::filter(Phamda::not($function), $list);
            },

        'reverse'     =>
        /**
         * @param array $list
         *
         * @return array
         */
            function (array $list) {
                return array_reverse($list);
            },

        'slice'       =>
        /**
         * @param int   $start
         * @param int   $end
         * @param array $list
         *
         * @return array
         */
            function ($start, $end, array $list) {
                return array_slice($list, $start, $end - $start);
            },

        'sort'        =>
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

        'sortBy'      =>
        /**
         * @param callable $function
         * @param array    $list
         *
         * @return array
         */
            function (callable $function, array $list) {
                $comparator = function ($a, $b) use ($function) {
                    $aKey = $function($a);
                    $bKey = $function($b);

                    return $aKey < $bKey ? -1 : ($aKey > $bKey ? 1 : 0);
                };

                usort($list, $comparator);

                return $list;
            },

        'subtract'    =>
        /**
         * @param int|float $a
         * @param int|float $b
         *
         * @return int|float
         */
            function ($a, $b) {
                return $a - $b;
            },

        'sum'         =>
        /**
         * @param int[]|float[] $values
         *
         * @return int|float
         */
            function (array $values) {
                return Phamda::reduce(Phamda::add(), 0, $values);
            },

        'zip'         =>
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

        'zipWith'     =>
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

        'where'       =>
        /**
         * @param array        $specification
         * @param array|object $object
         *
         * @return mixed
         */
            function (array $specification, $object) {
                foreach ($specification as $name => $part) {
                    if (! static::testSpecificationPart($name, $part, $object)) {
                        return false;
                    }
                }

                return true;
            },
    ],
    'simple'  => [

        'compose'  =>
        /**
         * @param callable ...$functions
         *
         * @return callable
         */
            function (... $functions) {
                return Phamda::pipe(... array_reverse($functions));
            },

        'false'    =>
        /**
         * @return callable
         */
            function () {
                return function () {
                    return false;
                };
            },

        'invoker'  =>
        /**
         * @param int    $arity
         * @param string $method
         * @param mixed  ...$initialArguments
         *
         * @return callable
         */
            function ($arity, $method, ... $initialArguments) {
                $remainingCount = $arity - count($initialArguments) + 1;

                return static::_curryN($remainingCount, function (... $arguments) use ($method, $initialArguments) {
                    $object = array_pop($arguments);

                    return $object->$method(...array_merge($initialArguments, $arguments));
                });
            },

        'partial'  =>
        /**
         * @param callable $function
         * @param mixed    ...$initialArguments
         *
         * @return callable
         */
            function (callable $function, ... $initialArguments) {
                return Phamda::partialN(static::getArity($function), $function, ...$initialArguments);
            },

        'partialN' =>
        /**
         * @param int      $arity
         * @param callable $function
         * @param mixed    ...$initialArguments
         *
         * @return callable
         */
            function ($arity, callable $function, ... $initialArguments) {
                $partial        = function (... $arguments) use ($function, $initialArguments) {
                    return $function(...array_merge($initialArguments, $arguments));
                };
                $remainingCount = $arity - count($initialArguments);

                return $remainingCount > 0 ? static::_curryN($remainingCount, $partial) : $partial;
            },

        'pipe'     =>
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

        'true'     =>
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
