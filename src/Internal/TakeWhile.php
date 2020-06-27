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
     * Keep parsing 0 or more characters as long as the predicate holds.
     *
     * @param callable(string) : bool $predicate
     *
     * @return Parser<T>
     * @internal
     *
     * @template T
     */
    public static function _takeWhile(callable $predicate): Parser
    {
        /**
         * @see \Tests\Verraes\Parsica\v0_4_0\primitivesTest::not_sure_how_takeWhile_should_deal_with_EOF()
         */
        return Parser::make(
            fn(Stream $input): ParseResult => //self::isEOF($input) ?
                //    new Fail("takeWhile(predicate)", "EOF") :
            self::parseRemainingInput($input, $predicate)
        );
    }

    /**
     * @param callable(string) : bool $predicate
     */
    private static function parseRemainingInput(Stream $input, callable $predicate): ParseResult
    {
        $chunk = "";
        $remaining = $input;
        while (!$remaining->isEOF() && self::testOneToken($predicate, $remaining)) {
            $t = $remaining->take1();
            $chunk .= $t->token();
            $remaining = $t->stream();
        }
        return new Succeed($chunk, $remaining);
    }


    /**
     * @param callable(string) : bool $predicate
     */
    private static function testOneToken(callable $predicate, Stream $stream): bool
    {
        return $predicate($stream->take1()->token());
    }

    /**
     * Keep parsing 1 or more characters as long as the predicate holds.
     *
     * @param callable(string) : bool $predicate
     *
     * @return Parser<T>
     * @internal
     *
     * @template T
     */
    public static function _takeWhile1(callable $predicate): Parser
    {
        return Parser::make(
            function (Stream $input) use ($predicate): ParseResult {
                // @todo generalise this?
                try {
                    $isToken = self::testOneToken($predicate, $input);
                } catch (EndOfStream $e) {
                    return new Fail("takeWhile1(predicate)", $input);
                }
                return !$isToken ?
                    new Fail("takeWhile1(predicate)", $input) :
                    self::parseRemainingInput($input, $predicate);
            }
        );
    }

    /**
     * Skip 0 or more characters as long as the predicate holds.
     *
     * @param callable(string) : bool $predicate
     *
     * @return Parser<T>
     * @internal
     *
     * @template T
     *
     */
    public static function _skipWhile(callable $predicate): Parser
    {
        return Parser::make(
            fn(Stream $input): ParseResult => self::skipRemainingInput($input, $predicate)
        );
    }

    /**
     * @param callable(string) : bool $predicate
     */
    private static function skipRemainingInput(Stream $input, callable $predicate): ParseResult
    {
        $output = "";
        $remaining = $input;
        while (!$remaining->isEOF() && self::testOneToken($predicate, $remaining)) {
            $t = $remaining->take1();
            $remaining = $t->stream();
        }
        return new Succeed($output, $remaining);
    }

    /**
     * Skip 1 or more characters as long as the predicate holds.
     *
     * @param callable(string) : bool $predicate
     *
     * @return Parser<T>
     * @internal
     *
     * @template T
     *
     */
    public static function _skipWhile1(callable $predicate): Parser
    {
        return Parser::make(
            function (Stream $input) use ($predicate): ParseResult {
                // @todo generalise this?
                try {
                    $isToken = self::testOneToken($predicate, $input);
                } catch (EndOfStream $e) {
                    return new Fail("takeWhile1(predicate)", $input);
                }
                return !$isToken ?
                    new Fail("skipWhile1(predicate)", $input) :
                    self::skipRemainingInput($input, $predicate);
            }
        );
    }

}
