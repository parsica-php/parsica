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

use BadMethodCallException;
use Parsica\Parsica\Internal\Position;

/**
 * @template T
 */
interface ParseResult
{
    /**
     * True if the parser was successful.
     *
     * @api
     */
    public function isSuccess(): bool;

    /**
     * True if the parser has failed.
     *
     * @api
     */
    public function isFail(): bool;

    /**
     * The output of the parser.
     *
     * @psalm-return T
     * @api
     */
    public function output();

    /**
     * The part of the input that did not get parsed.
     *
     * @api
     */
    public function remainder(): Stream;

    /**
     * A message that indicates what the failed parser expected to find at its position in the input. It contains the
     * label that was attached to the parser.
     *
     * @see Parser::label()
     *
     * @api
     */
    public function expected(): string;

    /**
     * A message indicating the input that the failed parser got at the point where it failed. It's only informational,
     * so don't use this for processing. A future version might change this behaviour.
     *
     * @api
     */
    public function got(): Stream;

    /**
     * Append the output of two successful ParseResults. If one or both have failed, it returns the first failed
     * ParseResult.
     *
     * @psalm-param ParseResult<T> $other
     *
     * @psalm-return ParseResult<T>
     *
     * @api
     */
    public function append(ParseResult $other): ParseResult;

    /**
     * Map a function over the output
     *
     * @template T2
     *
     * @psalm-param callable(T):T2 $transform
     *
     * @psalm-return ParseResult<T2>
     *
     * @api
     */
    public function map(callable $transform): ParseResult;

    /**
     * Use the remainder of this ParseResult as the input for a parser.
     *
     * @template T2
     *
     * @psalm-param Parser<T2> $parser
     *
     * @psalm-return ParseResult<T2>
     *
     * @api
     */
    public function continueWith(Parser $parser): ParseResult;

    public function errorMessage() : string;

    /**
     * Get the last position of where the parser ended up when producing this result.
     */
    public function position(): Position;

    /**
     * Throw a ParserFailure exception if the Parser failed, or complain if you're trying to throw a successful
     * ParseResult.
     *
     * @api
     * @throws ParserHasFailed
     * @throws BadMethodCallException
     */
    public function throw() : void;
}
