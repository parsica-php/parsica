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
use function Parsica\Parsica\Internal\FP\foldr;
use function Parsica\Parsica\keepFirst;
use function Parsica\Parsica\many;
use function Parsica\Parsica\map;
use function Parsica\Parsica\pure;

/**
 * @internal
 * @template TSymbol
 * @template TExpressionAST
 * @psalm-immutable
 */
final class RightAssoc implements ExpressionType
{
    /** @var non-empty-list<BinaryOperator<TSymbol, TExpressionAST>> */
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
     */
    public function buildPrecedenceLevel(Parser $previousPrecedenceLevel): Parser
    {
        /**
         * @psalm-var list<Parser<pure-callable(Parser<TExpressionAST>):Parser<TExpressionAST>>> $operatorParsers
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
             * @psalm-param array{0: list<pure-callable(TExpressionAST):TExpressionAST>, 1: TExpressionAST} $o
             * @psalm-return TExpressionAST
             */
            fn(array $o) => foldr(
                $o[0],

                /**
                 * @psalm-param pure-callable(TExpressionAST):TExpressionAST $appl
                 * @psalm-param TExpressionAST $acc
                 * @psalm-return TExpressionAST
                 */
                fn(callable $appl, $acc) => $appl($acc),
                $o[1]
            )
        );

    }
}
