<?php declare(strict_types=1);
/**
 * This file is part of the Parsica library.
 *
 * Copyright (c) 2020 Mathias Verraes <mathias@verraes.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Verraes\Parsica;

use Verraes\Parsica\ParseResult\T;

/**
 * @template T
 */
interface ParseResult
{
    public function isSuccess(): bool;

    public function isFail(): bool;

    /**
     * @return T
     */
    public function output();

    public function remainder(): string;

    public function expected(): string;

    public function got(): string;

    /**
     * @param ParseResult<T> $other
     *
     * @return ParseResult<T>
     */
    public function append(ParseResult $other): ParseResult;

    /**
     * Map a function over the output
     *
     * @template T2
     *
     * @param callable(T):T2 $transform
     *
     * @return ParseResult<T2>
     */
    public function map(callable $transform): ParseResult;

    /**
     * Return the first successful ParseResult if any, and otherwise return the first failing one.
     *
     * @param ParseResult<T> $other
     *
     * @return ParseResult<T>
     */
    public function alternative(ParseResult $other): ParseResult;

    /**
     * @template T2
     *
     * @param Parser<T2> $parser
     *
     * @return ParseResult<T2>
     */
    public function continueWith(Parser $parser): ParseResult;
}