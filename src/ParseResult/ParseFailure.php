<?php declare(strict_types=1);

namespace Mathias\ParserCombinator\ParseResult;

use BadMethodCallException;
use Exception;
use Mathias\ParserCombinator\Parser\Parser;

/**
 * @template T
 */
final class ParseFailure extends Exception implements ParseResult
{
    private string $expected;
    private string $got;

    public function __construct(string $expected, string $got)
    {
        $this->expected = $expected;
        $this->got = $got;
        parent::__construct("Expected: $expected, got $got");
    }

    public function isSuccess(): bool
    {
        return false;
    }

    public function isFail(): bool
    {
        return !$this->isSuccess();
    }

    public function expected(): string
    {
        return $this->expected;
    }

    public function got(): string
    {
        return $this->got;
    }

    /**
     * @return T
     */
    public function parsed()
    {
        throw new BadMethodCallException("Can't read the parsed value of a failed ParseResult.");
    }

    public function remaining(): string
    {
        throw new BadMethodCallException("Can't read the remaining string of a failed ParseResult.");
    }

    /**
     * @param ParseResult<T> $other
     *
     * @return ParseResult<T>
     */
    public function mappend(ParseResult $other): ParseResult
    {
        return $this;
    }

    /**
     * Map a function over the parsed result
     *
     * @template T2
     *
     * @param callable(T) : T2 $transform
     *
     * @return ParseResult<T2>
     */
    public function fmap(callable $transform): ParseResult
    {
        return fail($this->expected, $this->got);
    }

    /**
     * Return the first successful ParseResult if any, and otherwise return the first failing one.
     *
     * @param ParseResult<T> $other
     * @return ParseResult<T>
     */
    public function alternative(ParseResult $other): ParseResult
    {
        return $other->isSuccess() ? $other : $this;
    }

    /**
     * @template T2
     *
     * @param Parser<T2> $parser
     *
     * @return ParseResult<T2>
     * @deprecated
     */
    public function continueOnRemaining(Parser $parser): ParseResult
    {
        return $this;
    }
}