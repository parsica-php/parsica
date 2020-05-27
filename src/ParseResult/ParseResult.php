<?php declare(strict_types=1);

namespace Mathias\ParserCombinator\ParseResult;

use Mathias\ParserCombinator\T;

/**
 * @template T
 */
interface ParseResult
{
    public function isSuccess(): bool;

    /**
     * @return T
     */
    public function parsed();

    public function remaining(): string;

    public function expectation(): string;
}
