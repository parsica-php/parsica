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

use Throwable;

interface ParserFailure extends Throwable
{
    /**
     * The input that the parser encountered when it failed.
     *
     * @api
     */
    public function got(): string;

    /**
     * Information about what the parser expected at the position where it failed.
     *
     * @api
     */
    public function expected(): string;
}
