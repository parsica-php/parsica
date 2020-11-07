<?php declare(strict_types=1);
/*
 * This file is part of the Parsica library.
 *
 * Copyright (c) 2020 Mathias Verraes <mathias@verraes.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Verraes\Parsica\Internal\FP;

/**
 * Swaps the arguments of the callable, returning a callable.
 *
 * @internal
 * @template Ta
 * @template Tb
 * @template Tc
 * @psalm-param callable(Ta, Tb):Tc $f
 * @psalm-return callable(Tb, Ta):Tc
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
 * @internal
 */
function foldl(array $input, callable $function, $initial = null) {
    return array_reduce($input, $function, $initial);
}

/**
 * @internal
 */
function foldr(array $input, callable $function, $initial = null) {
    if (empty($input)) return $initial;
    $head = array_shift($input);
    return $function(
        $head,
        foldr($input, $function, $initial)
    );
};
