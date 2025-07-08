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

use Parsica\Parsica\Stream;

/**
 * The result of Stream::take*() functions
 *
 * @internal
 * @psalm-immutable
 */
final class TakeResult
{
    private string $chunk;
    private Stream $stream;

    function __construct(string $chunk, Stream $stream)
    {
        $this->chunk = $chunk;
        $this->stream = $stream;
    }

    function chunk(): string
    {
        return $this->chunk;
    }

    function stream(): Stream
    {
        return $this->stream;
    }
}
