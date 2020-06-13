<?php declare(strict_types=1);

namespace Mathias\ParserCombinator;

use Mathias\ParserCombinator\Parser\Parser;
use function Mathias\ParserCombinator\Predicates\isDigit;

/**
 * Parse 0-9. Like all parsers, this returns the digit as a string. Use into1('intval')
 * or similar to cast it to a numeric type.
 *
 * @return Parser<string>
 */
function digit(): Parser
{
    return satisfy(isDigit())->label('digit');
}

/**
 * Parse a float. Like all parsers, this returns the float as a string. Use fmap('floatval')
 * or similar to cast it to a numeric type.
 */
function float(): Parser
{
    return
        atLeastOne(digit())
            ->mappend(
                optional(
                    char('.')
                        ->mappend(atLeastOne(digit()))
                )
            )->label('float');

}