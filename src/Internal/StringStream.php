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

final class StringStream implements Stream
{
    private string $string;
    private Position $position;

    public function __construct(string $string, ?Position $position = null)
    {
        $this->string = $string;
        $this->position = $position ?? Position::initial();
    }

    /**
     * @inheritDoc
     */
    public function take1(): TakeResult
    {
        $this->guardEndOfStream();

        $token = mb_substr($this->string, 0, 1);
        $position = $this->position->update($token);

        return new TakeResult(
            $token,
            new StringStream(mb_substr($this->string, 1), $position)
        );
    }

    private function guardEndOfStream(): void
    {
        if ($this->isEOF()) {
            throw new EndOfStream("End of stream was reached  in " . $this->position->pretty());
        }
    }

    public function isEOF(): bool
    {
        return mb_strlen($this->string) === 0;
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


        $chunk = mb_substr($this->string, 0, $n);
        $position = $this->position->update($chunk);

        return new TakeResult(
            $chunk,
            new StringStream(mb_substr($this->string, $n), $position)
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
