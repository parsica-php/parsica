<?php declare(strict_types=1);

namespace Mathias\ParserCombinator;

use Mathias\ParserCombinator\Parser\Parser;

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
 * Parse a newline character. {\n}
 *
 * @return Parser<string>
 */
function tab(): Parser
{
    return char("\t")->label("tab");
}

/**
 * Skip zero or more white space characters.
 *
 * @return Parser<string>
 */
function skipSpace(): Parser
{
    return takeWhile(isSpace())->ignore();
}

/**
 * Like 'skipSpace', but does not accept newlines and carriage returns.
 *
 * @return Parser<string>
 * @see skipSpace
 */
function skipHSpace(): Parser
{
    return takeWhile(isHSpace())->ignore();
}

/**
 * Skip one or more white space characters.
 *
 * @return Parser<string>
 */
function skipSpace1(): Parser
{
    return takeWhile1(isSpace())->label("skipSpace1")->ignore();
}

/**
 * Like 'skipSpace1', but does not accept newlines and carriage returns.
 *
 * @return Parser<string>
 * @see skipSpace1
 */
function skipHSpace1(): Parser
{
    return takeWhile1(isHSpace())->label("skipHSpace1")->ignore();
}
