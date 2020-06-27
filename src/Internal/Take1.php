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

/**
 * The result of Stream::take1
 *
 * @internal
 */
final class Take1
{
    private string $token;
    private Stream $stream;

    function __construct(string $token, Stream $stream)
    {
        $this->token = $token;
        $this->stream = $stream;
    }

    function token(): string
    {
        return $this->token;
    }

    function stream(): Stream
    {
        return $this->stream;
    }
}
