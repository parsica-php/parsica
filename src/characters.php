<?php declare(strict_types=1);

namespace Mathias\ParserCombinator;

use Mathias\ParserCombinator\Assert\Assert;
use Mathias\ParserCombinator\Parser\Parser;
use Mathias\ParserCombinator\ParseResult\ParseResult;
use function Mathias\ParserCombinator\Parser\parser;
use function Mathias\ParserCombinator\ParseResult\{fail, succeed};

/**
 * Parse a character
 *
 * @param string $c A single character
 *
 * @return Parser<string>
 */
function char(string $c): Parser
{
    Assert::length($c, 1, "char() expects a single character. Use string() if you want longer strings");
    return satisfy(equals($c), "char($c)");
}


/**
 * Parse a non-empty string
 *
 * @return Parser<string>
 * @psalm-suppress MixedReturnTypeCoercion
 */
function string(string $str): Parser
{
    Assert::minLength($str, 1, "The string must not be empty.");
    $len = mb_strlen($str);
    return new Parser(
        fn(string $input): ParseResult => mb_substr($input, 0, $len) === $str
            ? succeed($str, mb_substr($input, $len))
            : fail("string($str)", $input)
    );
}
