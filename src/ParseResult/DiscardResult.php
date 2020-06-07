<?php declare(strict_types=1);

namespace Mathias\ParserCombinator\ParseResult;

use BadMethodCallException;
use Mathias\ParserCombinator\Parser\Parser;

/**
 * Discard is a special case of ParseSuccess. It represents a success but discards the result.
 *
 * @template T
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
        throw new \Exception("DiscardResult has no parsed value");
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

    /**
     *
     * @template T2
     *
     * @param ParseResult<T2> $other
     *
     * @return ParseResult<T2>
     *
     * @todo get rid of suppression?
     * @psalm-suppress MixedOperand
     * @psalm-suppress MixedArgumentTypeCoercion
     */
    public function mappend(ParseResult $other): ParseResult
    {
        if($other->isFail()) {
            return $other;
        } elseif($other->isDiscarded()) {
            return $other;
        } elseif($other->isSuccess()) {
            return $other;
        }
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

    /**
     * @inheritDoc
     */
    public function discard(): ParseResult
    {
        return $this;
    }
}