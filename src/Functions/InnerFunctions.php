<?php

namespace Phamda\Functions;

/**
 * @param mixed $a
 * @param mixed $b
 *
 * @return bool
 */
function eq($a, $b)
{
    return $a === $b;
}

/**
 * @param callable $function
 * @param array    $list
 *
 * @return array
 */
function filter(callable $function, array $list)
{
    return array_filter($list, $function);
}

/**
 * @param callable $function
 * @param array    $list
 *
 * @return array
 */
function map(callable $function, array $list)
{
    return array_map($function, $list);
}

/**
 * @param string       $name
 * @param mixed        $value
 * @param array|object $object
 *
 * @return bool
 */
function propEq($name, $value, $object)
{
    return is_object($object)
        ? $object->$name === $value
        : $object[$name] === $value;
}

/**
 * @param callable $function
 * @param mixed    $initial
 * @param array    $list
 *
 * @return mixed
 */
function reduce(callable $function, $initial, array $list)
{
    return array_reduce($list, $function, $initial);
}

/**
 * @param callable $comparator
 * @param array    $list
 *
 * @return array
 */
function sort(callable $comparator, array $list)
{
    $newList = $list;

    usort($list, $comparator);

    return $newList;
}
