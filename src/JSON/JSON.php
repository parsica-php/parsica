<?php declare(strict_types=1);
/**
 * This file is part of the Parsica library.
 *
 * Copyright (c) 2020 Mathias Verraes <mathias@verraes.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Verraes\Parsica\JSON;

use Verraes\Parsica\Parser;
use function Verraes\Parsica\{anySingleBut,
    between,
    char,
    choice,
    collect,
    float,
    hexDigitChar,
    isCharCode,
    keepFirst,
    recursive,
    repeat,
    satisfy,
    sepBy,
    string,
    zeroOrMore};

/**
 * JSON parser and utility parsers
 *
 * @TODO fix psalm annotations
 */
final class JSON
{
    private function __construct()
    {
    }

    /**
     * Fully compliant JSON parser, built entirely in Parsica. The output is compatible with PHP's native json_decode().
     *
     * It was built to illustrate the usage of Parsica on a real world format, and to benchmark Parsica against
     * json_decode(). It will probably never reach the same performance as a C extension, so it shouldn't be used for
     * typical production JSON parsing.
     *
     * It could however be useful as a basis to expand into a custom JSON parser, for example to expand JSON with custom
     * notations or comments, or to return a custom AST instead of json_decode()'s plain PHP objects & arrays.
     *
     * To understand the terminology and the structure, have a peak at {@see https://www.json.org/json-en.html}
     *
     * @api
     * @psalm-return Parser<mixed>
     */
    public static function json(): Parser
    {
        return JSON::ws()->sequence(JSON::element());
    }

    /**
     * @psalm-return Parser<mixed>
     */
    public static function element(): Parser
    {
        // Memoize $element so we can keep reusing it for recursion.
        static $element;
        if (!isset($element)) {
            $element = recursive();
            $element->recurse(
                choice(
                    JSON::object(),
                    JSON::array(),
                    JSON::stringLiteral(),
                    JSON::number(),
                    JSON::true(),
                    JSON::false(),
                    JSON::null(),
                )
            );
        }
        return $element;
    }

    /**
     * @psalm-return Parser<object>
     */
    public static function object(): Parser
    {
        return map(
            between(
                JSON::token(char('{')),
                JSON::token(char('}')),
                sepBy(
                    JSON::token(char(',')),
                    JSON::member()
                )
            ),
            /**
             * @psalm-param list<array{string:mixed}> $members
             * @psalm-return object
             */
            fn(array $members) => (object)array_merge($members));
    }

    /**
     * @psalm-return Parser<list>
     */
    public static function array(): Parser
    {
        return between(
            JSON::token(char('[')),
            JSON::token(char(']')),
            sepBy(
                JSON::token(char(',')), JSON::element()
            )
        );
    }

    /**
     * @psalm-return Parser<bool>
     */
    public static function true(): Parser
    {
        return JSON::token(string('true'))->map(fn($_) => true)->label('true');
    }

    /**
     * @psalm-return Parser<bool>
     */
    public static function false(): Parser
    {
        return JSON::token(string('false'))->map(fn($_) => false)->label('false');
    }

    /**
     * @psalm-return Parser<null>
     */
    public static function null(): Parser
    {
        return JSON::token(string('null'))->map(fn($_) => null)->label('null');
    }

    /**
     * Whitespace
     *
     * @psalm-return Parser<null>
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

    public static function number(): Parser
    {
        return JSON::token(float())->map('floatval')->label("number");
    }

    public static function stringLiteral(): Parser
    {
        return JSON::token(
            between(
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
                        anySingleBut('"') // @TODO is a backslash  that is not one of the escapes above legal?
                    )
                )
            )->map(fn($o) => (string)$o) // because the empty json string returns null
        )->label("string literal");
    }

    /**
     * @return Parser<array{string:mixed}>
     */
    public static function member(): Parser
    {
        return map(
            collect(
                JSON::stringLiteral(),
                JSON::token(char(':')),
                JSON::token(JSON::element())
            ),
            /**
             * @psalm-param array{0:string, 1:string, 2:mixed}
             */
            fn(array $o): array => [$o[0] => $o[2]]);
    }
}
