<?php declare(strict_types=1);
/*
 * This file is part of the Parsica library.
 *
 * Copyright (c) 2020 Mathias Verraes <mathias@verraes.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Parsica\Parsica\Expression;

use Parsica\Parsica\Parser;
use function Parsica\Parsica\Curry\curry;
use function Parsica\Parsica\choice;
use function Parsica\Parsica\collect;
use function Parsica\Parsica\Internal\FP\flip;
use function Parsica\Parsica\Internal\FP\foldl;
use function Parsica\Parsica\many;
use function Parsica\Parsica\map;
use function Parsica\Parsica\pure;

/**
 * @internal
 * @template TSymbol
 * @template TExpressionAST
 * @psalm-immutable
 */
final class LeftAssoc implements ExpressionType
{
    /** @psalm-var non-empty-list<BinaryOperator<TSymbol, TExpressionAST>> */
    private array $operators;

    /**
     * @internal
     * @psalm-param non-empty-list<BinaryOperator<TSymbol, TExpressionAST>> $operators
     * @psalm-pure
     * @psalm-suppress ImpureVariable
     */
    function __construct(array $operators)
    {
        $this->operators = $operators;
    }

    /**
     * @psalm-param Parser<TExpressionAST> $previousPrecedenceLevel
     * @psalm-return Parser<TExpressionAST>
     * @psalm-mutation-free
     */
    public function buildPrecedenceLevel(Parser $previousPrecedenceLevel): Parser
    {
        /**
         * @psalm-var list<Parser<pure-callable(Parser<TExpressionAST>):Parser<TExpressionAST>>> $operatorParsers
         */
        $operatorParsers = [];
        // @todo use folds?
        foreach ($this->operators as $operator) {
            $operatorParsers[] =
                pure(curry(flip($operator->transform())))
                    ->apply($operator->symbol()->followedBy($previousPrecedenceLevel))
                    ->label($operator->label());
        }

        return map(
            collect(
                $previousPrecedenceLevel,
                many(choice(...$operatorParsers))
            ),

            /**
             * @psalm-param array{0: TExpressionAST, 1: list<pure-callable(TExpressionAST):TExpressionAST>} $o
             * @psalm-return TExpressionAST
             * @psalm-pure
             */
            fn(array $o) => foldl(
                $o[1],

                /**
                 * @psalm-param TExpressionAST $acc
                 * @psalm-param pure-callable(TExpressionAST):TExpressionAST $appl
                 * @psalm-return TExpressionAST
                 * @psalm-pure
                 */
                fn($acc, callable $appl)  => $appl($acc),
                $o[0]
            )
        );

    }
}
