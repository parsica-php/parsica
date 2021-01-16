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
use Verraes\Parsica\Parser;
use Verraes\Parsica\ParseResult;
use Verraes\Parsica\ParserHasFailed;
use Verraes\Parsica\Stream;
use function Verraes\Parsica\isEqual;
use function Verraes\Parsica\notPred;

/**
 * The return value of a failed parser.
 *
 * @template T
 * @internal
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
     * @return string
     * @api
     */
    public function errorMessage(): string
    {
        $firstChar = $this->got->peakBack();
        $text = $firstChar . $this->got->peakWhile(notPred(isEqual("\n")));
        $unexpected = Ascii::printable($firstChar);
        $position = $this->got->position()->retreat($firstChar);

        $lineNumber = $position->line();
        $spaceLength = str_repeat(" ", strlen((string)$lineNumber));
        $expecting = $this->expected;
        $pretty = $position->pretty($this->got->filename());
        $columnNumber = $position->column();
        $leftDots = $columnNumber == 1 ? "" : "...";
        $leftSpace = $columnNumber == 1 ? "" : "   ";
        $bodyLine = "$lineNumber | $leftDots$text";
        $bodyLine = strlen($bodyLine) > 80 ? (substr($bodyLine, 0, 79) . "…") : $bodyLine;

        return
            "$pretty\n"
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
    public function throw(): void
    {
        throw new ParserHasFailed($this);
    }


}
