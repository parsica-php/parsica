<?php declare(strict_types=1);

namespace Mathias\ParserCombinators;

use Mathias\ParserCombinators\Infra\Parser;
use Mathias\ParserCombinators\Infra\ParseResult;
use Mathias\ParserCombinators\Infra\ParserFailure;
use Mathias\ParserCombinators\Infra\ParseSuccess;

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

/**
 * @return mixed
 */
function runparser(Parser $parser, string $input)
{
    $result = $parser($input);
    return $result->parsed();
}
