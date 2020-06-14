<?php declare(strict_types=1);

namespace Mathias\ParserCombinator;

use Mathias\ParserCombinator\Assert\Assert;
use Mathias\ParserCombinator\Parser\Parser;
use Mathias\ParserCombinator\ParseResult\ParseResult;
use function Mathias\ParserCombinator\ParseResult\{fail,succeed};
use function Mathias\ParserCombinator\Predicates\{isAlpha,
    isAlphaNum,
    isControl,
    isEqual,
    isLower,
    isPrintable,
    isPunctuation,
    isUpper,
    orPred};

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
    /** @var Parser<string> $parser */
    $parser = Parser::make(
        fn(string $input): ParseResult => mb_substr($input, 0, $len) === $str
            ? succeed($str, mb_substr($input, $len))
            : fail("string($str)", $input)
    );
    return $parser;
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

/**
 * Parse a control character (a non-printing character of the Latin-1 subset of Unicode).
 *
 * @return Parser<string>
 */
function controlChar() : Parser
{
    return satisfy(isControl())->label("controlChar");
}

/**
 * Parse an uppercase character A-Z.
 *
 * @return Parser<string>
 */
function upperChar(): Parser
{
    return satisfy(isUpper())->label("upperChar");
}

/**
 * Parse a lowercase character a-z.
 *
 * @return Parser<string>
 */
function lowerChar(): Parser
{
    return satisfy(isLower())->label("lowerChar");
}

/**
 * Parse an uppercase or lowercase character A-Z, a-z.
 *
 * @return Parser<string>
 */
function alphaChar(): Parser
{
    return satisfy(isAlpha())->label("alphaChar");
}

/**
 * Parse an alpha or numeric character A-Z, a-z, 0-9.
 *
 * @return Parser<string>
 */
function alphaNumChar(): Parser
{
    return satisfy(isAlphaNum())->label("alphaNumChar");
}

/**
 * Parse a printable ASCII char.
 *
 * @return Parser<string>
 */
function printChar(): Parser
{
    return satisfy(isPrintable())->label("printChar");
}

/**
 * Parse a single punctuation character !"#$%&'()*+,-./:;<=>?@[\]^_`{|}~
 *
 * @return Parser<string>
 */
function punctuationChar(): Parser
{
    return satisfy(isPunctuation())->label("punctuationChar");
}
