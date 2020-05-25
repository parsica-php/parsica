<?php declare(strict_types=1);

namespace Mathias\ParserCombinators;

use Exception;

interface ParseResult
{
    public function isSuccess(): bool;
}

final class Success implements ParseResult
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

function succeed($parsed, string $output): ParseResult
{
    return new Success($parsed, $output);
}


function fail(string $expectation): ParseResult
{
    return new ParserFailure($expectation);
}


function runparser($parser, $input)
{
    $result = $parser($input);
    return $result->parsed();

}
