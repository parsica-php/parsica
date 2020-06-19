<?php declare(strict_types=1);

namespace Mathias\ParserCombinator;

use Mathias\ParserCombinator\Internal\Assert;

/**
 * Creates an equality predicate
 *
 * @return callable(string) : bool
 */
function isEqual(string $x): callable
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
function notPred(callable $predicate): callable
{
    return fn(string $x): bool => !$predicate($x);
}

/**
 * Boolean And predicate.
 *
 * @param callable(string) : bool $first
 * @param callable(string) : bool $second
 *
 * @return callable(string) : bool
 */
function andPred(callable $first, callable $second): callable
{
    return fn(string $x): bool => $first($x) && $second($x);
}

/**
 * Boolean Or predicate.
 *
 * @param callable(string) : bool $first
 * @param callable(string) : bool $second
 *
 * @return callable(string) : bool
 */
function orPred(callable $first, callable $second): callable
{
    return fn(string $x): bool => $first($x) || $second($x);
}

/**
 * Predicate that checks if a character is in an array of character codes.
 *
 * @param list<int> $chars
 *
 * @return callable(string) : bool
 * @link https://doc.bccnsoft.com/docs/cppreference2018/en/c/string/wide/iswcntrl.html
 */
function isCharCode(array $chars): callable
{
    return fn(string $x): bool => in_array(mb_ord($x), $chars);
}

/**
 * Returns true for a space character, and the control characters \t, \n, \r, \f, \v.
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
    return isCharCode(range(0x30, 0x39));
}

/**
 * Control character predicate (a non-printing character of the Latin-1 subset of Unicode).
 *
 * @return callable(string) : bool
 */
function isControl(): callable
{
    return isCharCode(range(0x00, 0x1F) + [0x7F]);
}

/**
 * Returns true for a space or a tab character
 *
 * @return callable(string) : bool
 */
function isBlank()
{
    return isCharCode([0x9, 0x20]);
}

/**
 * Returns true for a space character, and \t, \n, \r, \f, \v.
 *
 * @return callable(string) : bool
 */
function isWhitespace()
{
    return isCharCode([0x20, 0x9, 0xA, 0xB, 0xC, 0xD]);
}

/**
 * Returns true for an uppercase character A-Z.
 *
 * @return callable(string) : bool
 */
function isUpper()
{
    return isCharCode(range(0x41, 0x5A));
}

/**
 * Returns true for a lowercase character a-z.
 *
 * @return callable(string) : bool
 */
function isLower()
{
    return isCharCode(range(0x61, 0x7A));
}

/**
 * Returns true for an uppercase or lowercase character A-Z, a-z.
 *
 * @return callable(string) : bool
 */
function isAlpha()
{
    return isCharCode(array_merge(range(0x41, 0x5A), range(0x61, 0x7A)));
}

/**
 * Returns true for an alpha or numeric character A-Z, a-z, 0-9.
 *
 * @return callable(string) : bool
 */
function isAlphaNum()
{
    return isCharCode(array_merge(range(0x30, 0x39), range(0x41, 0x5A), range(0x61, 0x7A)));
}

/**
 * Returns true if the given character is a hexadecimal numeric character 0123456789abcdefABCDEF.
 *
 * @return callable(string) : bool
 */
function isHexDigit()
{
    return isCharCode(array_merge(range(0x30, 0x39), range(0x41, 0x46), range(0x61, 0x66)));
}

/**
 * Returns true if the given character is a printable ASCII char.
 *
 * @return callable(string) : bool
 */
function isPrintable()
{
    return isCharCode(range(0x20, 0x7E));
}

/**
 * Returns true if the given character is a punctuation character !"#$%&'()*+,-./:;<=>?@[\]^_`{|}~
 *
 * @return callable(string) : bool
 */
function isPunctuation()
{
    return isCharCode(array_merge(range(0x21, 0x2F), range(0x3A, 0x40), range(0x5B, 0x60), range(0x7B, 0x7E)));
}


