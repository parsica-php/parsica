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
use Verraes\Parsica\ParserFailure;

/**
 * @template T
 * @internal
 */
final class Fail extends Exception implements ParserFailure, ParseResult
{
    private string $expected;
    private string $got;

    /**
     * @internal
     */
    public function __construct(string $expected, string $got)
    {
        $this->expected = $expected;
        $this->got = $got;
        parent::__construct("Expected: $expected, got $got");
    }

    public function expected(): string
    {
        return $this->expected;
    }

    public function isSuccess(): bool
    {
        return false;
    }

    public function got(): string
    {
        return $this->got;
    }

    public function isFail(): bool
    {
        return !$this->isSuccess();
    }

    /**
     * @return T
     */
    public function output()
    {
        throw new BadMethodCallException("Can't read the output of a failed ParseResult.");
    }

    public function remainder(): string
    {
        throw new BadMethodCallException("Can't read the remainder of a failed ParseResult.");
    }

    /**
     * @param ParseResult<T> $other
     *
     * @return ParseResult<T>
     */
    public function append(ParseResult $other): ParseResult
    {
        return $this;
    }

    /**
     * Map a function over the output
     *
     * @template T2
     *
     * @param callable(T) : T2 $transform
     *
     * @return ParseResult<T2>
     */
    public function map(callable $transform): ParseResult
    {
        return new Fail($this->expected, $this->got);
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
        return $other->isSuccess() ? $other : $this;
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
        return $this;
    }
}
