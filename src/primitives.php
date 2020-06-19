<?php declare(strict_types=1);

namespace Mathias\ParserCombinator;

use Mathias\ParserCombinator\Internal\Assert;
use Mathias\ParserCombinator\Internal\Fail;
use Mathias\ParserCombinator\Internal\Succeed;
use Mathias\ParserCombinator\Internal\TakeWhile;
use function Mathias\ParserCombinator\{isEqual, notPred};

/**
 * A parser that satisfies a predicate. Useful as a building block for writing things like char(), digit()...
 *
 * @template T
 *
 * @param callable(string) : bool $predicate
 *
 * @return Parser<T>
 */
function satisfy(callable $predicate): Parser
{
    return Parser::make(function (string $input) use ($predicate) : ParseResult {
        if (mb_strlen($input) === 0) {
            return new Fail("satisfy(predicate)", "EOF");
        }
        $token = mb_substr($input, 0, 1);
        return $predicate($token)
            ? new Succeed($token, mb_substr($input, 1))
            : new Fail("satisfy(predicate)", $token);
    });
}

/**
 * Skip 0 or more characters as long as the predicate holds.
 *
 * @template T
 *
 * @param callable(string) : bool $predicate
 * @param string $expected
 *
 * @return Parser<T>
 */
function skipWhile(callable $predicate): Parser
{
    return TakeWhile::_skipWhile($predicate);
}

/**
 * Skip 1 or more characters as long as the predicate holds.
 *
 * @template T
 *
 * @param callable(string) : bool $predicate
 * @param string $expected
 *
 * @return Parser<T>
 */
function skipWhile1(callable $predicate): Parser
{
    return TakeWhile::_skipWhile1($predicate);
}

/**
 * Keep parsing 0 or more characters as long as the predicate holds.
 *
 * @template T
 *
 * @param callable(string) : bool $predicate
 * @param string $expected
 *
 * @return Parser<T>
 */
function takeWhile(callable $predicate): Parser
{
    return TakeWhile::_takeWhile($predicate);
}


/**
 * Keep parsing 1 or more characters as long as the predicate holds.
 *
 * @template T
 *
 * @param callable(string) : bool $predicate
 * @param string $expected
 *
 * @return Parser<T>
 */
function takeWhile1(callable $predicate): Parser
{
    return TakeWhile::_takeWhile1($predicate);
}

/**
 * Parse and return a single character of anything.
 *
 * @template T
 *
 * @return Parser<T>
 */
function anySingle(): Parser
{
    return satisfy(
    /** @param mixed $_ */
        fn($_) => true
    )->label("anySingle");
}

/**
 * Parse and return a single character of anything.
 *
 * @TODO This is an alias of anySingle. Should we get rid of one of them?
 * @return Parser<string>
 */
function anything(): Parser
{
    return satisfy(fn(string $_) => true)->label("anything");
}


/**
 * Match any character but the given one.
 *
 * @template T
 *
 * @return Parser<string>
 */
function anySingleBut(string $x): Parser
{
    return satisfy(notPred(isEqual($x)))->label("anySingleBut($x)");
}

/**
 * Succeeds if the current character is in the supplied list of characters. Returns the parsed character.
 *
 * @template T
 *
 * @param list<string> $chars
 *
 * @return Parser<string>
 */
function oneOf(array $chars): Parser
{
    Assert::singleChars($chars);
    return satisfy(fn(string $x) => in_array($x, $chars))->label("oneOf(" . implode('', $chars) . ")");
}

/**
 * A compact form of 'oneOf'.
 * oneOfS("abc") == oneOf(['a', 'b', 'c'])
 *
 * @template T
 *
 * @param string $chars
 *
 * @return Parser<string>
 */
function oneOfS(string $chars): Parser
{
    /** @var list<string> $split */
    $split = mb_str_split($chars);
    return oneOf($split);
}


/**
 * The dual of 'oneOf'. Succeeds if the current character is not in the supplied list of characters. Returns the
 * parsed character.
 *
 * @template T
 *
 * @param list<string> $chars
 *
 * @return Parser<string>
 */
function noneOf(array $chars): Parser
{
    Assert::singleChars($chars);
    return satisfy(fn(string $x) => !in_array($x, $chars))
        ->label("noneOf(" . implode('', $chars) . ")");
}

/**
 * A compact form of 'noneOf'.
 * noneOfS("abc") == noneOf(['a', 'b', 'c'])
 *
 * @template T
 *
 * @param string $chars
 *
 * @return Parser<string>
 */
function noneOfS(string $chars): Parser
{
    /** @var list<string> $split */
    $split = mb_str_split($chars);
    return noneOf($split);
}

/**
 * Consume the rest of the input and return it as a string. This parser never fails, but may return the empty string.
 *
 * @template T
 * @return Parser<string>
 */
function takeRest(): Parser
{
    return takeWhile(fn(string $_): bool => true);
}

/**
 * Parse nothing, but still succeed.
 *
 * This serves as the zero parser in `append()` operations.
 */
function nothing(): Parser
{
    return Parser::make(fn(string $input) => new Succeed(null, $input));
}

/**
 * Parse everything; that is, consume the rest of the input until the end.
 */
function everything(): Parser
{
    return Parser::make(fn(string $input) => new Succeed($input, ""));
}

/**
 * Always succeed, no matter what the input was.
 */
function success(): Parser
{
    return Parser::make(fn(string $input) => new Succeed('', $input))->label('success');
}

/**
 * Always fail, no matter what the input was.
 */
function failure(): Parser
{
    return Parser::make(fn(string $input) => new Fail('', $input))->label('failure');
}

/**
 * Parse the end of the input
 *
 * @template T
 * @return Parser<T>
 */
function eof(): Parser
{
    return Parser::make(fn(string $input): ParseResult => mb_strlen($input) === 0
        ? new Succeed("", "")
        : new Fail("eof", $input)
    );
}
