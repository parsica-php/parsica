<?php declare(strict_types=1);

namespace Mathias\ParserCombinators;

function char(string $char)
{
    return fn($input): ParseResult => (head($input[0]) === $char)
        ? succeed($char, tail($input))
        : fail("char($char)");
}
