<?php declare(strict_types=1);

namespace Mathias\ParserCombinator;

use Mathias\ParserCombinator\Parser\Parser;
use function Mathias\ParserCombinator\Predicates\isCharCode;
use function Mathias\ParserCombinator\Predicates\isDigit;
use function Mathias\ParserCombinator\Predicates\isHexDigit;

/**
 * Parse 0-9. Returns the digit as a string. Use ->map('intval')
 * or similar to cast it to a numeric type.
 *
 * @return Parser<string>
 */
function digitChar(): Parser
{
    return satisfy(isDigit())->label('digit');
}

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

/**
 * Parse a binary character 0 or 1.
 *
 * @return Parser<string>
 *
 * @deprecated @TODO doesn't support signed numbers yet
 */
function binDigitChar(): Parser
{
    return satisfy(isCharCode([0x30, 0x31]))->label("binDigitChar");
}

/**
 * Parse an octodecimal character 0-7.
 *
 * @return Parser<string>
 *
 * @deprecated @TODO doesn't support signed numbers yet
 */
function octDigitChar(): Parser
{
    return satisfy(isCharCode(range(0x30, 0x37)))->label("octDigitChar");
}

/**
 * Parse a hexadecimal numeric character 0123456789abcdefABCDEF.
 *
 * @return Parser<string>
 *
 * @deprecated @TODO doesn't support signed numbers yet
 */
function hexDigitChar(): Parser
{
    return satisfy(isHexDigit())->label("hexDigitChar");
}