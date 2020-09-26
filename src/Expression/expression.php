<?php declare(strict_types=1);
/**
 * This file is part of the Parsica library.
 *
 * Copyright (c) 2020 Mathias Verraes <mathias@verraes.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Verraes\Parsica\Expression;

use Verraes\Parsica\Parser;

/**
 * @template T
 *
 * @psalm-param Parser<T> $term
 * @psalm-param ExpressionType[] $expressionTypes
 *
 * @psalm-return Parser<T>
 */
function expression(Parser $term, array $expressionTypes) : Parser
{
    $currentPrecedenceLevel = $term;
    foreach ($expressionTypes as $precedenceLevelGenerator) {
        $currentPrecedenceLevel = $precedenceLevelGenerator->buildPrecedenceLevel($currentPrecedenceLevel);
    }
    return $currentPrecedenceLevel;

}



