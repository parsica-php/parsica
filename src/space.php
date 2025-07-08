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

/**
 * Parse a single space character.
 *
 * @psalm-return Parser<string>
 * @api
 * @psalm-pure
 */
function space(): Parser
{
    return char(' ')->label("<space>");
}

/**
 * Parse a single tab character.
 *
 * @psalm-return Parser<string>
 * @api
 * @psalm-pure
 */
function tab(): Parser
{
    return char("\t")->label("<tab>");
}

/**
 *  Parse a space or tab.
 *
 * @psalm-return Parser<string>
 * @api
 * @psalm-pure
 */
function blank(): Parser
{
    return satisfy(isBlank())->label("<blank>");
}

/**
 *  Parse a space character, and \t, \n, \r, \f, \v.
 *
 * @psalm-return Parser<string>
 * @api
 * @psalm-pure
 */
function whitespace(): Parser
{
    return satisfy(isWhitespace())->label("<whitespace>");
}


/**
 * Parse a newline character.
 *
 * @psalm-return Parser<string>
 * @api
 * @psalm-pure
 */
function newline(): Parser
{
    return char("\n")->label("<newline>");
}

/**
 * Parse a carriage return character and a newline character. Return the two characters. {\r\n}
 *
 * @psalm-return Parser<string>
 * @api
 * @psalm-pure
 */
function crlf(): Parser
{
    return string("\r\n")->label("<crlf>");
}

/**
 * Parse a newline or a crlf.
 *
 * @psalm-return Parser<string>
 * @api
 * @psalm-pure
 */
function eol(): Parser
{
    return either(newline(), crlf())->label("<EOL>");
}

/**
 * Skip zero or more white space characters.
 *
 * @psalm-return Parser<null>
 * @api
 * @psalm-pure
 */
function skipSpace(): Parser
{
    return skipWhile(isSpace());
}

/**
 * Like 'skipSpace', but does not accept newlines and carriage returns.
 *
 * @psalm-return Parser<null>
 * @api
 * @see skipSpace
 * @psalm-pure
 */
function skipHSpace(): Parser
{
    return skipWhile(isHSpace());
}

/**
 * Skip one or more white space characters.
 *
 * @psalm-return Parser<null>
 * @api
 * @psalm-pure
 */
function skipSpace1(): Parser
{
    return skipWhile1(isSpace())->label("<space>");
}

/**
 * Like 'skipSpace1', but does not accept newlines and carriage returns.
 *
 * @psalm-return Parser<null>
 * @api
 * @see skipSpace1
 * @psalm-pure
 */
function skipHSpace1(): Parser
{
    return skipWhile1(isHSpace())->label("<space>");
}
