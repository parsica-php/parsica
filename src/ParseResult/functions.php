<?php declare(strict_types=1);

namespace Mathias\ParserCombinator\ParseResult;

use Mathias\ParserCombinator\Parser;
use Mathias\ParserCombinator\ParseResult\ParseResult;
use Mathias\ParserCombinator\ParseResult\ParserFailure;
use Mathias\ParserCombinator\ParseResult\ParseSuccess;

function parser(callable $f): Parser
{
    return new Parser($f);
}

/**
 * @param mixed $parsed
 */
function succeed($parsed, string $output): ParseResult
{
    return new ParseSuccess($parsed, $output);
}

function fail(string $expectation): ParseResult
{
    return new ParserFailure($expectation);
}
