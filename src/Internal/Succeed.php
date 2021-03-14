<?php declare(strict_types=1);
/**
 * This file is part of the Parsica library.
 *
 * Copyright (c) 2020 Mathias Verraes <mathias@verraes.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Parsica\Parsica\Internal;

use BadMethodCallException;
use Exception;
use Parsica\Parsica\Parser;
use Parsica\Parsica\ParseResult;
use Parsica\Parsica\ParserHasFailed;
use Parsica\Parsica\Stream;

/**
 * @internal
 *
 * @template T
 */
final class Succeed implements ParseResult
{
    /**
     * @psalm-var T
     */
    private $output;

    private Stream $remainder;

    /**
     * @psalm-param T $output
     *
     * @internal
     */
    public function __construct($output, Stream $remainder)
    {
        $this->output = $output;
        $this->remainder = $remainder;
    }

    /**
     * @psalm-return T
     */
    public function output()
    {
        return $this->output;
    }

    public function remainder(): Stream
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

    public function got(): Stream
    {
        throw new BadMethodCallException("Can't read the expectation of a succeeded ParseResult.");
    }

    /**
     * @inheritDoc
     *
     * @psalm-param ParseResult<T> $other
     * @psalm-return ParseResult<T>
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
        $type1isNull = is_null($this->output);
        $type2isNull = is_null($other->output);

        // Ignore nulls
        if($type1isNull && $type2isNull) {
            return new Succeed(null, $other->remainder);
        } elseif(!$type1isNull && $type2isNull) {
            return new Succeed($this->output, $other->remainder);
        } elseif($type1isNull && !$type2isNull) {
            return new Succeed($other->output, $other->remainder);
        }

        // Only append for the same type
        /*
        if ($type1 !== $type2) {
            throw new Exception("Append only works for ParseResult<T> instances with the same type T, got ParseResult<$type1> and ParseResult<$type2>.");
        }
        */

        if (is_string($this->output)) {
            /** @psalm-suppress MixedOperand */
            return new Succeed($this->output . $other->output, $other->remainder);
        } elseif (is_array($this->output)) {
            /** @psalm-suppress MixedArgument */
            return new Succeed(
                array_merge($this->output, $other->output),
                $other->remainder
            );
        }

        throw new Exception("@TODO cannot append ParseResult<". gettype($this->output) .">");
    }

    /**
     * Map a function over the output
     *
     * @template T2
     *
     * @psalm-param callable(T):T2 $transform
     *
     * @psalm-return ParseResult<T2>
     */
    public function map(callable $transform): ParseResult
    {
        return new Succeed($transform($this->output), $this->remainder);
    }

    /**
     * @template T2
     *
     * @psalm-param Parser<T2> $parser
     *
     * @psalm-return ParseResult<T2>
     */
    public function continueWith(Parser $parser): ParseResult
    {
        return $parser->run($this->remainder);
    }

    public function errorMessage(): string
    {
        throw new BadMethodCallException("A succeeded ParseResult has no error message.");
    }

    /**
     * @inheritDoc
     */
    public function position(): Position
    {
        return $this->remainder->position();
    }

    /**
     * @inheritDoc
     */
    public function throw() : void
    {
        throw new BadMethodCallException("You can't throw a successful ParseResult.");
    }
}
