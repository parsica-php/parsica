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
 * Parse something, strip it from the remaining input, but discard the output.
 *
 * @template T
 *
 * @param Parser<T> $parser
 *
 * @return Parser<T>
 */
function ignore(Parser $parser): Parser
{
    return $parser->ignore();
}

/**
 * A parser that will have the argument as its output, no matter what the input was. It doesn't consume any input.
 *
 * @template T
 * @param T $output
 * @return Parser<T>
 */
function pure($output) : Parser
{
    return Parser::make(fn(string $input) => succeed($output, $input));
}

/**
 * Optionally parse something, but still succeed if the thing is not there
 *
 * @template T
 *
 * @param Parser<T> $parser
 *
 * @return Parser<T|string>
 */
function optional(Parser $parser): Parser
{
    return $parser->optional();
}

/**
 * Create a parser that takes the output from the first parser (if successful) and feeds it to the callable. The callable
 * must return another parser. If the first parser fails, the first parser is returned.
 *
 * This is a monadic bind aka flatmap.
 *
 * @template T1
 * @template T2
 *
 * @param Parser<T1> $parser
 * @param callable(T1) : Parser<T2> $f
 *
 * @return Parser<T2>
 */
function bind(Parser $parser, callable $f): Parser
{
    return $parser->bind($f);
}

/**
 * Parse something, then follow by something else. Ignore the result of the first parser and return the result of the
 * second parser.
 *
 * @see Parser::sequence()
 *
 * @param Parser<T1> $first
 * @param Parser<T2> $second
 *
 * @return Parser<T2>
 * @template T1
 * @template T2
 */
function sequence(Parser $first, Parser $second): Parser
{
    return $first->sequence($second)->label('sequence');
}

/**
 * Either parse the first thing or the second thing
 *
 * @see Parser::or()
 *
 * @template T
 *
 * @param Parser<T> $first
 * @param Parser<T> $second
 *
 * @return Parser<T>
 */
function either(Parser $first, Parser $second): Parser
{
    return $first->or($second);
}

/**
 * Append all the passed parsers.
 *
 * @template T
 *
 * @param list<Parser<T>> $parsers
 *
 * @return Parser<T>
 */
function assemble(Parser ...$parsers): Parser
{
    return array_reduce(
        $parsers,
        fn(Parser $l, Parser $r): Parser => $l->append($r),
        nothing()
    )->label('assemble()');
}

/**
 * Parse into an array that consists of the results of all parsers.
 *
 * @template T
 *
 * @param list<Parser<T>> $parsers
 *
 * @return Parser<T>
 */
function collect(Parser ...$parsers): Parser
{
    /** @psalm-suppress MissingClosureParamType */
    $toArray = fn($v): array => [$v];
    $arrayParsers = array_map(
        fn(Parser $parser): Parser => $parser->fmap($toArray),
        $parsers
    );
    return assemble(...$arrayParsers)->label('collect()');
}

/**
 * Tries each parser one by one, returning the result of the first one that succeeds.
 * @deprecated 0.2 Do we have tests for this?
 *
 * @param Parser<TParsed>[] $parsers
 *
 * @return Parser<TParsed>
 *
 * @template TParsed
 */
function any(Parser ...$parsers): Parser
{
    return array_reduce(
        $parsers,
        fn(Parser $first, Parser $second):Parser => $first->or($second),
        failure()
    )->label('any');
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
    return Parser::make(function (string $input) use ($parser): ParseResult {
        $r = $parser->run($input);
        if ($r->isFail()) return $r;

        while ($r->isSuccess()) {
            $next = $parser->continueFrom($r);
            if ($next->isFail()) return $r;
            $r = $r->append($next);
        }
        return $r;
    });
}

/**
 * Parse something exactly n times
 *
 * @template T
 *
 * @param Parser<T> $parser
 *
 * @return Parser<T>
 *
 */
function repeat(int $n, Parser $parser): Parser
{
    return array_reduce(
        array_fill(0, $n, $parser),
        fn(Parser $l, Parser $r): Parser => $l->append($r),
        nothing()
    )->label("repeat($n)");
}