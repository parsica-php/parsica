<?php declare(strict_types=1);
/**
 * This file is part of the Parsica library.
 *
 * Copyright (c) 2020 Mathias Verraes <mathias@verraes.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Verraes\Parsica;

use Verraes\Parsica\Internal\Assert;
use Verraes\Parsica\Internal\EndOfStream;
use Verraes\Parsica\Internal\Fail;
use Verraes\Parsica\Internal\Stream;
use Verraes\Parsica\Internal\StringStream;
use Verraes\Parsica\Internal\Succeed;
use Verraes\Parsica\Internal\TakeWhile;

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
    return Parser::make(function (Stream $input) use ($predicate) : ParseResult {
        try {
            $t = $input->take1();
        } catch(EndOfStream $e) {
            return new Fail("satisfy(predicate)", $input);
        }
        return $predicate($t->token())
            ? new Succeed($t->token(), $t->stream())
            : new Fail("satisfy(predicate)", $input);
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
 * @return Parser<string>
 * @api
 * @template T
 *
 */
function anySingleBut(string $x): Parser
{
    return satisfy(notPred(isEqual($x)))->label("anySingleBut($x)");
}

/**
 * Succeeds if the current character is in the supplied list of characters. Returns the parsed character.
 *
 * @param list<string> $chars
 *
 * @return Parser<string>
 * @api
 * @template T
 *
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
 * @param string $chars
 *
 * @return Parser<string>
 * @api
 * @template T
 *
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
 * @param list<string> $chars
 *
 * @return Parser<string>
 * @api
 * @template T
 *
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
 * @param string $chars
 *
 * @return Parser<string>
 * @api
 * @template T
 *
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
 * @return Parser<string>
 * @api
 * @template T
 */
function takeRest(): Parser
{
    return takeWhile(fn(string $_): bool => true);
}

/**
 * Parse nothing, but still succeed.
 *
 * This serves as the zero parser in `append()` operations.
 *
 * @api
 */
function nothing(): Parser
{
    return Parser::make(fn(Stream $input) => new Succeed(null, $input));
}

/**
 * Parse everything; that is, consume the rest of the input until the end.
 *
 * @api
 */
function everything(): Parser
{
    return Parser::make(fn(Stream $input) => new Succeed((string) $input, new StringStream("")));
}

/**
 * Always succeed, no matter what the input was.
 *
 * @api
 */
function success(): Parser
{
    return Parser::make(fn(Stream $input) => new Succeed('', $input))->label('success');
}

/**
 * Always fail, no matter what the input was.
 *
 * @api
 */
function failure(): Parser
{
    return Parser::make(fn(Stream $input) => new Fail('', $input))->label('failure');
}

/**
 * Parse the end of the input
 *
 * @return Parser<T>
 * @api
 * @template T
 */
function eof(): Parser
{
    return Parser::make(fn(Stream $input): ParseResult => $input->isEOF()
        ? new Succeed("", $input)
        : new Fail("eof", $input)
    );
}
