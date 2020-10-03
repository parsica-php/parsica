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
 * An operator in an expression. The operands of the expression will be passed into $transform to produce the output of
 * the expression parser.
 *
 * @api
 *
 * @psalm-param Parser<T> $symbol
 * @psalm-param callable(TTerm):TOutput|callable(TTerm1, TTerm2):TOuput $transform
 * @psalm-param string $label
 *
 * @return Operator<TOutput>
 */
function operator(Parser $symbol, callable $transform, string $label = ""): Operator
{
    return new Operator($symbol, $transform, $label);
}

/*
 * @api
 */
function leftAssoc(Operator ...$operators): LeftAssoc
{
    return new LeftAssoc($operators);
}

/*
 * @api
 */
function rightAssoc(Operator ...$operators): RightAssoc
{
    return new RightAssoc($operators);
}

/*
 * @api
 */
function nonAssoc(Operator $operator): NonAssoc
{
    return new NonAssoc($operator);
}

/*
 * @api
 */
function prefix(Operator ...$operators): Prefix
{
    return new Prefix($operators);
}

/*
 * @api
 */
function postfix(Operator ...$operators): Postfix
{
    return new Postfix($operators);
}
