<?php declare(strict_types=1);

namespace Mathias\ParserCombinator\FP;

/**
 * @template T
 */
interface Maybe
{
    public function isJust(): bool;

    public function isNothing(): bool;

    /**
     * @param T $defaultValue
     *
     * @return T
     */
    public function default($defaultValue);
}