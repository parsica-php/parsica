<?php declare(strict_types=1);
/**
 * This file is part of the Parsica library.
 *
 * Copyright (c) 2020 Mathias Verraes <mathias@verraes.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Parsica\Parsica;

use Parsica\Parsica\Internal\EndOfStream;
use Parsica\Parsica\Internal\Position;
use Parsica\Parsica\Internal\TakeResult;

/**
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
     * @psalm-mutation-free
     */
    public function take1(): TakeResult
    {
        if ($this->string === '') {
            throw new EndOfStream("End of stream was reached in " . $this->position->pretty());
        }

        $token = mb_substr($this->string, 0, 1);
        $position = $this->position->advance($token);

        return new TakeResult(
            $token,
            new StringStream(mb_substr($this->string, 1), $position)
        );
    }

    /**
     * @inheritDoc
     * @psalm-mutation-free
     */
    public function isEOF(): bool
    {
        return $this->string === '';
    }

    /**
     * @inheritDoc
     * @psalm-mutation-free
     */
    public function takeN(int $n): TakeResult
    {
        if ($n <= 0) {
            return new TakeResult("", $this);
        }

        if ($this->string === '') {
            throw new EndOfStream("End of stream was reached in " . $this->position->pretty());
        }

        $chunk = mb_substr($this->string, 0, $n);
        return new TakeResult(
            $chunk,
            new StringStream(
                mb_substr($this->string, $n),
                $this->position->advance($chunk)
            )
        );
    }

    /**
     * @psalm-param pure-callable(string) : bool $predicate
     * @psalm-mutation-free
     * @inheritDoc
     */
    public function takeWhile(callable $predicate): TakeResult
    {
        if ($this->string === '') {
            return new TakeResult("", $this);
        }

        $remaining = $this->string;
        $nextToken = mb_substr($remaining, 0, 1);
        $chunk = "";
        while ($predicate($nextToken)) {
            $chunk .= $nextToken;
            $remaining = mb_substr($remaining, 1);
            if ($remaining !== '') {
                $nextToken = mb_substr($remaining, 0, 1);
            } else {
                break;
            }
        }

        return new TakeResult(
            $chunk,
            new StringStream($remaining, $this->position->advance($chunk))
        );
    }

    /**
     * @psalm-mutation-free
     */
    public function __toString(): string
    {
        return $this->string;
    }

    /**
     * @inheritDoc
     * @psalm-mutation-free
     */
    public function position(): Position
    {
        return $this->position;
    }
}
