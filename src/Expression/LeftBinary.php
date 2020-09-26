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

use Verraes\Parsica\Parser;
use function Cypress\Curry\curry;
use function Verraes\Parsica\choice;
use function Verraes\Parsica\collect;
use function Verraes\Parsica\Internal\FP\flip;
use function Verraes\Parsica\many;
use function Verraes\Parsica\pure;

final class LeftBinary implements ExpressionType
{
    /** @psalm-var Operator[] */
    private array $operators;

    function __construct(Operator ...$operators)
    {
        // @todo use array as argument -^

        // @todo replace with atLeastOneArg, adjust message
        if (empty($operators)) throw new \InvalidArgumentException("PrefixUnary expects at least one Operator");
        $this->operators = $operators;
    }

    public function buildPrecedenceLevel(Parser $previousPrecedenceLevel): Parser
    {
        $operatorParsers = [];
        foreach ($this->operators as $operator) {
            $operatorParsers[] =
                pure(curry(flip($operator->constructor())))
                    ->apply($operator->parser()->followedBy($previousPrecedenceLevel))
                    ->label($operator->label());
        }

        return
            collect(
                $previousPrecedenceLevel,
                many(choice(...$operatorParsers))
            )->map(fn(array $o) => array_reduce(
                $o[1],
                fn($acc, callable $appl) => $appl($acc),
                $o[0]
            ));

    }

}
