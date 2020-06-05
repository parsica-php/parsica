<?php declare(strict_types=1);

namespace Mathias\ParserCombinator\FP;

/**
 * @template TLeft
 * @template TRight
 */
interface Either
{
    public function isLeft(): bool;

    public function isRight(): bool;
}