<?php declare(strict_types=1);


namespace Mathias\ParserCombinator;

/**
 * Creates an equality function
 */
function equals($x) {
    return fn($y) => $x === $y;
}