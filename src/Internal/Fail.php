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
 * The return value of a failed parser.
 *
 * @TODO make our own Throwable implementation that uses the parsed files as the exceptions file etc?
 * @template T
 * @internal
 */
final class Fail extends Exception implements ParserFailure, ParseResult
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
        parent::__construct($this->errorMessage());
    }

    public function errorMessage(): string
    {
        try {
            $firstChar = $this->got->take1()->chunk();
            $unexpected = self::replaceControlChar($firstChar);
            $body = $this->got()->takeWhile(fn($c) => $c != "\n")->chunk();
        } catch (EndOfStream $e) {
            $unexpected = $body = "<EOF>";
        }
        $lineNumber = $this->got->position()->line();
        $spaceLength = str_repeat(" ", strlen((string)$lineNumber));
        $expecting = $this->expected;
        $position = $this->got->position()->pretty();

        return
              "$position\n"
            . "$spaceLength |\n"
            . "$lineNumber | $body\n"
            . "$spaceLength | ^\n"
            . "Unexpected '$unexpected'\n"
            . "Expecting $expecting\n"
            ;
    }

    private static function replaceControlChar(string $char): string
    {
        switch (mb_ord($char)) {
            case 0:
                return "<NUL>";
            case 7:
                return "<bell>";
            case 8:
                return "<backspace>";
            case 9:
                return "<tab>";
            case 10:
                return "<line feed>";
            case 11:
                return "<vertical tab>";
            case 12:
                return "<form feed>";
            case 13:
                return "<carriage return>";
            case 26:
                return "<EOF>";
            case 27:
                return "<ESC>";
            default:
                return $char;
        }
    }

    public function expected(): string
    {
        return $this->expected;
    }

    public function got(): Stream
    {
        return $this->got;
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
}
