<?php declare(strict_types=1);

namespace Mathias\ParserCombinators\Infra;

final class ParseSuccess implements ParseResult
{
    private $parsed;
    private string $output;

    public function __construct($parsed, string $output)
    {
        $this->parsed = $parsed;
        $this->output = $output;
    }

    public function parsed()
    {
        return $this->parsed;
    }

    public function output(): string
    {
        return $this->output;
    }

    public function isSuccess(): bool
    {
        return true;
    }
}
