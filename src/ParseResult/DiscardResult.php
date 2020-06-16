<?php declare(strict_types=1);

namespace Mathias\ParserCombinator\ParseResult;

use BadMethodCallException;
use Exception;
use Mathias\ParserCombinator\Parser\Parser;

/**
 * Discard is a special case of ParseSuccess. It represents a success but discards the result.
 *
 * @template T
 */
final class DiscardResult implements ParseResult
{
    private string $remainder;

    public function __construct(string $remainder)
    {
        $this->remainder = $remainder;
    }

    public function isSuccess(): bool
    {
        return true;
    }

    public function isFail(): bool
    {
        return false;
    }

    public function output()
    {
        throw new Exception("DiscardResult has no output");
    }

    public function remainder(): string
    {
        return $this->remainder;
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
    public function append(ParseResult $other): ParseResult
    {
        return $other;
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
     */
    public function continueWith(Parser $parser): ParseResult
    {
        return $parser->run($this->remainder());
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