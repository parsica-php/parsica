<?php declare(strict_types=1);

namespace Mathias\ParserCombinator\ParseResult;

use Exception;

/**
 * @template T
 */
final class ParserFailure extends Exception implements ParseResult
{
    private string $expected;
    private string $got;

    public function __construct(string $expected, string $got)
    {
        $this->expected = $expected;
        $this->got = $got;
        parent::__construct("Expected: $expected, got $got");
    }

    public function isSuccess(): bool
    {
        return false;
    }

    public function isFail(): bool
    {
        return !$this->isSuccess();
    }

    public function expected(): string
    {
        return $this->expected;
    }

    public function got(): string
    {
        return $this->got;
    }

    /**
     * @return T
     */
    public function parsed()
    {
        throw new \BadMethodCallException("Can't read the parsed value of a failed ParseResult.");
    }

    public function remaining(): string
    {
        throw new \BadMethodCallException("Can't read the remaining string of a failed ParseResult.");
    }
}