<?php declare(strict_types=1);

namespace Verraes\Parsica;

use Verraes\Parsica\Internal\Assert;
use function Verraes\Parsica\{isAlpha,
    isAlphaNum,
    isCharCode,
    isControl,
    isDigit,
    isEqual,
    isHexDigit,
    isLower,
    isPrintable,
    isPunctuation,
    isUpper,
    orPred};

/**
 * Parse a single character.
 *
 * @param string $c A single character
 *
 * @return Parser<string>
 * @see charI()
 *
 */
function char(string $c): Parser
{
    Assert::singleChar($c);
    return satisfy(isEqual($c))->label("char($c)");
}

/**
 * Parse a single character, case-insensitive and case-preserving. On success it returns the string cased as the
 * actually parsed input.
 *
 * eg charI('a'')->run("ABC") will succeed with "A", not "a".
 *
 * @param string $c A single character
 *
 * @return Parser<string>
 * @see char()
 */
function charI(string $c): Parser
{
    Assert::singleChar($c);
    return satisfy(orPred(isEqual(mb_strtolower($c)), isEqual(mb_strtoupper($c))))->label("charI($c)");
}


/**
 * Parse a control character (a non-printing character of the Latin-1 subset of Unicode).
 *
 * @return Parser<string>
 */
function controlChar(): Parser
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


/**
 * Parse 0-9. Returns the digit as a string. Use ->map('intval')
 * or similar to cast it to a numeric type.
 *
 * @return Parser<string>
 */
function digitChar(): Parser
{
    return satisfy(isDigit())->label('digit');
}

/**
 * Parse a binary character 0 or 1.
 *
 * @return Parser<string>
 */
function binDigitChar(): Parser
{
    return satisfy(isCharCode([0x30, 0x31]))->label("binDigitChar");
}

/**
 * Parse an octodecimal character 0-7.
 *
 * @return Parser<string>
 *
 * @deprecated @TODO doesn't support signed numbers yet
 */
function octDigitChar(): Parser
{
    return satisfy(isCharCode(range(0x30, 0x37)))->label("octDigitChar");
}

/**
 * Parse a hexadecimal numeric character 0123456789abcdefABCDEF.
 *
 * @return Parser<string>
 */
function hexDigitChar(): Parser
{
    return satisfy(isHexDigit())->label("hexDigitChar");
}