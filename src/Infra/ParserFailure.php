<?php declare(strict_types=1);

namespace Mathias\ParserCombinators\Infra;

use Exception;

final class ParserFailure extends Exception implements ParseResult
{
    private string $expectation;

    public function __construct(string $expectation)
    {
        $this->expectation = $expectation;
        parent::__construct("Expected: {$this->expectation}");
    }

    public function isSuccess(): bool
    {
        return false;
    }

    public function expectation(): string
    {
        return $this->expectation;
    }
}