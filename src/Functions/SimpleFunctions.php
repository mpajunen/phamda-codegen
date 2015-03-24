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
