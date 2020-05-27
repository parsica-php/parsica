<?php declare(strict_types=1);

namespace Mathias\ParserCombinator;

use Webmozart\Assert\Assert;

/**
 * Parse a character
 *
 * @return Parser<string>
 */
function char(string $c): Parser
{
    Assert::length($c, 1, "char() expects a single character. Use string() if you want longer strings");
    return satisfy(equals($c), "char($c)");
}