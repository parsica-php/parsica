<?php declare(strict_types=1);


namespace Mathias\ParserCombinator\FP;


final class Just implements Maybe
{
    private $value;

    function __construct($value)
    {
        $this->value = $value;
    }

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

    public function default($defaultValue)
    {
        return $this->value;
    }

    public function fmap(callable $f)
    {
        return new Just($f($this->value));
    }
}