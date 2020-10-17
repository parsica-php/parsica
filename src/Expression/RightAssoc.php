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
use function Verraes\Parsica\keepFirst;
use function Verraes\Parsica\many;
use function Verraes\Parsica\pure;

/**
 * @internal
 */
final class RightAssoc implements ExpressionType
{
    /** @var BinaryOperator[] */
    private array $operators;

    /**
     * @psalm-param BinaryOperator[] $operators
     */
    function __construct(array $operators)
    {
        // @todo replace with atLeastOneArg, adjust message
        if (empty($operators)) throw new InvalidArgumentException("RightAssoc expects at least one Operator");
        $this->operators = $operators;
    }

    public function buildPrecedenceLevel(Parser $previousPrecedenceLevel): Parser
    {
        /** @todo refactor for performance */
        $foldr = function (array $input, callable $function, $initial = null) use (&$foldr) {
            if (empty($input)) return $initial;
            $head = array_shift($input);
            return $function(
                $head,
                $foldr($input, $function, $initial)
            );
        };


        $operatorParsers = [];
        foreach ($this->operators as $operator) {
            $operatorParsers[] =
                pure(curry($operator->transform()))
                    ->apply(keepFirst($previousPrecedenceLevel, $operator->symbol()))
                    ->label($operator->label());
        }

        return collect(
            many(choice(...$operatorParsers)),
            $previousPrecedenceLevel
        )->map(fn(array $o) => $foldr(
            $o[0],
            fn(callable $appl, $acc) => $appl($acc),
            $o[1]
        ));
    }
}
