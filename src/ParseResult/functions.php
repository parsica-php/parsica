<?php declare(strict_types=1);

namespace Mathias\ParserCombinator\ParseResult;


/**
 * @param mixed $output
 */
function succeed($output, string $remainder): ParseResult
{
    return new ParseSuccess($output, $remainder);
}

function fail(string $expected, string $got): ParseResult
{
    return new ParseFailure($expected, $got);
}

function discard(string $remainder): ParseResult
{
    return new DiscardResult($remainder);
}