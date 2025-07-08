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
 * Parse an integer and return it as a string. Use ->map('intval')
 * or similar to cast it to a numeric type.
 *
 * Example: "-123"
 *
 * @psalm-return Parser<string>
 * @api
 * @psalm-pure
 */
function integer(): Parser
{
    $zeroNine = digitChar();
    $oneNine = oneOfS("123456789");
    $minus = char('-');
    $digits = takeWhile1(isDigit())->label('at least one 0-9');
    /** @var Parser<string> $parser */
    $parser = choice(
        $minus->append($oneNine)->append($digits),
        $minus->append($zeroNine),
        $oneNine->append($digits),
        $zeroNine
    );
    return $parser;
}

/**
 * Parse a float and return it as a string. Use ->map('floatval')
 * or similar to cast it to a numeric type.
 *
 * Example: -123.456E-789
 *
 * @psalm-return Parser<string>
 * @psalm-suppress InvalidReturnType
 * @psalm-suppress InvalidReturnStatement
 * @api
 * @psalm-pure
 */
function float(): Parser
{
    $digits = takeWhile1(isDigit())->label('at least one 0-9');
    $fraction = char('.')->append($digits);
    $sign = char('+')->or(char('-'))->or(pure('+'));
    $exponent = assemble(
        charI('e')->map(fn(string $s) : string => strtoupper($s)),
        $sign,
        $digits
    );
    return assemble(
        integer(),
        optional($fraction),
        optional($exponent)
    )->label("float");
}
