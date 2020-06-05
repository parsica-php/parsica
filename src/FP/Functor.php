<?php declare(strict_types=1);

namespace Mathias\ParserCombinator\FP;

/**
 * @template T1
 */
interface Functor
{
    /**
     * @template T2
     * @param callable(T1):T2 $f
     * @return T2
     */
    public function fmap(callable $f);

}