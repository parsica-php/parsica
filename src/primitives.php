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
        if ((strlen($input) === 0)) return fail($expected, "EOF");
        $token = Str::head($input);
        return $predicate($token)
            ? succeed($token, Str::tail($input))
            : fail($expected, $token);
    });
}

/**
 * Parse a single character of anything
 *
 * @return Parser<string>
 */
function single() : Parser
{
    return satisfy(fn($_) => true, 'single');
}
