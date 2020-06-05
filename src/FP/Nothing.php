<?php declare(strict_types=1);

namespace Mathias\ParserCombinator\FP;

final class Nothing implements Functor, Maybe
{
    public function isJust(): bool
    {
        return false;
    }

    public function isNothing(): bool
    {
        return true;
    }

    public function default($defaultValue)
    {
        return $defaultValue;
    }

    /**
     * @template T2
     * @param callable(T1):T2 $f
     * @return Maybe<T2>
     */
    public function fmap(callable $f) : Maybe
    {
        return new Nothing;
    }
}