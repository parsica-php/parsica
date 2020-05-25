<?php declare(strict_types=1);

namespace Mathias\ParserCombinators;

use Mathias\ParserCombinators\Infra\Parser;
use Mathias\ParserCombinators\Infra\ParseResult;

function char(string $char) : Parser
{
    return parser(fn($input): ParseResult => (head($input[0]) === $char)
        ? succeed($char, tail($input))
        : fail("char($char)")
    );
}
