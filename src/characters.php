<?php declare(strict_types=1);

namespace Mathias\ParserCombinator;

use Mathias\ParserCombinator\Parser;
use Mathias\ParserCombinator\ParseResult\ParseResult;
use Webmozart\Assert\Assert;
use function Mathias\ParserCombinator\ParseResult\fail;
use function Mathias\ParserCombinator\ParseResult\parser;
use function Mathias\ParserCombinator\ParseResult\succeed;

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

/**
 * Parse a newline character. {\n}
 *
 * @return Parser<string>
 */
function newline(): Parser
{
    return char("\n");
}

/**
 * Parse a carriage return character and a newline character. Return the two characters. {\r\n}
 *
 * @return Parser<string>
 */
function crlf(): Parser
{
    return string("\r\n")->label("crlf");
}

/**
 * Parse a newline or a crlf.
 *
 * @return Parser<string>
 */
function eol(): Parser
{
    return either(newline(), crlf())->label("eol");
}
