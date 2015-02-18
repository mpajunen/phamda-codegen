<?php

namespace Phamda\Functions;

/**
 * @param callable $function
 * @param array    $list
 *
 * @return bool
 */
function all(callable $function, array $list)
{
    foreach ($list as $value) {
        if (! $function($value)) {
            return false;
        }
    }

    return true;
}

/**
 * @param callable $function
 * @param array    $list
 *
 * @return bool
 */
function any(callable $function, array $list)
{
    foreach ($list as $value) {
        if ($function($value)) {
            return true;
        }
    }

    return false;
}

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
 * @param array $names
 * @param array $item
 *
 * @return array
 */
function pick(array $names, array $item)
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
 * @param array $names
 * @param array $item
 *
 * @return array
 */
function pickAll(array $names, array $item)
{
    $new = [];
    foreach ($names as $name) {
        $new[$name] = isset($item[$name]) ? $item[$name] : null;
    }

    return $new;
}

/**
 * @param string       $name
 * @param array|object $object
 *
 * @return mixed
 */
function prop($name, $object)
{
    return is_object($object) ? $object->$name : $object[$name];
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
