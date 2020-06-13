<?php declare(strict_types=1);


namespace Mathias\ParserCombinator\Predicates;

use Mathias\ParserCombinator\Assert\Assert;

/**
 * Creates an equality predicate
 *
 * @return callable(string) : bool
 */
function equals(string $x): callable
{
    Assert::singleChar($x);
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
 * Predicate that checks if a character is in an array of character codes.
 *
 * @param list<int> $chars
 *
 * @return callable(string) : bool
 */
function isCharCode(array $chars): callable
{
    return fn(string $x): bool => in_array(mb_ord($x), $chars);
}

/**
 * Returns true for any Unicode space character, and the control characters \t, \n, \r, \f, \v.
 *
 * @return callable(string) : bool
 */
function isSpace(): callable
{
    return isCharCode([9, 10, 11, 12, 13, 32, 160]);
}

/**
 * Like 'isSpace', but does not accept newlines and carriage returns.
 *
 * @return callable(string) : bool
 * @see isSpace
 */
function isHSpace(): callable
{
    return isCharCode([9, 11, 12, 32, 160]);
}

/**
 * True for 0-9
 *
 * @return callable(string) : bool
 */
function isDigit(): callable
{
    return isCharCode(range(48, 57));
}