<?php declare(strict_types=1);

namespace Verraes\Parsica;

use InvalidArgumentException;
use Verraes\Parsica\Internal\EndOfStream;
use Verraes\Parsica\Internal\Position;
use Verraes\Parsica\Internal\TakeResult;

final class TextFileStream implements Stream
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
        if (!is_file($filePath)) {
            throw new InvalidArgumentException("The file path for the text-file is not a valid file.");
        }
        $this->filePath = $filePath;
        $this->fileHandle = fopen($this->filePath, 'rb');
        $this->position = $position ?? Position::initial($this->filePath);
        if (true !== is_null($position)) {
            fseek($this->fileHandle, $this->position->bytePosition());
        }
    }

    public function __destruct()
    {
        if (is_resource($this->fileHandle)) {
            fclose($this->fileHandle);
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

    private function safeRead(?int $n = null): string
    {
        if (is_null($n)) {
            $tokenChunk = fgetc($this->fileHandle);
        } else {
            $tokenChunk = fread($this->fileHandle, $n);
        }
        rewind($this->fileHandle);
        fseek($this->fileHandle, $this->position->bytePosition());
        return !$tokenChunk ? '' : $tokenChunk;
    }

    /**
     * @inheritDoc
     */
    public function take1(): TakeResult
    {
        $this->guardEndOfStream();

        $token = $this->safeRead();
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

        $chunk = $this->safeRead($n);
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

        $chunk = ""; // Init the result buffer
        $nextToken = fgetc($this->fileHandle);
        while ($predicate($nextToken)) {
            $chunk .= $nextToken;
            if (!feof($this->fileHandle)) {
                $nextToken = fgetc($this->fileHandle);
            } else {
                break;
            }
        }
        $position = $this->position->advance($chunk);
        $this->safeRead();

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

        fseek($this->fileHandle, $this->position->bytePosition());
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
