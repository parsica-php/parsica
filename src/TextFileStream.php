<?php declare(strict_types=1);

namespace Parsica\Parsica;

use InvalidArgumentException;
use MallardDuck\ImmutableReadFile\ImmutableFile;
use Parsica\Parsica\Internal\EndOfStream;
use Parsica\Parsica\Internal\PositionWithBytes;
use Parsica\Parsica\Internal\TakeResult;

/**
 * @psalm-external-mutation-free
 */
final class TextFileStream implements Stream
{

    private string $filePath;
    /**
     * @psalm-allow-private-mutation
     */
    private ImmutableFile $fileHandle;
    private PositionWithBytes $position;

    public static function createFromPosition(PositionWithBytes $position): self
    {
        return new self($position->filename(), $position);
    }

    public function __construct(string $filePath, ?PositionWithBytes $position = null)
    {
        /**
         * @psalm-suppress ImpureFunctionCall
         */
        if (!is_file($filePath)) {
            throw new InvalidArgumentException("The file path for the text-file is not a valid file.");
        }
        $this->filePath = $filePath;
        $this->position = $position ?? PositionWithBytes::initial($this->filePath);
        $this->fileHandle = ImmutableFile::fromFilePathWithPosition($this->filePath, $this->position->bytePosition());
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

        /**
         * @psalm-suppress ImpureMethodCall
         */
        $token = $this->fileHandle->fgetc();
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

        /**
         * @psalm-suppress ImpureMethodCall
         */
        $chunk = $this->fileHandle->fread($n);
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

        $remaining = $this->fileHandle;
        /**
         * @psalm-suppress ImpureMethodCall
         */
        $nextToken = $this->fileHandle->fgetc();
        $chunk = ""; // Init the result buffer
        while ($predicate($nextToken)) {
            $chunk .= $nextToken;
            /**
             * @psalm-suppress ImpureMethodCall
             */
            $remaining = $remaining->advanceBytePosition();
            if (!$remaining->feof()) {
                /**
                 * @psalm-suppress ImpureMethodCall
                 */
                $nextToken = $remaining->fgetc();
            } else {
                break;
            }
        }

        return new TakeResult(
            $chunk,
            self::createFromPosition($this->position->advance($chunk))
        );
    }

    /**
     * @inheritDoc
     */
    public function __toString(): string
    {
        /**
         * @psalm-suppress ImpureMethodCall
         */
        if (0 === $this->fileHandle->getFileSize()) {
            return "<EMPTYFILE>";
        }

        return (string) $this->fileHandle;
    }

    /**
     * @inheritDoc
     */
    public function isEOF(): bool
    {
        return $this->fileHandle->feof();
    }

    /**
     * @inheritDoc
     */
    public function position(): PositionWithBytes
    {
        return $this->position;
    }

    public function filePath(): string
    {
        return $this->filePath;
    }
}
