<?php declare(strict_types=1);
/**
 * This file is part of the Parsica library.
 *
 * Copyright (c) 2020 Mathias Verraes <mathias@verraes.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Parsica\Parsica\Expression;

use Parsica\Parsica\Internal\Assert;
use Parsica\Parsica\Parser;
use function Parsica\Parsica\Internal\FP\foldl;

/**
 * Build an expression parser from a term parser and an expression table.
 *
 * @api
 *
 * @template TTerm
 * @template TExpressionAST
 *
 * @psalm-param Parser<TTerm> $term
 * @psalm-param list<ExpressionType> $expressionTable
 *
 * @psalm-return Parser<TExpressionAST>
 */
function expression(Parser $term, array $expressionTable): Parser
{
    /** @psalm-var Parser<TExpressionAST> $parser */
    $parser = foldl(
        $expressionTable,
        fn(Parser $previous, ExpressionType $next) => $next->buildPrecedenceLevel($previous),
        $term
    );
    return $parser;
}

/**
 * A binary operator in an expression. The operands of the expression will be passed into $transform to produce the
 * output of the expression parser.
 *
 * @api
 *
 * @template TSymbol
 * @template TExpressionAST
 * @psalm-param Parser<TSymbol> $symbol
 * @psalm-param callable(TExpressionAST, TExpressionAST):TExpressionAST $transform
 * @psalm-param string $label
 *
 * @psalm-return BinaryOperator<TSymbol, TExpressionAST>
 */
function binaryOperator(Parser $symbol, callable $transform, string $label = ""): BinaryOperator
{
    return new BinaryOperator($symbol, $transform, $label);
}

/**
 * A unary operator in an expression. The operands of the expression will be passed into $transform to produce the
 * output of the expression parser.
 *
 * @api
 *
 * @template TSymbol
 * @template TExpressionAST
 * @psalm-param Parser<TSymbol> $symbol
 * @psalm-param callable(TExpressionAST):TExpressionAST $transform
 * @psalm-param string $label
 *
 * @return UnaryOperator<TSymbol, TExpressionAST>
 */
function unaryOperator(Parser $symbol, callable $transform, string $label = ""): UnaryOperator
{
    return new UnaryOperator($symbol, $transform, $label);
}

/**
 * @api
 * @template TSymbol
 * @template TExpressionAST
 * @psalm-param non-empty-list<BinaryOperator<TSymbol, TExpressionAST>> $operators
 * @psalm-return LeftAssoc<TSymbol, TExpressionAST>
 */
function leftAssoc(BinaryOperator ...$operators): LeftAssoc
{
    Assert::nonEmptyList($operators, "LeftAssoc expects at least one Operator");
    return new LeftAssoc($operators);
}

/**
 * @api
 * @template TSymbol
 * @template TExpressionAST
 * @psalm-param non-empty-list<BinaryOperator<TSymbol,TExpressionAST>> $operators
 * @psalm-return RightAssoc<TSymbol, TExpressionAST>
 */
function rightAssoc(BinaryOperator ...$operators): RightAssoc
{
    Assert::nonEmptyList($operators, "RightAssoc expects at least one Operator");
    return new RightAssoc($operators);
}

/**
 * @api
 *
 * @template TSymbol
 * @template TExpressionAST
 * @psalm-param BinaryOperator<TSymbol, TExpressionAST> $operator
 * @psalm-return NonAssoc<TSymbol, TExpressionAST>
 */
function nonAssoc(BinaryOperator $operator): NonAssoc
{
    return new NonAssoc($operator);
}

/**
 * @api
 *
 * @template TSymbol
 * @template TExpressionAST
 *
 * @psalm-param non-empty-list<UnaryOperator<TSymbol, TExpressionAST>> $operators
 * @psalm-return Prefix<TSymbol, TExpressionAST>
 */
function prefix(UnaryOperator ...$operators): Prefix
{
    Assert::nonEmptyList($operators, "Prefix expects at least one Operator");
    return new Prefix($operators);
}

/**
 * @api
 *
 * @template TSymbol
 * @template TExpressionAST
 * @psalm-param non-empty-list<UnaryOperator<TSymbol, TExpressionAST>> $operators
 * @psalm-return Postfix<TSymbol, TExpressionAST>
 */
function postfix(UnaryOperator ...$operators): Postfix
{
    Assert::nonEmptyList($operators, "Postfix expects at least one Operator");
    return new Postfix($operators);
}
