<?php declare(strict_types=1);

namespace Mathias\ParserCombinator;

/**
 * Create a recursive parser. Used in combination with recurse(Parser).
 *
 * For an example see {@see RecursiveParserTest}.
 *
 * @template T
 * @return Parser<T>
 */
function recursive(): Parser
{
    return Parser::recursive();
}