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
    between,
    char,
    collect,
    isCharCode,
    keepFirst,
    nothing,
    satisfy,
    sequence,
    zeroOrMore};


/**
 * JSON Whitespace parser.
 */
function ws(): Parser
{
    return zeroOrMore(satisfy(isCharCode([0x20, 0x0A, 0x0D, 0x09])))->voidLeft(null);
}


function token(Parser $parser): Parser
{
    return keepFirst($parser, ws());
}

function sign(): Parser{
    return char('+')->or(char('-'))->or(nothing()->voidLeft("+"));
}

/**
 * @deprecated @TODO incomplete
 */
function string(): Parser
{
    return between(char('"'), char('"'), zeroOrMore(alphaNumChar()));
}

function key():Parser {
    return token(string());
}

function value() : Parser {
    return token(string());
}
function key_value(): Parser
{
    return collect(
        key(),
        token(char(':'))->followedBy(value())
    );
}
