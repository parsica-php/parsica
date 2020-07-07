<?php declare(strict_types=1);
/**
 * This file is part of the Parsica library.
 *
 * Copyright (c) 2020 Mathias Verraes <mathias@verraes.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Verraes\Parsica\Internal;

use BadMethodCallException;
use Exception;
use Verraes\Parsica\Parser;
use Verraes\Parsica\ParseResult;
use Verraes\Parsica\Stream;

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
     * @psalm-param ParseResult<T> $other
     *
     * @psalm-return ParseResult<T>
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

        // Ignore nulls
        if($type1 === 'NULL' && $type2 === 'NULL') {
            return new Succeed(null, $other->remainder());
        } elseif($type1 !== 'NULL' && $type2 === 'NULL') {
            return new Succeed($this->output(), $other->remainder());
        } elseif($type1 === 'NULL' && $type2 !== 'NULL') {
            return new Succeed($other->output(), $other->remainder());
        }

        // Only append for the same type
        if ($type1 !== $type2) {
            throw new Exception("Append only works for ParseResult<T> instances with the same type T, got ParseResult<$type1> and ParseResult<$type2>.");
        }

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
        return $parser->run($this->remainder());
    }

    /**
     * The type of the ParseResult
     *
     * @psalm-return class-string<T>|'NULL'|'string'|'array'
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
}
