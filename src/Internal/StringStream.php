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

    public function __construct(string $string)
    {
        $this->string = $string;
    }

    /**
     * @inheritDoc
     */
    public function take1(): Take1
    {
        $this->guardEndOfStream();

        return new Take1(
            mb_substr($this->string, 0, 1),
            new StringStream(mb_substr($this->string, 1))
        );
    }

    /**
     * @inheritDoc
     */
    public function takeN(int $n): TakeN
    {
        if ($n <= 0) {
            return new TakeN("", $this);
        }

        $this->guardEndOfStream();

        return new TakeN(
            mb_substr($this->string, 0, $n),
            new StringStream(mb_substr($this->string, $n))
        );
    }

    private function guardEndOfStream(): void
    {
        if ($this->isEOF()) {
            throw new EndOfStream("End of stream was reached.");
        }
    }

    public function isEOF(): bool
    {
        return mb_strlen($this->string) === 0;
    }

    public function __toString(): string
    {
        return $this->string;
    }

}
