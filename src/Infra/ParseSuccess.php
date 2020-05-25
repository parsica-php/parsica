<?php declare(strict_types=1);

namespace Mathias\ParserCombinators\Infra;

final class ParseSuccess implements ParseResult
{
    private $parsed;
    private string $remaining;

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
}
