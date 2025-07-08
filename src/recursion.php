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

/**
 * Create a recursive parser. Used in combination with Parser#recurse().
 *
 * @psalm-return Parser<T>
 * @api
 *
 * @template T
 * @psalm-pure
 */
function recursive(): Parser
{
    return Parser::recursive();
}
