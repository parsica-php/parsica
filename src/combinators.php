<?php declare(strict_types=1);

namespace Mathias\ParserCombinator;

use function Mathias\ParserCombinator\ParseResult\{fail, parser, succeed};
use Mathias\ParserCombinator\ParseResult\ParseResult;

/**
 * Identity parser, returns the Parser as is.
 */
function identity(Parser $parser): Parser
{
    return $parser;
}

/**
 * @deprecated 0.2
 * Parse something, strip it from the remaining string, but do not return anything
 */
function ignore(Parser $parser): Parser
{
    return $parser->ignore();
}

/**
 * @deprecated 0.2
 * Optionally parse something, but still succeed if the thing is not there
 */
function optional(Parser $parser): Parser
{
    return $parser->optional();
}

/**
 * @deprecated 0.2
 * Parse something, then follow by something else.
 */
function seq(Parser $first, Parser $second): Parser
{
    return $first->followedBy($second);
}

/**
 * @deprecated 0.2
 * Either parse the first thing or the second thing
 */
function either(Parser $first, Parser $second): Parser
{
    return $first->or($second);
}

/**
 * @deprecated 0.2
 */
function collect(Parser $first, Parser $second): Parser
{
    return parser(function (string $input) use ($first, $second) : ParseResult {
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
 * @param Parser<T1> $parser
 * @param callable(T1):T2 $transform
 *
 * @return Parser<T2>
 *
 * @deprecated 0.2
 * Transform the parsed string into something else using a callable.
 *
 * @template T1
 * @template T2
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
            $r = $parser->run($input);
            if ($r->isSuccess()) {
                return succeed($transform($r->parsed()), $r->remaining());
            }
            return $r;
        }
    );
}

/**
 * @param Parser<T1> $parser
 * @param class-string<T2> $className
 *
 * @return Parser<T2>
 * @deprecated 0.2
 * Transform the parsed string into an object of type $className
 *
 * @template T1
 * @template T2
 *
 */
function intoNew1(Parser $parser, string $className): Parser
{
    return $parser->intoNew1($className);
}

/**
 * @param Parser<T>[] $parsers
 *
 * @return Parser<T>
 * @deprecated 0.2
 * Tries each parser one by one
 *
 * @template T
 *
 */
function any(Parser ...$parsers): Parser
{
    return parser(function (string $input) use ($parsers): ParseResult {
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
 * @param Parser<T> $parser
 *
 * @return Parser<T>
 * @deprecated 0.2
 * One or more repetitions of Parser
 *
 * @template T
 *
 */
function atLeastOne(Parser $parser): Parser
{
    return parser(function (string $input) use ($parser): ParseResult {
        $r = $parser->run($input);
        if (!$r->isSuccess()) return $r;
        /** @psalm-var T $parsed */
        $parsed = "";
        do {
            $parsed .= $r->parsed();
            $remaining = $r->remaining();
            $r = $parser->run($remaining);
        } while ($r->isSuccess());
        return succeed($parsed, $remaining);
    });
}