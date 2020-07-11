<?php declare(strict_types=1);
/**
 * This file is part of the Parsica library.
 *
 * Copyright (c) 2020 Mathias Verraes <mathias@verraes.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * See https://www.json.org/json-en.html
 */

namespace Verraes\Parsica\JSON;

use Verraes\Parsica\Parser;
use function Verraes\Parsica\{alphaNumChar,
    assemble,
    atLeastOne,
    between,
    char,
    charI,
    choice,
    collect,
    digitChar,
    isCharCode,
    keepFirst,
    oneOfS,
    optional,
    pure,
    satisfy,
    zeroOrMore};


/**
 * Whitespace
 */
function ws(): Parser
{
    return zeroOrMore(satisfy(isCharCode([0x20, 0x0A, 0x0D, 0x09])))->voidLeft(null)->label('whitespace');
}

/**
 * Apply $parser and consume all the following whitespace.
 */
function token(Parser $parser): Parser
{
    return keepFirst($parser, ws());
}

function oneNine(): Parser
{
    return oneOfS("123456789");
}


function digits(): Parser
{
    return atLeastOne(digitChar());
}

function integer(): Parser
{
    return choice(
        minus()->append(oneNine())->append(digits()),
        minus()->append(digitChar()),
        oneNine()->append(digits()),
        digitChar()
    )->map('intval')->label("integer");
}

function fraction(): Parser
{
    return char('.')->append(digits());
}

function number(): Parser
{
    return assemble(
        choice(
            minus()->append(oneNine())->append(digits()),
            minus()->append(digitChar()),
            oneNine()->append(digits()),
            digitChar()
        ),
        optional(fraction()),
        optional(exponent())
    )->map('floatval')->label("number");
}

/**
 * Optional minus sgn for numbers
 */
function minus(): Parser
{
    return char('-');
}

/**
 * Optional + or -
 */
function sign(): Parser
{
    return char('+')->or(char('-'))->or(pure('+'));
}

/**
 * @deprecated @TODO incomplete
 */
function string(): Parser
{
    return between(char('"'), char('"'), zeroOrMore(alphaNumChar()))->label("string");
}

function key(): Parser
{
    return token(string());
}

function value(): Parser
{
    return token(string());
}

function key_value(): Parser
{
    return collect(
        key(),
        token(char(':'))->followedBy(value())
    );
}

/**
 * The E in 1.23456E-78
 */
function exponent(): Parser
{
    return assemble(
        charI('e'),
        sign(),
        digits()
    );
}
