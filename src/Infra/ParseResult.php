<?php declare(strict_types=1);

namespace Mathias\ParserCombinators\Infra;

interface ParseResult
{
    public function isSuccess(): bool;

    /**
     * @return mixed
     */
    public function parsed();

    public function remaining(): string;

    public function expectation(): string;
}
