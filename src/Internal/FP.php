<?php declare(strict_types=1);
/*
 * This file is part of the Parsica library.
 *
 * Copyright (c) 2020 Mathias Verraes <mathias@verraes.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Parsica\Parsica\Internal\FP;

/**
 * Swaps the arguments of the callable, returning a callable.
 *
 * @internal
 * @template Ta
 * @template Tb
 * @template Tc
 * @psalm-param pure-callable(Ta, Tb):Tc $f
 * @psalm-return pure-callable(Tb, Ta):Tc
 * @psalm-pure
 */
function flip(callable $f): callable
{
    /**
     * @psalm-param Ta $x
     * @psalm-param Tb $y
     * @psalm-return Tc
     */
    return fn($x, $y) => $f($y, $x);
}


/**
 * @template TA
 * @template TB
 *
 * @psalm-param list<TA> $input
 * @psalm-param callable(TB, TA):TB $function
 * @psalm-param TB $initial
 * @psalm-return TB
 *
 * @internal
 * @psalm-pure
 */
function foldl(array $input, callable $function, $initial) {
    /** @psalm-suppress ImpureFunctionCall */
    return array_reduce($input, $function, $initial);
}

/**
 * @template TA
 * @template TB
 *
 * @psalm-param list<TA> $input
 * @psalm-param pure-callable(TA, TB):TB $function
 * @psalm-param TB $initial
 * @psalm-return TB
 *
 * @internal
 * @psalm-pure
 */
function foldr(array $input, callable $function, $initial) {
    while($head = array_pop($input))
    {
        $initial = $function($head, $initial);
    }
    return $initial;
}
