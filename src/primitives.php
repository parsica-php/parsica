<?php declare(strict_types=1);

namespace Mathias\ParserCombinator;

use Mathias\ParserCombinator\ParseResult\ParseResult;
use Webmozart\Assert\Assert;
use function Mathias\ParserCombinator\ParseResult\{fail, parser, succeed};

/**
 * A parser that satisfies a predicate. Useful as a building block for writing things like char(), digit()...
 *
 * @param callable(string) : bool $predicate
 * @param string $expected
 *
 * @return Parser<string>
 */
function satisfy(callable $predicate, string $expected = "satisfy(predicate)"): Parser
{
    return parser(function (string $input) use ($predicate, $expected) : ParseResult {
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
 * Parse a single character of anything
 *
 * @return Parser<string>
 */
function single(): Parser
{
    return satisfy(fn(string $_) => true, 'single');
}


/**
 * Parse the end of the input
 *
 * @return Parser<string>
 */
function eof(): Parser
{
    return parser(function (string $input): ParseResult {
        return mb_strlen($input) === 0
            ? succeed("", "")
            : fail("eof", $input);
    });
}
