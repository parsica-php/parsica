<?php declare(strict_types=1);

namespace Mathias\ParserCombinator;

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
 *
 * @return Parser<string>
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
