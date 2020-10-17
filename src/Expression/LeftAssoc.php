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

use InvalidArgumentException;
use Verraes\Parsica\Parser;
use function Cypress\Curry\curry;
use function Verraes\Parsica\choice;
use function Verraes\Parsica\collect;
use function Verraes\Parsica\Internal\FP\flip;
use function Verraes\Parsica\many;
use function Verraes\Parsica\map;
use function Verraes\Parsica\pure;

/**
 * @internal
 * @template TTermL
 * @template TTermR
 * @template TExpressionAST
 */
final class LeftAssoc implements ExpressionType
{
    /** @psalm-var BinaryOperator[] */
    private array $operators;

    /**
     * @psalm-param BinaryOperator[] $operators
     */
    function __construct(array $operators)
    {
        // @todo replace with atLeastOneArg, adjust message
        if (empty($operators)) throw new InvalidArgumentException("LeftAssoc expects at least one Operator");

        $this->operators = $operators;
    }

    public function buildPrecedenceLevel(Parser $previousPrecedenceLevel): Parser
    {
        /**
         * @psalm-var list<Parser<callable(TTermL):TExpressionAST>> $operatorParsers
         */
        $operatorParsers = [];
        // @todo use folds?
        foreach ($this->operators as $operator) {
            $operatorParsers[] =
                pure(curry(flip($operator->transform())))
                    ->apply($operator->symbol()->followedBy($previousPrecedenceLevel))
                    ->label($operator->label());
        }

        return
            map(
                collect(
                    $previousPrecedenceLevel,
                    many(choice(...$operatorParsers))
                ),
                /**
                 * @psalm-param array{0:Parser<TExpressionAST>, 1: list<callable(Parser<TTermL>):Parser<TExpressionAST>>} $o
                 * @psalm-return Parser<TExpressionAST>
                 */
                fn(array $o): Parser => array_reduce(
                    $o[1],
                    /**
                     * @psalm-param Parser<TTermL> $acc
                     * @psalm-param callable(Parser<TTermL>):Parser<TExpressionAST> $appl
                     * @psalm-return Parser<TExpressionAST>
                     */
                    fn($acc, callable $appl) => $appl($acc),
                    $o[0]
                )
            );


        return collect(
            $previousPrecedenceLevel,
            many(choice(...$operatorParsers))
        )->map(
        /** @psalm-return Parser<TExpressionAST> */
            fn(array $o): Parser => array_reduce(
                $o[1],
                /**
                 * @psalm-param TTermL $acc
                 * @psalm-param Parser<callable(TTermL):TExpressionAST> $appl
                 * @psalm-return Parser<TExpressionAST>
                 */
                fn($acc, callable $appl) => $appl($acc),
                $o[0]
            )
        );
    }
}
