<?php declare(strict_types=1);

namespace Mathias\ParserCombinators;

use Mathias\ParserCombinators\Infra\Parser;
use Mathias\ParserCombinators\Infra\ParseResult;

/**
 * Identity function, returns the Parser. Sometimes useful.
 */
function id(Parser $parser): Parser
{
    return $parser;
}

/**
 * Parse something, strip it from the remaining string, but do not return anything
 */
function ignore(Parser $parser): Parser
{
    return $parser->ignore();
}

/**
 * Optionally parse something, but still succeed if the thing is not there
 */
function optional(Parser $parser): Parser
{
    return $parser->optional();
}

/**
 * Parse something, then follow by something else.
 */
function seq(Parser $first, Parser $second): Parser
{
    return $first->followedBy($second);
}

/**
 * Either parse the first thing or the second thing
 */
function either(Parser $first, Parser $second): Parser
{
    return $first->or($second);
}

function collect(Parser $first, Parser $second): Parser
{
    // @TODO ignoring failures for now
    return parser(function (string $input) use ($first, $second) : ParseResult {
        $r1 = $first($input);
        if ($r1->isSuccess()) {
            $r2 = $second($r1->remaining());
            if ($r2->isSuccess()) {
                return succeed([$r1->parsed(), $r2->parsed()], $r2->remaining());
            } else {
                return $r2;
            }
        } else {
            return $r1;
        }

    });
}

/**
 * Transform the parsed string into something else using a callable.
 */
function into1(Parser $parser, callable $transform): Parser
{
    return $parser->into1($transform);
}

/**
 * Transform the parsed string into an object of type $className
 */
function intoNew1(Parser $parser, string $className): Parser
{
    return $parser->intoNew1($className);
}

/**
 * Tries each parser one by one
 */
function any(Parser ...$parsers): Parser
{
    return parser(function ($input) use ($parsers): ParseResult {
        foreach ($parsers as $parser) {
            $r = $parser($input);
            if ($r->isSuccess()) {
                return $r;
            }
        }
        return fail("@TODO failures for " . __METHOD__);
    });
}

/**
 * One or more repetitions of Parser
 */
function atLeastOne($parser) :Parser{
    return parser(function ($input) use ($parser): ParseResult {
        $r = $parser($input);
        if(!$r->isSuccess()) return $r;
        $parsed = $r->parsed();
        while (true) {
            $next = $parser($r->remaining());
            if($next->isSuccess()) {
                $parsed .= $next->parsed();
                $r = $next;
            } else {
                return succeed($parsed, $r->remaining());
            }
        }
    });
}