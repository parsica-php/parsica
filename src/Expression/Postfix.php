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
use function Verraes\Parsica\choice;
use function Verraes\Parsica\keepFirst;
use function Verraes\Parsica\pure;

/**
 * @internal
 * @template TExpressionAST
 */
final class Postfix implements ExpressionType
{
    /** @psalm-var UnaryOperator[] */
    private array $operators;

    /**
     * @psalm-param UnaryOperator[] $operators
     */
    function __construct(array $operators)
    {
        // @todo replace with atLeastOneArg, adjust message
        if (empty($operators)) throw new InvalidArgumentException("Postfix expects at least one Operator");
        $this->operators = $operators;
    }

    public function buildPrecedenceLevel(Parser $previousPrecedenceLevel): Parser
    {
        /**
         * @todo template TOutput
         * @todo psalm-var Operator<TOutput>[] $operatorParsers
         */
        $operatorParsers = [];
        foreach ($this->operators as $operator) {
            $operatorParsers[] =
                pure($operator->transform())
                    ->apply(keepFirst($previousPrecedenceLevel, $operator->symbol()))
                    ->label($operator->label());
        }

        return choice(...$operatorParsers)->or($previousPrecedenceLevel);
    }
}
