<?php

namespace Phamda\Functions;

use Phamda\Phamda;

$variables = [
    $value = null,
];

$functions = [
    'curried' => [

        'add'           =>
        /**
         * @param int|float $x
         * @param int|float $y
         *
         * @return int|float
         */
            function ($x, $y) {
                return $x + $y;
            },

        'all'           =>
        /**
         * @param callable           $predicate
         * @param array|\Traversable $collection
         *
         * @return bool
         */
            function (callable $predicate, $collection) {
                foreach ($collection as $item) {
                    if (! $predicate($item)) {
                        return false;
                    }
                }

                return true;
            },

        'allPass'       =>
        /**
         * @param callable[] $predicates
         *
         * @return callable
         */
            function (array $predicates) {
                return function (...$arguments) use ($predicates) {
                    foreach ($predicates as $predicate) {
                        if (! $predicate(...$arguments)) {
                            return false;
                        }
                    }

                    return true;
                };
            },

        'any'           =>
        /**
         * @param callable           $predicate
         * @param array|\Traversable $collection
         *
         * @return bool
         */
            function (callable $predicate, $collection) {
                foreach ($collection as $item) {
                    if ($predicate($item)) {
                        return true;
                    }
                }

                return false;
            },

        'anyPass'       =>
        /**
         * @param callable[] $predicates
         *
         * @return callable
         */
            function (array $predicates) {
                return function (...$arguments) use ($predicates) {
                    foreach ($predicates as $predicate) {
                        if ($predicate(...$arguments)) {
                            return true;
                        }
                    }

                    return false;
                };
            },

        'assoc'         =>
        /**
         * @param string       $property
         * @param mixed        $value
         * @param array|object $object
         *
         * @return array|object
         */
            function ($property, $value, $object) {
                return static::_assoc($property, $value, $object);
            },

        'assocPath'     =>
        /**
         * @param array        $path
         * @param mixed        $value
         * @param array|object $object
         *
         * @return array|object
         */
            function (array $path, $value, $object) {
                return static::_assocPath($path, $value, $object);
            },

        'both'          =>
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

        'clone_'        =>
        /**
         * @param object $object
         *
         * @return mixed
         */
            function ($object) {
                return clone $object;
            },

        'comparator'    =>
        /**
         * @param callable $predicate
         *
         * @return callable
         */
            function (callable $predicate) {
                return function ($x, $y) use ($predicate) {
                    return $predicate($x, $y) ? -1 : ($predicate($y, $x) ? 1 : 0);
                };
            },

        'construct'     =>
        /**
         * @param string $class
         *
         * @return object
         */
            function ($class) {
                return Phamda::constructN(static::getConstructorArity($class), $class);
            },

        'constructN'    =>
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

        'contains'      =>
        /**
         * @param mixed              $value
         * @param array|\Traversable $collection
         *
         * @return bool
         */
            function ($value, $collection) {
                foreach ($collection as $item) {
                    if ($item === $value) {
                        return true;
                    }
                }

                return false;
            },

        'curry'         =>
        /**
         * @param callable $function
         * @param mixed    ...$initialArguments
         *
         * @return callable
         */
            function (callable $function, ...$initialArguments) {
                return static::_curryN(static::getArity($function), $function, ...$initialArguments);
            },

        'curryN'        =>
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

        'dec'           =>
        /**
         * @param int|float $number
         *
         * @return int|float
         */
            function ($number) {
                return Phamda::add(-1, $number);
            },

        'defaultTo'     =>
        /**
         * @param mixed $default
         * @param mixed $value
         *
         * @return mixed
         */
            function ($default, $value) {
                return $value !== null ? $value : $default;
            },

        'divide'        =>
        /**
         * @param int|float $x
         * @param int|float $y
         *
         * @return int|float
         */
            function ($x, $y) {
                return $x / $y;
            },

        'either'        =>
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

        'eq'            =>
        /**
         * @param mixed $x
         * @param mixed $y
         *
         * @return bool
         */
            function ($x, $y) {
                return $x === $y;
            },

        'filter'        =>
        /**
         * @param callable $predicate
         * @param array    $collection
         *
         * @return array
         */
            function (callable $predicate, array $collection) {
                $result = [];
                foreach ($collection as $key => $item) {
                    if ($predicate($item, $key, $collection)) {
                        $result[$key] = $item;
                    }
                }

                return $result;
            },

        'find'          =>
        /**
         * @param callable           $predicate
         * @param array|\Traversable $collection
         *
         * @return mixed|null
         */
            function (callable $predicate, $collection) {
                foreach ($collection as $item) {
                    if ($predicate($item)) {
                        return $item;
                    }
                }

                return null;
            },

        'findIndex'     =>
        /**
         * @param callable           $predicate
         * @param array|\Traversable $collection
         *
         * @return int|string|null
         */
            function (callable $predicate, $collection) {
                foreach ($collection as $index => $item) {
                    if ($predicate($item)) {
                        return $index;
                    }
                }

                return null;
            },

        'findLast'      =>
        /**
         * @param callable           $predicate
         * @param array|\Traversable $collection
         *
         * @return mixed|null
         */
            function (callable $predicate, $collection) {
                foreach (static::_reverse($collection) as $item) {
                    if ($predicate($item)) {
                        return $item;
                    }
                }

                return null;
            },

        'findLastIndex' =>
        /**
         * @param callable           $predicate
         * @param array|\Traversable $collection
         *
         * @return int|string|null
         */
            function (callable $predicate, $collection) {
                foreach (static::_reverse($collection, true) as $index => $item) {
                    if ($predicate($item)) {
                        return $index;
                    }
                }

                return null;
            },

        'first'         =>
        /**
         * @param array $collection
         *
         * @return mixed
         */
            function (array $collection) {
                return reset($collection);
            },

        'flip'          =>
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

        'groupBy'       =>
        /**
         * @param callable $function
         * @param array    $collection
         *
         * @return array[]
         */
            function (callable $function, array $collection) {
                return static::_reduce(function (array $collections, $item) use ($function) {
                    $collections[$function($item)][] = $item;

                    return $collections;
                }, [], $collection);
            },

        'gt'            =>
        /**
         * @param mixed $x
         * @param mixed $y
         *
         * @return bool
         */
            function ($x, $y) {
                return $x > $y;
            },

        'gte'           =>
        /**
         * @param mixed $x
         * @param mixed $y
         *
         * @return bool
         */
            function ($x, $y) {
                return $x >= $y;
            },

        'identity'      =>
        /**
         * @param mixed $x
         *
         * @return mixed
         */
            function ($x) {
                return $x;
            },

        'ifElse'        =>
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

        'inc'           =>
        /**
         * @param int|float $number
         *
         * @return int|float
         */
            function ($number) {
                return Phamda::add(1, $number);
            },

        'indexOf'       =>
        /**
         * @param mixed              $item
         * @param array|\Traversable $collection
         *
         * @return int|string|false
         */
            function ($item, array $collection) {
                foreach ($collection as $key => $current) {
                    if ($item === $current) {
                        return $key;
                    }
                }

                return false;
            },

        'isEmpty'       =>
        /**
         * @param array $collection
         *
         * @return bool
         */
            function (array $collection) {
                return empty($collection);
            },

        'isInstance'    =>
        /**
         * @param string $class
         * @param object $object
         *
         * @return bool
         */
            function ($class, $object) {
                return $object instanceof $class;
            },

        'last'          =>
        /**
         * @param array $collection
         *
         * @return mixed
         */
            function (array $collection) {
                return end($collection);
            },

        'lt'            =>
        /**
         * @param mixed $x
         * @param mixed $y
         *
         * @return bool
         */
            function ($x, $y) {
                return $x < $y;
            },

        'lte'           =>
        /**
         * @param mixed $x
         * @param mixed $y
         *
         * @return bool
         */
            function ($x, $y) {
                return $x <= $y;
            },

        'map'           =>
        /**
         * @param callable $function
         * @param array    $collection
         *
         * @return array
         */
            function (callable $function, array $collection) {
                $result = [];
                foreach ($collection as $key => $item) {
                    $result[$key] = $function($item, $key, $collection);
                }

                return $result;
            },

        'max'           =>
        /**
         * @param array|\Traversable $collection
         *
         * @return mixed
         */
            function ($collection) {
                return static::getCompareResult(Phamda::gt(), $collection);
            },

        'maxBy'         =>
        /**
         * @param callable           $getValue
         * @param array|\Traversable $collection
         *
         * @return mixed
         */
            function (callable $getValue, $collection) {
                return static::getCompareByResult(Phamda::gt(), $getValue, $collection);
            },

        'min'           =>
        /**
         * @param array|\Traversable $collection
         *
         * @return mixed
         */
            function ($collection) {
                return static::getCompareResult(Phamda::lt(), $collection);
            },

        'minBy'         =>
        /**
         * @param callable           $getValue
         * @param array|\Traversable $collection
         *
         * @return mixed
         */
            function (callable $getValue, $collection) {
                return static::getCompareByResult(Phamda::lt(), $getValue, $collection);
            },

        'modulo'        =>
        /**
         * @param int $x
         * @param int $y
         *
         * @return int
         */
            function ($x, $y) {
                return $x % $y;
            },

        'multiply'      =>
        /**
         * @param int|float $x
         * @param int|float $y
         *
         * @return int|float
         */
            function ($x, $y) {
                return $x * $y;
            },

        'negate'        =>
        /**
         * @param int|float $x
         *
         * @return int|float
         */
            function ($x) {
                return Phamda::multiply($x, -1);
            },

        'none'          =>
        /**
         * @param callable           $predicate
         * @param array|\Traversable $collection
         *
         * @return bool
         */
            function (callable $predicate, $collection) {
                return ! Phamda::any($predicate, $collection);
            },

        'not'           =>
        /**
         * @param callable $predicate
         *
         * @return callable
         */
            function (callable $predicate) {
                return function (... $arguments) use ($predicate) {
                    return ! $predicate(...$arguments);
                };
            },

        'partition'     =>
        /**
         * @param callable $predicate
         * @param array    $collection
         *
         * @return array[]
         */
            function (callable $predicate, array $collection) {
                return static::_reduce(function (array $collections, $item) use ($predicate) {
                    $collections[$predicate($item) ? 0 : 1][] = $item;

                    return $collections;
                }, [[], []], $collection);
            },

        'path'          =>
        /**
         * @param array        $path
         * @param array|object $object
         *
         * @return mixed
         */
            function (array $path, $object) {
                foreach ($path as $name) {
                    $object = Phamda::prop($name, $object);
                }

                return $object;
            },

        'pathEq'        =>
        /**
         * @param array        $path
         * @param mixed        $value
         * @param array|object $object
         *
         * @return boolean
         */
            function (array $path, $value, $object) {
                return Phamda::path($path, $object) === $value;
            },

        'pick'          =>
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

        'pickAll'       =>
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

        'pluck'         =>
        /**
         * @param string $name
         * @param array  $collection
         *
         * @return mixed
         */
            function ($name, array $collection) {
                return Phamda::map(Phamda::prop($name), $collection);
            },

        'product'       =>
        /**
         * @param int[]|float[] $values
         *
         * @return int|float
         */
            function (array $values) {
                return static::_reduce(Phamda::multiply(), 1, $values);
            },

        'prop'          =>
        /**
         * @param string       $name
         * @param array|object $object
         *
         * @return mixed
         */
            function ($name, $object) {
                return is_object($object) ? $object->$name : $object[$name];
            },

        'propEq'        =>
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

        'reduce'        =>
        /**
         * @param callable           $function
         * @param mixed              $initial
         * @param array|\Traversable $collection
         *
         * @return mixed
         */
            function (callable $function, $initial, $collection) {
                return static::_reduce($function, $initial, $collection);
            },

        'reduceRight'   =>
        /**
         * @param callable           $function
         * @param mixed              $initial
         * @param array|\Traversable $collection
         *
         * @return mixed
         */
            function (callable $function, $initial, $collection) {
                return static::_reduce($function, $initial, static::_reverse($collection));
            },

        'reject'        =>
        /**
         * @param callable $predicate
         * @param array    $collection
         *
         * @return array
         */
            function (callable $predicate, array $collection) {
                return Phamda::filter(Phamda::not($predicate), $collection);
            },

        'reverse'       =>
        /**
         * @param array|\Traversable $collection
         *
         * @return array
         */
            function ($collection) {
                return static::_reverse($collection);
            },

        'slice'         =>
        /**
         * @param int   $start
         * @param int   $end
         * @param array $collection
         *
         * @return array
         */
            function ($start, $end, array $collection) {
                return array_slice($collection, $start, $end - $start);
            },

        'sort'          =>
        /**
         * @param callable $comparator
         * @param array    $collection
         *
         * @return array
         */
            function (callable $comparator, array $collection) {
                usort($collection, $comparator);

                return $collection;
            },

        'sortBy'        =>
        /**
         * @param callable $function
         * @param array    $collection
         *
         * @return array
         */
            function (callable $function, array $collection) {
                $comparator = function ($x, $y) use ($function) {
                    $xKey = $function($x);
                    $yKey = $function($y);

                    return $xKey < $yKey ? -1 : ($xKey > $yKey ? 1 : 0);
                };

                usort($collection, $comparator);

                return $collection;
            },

        'subtract'      =>
        /**
         * @param int|float $x
         * @param int|float $y
         *
         * @return int|float
         */
            function ($x, $y) {
                return $x - $y;
            },

        'sum'           =>
        /**
         * @param int[]|float[] $values
         *
         * @return int|float
         */
            function (array $values) {
                return static::_reduce(Phamda::add(), 0, $values);
            },

        'tap'           =>
        /**
         * @param callable $function
         * @param object   $object
         *
         * @return object
         */
            function (callable $function, $object) {
                $function($object);

                return $object;
            },

        'times'         =>
        /**
         * @param callable $function
         * @param int      $count
         *
         * @return array
         */
            function (callable $function, $count) {
                return Phamda::map($function, range(0, $count - 1));
            },

        'zip'           =>
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

        'zipWith'       =>
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

        'where'         =>
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
