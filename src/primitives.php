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
use Verraes\Parsica\Internal\Succeed;

/**
 * A parser that satisfies a predicate on a single token. Useful as a building block for writing things like char(),
 * digit()...
 *
 * @template T
 *
 * @psalm-param callable(string) : bool $predicate
 *
 * @psalm-return Parser<T>
 */
function satisfy(callable $predicate): Parser
{
    $label = "satisfy(predicate)";
    return Parser::make($label, function (Stream $input) use ($label, $predicate) : ParseResult {
        try {
            $t = $input->take1();
        } catch (EndOfStream $e) {
            return new Fail($label, $input);
        }
        return $predicate($t->chunk())
            ? new Succeed($t->chunk(), $t->stream())
            : new Fail($label, $input);
    });
}

/**
 * Skip 0 or more characters as long as the predicate holds.
 *
 * @template T
 *
 * @psalm-param callable(string) : bool $predicate
 * @psalm-return Parser<null>
 */
function skipWhile(callable $predicate): Parser
{
    return takeWhile($predicate)->followedBy(pure(null));
}

/**
 * Skip 1 or more characters as long as the predicate holds.
 *
 * @template T
 *
 * @psalm-param callable(string) : bool $predicate
 *
 * @psalm-return Parser<null>
 */
function skipWhile1(callable $predicate): Parser
{
    return takeWhile1($predicate)->followedBy(pure(null));
}

/**
 * Keep parsing 0 or more characters as long as the predicate holds.
 *
 * @template T
 * @psalm-param callable(string) : bool $predicate
 * @psalm-return Parser<T>
 */
function takeWhile(callable $predicate): Parser
{
    return Parser::make(
        "takeWhile(predicate)",
        function (Stream $input) use ($predicate): ParseResult {
            $t = $input->takeWhile($predicate);
            return new Succeed($t->chunk(), $t->stream());
        }
    );
}


/**
 * Keep parsing 1 or more characters as long as the predicate holds.
 *
 * @template T
 *
 * @psalm-param callable(string) : bool $predicate
 *
 * @psalm-return Parser<T>
 */
function takeWhile1(callable $predicate): Parser
{
    $label = "takeWhile1(predicate)";
    return Parser::make($label, function (Stream $input) use ($label, $predicate): ParseResult {

        try {
            $t = $input->take1();
        } catch (EndOfStream $e) {
            return new Fail($label, $input);
        }

        if (!$predicate($t->chunk())) {
            return new Fail($label, $input);
        }

        $t = $input->takeWhile($predicate);
        return new Succeed($t->chunk(), $t->stream());
    }
    );
}

/**
 * Parse and return a single character of anything.
 *
 * @template T
 *
 * @psalm-return Parser<T>
 */
function anySingle(): Parser
{
    return satisfy(
    /** @psalm-param mixed $_ */
        fn($_) => true
    )->label("anySingle");
}

/**
 * Parse and return a single character of anything.
 *
 * @TODO This is an alias of anySingle. Should we get rid of one of them?
 * @psalm-return Parser<string>
 */
function anything(): Parser
{
    return satisfy(fn(string $_) => true)->label("anything");
}


/**
 * Match any character but the given one.
 *
 * @psalm-return Parser<string>
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
 * @psalm-param list<string> $chars
 *
 * @psalm-return Parser<string>
 * @api
 * @template T
 *
 */
function oneOf(array $chars): Parser
{
    Assert::singleChars($chars);
    return satisfy(fn(string $x) => in_array($x, $chars))->label("one of " . implode('', $chars));
}

/**
 * A compact form of 'oneOf'.
 * oneOfS("abc") == oneOf(['a', 'b', 'c'])
 *
 * @psalm-param string $chars
 *
 * @psalm-return Parser<string>
 * @api
 */
function oneOfS(string $chars): Parser
{
    /** @psalm-var list<string> $split */
    $split = mb_str_split($chars);
    return oneOf($split);
}


/**
 * The dual of 'oneOf'. Succeeds if the current character is not in the supplied list of characters. Returns the
 * parsed character.
 *
 * @psalm-param list<string> $chars
 *
 * @psalm-return Parser<string>
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
 * @psalm-param string $chars
 *
 * @psalm-return Parser<string>
 * @api
 * @template T
 *
 */
function noneOfS(string $chars): Parser
{
    /** @psalm-var list<string> $split */
    $split = mb_str_split($chars);
    return noneOf($split);
}

/**
 * Consume the rest of the input and return it as a string. This parser never fails, but may return the empty string.
 *
 * @psalm-return Parser<string>
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
 * @psalm-return Parser<null>
 *
 * @api
 */
function nothing(): Parser
{
    /** @psalm-var callable(Stream):ParseResult<null> $result */
    $result = fn(Stream $input) : ParseResult => new Succeed(null, $input);
    $parser = Parser::make("<nothing>", $result);
    return $parser;
}

/**
 * Parse everything; that is, consume the rest of the input until the end.
 *
 * @api
 */
function everything(): Parser
{
    return Parser::make("<everything>", fn(Stream $input) => new Succeed((string)$input, new MBStringStream("")));
}

/**
 * Always succeed, no matter what the input was.
 *
 * @api
 */
function succeed(): Parser
{
    return Parser::make("<always succeed>", fn(Stream $input) => new Succeed(null, $input));
}

/**
 * Always fail, no matter what the input was.
 *
 * @return Parser
 * @api
 */
function fail(string $label): Parser
{
    return Parser::make($label, fn(Stream $input) => new Fail($label, $input));
}

/**
 * Parse the end of the input
 *
 * @psalm-return Parser<T>
 * @api
 * @template T
 */
function eof(): Parser
{
    $label = "<EOF>";
    return Parser::make($label, fn(Stream $input): ParseResult => $input->isEOF()
        ? new Succeed("", $input)
        : new Fail($label, $input)
    );
}
