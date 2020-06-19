<?php declare(strict_types=1);

namespace Mathias\ParserCombinator;

/**
 * Parse a float. Returns the float as a string. Use ->map('floatval')
 * or similar to cast it to a numeric type.
 *
 * @return Parser<string>
 *
 * @deprecated @TODO doesn't support signed numbers yet
 */
function float(): Parser
{
    return
        atLeastOne(digitChar())
            ->append(
                optional(
                    char('.')
                        ->append(atLeastOne(digitChar()))
                )
            )->label('float');
}