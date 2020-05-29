<?php declare(strict_types=1);


namespace Mathias\ParserCombinator;

use Mathias\ParserCombinator\Assert\Assert;

/**
 * Creates an equality predicate
 *
 * @return callable(string) : bool
 */
function equals(string $x): callable
{
    Assert::length($x, 1, "The argument to equals() must be 1 character in length");
    return fn(string $y): bool => $x === $y;
}

/**
 * Negates a predicate.
 *
 * @param callable(string) : bool $predicate
 *
 * @return callable(string) : bool
 */
function not(callable $predicate): callable
{
    return fn(string $x): bool => !$predicate($x);
}

/**
 * Returns true for any Unicode space character, and the control characters \t, \n, \r, \f, \v.
 *
 * @return callable(string) : bool
 */
function isSpace(): callable
{
    return fn(string $y): bool => in_array(mb_ord($y), [9, 10, 11, 12, 13, 32, 160]);
}

/**
 * Like 'isSpace', but does not accept newlines and carriage returns.
 *
 * @return callable(string) : bool
 * @see isSpace
 */
function isHSpace(): callable
{
    return fn(string $y): bool => in_array(mb_ord($y), [9, 11, 12, 32, 160]);
}