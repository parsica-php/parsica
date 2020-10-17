<?php declare(strict_types=1);
/**
 * This file is part of the Parsica library.
 *
 * Copyright (c) 2020 Mathias Verraes <mathias@verraes.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Verraes\Parsica\Expression;

use Verraes\Parsica\Parser;

/**
 * Build an expression parser from a term parser and an expression table.
 *
 * @api
 *
 * @template T
 *
 * @psalm-param Parser<T> $term
 * @psalm-param ExpressionType[] $expressionTable
 *
 * @psalm-return Parser<T>
 */
function expression(Parser $term, array $expressionTable): Parser
{
    return array_reduce(
        $expressionTable,
        fn(Parser $previous, ExpressionType $next) => $next->buildPrecedenceLevel($previous),
        $term
    );
}

/*
 * An binary operator in an expression. The operands of the expression will be passed into $transform to produce the
 * output of the expression parser.
 *
 * @api
 *
 * @template TSymbol
 * @template TTermL
 * @template TTermR
 * @template TOutput
 * @psalm-param Parser<TSymbol> $symbol
 * @psalm-param callable(TTermL, TTermR):TOutput $transform
 * @psalm-param string $label
 *
 * @return BinaryOperator<TSymbol, TTermL, TTermR, TOutput>
 */
function binaryOperator(Parser $symbol, callable $transform, string $label = ""): BinaryOperator
{
    return new BinaryOperator($symbol, $transform, $label);
}

/*
 * An unary operator in an expression. The operands of the expression will be passed into $transform to produce the
 * output of the expression parser.
 *
 * @api
 *
 * @template TSymbol
 * @template TTerm
 * @template TOutput
 * @psalm-param Parser<TSymbol> $symbol
 * @psalm-param callable(TTerm):TOutput $transform
 * @psalm-param string $label
 *
 * @return BinaryOperator<TSymbol, TTerm, TOutput>
 */
function unaryOperator(Parser $symbol, callable $transform, string $label = ""): UnaryOperator
{
    return new UnaryOperator($symbol, $transform, $label);
}

/*
 * @api
 */
function leftAssoc(BinaryOperator ...$operators): LeftAssoc
{
    return new LeftAssoc($operators);
}

/*
 * @api
 */
function rightAssoc(BinaryOperator ...$operators): RightAssoc
{
    return new RightAssoc($operators);
}

/*
 * @api
 */
function nonAssoc(BinaryOperator $operator): NonAssoc
{
    return new NonAssoc($operator);
}

/*
 * @api
 */
function prefix(UnaryOperator ...$operators): Prefix
{
    return new Prefix($operators);
}

/*
 * @api
 */
function postfix(UnaryOperator ...$operators): Postfix
{
    return new Postfix($operators);
}
