<?php declare(strict_types=1);


namespace Mathias\ParserCombinator\FP;

/**
 * @template T
 */
final class Just implements Maybe
{
    /** @var T */
    private $value;

    /**
     * @param T $value
     */
    function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * @return T
     */
    public function value()
    {
        return $this->value;
    }

    public function isJust(): bool
    {
        return true;
    }

    public function isNothing(): bool
    {
        return false;
    }

    /**
     * @param T $defaultValue
     *
     * @return T
     */
    public function default($defaultValue)
    {
        return $this->value;
    }
}