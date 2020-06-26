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

/**
 * Create a recursive parser. Used in combination with recurse(Parser).
 *
 * For an example see {@see RecursiveParserTest}.
 *
 * @return Parser<T>
 * @api
 *
 * @template T
 */
function recursive(): Parser
{
    return Parser::recursive();
}
