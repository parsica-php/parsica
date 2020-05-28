<?php declare(strict_types=1);


namespace Mathias\ParserCombinator;

/**
 * Creates an equality predicate
 *
 * @template T
 *
 * @param T $x
 *
 * @return callable(T) : bool
 */
function equals($x): callable
{
    return fn($y) => $x === $y;
}

/**
 * Negates a predicate.
 *
 * @template T
 *
 * @param callable(T) : bool $predicate
 *
 * @return callable(T) : bool
 */
function not(callable $predicate): callable
{
    return fn($x) => !$predicate($x);
}
