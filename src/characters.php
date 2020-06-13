<?php declare(strict_types=1);

namespace Mathias\ParserCombinator;

use Mathias\ParserCombinator\Assert\Assert;
use Mathias\ParserCombinator\Parser\Parser;
use Mathias\ParserCombinator\ParseResult\ParseResult;
use function Mathias\ParserCombinator\ParseResult\{fail,succeed};
use function Mathias\ParserCombinator\Predicates\{isEqual, orPred};

/**
 * Parse a single character.
 *
 * @see charI()
 *
 * @param string $c A single character
 *
 * @return Parser<string>
 */
function char(string $c): Parser
{
    Assert::singleChar($c);
    return satisfy(isEqual($c), "char($c)");
}

/**
 * Parse a single character, case-insensitive and case-preserving. On success it returns the string cased as the
 * actually parsed input.
 * eg charI('a'')->run("ABC") will succeed with "A", not "a".
 *
 * @see char()
 *
 * @param string $c A single character
 *
 * @return Parser<string>
 */
function charI(string $c): Parser
{
    Assert::singleChar($c);
    return satisfy(
            orPred(isEqual(mb_strtolower($c)), isEqual(mb_strtoupper($c))),
                "charI($c)"
            );
}

/**
 * Parse a non-empty string.
 *
 * @see stringI()
 *
 * @return Parser<string>
 */
function string(string $str): Parser
{
    Assert::nonEmpty($str);
    $len = mb_strlen($str);
    return Parser::make(
        fn(string $input): ParseResult => mb_substr($input, 0, $len) === $str
            ? succeed($str, mb_substr($input, $len))
            : fail("string($str)", $input)
    );
}

/**
 * Parse a non-empty string, case-insensitive and case-preserving. On success it returns the string cased as the
 * actually parsed input.
 * eg stringI("foobar")->run("foObAr") will succeed with "foObAr"
 *
 * @see string()
 *
 * @return Parser<string>
 */
function stringI(string $str): Parser
{
    throw new \Exception("@TODO not implemented");
}

