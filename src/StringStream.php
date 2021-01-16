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

use Exception;
use InvalidArgumentException;
use Verraes\Parsica\Internal\EndOfStream;
use Verraes\Parsica\Internal\NovelImmutablePosition;

final class StringStream implements Stream
{
    private array $stringAsArray;
    private string $filename = "<input>";
    private array $positions = [];
    private int $length;

    private function __construct()
    {
    }

    /**
     * @api
     */
    public static function fromString(string $string, string $filename = "<input>"): StringStream
    {
        $stream = new StringStream();
        $stream->filename = $filename;
        $stream->stringAsArray = mb_str_split($string);
        $stream->length = count($stream->stringAsArray);
        $stream->positions[] = NovelImmutablePosition::initial();
        return $stream;
    }

    public function filename(): string
    {
        return $this->filename;
    }



    /**
     * The position of the parser in the stream.
     *
     * @internal
     */
    public function position(): NovelImmutablePosition
    {
        return end($this->positions);
    }

    /**
     * Pretty print as "filename:line:column"
     */
    public function pretty(): string
    {
        return $this->position()->pretty($this->filename);
    }


    /**
     * @inheritDoc
     */
    public function isEOF(): bool
    {
        return $this->position()->pointer() >= $this->length;
    }

    /**
     * @deprecated use peakAll()
     * @deprecated We should probably get rid of this entirely
     * @todo We should probably get rid of this entirely
     */
    public function __toString(): string
    {
        return $this->peakAll();
    }

    public function beginTransaction(): void
    {
        $this->positions[] = $this->position();
    }

    public function commit(): void
    {
        if (count($this->positions) === 1) {
            throw new Exception("Can't commit, there are no active transactions.");
        }
        $last = array_pop($this->positions);
        $this->positions[array_key_last($this->positions)] = $last;
    }

    public function rollback(): void
    {
        if (count($this->positions) === 1) {
            throw new Exception("Can't rollback, there are no active transactions.");
        }
        array_pop($this->positions);
    }

    /**
     * @inheritDoc
     * @internal
     */
    public function take1(): string
    {
        // @TODO If we move to PHP8:
        // $token = $this->stringAsArray[$this->position()->pointer()] ?? throw new EndOfStream("End of stream was reached in " . $this->pretty());
        $token = $this->stringAsArray[$this->position()->pointer()] ?? null;
        if(is_null($token)) {
            throw new EndOfStream("End of stream was reached in " . $this->pretty());
        }
        //

        $this->positions[array_key_last($this->positions)] = $this->position()->advance($token);

        return $token;
    }

    /**
     * @inheritDoc
     */
    public function takeN(int $n): string
    {
        if ($n < 0) {
            throw new InvalidArgumentException("The argument to takeN() must be >= 0, got $n.");
        } elseif ($n === 0) {
            return '';
        }

        $chunk = '';
        for ($i = 0; $i < $n; $i++) {
            $token = $this->take1();
            $chunk .= $token;
        }
        return $chunk;
    }


    /**
     * @inheritDoc
     */
    public function takeWhile(callable $predicate): string
    {
        if (!array_key_exists($this->position()->pointer(), $this->stringAsArray)) {
            return '';
        }
        $chunk = '';

        while ($predicate($this->peak1())) {
            $token = $this->take1();
            $chunk .= $token;
            if (!array_key_exists($this->position()->pointer(), $this->stringAsArray)) {
                return $chunk;
            }
        }

        return $chunk;
    }

    /**
     * Read the next token without advancing the stream pointer, or return the empty string
     *
     */
    public function peak1(): string
    {
        return array_key_exists($this->position()->pointer(), $this->stringAsArray)
            ? $this->stringAsArray[$this->position()->pointer()]
            : '';
    }

    /**
     * Read the next n tokens without advancing the stream pointer
     *
     */
    public function peakN(int $n): string
    {
        return join('', array_slice($this->stringAsArray, $this->position()->pointer(), $n));
    }

    /**
     * Read the next n tokens without advancing the stream pointer
     *
     */
    public function peakWhile(callable $predicate): string
    {
        $chunk = '';

        $pointer = $this->position()->pointer();
        while (array_key_exists($pointer, $this->stringAsArray) && $predicate($this->stringAsArray[$pointer])) {
            $chunk .= $this->stringAsArray[$pointer];
            if (!array_key_exists($this->position()->pointer(), $this->stringAsArray)) {
                return $chunk;
            }
            $pointer++;
        }

        return $chunk;
    }

    /**
     * @deprecated
     */
    public function peakAll() : string
    {
        return implode('', array_slice($this->stringAsArray, $this->position()->pointer() ));
    }

    /**
     * @inheritDoc
     */
    public function peakBack(): string
    {
        return '';
    }
}
