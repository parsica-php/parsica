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
 * @template T1
 * @template T2
 */
final class Operator
{
    private Parser $parser;

    /** @var callable(T1):T2 $constructor */
    private $constructor;

    private string $label;

    function __construct(Parser $parser, callable $constructor, string $label = "")
    {
        $this->parser = $parser;
        $this->constructor = $constructor;
        $this->label = $label ?: $parser->getLabel() . " prefix operator";
    }

    function parser(): Parser
    {
        return $this->parser;
    }

    /**
     * @psalm-return callable(T1):T2
     */
    function constructor(): callable
    {
        return $this->constructor;
    }

    function label(): string
    {
        return $this->label;
    }


}
