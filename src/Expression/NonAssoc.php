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
use function Parsica\Parsica\choice;
use function Parsica\Parsica\collect;
use function Parsica\Parsica\map;

/**
 * @internal
 * @template TSymbol
 * @template TExpressionAST
 * @psalm-immutable
 */
final class NonAssoc implements ExpressionType
{
    /**
     * @psalm-var BinaryOperator<TSymbol, TExpressionAST>
     */
    private BinaryOperator $operator;

    /**
     * @psalm-param BinaryOperator<TSymbol, TExpressionAST> $operator
     * @psalm-pure
     * @psalm-suppress ImpureVariable
     */
    function __construct(BinaryOperator $operator)
    {
        $this->operator = $operator;
    }

    /**
     * @psalm-param Parser<TExpressionAST> $previousPrecedenceLevel
     * @psalm-return Parser<TExpressionAST>
     */
    public function buildPrecedenceLevel(Parser $previousPrecedenceLevel): Parser
    {
        return choice(
            map(
                collect(
                    $previousPrecedenceLevel,
                    $this->operator->symbol(),
                    $previousPrecedenceLevel
                ),

                /**
                 * @psalm-param array{0: TExpressionAST, 1: TSymbol, 2: TExpressionAST} $o
                 * @psalm-return TExpressionAST
                 * @psalm-pure
                 * @psalm-suppress ImpureVariable
                 */
                fn(array $o) => $this->operator->transform()($o[0], $o[2])),
            $previousPrecedenceLevel
        );
    }
}
