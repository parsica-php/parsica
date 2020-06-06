<?php declare(strict_types=1);

namespace Mathias\ParserCombinator\ParseResult;

use BadMethodCallException;
use Mathias\ParserCombinator\Parser\Parser;

/**
 * Discard is a special case of ParseSuccess
 */
final class DiscardResult implements ParseResult
{
    private string $remaining;

    public function __construct(string $remaining)
    {
        $this->remaining = $remaining;
    }

    public function isSuccess(): bool
    {
        return true;
    }

    public function isFail(): bool
    {
        return false;
    }

    public function parsed()
    {
        throw new \Exception("ParseNothing has no parsed value");
    }

    public function remaining(): string
    {
        return $this->remaining;
    }

    public function expected(): string
    {
        throw new BadMethodCallException("Can't read the expectation of a succeeded ParseResult.");
    }

    public function got(): string
    {
        throw new BadMethodCallException("Can't read the expectation of a succeeded ParseResult.");
    }

    public function mappend(ParseResult $other): ParseResult
    {
        return MAppend::mappend($this, $other);
    }

    public function fmap(callable $transform): ParseResult
    {
        return $this;
    }

    public function alternative(ParseResult $other): ParseResult
    {
        return $this;
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
        return $parser->run($this->remaining());
    }

    public function isDiscarded(): bool
    {
        return true;
    }
}