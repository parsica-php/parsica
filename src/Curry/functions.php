<?php declare(strict_types=1);
/**
 * This code is forked from https://github.com/matteosister/php-curry, which is abandoned. It could be integrated into
 * the rest of Parsica.
 */

namespace Parsica\Parsica\Curry;

use ReflectionClass;
use ReflectionFunction;

function curry(callable $callable) : callable
{
    if (_number_of_required_params($callable) === 0) {
        return _make_function($callable);
    }
    if (_number_of_required_params($callable) === 1) {
        return _curry_array_args($callable, _rest(func_get_args()));
    }

    return _curry_array_args($callable, _rest(func_get_args()));
}

/**
 * @param       $callable
 * @param array $args pass the arguments to be curried as an array
 *
 * @return callable
 */
function curry_args(callable $callable, array $args) : callable
{
    return _curry_array_args($callable, $args);
}

/**
 * @param callable $callable
 *
 * @return callable
 */
function curry_right(callable $callable) : callable
{
    if (_number_of_required_params($callable) < 2) return _make_function($callable);
    return _curry_array_args($callable, _rest(func_get_args()), false);
}

/**
 * @param callable $callable
 * @param array    $args pass the arguments to be curried as an array
 *
 * @return callable
 */
function curry_right_args(callable $callable, array $args) : callable
{
    return _curry_array_args($callable, $args, false);
}

/**
 * @param callable $callable
 * @param          $args
 * @param bool     $left
 *
 * @return callable
 */
function _curry_array_args(callable $callable, array $args, bool $left = true) : callable
{
    return function () use ($callable, $args, $left) {
        if (_is_fullfilled($callable, $args)) {
            return _execute($callable, $args, $left);
        }
        $newArgs = array_merge($args, func_get_args());
        if (_is_fullfilled($callable, $newArgs)) {
            return _execute($callable, $newArgs, $left);
        }
        return _curry_array_args($callable, $newArgs, $left);
    };
}

/**
 * @param $callable
 * @param $args
 * @param $left
 *
 * @return mixed
 * @internal
 */
function _execute(callable $callable, array $args, $left)
{
    if (!$left) {
        $args = array_reverse($args);
    }

    $placeholders = _placeholder_positions($args);
    if (0 < count($placeholders)) {
        $n = _number_of_required_params($callable);
        if ($n <= _last($placeholders)) {
            // This means that we have more placeholders than needed
            // I know that throwing exceptions is not really the
            // functional way, but this case should not happen.
            throw new \Exception("Argument Placeholder found on unexpected position !");
        }
        foreach ($placeholders as $i) {
            $args[$i] = $args[$n];
            array_splice($args, $n, 1);
        }
    }

    return call_user_func_array($callable, $args);
}

/**
 * @param array $args
 *
 * @return array
 * @internal
 */
function _rest(array $args) : array
{
    return array_slice($args, 1);
}

/**
 * @internal
 */
function _is_fullfilled(callable $callable, array $args) : bool
{
    $args = array_filter($args, function ($arg) {
        return !_is_placeholder($arg);
    });
    return count($args) >= _number_of_required_params($callable);
}

/**
 * @internal
 */
function _number_of_required_params(callable $callable) : int
{
    if (is_array($callable)) {
        $refl = new ReflectionClass($callable[0]);
        $method = $refl->getMethod($callable[1]);
        return $method->getNumberOfRequiredParameters();
    }

    $refl = new ReflectionFunction($callable);
    return $refl->getNumberOfRequiredParameters();
}

/**
 * if the callback is an array(instance, method),
 * it returns an equivalent function for PHP 5.3 compatibility.
 *
 * @param callable $callable
 *
 * @return callable
 * @internal
 */
function _make_function(callable $callable) : callable
{
    if (is_array($callable)) {
        return /** @return mixed */ fn() => call_user_func_array($callable, func_get_args());
    }
    return $callable;
}

/**
 * Checks if an argument is a placeholder.
 *
 * @param mixed $arg
 *
 * @internal
 */
function _is_placeholder($arg) : bool
{
    return $arg instanceof Placeholder;
}

/**
 * Gets an array of placeholders positions in the given arguments.
 *
 * @param array $args
 *
 * @return array
 * @internal
 */
function _placeholder_positions(array $args) : array
{
    return array_keys(array_filter($args, 'Parsica\\Parsica\\Curry\\_is_placeholder'));
}

/**
 * Get the last element in an array.
 *
 * @param array $array
 *
 * @return mixed
 * @internal
 */
function _last(array $array)
{
    $lastKey = array_key_last($array);
    return is_null($lastKey) ? null : $array[$lastKey];
}

/**
 * Gets a special placeholder value used to specify "gaps" within curried
 * functions, allowing partial application of any combination of arguments,
 * regardless of their positions. Should be used only for required arguments.
 * When used, optional arguments must be at the end of the argument list.
 */
function __() : Placeholder
{
    return new Placeholder;
}
