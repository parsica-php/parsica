<?php declare(strict_types=1);


namespace Mathias\ParserCombinator;

/**
 * Creates an equality function
 *
 * @template T
 *
 * @param T
 *
 * @return callable(T) : bool
 */
function equals($x): callable
{
    return fn($y) => $x === $y;
}