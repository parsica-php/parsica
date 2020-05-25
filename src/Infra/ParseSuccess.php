<?php declare(strict_types=1);

namespace Mathias\ParserCombinators\Infra;

final class ParseSuccess implements ParseResult
{
    /**
     * @var mixed
     */
    private $parsed;
    private string $remaining;

    /**
     * @param mixed $parsed
     */
    public function __construct($parsed, string $remaining)
    {
        $this->parsed = $parsed;
        $this->remaining = $remaining;
    }

    public function parsed()
    {
        return $this->parsed;
    }

    public function remaining(): string
    {
        return $this->remaining;
    }

    public function isSuccess(): bool
    {
        return true;
    }

    public function expectation(): string
    {
        throw new \Exception("Can't read the expectation of a succeeded ParseResult.");
    }
}
