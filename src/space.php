<?php declare(strict_types=1);
/**
 * This file is part of the Parsica library.
 *
 * Copyright (c) 2020 Mathias Verraes <mathias@verraes.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Verraes\Parsica;

use function Verraes\Parsica\{isBlank, isHSpace, isSpace, isWhitespace};

/**
 * Parse a single space character.
 *
 * @return Parser<string>
 */
function space(): Parser
{
    return char(' ')->label("space");
}

/**
 * Parse a single tab character.
 *
 * @return Parser<string>
 */
function tab(): Parser
{
    return char("\t")->label("tab");
}

/**
 *  Parse a space or tab.
 *
 * @return Parser<string>
 */
function blank(): Parser
{
    return satisfy(isBlank())->label("blank");
}

/**
 *  Parse a space character, and \t, \n, \r, \f, \v.
 *
 * @return Parser<string>
 */
function whitespace(): Parser
{
    return satisfy(isWhitespace())->label("whitespace");
}


/**
 * Parse a newline character.
 *
 * @return Parser<string>
 */
function newline(): Parser
{
    return char("\n")->label("newline");
}

/**
 * Parse a carriage return character and a newline character. Return the two characters. {\r\n}
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

/**
 * Skip zero or more white space characters.
 *
 * @return Parser<string>
 */
function skipSpace(): Parser
{
    return skipWhile(isSpace());
}

/**
 * Like 'skipSpace', but does not accept newlines and carriage returns.
 *
 * @return Parser<string>
 * @see skipSpace
 */
function skipHSpace(): Parser
{
    return skipWhile(isHSpace());
}

/**
 * Skip one or more white space characters.
 *
 * @return Parser<string>
 */
function skipSpace1(): Parser
{
    return skipWhile1(isSpace())->label("skipSpace1");
}

/**
 * Like 'skipSpace1', but does not accept newlines and carriage returns.
 *
 * @return Parser<string>
 * @see skipSpace1
 */
function skipHSpace1(): Parser
{
    return skipWhile1(isHSpace())->label("skipHSpace1");
}
