<?php declare(strict_types=1);
/**
 * This file is part of the Parsica library.
 *
 * Copyright (c) 2020 Mathias Verraes <mathias@verraes.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Verraes\Parsica\Internal;

use Verraes\Parsica\Parser;
use Verraes\Parsica\ParseResult;
use function Verraes\Parsica\ParseResult\fail;
use function Verraes\Parsica\ParseResult\succeed;

/**
 * @internal
 */
final class TakeWhile
{
    /**
     * @internal
     * Keep parsing 0 or more characters as long as the predicate holds.
     *
     * @template T
     *
     * @param callable(string) : bool $predicate
     *
     * @return Parser<T>
     */
    public static function _takeWhile(callable $predicate): Parser
    {
        /**
         * @see \Tests\Verraes\Parsica\v0_3_0\primitivesTest::not_sure_how_takeWhile_should_deal_with_EOF()
         */
        return Parser::make(
            fn(string $input): ParseResult => //self::isEOF($input) ?
                //    new Fail("takeWhile(predicate)", "EOF") :
            self::parseRemainingInput($input, $predicate)
        );
    }

    /**
     * @param callable(string) : bool $predicate
     */
    private static function parseRemainingInput(string $input, callable $predicate): ParseResult
    {
        $chunk = "";
        $remaining = $input;
        while (!self::isEOF($remaining) && self::matchFirst($predicate, $remaining)) {
            $chunk .= mb_substr($remaining, 0, 1);
            $remaining = mb_substr($remaining, 1);
        }
        return new Succeed($chunk, $remaining);
    }

    private static function isEOF(string $input): bool
    {
        return mb_strlen($input) === 0;
    }

    /**
     * @param callable(string) : bool $predicate
     */
    private static function matchFirst(callable $predicate, string $str): bool
    {
        return $predicate(mb_substr($str, 0, 1));
    }

    /**
     * @internal
     * Keep parsing 1 or more characters as long as the predicate holds.
     *
     * @template T
     *
     * @param callable(string) : bool $predicate
     *
     * @return Parser<T>
     */
    public static function _takeWhile1(callable $predicate): Parser
    {
        return Parser::make(
            fn(string $input): ParseResult => !self::matchFirst($predicate, $input) ?
                new Fail("takeWhile1(predicate)", $input) :
                self::parseRemainingInput($input, $predicate)
        );
    }

    /**
     * @internal
     * Skip 0 or more characters as long as the predicate holds.
     *
     * @template T
     *
     * @param callable(string) : bool $predicate
     *
     * @return Parser<T>
     */
    public static function _skipWhile(callable $predicate): Parser
    {
        return Parser::make(
            fn(string $input): ParseResult => self::skipRemainingInput($input, $predicate)
        );
    }

    /**
     * @param callable(string) : bool $predicate
     */
    private static function skipRemainingInput(string $input, callable $predicate): ParseResult
    {
        $output = "";
        $remaining = $input;
        while (!self::isEOF($remaining) && self::matchFirst($predicate, $remaining)) {
            $remaining = mb_substr($remaining, 1);
        }
        return new Succeed($output, $remaining);
    }

    /**
     * @internal
     * Skip 1 or more characters as long as the predicate holds.
     *
     * @template T
     *
     * @param callable(string) : bool $predicate
     *
     * @return Parser<T>
     */
    public static function _skipWhile1(callable $predicate): Parser
    {
        return Parser::make(
            fn(string $input): ParseResult => !self::matchFirst($predicate, $input) ?
                new Fail("skipWhile1(predicate)", $input) :
                self::skipRemainingInput($input, $predicate)
        );
    }

}
