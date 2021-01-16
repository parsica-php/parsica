<?php
/*
 * This file is part of the Parsica library.
 *
 * Copyright (c) 2020 Mathias Verraes <mathias@verraes.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Verraes\Parsica\Internal;

final class NovelImmutablePosition
{
    private int $pointer;
    private int $line;
    private int $column;
    private string $bufferedToken;

    public function __construct(int $pointer, int $line, int $column)
    {
        $this->pointer = $pointer;
        $this->line = $line;
        $this->column = $column;
    }

    public static function initial(): NovelImmutablePosition
    {
        return new NovelImmutablePosition(0, 1, 1);

    }

    public function advance(string $token): NovelImmutablePosition
    {
        if (mb_strlen($token) !== 1) {
            throw new \InvalidArgumentException("Expected 1 character.");
        }
        $pointer = $this->pointer + 1;
        switch ($token) {
            case "\n":
            case "\r":
                $line = $this->line + 1;
                $column = 1;
                break;
            case "\t":
                $line = $this->line;
                $column = $this->column + 4 - (($this->column - 1) % 4);
                break;
            default:
                $line = $this->line;
                $column = $this->column + 1;
        }
        return new NovelImmutablePosition($pointer, $line, $column);
    }


    public function retreat($token): NovelImmutablePosition
    {
        if (mb_strlen($token) !== 1) {
            throw new \InvalidArgumentException("Expected 1 character.");
        }
        $pointer = $this->pointer - 1;
        switch ($token) {
            case "\n":
            case "\r":
                $line = $this->line - 1;
                $column = 1;
                break;
            case "\t":
                $line = $this->line;
                $column = $this->column + 4 - (($this->column - 1) % 4);
                break;
            default:
                $line = $this->line;
                $column = $this->column + 1;
        }
        return new NovelImmutablePosition($pointer, $line, $column);
    }

    public function pretty(string $filename): string
    {
        return $filename . ":" . $this->line() . ":" . $this->column();
    }

    /**
     * Index of where we are in the stream. Zero-based indexing.
     */
    public function pointer(): int
    {
        return $this->pointer;
    }

    public function line(): int
    {
        return $this->line;
    }

    public function column(): int
    {
        return $this->column;
    }



}
