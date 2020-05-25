<?php declare(strict_types=1);

namespace Mathias\ParserCombinators;

function either($left, $right)
{
    return function ($input) use ($left, $right) : ParseResult {
        $leftr = $left($input);
        if ($leftr->isSuccess()) {
            return $leftr;
        }

        $rightr = $right($input);
        if ($rightr->isSuccess()) {
            return $rightr;
        }

        $expectation = "either (\n\t{$leftr->expectation()}\n\tor\n\t{$rightr->expectation()}\n)";
        return fail($expectation);
    };
}
