<?php declare(strict_types=1);
/**
 * This file is part of the Parsica library.
 *
 * Copyright (c) 2020 Mathias Verraes <mathias@verraes.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Parsica\Parsica\JSON;

use Parsica\Parsica\Parser;
use function Parsica\Parsica\{any,
    between,
    char,
    choice,
    collect,
    float,
    hexDigitChar,
    isCharCode,
    keepFirst,
    map,
    recursive,
    repeat,
    satisfy,
    sepBy,
    string,
    takeWhile,
    zeroOrMore};

/**
 * JSON parser and utility parsers
 *
 * @TODO fix psalm annotations
 * @psalm-immutable
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
     * @template T
     * @psalm-return Parser<mixed>
     * @psalm-suppress DocblockTypeContradiction
     */
    public static function element(): Parser
    {
        // Memoize $element so we can keep reusing it for recursion.
        /** @psalm-var Parser<mixed> $element */
        static $element;
        if (!isset($element)) {
            $element = recursive();
            $element->recurse(
                any(
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
             * @psalm-param  list<array{string:mixed}> $members
             * @psalm-return object
             */
            fn(array $members):object => (object)array_merge(...$members));
    }

    /**
     * @psalm-return Parser<list<mixed>>
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
        return takeWhile(isCharCode([0x20, 0x0A, 0x0D, 0x09]))->voidLeft(null)
            ->label('whitespace');
    }

    /**
     * Apply $parser and consume all the following whitespace.
     *
     * @template T
     * @psalm-param Parser<T> $parser
     * @psalm-return Parser<T>
     */
    public static function token(Parser $parser): Parser
    {
        return keepFirst($parser, JSON::ws());
    }

    public static function number(): Parser
    {
        return JSON::token(float())->map('floatval')->label("number");
    }

    /**
     * @psalm-return Parser<string>
     */
    public static function stringLiteral(): Parser
    {
        return JSON::token(
            between(
                char('"'),
                char('"'),
                zeroOrMore(
                    choice(
                        satisfy(fn(string $char): bool => !in_array($char, ['"', '\\'])),
                        char("\\")->followedBy(
                            choice(
                                char("\"")->map(fn($_) => '"'),
                                char("\\")->map(fn($_) => '\\'),
                                char("/")->map(fn($_) => '/'),
                                char("b")->map(fn($_) => mb_chr(8)),
                                char("f")->map(fn($_) => mb_chr(12)),
                                char("n")->map(fn($_) => "\n"),
                                char("r")->map(fn($_) => "\r"),
                                char("t")->map(fn($_) => "\t"),
                                char("u")->sequence(repeat(4, hexDigitChar()))->map(fn($o) => mb_chr(hexdec($o))),
                            )
                        )
                    )
                )
            )->map(fn($o): string => (string)$o) // because the empty json string returns null
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
             * @psalm-param array{0:string, 1:string, 2:mixed} $o
             * @psalm-return array{string:mixed}
             * @psalm-suppress MoreSpecificReturnType
             * @psalm-suppress LessSpecificReturnStatement
             */
            fn(array $o): array => [$o[0] => $o[2]]);
    }
}
