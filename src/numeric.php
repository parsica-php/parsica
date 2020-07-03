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
 * Parse a float. Returns the float as a string. Use ->map('floatval')
 * or similar to cast it to a numeric type.
 *
 * @psalm-return Parser<string>
 *
 * @deprecated @TODO doesn't support signed numbers yet
 * @api
 */
function float(): Parser
{
    return
        atLeastOne(digitChar())
            ->append(
                optional(
                    char('.')
                        ->append(atLeastOne(digitChar()))
                )
            )->label('float');
}
