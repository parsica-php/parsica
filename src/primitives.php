<?php declare(strict_types=1);

namespace Mathias\ParserCombinator;

use Mathias\ParserCombinator\ParseResult\ParseResult;
use function Mathias\ParserCombinator\ParseResult\{fail, parser, succeed};

/**
 * @since 0.2
 */
function satisfy(callable $predicate) : Parser
{
    return parser(function (string $input) use ($predicate) : ParseResult {
        if ((strlen($input) === 0)) return fail("input", "EOF");
        $token = Str::head($input);
        return $predicate($token)
            ? succeed($token, Str::tail($input))
            : fail("satisfy(predicate)", $token);
    });
}