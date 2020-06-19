<?php declare(strict_types=1);

namespace Mathias\ParserCombinator;

use Mathias\ParserCombinator\Internal\Assert;
use Mathias\ParserCombinator\Internal\Succeed;
use function Mathias\ParserCombinator\ParseResult\{succeed};

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
 * A parser that will have the argument as its output, no matter what the input was. It doesn't consume any input.
 *
 * @template T
 *
 * @param T $output
 *
 * @return Parser<T>
 */
function pure($output): Parser
{
    return Parser::make(fn(string $input) => new Succeed($output, $input));
}

/**
 * Optionally parse something, but still succeed if the thing is not there
 *
 * @template T
 *
 * @param Parser<T> $parser
 *
 * @return Parser<T>
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
 * @param Parser<T1> $first
 * @param Parser<T2> $second
 *
 * @return Parser<T2>
 * @template T1
 * @template T2
 * @see Parser::sequence()
 *
 */
function sequence(Parser $first, Parser $second): Parser
{
    return $first->sequence($second)->label('sequence');
}

/**
 * Sequence two parsers, and return the output of the first one.
 *
 * @see Parser::thenIgnore()
 */
function keepFirst(Parser $first, Parser $second): Parser
{
    return $first->bind(fn($a) => $second->sequence(pure($a)))->label('keepFirst');
}

/**
 * Sequence two parsers, and return the output of the second one.
 */
function keepSecond(Parser $first, Parser $second): Parser
{
    return $first->sequence($second)->label("keepSecond");
}

/**
 * Either parse the first thing or the second thing
 *
 * @param Parser<T> $first
 * @param Parser<T> $second
 *
 * @return Parser<T>
 * @see Parser::or()
 *
 * @template T
 *
 */
function either(Parser $first, Parser $second): Parser
{
    return $first->or($second)->label('either');
}

/**
 * Combine the parser with another parser of the same type, which will cause the results to be appended.
 *
 * @template T
 *
 * @param Parser<T> $left
 * @param Parser<T> $right
 *
 * @return Parser<T>
 */
function append(Parser $left, Parser $right): Parser
{
    return Parser::make(function (string $input) use ($left, $right): ParseResult {
        $r1 = $left->run($input);
        $r2 = $r1->continueWith($right);
        return $r1->append($r2);
    });
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
    Assert::atLeastOneArg($parsers, "assemble()");
    $first = array_shift($parsers);
    return array_reduce($parsers, __NAMESPACE__.'\\append', $first)
        ->label('assemble()');
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
        fn(Parser $parser): Parser => $parser->map($toArray),
        $parsers
    );
    return assemble(...$arrayParsers)->label('collect()');
}

/**
 * Tries each parser one by one, returning the result of the first one that succeeds.
 *
 * @param Parser<T>[] $parsers
 *
 * @return Parser<T>
 *
 * @template T
 */
function any(Parser ...$parsers): Parser
{
    return array_reduce(
        $parsers,
        fn(Parser $first, Parser $second): Parser => $first->or($second),
        failure()
    )->label('any');
}

/**
 * One or more repetitions of Parser
 *
 * @template T
 *
 * @param Parser<T> $parser
 *
 * @return Parser<T>
 *
 */
function atLeastOne(Parser $parser): Parser
{
    $rec = recursive();
    return $rec->recurse($parser->append(optional($rec)));
}

/**
 * Parse something exactly n times
 *
 * @TODO this could probably be more elegant.
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
        array_fill(0, $n - 1, $parser),
        fn(Parser $l, Parser $r): Parser => $l->append($r),
        $parser
    )->label("repeat($n)");
}

/**
 * Parse something zero or more times, and output an array of the successful outputs.
 */
function many(Parser $parser): Parser
{
    return some($parser)->or(pure([]));
}

/**
 * Parse something one or more times, and output an array of the successful outputs.
 *
 * @psalm-suppress MixedArgumentTypeCoercion
 */
function some(Parser $parser): Parser
{
    $rec = recursive();
    return $parser->map(fn($x) => [$x])->append(
        $rec->recurse(
            either(
                $parser->map(fn($x) => [$x])->append($rec),
                pure([])
            )
        )
    );
}

/**
 * Parse $open, followed by $middle, followed by $close, and return the result of $middle. Useful for eg. "(value)".
 *
 * @template TO
 * @template TM
 * @template TC
 *
 * @param Parser<TO> $open
 * @param Parser<TM> $middle
 * @param Parser<TC> $close
 *
 * @return Parser<TM>
 */
function between(Parser $open, Parser $middle, Parser $close): Parser
{
    return keepSecond($open, keepFirst($middle, $close));
}
