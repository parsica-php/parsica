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

/**
 * @internal
 * @template TSymbol
 * @template TExpressionAST
 * @psalm-immutable
 */
final class BinaryOperator
{
    /**
     * @psalm-var Parser<TSymbol>
     */
    private Parser $symbol;

    /**
     * @psalm-var pure-callable(TExpressionAST, TExpressionAST):TExpressionAST
     */
    private $transform;

    private string $label;

    /**
     * @psalm-param Parser<TSymbol> $symbol
     * @psalm-param pure-callable(TExpressionAST, TExpressionAST):TExpressionAST $transform
     * @psalm-param string $label
     * @psalm-pure
     * @psalm-suppress ImpureVariable
     */
    function __construct(Parser $symbol, callable $transform, string $label = "")
    {
        $this->symbol = $symbol;
        $this->transform = $transform;
        $this->label = $label ?: $symbol->getLabel() . " operator";
    }

    /**
     * @psalm-return Parser<TSymbol>
     * @psalm-mutation-free
     */
    function symbol(): Parser
    {
        return $this->symbol;
    }

    /**
     * @psalm-return pure-callable(TExpressionAST, TExpressionAST):TExpressionAST
     * @psalm-mutation-free
     */
    function transform(): callable
    {
        return $this->transform;
    }

    /**
     * @psalm-mutation-free
     */
    function label(): string
    {
        return $this->label;
    }

}
