<?php declare(strict_types=1);

namespace Mathias\ParserCombinator;

use Mathias\ParserCombinator\Assert\Assert;
use Mathias\ParserCombinator\Parser\Parser;
use Mathias\ParserCombinator\ParseResult\ParseResult;
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
    Assert::singleChar($c);
    return satisfy(equals($c), "char($c)");
}


/**
 * Parse a non-empty string
 */
function string(string $str): Parser
{
    Assert::nonEmpty($str);
    $len = mb_strlen($str);
    return new Parser(
        fn(string $input): ParseResult => mb_substr($input, 0, $len) === $str
            ? succeed($str, mb_substr($input, $len))
            : fail("string($str)", $input)
    );
}
