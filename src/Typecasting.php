<?php declare(strict_types=1);

namespace Mathias\ParserCombinators;

use Mathias\ParserCombinators\Infra\Parser;
use Mathias\ParserCombinators\Infra\ParseResult;

/**
 * Transform the parsed string into something else using a callable
 */
function into(Parser $parser, callable $transform): Parser
{
    return parser(function ($input) use ($parser, $transform) : ParseResult {
        $r = $parser($input);
        if ($r->isSuccess()) {
            return succeed($transform($r->parsed()), $r->remaining());
        }
        return $r;
    });
}

/**
 * Transform the parsed string into an object of type $className
 */
function intoNew(Parser $parser, string $className): Parser
{
    return parser(function ($input) use ($parser, $className) : ParseResult {
        $r = $parser($input);
        if ($r->isSuccess()) {
            return succeed(new $className($r->parsed()), $r->remaining());
        }
        return $r;
    });
}

