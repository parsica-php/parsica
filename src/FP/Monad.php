<?php declare(strict_types=1);

namespace Mathias\ParserCombinator\FP;

/**
 * @template Ta
 */
interface Monad extends Applicative
{
    /**
     * Monad Ta -> (Ta -> Monad Tb) -> Monad Tb
     *
     * @template Tb
     * @param callable(Ta):Monad<Tb> $f
     * @return Monad<Tb>
     */
    public function bind(callable $f) : Monad;

    /**
     * Monad Ta -> Monad Tb
     *
     * @template Tb
     * @return Monad<Tb>
     */
    public function discard() : Monad;
}