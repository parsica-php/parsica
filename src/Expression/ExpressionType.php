<?php declare(strict_types=1);
/*
 * This file is part of the Parsica library.
 *
 * Copyright (c) 2020 Mathias Verraes <mathias@verraes.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Parsica\Parsica\Expression;

use Parsica\Parsica\Parser;

/**
 * @internal
 * @template TExpressionAST
 * @psalm-immutable
 */
interface ExpressionType
{
    /**
     * @psalm-param Parser<TExpressionAST> $previousPrecedenceLevel
     * @psalm-return  Parser<TExpressionAST>
     */
    public function buildPrecedenceLevel(Parser $previousPrecedenceLevel): Parser;
}
