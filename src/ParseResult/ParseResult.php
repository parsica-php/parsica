<?php declare(strict_types=1);

namespace Mathias\ParserCombinator\ParseResult;

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
    public function parsed();

    public function remaining(): string;

    public function expected(): string;

    public function got(): string;

    /**
     * @param ParseResult<T> $other
     *
     * @return ParseResult<T>
     */
    public function mappend(ParseResult $other): ParseResult;

    /**
     * Map a function over the parsed result
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
}
