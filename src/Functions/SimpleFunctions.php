<?php

namespace Phamda\Functions;

use Phamda\CoreFunctionsTrait;
use Phamda\Exception\InvalidFunctionCompositionException;
use Phamda\Phamda;
use Phamda\Placeholder;

class SimpleFunctions
{
    use CoreFunctionsTrait;

    /**
     * Returns a placeholder to be used with curried functions.
     *
     * @return Placeholder
     */
    public static function _()
    {
        return static::$placeholder ?: static::$placeholder = new Placeholder();
    }

    /**
     * Returns a new function that calls each supplied function in turn in reverse order and passes the result as a parameter to the next function.
     *
     * @param callable ...$functions
     *
     * @return callable
     */
    public static function compose(... $functions)
    {
        return Phamda::pipe(...array_reverse($functions));
    }

    /**
     * Returns a function that always returns `false`.
     *
     * @return callable
     */
    public static function false()
    {
        return function () {
            return false;
        };
    }

    /**
     * Returns a function that calls the specified method of a given object.
     *
     * @param int    $arity
     * @param string $method
     * @param mixed  ...$initialArguments
     *
     * @return callable
     */
    public static function invoker($arity, $method, ... $initialArguments)
    {
        $remainingCount = $arity - count($initialArguments) + 1;

        return static::_curryN($remainingCount, function (... $arguments) use ($method, $initialArguments) {
            $object = array_pop($arguments);

            return $object->{$method}(...array_merge($initialArguments, $arguments));
        });
    }

    /**
     * Wraps the given function and returns a new function that can be called with the remaining parameters.
     *
     * @param callable $function
     * @param mixed    ...$initialArguments
     *
     * @return callable
     */
    public static function partial(callable $function, ... $initialArguments)
    {
        return Phamda::partialN(static::getArity($function), $function, ...$initialArguments);
    }

    /**
     * Wraps the given function and returns a new function of fixed arity that can be called with the remaining parameters.
     *
     * @param int      $arity
     * @param callable $function
     * @param mixed    ...$initialArguments
     *
     * @return callable
     */
    public static function partialN($arity, callable $function, ... $initialArguments)
    {
        $remainingCount = $arity - count($initialArguments);
        $partial        = function (... $arguments) use ($function, $initialArguments) {
            return $function(...array_merge($initialArguments, $arguments));
        };

        return $remainingCount > 0 ? static::_curryN($remainingCount, $partial) : $partial;
    }

    /**
     * Returns a new function that calls each supplied function in turn and passes the result as a parameter to the next function.
     *
     * @param callable ...$functions
     *
     * @return callable
     */
    public static function pipe(... $functions)
    {
        if (count($functions) < 2) {
            throw InvalidFunctionCompositionException::create();
        }

        return function (... $arguments) use ($functions) {
            $result = null;
            foreach ($functions as $function) {
                $result = $result !== null ? $function($result) : $function(...$arguments);
            }

            return $result;
        };
    }

    /**
     * Returns a function that always returns `true`.
     *
     * @return callable
     */
    public static function true()
    {
        return function () {
            return true;
        };
    }
}
