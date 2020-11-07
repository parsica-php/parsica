<?php declare(strict_types=1);
/*
 * This file is part of the Parsica library.
 *
 * Copyright (c) 2020 Mathias Verraes <mathias@verraes.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Verraes\Parsica\Expression;

use Verraes\Parsica\Internal\Assert;
use Verraes\Parsica\Parser;
use function Cypress\Curry\curry;
use function Verraes\Parsica\choice;
use function Verraes\Parsica\collect;
use function Verraes\Parsica\Internal\FP\foldr;
use function Verraes\Parsica\keepFirst;
use function Verraes\Parsica\many;
use function Verraes\Parsica\map;
use function Verraes\Parsica\pure;

/**
 * @internal
 * @template TSymbol
 * @template TExpressionAST
 */
final class RightAssoc implements ExpressionType
{
    /** @var non-empty-list<BinaryOperator<TSymbol, TExpressionAST>> */
    private array $operators;

    /**
     * @psalm-param non-empty-list<BinaryOperator<TSymbol, TExpressionAST>> $operators
     */
    function __construct(array $operators)
    {
        /** @psalm-suppress RedundantCondition */
        Assert::nonEmptyList($operators, "RightAssoc expects at least one Operator");
        $this->operators = $operators;
    }

    /**
     * @psalm-param Parser<TExpressionAST> $previousPrecedenceLevel
     * @psalm-return Parser<TExpressionAST>
     */
    public function buildPrecedenceLevel(Parser $previousPrecedenceLevel): Parser
    {
        /**
         * @psalm-var list<Parser<callable(Parser<TExpressionAST>):Parser<TExpressionAST>>> $operatorParsers
         */
        $operatorParsers = [];
        foreach ($this->operators as $operator) {
            $operatorParsers[] =
                pure(curry($operator->transform()))
                    ->apply(keepFirst($previousPrecedenceLevel, $operator->symbol()))
                    ->label($operator->label());
        }

        return map(
            collect(
                many(choice(...$operatorParsers)),
                $previousPrecedenceLevel
            ),

            /**
             * @psalm-param array{0: list<callable(TExpressionAST, 1: TExpressionAST):TExpressionAST>} $o
             * @psalm-return TExpressionAST
             */
            fn(array $o) => foldr(
                $o[0],

                /**
                 * @psalm-param callable(TExpressionAST):TExpressionAST $appl
                 * @psalm-param TExpressionAST $acc
                 * @psalm-return TExpressionAST
                 */
                fn(callable $appl, $acc) => $appl($acc),
                $o[1]
            )
        );

    }
}
