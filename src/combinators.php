<?php declare(strict_types=1);
/**
 * This file is part of the Parsica library.
 *
 * Copyright (c) 2020 Mathias Verraes <mathias@verraes.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Verraes\Parsica;

use Verraes\Parsica\Internal\Assert;
use Verraes\Parsica\Internal\Fail;
use Verraes\Parsica\Internal\Stream;
use Verraes\Parsica\Internal\Succeed;

/**
 * Identity parser, returns the Parser as is.
 *
 * @psalm-param Parser<T> $parser
 *
 * @psalm-return Parser<T>
 * @api
 *
 * @template T
 *
 */
function identity(Parser $parser): Parser
{
    return $parser;
}

/**
 * A parser that will have the argument as its output, no matter what the input was. It doesn't consume any input.
 *
 * @psalm-param T $output
 *
 * @psalm-return Parser<T>
 * @api
 *
 * @template T
 *
 */
function pure($output): Parser
{
    return Parser::make("<pure>", fn(Stream $input) => new Succeed($output, $input));
}

/**
 * Optionally parse something, but still succeed if the thing is not there
 *
 * @psalm-param Parser<T> $parser
 *
 * @psalm-return Parser<T>
 * @api
 * @template T
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
 * @psalm-param Parser<T1> $parser
 * @psalm-param callable(T1) : Parser<T2> $f
 *
 * @psalm-return Parser<T2>
 * @api
 * @template T1
 * @template T2
 *
 */
function bind(Parser $parser, callable $f): Parser
{
    return $parser->bind($f);
}

/**
 * Parse something, then follow by something else. Ignore the result of the first parser and return the result of the
 * second parser.
 *
 * @psalm-param Parser<T1> $first
 * @psalm-param Parser<T2> $second
 *
 * @psalm-return Parser<T2>
 * @template T1
 * @template T2
 * @api
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
 * @api
 * @see Parser::thenIgnore()
 */
function keepFirst(Parser $first, Parser $second): Parser
{
    return $first->bind(fn($a) => $second->sequence(pure($a)))->label('keepFirst');
}

/**
 * Sequence two parsers, and return the output of the second one.
 *
 * @api
 */
function keepSecond(Parser $first, Parser $second): Parser
{
    return $first->sequence($second)->label("keepSecond");
}

/**
 * Either parse the first thing or the second thing
 *
 * @psalm-param Parser<T> $first
 * @psalm-param Parser<T> $second
 *
 * @psalm-return Parser<T>
 * @api
 *
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
 * @psalm-param Parser<T> $left
 * @psalm-param Parser<T> $right
 *
 * @psalm-return Parser<T>
 * @api
 * @template T
 *
 */
function append(Parser $left, Parser $right): Parser
{
    return Parser::make($right->getLabel(), function (Stream $input) use ($left, $right): ParseResult {
        $r1 = $left->run($input);
        $r2 = $r1->continueWith($right);
        return $r1->append($r2);
    });
}

/**
 * Append all the passed parsers.
 *
 * @psalm-param list<Parser<T>> $parsers
 *
 * @psalm-return Parser<T>
 * @api
 * @template T
 *
 */
function assemble(Parser ...$parsers): Parser
{
    Assert::atLeastOneArg($parsers, "assemble()");
    $first = array_shift($parsers);
    return array_reduce($parsers, __NAMESPACE__ . '\\append', $first)
        ->label('assemble()');
}

/**
 * Parse into an array that consists of the results of all parsers.
 *
 * @psalm-param list<Parser<T>> $parsers
 *
 * @psalm-return Parser<T>
 * @api
 * @template T
 *
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
 * @psalm-param Parser<T>[] $parsers
 *
 * @psalm-return Parser<T>
 *
 * @template T
 * @api
 */
function any(Parser ...$parsers): Parser
{
    if(empty($parsers)) {
        throw new \InvalidArgumentException("any() expects at least one parser");
    }

    $labels = array_map(fn(Parser $p) : string => $p->getLabel(), $parsers);
    $label = implode(' or ', $labels);

    return array_reduce(
        $parsers,
        fn(Parser $first, Parser $second): Parser => $first->or($second),
        failure("")
    )->label($label);
}

/**
 * Tries each parser one by one, returning the result of the first one that succeeds.
 *
 * Alias for {@see any()}
 *
 * @psalm-param Parser<T>[] $parsers
 *
 * @psalm-return Parser<T>
 *
 * @template T
 * @api
 */
function choice(Parser ...$parsers): Parser
{
    return any(...$parsers);
}

/**
 * One or more repetitions of Parser
 *
 * @psalm-param Parser<T> $parser
 *
 * @psalm-return Parser<T>
 *
 * @api
 * @template T
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
 * @template T
 *
 * @psalm-param Parser<T> $parser
 *
 * @psalm-return Parser<T>
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
 * Parse something exactly n times and return as an array
 *
 * @TODO This doesn't feel very elegant.
 *
 * @template T
 *
 * @psalm-param Parser<T> $parser
 *
 * @psalm-return Parser<T>
 *
 */
function repeatList(int $n, Parser $parser): Parser
{

    $parser = $parser->map(fn($output) => [$output]);

    $parsers = array_fill(0, $n - 1, $parser);
    return array_reduce(
        $parsers,
        fn(Parser $l, Parser $r): Parser => $l->append($r),
        $parser
    )->label("repeatList($n)");
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
    $pArray = $parser->map(fn($x) => [$x]);
    return $pArray->append(
        $rec->recurse(
            either(
                $pArray->append($rec),
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
 * @psalm-param Parser<TO> $open
 * @psalm-param Parser<TC> $close
 * @psalm-param Parser<TM> $middle
 *
 * @psalm-return Parser<TM>
 */
function between(Parser $open, Parser $close, Parser $middle): Parser
{
    return keepSecond($open, keepFirst($middle, $close))->label('between');
}

/**
 * Parses zero or more occurrences of $parser, separated by $separator. Returns a list of values.
 *
 * The sepBy parser always succeed, even if it doesn't find anything. Use {@see sepBy1()} if you want it to find at
 * least one value.
 *
 * @template TS
 * @template T
 *
 * @psalm-param Parser<TS> $separator
 * @psalm-param Parser<T>  $parser
 *
 * @psalm-return Parser<list<T>>
 */
function sepBy(Parser $separator, Parser $parser): Parser
{
    return sepBy1($separator, $parser)->or(pure([]))->label('sepBy');
}


/**
 * Parses one or more occurrences of $parser, separated by $separator. Returns a list of values.
 *
 * @template TS
 * @template T
 *
 * @psalm-param Parser<TS> $separator
 * @psalm-param Parser<T>  $parser
 *
 * @psalm-return Parser<list<T>>
 *
 * @psalm-suppress MissingClosureReturnType
 */
function sepBy1(Parser $separator, Parser $parser): Parser
{
    /** @psalm-suppress MissingClosureParamType */
    $prepend = fn($x) => fn(array $xs): array => array_merge([$x], $xs);
    return pure($prepend)->apply($parser)->apply(many($separator->sequence($parser)))->label('sepBy1');
}

/**
 * notFollowedBy only succeeds when $parser fails. It never consumes any input.
 *
 * Example:
 *
 * `string("print")` will also match "printXYZ"
 *
 * `keepFirst(string("print"), notFollowedBy(alphaNumChar()))` will match "print something" but not "printXYZ something"
 *
 * @psalm-param Parser<T> $parser
 *
 * @psalm-return Parser<string>
 * @see Parser::notFollowedBy()
 *
 * @template T
 *
 */
function notFollowedBy(Parser $parser): Parser
{
    /** @var Parser<string> $p */
    $label = "notFollowedBy({$parser->getLabel()})";

    $p = Parser::make($label, function (Stream $input) use ($label, $parser): ParseResult {
        $result = $parser->run($input);
        return $result->isSuccess()
            ? new Fail($label, $input)
            : new Succeed("", $input);
    });
    return $p;
}
