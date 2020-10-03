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

/**
 * @internal
 * @template TOutput
 * @template TTerm
 * @template TTermL
 * @template TTermR
 */
final class Operator
{
    /**
     * @psalm-var Parser<TOutput>
     */
    private Parser $parser;

    /** @var callable(TTerm):TOutput|callable(TTermL, TTermR):TOutput $constructor */
    private $transform;

    private string $label;

    /**
     * @psalm-param Parser<T> $symbol
     * @psalm-param callable(TTerm):TOutput|callable(TTermL, TTermR):TOutput $transform
     * @psalm-param string $label
     */
    function __construct(Parser $parser, callable $transform, string $label = "")
    {
        $this->parser = $parser;
        $this->transform = $transform;
        $this->label = $label ?: $parser->getLabel() . " prefix operator";
    }


    /**
     * Return the arity of the inner transform() function
     */
    public function arity(): Int
    {
        throw new \Exception("@todo not implemented");
    }


    /**
     * @psalm-return Parser<T>
     */
    function parser(): Parser
    {
        return $this->parser;
    }

    /**
     * @psalm-return callable(TTerm):TOutput|callable(TTermL, TTermR):TOutput
     */
    function transform(): callable
    {
        return $this->transform;
    }

    function label(): string
    {
        return $this->label;
    }


}
