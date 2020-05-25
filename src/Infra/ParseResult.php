<?php declare(strict_types=1);

namespace Mathias\ParserCombinators\Infra;

interface ParseResult
{
    public function isSuccess(): bool;
}
