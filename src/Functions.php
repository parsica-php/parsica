<?php declare(strict_types=1);

namespace Mathias\ParserCombinators;

require_once __DIR__ . '/../vendor/autoload.php';

use Exception;

final class Functions
{
}


interface Result
{
    public function isSuccess(): bool;
}

final class Success implements Result
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

final class ParserFailure extends Exception implements Result
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

function succeed($parsed, string $output): Result
{
    return new Success($parsed, $output);
}


function fail(string $expectation): Result
{
    return new ParserFailure($expectation);
}


function runparser($parser, $input)
{
    $result = $parser($input);
    return $result->parsed();

}

// *** BASIC PARSERS

function char(string $char)
{
    return fn($input): Result => (head($input[0]) === $char)
        ? succeed($char, tail($input))
        : fail("char($char)");
}

// ***  COMBINATORS

function either($left, $right)
{
    return function ($input) use ($left, $right) : Result {
        $leftr = $left($input);
        if ($leftr->isSuccess()) {
            return $leftr;
        }

        $rightr = $right($input);
        if ($rightr->isSuccess()) {
            return $rightr;
        }

        $expectation = "either (\n\t{$leftr->expectation()}\n\tor\n\t{$rightr->expectation()}\n)";
        return fail($expectation);
    };
}

// *** UTIL


function head(string $s): string
{
    return $s[0];
}

function tail(string $s): string
{
    return substr($s, 1);
}