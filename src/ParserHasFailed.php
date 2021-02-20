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

use Exception;
use Parsica\Parsica\Internal\Fail;

/**
 * @api
 */
final class ParserHasFailed extends Exception
{
    private Fail $parseResult;

    /**
     * @inheritDoc
     */

    function __construct(Fail $parseResult)
    {
        $this->parseResult = $parseResult;
        parent::__construct($this->parseResult->errorMessage());
    }

    function parseResult() : Fail
    {
        return $this->parseResult;
    }

}
