<?php declare(strict_types=1);

namespace Mathias\ParserCombinator;

use Mathias\ParserCombinator\Parser;
use Mathias\ParserCombinator\ParseResult\ParseResult;
use Webmozart\Assert\Assert;
use function Mathias\ParserCombinator\ParseResult\{fail, parser, succeed};

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
 */
function string(string $str): Parser
{
    Assert::minLength($str, 1);
    $len = mb_strlen($str);
    return parser(
        fn(string $input): ParseResult => mb_substr($input, 0, $len) === $str
            ? succeed($str, mb_substr($input, $len))
            : fail("string($str)", $input)
    );
}
