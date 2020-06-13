<?php declare(strict_types=1);

namespace Mathias\ParserCombinator\ParseResult;

use Mathias\ParserCombinator\Parser\Parser;

/**
 * @template T
 */
interface ParseResult
{
    public function isSuccess(): bool;

    public function isFail(): bool;

    /**
     * @return T
     */
    public function output();

    public function remainder(): string;

    public function expected(): string;

    public function got(): string;

    /**
     * @param ParseResult<T> $other
     *
     * @return ParseResult<T>
     */
    public function mappend(ParseResult $other): ParseResult;

    /**
     * Map a function over the output
     *
     * @template T2
     *
     * @param callable(T):T2 $transform
     *
     * @return ParseResult<T2>
     */
    public function fmap(callable $transform): ParseResult;

    /**
     * Return the first successful ParseResult if any, and otherwise return the first failing one.
     *
     * @param ParseResult<T> $other
     * @return ParseResult<T>
     */
    public function alternative(ParseResult $other) : ParseResult;

    /**
     * @template T2
     *
     * @param Parser<T2> $parser
     *
     * @return ParseResult<T2>
     */
    public function continueOnRemaining(Parser $parser): ParseResult;

    /**
     * Discard a successful result or return the failed result.
     */
    public function discard() : ParseResult;

    public function isDiscarded() : bool;
}
