<?php declare(strict_types=1);

namespace Mathias\ParserCombinators;

use Mathias\ParserCombinators\Infra\Parser;
use Mathias\ParserCombinators\Infra\ParseResult;
use Webmozart\Assert\Assert;

function char(string $char): Parser
{
    Assert::length($char, 1);
    return parser(fn($input): ParseResult => (head($input) === $char)
        ? succeed($char, tail($input))
        : fail("char($char)")
    );
}


function string(string $str): Parser
{
    Assert::minLength($str, 1);
    return 1 == strlen($str)
        ? char($str)
        : char(head($str))
            ->seq(
                string(tail($str))
            );
}
