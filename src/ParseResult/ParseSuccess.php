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
     * @param ParseResult<T> $other
     *
     * @return ParseResult<T>
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
            return succeed($this->parsed(), $other->remaining());
        } else {
            /** @psalm-suppress ArgumentTypeCoercion */
            return $this->mappendSuccess($other);
        }
    }

    /**
     * @TODO    This is hardcoded to only deal with certain types. We need an interface with a mappend() for arbitrary types.
     *
     * @psalm-suppress MixedArgumentTypeCoercion
     */
    private function mappendSuccess(ParseSuccess $other) : ParseResult
    {
        $type1 = $this->type();
        $type2 = $other->type();
        if($type1!==$type2) throw new \Exception("Mappend only works for ParseResult<T> instances with the same type T, got ParseResult<$type1> and ParseResult<$type2>.");

        switch($type1) {
            case 'string':
                /** @psalm-suppress MixedOperand */
                return succeed($this->parsed() . $other->parsed(), $other->remaining());
            case 'array':
                /** @psalm-suppress MixedArgument */
                return succeed(
                    array_merge(array_values($this->parsed()), array_values($other->parsed())),
                    $other->remaining()
                );
            default:
                throw new \Exception("@TODO cannot mappend ParseResult<$type1>");
        }
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

    /**
     * The type of the ParseResult
     * @return class-string<T>
     *
     * @psalm-suppress MoreSpecificReturnType
     * @psalm-suppress LessSpecificReturnStatement
     * @psalm-suppress MixedArgumentTypeCoercion
     */
    private function type() : string
    {
        $t = gettype($this->parsed);
        return $t == 'object' ? get_class($this->parsed) : $t;
    }

    public function isDiscarded(): bool
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function discard(): ParseResult
    {
        return new DiscardResult($this->remaining());
    }
}
