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

final class StringStream implements Stream
{
    private array $stringAsArray;
    private Position $position;
    private array $transactionCounters = [];

    /**
     * @api
     */
    public function __construct(string $string, ?Position $position = null)
    {
        $this->stringAsArray = mb_str_split($string);
        $this->position = $position ?? Position::initial();
    }

    /**
     * @inheritDoc
     * @internal
     */
    public function take1(): TakeResult
    {
        $token = current($this->stringAsArray);
        $this->guardEndOfStream();
        next($this->stringAsArray);
        $this->advance();

        $this->position = $this->position->advance($token);

        return new TakeResult(
            $token,
            $this
        );
    }

    /**
     * @inheritDoc
     */
    public function isEOF(): bool
    {
        $isEOF = current($this->stringAsArray) === false;

        return $isEOF;
    }

    /**
     * @inheritDoc
     */
    public function takeN(int $n): TakeResult
    {
        if ($n <= 0) {
            return new TakeResult("", $this);
        }

        $chunk = '';
        for ($i = 1; $i <= $n; $i++) {
            $token = current($this->stringAsArray);
            $this->guardEndOfStream();
            next($this->stringAsArray);
            $this->advance();

            $chunk .= $token;
        }

        $this->position = $this->position->advance($chunk);

        return new TakeResult(
            $chunk,
            $this
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

        $chunk = '';

        while ($token = current($this->stringAsArray)) {
            $this->guardEndOfStream();
            next($this->stringAsArray);
            $this->advance();

            if ($predicate($token)) {
                $chunk .= $token;
            } else {
                prev($this->stringAsArray);
                break;
            }
        }

        $this->position = $this->position->advance($chunk);

        return new TakeResult(
            $chunk,
            $this
        );
    }

    public function __toString(): string
    {
        $currentIndex = key($this->stringAsArray);
        if ($currentIndex === null) {
            return '';
        }

        return implode('', array_slice($this->stringAsArray, $currentIndex));
    }

    /**
     * @inheritDoc
     */
    public function position(): Position
    {
        return $this->position;
    }

    private function guardEndOfStream(): void
    {
        if (current($this->stringAsArray) === false) {
            throw new EndOfStream("End of stream was reached in " . $this->position->pretty());
        }
    }

    public function beginTransaction(): void
    {
        $this->transactionCounters[] = 0;
    }

    private function advance(): void
    {
        if (empty($this->transactionCounters)) {
            return;
        }

        $lastCounterKey = array_key_last($this->transactionCounters);

        $this->transactionCounters[$lastCounterKey]++;
    }

    public function commit(): void
    {
        array_pop($this->transactionCounters);
    }

    public function rollback(): void
    {
        $count = end($this->transactionCounters);

        if ($count === false) {
            return;
        }

        for ($i = 0; $i < $count; $i++) {
            prev($this->stringAsArray);
        }

        array_pop($this->transactionCounters);
    }
}
