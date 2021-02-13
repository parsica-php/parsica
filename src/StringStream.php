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

use Verraes\Parsica\Internal\EndOfStream;
use Verraes\Parsica\Internal\Position;
use Verraes\Parsica\Internal\TakeResult;

/**
 * A stream for regular strings. Use MBStringStream instead if you need unicode support.
 *
 * @psalm-immutable
 */
final class StringStream implements Stream
{
    private string $string;
    private Position $position;

    /**
     * @api
     */
    public function __construct(string $string, ?Position $position = null)
    {
        $this->string = $string;
        $this->position = $position ?? Position::initial();
    }

    /**
     * @inheritDoc
     * @internal
     */
    public function take1(): TakeResult
    {
        $this->guardEndOfStream();

        $token = substr($this->string, 0, 1);
        $position = $this->position->advance($token);

        return new TakeResult(
            $token,
            new StringStream(mb_substr($this->string, 1), $position)
        );
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
    public function isEOF(): bool
    {
        return strlen($this->string) === 0;
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

        $chunk = substr($this->string, 0, $n);
        return new TakeResult(
            $chunk,
            new StringStream(
                substr($this->string, $n),
                $this->position->advance($chunk)
            )
        );
    }

    /**
     * @inheritDoc
     */
    public function takeWhile(callable $predicate): TakeResult
    {
        if($this->isEOF()) {
            return new TakeResult("", $this);
        }

        $remaining = $this->string;
        $nextToken = substr($remaining, 0, 1);
        $chunk = "";
        while ($predicate($nextToken)) {
            $chunk .= $nextToken;
            $remaining = substr($remaining, 1);
            if (strlen($remaining) > 0) {
                $nextToken = substr($remaining, 0, 1);
            } else {
                break;
            }
        }

        return new TakeResult(
            $chunk,
            new StringStream($remaining, $this->position->advance($chunk))
        );
    }

    public function __toString(): string
    {
        return $this->string;
    }

    /**
     * @inheritDoc
     */
    public function position(): Position
    {
        return $this->position;
    }
}
