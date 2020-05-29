<?php declare(strict_types=1);

namespace Mathias\ParserCombinator\ParseResult;

use BadMethodCallException;

/**
 * @template T
 */
final class ParseSuccess implements ParseResult
{
    /**
     * @var T
     */
    private $parsed;

    private string $remaining;

    /**
     * @param T $parsed
     */
    public function __construct($parsed, string $remaining)
    {
        $this->parsed = $parsed;
        $this->remaining = $remaining;
    }

    /**
     * @return T
     */
    public function parsed()
    {
        return $this->parsed;
    }

    public function remaining(): string
    {
        return $this->remaining;
    }

    public function isSuccess(): bool
    {
        return true;
    }

    public function isFail(): bool
    {
        return !$this->isSuccess();
    }

    public function expected(): string
    {
        throw new BadMethodCallException("Can't read the expectation of a succeeded ParseResult.");
    }

    public function got(): string
    {
        throw new BadMethodCallException("Can't read the expectation of a succeeded ParseResult.");
    }

    /**
     * @param ParseResult<T> $other
     * @return ParseResult<T>
     *
     * @TODO can we avoid suppressing this?
     * @psalm-suppress MixedOperand
     */
    public function mappend(ParseResult $other): ParseResult
    {
        return succeed($this->parsed() . $other->parsed(), $other->remaining());
    }

    /**
     * Map a function over the parsed result
     * @template T2
     * @param callable(T):T2 $transform
     * @return ParseResult<T2>
     */
    public function fmap(callable $transform): ParseResult
    {
        return succeed($transform($this->parsed), $this->remaining);
    }
}
