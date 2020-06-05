<?php declare(strict_types=1);

namespace Mathias\ParserCombinator\FP;

/**
 * @template T
 */
interface Monoid
{
    /**
     * @param Monoid<T> $other
     * @return Monoid<T>
     */
    public function mappend(Monoid $other) : Monoid;

    /**
     * @return Monoid<T>
     */
    public static function mempty() : Monoid;
}