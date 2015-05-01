<?php

namespace Phamda\Functions;

use Phamda\Collection\Collection;
use Phamda\CoreFunctionsTrait;
use Phamda\Phamda;

class CurriedFunctions
{
    use CoreFunctionsTrait;

    /**
     * Adds two numbers.
     *
     * @param int|float $x
     * @param int|float $y
     *
     * @return int|float
     */
    public static function add($x, $y)
    {
        return $x + $y;
    }

    /**
     * Returns `true` if all elements of the collection match the predicate, `false` otherwise.
     *
     * @param callable           $predicate
     * @param array|\Traversable $collection
     *
     * @return bool
     */
    public static function all(callable $predicate, $collection)
    {
        foreach ($collection as $item) {
            if (! $predicate($item)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Creates a single predicate from a list of predicates that returns `true` when all the predicates match, `false` otherwise.
     *
     * @param callable[] $predicates
     *
     * @return callable
     */
    public static function allPass(array $predicates)
    {
        return function (... $arguments) use ($predicates) {
            foreach ($predicates as $predicate) {
                if (! $predicate(...$arguments)) {
                    return false;
                }
            }

            return true;
        };
    }

    /**
     * Returns a function that always returns the passed value.
     *
     * @param mixed $value
     *
     * @return callable
     */
    public static function always($value)
    {
        return function () use ($value) {
            return $value;
        };
    }

    /**
     * Returns `true` if any element of the collection matches the predicate, `false` otherwise.
     *
     * @param callable           $predicate
     * @param array|\Traversable $collection
     *
     * @return bool
     */
    public static function any(callable $predicate, $collection)
    {
        foreach ($collection as $item) {
            if ($predicate($item)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Creates a single predicate from a list of predicates that returns `true` when any of the predicates matches, `false` otherwise.
     *
     * @param callable[] $predicates
     *
     * @return callable
     */
    public static function anyPass(array $predicates)
    {
        return function (... $arguments) use ($predicates) {
            foreach ($predicates as $predicate) {
                if ($predicate(...$arguments)) {
                    return true;
                }
            }

            return false;
        };
    }

    /**
     * Return a new collection that contains all the items in the given collection and the given item last.
     *
     * @param mixed            $item
     * @param array|Collection $collection
     *
     * @return array|Collection
     */
    public static function append($item, $collection)
    {
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
    }

    /**
     * Returns a new array or object, setting the given value to the specified property.
     *
     * @param string       $property
     * @param mixed        $value
     * @param array|object $object
     *
     * @return array|object
     */
    public static function assoc($property, $value, $object)
    {
        return static::_assoc($property, $value, $object);
    }

    /**
     * Returns a new array or object, setting the given value to the property specified by the path.
     *
     * @param array        $path
     * @param mixed        $value
     * @param array|object $object
     *
     * @return array|object
     */
    public static function assocPath(array $path, $value, $object)
    {
        return static::_assocPath($path, $value, $object);
    }

    /**
     * Wraps the given function in a function that accepts exactly two parameters.
     *
     * @param callable $function
     *
     * @return callable
     */
    public static function binary(callable $function)
    {
        return function ($a, $b) use ($function) {
            return $function($a, $b);
        };
    }

    /**
     * Returns a function that returns `true` when both of the predicates match, `false` otherwise.
     *
     * @param callable $a
     * @param callable $b
     *
     * @return callable
     */
    public static function both(callable $a, callable $b)
    {
        return function (... $arguments) use ($a, $b) {
            return $a(...$arguments) && $b(...$arguments);
        };
    }

    /**
     * Clones an object.
     *
     * @param object $object
     *
     * @return mixed
     */
    public static function clone_($object)
    {
        return clone $object;
    }

    /**
     * Creates a comparator function from a function that returns whether the first argument is less than the second.
     *
     * @param callable $predicate
     *
     * @return callable
     */
    public static function comparator(callable $predicate)
    {
        return function ($x, $y) use ($predicate) {
            return $predicate($x, $y) ? -1 : ($predicate($y, $x) ? 1 : 0);
        };
    }

    /**
     * Wraps the constructor of the given class to a function.
     *
     * @param string $class
     * @param mixed  ...$initialArguments
     *
     * @return object
     */
    public static function construct($class, ... $initialArguments)
    {
        return Phamda::constructN(static::getConstructorArity($class), $class, ...$initialArguments);
    }

    /**
     * Wraps the constructor of the given class to a function of specified arity.
     *
     * @param int    $arity
     * @param string $class
     * @param mixed  ...$initialArguments
     *
     * @return object
     */
    public static function constructN($arity, $class, ... $initialArguments)
    {
        return static::_curryN($arity, function (... $arguments) use ($class) {
            return new $class(...array_merge($arguments));
        }, ...$initialArguments);
    }

    /**
     * Returns `true` if the specified item is found in the collection, `false` otherwise.
     *
     * @param mixed              $value
     * @param array|\Traversable $collection
     *
     * @return bool
     */
    public static function contains($value, $collection)
    {
        foreach ($collection as $item) {
            if ($item === $value) {
                return true;
            }
        }

        return false;
    }

    /**
     * Wraps the given function to a function that returns a new function until all required parameters are given.
     *
     * @param callable $function
     * @param mixed    ...$initialArguments
     *
     * @return callable
     */
    public static function curry(callable $function, ... $initialArguments)
    {
        return static::_curryN(static::getArity($function), $function, ...$initialArguments);
    }

    /**
     * Wraps the given function to a function of specified arity that returns a new function until all required parameters are given.
     *
     * @param int      $length
     * @param callable $function
     * @param mixed    ...$initialArguments
     *
     * @return callable
     */
    public static function curryN($length, callable $function, ... $initialArguments)
    {
        return static::_curryN($length, $function, ...$initialArguments);
    }

    /**
     * Decrements the given number.
     *
     * @param int|float $number
     *
     * @return int|float
     */
    public static function dec($number)
    {
        return Phamda::add(-1, $number);
    }

    /**
     * Returns the default argument if the value argument is `null`.
     *
     * @param mixed $default
     * @param mixed $value
     *
     * @return mixed
     */
    public static function defaultTo($default, $value)
    {
        return $value !== null ? $value : $default;
    }

    /**
     * Divides two numbers.
     *
     * @param int|float $x
     * @param int|float $y
     *
     * @return int|float
     */
    public static function divide($x, $y)
    {
        return $x / $y;
    }

    /**
     * Calls the given function for each element in the collection and returns the original collection.
     *
     * @param callable                      $function
     * @param array|\Traversable|Collection $collection
     *
     * @return array|\Traversable|Collection
     */
    public static function each(callable $function, $collection)
    {
        foreach ($collection as $key => $item) {
            $function($item, $key, $collection);
        }

        return $collection;
    }

    /**
     * Returns a function that returns `true` when either of the predicates matches, `false` otherwise.
     *
     * @param callable $a
     * @param callable $b
     *
     * @return callable
     */
    public static function either(callable $a, callable $b)
    {
        return function (... $arguments) use ($a, $b) {
            return $a(...$arguments) || $b(...$arguments);
        };
    }

    /**
     * Return true when the arguments are strictly equal.
     *
     * @param mixed $x
     * @param mixed $y
     *
     * @return bool
     */
    public static function eq($x, $y)
    {
        return $x === $y;
    }

    /**
     * Returns an array containing the parts of a string split by the given delimiter.
     *
     * @param string $delimiter
     * @param string $string
     *
     * @return string[]
     */
    public static function explode($delimiter, $string)
    {
        return explode($delimiter, $string);
    }

    /**
     * Returns a new collection containing the items that match the given predicate.
     *
     * @param callable                      $predicate
     * @param array|\Traversable|Collection $collection
     *
     * @return array|Collection
     */
    public static function filter(callable $predicate, $collection)
    {
        return static::_filter($predicate, $collection);
    }

    /**
     * Returns the first item of a collection for which the given predicate matches, or null if no match is found.
     *
     * @param callable           $predicate
     * @param array|\Traversable $collection
     *
     * @return mixed|null
     */
    public static function find(callable $predicate, $collection)
    {
        foreach ($collection as $item) {
            if ($predicate($item)) {
                return $item;
            }
        }

        return null;
    }

    /**
     * Returns the index of the first item of a collection for which the given predicate matches, or null if no match is found.
     *
     * @param callable           $predicate
     * @param array|\Traversable $collection
     *
     * @return int|string|null
     */
    public static function findIndex(callable $predicate, $collection)
    {
        foreach ($collection as $index => $item) {
            if ($predicate($item)) {
                return $index;
            }
        }

        return null;
    }

    /**
     * Returns the last item of a collection for which the given predicate matches, or null if no match is found.
     *
     * @param callable           $predicate
     * @param array|\Traversable $collection
     *
     * @return mixed|null
     */
    public static function findLast(callable $predicate, $collection)
    {
        foreach (static::_reverse($collection) as $item) {
            if ($predicate($item)) {
                return $item;
            }
        }

        return null;
    }

    /**
     * Returns the index of the last item of a collection for which the given predicate matches, or null if no match is found.
     *
     * @param callable           $predicate
     * @param array|\Traversable $collection
     *
     * @return int|string|null
     */
    public static function findLastIndex(callable $predicate, $collection)
    {
        foreach (static::_reverse($collection) as $index => $item) {
            if ($predicate($item)) {
                return $index;
            }
        }

        return null;
    }

    /**
     * Returns the first item of a collection, or false if the collection is empty.
     *
     * @param array|\Traversable|Collection $collection
     *
     * @return mixed
     */
    public static function first($collection)
    {
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
    }

    /**
     * Wraps the given function and returns a new function for which the order of the first two parameters is reversed.
     *
     * @param callable $function
     *
     * @return callable
     */
    public static function flip(callable $function)
    {
        return function ($a, $b, ... $arguments) use ($function) {
            return $function($b, $a, ...$arguments);
        };
    }

    /**
     * Returns an array of sub collections based on a function that returns the group keys for each item.
     *
     * @param callable                      $function
     * @param array|\Traversable|Collection $collection
     *
     * @return array[]|Collection[]
     */
    public static function groupBy(callable $function, $collection)
    {
        if (method_exists($collection, 'groupBy')) {
            return $collection->groupBy($function);
        }

        return static::_reduce(function (array $collections, $item, $key) use ($function) {
            $collections[$function($item)][$key] = $item;

            return $collections;
        }, [], $collection);
    }

    /**
     * Returns `true` if the first parameter is greater than the second, `false` otherwise.
     *
     * @param mixed $x
     * @param mixed $y
     *
     * @return bool
     */
    public static function gt($x, $y)
    {
        return $x > $y;
    }

    /**
     * Returns `true` if the first parameter is greater than or equal to the second, `false` otherwise.
     *
     * @param mixed $x
     * @param mixed $y
     *
     * @return bool
     */
    public static function gte($x, $y)
    {
        return $x >= $y;
    }

    /**
     * Returns the given parameter.
     *
     * @param mixed $x
     *
     * @return mixed
     */
    public static function identity($x)
    {
        return $x;
    }

    /**
     * Returns a function that applies either the `onTrue` or the `onFalse` function, depending on the result of the `condition` predicate.
     *
     * @param callable $condition
     * @param callable $onTrue
     * @param callable $onFalse
     *
     * @return mixed
     */
    public static function ifElse(callable $condition, callable $onTrue, callable $onFalse)
    {
        return function (... $arguments) use ($condition, $onTrue, $onFalse) {
            return $condition(...$arguments) ? $onTrue(...$arguments) : $onFalse(...$arguments);
        };
    }

    /**
     * Returns a string formed by combining a list of strings using the given glue string.
     *
     * @param string   $glue
     * @param string[] $strings
     *
     * @return string
     */
    public static function implode($glue, $strings)
    {
        return implode($glue, $strings);
    }

    /**
     * Increments the given number.
     *
     * @param int|float $number
     *
     * @return int|float
     */
    public static function inc($number)
    {
        return Phamda::add(1, $number);
    }

    /**
     * Returns the index of the given item in a collection, or `false` if the item is not found.
     *
     * @param mixed              $item
     * @param array|\Traversable $collection
     *
     * @return int|string|false
     */
    public static function indexOf($item, $collection)
    {
        foreach ($collection as $key => $current) {
            if ($item === $current) {
                return $key;
            }
        }

        return false;
    }

    /**
     * Returns `true` if a collection has no elements, `false` otherwise.
     *
     * @param array|\Traversable|Collection $collection
     *
     * @return bool
     */
    public static function isEmpty($collection)
    {
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
    }

    /**
     * Return `true` if an object is of the specified class, `false` otherwise.
     *
     * @param string $class
     * @param object $object
     *
     * @return bool
     */
    public static function isInstance($class, $object)
    {
        return $object instanceof $class;
    }

    /**
     * Returns the last item of a collection, or false if the collection is empty.
     *
     * @param array|\Traversable|Collection $collection
     *
     * @return mixed
     */
    public static function last($collection)
    {
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
    }

    /**
     * Returns `true` if the first parameter is less than the second, `false` otherwise.
     *
     * @param mixed $x
     * @param mixed $y
     *
     * @return bool
     */
    public static function lt($x, $y)
    {
        return $x < $y;
    }

    /**
     * Returns `true` if the first parameter is less than or equal to the second, `false` otherwise.
     *
     * @param mixed $x
     * @param mixed $y
     *
     * @return bool
     */
    public static function lte($x, $y)
    {
        return $x <= $y;
    }

    /**
     * Returns a new collection where values are created from the original collection by calling the supplied function.
     *
     * @param callable                      $function
     * @param array|\Traversable|Collection $collection
     *
     * @return array|Collection
     */
    public static function map(callable $function, $collection)
    {
        return static::_map($function, $collection);
    }

    /**
     * Returns the largest value in the collection.
     *
     * @param array|\Traversable $collection
     *
     * @return mixed
     */
    public static function max($collection)
    {
        return static::getCompareResult(Phamda::gt(), $collection);
    }

    /**
     * Returns the item from a collection for which the supplied function returns the largest value.
     *
     * @param callable           $getValue
     * @param array|\Traversable $collection
     *
     * @return mixed
     */
    public static function maxBy(callable $getValue, $collection)
    {
        return static::getCompareByResult(Phamda::gt(), $getValue, $collection);
    }

    /**
     * Returns the smallest value in the collection.
     *
     * @param array|\Traversable $collection
     *
     * @return mixed
     */
    public static function min($collection)
    {
        return static::getCompareResult(Phamda::lt(), $collection);
    }

    /**
     * Returns the item from a collection for which the supplied function returns the smallest value.
     *
     * @param callable           $getValue
     * @param array|\Traversable $collection
     *
     * @return mixed
     */
    public static function minBy(callable $getValue, $collection)
    {
        return static::getCompareByResult(Phamda::lt(), $getValue, $collection);
    }

    /**
     * Divides two integers and returns the modulo.
     *
     * @param int $x
     * @param int $y
     *
     * @return int
     */
    public static function modulo($x, $y)
    {
        return $x % $y;
    }

    /**
     * Multiplies two numbers.
     *
     * @param int|float $x
     * @param int|float $y
     *
     * @return int|float
     */
    public static function multiply($x, $y)
    {
        return $x * $y;
    }

    /**
     * Wraps the given function in a function that accepts exactly the given amount of parameters.
     *
     * @param int      $arity
     * @param callable $function
     *
     * @return callable
     */
    public static function nAry($arity, callable $function)
    {
        return function (... $arguments) use ($arity, $function) {
            return $function(...array_slice($arguments, 0, $arity));
        };
    }

    /**
     * Returns the negation of a number.
     *
     * @param int|float $x
     *
     * @return int|float
     */
    public static function negate($x)
    {
        return Phamda::multiply($x, -1);
    }

    /**
     * Returns `true` if no element in the collection matches the predicate, `false` otherwise.
     *
     * @param callable           $predicate
     * @param array|\Traversable $collection
     *
     * @return bool
     */
    public static function none(callable $predicate, $collection)
    {
        return ! Phamda::any($predicate, $collection);
    }

    /**
     * Wraps a predicate and returns a function that return `true` if the wrapped function returns a falsey value, `false` otherwise.
     *
     * @param callable $predicate
     *
     * @return callable
     */
    public static function not(callable $predicate)
    {
        return function (... $arguments) use ($predicate) {
            return ! $predicate(...$arguments);
        };
    }

    /**
     * Returns the items of the original collection divided into two collections based on a predicate function.
     *
     * @param callable                      $predicate
     * @param array|\Traversable|Collection $collection
     *
     * @return array[]|Collection[]
     */
    public static function partition(callable $predicate, $collection)
    {
        if (method_exists($collection, 'partition')) {
            return $collection->partition($predicate);
        }

        return static::_reduce(function (array $collections, $item, $key) use ($predicate) {
            $collections[$predicate($item) ? 0 : 1][$key] = $item;

            return $collections;
        }, [[], []], $collection);
    }

    /**
     * Returns a value found at the given path.
     *
     * @param array        $path
     * @param array|object $object
     *
     * @return mixed
     */
    public static function path(array $path, $object)
    {
        foreach ($path as $name) {
            $object = static::_prop($name, $object);
        }

        return $object;
    }

    /**
     * Returns `true` if the given value is found at the specified path, `false` otherwise.
     *
     * @param array        $path
     * @param mixed        $value
     * @param array|object $object
     *
     * @return boolean
     */
    public static function pathEq(array $path, $value, $object)
    {
        return Phamda::path($path, $object) === $value;
    }

    /**
     * Returns a new array, containing only the values that have keys matching the given list.
     *
     * @param array $names
     * @param array $item
     *
     * @return array
     */
    public static function pick(array $names, array $item)
    {
        $new = [];
        foreach ($names as $name) {
            if (array_key_exists($name, $item)) {
                $new[$name] = $item[$name];
            }
        }

        return $new;
    }

    /**
     * Returns a new array, containing the values that have keys matching the given list, including keys that are not found in the item.
     *
     * @param array $names
     * @param array $item
     *
     * @return array
     */
    public static function pickAll(array $names, array $item)
    {
        $new = [];
        foreach ($names as $name) {
            $new[$name] = isset($item[$name]) ? $item[$name] : null;
        }

        return $new;
    }

    /**
     * Returns a new collection, where the items are single properties plucked from the given collection.
     *
     * @param string                        $name
     * @param array|\Traversable|Collection $collection
     *
     * @return array|Collection
     */
    public static function pluck($name, $collection)
    {
        return static::_map(Phamda::prop($name), $collection);
    }

    /**
     * Return a new collection that contains the given item first and all the items in the given collection.
     *
     * @param mixed            $item
     * @param array|Collection $collection
     *
     * @return array|Collection
     */
    public static function prepend($item, $collection)
    {
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
    }

    /**
     * Multiplies a list of numbers.
     *
     * @param int[]|float[] $values
     *
     * @return int|float
     */
    public static function product($values)
    {
        return static::_reduce(Phamda::multiply(), 1, $values);
    }

    /**
     * Returns the given element of an array or property of an object.
     *
     * @param string                    $name
     * @param array|object|\ArrayAccess $object
     *
     * @return mixed
     */
    public static function prop($name, $object)
    {
        return static::_prop($name, $object);
    }

    /**
     * Returns `true` if the specified property has the given value, `false` otherwise.
     *
     * @param string       $name
     * @param mixed        $value
     * @param array|object $object
     *
     * @return bool
     */
    public static function propEq($name, $value, $object)
    {
        return static::_prop($name, $object) === $value;
    }

    /**
     * Returns a value accumulated by calling the given function for each element of the collection.
     *
     * @param callable           $function
     * @param mixed              $initial
     * @param array|\Traversable $collection
     *
     * @return mixed
     */
    public static function reduce(callable $function, $initial, $collection)
    {
        return static::_reduce($function, $initial, $collection);
    }

    /**
     * Returns a value accumulated by calling the given function for each element of the collection in reverse order.
     *
     * @param callable           $function
     * @param mixed              $initial
     * @param array|\Traversable $collection
     *
     * @return mixed
     */
    public static function reduceRight(callable $function, $initial, $collection)
    {
        return static::_reduce($function, $initial, static::_reverse($collection));
    }

    /**
     * Returns a new collection containing the items that do not match the given predicate.
     *
     * @param callable                      $predicate
     * @param array|\Traversable|Collection $collection
     *
     * @return array|Collection
     */
    public static function reject(callable $predicate, $collection)
    {
        return static::_filter(Phamda::not($predicate), $collection);
    }

    /**
     * Returns a new collection where the items are in a reverse order.
     *
     * @param array|\Traversable|Collection $collection
     *
     * @return array|Collection
     */
    public static function reverse($collection)
    {
        return static::_reverse($collection);
    }

    /**
     * Returns a new collection, containing the items of the original from index `start` (inclusive) to index `end` (exclusive).
     *
     * @param int                           $start
     * @param int                           $end
     * @param array|\Traversable|Collection $collection
     *
     * @return array|Collection
     */
    public static function slice($start, $end, $collection)
    {
        return static::_slice($start, $end, $collection);
    }

    /**
     * Returns a new collection sorted by the given comparator function.
     *
     * @param callable                      $comparator
     * @param array|\Traversable|Collection $collection
     *
     * @return array|Collection
     */
    public static function sort(callable $comparator, $collection)
    {
        return static::_sort($comparator, $collection);
    }

    /**
     * Returns a new collection sorted by comparing the values provided by calling the given function for each item.
     *
     * @param callable                      $function
     * @param array|\Traversable|Collection $collection
     *
     * @return array|Collection
     */
    public static function sortBy(callable $function, $collection)
    {
        $comparator = function ($x, $y) use ($function) {
            $xKey = $function($x);
            $yKey = $function($y);

            return $xKey < $yKey ? -1 : ($xKey > $yKey ? 1 : 0);
        };

        return static::_sort($comparator, $collection);
    }

    /**
     * Returns the first index of a substring in a string, or `false` if the substring is not found.
     *
     * @param string $substring
     * @param string $string
     *
     * @return int|false
     */
    public static function stringIndexOf($substring, $string)
    {
        return strpos($string, $substring);
    }

    /**
     * Returns the last index of a substring in a string, or `false` if the substring is not found.
     *
     * @param string $substring
     * @param string $string
     *
     * @return int|false
     */
    public static function stringLastIndexOf($substring, $string)
    {
        return strrpos($string, $substring);
    }

    /**
     * Returns a substring of the original string between given indexes.
     *
     * @param int    $start
     * @param int    $end
     * @param string $string
     *
     * @return string
     */
    public static function substring($start, $end, $string)
    {
        return substr($string, $start, $end - $start);
    }

    /**
     * Returns a substring of the original string starting from the given index.
     *
     * @param int    $start
     * @param string $string
     *
     * @return string
     */
    public static function substringFrom($start, $string)
    {
        return substr($string, $start);
    }

    /**
     * Returns a substring of the original string ending before the given index.
     *
     * @param int    $end
     * @param string $string
     *
     * @return string
     */
    public static function substringTo($end, $string)
    {
        return substr($string, 0, $end);
    }

    /**
     * Subtracts two numbers.
     *
     * @param int|float $x
     * @param int|float $y
     *
     * @return int|float
     */
    public static function subtract($x, $y)
    {
        return $x - $y;
    }

    /**
     * Adds together a list of numbers.
     *
     * @param int[]|float[] $values
     *
     * @return int|float
     */
    public static function sum($values)
    {
        return static::_reduce(Phamda::add(), 0, $values);
    }

    /**
     * Calls the provided function with the given value as a parameter and returns the value.
     *
     * @param callable $function
     * @param mixed    $object
     *
     * @return mixed
     */
    public static function tap(callable $function, $object)
    {
        $function($object);

        return $object;
    }

    /**
     * Calls the provided function the specified number of times and returns the results in an array.
     *
     * @param callable $function
     * @param int      $count
     *
     * @return array
     */
    public static function times(callable $function, $count)
    {
        return static::_map($function, range(0, $count - 1));
    }

    /**
     * Wraps the given function in a function that accepts exactly one parameter.
     *
     * @param callable $function
     *
     * @return callable
     */
    public static function unary(callable $function)
    {
        return function ($a) use ($function) {
            return $function($a);
        };
    }

    /**
     * Returns true if the given object matches the specification.
     *
     * @param array        $specification
     * @param array|object $object
     *
     * @return mixed
     */
    public static function where(array $specification, $object)
    {
        foreach ($specification as $name => $part) {
            if (! static::testSpecificationPart($name, $part, $object)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Returns a new array of value pairs from the values of the given arrays with matching keys.
     *
     * @param array $a
     * @param array $b
     *
     * @return array
     */
    public static function zip(array $a, array $b)
    {
        $zipped = [];
        foreach (array_intersect_key($a, $b) as $key => $value) {
            $zipped[$key] = [$value, $b[$key]];
        }

        return $zipped;
    }

    /**
     * Returns a new array of values created by calling the given function with the matching values of the given arrays.
     *
     * @param callable $function
     * @param array    $a
     * @param array    $b
     *
     * @return array
     */
    public static function zipWith(callable $function, array $a, array $b)
    {
        $zipped = [];
        foreach (array_intersect_key($a, $b) as $key => $value) {
            $zipped[$key] = $function($value, $b[$key]);
        }

        return $zipped;
    }
}
