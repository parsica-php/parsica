<?php declare(strict_types=1);

namespace Verraes\Parsica;

use Throwable;

interface ParserFailure extends Throwable
{
    /**
     * The input that the parser encountered when it failed.
     */
    public function got(): string;

    /**
     * Information about what the parser expected at the position where it failed.
     */
    public function expected(): string;
}