<?php declare(strict_types=1);

namespace Mathias\ParserCombinators;

use Mathias\ParserCombinators\Infra\Parser;
use Mathias\ParserCombinators\Infra\ParseResult;

/**
 * Parse something, strip it from the remaining string, but do not return anything
 */
function ignore(Parser $parser): Parser
{
    return $parser->into(fn($_) => "");
}

/**
 * Optionally parse something, but still succeed if the thing is not there
 */
function optional(Parser $parser): Parser
{
    return parser(function ($input) use ($parser) : ParseResult {
        $r1 = $parser($input);
        if ($r1->isSuccess()) {
            return $r1;
        } else {
            return succeed("", $input);
        }
    });
}

/**
 * Parse something, then follow by something else.
 */
function seq(Parser $first, Parser $second): Parser
{
    return parser(function ($input) use ($first, $second) : ParseResult {
        $r1 = $first($input);
        if ($r1->isSuccess()) {
            $r2 = $second($r1->remaining());
            if ($r2->isSuccess()) {
                return succeed($r1->parsed() . $r2->parsed(), $r2->remaining());
            }
            return fail("seq ({$r1->parsed()} {$r2->expectation()})");
        }
        return fail("seq ({$r1->expectation()} ...)");
    });
}

/**
 * Either parse the first thing or the second thing
 */
function either(Parser $first, Parser $second): Parser
{
    return parser(function ($input) use ($first, $second) : ParseResult {
        $r1 = $first($input);
        if ($r1->isSuccess()) {
            return $r1;
        }

        $r2 = $second($input);
        if ($r2->isSuccess()) {
            return $r2;
        }

        $expectation = "either ({$r1->expectation()} or {$r2->expectation()})";
        return fail($expectation);
    });
}

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

