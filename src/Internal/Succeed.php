<?php declare(strict_types=1);

namespace Mathias\ParserCombinator\Internal;

use BadMethodCallException;
use Exception;
use Mathias\ParserCombinator\Parser;
use Mathias\ParserCombinator\ParseResult;
use Mathias\ParserCombinator\ParseResult\T;

/**
 * @internal
 *
 * @template T
 */
final class Succeed implements ParseResult
{
    /**
     * @var T
     */
    private $output;

    private string $remainder;

    /**
     * @param T $output
     */
    public function __construct($output, string $remainder)
    {
        $this->output = $output;
        $this->remainder = $remainder;
    }

    /**
     * @return T
     */
    public function output()
    {
        return $this->output;
    }

    public function remainder(): string
    {
        return $this->remainder;
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
    public function append(ParseResult $other): ParseResult
    {
        if ($other->isFail()) {
            return $other;
        } else {
            /** @psalm-suppress ArgumentTypeCoercion */
            return $this->appendSuccess($other);
        }
    }

    /**
     * @TODO    This is hardcoded to only deal with certain types. We need an interface with a append() for arbitrary types.
     *
     * @psalm-suppress MixedArgumentTypeCoercion
     */
    private function appendSuccess(Succeed $other): ParseResult
    {
        $type1 = $this->type();
        $type2 = $other->type();
        if ($type1 !== $type2) throw new Exception("Append only works for ParseResult<T> instances with the same type T, got ParseResult<$type1> and ParseResult<$type2>.");

        switch ($type1) {
            case 'string':
                /** @psalm-suppress MixedOperand */
                return new Succeed($this->output() . $other->output(), $other->remainder());
            case 'array':
                /** @psalm-suppress MixedArgument */
                return new Succeed(
                    array_merge($this->output(), $other->output()),
                    $other->remainder()
                );
            default:
                throw new Exception("@TODO cannot append ParseResult<$type1>");
        }
    }

    /**
     * Map a function over the output
     *
     * @template T2
     *
     * @param callable(T):T2 $transform
     *
     * @return ParseResult<T2>
     */
    public function map(callable $transform): ParseResult
    {
        return new Succeed($transform($this->output), $this->remainder);
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

    /**
     * Return the first successful ParseResult if any, and otherwise return the first failing one.
     *
     * @param ParseResult<T> $other
     *
     * @return ParseResult<T>
     */
    public function alternative(ParseResult $other): ParseResult
    {
        return $this;
    }

    /**
     * The type of the ParseResult
     *
     * @return class-string<T>
     *
     * @psalm-suppress MoreSpecificReturnType
     * @psalm-suppress LessSpecificReturnStatement
     * @psalm-suppress MixedArgumentTypeCoercion
     */
    private function type(): string
    {
        $t = gettype($this->output);
        return $t == 'object' ? get_class($this->output) : $t;
    }
}
