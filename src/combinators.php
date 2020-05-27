<?php declare(strict_types=1);

namespace Mathias\ParserCombinator;

use function Mathias\ParserCombinator\ParseResult\{fail, parser, succeed};
use Mathias\ParserCombinator\ParseResult\ParseResult;

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
 *
 * @template T1
 * @template T2
 *
 * @param Parser<T1> $parser
 * @param callable(T1):T2 $transform
 *
 * @return Parser<T2>
 *
 * @see Parser::into1()
 */
function into1(Parser $parser, callable $transform): Parser
{
    return parser(
    /**
     * @return ParseResult<T2>
     */
        function (string $input) use ($parser, $transform) : ParseResult {
            $r = $parser($input);
            if ($r->isSuccess()) {
                return succeed($transform($r->parsed()), $r->remaining());
            }
            return $r;
        }
    );
}

/**
 * Transform the parsed string into an object of type $className
 *
 * @template T1
 * @template T2
 *
 * @param Parser<T1> $parser
 * @param class-string<T2> $className
 *
 * @return Parser<T2>
 */
function intoNew1(Parser $parser, string $className): Parser
{
    return $parser->intoNew1($className);
}

/**
 * Tries each parser one by one
 *
 * @template T
 *
 * @param Parser<T>[] $parsers
 *
 * @return Parser<T>
 */
function any(Parser ...$parsers): Parser
{
    return parser(function (string $input) use ($parsers): ParseResult {
        $expectations = [];
        foreach ($parsers as $parser) {
            $r = $parser($input);
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
 * @template T
 *
 * @param Parser<T> $parser
 *
 * @return Parser<T>
 */
function atLeastOne(Parser $parser): Parser
{
    return parser(function (string $input) use ($parser): ParseResult {
        $r = $parser($input);
        if (!$r->isSuccess()) return $r;
        /** @psalm-var T $parsed */
        $parsed = "";
        do {
            $parsed .= $r->parsed();
            $remaining = $r->remaining();
            $r = $parser($remaining);
        } while ($r->isSuccess());
        return succeed($parsed, $remaining);
    });
}