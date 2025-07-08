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
use Parsica\Parsica\Parser;
use Parsica\Parsica\ParseResult;
use Parsica\Parsica\ParserHasFailed;
use Parsica\Parsica\Stream;
use function Parsica\Parsica\isEqual;
use function Parsica\Parsica\notPred;

/**
 * The return value of a failed parser.
 *
 * @template T
 * @internal
 * @psalm-immutable
 */
final class Fail implements ParseResult
{
    private string $expected;
    private Stream $got;

    /**
     * @internal
     */
    public function __construct(string $expected, Stream $got)
    {
        $this->expected = $expected;
        $this->got = $got;
    }

    /**
     * @api
     */
    public function errorMessage(): string
    {
        try {
            $firstChar = $this->got->take1()->chunk();
            $unexpected = Ascii::printable($firstChar);
            $body = $this->got()->takeWhile(notPred(isEqual("\n")))->chunk();
        } catch (EndOfStream $e) {
            $unexpected = $body = "<EOF>";
        }
        $lineNumber = $this->got->position()->line();
        $spaceLength = str_repeat(" ", strlen((string)$lineNumber));
        $expecting = $this->expected;
        $position = $this->got->position()->pretty();
        $columnNumber = $this->got->position()->column();
        $leftDots = $columnNumber == 1 ? "" : "...";
        $leftSpace = $columnNumber == 1 ? "" : "   ";
        $bodyLine = "$lineNumber | $leftDots$body";
        $bodyLine = strlen($bodyLine) > 80 ? (substr($bodyLine, 0, 77) . "...") : $bodyLine;

        return
            "$position\n"
            . "$spaceLength |\n"
            . "$bodyLine\n"
            . "$spaceLength | $leftSpace^— column $columnNumber\n"
            . "Unexpected $unexpected\n"
            . "Expecting $expecting";
    }

    public function got(): Stream
    {
        return $this->got;
    }

    public function expected(): string
    {
        return $this->expected;
    }

    public function isSuccess(): bool
    {
        return false;
    }

    public function isFail(): bool
    {
        return !$this->isSuccess();
    }

    /**
     * @psalm-return T
     */
    public function output()
    {
        throw new BadMethodCallException("Can't read the output of a failed ParseResult.");
    }

    /**
     * @psalm-param ParseResult<T> $other
     *
     * @psalm-return ParseResult<T>
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
     * @psalm-param callable(T) : T2 $transform
     *
     * @psalm-return ParseResult<T2>
     */
    public function map(callable $transform): ParseResult
    {
        return $this;
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
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function remainder(): Stream
    {
        throw new BadMethodCallException("Can't read the remainder of a failed ParseResult.");
    }

    /**
     * @inheritDoc
     */
    public function position(): Position
    {
        return $this->got->position();
    }

    /**
     * @inheritDoc
     */
    public function throw() : void
    {
        throw new ParserHasFailed($this);
    }


}
