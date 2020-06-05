<?php declare(strict_types=1);

namespace Mathias\ParserCombinator\FP;

/**
 * @template MaybeT
 */
interface Maybe extends Functor
{
    public function isJust(): bool;

    public function isNothing(): bool;

    /**
     * @param MaybeT $defaultValue
     *
     * @return MaybeT
     */
    public function default($defaultValue);
}