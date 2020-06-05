<?php declare(strict_types=1);

namespace Mathias\ParserCombinator\FP;

/**
 * @template TLeft
 * @template TRight
 */
final class Left implements Either
{
    /** @var TLeft */
    private $left;

    /**
     * @param TLeft $left
     */
    function __construct($left)
    {
        $this->left = $left;
    }

    public function isLeft(): bool
    {
        return true;
    }

    public function isRight(): bool
    {
        return false;
    }

}