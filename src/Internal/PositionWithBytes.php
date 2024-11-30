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

/**
 * File, line, and column position of the parser.
 *
 * @psalm-immutable
 * @psalm-external-mutation-free
 */
final class PositionWithBytes implements BasePosition
{
    /** @psalm-readonly  */
    private string $filename;
    /** @psalm-readonly  */
    private int $line;
    /** @psalm-readonly  */
    private int $column;
    /** @psalm-readonly  */
    private int $bytePosition;

    function __construct(string $filename, int $line, int $column, int $bytePosition)
    {
        $this->filename = $filename;
        $this->line = $line;
        $this->column = $column;
        $this->bytePosition = $bytePosition;
    }

    /**
     * Initial position (line 1, column 1). The optional filename is the source of the input, and is really just a label
     * to make more useful error messages.
     */
    public static function initial(string $filename = "<input>"): PositionWithBytes
    {
        return new PositionWithBytes($filename, 1, 1, 0);
    }

    /**
     * Pretty print as "filename:line:column"
     */
    public function pretty(): string
    {
        return $this->filename . ":" . $this->line . ":" . $this->column;
    }

    public function advance(string $parsed): PositionWithBytes
    {
        $column = $this->column;
        $line = $this->line;
        $bytePosition = $this->bytePosition;
        /** @psalm-var string $char */
        foreach (mb_str_split($parsed, 1) as $char) {
            switch ($char) {
                case "\n":
                case "\r":
                    $line++;
                    $column = 1;
                    break;
                case "\t":
                    $column = $column + 4 - (($column - 1) % 4);
                    break;
                default:
                    $column++;
            }
            $bytePosition += strlen($char);
        }

        return new PositionWithBytes($this->filename, $line, $column, $bytePosition);
    }

    public function filename(): string
    {
        return $this->filename;
    }

    public function line(): int
    {
        return $this->line;
    }

    public function column(): int
    {
        return $this->column;
    }

    public function bytePosition(): int
    {
        return $this->bytePosition;
    }
}
