<?php

namespace Phamda\Functions;

use Phamda\Phamda;
use Phamda\Collection\Collection;
use Phamda\Exception\InvalidFunctionCompositionException;
use Phamda\Placeholder;

$functions = [
    'curried' => [

        'add'               =>
        /**
         * Adds two numbers.
         *
         * @param int|float $x
         * @param int|float $y
         *
         * @return int|float
         */
            function ($x, $y) {
                return $x + $y;
            },

        'all'               =>
        /**
         * Returns `true` if all elements of the collection match the predicate, `false` otherwise.
         *
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

        'allPass'           =>
        /**
         * Creates a single predicate from a list of predicates that returns `true` when all the predicates match, `false` otherwise.
         *
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

        'always'            =>
        /**
         * Returns a function that always returns the passed value.
         *
         * @param mixed $value
         *
         * @return callable
         */
            function ($value) {
                return function () use ($value) {
                    return $value;
                };
            },

        'any'               =>
        /**
         * Returns `true` if any element of the collection matches the predicate, `false` otherwise.
         *
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

        'anyPass'           =>
        /**
         * Creates a single predicate from a list of predicates that returns `true` when any of the predicates matches, `false` otherwise.
         *
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

        'append'            =>
        /**
         * Return a new collection that contains all the items in the given collection and the given item last.
         *
         * @param mixed            $item
         * @param array|Collection $collection
         *
         * @return array|Collection
         */
            function ($item, $collection) {
                if (is_array($collection)) {
                    $collection[] = $item;

                    return $collection;
                } elseif (method_exists($collection, 'append')) {
                    return $collection->append($item);
                } else {
                    foreach ($collection as $collectionItem) {
                        $items[] = $collectionItem;
                    }
                    $items[] = $item;

                    return $items;
                }
            },

        'assoc'             =>
        /**
         * Returns a new array or object, setting the given value to the specified property.
         *
         * @param string       $property
         * @param mixed        $value
         * @param array|object $object
         *
         * @return array|object
         */
            function ($property, $value, $object) {
                return static::_assoc($property, $value, $object);
            },

        'assocPath'         =>
        /**
         * Returns a new array or object, setting the given value to the property specified by the path.
         *
         * @param array        $path
         * @param mixed        $value
         * @param array|object $object
         *
         * @return array|object
         */
            function (array $path, $value, $object) {
                return static::_assocPath($path, $value, $object);
            },

        'binary'            =>
        /**
         * Wraps the given function in a function that accepts exactly two parameters.
         *
         * @param callable $function
         *
         * @return callable
         */
            function (callable $function) {
                return function ($a, $b) use ($function) {
                    return $function($a, $b);
                };
            },

        'both'              =>
        /**
         * Returns a function that returns `true` when both of the predicates match, `false` otherwise.
         *
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

        'clone_'            =>
        /**
         * Clones an object.
         *
         * @param object $object
         *
         * @return mixed
         */
            function ($object) {
                return clone $object;
            },

        'comparator'        =>
        /**
         * Creates a comparator function from a function that returns whether the first argument is less than the second.
         *
         * @param callable $predicate
         *
         * @return callable
         */
            function (callable $predicate) {
                return function ($x, $y) use ($predicate) {
                    return $predicate($x, $y) ? -1 : ($predicate($y, $x) ? 1 : 0);
                };
            },

        'construct'         =>
        /**
         * Wraps the constructor of the given class to a function.
         *
         * @param string $class
         * @param mixed  ...$initialArguments
         *
         * @return object
         */
            function ($class, ... $initialArguments) {
                return Phamda::constructN(static::getConstructorArity($class), $class, ...$initialArguments);
            },

        'constructN'        =>
        /**
         * Wraps the constructor of the given class to a function of specified arity.
         *
         * @param int    $arity
         * @param string $class
         * @param mixed  ...$initialArguments
         *
         * @return object
         */
            function ($arity, $class, ... $initialArguments) {
                return static::_curryN($arity, function (...$arguments) use ($class) {
                    return new $class(...array_merge($arguments));
                }, ...$initialArguments);
            },

        'contains'          =>
        /**
         * Returns `true` if the specified item is found in the collection, `false` otherwise.
         *
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

        'curry'             =>
        /**
         * Wraps the given function to a function that returns a new function until all required parameters are given.
         *
         * @param callable $function
         * @param mixed    ...$initialArguments
         *
         * @return callable
         */
            function (callable $function, ...$initialArguments) {
                return static::_curryN(static::getArity($function), $function, ...$initialArguments);
            },

        'curryN'            =>
        /**
         * Wraps the given function to a function of specified arity that returns a new function until all required parameters are given.
         *
         * @param int      $length
         * @param callable $function
         * @param mixed    ...$initialArguments
         *
         * @return callable
         */
            function ($length, callable $function, ...$initialArguments) {
                return static::_curryN($length, $function, ...$initialArguments);
            },

        'dec'               =>
        /**
         * Decrements the given number.
         *
         * @param int|float $number
         *
         * @return int|float
         */
            function ($number) {
                return Phamda::add(-1, $number);
            },

        'defaultTo'         =>
        /**
         * Returns the default argument if the value argument is `null`.
         *
         * @param mixed $default
         * @param mixed $value
         *
         * @return mixed
         */
            function ($default, $value) {
                return $value !== null ? $value : $default;
            },

        'divide'            =>
        /**
         * Divides two numbers.
         *
         * @param int|float $x
         * @param int|float $y
         *
         * @return int|float
         */
            function ($x, $y) {
                return $x / $y;
            },

        'each'              =>
        /**
         * Calls the given function for each element in the collection and returns the original collection.
         *
         * @param callable                      $function
         * @param array|\Traversable|Collection $collection
         *
         * @return array|\Traversable|Collection
         */
            function (callable $function, $collection) {
                foreach ($collection as $key => $item) {
                    $function($item, $key, $collection);
                }

                return $collection;
            },

        'either'            =>
        /**
         * Returns a function that returns `true` when either of the predicates matches, `false` otherwise.
         *
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

        'eq'                =>
        /**
         * Return true when the arguments are strictly equal.
         *
         * @param mixed $x
         * @param mixed $y
         *
         * @return bool
         */
            function ($x, $y) {
                return $x === $y;
            },

        'explode'           =>
        /**
         * Returns an array containing the parts of a string split by the given delimiter.
         *
         * @param string $delimiter
         * @param string $string
         *
         * @return string[]
         */
            function ($delimiter, $string) {
                return explode($delimiter, $string);
            },

        'filter'            =>
        /**
         * Returns a new collection containing the items that match the given predicate.
         *
         * @param callable                      $predicate
         * @param array|\Traversable|Collection $collection
         *
         * @return array|Collection
         */
            function (callable $predicate, $collection) {
                return static::_filter($predicate, $collection);
            },

        'find'              =>
        /**
         * Returns the first item of a collection for which the given predicate matches, or null if no match is found.
         *
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

        'findIndex'         =>
        /**
         * Returns the index of the first item of a collection for which the given predicate matches, or null if no match is found.
         *
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

        'findLast'          =>
        /**
         * Returns the last item of a collection for which the given predicate matches, or null if no match is found.
         *
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

        'findLastIndex'     =>
        /**
         * Returns the index of the last item of a collection for which the given predicate matches, or null if no match is found.
         *
         * @param callable           $predicate
         * @param array|\Traversable $collection
         *
         * @return int|string|null
         */
            function (callable $predicate, $collection) {
                foreach (static::_reverse($collection) as $index => $item) {
                    if ($predicate($item)) {
                        return $index;
                    }
                }

                return null;
            },

        'first'             =>
        /**
         * Returns the first item of a collection, or false if the collection is empty.
         *
         * @param array|\Traversable|Collection $collection
         *
         * @return mixed
         */
            function ($collection) {
                if (is_array($collection)) {
                    return reset($collection);
                } elseif (method_exists($collection, 'first')) {
                    return $collection->first();
                } else {
                    foreach ($collection as $item) {
                        return $item;
                    }

                    return false;
                }
            },

        'flip'              =>
        /**
         * Wraps the given function and returns a new function for which the order of the first two parameters is reversed.
         *
         * @param callable $function
         *
         * @return callable
         */
            function (callable $function) {
                return function ($a, $b, ...$arguments) use ($function) {
                    return $function($b, $a, ...$arguments);
                };
            },

        'groupBy'           =>
        /**
         * Returns an array of sub collections based on a function that returns the group keys for each item.
         *
         * @param callable                      $function
         * @param array|\Traversable|Collection $collection
         *
         * @return array[]|Collection[]
         */
            function (callable $function, $collection) {
                if (method_exists($collection, 'groupBy')) {
                    return $collection->groupBy($function);
                }

                return static::_reduce(function (array $collections, $item, $key) use ($function) {
                    $collections[$function($item)][$key] = $item;

                    return $collections;
                }, [], $collection);
            },

        'gt'                =>
        /**
         * Returns `true` if the first parameter is greater than the second, `false` otherwise.
         *
         * @param mixed $x
         * @param mixed $y
         *
         * @return bool
         */
            function ($x, $y) {
                return $x > $y;
            },

        'gte'               =>
        /**
         * Returns `true` if the first parameter is greater than or equal to the second, `false` otherwise.
         *
         * @param mixed $x
         * @param mixed $y
         *
         * @return bool
         */
            function ($x, $y) {
                return $x >= $y;
            },

        'identity'          =>
        /**
         * Returns the given parameter.
         *
         * @param mixed $x
         *
         * @return mixed
         */
            function ($x) {
                return $x;
            },

        'ifElse'            =>
        /**
         * Returns a function that applies either the `onTrue` or the `onFalse` function, depending on the result of the `condition` predicate.
         *
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

        'implode'           =>
        /**
         * Returns a string formed by combining a list of strings using the given glue string.
         *
         * @param string   $glue
         * @param string[] $strings
         *
         * @return string
         */
            function ($glue, $strings) {
                return implode($glue, $strings);
            },

        'inc'               =>
        /**
         * Increments the given number.
         *
         * @param int|float $number
         *
         * @return int|float
         */
            function ($number) {
                return Phamda::add(1, $number);
            },

        'indexOf'           =>
        /**
         * Returns the index of the given item in a collection, or `false` if the item is not found.
         *
         * @param mixed              $item
         * @param array|\Traversable $collection
         *
         * @return int|string|false
         */
            function ($item, $collection) {
                foreach ($collection as $key => $current) {
                    if ($item === $current) {
                        return $key;
                    }
                }

                return false;
            },

        'isEmpty'           =>
        /**
         * Returns `true` if a collection has no elements, `false` otherwise.
         *
         * @param array|\Traversable|Collection $collection
         *
         * @return bool
         */
            function ($collection) {
                if (is_array($collection)) {
                    return empty($collection);
                } elseif (method_exists($collection, 'isEmpty')) {
                    return $collection->isEmpty();
                } else {
                    foreach ($collection as $item) {
                        return false;
                    }

                    return true;
                }
            },

        'isInstance'        =>
        /**
         * Return `true` if an object is of the specified class, `false` otherwise.
         *
         * @param string $class
         * @param object $object
         *
         * @return bool
         */
            function ($class, $object) {
                return $object instanceof $class;
            },

        'last'              =>
        /**
         * Returns the last item of a collection, or false if the collection is empty.
         *
         * @param array|\Traversable|Collection $collection
         *
         * @return mixed
         */
            function ($collection) {
                if (is_array($collection)) {
                    return end($collection);
                } elseif (method_exists($collection, 'last')) {
                    return $collection->last();
                } else {
                    foreach (static::_reverse($collection) as $item) {
                        return $item;
                    }

                    return false;
                }
            },

        'lt'                =>
        /**
         * Returns `true` if the first parameter is less than the second, `false` otherwise.
         *
         * @param mixed $x
         * @param mixed $y
         *
         * @return bool
         */
            function ($x, $y) {
                return $x < $y;
            },

        'lte'               =>
        /**
         * Returns `true` if the first parameter is less than or equal to the second, `false` otherwise.
         *
         * @param mixed $x
         * @param mixed $y
         *
         * @return bool
         */
            function ($x, $y) {
                return $x <= $y;
            },

        'map'               =>
        /**
         * Returns a new collection where values are created from the original collection by calling the supplied function.
         *
         * @param callable                      $function
         * @param array|\Traversable|Collection $collection
         *
         * @return array|Collection
         */
            function (callable $function, $collection) {
                return static::_map($function, $collection);
            },

        'max'               =>
        /**
         * Returns the largest value in the collection.
         *
         * @param array|\Traversable $collection
         *
         * @return mixed
         */
            function ($collection) {
                return static::getCompareResult(Phamda::gt(), $collection);
            },

        'maxBy'             =>
        /**
         * Returns the item from a collection for which the supplied function returns the largest value.
         *
         * @param callable           $getValue
         * @param array|\Traversable $collection
         *
         * @return mixed
         */
            function (callable $getValue, $collection) {
                return static::getCompareByResult(Phamda::gt(), $getValue, $collection);
            },

        'min'               =>
        /**
         * Returns the smallest value in the collection.
         *
         * @param array|\Traversable $collection
         *
         * @return mixed
         */
            function ($collection) {
                return static::getCompareResult(Phamda::lt(), $collection);
            },

        'minBy'             =>
        /**
         * Returns the item from a collection for which the supplied function returns the smallest value.
         *
         * @param callable           $getValue
         * @param array|\Traversable $collection
         *
         * @return mixed
         */
            function (callable $getValue, $collection) {
                return static::getCompareByResult(Phamda::lt(), $getValue, $collection);
            },

        'modulo'            =>
        /**
         * Divides two integers and returns the modulo.
         *
         * @param int $x
         * @param int $y
         *
         * @return int
         */
            function ($x, $y) {
                return $x % $y;
            },

        'multiply'          =>
        /**
         * Multiplies two numbers.
         *
         * @param int|float $x
         * @param int|float $y
         *
         * @return int|float
         */
            function ($x, $y) {
                return $x * $y;
            },

        'nAry'              =>
        /**
         * Wraps the given function in a function that accepts exactly the given amount of parameters.
         *
         * @param int      $arity
         * @param callable $function
         *
         * @return callable
         */
            function ($arity, callable $function) {
                return function (...$arguments) use ($arity, $function) {
                    return $function(...array_slice($arguments, 0, $arity));
                };
            },

        'negate'            =>
        /**
         * Returns the negation of a number.
         *
         * @param int|float $x
         *
         * @return int|float
         */
            function ($x) {
                return Phamda::multiply($x, -1);
            },

        'none'              =>
        /**
         * Returns `true` if no element in the collection matches the predicate, `false` otherwise.
         *
         * @param callable           $predicate
         * @param array|\Traversable $collection
         *
         * @return bool
         */
            function (callable $predicate, $collection) {
                return ! Phamda::any($predicate, $collection);
            },

        'not'               =>
        /**
         * Wraps a predicate and returns a function that return `true` if the wrapped function returns a falsey value, `false` otherwise.
         *
         * @param callable $predicate
         *
         * @return callable
         */
            function (callable $predicate) {
                return function (... $arguments) use ($predicate) {
                    return ! $predicate(...$arguments);
                };
            },

        'partition'         =>
        /**
         * Returns the items of the original collection divided into two collections based on a predicate function.
         *
         * @param callable                      $predicate
         * @param array|\Traversable|Collection $collection
         *
         * @return array[]|Collection[]
         */
            function (callable $predicate, $collection) {
                if (method_exists($collection, 'partition')) {
                    return $collection->partition($predicate);
                }

                return static::_reduce(function (array $collections, $item, $key) use ($predicate) {
                    $collections[$predicate($item) ? 0 : 1][$key] = $item;

                    return $collections;
                }, [[], []], $collection);
            },

        'path'              =>
        /**
         * Returns a value found at the given path.
         *
         * @param array        $path
         * @param array|object $object
         *
         * @return mixed
         */
            function (array $path, $object) {
                foreach ($path as $name) {
                    $object = static::_prop($name, $object);
                }

                return $object;
            },

        'pathEq'            =>
        /**
         * Returns `true` if the given value is found at the specified path, `false` otherwise.
         *
         * @param array        $path
         * @param mixed        $value
         * @param array|object $object
         *
         * @return boolean
         */
            function (array $path, $value, $object) {
                return Phamda::path($path, $object) === $value;
            },

        'pick'              =>
        /**
         * Returns a new array, containing only the values that have keys matching the given list.
         *
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

        'pickAll'           =>
        /**
         * Returns a new array, containing the values that have keys matching the given list, including keys that are not found in the item.
         *
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

        'pluck'             =>
        /**
         * Returns a new collection, where the items are single properties plucked from the given collection.
         *
         * @param string                        $name
         * @param array|\Traversable|Collection $collection
         *
         * @return array|Collection
         */
            function ($name, $collection) {
                return static::_map(Phamda::prop($name), $collection);
            },

        'prepend'           =>
        /**
         * Return a new collection that contains the given item first and all the items in the given collection.
         *
         * @param mixed            $item
         * @param array|Collection $collection
         *
         * @return array|Collection
         */
            function ($item, $collection) {
                if (is_array($collection)) {
                    array_unshift($collection, $item);

                    return $collection;
                } elseif (method_exists($collection, 'prepend')) {
                    return $collection->prepend($item);
                } else {
                    $items[] = $item;
                    foreach ($collection as $collectionItem) {
                        $items[] = $collectionItem;
                    }

                    return $items;
                }
            },

        'product'           =>
        /**
         * Multiplies a list of numbers.
         *
         * @param int[]|float[] $values
         *
         * @return int|float
         */
            function ($values) {
                return static::_reduce(Phamda::multiply(), 1, $values);
            },

        'prop'              =>
        /**
         * Returns the given element of an array or property of an object.
         *
         * @param string                    $name
         * @param array|object|\ArrayAccess $object
         *
         * @return mixed
         */
            function ($name, $object) {
                return static::_prop($name, $object);
            },

        'propEq'            =>
        /**
         * Returns `true` if the specified property has the given value, `false` otherwise.
         *
         * @param string       $name
         * @param mixed        $value
         * @param array|object $object
         *
         * @return bool
         */
            function ($name, $value, $object) {
                return static::_prop($name, $object) === $value;
            },

        'reduce'            =>
        /**
         * Returns a value accumulated by calling the given function for each element of the collection.
         *
         * @param callable           $function
         * @param mixed              $initial
         * @param array|\Traversable $collection
         *
         * @return mixed
         */
            function (callable $function, $initial, $collection) {
                return static::_reduce($function, $initial, $collection);
            },

        'reduceRight'       =>
        /**
         * Returns a value accumulated by calling the given function for each element of the collection in reverse order.
         *
         * @param callable           $function
         * @param mixed              $initial
         * @param array|\Traversable $collection
         *
         * @return mixed
         */
            function (callable $function, $initial, $collection) {
                return static::_reduce($function, $initial, static::_reverse($collection));
            },

        'reject'            =>
        /**
         * Returns a new collection containing the items that do not match the given predicate.
         *
         * @param callable                      $predicate
         * @param array|\Traversable|Collection $collection
         *
         * @return array|Collection
         */
            function (callable $predicate, $collection) {
                return static::_filter(Phamda::not($predicate), $collection);
            },

        'reverse'           =>
        /**
         * Returns a new collection where the items are in a reverse order.
         *
         * @param array|\Traversable|Collection $collection
         *
         * @return array|Collection
         */
            function ($collection) {
                return static::_reverse($collection);
            },

        'slice'             =>
        /**
         * Returns a new collection, containing the items of the original from index `start` (inclusive) to index `end` (exclusive).
         *
         * @param int                           $start
         * @param int                           $end
         * @param array|\Traversable|Collection $collection
         *
         * @return array|Collection
         */
            function ($start, $end, $collection) {
                return static::_slice($start, $end, $collection);
            },

        'sort'              =>
        /**
         * Returns a new collection sorted by the given comparator function.
         *
         * @param callable                      $comparator
         * @param array|\Traversable|Collection $collection
         *
         * @return array|Collection
         */
            function (callable $comparator, $collection) {
                return static::_sort($comparator, $collection);
            },

        'sortBy'            =>
        /**
         * Returns a new collection sorted by comparing the values provided by calling the given function for each item.
         *
         * @param callable                      $function
         * @param array|\Traversable|Collection $collection
         *
         * @return array|Collection
         */
            function (callable $function, $collection) {
                $comparator = function ($x, $y) use ($function) {
                    $xKey = $function($x);
                    $yKey = $function($y);

                    return $xKey < $yKey ? -1 : ($xKey > $yKey ? 1 : 0);
                };

                return static::_sort($comparator, $collection);
            },

        'stringIndexOf'     =>
        /**
         * Returns the first index of a substring in a string, or `false` if the substring is not found.
         *
         * @param string $substring
         * @param string $string
         *
         * @return int|false
         */
            function ($substring, $string) {
                return strpos($string, $substring);
            },

        'stringLastIndexOf' =>
        /**
         * Returns the last index of a substring in a string, or `false` if the substring is not found.
         *
         * @param string $substring
         * @param string $string
         *
         * @return int|false
         */
            function ($substring, $string) {
                return strrpos($string, $substring);
            },

        'substring'         =>
        /**
         * Returns a substring of the original string between given indexes.
         *
         * @param int    $start
         * @param int    $end
         * @param string $string
         *
         * @return string
         */
            function ($start, $end, $string) {
                return substr($string, $start, $end - $start);
            },

        'substringFrom'     =>
        /**
         * Returns a substring of the original string starting from the given index.
         *
         * @param int    $start
         * @param string $string
         *
         * @return string
         */
            function ($start, $string) {
                return substr($string, $start);
            },

        'substringTo'       =>
        /**
         * Returns a substring of the original string ending before the given index.
         *
         * @param int    $end
         * @param string $string
         *
         * @return string
         */
            function ($end, $string) {
                return substr($string, 0, $end);
            },

        'subtract'          =>
        /**
         * Subtracts two numbers.
         *
         * @param int|float $x
         * @param int|float $y
         *
         * @return int|float
         */
            function ($x, $y) {
                return $x - $y;
            },

        'sum'               =>
        /**
         * Adds together a list of numbers.
         *
         * @param int[]|float[] $values
         *
         * @return int|float
         */
            function ($values) {
                return static::_reduce(Phamda::add(), 0, $values);
            },

        'tap'               =>
        /**
         * Calls the provided function with the given value as a parameter and returns the value.
         *
         * @param callable $function
         * @param mixed    $object
         *
         * @return mixed
         */
            function (callable $function, $object) {
                $function($object);

                return $object;
            },

        'times'             =>
        /**
         * Calls the provided function the specified number of times and returns the results in an array.
         *
         * @param callable $function
         * @param int      $count
         *
         * @return array
         */
            function (callable $function, $count) {
                return static::_map($function, range(0, $count - 1));
            },

        'unary'             =>
        /**
         * Wraps the given function in a function that accepts exactly one parameter.
         *
         * @param callable $function
         *
         * @return callable
         */
            function (callable $function) {
                return function ($a) use ($function) {
                    return $function($a);
                };
            },

        'zip'               =>
        /**
         * Returns a new array of value pairs from the values of the given arrays with matching keys.
         *
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

        'zipWith'           =>
        /**
         * Returns a new array of values created by calling the given function with the matching values of the given arrays.
         *
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

        'where'             =>
        /**
         * Returns true if the given object matches the specification.
         *
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
        '_'        =>
        /**
         * Returns a placeholder to be used with curried functions.
         *
         * @return Placeholder
         */
            function () {
                return self::$placeholder ?: self::$placeholder = new Placeholder();
            },

        'compose'  =>
        /**
         * Returns a new function that calls each supplied function in turn in reverse order and passes the result as a parameter to the next function.
         *
         * @param callable ...$functions
         *
         * @return callable
         */
            function (... $functions) {
                return Phamda::pipe(... array_reverse($functions));
            },

        'false'    =>
        /**
         * Returns a function that always returns `false`.
         *
         * @return callable
         */
            function () {
                return function () {
                    return false;
                };
            },

        'invoker'  =>
        /**
         * Returns a function that calls the specified method of a given object.
         *
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
         * Wraps the given function and returns a new function that can be called with the remaining parameters.
         *
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
         * Wraps the given function and returns a new function of fixed arity that can be called with the remaining parameters.
         *
         * @param int      $arity
         * @param callable $function
         * @param mixed    ...$initialArguments
         *
         * @return callable
         */
            function ($arity, callable $function, ... $initialArguments) {
                $remainingCount = $arity - count($initialArguments);
                $partial        = function (... $arguments) use ($function, $initialArguments) {
                    return $function(...array_merge($initialArguments, $arguments));
                };

                return $remainingCount > 0 ? static::_curryN($remainingCount, $partial) : $partial;
            },

        'pipe'     =>
        /**
         * Returns a new function that calls each supplied function in turn and passes the result as a parameter to the next function.
         *
         * @param callable ...$functions
         *
         * @return callable
         */
            function (... $functions) {
                if (count($functions) < 2) {
                    throw InvalidFunctionCompositionException::create();
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
         * Returns a function that always returns `true`.
         *
         * @return callable
         */
            function () {
                return function () {
                    return true;
                };
            },
    ],
];
