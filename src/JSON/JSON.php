<?php declare(strict_types=1);
/**
 * This file is part of the Parsica library.
 *
 * Copyright (c) 2020 Mathias Verraes <mathias@verraes.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * See https://www.json.org/json-en.html
 */

namespace Verraes\Parsica\JSON;

use Verraes\Parsica\Parser;
use function Verraes\Parsica\{anySingleBut,
    assemble,
    atLeastOne,
    between,
    char,
    charI,
    choice,
    collect,
    digitChar,
    hexDigitChar,
    isCharCode,
    keepFirst,
    oneOfS,
    optional,
    pure,
    repeat,
    satisfy,
    string,
    zeroOrMore
};

final class JSON
{


    /**
     * Whitespace
     */
    public static function ws(): Parser
    {
        return zeroOrMore(satisfy(isCharCode([0x20, 0x0A, 0x0D, 0x09])))->voidLeft(null)
            ->label('whitespace');
    }

    /**
     * Apply $parser and consume all the following whitespace.
     */
    public static function token(Parser $parser): Parser
    {
        return keepFirst($parser, JSON::ws());
    }

    public static function oneNine(): Parser
    {
        return oneOfS("123456789");
    }


    public static function digits(): Parser
    {
        return atLeastOne(digitChar());
    }

    public static function integer(): Parser
    {
        return self::_digits()->map('intval')->label("integer");
    }

    private static function _digits(): Parser{
        return choice(
            JSON::minus()->append(JSON::oneNine())->append(JSON::digits()),
            JSON::minus()->append(digitChar()),
            JSON::oneNine()->append(JSON::digits()),
            digitChar()
        );
    }

    public static function fraction(): Parser
    {
        return char('.')->append(JSON::digits());
    }

    public static function number(): Parser
    {
        return assemble(
            self::_digits(),
            optional(JSON::fraction()),
            optional(JSON::exponent())
        )->map('floatval')->label("number");
    }

    /**
     * Optional minus sgn for numbers
     */
    public static function minus(): Parser
    {
        return char('-');
    }

    /**
     * Optional + or -
     */
    public static function sign(): Parser
    {
        return char('+')->or(char('-'))->or(pure('+'));
    }

    public static function stringLiteral(): Parser
    {
        return between(
            char('"'),
            char('"'),
            zeroOrMore(
                choice(
                    string("\\\"")->map(fn($_) => '"'),
                    string("\\\\")->map(fn($_) => '\\'),
                    string("\\/")->map(fn($_) => '/'),
                    string("\\b")->map(fn($_) => mb_chr(8)),
                    string("\\f")->map(fn($_) => mb_chr(12)),
                    string("\\n")->map(fn($_) => "\n"),
                    string("\\r")->map(fn($_) => "\r"),
                    string("\\t")->map(fn($_) => "\t"),
                    string("\\u")->sequence(repeat(4, hexDigitChar()))->map(fn($o) => mb_chr(hexdec($o))),
                    anySingleBut('"')
                )
            )->map(fn($o) => (string)$o) // because the empty json string returns null
        )->label("string literal");
    }

    public static function key(): Parser
    {
        return JSON::token(JSON::stringLiteral());
    }

    public static function value(): Parser
    {
        return JSON::token(JSON::stringLiteral());
    }

    public static function key_value(): Parser
    {
        return collect(
            JSON::key(),
            JSON::token(char(':'))->followedBy(JSON::value())
        );
    }

    /**
     * The E in 1.23456E-78
     */
    public static function exponent(): Parser
    {
        return assemble(
            charI('e'),
            JSON::sign(),
            JSON::digits()
        );
    }
}
