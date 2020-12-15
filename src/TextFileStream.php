<?php

namespace Verraes\Parsica;

use Verraes\Parsica\Internal\Position;
use Verraes\Parsica\Internal\TakeResult;

class TextFileStream implements Stream
{

    private string $filePath;
    private $fileHandle;
    private Position $position;

    public static function createFromPosition(Position $position): self
    {
        return new self($position->filename(), $position);
    }

    public function __construct(string $filePath, ?Position $position = null)
    {
        $this->filePath = $filePath;
        $this->fileHandle = fopen($this->filePath, 'rb');
        $this->position = $position ?? Position::initial($this->filePath);
        if (!is_null($position)) {
            fseek($this->fileHandle, $this->position->bytePosition());
        }
    }

    /**
     * @throws EndOfStream
     */
    private function guardEndOfStream(): void
    {
        if ($this->isEOF()) {
            throw new EndOfStream("End of stream was reached in " . $this->position->pretty());
        }
    }

    /**
     * @inheritDoc
     */
    public function take1(): TakeResult
    {
        $this->guardEndOfStream();

        $token = fgetc($this->fileHandle);
        $position = $this->position->advance($token);

        return new TakeResult(
            $token,
            self::createFromPosition($position)
        );
    }

    /**
     * @inheritDoc
     */
    public function takeN(int $n): TakeResult
    {
        if ($n <= 0) {
            return new TakeResult("", $this);
        }

        $this->guardEndOfStream();

        $chunk = fread($this->fileHandle, $n);
        $position = $this->position->advance($chunk);

        return new TakeResult(
            $chunk,
            self::createFromPosition($position)
        );
    }

    /**
     * @inheritDoc
     */
    public function takeWhile(callable $predicate): TakeResult
    {
        if ($this->isEOF()) {
            return new TakeResult("", $this);
        }

        /**
         * Variable to track if loop breaks due to EOF.
         * @var bool $eof
         */
        $eof = false;

        $chunk = ""; // Init the result buffer
        $nextToken = fgetc($this->fileHandle);
        while ($predicate($nextToken)) {
            $chunk .= $nextToken;
            if (!feof($this->fileHandle)) {
                $nextToken = fgetc($this->fileHandle);
            } else {
                $eof = true;
                break;
            }
        }
        // If the loop breaks because EOF then skip this.
        if (!$eof) {
            // However if the loop breaks because the predicate, then step one byte back.
            fseek($this->fileHandle, -1, SEEK_CUR);
        }
        $position = $this->position->advance($chunk);

        return new TakeResult(
            $chunk,
            self::createFromPosition($position)
        );
    }

    /**
     * @inheritDoc
     */
    public function __toString(): string
    {
        if (0 === ($size = filesize($this->filePath))) {
            return "<EMPTYFILE>";
        }

        $stringData = fread($this->fileHandle, $size);
        fseek($this->fileHandle, $this->position->bytePosition());
        return $stringData;
    }

    /**
     * @inheritDoc
     */
    public function isEOF(): bool
    {
        return feof($this->fileHandle);
    }

    /**
     * @inheritDoc
     */
    public function position(): Position
    {
        return $this->position;
    }

    public function filePath(): string
    {
        return $this->filePath;
    }
}
