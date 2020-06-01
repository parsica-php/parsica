<?php declare(strict_types=1);

namespace Mathias\ParserCombinator\ParseResult;

use BadMethodCallException;
use Mathias\ParserCombinator\Parser\Parser;

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
     * @deprecated depends on this being a ParseResult<string>, but it should work for ParseResult<Semigroup>
     * @param ParseResult<T> $other
     *
     * @return ParseResult<T>
     *
     * @TODO    This is hardcoded to only deal with strings.
     *
     * @TODO    Can we avoid suppressing this? We'd need some way of indicating that the parsed types are monoids.
     *          For strings that would mean wrapping them in a String class, for user types it would mean they have to
     *          implement Monoid, which is going to be impractical for parsing into existing types.
     * @psalm-suppress MixedOperand
     */
    public function mappend(ParseResult $other): ParseResult
    {
        return succeed($this->parsed() . $other->parsed(), $other->remaining());
    }

    /**
     * Map a function over the parsed result
     *
     * @template T2
     *
     * @param callable(T):T2 $transform
     *
     * @return ParseResult<T2>
     */
    public function fmap(callable $transform): ParseResult
    {
        return succeed($transform($this->parsed), $this->remaining);
    }

    /**
     * @template T2
     * @param Parser<T2> $parser
     * @return ParseResult<T2>
     * @deprecated
     */
    public function continueOnRemaining(Parser $parser) : ParseResult
    {
        return $parser->run($this->remaining());
    }

    /**
     * Return the first successful ParseResult if any, and otherwise return the first failing one.
     *
     * @param ParseResult<T> $other
     * @return ParseResult<T>
     */
    public function alternative(ParseResult $other): ParseResult
    {
        return $this;
    }
}
