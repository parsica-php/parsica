<?php declare(strict_types=1);

namespace Mathias\ParserCombinator;

use Mathias\ParserCombinator\Parser\Parser;
use Mathias\ParserCombinator\ParseResult\ParseResult;
use function Mathias\ParserCombinator\ParseResult\{fail, succeed};
use function Mathias\ParserCombinator\Parser\parser;

/**
 * A parser that satisfies a predicate. Useful as a building block for writing things like char(), digit()...
 *
 * @template T
 * @param callable(string) : bool $predicate
 * @param string $expected
 * @return Parser<T>
 */
function satisfy(callable $predicate, string $expected = "satisfy(predicate)"): Parser
{
    return new Parser(function (string $input) use ($predicate, $expected) : ParseResult {
        if (mb_strlen($input) === 0) {
            return fail($expected, "EOF");
        }
        $token = mb_substr($input, 0, 1);
        return $predicate($token)
            ? succeed($token, mb_substr($input, 1))
            : fail($expected, $token);
    });
}



/**
 * Keep parsing 0 or more characters as long as the predicate holds.
 *
 * @template T
 * @param callable(string) : bool $predicate
 * @param string $expected
 * @return Parser<T>
 */
function takeWhile(callable $predicate, string $expected = "takeWhile(predicate)"): Parser
{
    return new Parser(function (string $input) use ($predicate, $expected) : ParseResult {
        if (mb_strlen($input) === 0) {
            return fail($expected, "EOF");
        }
        $chunk = "";
        $remaining = $input;
        while (mb_strlen($remaining) > 0 && $predicate(mb_substr($remaining, 0, 1))) {
            $chunk .= mb_substr($remaining, 0, 1);
            $remaining = mb_substr($remaining, 1);
        }
        return succeed($chunk, $remaining);
    });
}

/**
 * Keep parsing 1 or more characters as long as the predicate holds.
 *
 * @template T
 * @param callable(string) : bool $predicate
 * @param string $expected
 *
 * @return Parser<T>
 */
function takeWhile1(callable $predicate, string $expected = "takeWhile1(predicate)"): Parser
{
    return new Parser(function (string $input) use ($predicate, $expected) : ParseResult {
        if (mb_strlen($input) === 0) {
            return fail($expected, "EOF");
        }
        if(!$predicate(mb_substr($input, 0, 1))) {
            return fail($expected, $input);
        }
        $chunk = "";
        $remaining = $input;
        while ($predicate(mb_substr($remaining, 0, 1))) {
            $chunk .= mb_substr($remaining, 0, 1);
            $remaining = mb_substr($remaining, 1);
        }
        return succeed($chunk, $remaining);
    });
}

/**
 * Parse a single character of anything
 *
 * @return Parser<string>
 */
function anything(): Parser
{
    return satisfy(fn(string $_) => true, 'anything');
}

/**
 * Parse nothing, but still succeed.
 */
function nothing(): Parser
{
    return new Parser(fn(string $input) => succeed("", $input));
}

/**
 * Parse everything; that is, consume the rest of the input until the end.
 */
function everything(): Parser
{
    return new Parser(fn(string $input) => succeed($input, ""));
}


/**
 * Parse the end of the input
 *
 * @template T
 * @return Parser<T>
 */
function eof(): Parser
{
    return new Parser(fn(string $input): ParseResult => mb_strlen($input) === 0
        ? succeed("", "")
        : fail("eof", $input)
    );
}
