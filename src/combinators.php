<?php declare(strict_types=1);

namespace Mathias\ParserCombinator;

use Mathias\ParserCombinator\Parser\Parser;
use Mathias\ParserCombinator\ParseResult\ParseResult;
use function Mathias\ParserCombinator\ParseResult\{fail, succeed};

/**
 * Identity parser, returns the Parser as is.
 *
 * @template T
 *
 * @param Parser<T> $parser
 *
 * @return Parser<T>
 */
function identity(Parser $parser): Parser
{
    return $parser;
}

/**
 * Parse something, strip it from the remaining input, but do not return anything.
 *
 * @template T
 *
 * @param Parser<T> $parser
 *
 * @return Parser<string>
 */
function ignore(Parser $parser): Parser
{
    return $parser->ignore();
}

/**
 * Optionally parse something, but still succeed if the thing is not there
 *
 * @template T
 *
 * @param Parser<T> $parsed
 *
 * @return Parser<T|string>
 * @deprecated 0.2
 */
function optional(Parser $parser): Parser
{
    return $parser->optional();
}

/**
 * Parse something, then follow by something else. Return the result of the second parser.
 *
 * @param Parser<T1> $first
 * @param Parser<T2> $second
 *
 * @return Parser<T2>
 * @deprecated 0.2
 * @template T1
 * @template T2
 */
function seq(Parser $first, Parser $second): Parser
{
    return $first->followedBy($second);
}

/**
 * Either parse the first thing or the second thing
 *
 * @template T
 *
 * @param Parser<T> $first
 * @param Parser<T> $second
 *
 * @return Parser<T>
 * @deprecated 0.2
 */
function either(Parser $first, Parser $second): Parser
{
    return $first->or($second);
}

/**
 * Parse into an array that consists of the results of both parsers.
 *
 * @template T
 *
 * @param Parser<T> $first
 * @param Parser<T> $second
 *
 * @return Parser
 * @deprecated 0.2
 */
function collect(Parser $first, Parser $second): Parser
{
    return new Parser(function (string $input) use ($first, $second) : ParseResult {
        $r1 = $first->run($input);
        if ($r1->isSuccess()) {
            $r2 = $second->run($r1->remaining());
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
 * Tries each parser one by one
 *
 * @param Parser<TParsed>[] $parsers
 *
 * @return Parser<TParsed>
 * @deprecated 0.2
 *
 * @template TParsed
 *
 */
function any(Parser ...$parsers): Parser
{
    return new Parser(function (string $input) use ($parsers): ParseResult {
        $expectations = [];
        foreach ($parsers as $parser) {
            $r = $parser->run($input);
            if ($r->isSuccess()) {
                return $r;
            } else {
                $expectations[] = $r->expected();
            }
        }
        return fail("any(" . implode(", ", $expectations) . ")", "@TODO");
    });
}

/**
 * One or more repetitions of Parser
 *
 * @param Parser<TParsed> $parser
 *
 * @return Parser<TParsed>
 * @deprecated 0.2
 *
 * @template TParsed
 *
 */
function atLeastOne(Parser $parser): Parser
{
    return new Parser(function (string $input) use ($parser): ParseResult {
        $r = $parser->run($input);
        if ($r->isFail()) return $r;

        while ($r->isSuccess()) {
            $next = $parser->continueFrom($r);
            if ($next->isFail()) return $r;
            $r = $r->mappend($next);
        }
        return $r;
    });
}