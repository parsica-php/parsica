<?php declare(strict_types=1);
/**
 * This file is part of the Parsica library.
 *
 * Copyright (c) 2020 Mathias Verraes <mathias@verraes.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Parsica\Parsica;

use Parsica\Parsica\Internal\Assert;

/**
 * Parse a single character.
 *
 * @psalm-param string $c A single character
 *
 * @psalm-return Parser<string>
 * @api
 * @see charI()
 * @psalm-pure
 */
function char(string $c): Parser
{
    /** @psalm-suppress ImpureMethodCall */
    Assert::singleChar($c);
    return satisfy(isEqual($c))->label("'$c'");
}

/**
 * Parse a single character, case-insensitive and case-preserving. On success, it returns the string cased as the
 * actually parsed input.
 *
 * eg charI('a'')->run("ABC") will succeed with "A", not "a".
 *
 * @psalm-param string $c A single character
 *
 * @psalm-return Parser<string>
 * @api
 *
 * @see char()
 * @psalm-pure
 */
function charI(string $c): Parser
{
    /** @psalm-suppress ImpureMethodCall */
    Assert::singleChar($c);
    $lower = mb_strtolower($c);
    $upper = mb_strtoupper($c);
    $label = $lower==$upper ? "'$c'" : "'$lower' or '$upper'";
    return satisfy(orPred(isEqual($lower), isEqual($upper)))->label($label);
}


/**
 * Parse a control character (a non-printing character of the Latin-1 subset of Unicode).
 *
 * @psalm-return Parser<string>
 * @api
 * @psalm-pure
 */
function controlChar(): Parser
{
    return satisfy(isControl())->label("<controlChar>");
}

/**
 * Parse an uppercase character A-Z.
 *
 * @psalm-return Parser<string>
 * @api
 * @psalm-pure
 */
function upperChar(): Parser
{
    return satisfy(isUpper())->label("A-Z");
}

/**
 * Parse a lowercase character a-z.
 *
 * @psalm-return Parser<string>
 * @api
 * @psalm-pure
 */
function lowerChar(): Parser
{
    return satisfy(isLower())->label("a-z");
}

/**
 * Parse an uppercase or lowercase character A-Z, a-z.
 *
 * @psalm-return Parser<string>
 * @api
 * @psalm-pure
 */
function alphaChar(): Parser
{
    return satisfy(isAlpha())->label("A-Z or a-z");
}

/**
 * Parse an alpha or numeric character A-Z, a-z, 0-9.
 *
 * @psalm-return Parser<string>
 * @api
 * @psalm-pure
 */
function alphaNumChar(): Parser
{
    return satisfy(isAlphaNum())->label("A-Z or a-z or 0-9");
}

/**
 * Parse a printable ASCII char.
 *
 * @psalm-return Parser<string>
 * @api
 * @psalm-pure
 */
function printChar(): Parser
{
    return satisfy(isPrintable())->label("<printChar>");
}

/**
 * Parse a single punctuation character !"#$%&'()*+,-./:;<=>?@[\]^_`{|}~
 *
 * @psalm-return Parser<string>
 * @api
 * @psalm-pure
 */
function punctuationChar(): Parser
{
    return satisfy(isPunctuation())->label("<punctuation>");
}


/**
 * Parse 0-9. Returns the digit as a string. Use ->map('intval')
 * or similar to cast it to a numeric type.
 *
 * @psalm-return Parser<string>
 * @api
 * @psalm-pure
 */
function digitChar(): Parser
{
    return satisfy(isDigit())->label('0-9');
}

/**
 * Parse a binary character 0 or 1.
 *
 * @psalm-return Parser<string>
 * @api
 * @psalm-pure
 */
function binDigitChar(): Parser
{
    return satisfy(isCharCode([0x30, 0x31]))->label("'0' or '1'");
}

/**
 * Parse an octodecimal character 0-7.
 *
 * @psalm-return Parser<string>
 *
 * @api
 * @psalm-pure
 */
function octDigitChar(): Parser
{
    return satisfy(isCharCode(range(0x30, 0x37)))->label("0-7");
}

/**
 * Parse a hexadecimal numeric character 0123456789abcdefABCDEF.
 *
 * @psalm-return Parser<string>
 * @api
 * @psalm-pure
 */
function hexDigitChar(): Parser
{
    return satisfy(isHexDigit())->label("<hexadecimal>");
}
