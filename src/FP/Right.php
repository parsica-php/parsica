<?php declare(strict_types=1);

namespace Mathias\ParserCombinator\FP;

/**
 * @template TLeft
 * @template TRight
 */
final class Right implements Either
{
    /** @var TRight */
    private $right;

    /**
     * @param TRight $right
     */
    function __construct($right)
    {
        $this->right = $right;
    }

    public function isLeft(): bool
    {
        return false;
    }

    public function isRight(): bool
    {
        return true;
    }

}