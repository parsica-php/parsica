<?php declare(strict_types=1);
/**
 * This code is forked from https://github.com/matteosister/php-curry, which is abandoned. It could be integrated into
 * the rest of Parsica.
 */

namespace Parsica\Parsica\Curry;

use Closure;
use Exception;
use ReflectionClass;
use ReflectionFunction;

/**
 * @psalm-param pure-callable $callable
 *
 * @psalm-return pure-callable
 * @throws Exception
 * @psalm-pure
 */
function curry(callable $callable) : callable
{
    return _number_of_required_params($callable) === 0
        ? _make_function($callable)
        : _curry_array_args($callable, _rest(func_get_args()));

}

/**
 * @psalm-param pure-callable $callable
 *
 * @psalm-return pure-callable
 * @psalm-pure
 */
function curry_right(callable $callable) : callable
{
    return _number_of_required_params($callable) < 2
        ? _make_function($callable)
        : _curry_array_args($callable, _rest(func_get_args()), false);
}

/**
 * @psalm-param pure-callable $callable
 * @psalm-param array $args
 * @psalm-param bool $left
 *
 * @psalm-return pure-callable
 * @psalm-pure
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
 * @psalm-param pure-callable $callable
 * @param array<mixed> $args
 * @param mixed $left
 *
 * @return mixed
 * @internal
 * @psalm-pure
 */
function _execute(callable $callable, array $args, bool $left = true)
{
    if (!$left) {
        $args = array_reverse($args);
    }

    $placeholderPositions = _placeholder_positions($args);
    if (0 < count($placeholderPositions)) {
        $reqdParams = _number_of_required_params($callable);
        if ($reqdParams <= _last($placeholderPositions)) {
            // This means that we have more placeholderPositions than needed
            // I know that throwing exceptions is not really the
            // functional way, but this case should not happen.
            throw new Exception("Argument Placeholder found on unexpected position!");
        }
        foreach ($placeholderPositions as $placeholderPosition) {
            /** @psalm-suppress MixedAssignment  */
            $args[$placeholderPosition] = $args[$reqdParams];
            array_splice($args, $reqdParams, 1);
        }
    }

    return call_user_func_array($callable, $args);
}

/**
 * @param array $args
 *
 * @return array
 * @internal
 * @psalm-pure
 */
function _rest(array $args) : array
{
    return array_slice($args, 1);
}

/**
 * @psalm-param pure-callable $callable
 * @param array    $args
 *
 * @return bool
 * @throws Exception
 * @internal
 * @psalm-pure
 */
function _is_fullfilled(callable $callable, array $args) : bool
{
    $nonPlaceholderArgs = array_filter(
        $args,
        fn($arg) => !($arg instanceof Placeholder)
    );
    return count($nonPlaceholderArgs) >= _number_of_required_params($callable);
}

/**
 * @psalm-param pure-callable $callable
 * @internal
 * @psalm-pure
 */
function _number_of_required_params(callable $callable) : int
{
    if (is_array($callable)) {
        /** @psalm-suppress ImpureMethodCall */
        $refl = new ReflectionClass($callable[0]);
        /** @psalm-suppress ImpureMethodCall */
        $method = $refl->getMethod($callable[1]);
        /** @psalm-suppress ImpureMethodCall */
        return $method->getNumberOfRequiredParameters();
    } elseif (is_string($callable) || $callable instanceof Closure) {
        /** @psalm-suppress ImpureMethodCall */
        $refl = new ReflectionFunction($callable);
        /** @psalm-suppress ImpureMethodCall */
        return $refl->getNumberOfRequiredParameters();
    }
    throw new Exception("Unexpected other type of callable");
}

/**
 * if the callback is an array(instance, method),
 * it returns an equivalent function for PHP 5.3 compatibility.
 *
 * @psalm-param pure-callable $callable
 *
 * @psalm-return pure-callable
 * @internal
 * @psalm-pure
 */
function _make_function(callable $callable) : callable
{
    if (is_array($callable)) {
        return /** @return mixed */ fn() => call_user_func_array($callable, func_get_args());
    }
    return $callable;
}

/**
 * Gets an array of placeholders positions in the given arguments.
 *
 * @param array $args
 *
 * @return list<int|string>
 * @internal
 * @psalm-pure
 */
function _placeholder_positions(array $args) : array
{
    return array_keys(
        array_filter(
            $args,
            fn($arg) : bool => $arg instanceof Placeholder
        )
    );
}

/**
 * Get the last element in an array.
 *
 * @psalm-param array<T> $array
 *
 * @psalm-return null|T
 * @template T
 * @internal
 * @psalm-pure
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
 * @psalm-pure
 */
function __() : Placeholder
{
    return new Placeholder;
}
