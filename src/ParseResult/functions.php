<?php declare(strict_types=1);

namespace Mathias\ParserCombinator\ParseResult;


/**
 * @param mixed $parsed
 */
function succeed($parsed, string $remaining): ParseResult
{
    return new ParseSuccess($parsed, $remaining);
}

function fail(string $expected, string $got): ParseResult
{
    return new ParseFailure($expected, $got);
}

function discard(string $remaining) : ParseResult
{
    return new DiscardResult($remaining);
}