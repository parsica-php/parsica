<?php declare(strict_types=1);

namespace Mathias\ParserCombinator\FP;

/**
 * @template T<callable(Ta):Tb>
 */
interface Applicative extends Functor
{
    /**
     * @template Ta
     * @template Tb
     * @param Applicative<Ta> $a
     * @return Applicative<Tb>
     *
     */
    public static function sequentialApplication(Applicative $a) : Applicative;
}