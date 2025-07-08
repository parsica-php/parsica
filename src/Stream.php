<?php declare(strict_types=1);
/**
 * This file is part of the Parsica library.
 *
 * Copyright (c) 2020 Mathias Verraes <mathias@verraes.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Parsica\Parsica;

use Parsica\Parsica\Internal\Position;
use Parsica\Parsica\Internal\TakeResult;

/**
 * Represents an input stream. This allows us to have different types of input, each with their own optimizations.
 *
 * @psalm-immutable
 */
interface Stream
{
    /**
     * Extract a single token from the stream. Throw if the stream is empty.
     *
     * @throw EndOfStream
     * @psalm-mutation-free
     */
    public function take1(): TakeResult;

    /**
     * Try to extract a chunk of length $n, or if the stream is too short, the rest of the stream.
     *
     * Valid implementation should follow the rules:
     *
     * 1. If the requested length <= 0, the empty token and the original stream should be returned.
     * 2. If the requested length > 0 and the stream is empty, throw EndOfStream.
     * 3. In other cases, take a chunk of length $n (or shorter if the stream is not long enough) from the input stream
     * and return the chunk along with the rest of the stream.
     *
     * @throw EndOfStream
     * @psalm-mutation-free
     */
    public function takeN(int $n): TakeResult;


    /**
     * Extract a chunk of the stream, by taking tokens as long as the predicate holds. Return the chunk and the rest of
     * the stream.
     *
     * @TODO This method isn't strictly necessary but let's see.
     *
     * @psalm-param pure-callable(string):bool $predicate
     * @psalm-mutation-free
     */
    public function takeWhile(callable $predicate) : TakeResult;

    /**
     * @deprecated We will need to get rid of this again at some point, we can't assume all streams will be strings
     * @psalm-mutation-free
     */
    public function __toString(): string;

    /**
     * Test if the stream is at its end.
     * @psalm-mutation-free
     */
    public function isEOF(): bool;

    /**
     * The position of the parser in the stream.
     *
     * @internal
     * @psalm-mutation-free
     */
    public function position() : Position;
}
