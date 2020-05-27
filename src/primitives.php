<?php declare(strict_types=1);

namespace Mathias\ParserCombinator;

use Mathias\ParserCombinator\ParseResult\ParseResult;
use function Mathias\ParserCombinator\ParseResult\{fail, parser, succeed};

/**
 * A parser that satisfies a predicate. Useful as a building block for writing things like char(), digit()...
 *
 * @param callable(string) : bool $predicate
 *
 * @return Parser<string>
 */
function satisfy(callable $predicate): Parser
{
    return parser(function (string $input) use ($predicate) : ParseResult {
        if ((strlen($input) === 0)) return fail("input", "EOF");
        $token = Str::head($input);
        return $predicate($token)
            ? succeed($token, Str::tail($input))
            : fail("satisfy(predicate)", $token);
    });
}