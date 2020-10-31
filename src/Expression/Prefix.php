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
use Verraes\Parsica\Internal\Assert;
use Verraes\Parsica\Parser;
use function Verraes\Parsica\choice;
use function Verraes\Parsica\pure;

/**
 * @internal
 * @template TSymbol
 * @template TExpressionAST
 */
final class Prefix implements ExpressionType
{
    /** @psalm-var non-empty-list<UnaryOperator<TSymbol, TExpressionAST>> */
    private array $operators;

    /**
     * @psalm-param non-empty-list<UnaryOperator<TSymbol, TExpressionAST>> $operators
     */
    function __construct(array $operators)
    {
        /** @psalm-suppress RedundantCondition */
        Assert::nonEmptyList($operators, "Prefix expects at least one Operator");
        $this->operators = $operators;
    }

    public function buildPrecedenceLevel(Parser $previousPrecedenceLevel): Parser
    {
        $operatorParsers = [];
        foreach ($this->operators as $operator) {

            $operatorParsers[] =
                pure($operator->transform())
                    ->apply($operator->symbol()->followedBy($previousPrecedenceLevel))
                    ->label($operator->label());
        }

        return choice(...$operatorParsers)->or($previousPrecedenceLevel);
    }
}
