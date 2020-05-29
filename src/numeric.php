<?php declare(strict_types=1);

namespace Mathias\ParserCombinator;

use Mathias\ParserCombinator\Parser\Parser;

/**
 * Parse 0-9. Like all parsers, this returns the digit as a string. Use into1('intval')
 * or similar to cast it to a numeric type.
 *
 * @return Parser<string>
 */
function digit(): Parser
{
    return any(
        char('0'),
        char('1'),
        char('2'),
        char('3'),
        char('4'),
        char('5'),
        char('6'),
        char('7'),
        char('8'),
        char('9'),
    );
}

/**
 * Parse a float. Like all parsers, this returns the float as a string. Use into1('floatval')
 * or similar to cast it to a numeric type.
 */
function float(): Parser
{
    return
        atLeastOne(digit())
            ->followedBy(
                optional(
                    char('.')
                        ->followedBy(atLeastOne(digit()))
                )
            );
}