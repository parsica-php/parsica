<?php declare(strict_types=1);

namespace Mathias\ParserCombinator\FP;

/**
 * @template T
 */
final class Nothing implements Maybe
{
    public function __construct()
    {
    }

    public function isJust(): bool
    {
        return false;
    }

    public function isNothing(): bool
    {
        return true;
    }

    /**
     * @param T $defaultValue
     *
     * @return T
     */
    public function default($defaultValue)
    {
        return $defaultValue;
    }

    /**
     * @template T2
     * @param callable(T):T2 $f
     * @return Maybe<T2>
     */
    public function fmap(callable $f) : Maybe
    {
        return new Nothing;
    }
}